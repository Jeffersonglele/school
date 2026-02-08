<?php
session_start();
require_once 'database.php';

$message = '';
$message_type = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: connexion.php');
    exit();
}

// Vérifier si le token est valide et non expiré
try {
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND created_at > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        $message = "Le lien de réinitialisation est invalide ou a expiré.";
        $message_type = "error";
    }
} catch(PDOException $e) {
    $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
    $message_type = "error";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $reset) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {
        $message = "Veuillez remplir tous les champs";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Les mots de passe ne correspondent pas";
        $message_type = "error";
    } elseif (strlen($password) < 8) {
        $message = "Le mot de passe doit contenir au moins 8 caractères";
        $message_type = "error";
    } else {
        try {
            // Hasher le nouveau mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashed_password, $reset['email']]);

            // Supprimer le token utilisé
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);

            $message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
            $message_type = "success";
        } catch(PDOException $e) {
            $message = "Une erreur est survenue lors de la réinitialisation du mot de passe.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--fond-clair), #e0e6f0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .conteneur {
            background: var(--blanc);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        .en-tete {
            text-align: center;
            margin-bottom: 30px;
        }

        .en-tete h1 {
            color: var(--couleur-secondaire);
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .en-tete p {
            color: var(--couleur-secondaire);
            opacity: 0.8;
        }

        .formulaire {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .groupe-formulaire {
            position: relative;
        }

        .groupe-formulaire i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--couleur-principale);
        }

        .groupe-formulaire input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .groupe-formulaire input:focus {
            border-color: var(--couleur-principale);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .bouton {
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .bouton:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .message-success {
            background-color: #d4edda;
            color: #155724;
        }

        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .lien-connexion {
            text-align: center;
            margin-top: 20px;
        }

        .lien-connexion a {
            color: var(--couleur-principale);
            text-decoration: none;
            transition: all 0.3s;
        }

        .lien-connexion a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .conteneur {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <div class="en-tete">
            <h1><i class="fas fa-key"></i> Nouveau mot de passe</h1>
            <p>Entrez votre nouveau mot de passe</p>
        </div>

        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
                <?php if ($message_type == 'success'): ?>
                    <p><a href="connexion.php">Cliquez ici pour vous connecter</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($reset): ?>
            <form method="POST" action="" class="formulaire">
                <div class="groupe-formulaire">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Nouveau mot de passe" required>
                </div>

                <div class="groupe-formulaire">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
                </div>

                <button type="submit" class="bouton">
                    <i class="fas fa-save"></i> Enregistrer le nouveau mot de passe
                </button>
            </form>
        <?php endif; ?>

        <div class="lien-connexion">
            <a href="connexion.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
        </div>
    </div>
</body>
</html> 