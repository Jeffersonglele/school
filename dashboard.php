<?php
session_start();
require_once 'database.php';

// Vérification de la connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit();
}

// Récupération des informations du parent
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$parent = $stmt->fetch();

// Récupération des élèves du parent avec leur classe actuelle
$stmt = $pdo->prepare("SELECT e.*, c.nom as nom_classe 
                      FROM eleves e 
                      JOIN parent_tuteurs pt ON e.parent_tuteur_id = pt.id 
                      JOIN inscriptions i ON e.id = i.eleve_id 
                      JOIN classes c ON i.classe_id = c.id 
                      WHERE pt.user_id = ? 
                      AND i.annee_scolaire_id = (
                          SELECT id FROM annee_scolaires WHERE active = 1
                      )");
$stmt->execute([$_SESSION['utilisateur_id']]);
$eleves = $stmt->fetchAll();

// Récupération de l'historique des paiements
$stmt = $pdo->prepare("SELECT t.*, e.nom as nom_eleve, e.prenom as prenom_eleve 
                      FROM transactions t 
                      JOIN eleves e ON t.eleve_id = e.id 
                      JOIN parent_tuteurs pt ON e.parent_tuteur_id = pt.id 
                      WHERE pt.user_id = ? 
                      AND t.status = 'completed'
                      ORDER BY t.created_at DESC");
$stmt->execute([$_SESSION['utilisateur_id']]);
$paiements = $stmt->fetchAll();

// Traitement du formulaire d'inscription d'élève
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inscrire_eleve'])) {
    try {
        // Récupération et validation des données
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $date_naissance = $_POST['date_naissance'];
        $lieu_naissance = htmlspecialchars(trim($_POST['lieu_naissance']));
        $classe_id = $_POST['classe_id'];
        $sexe = $_POST['sexe'];

        // Récupération de l'ID du parent tuteur
        $stmt = $pdo->prepare("SELECT id FROM parent_tuteurs WHERE user_id = ?");
        $stmt->execute([$_SESSION['utilisateur_id']]);
        $parent_tuteur = $stmt->fetch();

        if (!$parent_tuteur) {
            // Création d'un parent tuteur si nécessaire
            $stmt = $pdo->prepare("INSERT INTO parent_tuteurs (user_id, nom, prenom, email, telephone, created_at, updated_at) 
                                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $_SESSION['utilisateur_id'],
                $parent['name'],
                '',
                $parent['email'],
                $parent['telephone']
            ]);
            $parent_tuteur_id = $pdo->lastInsertId();
        } else {
            $parent_tuteur_id = $parent_tuteur['id'];
        }

        // Insertion de l'élève
        $stmt = $pdo->prepare("INSERT INTO eleves (nom, prenom, date_naissance, lieu_naissance, sexe, parent_tuteur_id, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $nom,
            $prenom,
            $date_naissance,
            $lieu_naissance,
            $sexe,
            $parent_tuteur_id
        ]);

        // Récupération de l'ID de l'élève
        $eleve_id = $pdo->lastInsertId();

        // Récupération de l'année scolaire active
        $stmt = $pdo->prepare("SELECT id FROM annee_scolaires WHERE active = 1");
        $stmt->execute();
        $annee_scolaire = $stmt->fetch();

        if (!$annee_scolaire) {
            throw new Exception("Aucune année scolaire active n'a été trouvée");
        }

        // Création de l'inscription
        $stmt = $pdo->prepare("INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, date_inscription, created_at, updated_at) 
                             VALUES (?, ?, ?, CURDATE(), NOW(), NOW())");
        $stmt->execute([
            $eleve_id,
            $classe_id,
            $annee_scolaire['id']
        ]);

        $message = "L'élève a été inscrit avec succès !";
        $message_type = "success";

        // Rafraîchir la page pour afficher le nouvel élève
        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        $message = "Erreur lors de l'inscription : " . $e->getMessage();
        $message_type = "error";
    }
}

// Récupération des classes disponibles
$stmt = $pdo->prepare("SELECT * FROM classes ORDER BY nom");
$stmt->execute();
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Portail de Paiement</title>
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
            background: var(--fond-clair);
            color: var(--couleur-secondaire);
            line-height: 1.6;
        }

        .conteneur {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .en-tete {
            background: linear-gradient(135deg, var(--couleur-principale), var(--couleur-secondaire));
            color: var(--blanc);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bienvenue {
            font-size: 1.5rem;
            font-weight: 500;
        }

        .deconnexion {
            color: var(--blanc);
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid var(--blanc);
            border-radius: 5px;
            transition: all 0.3s;
        }

        .deconnexion:hover {
            background: var(--blanc);
            color: var(--couleur-principale);
        }

        .grille {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .carte {
            background: var(--blanc);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .carte h2 {
            color: var(--couleur-secondaire);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--fond-clair);
        }

        .liste-eleves,
        .liste-paiements {
            list-style: none;
        }

        .liste-eleves li,
        .liste-paiements li {
            padding: 10px;
            border-bottom: 1px solid var(--fond-clair);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .liste-eleves li:last-child,
        .liste-paiements li:last-child {
            border-bottom: none;
        }

        .bouton {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .bouton:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .contenu-modal {
            background: var(--blanc);
            width: 90%;
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            position: relative;
        }

        .fermer-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--couleur-secondaire);
        }

        .formulaire-inscription {
            display: grid;
            gap: 15px;
        }

        .groupe-formulaire {
            margin-bottom: 15px;
        }

        .groupe-formulaire label {
            display: block;
            margin-bottom: 5px;
            color: var(--couleur-secondaire);
        }

        .groupe-formulaire input,
        .groupe-formulaire select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
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

        @media (max-width: 768px) {
            .en-tete {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .grille {
                grid-template-columns: 1fr;
            }
        }

        .bouton-inscription {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
            margin: 20px 0;
        }

        .bouton-inscription:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(52, 152, 219, 0.4);
        }

        .bouton-inscription i {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="conteneur">
        <div class="en-tete">
            <div class="bienvenue">
                Bienvenue, <?php echo htmlspecialchars($parent['name']); ?>
            </div>
            <a href="deconnexion.php" class="deconnexion">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grille">
            <div class="carte">
                <h2>Inscrire un nouvel élève</h2>
                <div class="actions">
                    <a href="inscrire_eleve.php" class="bouton-inscription">
                        <i class="fas fa-user-plus"></i> Inscrire un élève
                    </a>
                </div>
            </div>

            <div class="carte">
                <h2>Vos élèves</h2>
                <ul class="liste-eleves">
                    <?php if (empty($eleves)): ?>
                        <li>Aucun élève enregistré</li>
                    <?php else: ?>
                        <?php foreach ($eleves as $eleve): ?>
                            <li>
                                <div>
                                    <strong><?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($eleve['nom_classe']); ?></small>
                                </div>
                                <div class="actions">
                                    <a href="paiement.php?eleve_id=<?php echo $eleve['id']; ?>" class="bouton">
                                        <i class="fas fa-money-bill-wave"></i> Payer
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <div class="actions">
                    <a href="meseleves.php" class="bouton-inscription">
                        <i class="fas fa-users"></i> Voir tous mes élèves
                    </a>
                </div>
            </div>

            <div class="carte">
                <h2>Historique des paiements</h2>
                <ul class="liste-paiements">
                    <?php if (empty($paiements)): ?>
                        <li>Aucun paiement effectué</li>
                    <?php else: ?>
                        <?php foreach ($paiements as $paiement): ?>
                            <li>
                                <div>
                                    <strong><?php echo htmlspecialchars($paiement['prenom_eleve'] . ' ' . $paiement['nom_eleve']); ?></strong>
                                    <br>
                                    <small><?php echo date('d/m/Y', strtotime($paiement['created_at'])); ?></small>
                                </div>
                                <div class="montant">
                                    <?php echo number_format($paiement['amount'], 2, ',', ' '); ?> FCFA
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal pour l'inscription d'élève -->
    <div id="modal-inscription" class="modal">
        <div class="contenu-modal">
            <span class="fermer-modal" onclick="fermerModal()">&times;</span>
            <h2>Inscription d'un nouvel élève</h2>
            <form method="POST" action="" class="formulaire-inscription">
                <input type="hidden" name="inscrire_eleve" value="1">

                <div class="groupe-formulaire">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>

                <div class="groupe-formulaire">
                    <label for="prenom">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>

                <div class="groupe-formulaire">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>

                <div class="groupe-formulaire">
                    <label for="lieu_naissance">Lieu de naissance</label>
                    <input type="text" id="lieu_naissance" name="lieu_naissance" required>
                </div>

                <div class="groupe-formulaire">
                    <label for="sexe">Sexe</label>
                    <select id="sexe" name="sexe" required>
                        <option value="">Sélectionnez le sexe</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                </div>

                <div class="groupe-formulaire">
                    <label for="classe_id">Classe</label>
                    <select id="classe_id" name="classe_id" required>
                        <option value="">Sélectionnez une classe</option>
                        <?php foreach ($classes as $classe): ?>
                            <option value="<?php echo $classe['id']; ?>">
                                <?php echo htmlspecialchars($classe['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="bouton">
                    <i class="fas fa-save"></i> Enregistrer l'inscription
                </button>
            </form>
        </div>
    </div>

    <script>
        function ouvrirModal() {
            document.getElementById('modal-inscription').style.display = 'block';
        }

        function fermerModal() {
            document.getElementById('modal-inscription').style.display = 'none';
        }

        // Fermer la modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('modal-inscription');
            if (event.target == modal) {
                fermerModal();
            }
        }
    </script>
</body>

</html>