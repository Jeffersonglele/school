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

// Récupération des cycles (primaire/collège)
$stmt = $pdo->prepare("SELECT * FROM cycles");
$stmt->execute();
$cycles = $stmt->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Récupération et validation des données
        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $date_naissance = $_POST['date_naissance'];
        $lieu_naissance = htmlspecialchars(trim($_POST['lieu_naissance']));
        $sexe = $_POST['sexe'];
        $niveau = $_POST['niveau'];
        
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
        $stmt = $pdo->prepare("INSERT INTO eleves (nom, prenom, date_naissance, lieu_naissance, sexe, niveau, parent_tuteur_id, created_at, updated_at) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $nom,
            $prenom,
            $date_naissance,
            $lieu_naissance,
            $sexe,
            $niveau,
            $parent_tuteur_id
        ]);
        
        // Récupération de l'ID de l'élève
        $eleve_id = $pdo->lastInsertId();
        
        // Récupération de l'année scolaire active
        $stmt = $pdo->prepare("SELECT id FROM annee_scolaires WHERE active = 1 LIMIT 1");
        $stmt->execute();
        $annee_scolaire = $stmt->fetch();
        
        if (!$annee_scolaire) {
            // Si aucune année scolaire n'est active, on utilise la dernière année scolaire
            $stmt = $pdo->prepare("SELECT id FROM annee_scolaires ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $annee_scolaire = $stmt->fetch();
            
            if (!$annee_scolaire) {
                throw new Exception("Aucune année scolaire n'a été trouvée dans la base de données");
            }
        }
        
        // Récupération de l'ID de la classe correspondant au niveau
        $stmt = $pdo->prepare("SELECT id FROM classes WHERE nom = ? LIMIT 1");
        $stmt->execute([$niveau]);
        $classe = $stmt->fetch();
        
        if (!$classe) {
            throw new Exception("La classe correspondant au niveau sélectionné n'a pas été trouvée");
        }
        
        // Création de l'inscription
        $stmt = $pdo->prepare("INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, date_inscription, created_at, updated_at) 
                             VALUES (?, ?, ?, CURDATE(), NOW(), NOW())");
        $stmt->execute([
            $eleve_id,
            $classe['id'],
            $annee_scolaire['id']
        ]);
        
        $message = "L'élève a été inscrit avec succès !";
        $message_type = "success";
        
        // Rafraîchir la page pour afficher le nouvel élève
        header("Location: dashboard.php");
        exit();
    } catch(PDOException $e) {
        $message = "Erreur lors de l'inscription : " . $e->getMessage();
        $message_type = "error";
    } catch(Exception $e) {
        $message = $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription d'un élève</title>
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
            max-width: 800px;
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

        .retour {
            color: var(--blanc);
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid var(--blanc);
            border-radius: 5px;
            transition: all 0.3s;
        }

        .retour:hover {
            background: var(--blanc);
            color: var(--couleur-principale);
        }

        .formulaire {
            background: var(--blanc);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .groupe-formulaire {
            margin-bottom: 20px;
        }

        .groupe-formulaire label {
            display: block;
            margin-bottom: 8px;
            color: var(--couleur-secondaire);
            font-weight: 500;
        }

        .groupe-formulaire input,
        .groupe-formulaire select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .groupe-formulaire input:focus,
        .groupe-formulaire select:focus {
            border-color: var(--couleur-principale);
            outline: none;
        }

        .bouton {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, var(--couleur-principale), #2980b9);
            color: var(--blanc);
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .bouton:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .message {
            padding: 15px;
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
            .conteneur {
                padding: 10px;
            }

            .en-tete {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="conteneur">
        <div class="en-tete">
            <div class="bienvenue">
                Inscription d'un nouvel élève
            </div>
            <a href="dashboard.php" class="retour">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>

        <?php if (isset($message)): ?>
            <div class="message message-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="formulaire">
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
                <label for="cycle">Cycle</label>
                <select id="cycle" name="cycle" required onchange="chargerNiveaux()">
                    <option value="">Sélectionnez un cycle</option>
                    <option value="primaire">Primaire</option>
                    <option value="secondaire">Secondaire</option>
                </select>
            </div>

            <div class="groupe-formulaire">
                <label for="niveau">Classe</label>
                <select id="niveau" name="niveau" required>
                    <option value="">Sélectionnez d'abord le cycle</option>
                </select>
            </div>

            <button type="submit" class="bouton">
                <i class="fas fa-save"></i> Enregistrer l'inscription
            </button>
        </form>
    </div>

    <script>
        function chargerNiveaux() {
            const cycle = document.getElementById('cycle').value;
            const niveauSelect = document.getElementById('niveau');
            
            // Réinitialiser le sélecteur
            niveauSelect.innerHTML = '<option value="">Sélectionnez d\'abord le cycle</option>';
            
            if (cycle === 'primaire') {
                const niveauxPrimaire = [
                    {value: 'CI', text: 'Cours d\'Initiation (CI)'},
                    {value: 'CP', text: 'Cours Préparatoire (CP)'},
                    {value: 'CE1', text: 'Cours Élémentaire 1 (CE1)'},
                    {value: 'CE2', text: 'Cours Élémentaire 2 (CE2)'},
                    {value: 'CM1', text: 'Cours Moyen 1 (CM1)'},
                    {value: 'CM2', text: 'Cours Moyen 2 (CM2)'}
                ];
                
                niveauxPrimaire.forEach(niveau => {
                    const option = document.createElement('option');
                    option.value = niveau.value;
                    option.textContent = niveau.text;
                    niveauSelect.appendChild(option);
                });
            } else if (cycle === 'secondaire') {
                const niveauxSecondaire = [
                    {value: '6eme', text: 'Sixième (6ème)'},
                    {value: '5eme', text: 'Cinquième (5ème)'},
                    {value: '4eme', text: 'Quatrième (4ème)'},
                    {value: '3eme', text: 'Troisième (3ème)'},
                    {value: '2nde', text: 'Seconde (2nde)'},
                    {value: '1ere', text: 'Première (1ère)'},
                    {value: 'Terminal', text: 'Terminale'}
                ];
                
                niveauxSecondaire.forEach(niveau => {
                    const option = document.createElement('option');
                    option.value = niveau.value;
                    option.textContent = niveau.text;
                    niveauSelect.appendChild(option);
                });
            }
        }
    </script>
</body>
</html> 