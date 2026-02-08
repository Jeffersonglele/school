<?php
// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'database.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupération et nettoyage des données
    $name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $telephone = htmlspecialchars(trim($_POST['telephone']), ENT_QUOTES, 'UTF-8');
    $adresse = htmlspecialchars(trim($_POST['adresse']), ENT_QUOTES, 'UTF-8');

    // Validation des données
    if (empty($name) || empty($email) || empty($password) || empty($password_confirm)) {
        $message = 'Veuillez remplir tous les champs obligatoires';
        $message_type = 'error';
    } elseif ($password !== $password_confirm) {
        $message = 'Les mots de passe ne correspondent pas';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Format d\'email invalide';
        $message_type = 'error';
    } else {
        try {
            // Vérification si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Cet email est déjà utilisé';
                $message_type = 'error';
            } else {
                // Hachage du mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertion de l'utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, telephone, adresse, created_at, updated_at) 
                                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                
                // Exécution de la requête avec les valeurs
                $result = $stmt->execute([
                    $name,
                    $email,
                    $hashed_password,
                    $telephone,
                    $adresse
                ]);

                if ($result) {
                    // Récupération de l'ID de l'utilisateur
                    $user_id = $pdo->lastInsertId();
                    
                    // Création de la session
                    $_SESSION['utilisateur_id'] = $user_id;
                    $_SESSION['name'] = $name;
                    
                    // Message de succès
                    $message = 'Inscription réussie ! Redirection vers votre tableau de bord...';
                    $message_type = 'success';
                    
                    // Redirection après 2 secondes
                    header("refresh:2;url=dashboard.php");
                } else {
                    $message = 'Erreur lors de l\'inscription. Veuillez réessayer.';
                    $message_type = 'error';
                }
            }
        } catch(PDOException $e) {
            $message = 'Erreur lors de l\'inscription : ' . $e->getMessage();
            $message_type = 'error';
            error_log("Erreur SQL : " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
            <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Portail de Paiement</title>
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
            max-width: 600px;
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
            animation: fadeIn 0.5s ease-in-out;
        }

        .message-success {
            background-color: #def7ec;
            color: var(--vert);
            border: 1px solid #bcf0da;
        }

        .message-error {
            background-color: #fde8e8;
            color: var(--rouge);
            border: 1px solid #fbd5d5;
        }

        .lien-connexion {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .lien-connexion a {
            color: var(--couleur-principale);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .lien-connexion a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .grille-formulaire {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .grille-formulaire {
                grid-template-columns: 1fr;
            }
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
        <h1>Inscription</h1>
        
        <?php if ($message): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($message_type !== 'success'): ?>
        <form method="POST" action="">
            <div class="grille-formulaire">
                <div class="groupe-formulaire">
                    <label for="name">Nom complet</label>
                    <div class="input-groupe">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" required placeholder="Entrez votre nom complet">
                    </div>
                </div>

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

                <div class="groupe-formulaire">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <div class="input-groupe">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password_confirm" name="password_confirm" required placeholder="Confirmez votre mot de passe">
                    </div>
                </div>

                <div class="groupe-formulaire">
                    <label for="telephone">Téléphone</label>
                    <div class="input-groupe">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="telephone" name="telephone" placeholder="Entrez votre numéro de téléphone">
                    </div>
                </div>

                <div class="groupe-formulaire">
                    <label for="adresse">Adresse</label>
                    <div class="input-groupe">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="adresse" name="adresse" placeholder="Entrez votre adresse">
                    </div>
                </div>
            </div>

            <button type="submit" class="bouton">
                <i class="fas fa-user-plus"></i> S'inscrire
            </button>
        </form>
        <?php endif; ?>

        <div class="lien-connexion">
            <p>Vous avez déjà un compte ? <a href="connexion.php">Se connecter</a></p>
        </div>
    </div>
        </body>
</html>