<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --couleur-principale: #3498db;
            --couleur-secondaire: #2c3e50;
            --couleur-accent: #e74c3c;
            --fond-clair: #f5f9fc;
            --blanc: #ffffff;
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
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .conteneur-erreur {
            max-width: 600px;
            background: var(--blanc);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .icone-erreur {
            font-size: 4rem;
            color: var(--rouge);
            margin-bottom: 20px;
        }

        .titre-erreur {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: var(--couleur-secondaire);
        }

        .message-erreur {
            margin-bottom: 30px;
            color: var(--couleur-secondaire);
        }

        .bouton-retour {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .bouton-retour:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
    </style>
</head>
<body>
    <div class="conteneur-erreur">
        <i class="fas fa-exclamation-circle icone-erreur"></i>
        <h1 class="titre-erreur">Une erreur est survenue</h1>
        <p class="message-erreur">
            <?php 
            if (isset($_SESSION['error'])) {
                echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']);
            } else {
                echo "Une erreur inattendue s'est produite.";
            }
            ?>
        </p>
        <a href="dashboard.php" class="bouton-retour">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
</body>
</html> 