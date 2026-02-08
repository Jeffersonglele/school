<?php
session_start();
require_once 'database.php';

$message = '';
$message_type = ''; // Pour le style du message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'Veuillez remplir tous les champs';
        $message_type = 'error';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['utilisateur_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] == 'admin') {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $message = 'Email ou mot de passe incorrect';
                $message_type = 'error';
            }
        } catch(PDOException $e) {
            $message = 'Erreur de connexion : ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Portail de Paiement</title>
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
            color: var(--couleur-secondaire);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .conteneur-formulaire {
            background-color: var(--blanc);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .conteneur-formulaire::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--couleur-principale), var(--couleur-secondaire));
        }

        h1 {
            text-align: center;
            color: var(--couleur-secondaire);
            margin-bottom: 30px;
            font-size: 2rem;
            position: relative;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background-color: var(--couleur-principale);
        }

        .groupe-formulaire {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--couleur-secondaire);
            font-weight: 500;
        }

        .input-groupe {
            position: relative;
        }

        .input-groupe i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        input:focus {
            border-color: var(--couleur-principale);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .bouton {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .bouton:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .bouton:active {
            transform: translateY(0);
        }

        .message {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
            font-weight: 500;
        }

        .message-error {
            background-color: #fde8e8;
            color: var(--rouge);
            border: 1px solid #fbd5d5;
        }

        .message-success {
            background-color: #def7ec;
            color: var(--vert);
            border: 1px solid #bcf0da;
        }

        .lien-inscription {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .lien-inscription a {
            color: var(--couleur-principale);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .lien-inscription a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .conteneur-formulaire {
                padding: 30px 20px;
            }

            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="conteneur-formulaire">
        <h1>Connexion</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="groupe-formulaire">
                <label for="email">Email</label>
                <div class="input-groupe">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" required placeholder="Entrez votre email">
                </div>
            </div>

            <div class="groupe-formulaire">
                <label for="password">Mot de passe</label>
                <div class="input-groupe">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" required placeholder="Entrez votre mot de passe">
                </div>
            </div>

            <button type="submit" class="bouton">
                <i class="fas fa-sign-in-alt"></i> Se connecter
            </button>
        </form>

        <div class="lien-inscription">
            <p>Vous n'avez pas de compte ? <a href="inscription.php">S'inscrire</a></p>
        </div>
    </div>
</body>
</html>