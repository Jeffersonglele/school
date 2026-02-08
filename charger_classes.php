<?php
require_once 'database.php';

// Vérification de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit();
}

// Vérification des paramètres
if (!isset($_GET['cycle']) || !isset($_GET['niveau'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit();
}

$cycle = $_GET['cycle'];
$niveau = $_GET['niveau'];

try {
    // Récupération des classes pour le cycle et niveau sélectionnés
    $stmt = $pdo->prepare("SELECT id, nom FROM classes WHERE cycle = ? AND niveau = ? ORDER BY nom");
    $stmt->execute([$cycle, $niveau]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retour des classes au format JSON
    header('Content-Type: application/json');
    echo json_encode($classes);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de base de données']);
} 