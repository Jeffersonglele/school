#!/bin/sh
# Script de démarrage : vérifie la base, importe le schéma si elle est vide,
# puis lance Apache. Tout est écrit sur stderr pour apparaître dans les logs.
#
# Principe : on n'avale JAMAIS une erreur. Si la base est injoignable, on
# affiche le message réel de MySQL à chaque tentative, puis on s'arrête avec
# un code d'erreur (Render affiche alors "Deploy failed" et garde l'ancienne
# version en ligne) au lieu de démarrer un site cassé en silence.
set -eu

log()  { echo "[school] $*" >&2; }
fail() { log "ERREUR : $*"; exit 1; }

PORT="${PORT:-10000}"
export PORT

DB_HOST="${DB_HOST:-}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_WAIT_SECONDS="${DB_WAIT_SECONDS:-60}"
DB_REQUIRED="${DB_REQUIRED:-true}"          # false = démarrer même si la base est KO (pages 503)
DB_AUTO_MIGRATE="${DB_AUTO_MIGRATE:-true}"  # false = ne jamais toucher au schéma
SCHEMA_FILE="${SCHEMA_FILE:-/opt/school/schema.sql}"

# 1) Variables obligatoires
missing=""
[ -n "$DB_HOST" ]     || missing="$missing DB_HOST"
[ -n "$DB_DATABASE" ] || missing="$missing DB_DATABASE"
[ -n "$DB_USERNAME" ] || missing="$missing DB_USERNAME"
[ -z "$missing" ] || fail "variables d'environnement manquantes :$missing (voir .env.example)"

log "Démarrage - port ${PORT}, base ${DB_USERNAME}@${DB_HOST}:${DB_PORT}/${DB_DATABASE}"
case "$DB_HOST" in
    localhost|127.0.0.1)
        log "ATTENTION : DB_HOST=${DB_HOST} pointe vers le conteneur lui-même, pas vers la base Render." ;;
esac
[ -n "${FEDAPAY_API_KEY:-}" ] || log "ATTENTION : FEDAPAY_API_KEY est vide, le paiement FedaPay sera indisponible."

# 2) Client MySQL (mot de passe via variable d'environnement : pas dans la liste des processus)
export MYSQL_PWD="$DB_PASSWORD"
SSL_OPTS=""
[ -z "${DB_SSL_CA:-}" ] || SSL_OPTS="--ssl-ca=${DB_SSL_CA}"
db() {
    # shellcheck disable=SC2086
    mysql --connect-timeout=5 --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USERNAME" \
        $SSL_OPTS "$DB_DATABASE" "$@"
}

# 3) Attente de la base, avec le vrai message d'erreur à chaque échec
max_tries=$(( DB_WAIT_SECONDS / 3 ))
[ "$max_tries" -ge 1 ] || max_tries=1
tries=0
db_ok=false
while [ "$tries" -lt "$max_tries" ]; do
    if err=$(db -N -e 'SELECT 1' 2>&1); then
        db_ok=true
        break
    fi
    tries=$(( tries + 1 ))
    log "Base injoignable (tentative ${tries}/${max_tries}) : ${err}"
    sleep 3
done

if [ "$db_ok" != "true" ]; then
    log "Vérifiez DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD (et DB_SSL_CA si la base impose TLS)."
    if [ "$DB_REQUIRED" = "true" ]; then
        fail "la base de données reste injoignable après ${DB_WAIT_SECONDS}s, arrêt du démarrage."
    fi
    log "DB_REQUIRED=false : Apache démarre quand même, les pages afficheront une erreur 503 (détail dans les logs)."
else
    log "Connexion à la base : OK"

    # 4) Schéma : importé seulement si la base est totalement vide
    if [ "$DB_AUTO_MIGRATE" = "true" ]; then
        total=$(db -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()") \
            || fail "impossible de lire la liste des tables."
        has_users=$(db -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users'") \
            || fail "impossible de lire la liste des tables."

        if [ "$total" = "0" ]; then
            log "Base vide : import du schéma initial (${SCHEMA_FILE})..."
            db < "$SCHEMA_FILE" || fail "l'import du schéma a échoué (voir le message MySQL ci-dessus). Videz la base puis redéployez."
            log "Schéma importé."
        elif [ "$has_users" = "0" ]; then
            fail "la base contient ${total} table(s) mais pas 'users' : import incomplet ou base étrangère. Rien n'est modifié ; videz la base ou corrigez-la."
        else
            log "Schéma déjà présent (${total} tables) : aucune migration lancée."
        fi
    else
        log "DB_AUTO_MIGRATE=false : schéma non vérifié."
    fi
fi

# 5) Apache (reçoit les signaux d'arrêt de Render grâce à exec)
log "Lancement d'Apache sur le port ${PORT}"
exec "$@"
