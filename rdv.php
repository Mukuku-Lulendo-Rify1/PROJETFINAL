<?php
session_start();

// Vérifie la session
if (!isset($_SESSION['RÔLE'])) {
    $urlRedirection = urlencode($_SERVER['PHP_SELF']);
    header("Location: conn.php?redir=$urlRedirection");
    exit;
}

$host = 'localhost';
$dbname = 'crps_amani';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Échec de connexion : " . $e->getMessage());
}

$heures_possibles = ['08:00', '08:45', '09:30', '14:00', '14:45', '15:30'];

function prochainRdvDisponible($pdo, $heures_possibles) {
    $date = new DateTime('tomorrow'); // Commencer à chercher à partir de demain

    while (true) {
        $jourSemaine = $date->format('N'); // 1 (lundi) à 7 (dimanche)
        if ($jourSemaine < 6) {
            $date_rdv = $date->format('Y-m-d');
            $stmt = $pdo->prepare("SELECT heure_rdv FROM rendezvous WHERE date_rdv = ?");
            $stmt->execute([$date_rdv]);
            $heures_prises = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $heures_disponibles = array_diff($heures_possibles, $heures_prises);
            $heures_disponibles = array_values($heures_disponibles); // Réindexer

            if (!empty($heures_disponibles)) {
                $heure = $heures_disponibles[array_rand($heures_disponibles)];
                return ['date' => $date_rdv, 'heure' => $heure];
            }
        }
        $date->modify('+1 day');
    }
}


function attribuerMedecin($pdo, $symptome) {
    $mappage = [
        'fièvre' => 'Médecin généraliste',
        'toux' => 'pneumologue',
        'mal de tête' => 'neurologue',
        'maux de ventre' => 'gastro-entérologue',
        'yeux' => 'ophtalmologue',
        'peau' => 'dermatologue'
    ];

    $specialite = 'Médecin généraliste'; // Par défaut
    foreach ($mappage as $motCle => $spec) {
        if (stripos($symptome, $motCle) !== false) {
            $specialite = $spec;
            break;
        }
    }

    $stmt = $pdo->prepare("SELECT id_medecins, nom, specialiste FROM medecins WHERE specialiste = :specialiste LIMIT 1");
    $stmt->execute([':specialiste' => $specialite]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$messageConfirmation = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $sexe = htmlspecialchars($_POST["sexe"]);
    $contact = htmlspecialchars($_POST["contact"]);
    $symptome = htmlspecialchars($_POST["symptome"]);

    $rdv = prochainRdvDisponible($pdo, $heures_possibles);
    $date_rdv = $rdv['date'];
    $heure_rdv = $rdv['heure'];

    $medecin = attribuerMedecin($pdo, $symptome);
    if (!$medecin) {
        die("Aucun médecin disponible pour ce symptôme.");
    }

    try {
        // Insérer dans rendezvous
        $sql_rify = "INSERT INTO rendezvous(nom, prenom, sexe, contact, symptome, date_rdv, heure_rdv,id_medecins) 
                    VALUES (:nom, :prenom, :sexe, :contact, :symptome, :date_rdv, :heure_rdv, :id_medecins)";
        $stmt_rify = $pdo->prepare($sql_rify);
        $stmt_rify->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':sexe' => $sexe,
            ':contact' => $contact,
            ':symptome' => $symptome,
            ':date_rdv' => $date_rdv,
            ':heure_rdv' => $heure_rdv,
            ':id_medecins' => $medecin['id_medecins']
        ]);

         $sql_rml = "INSERT INTO `patients`( `nom`, `prenom`, `sexe`, `contact`, `symptome`, `date_rdv`, `heure_rdv`)
          VALUES ( :nom, :prenom, :sexe, :contact, :symptome, :date_rdv, :heure_rdv)";
        $stmt_rml = $pdo->prepare($sql_rml);
        $stmt_rml->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':sexe' => $sexe,
            ':contact' => $contact,
            ':symptome' => $symptome,
            ':date_rdv' => $date_rdv,
            ':heure_rdv' => $heure_rdv,
        ]);


        $messageConfirmation = "Votre rendez-vous est prévu pour le <strong>$date_rdv à $heure_rdv</strong><br>
        avec le médecin <strong>" . htmlspecialchars($medecin['nom']) . "</strong>, spécialiste en <strong>" . htmlspecialchars($medecin['specialiste']) . "</strong>.<br>
        En attente de confirmation.";
    } catch (PDOException $e) {
        die("Erreur lors de l'enregistrement : " . $e->getMessage());
    }

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Prise de rendez-vous | Hôpital Médica</title>
    <link rel="stylesheet" href="rdv.css">
</head>
<body>
<header>
    <div class="logo">
        <img src="Cross red hospital medical sign vector image on VectorStock.jpg" height="96px">
        <span>H</span>ôpital Médica
    </div>
     <a href="services.php">⬅ Retour aux Services</a>
</header>

<div class="container">
    <?php if (!empty($messageConfirmation)) {
        echo "<div style='text-align:center; padding:10px; color:green;'>$messageConfirmation</div>";
    } ?>

    <p style="text-align:center; font-weight:bold;">
        Connecté en tant que : <?= htmlspecialchars($_SESSION['NOMS']) ?> 
        
         <a href="deconn.php" >Se déconnecter</a>
    </p>
  

    <h1>Prendre rendez-vous</h1>
    <p>Remplissez le formulaire pour réserver votre consultation</p>

    <form method="POST">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required><br><br>

        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required><br><br>

        <label for="sexe">Sexe :</label>
        <select id="sexe" name="sexe" required>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
        </select><br><br>

        <label for="contact">Contact :</label>
        <input type="tel" id="contact" name="contact" required><br><br>

        <label for="symptome">Symptômes :</label>
        <input type="text" id="symptome" name="symptome" required><br><br>

        <input type="submit" value="Confirmer">
    </form>
</div>

<footer>
    <p>&copy; 2025 Centre Médical crps_amani - Tous droits réservés</p>
</footer>
</body>
</html>
