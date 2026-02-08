<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Vérification de l'authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupération de l'ID de la transaction depuis l'URL
$transaction_id = $_GET['id'] ?? null;

if (!$transaction_id) {
    $_SESSION['message'] = "ID de transaction manquant";
    $_SESSION['message_type'] = "error";
    header('Location: dashboard.php');
    exit();
}

try {
    // Récupération de la transaction FedaPay
    $transaction = \FedaPay\Transaction::retrieve($transaction_id);
    
    // Mise à jour du statut dans la base de données
    $stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE transaction_id = ?");
    $stmt->execute([$transaction->status, $transaction_id]);
    
    // Message de succès
    $_SESSION['message'] = "Paiement effectué avec succès !";
    $_SESSION['message_type'] = "success";
    
} catch (Exception $e) {
    error_log("Erreur lors de la vérification du paiement : " . $e->getMessage());
    $_SESSION['message'] = "Erreur lors de la vérification du paiement";
    $_SESSION['message_type'] = "error";
}

// Redirection vers le tableau de bord
header('Location: dashboard.php');
exit();
?> 