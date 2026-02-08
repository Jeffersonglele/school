<?php
session_start();
require_once 'database.php';
require_once 'vendor/autoload.php';
require_once 'config.php';

// Configuration de FedaPay avec gestion d'erreurs
try {
    // Log de la configuration initiale
    error_log("Début de la configuration FedaPay");
    error_log("Version de PHP: " . PHP_VERSION);
    error_log("Extensions chargées: " . implode(", ", get_loaded_extensions()));

    // Vérification de la clé API
    if (empty(FEDAPAY_API_KEY)) {
        error_log("ERREUR: Clé API vide");
        throw new Exception("La clé API FedaPay n'est pas configurée");
    }

    // Vérification du format de la clé API
    if (!preg_match('/^sk_(sandbox|live)_/', FEDAPAY_API_KEY)) {
        error_log("ERREUR: Format de clé API invalide - Clé reçue: " . substr(FEDAPAY_API_KEY, 0, 15) . "...");
        throw new Exception("Format de clé API invalide. La clé doit commencer par 'sk_sandbox_' ou 'sk_live_'");
    }

    // Configuration de base
    error_log("Configuration de FedaPay avec:");
    error_log("- Clé API: " . substr(FEDAPAY_API_KEY, 0, 15) . "...");
    error_log("- Environnement: " . FEDAPAY_ENVIRONMENT);
    error_log("- URL du site: " . SITE_URL);

    // Configuration de FedaPay selon l'exemple
    \FedaPay\FedaPay::setApiKey(FEDAPAY_API_KEY);
    \FedaPay\FedaPay::setEnvironment(FEDAPAY_ENVIRONMENT);

    // Test de connexion avec une requête simple
    try {
        error_log("Tentative de connexion à l'API FedaPay...");
        
        // Test avec une requête simple de création de transaction
        $test_transaction = \FedaPay\Transaction::create([
            "description" => "Test de connexion API",
            "amount" => 100,
            "currency" => ["iso" => "XOF"],
            "callback_url" => SITE_URL . "/callback.php",
            "customer" => [
                "firstname" => "Test",
                "lastname" => "User",
                "email" => "test@example.com",
                "phone_number" => [
                    "number" => "+22900000000",
                    "country" => "bj"
                ]
            ]
        ]);

        error_log("Test de connexion FedaPay réussi");
        error_log("Configuration actuelle: " . json_encode([
            'api_key_prefix' => substr(FEDAPAY_API_KEY, 0, 15) . '...',
            'environment' => FEDAPAY_ENVIRONMENT,
            'site_url' => SITE_URL,
            'php_version' => PHP_VERSION,
            'time' => date('Y-m-d H:i:s')
        ]));
    } catch (Exception $e) {
        error_log("ERREUR de test de connexion FedaPay: " . $e->getMessage());
        error_log("Trace complète: " . $e->getTraceAsString());
        error_log("Configuration au moment de l'erreur: " . json_encode([
            'api_key_prefix' => substr(FEDAPAY_API_KEY, 0, 15) . '...',
            'environment' => FEDAPAY_ENVIRONMENT,
            'site_url' => SITE_URL,
            'php_version' => PHP_VERSION,
            'time' => date('Y-m-d H:i:s'),
            'error_type' => get_class($e),
            'error_code' => $e->getCode(),
            'json_last_error' => json_last_error(),
            'json_last_error_msg' => json_last_error_msg()
        ]));
        
        // Vérification spécifique de l'erreur d'authentification
        if (strpos($e->getMessage(), 'authentication') !== false || 
            strpos($e->getMessage(), '401') !== false || 
            strpos($e->getMessage(), '403') !== false) {
            error_log("ERREUR D'AUTHENTIFICATION détectée");
            throw new Exception("Clé API FedaPay invalide ou expirée. Veuillez vérifier votre clé API dans le tableau de bord FedaPay.");
        }
        
        // Vérification spécifique de l'erreur 500
        if (strpos($e->getMessage(), 'HTTP response code was 500') !== false) {
            error_log("ERREUR 500 détectée");
            throw new Exception("Erreur serveur FedaPay. Veuillez réessayer plus tard ou contacter le support FedaPay.");
        }
        
        throw new Exception("Impossible de se connecter à l'API FedaPay. Détails : " . $e->getMessage());
    }
} catch (Exception $e) {
    error_log("ERREUR FATALE de configuration FedaPay: " . $e->getMessage());
    error_log("Trace complète: " . $e->getTraceAsString());
    error_log("Configuration finale: " . json_encode([
        'api_key_prefix' => substr(FEDAPAY_API_KEY, 0, 15) . '...',
        'environment' => FEDAPAY_ENVIRONMENT,
        'site_url' => SITE_URL,
        'php_version' => PHP_VERSION,
        'time' => date('Y-m-d H:i:s'),
        'error_type' => get_class($e),
        'error_code' => $e->getCode(),
        'json_last_error' => json_last_error(),
        'json_last_error_msg' => json_last_error_msg()
    ]));
    $_SESSION['error'] = "Erreur de configuration du système de paiement : " . $e->getMessage() . ". Veuillez contacter l'administrateur.";
    header('Location: error.php');
    exit();
}

// Vérification de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupération des informations du dernier élève inscrit
$stmt = $pdo->prepare("SELECT e.*, pt.nom as parent_nom, pt.prenom as parent_prenom, u.email, u.telephone
                      FROM eleves e 
                      JOIN parent_tuteurs pt ON e.parent_tuteur_id = pt.id 
                      JOIN users u ON pt.user_id = u.id
                      WHERE pt.user_id = ? 
                      ORDER BY e.created_at DESC LIMIT 1");
$stmt->execute([$_SESSION['utilisateur_id']]);
$eleve = $stmt->fetch();

if (!$eleve) {
    header('Location: dashboard.php');
    exit();
}

// Récupération de la liste des élèves du parent/tuteur
$stmt_eleves = $pdo->prepare("SELECT e.*, c.nom as classe_nom 
                             FROM eleves e 
                             JOIN parent_tuteurs pt ON e.parent_tuteur_id = pt.id 
                             JOIN classes c ON e.niveau = c.nom
                             WHERE pt.user_id = ? 
                             ORDER BY e.created_at DESC");
$stmt_eleves->execute([$_SESSION['utilisateur_id']]);
$liste_eleves = $stmt_eleves->fetchAll();

// Définition des montants par niveau
$montants = [
    'CI' => 85000,
    'CP' => 85000,
    'CE1' => 85000,
    'CE2' => 85000,
    'CM1' => 85000,
    'CM2' => 95000,
    '6eme' => 110000,
    '5eme' => 110000,
    '4eme' => 110000,
    '3eme' => 120000,
    '2nde' => 130000,
    '1ere' => 130000,
    'Terminal' => 150000
];

// Récupération du montant selon le niveau
$montant_total = $montants[$eleve['niveau']];
$montant_tranche = ceil($montant_total / 3);

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Vérification des données POST
        if (empty($_POST['mode_paiement']) || empty($_POST['type_paiement'])) {
            throw new Exception("Données de formulaire manquantes");
        }

        $mode_paiement = $_POST['mode_paiement'];
        $type_paiement = $_POST['type_paiement'];
        
        if ($type_paiement == 'tranche') {
            $montant = $montant_tranche;
        } else {
            $montant = $montant_total;
        }

        // Création de la transaction FedaPay
        $transaction = \FedaPay\Transaction::create([
            "description" => "Frais de scolarité - " . htmlspecialchars($eleve['prenom']) . ' ' . htmlspecialchars($eleve['nom']),
            "amount" => (int)$montant,
            "currency" => ["iso" => "XOF"],
            "callback_url" => SITE_URL . "/success.php",
            "cancel_url" => SITE_URL . "/paiement.php",
            "customer" => [
                "firstname" => htmlspecialchars($eleve['prenom']),
                "lastname" => htmlspecialchars($eleve['nom']),
                "email" => filter_var($eleve['email'], FILTER_SANITIZE_EMAIL),
                "phone_number" => [
                    "number" => preg_replace('/[^0-9+]/', '', $eleve['telephone']),
                    "country" => "bj"
                ]
            ]
        ]);

        // Enregistrement de la transaction dans la base de données
        $stmt = $pdo->prepare("INSERT INTO transactions (eleve_id, montant, type_paiement, mode_paiement, transaction_id, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $eleve['id'],
            $montant,
            $type_paiement,
            $mode_paiement,
            $transaction->id
        ]);

        // Redirection vers la page de paiement FedaPay
        header('Location: ' . $transaction->generateToken());
        exit();
    } catch(Exception $e) {
        error_log("Erreur lors du paiement : " . $e->getMessage());
        $message = "Erreur lors du paiement : " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement des frais de scolarité</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --couleur-principale: #3498db;
            --couleur-secondaire: #2c3e50;
            --couleur-accent: #e74c3c;
            --fond-clair: #f5f9fc;
            --blanc: #ffffff;
            --vert: #2ecc71;
            --rouge: #e74c3c;
            --gris-clair: #ecf0f1;
            --gris-fonce: #bdc3c7;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--fond-clair), #e0e6f0);
            color: var(--couleur-secondaire);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .conteneur {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: var(--blanc);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .en-tete {
            background: linear-gradient(135deg, var(--couleur-principale), var(--couleur-secondaire));
            color: var(--blanc);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .en-tete::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>') center/cover;
            opacity: 0.1;
        }

        .titre {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .info-eleve {
            background: var(--blanc);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--gris-clair);
            transition: transform 0.3s ease;
        }

        .info-eleve:hover {
            transform: translateY(-5px);
        }

        .info-eleve h2 {
            color: var(--couleur-secondaire);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--couleur-principale);
        }

        .info-eleve p {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }

        .info-eleve p i {
            margin-right: 10px;
            color: var(--couleur-principale);
        }

        .montant {
            background: linear-gradient(135deg, var(--vert), #27ae60);
            color: var(--blanc);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.2);
        }

        .montant h3 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .info-tranche {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .type-paiement {
            margin-bottom: 30px;
        }

        .type-paiement h3 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--couleur-secondaire);
            font-size: 1.5rem;
        }

        .options-type {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .option-type {
            background: var(--blanc);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid var(--gris-clair);
            position: relative;
            overflow: hidden;
        }

        .option-type:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: var(--couleur-principale);
        }

        .option-type input[type="radio"]:checked + label {
            background: var(--couleur-principale);
            color: var(--blanc);
            border-color: var(--couleur-principale);
        }

        .option-type i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--couleur-principale);
            transition: all 0.3s;
        }

        .option-type:hover i {
            transform: scale(1.1);
        }

        .option-type h4 {
            margin: 15px 0;
            font-size: 1.3rem;
        }

        .option-type p {
            font-size: 1.2rem;
            font-weight: 500;
        }

        .options-paiement {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }

        .option-paiement {
            background: var(--blanc);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .option-paiement:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .option-paiement.total {
            border-color: var(--vert);
        }

        .option-paiement.tranche {
            border-color: var(--couleur-principale);
        }

        .option-paiement h3 {
            color: var(--couleur-secondaire);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .option-paiement.total h3 i {
            color: var(--vert);
        }

        .option-paiement.tranche h3 i {
            color: var(--couleur-principale);
        }

        .montant-option {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--couleur-secondaire);
            margin: 20px 0;
            text-align: center;
        }

        .avantages {
            background: var(--fond-clair);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            line-height: 1.8;
        }

        .avantages i {
            color: var(--couleur-principale);
            margin-right: 8px;
        }

        .fedapay-button {
            width: 100% !important;
            padding: 15px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease !important;
        }

        .fedapay-button.total {
            background: linear-gradient(135deg, var(--vert), #27ae60) !important;
        }

        .fedapay-button.tranche {
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9) !important;
        }

        .fedapay-button:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        }

        .button-class {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 20px 30px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-align: center;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            position: relative;
            overflow: hidden;
        }

        .button-class::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        .button-class:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .button-class:hover::before {
            left: 100%;
        }

        .button-class i {
            margin-right: 10px;
            font-size: 1.4rem;
        }

        /* Animation du bouton */
        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .button-class {
            animation: pulse 2s infinite;
        }

        /* Style pour le texte du bouton */
        .button-class span {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Style pour les appareils mobiles */
        @media (max-width: 768px) {
            .conteneur {
                padding: 15px;
            }

            .options-paiement {
                grid-template-columns: 1fr;
            }

            .titre {
                font-size: 1.8rem;
            }

            .button-class {
                padding: 15px 20px;
                font-size: 1.1rem;
            }
            
            .button-class i {
                font-size: 1.2rem;
            }
        }

        /* Animation pour les messages */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message {
            animation: fadeIn 0.5s ease-out;
        }

        .section-eleves {
            margin-bottom: 40px;
        }

        .section-eleves h2 {
            color: var(--couleur-secondaire);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--couleur-principale);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-eleves h2 i {
            color: var(--couleur-principale);
        }

        .liste-eleves {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .carte-eleve {
            background: var(--blanc);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid var(--gris-clair);
        }

        .carte-eleve:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .info-eleve-carte {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .photo-eleve {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--gris-clair);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .photo-eleve img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-eleve i {
            font-size: 2.5rem;
            color: var(--couleur-principale);
        }

        .details-eleve {
            flex: 1;
        }

        .details-eleve h3 {
            margin-bottom: 10px;
            color: var(--couleur-secondaire);
        }

        .details-eleve p {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gris-fonce);
        }

        .details-eleve p i {
            color: var(--couleur-principale);
        }

        .actions-eleve {
            text-align: right;
        }

        .bouton-paiement {
            display: inline-block;
            padding: 8px 15px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .bouton-paiement:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(52, 152, 219, 0.3);
        }

        .bouton-paiement i {
            margin-right: 5px;
        }

        .aucun-eleve {
            text-align: center;
            padding: 40px;
            background: var(--blanc);
            border-radius: 15px;
            border: 2px dashed var(--gris-clair);
        }

        .aucun-eleve i {
            font-size: 3rem;
            color: var(--gris-fonce);
            margin-bottom: 15px;
        }

        .aucun-eleve p {
            color: var(--gris-fonce);
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .info-eleve-carte {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .details-eleve p {
                justify-content: center;
            }
        }

        .sandbox-notice {
            font-size: 0.9rem;
            color: var(--blanc);
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <div class="en-tete">
            <h1 class="titre">Paiement des frais de scolarité</h1>
            <?php if (FEDAPAY_ENVIRONMENT == 'sandbox'): ?>
                <p class="sandbox-notice">Soyez la Bienvenue sur le site de paiement en ligne 😉</p>
            <?php endif; ?>
        </div>

        <?php if (isset($message)): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="info-eleve">
            <h2><i class="fas fa-user-graduate"></i> Informations de l'élève</h2>
            <p><i class="fas fa-user"></i> <strong>Nom :</strong> <?php echo htmlspecialchars($eleve['nom']); ?></p>
            <p><i class="fas fa-user"></i> <strong>Prénom :</strong> <?php echo htmlspecialchars($eleve['prenom']); ?></p>
            <p><i class="fas fa-graduation-cap"></i> <strong>Classe :</strong> <?php echo htmlspecialchars($eleve['niveau']); ?></p>
            <p><i class="fas fa-user-friends"></i> <strong>Parent/Tuteur :</strong> <?php echo htmlspecialchars($eleve['parent_nom'] . ' ' . $eleve['parent_prenom']); ?></p>
        </div>

        <div class="montant">
            <h3><i class="fas fa-money-bill-wave"></i> Montant total : <?php echo number_format($montant_total, 0, ',', ' '); ?> FCFA</h3>
            <p class="info-tranche"><i class="fas fa-calendar-alt"></i> Paiement en 3 tranches : <?php echo number_format($montant_tranche, 0, ',', ' '); ?> FCFA par tranche</p>
        </div>

        <div class="options-paiement">
            <div class="option-paiement total">
                <h3><i class="fas fa-check-circle"></i> Paiement en totalité</h3>
                <p class="montant-option"><?php echo number_format($montant_total, 0, ',', ' '); ?> FCFA</p>
                <p class="avantages">
                    <i class="fas fa-star"></i> Paiement unique<br>
                    <i class="fas fa-star"></i> Pas de suivi mensuel<br>
                    <i class="fas fa-star"></i> Simplification administrative
                </p>
                <form action="paiement.php" method="POST">
                    <input type="hidden" name="mode_paiement" value="carte">
                    <input type="hidden" name="type_paiement" value="total">
                    <script
                        src="https://cdn.fedapay.com/checkout.js?v=1.1.7"
                        data-public-key="pk_sandbox_OahYcbUvoQuVpYiqU8E1yqjC"
                        data-button-text="<i class='fas fa-lock'></i> Payer en totalité"
                        data-button-class="fedapay-button total"
                        data-transaction-amount="<?php echo $montant_total; ?>"
                        data-transaction-description="Frais de scolarité - <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>"
                        data-currency-iso="XOF">
                    </script>
                </form>
            </div>

            <div class="option-paiement tranche">
                <h3><i class="fas fa-calendar-check"></i> Paiement par tranches</h3>
                <p class="montant-option"><?php echo number_format($montant_tranche, 0, ',', ' '); ?> FCFA par tranche</p>
                <p class="avantages">
                    <i class="fas fa-star"></i> 3 paiements échelonnés<br>
                    <i class="fas fa-star"></i> Plus de flexibilité<br>
                    <i class="fas fa-star"></i> Suivi des paiements en ligne
                </p>
                <form action="paiement.php" method="POST">
                    <input type="hidden" name="mode_paiement" value="carte">
                    <input type="hidden" name="type_paiement" value="tranche">
                    <script
                        src="https://cdn.fedapay.com/checkout.js?v=1.1.7"
                        data-public-key="pk_sandbox_OahYcbUvoQuVpYiqU8E1yqjC"
                        data-button-text="<i class='fas fa-lock'></i> Payer la première tranche"
                        data-button-class="fedapay-button tranche"
                        data-transaction-amount="<?php echo $montant_tranche; ?>"
                        data-transaction-description="Frais de scolarité (1ère tranche) - <?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?>"
                        data-currency-iso="XOF">
                    </script>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 