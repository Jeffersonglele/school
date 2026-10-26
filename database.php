<?php
// Paramètres lus dans l'environnement (Docker / Render).
// Les valeurs par défaut correspondent à l'ancien réglage XAMPP local.
$host     = getenv('DB_HOST')     ?: 'localhost';
$port     = getenv('DB_PORT')     ?: '3306';
$dbname   = getenv('DB_DATABASE') ?: 'projet1';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$sslCa    = getenv('DB_SSL_CA')   ?: '';   // optionnel : base externe qui impose TLS

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ];
    if ($sslCa !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
    }

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Le détail technique va dans les logs (jamais à l'écran : il révèle hôte et utilisateur).
    error_log(sprintf(
        '[school] Connexion MySQL impossible (host=%s port=%s db=%s user=%s) : %s',
        $host, $port, $dbname, $username, $e->getMessage()
    ));

    http_response_code(503);
    header('Retry-After: 30');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Service indisponible</title></head>'
       . '<body style="font-family:sans-serif;text-align:center;padding:4rem">'
       . '<h1>Service temporairement indisponible</h1>'
       . '<p>La base de données ne répond pas. Veuillez réessayer dans quelques instants.</p>'
       . '</body></html>';
    exit;
}
