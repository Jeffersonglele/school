<?php
session_start();
require_once 'database.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $message = "Veuillez entrer votre adresse email";
        $message_type = "error";
    } else {
        try {
            // Vérifier si l'email existe dans la base de données
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Stocker le token dans la base de données
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);

                // Créer l'instance PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Configuration du serveur SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';  // Utilisez votre serveur SMTP
                    $mail->SMTPAuth = true;
                    $mail->Username = 'votre_email@gmail.com';  // Votre adresse email
                    $mail->Password = 'votre_mot_de_passe_app';  // Votre mot de passe d'application
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';

                    // Destinataires
                    $mail->setFrom('votre_email@gmail.com', 'Gestion Scolaire');
                    $mail->addAddress($email);

                    // Contenu
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
                    
                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe';
                    $mail->Body = "
                        <h2>Réinitialisation de mot de passe</h2>
                        <p>Bonjour,</p>
                        <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                        <p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe :</p>
                        <p><a href='$reset_link'>$reset_link</a></p>
                        <p>Ce lien expirera dans 1 heure.</p>
                        <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.</p>
                        <p>Cordialement,<br>L'équipe de gestion scolaire</p>
                    ";

                    $mail->send();
                    $message = "Un email de réinitialisation a été envoyé à votre adresse email.";
                    $message_type = "success";
                } catch (Exception $e) {
                    $message = "L'email n'a pas pu être envoyé. Erreur : " . $mail->ErrorInfo;
                    $message_type = "error";
                }
            } else {
                $message = "Aucun compte n'est associé à cette adresse email.";
                $message_type = "error";
            }
        } catch(PDOException $e) {
            $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
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
    <title>Réinitialisation du mot de passe</title>
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
            <h1><i class="fas fa-key"></i> Réinitialisation du mot de passe</h1>
            <p>Entrez votre adresse email pour recevoir un lien de réinitialisation</p>
        </div>

        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="formulaire">
            <div class="groupe-formulaire">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Votre adresse email" required>
            </div>

            <button type="submit" class="bouton">
                <i class="fas fa-paper-plane"></i> Envoyer le lien de réinitialisation
            </button>
        </form>

        <div class="lien-connexion">
            <a href="connexion.php"><i class="fas fa-arrow-left"></i> Retour à la connexion</a>
        </div>
    </div>
</body>
</html> 