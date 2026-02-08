<?php
session_start();
require_once 'database.php';

// Vérification de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupère les élèves inscrits pour le parent connecté
try {
    // Requête pour récupérer les élèves inscrits pour l'année active
    $stmt = $pdo->prepare("
        SELECT e.nom, e.prenom, e.date_naissance, c.nom AS classe, a.libelle AS annee_scolaire
        FROM eleves e
        JOIN parent_tuteurs pt ON e.parent_tuteur_id = pt.id
        JOIN inscriptions i ON e.id = i.eleve_id
        JOIN classes c ON i.classe_id = c.id
        JOIN annee_scolaires a ON i.annee_scolaire_id = a.id
        WHERE pt.user_id = ?
        AND a.active = 0
    ");
    $stmt->execute([$_SESSION['utilisateur_id']]);
    $eleves = $stmt->fetchAll();

} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de connexion : " . $e->getMessage();
    header('Location: error.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Enfants Inscrits</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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

        .sous-titre {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .tableau-container {
            background: var(--blanc);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .tableau {
            width: 100%;
            border-collapse: collapse;
        }

        .tableau thead {
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
        }

        .tableau th {
            padding: 15px;
            text-align: left;
            font-weight: 500;
        }

        .tableau td {
            padding: 15px;
            border-bottom: 1px solid var(--gris-clair);
        }

        .tableau tbody tr:hover {
            background-color: var(--fond-clair);
            transition: background-color 0.3s ease;
        }

        .tableau tbody tr:last-child td {
            border-bottom: none;
        }

        .aucun-eleve {
            background: var(--blanc);
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .icone-aucun {
            font-size: 3rem;
            color: var(--gris-fonce);
            margin-bottom: 20px;
        }

        .message-aucun {
            color: var(--couleur-secondaire);
            font-size: 1.2rem;
        }

        .bouton-retour {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .bouton-retour:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        @media (max-width: 768px) {
            .conteneur {
                padding: 10px;
            }

            .en-tete {
                padding: 20px;
            }

            .titre {
                font-size: 1.8rem;
            }

            .tableau {
                display: block;
                overflow-x: auto;
            }

            .tableau th,
            .tableau td {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <div class="en-tete">
            <h1 class="titre">Mes Enfants Inscrits</h1>
            <p class="sous-titre">Liste des enfants inscrits pour l'année scolaire en cours</p>
        </div>

        <?php if (count($eleves) > 0): ?>
            <div class="tableau-container">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Nom</th>
                            <th><i class="fas fa-user"></i> Prénom</th>
                            <th><i class="fas fa-birthday-cake"></i> Date de naissance</th>
                            <th><i class="fas fa-graduation-cap"></i> Classe</th>
                            <th><i class="fas fa-calendar-alt"></i> Année scolaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eleves as $eleve): ?>
                            <tr>
                                <td><?= htmlspecialchars($eleve['nom']) ?></td>
                                <td><?= htmlspecialchars($eleve['prenom']) ?></td>
                                <td><?= date('d/m/Y', strtotime($eleve['date_naissance'])) ?></td>
                                <td><?= htmlspecialchars($eleve['classe']) ?></td>
                                <td><?= htmlspecialchars($eleve['annee_scolaire']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="aucun-eleve">
                <i class="fas fa-user-graduate icone-aucun"></i>
                <p class="message-aucun">Aucun élève inscrit pour cette année scolaire.</p>
            </div>
        <?php endif; ?>

        <a href="dashboard.php" class="bouton-retour">
            <i class="fas fa-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>
</body>
</html>
