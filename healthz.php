<?php
// Point de contrôle pour Render / Docker.
//   /healthz.php          -> 200 si PHP répond (ne dépend pas de la base)
//   /healthz.php?db=1     -> 200 si la base répond aussi, sinon 503
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!isset($_GET['db'])) {
    echo json_encode(['status' => 'ok']);
    exit;
}

// database.php répond déjà 503 + log détaillé si la connexion échoue.
require_once __DIR__ . '/database.php';
try {
    $pdo->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'db' => 'ok']);
} catch (PDOException $e) {
    error_log('[school] healthz : requête de test échouée : ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['status' => 'error', 'db' => 'unreachable']);
}
