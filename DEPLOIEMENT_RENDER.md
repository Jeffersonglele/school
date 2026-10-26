# Déployer « school » sur Render

Application : PHP 8.2 (sans framework) + MySQL/MariaDB, paiement FedaPay.
Fichiers : `Dockerfile`, `docker-compose.yml` (test local), `render.yaml` (déploiement), `docker/` (config, script de démarrage, schéma SQL).

## 0. Ce qu'il faut savoir avant

- **Render n'a pas de MySQL managé** (seulement PostgreSQL et Key Value). Deux solutions :
  - **A. MariaDB hébergée sur Render** (Private Service + disque) : tout au même endroit, prévu dans `render.yaml`. Les disques persistants exigent une instance payante.
  - **B. MySQL externe** (n'importe quel hébergeur MySQL/MariaDB accessible depuis Internet) : tu renseignes seulement les variables `DB_*`.
- **Régénère ta clé FedaPay** : l'ancienne clé `sk_sandbox_...` était écrite dans `config.php` et se trouve dans l'historique Git. Elle n'est plus dans le code ; crée-en une nouvelle dans le tableau de bord FedaPay et utilise-la ci-dessous.
- Le site doit être sur GitHub (ou GitLab) pour que Render le lise. **Rien n'est commité ni poussé pour l'instant** : relis les changements (`git diff`, `git status`), puis commite/pousse quand tu es d'accord.

## 1. Tester en local (Docker Desktop requis)

```bash
docker compose up --build
```

Puis ouvre http://localhost:8080 (page de connexion) et http://localhost:8080/healthz.php?db=1 (doit afficher `"db":"ok"`).
Pour le paiement local : `FEDAPAY_API_KEY=sk_sandbox_xxx docker compose up --build` (ou un fichier `.env`, voir `.env.example`).
Les données MariaDB sont dans le volume `db_data` (elles survivent à `docker compose down` ; `docker compose down -v` les efface).

## 2. Solution A : tout sur Render avec le Blueprint (`render.yaml`)

1. Pousse le code sur GitHub.
2. Render → **New +** → **Blueprint** → choisis le dépôt. Render lit `render.yaml` et propose 2 services : `school` (site, Docker) et `school-db` (MariaDB privée + disque 1 Go).
3. Renseigne les variables demandées (marquées « secret ») :
   - `FEDAPAY_API_KEY` : ta nouvelle clé (`sk_sandbox_...` pour tester, `sk_live_...` en réel).
   - `APP_URL` : laisse vide pour l'instant (tu la rempliras à l'étape 6).
4. **Apply**. Render construit les deux images. Le mot de passe de la base est généré automatiquement et transmis au site (`fromService`), tu n'as rien à copier.
5. Suis les logs de `school` : tu dois voir `Connexion à la base : OK` puis `Base vide : import du schéma initial` puis `Lancement d'Apache`.

## 3. Solution B : site seul sur Render + MySQL externe

1. Crée la base chez ton hébergeur MySQL/MariaDB (nom, utilisateur, mot de passe). Autorise les connexions depuis Internet (ou les IP de Render).
2. Render → **New +** → **Web Service** → dépôt GitHub.
3. **Language : Docker** (Render détecte le `Dockerfile` à la racine). Région au choix, plan au choix.
4. **Advanced** → **Health Check Path** : `/healthz.php`.
5. Variables d'environnement (voir le tableau ci-dessous).
6. **Create Web Service**.

## 4. Variables d'environnement

| Variable | Obligatoire | Exemple / valeur |
|---|---|---|
| `DB_HOST` | oui | hôte de la base (Blueprint : rempli automatiquement) |
| `DB_PORT` | non (3306) | `3306` |
| `DB_DATABASE` | oui | `projet1` |
| `DB_USERNAME` | oui | `school` |
| `DB_PASSWORD` | oui | mot de passe de la base (secret) |
| `DB_SSL` | non | `true` si l'hébergeur MySQL impose TLS (Aiven : oui) |
| `DB_SSL_VERIFY` | non (`true`) | `false` avec Aiven (certificat signé par une CA privée) : la connexion est chiffrée mais le certificat n'est pas vérifié |
| `DB_SSL_CA` | non | chemin d'un certificat CA précis, à la place de `DB_SSL` |
| `FEDAPAY_API_KEY` | oui pour payer | `sk_sandbox_...` ou `sk_live_...` (secret) |
| `FEDAPAY_ENVIRONMENT` | non (`sandbox`) | `sandbox` ou `live` |
| `APP_URL` | recommandé | `https://school.onrender.com` (sert aux callbacks de paiement) |
| `APP_DEBUG` | non | laisser vide en production (`1` = erreurs PHP à l'écran) |
| `DB_AUTO_MIGRATE` | non (`true`) | `false` pour ne jamais toucher au schéma |
| `DB_REQUIRED` | non (`true`) | `false` = démarrer même si la base est KO (pages 503) |
| `DB_WAIT_SECONDS` | non (`60`) | attente max de la base au démarrage |

Ne définis **pas** `PORT` : Render le fournit (10000 par défaut) et Apache l'utilise automatiquement.

## 5. Migrations (base de données)

Il n'y a pas d'outil de migration dans ce projet (pas Laravel, pas de `php artisan migrate`). À la place, le script de démarrage fait ceci, **sans commande à lancer toi-même** :

- base **vide** → importe `docker/db/schema.sql` (structure + années, cycles, classes ; **sans** les données personnelles du dump d'origine) ;
- base avec la table `users` → ne fait rien (tes données ne sont jamais touchées) ;
- base à moitié remplie, sans `users` → s'arrête avec une erreur claire plutôt que d'écraser quoi que ce soit.

Pour changer le schéma plus tard : écris un fichier SQL `ALTER TABLE...` et lance-le à la main sur la base (phpMyAdmin, ou `mysql -h ... < fichier.sql`). Le script ne rejoue jamais d'ancien SQL.

## 6. Après le premier déploiement

1. Copie l'URL du site (`https://school-xxxx.onrender.com`) et mets-la dans `APP_URL`. Render redéploie.
2. Ouvre `/healthz.php?db=1` : `{"status":"ok","db":"ok"}`.
3. Ouvre la page d'accueil (page de connexion), crée un compte via « Inscription », connecte-toi.

## 7. Si la base est injoignable (le cas de MyWallet)

Le démarrage est volontairement bruyant :

- Chaque tentative affiche l'erreur **réelle** de MySQL dans les logs, par exemple `[school] Base injoignable (tentative 2/20) : ERROR 1045 (28000): Access denied for user ...` (mauvais mot de passe) ou `ERROR 2002 ... Can't connect` (mauvais hôte/port) ou `ERROR 1049 Unknown database`.
- Au bout de `DB_WAIT_SECONDS`, le conteneur s'arrête avec `ERREUR : la base de données reste injoignable`. Render marque le déploiement **échoué** et garde l'ancienne version en ligne : pas de site cassé en silence.
- Si la base tombe plus tard, les visiteurs voient « Service temporairement indisponible » (HTTP 503) et le détail technique est dans les logs (`[school] Connexion MySQL impossible (host=... port=... db=... user=...)`). Il n'est jamais affiché dans la page.

## 8. Limites connues du projet (non corrigées ici)

- **Fichiers du site qui n'existent pas** : `success.php` appelle `db.php` et `login.php`, `paiement.php` appelle `callback.php`, `connexion.php` redirige vers `admin_dashboard.php`, `passreset.php` utilise PHPMailer (absent de `composer.json`). Le retour de paiement FedaPay (`success.php`) ne fonctionnera pas tant que ce n'est pas corrigé.
- **E-mails** : `passforget.php` utilise `mail()`, qui ne marche pas dans un conteneur. Il faudra un vrai service SMTP (PHPMailer + SMTP).
- **Schéma SQL ≠ code** : la table `users` n'a pas de colonne `role` (avertissements PHP dans les logs, sans blocage), et `charger_classes.php` cherche des colonnes `cycle`/`niveau` qui n'existent pas dans `classes`.
- `paiement.php` semble créer une transaction FedaPay « de test » à chaque chargement de la page (début du fichier) : à vérifier.
- Les sessions PHP sont dans le conteneur : un redéploiement déconnecte les utilisateurs. Le code n'envoie aucun fichier (pas d'upload), donc rien à conserver sur disque.
- Plus tard, si tu veux une base 100 % managée par Render, il faudrait migrer le code vers PostgreSQL (les requêtes MySQL sont à revoir).
