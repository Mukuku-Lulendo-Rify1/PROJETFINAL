<?php
session_start();

// Vérifie la session
if (!isset($_SESSION['rôle'])) {
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
    $date = new DateTime();

    while (true) {
        $jourSemaine = $date->format('N'); // 1 (lundi) à 7 (dimanche)
        if ($jourSemaine < 6) {
            $date_rdv = $date->format('Y-m-d');
            $stmt = $pdo->prepare("SELECT heure_rdv FROM rendezvous WHERE date_rdv = ?");
            $stmt->execute([$date_rdv]);
            $heures_prises = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($heures_possibles as $heure) {
                if (!in_array($heure, $heures_prises)) {
                    return ['date' => $date_rdv, 'heure' => $heure];
                }
            }
        }
        $date->modify('+1 day');
    }
}

function attribuerMedecin($pdo, $symptome) {
    $mappage = [
        'fièvre' => 'généraliste',
        'toux' => 'pneumologue',
        'mal de tête' => 'neurologue',
        'maux de ventre' => 'gastro-entérologue',
        'yeux' => 'ophtalmologue',
        'peau' => 'dermatologue'
    ];

    $specialite = 'généraliste'; // Par défaut
    foreach ($mappage as $motCle => $spec) {
        if (stripos($symptome, $motCle) !== false) {
            $specialite = $spec;
            break;
        }
    }

    $stmt = $pdo->prepare("SELECT nom FROM medecins WHERE specialiste = :specialiste LIMIT 1");
    $stmt->execute([':specialiste' => $specialite]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$messageConfirmation = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $sexe = htmlspecialchars($_POST["sexe"]);
    $contact = htmlspecialchars($_POST["contact"]);
    $specialiste = htmlspecialchars($_POST["specialiste"]);
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
        $sql_rml = "INSERT INTO rendezvous(nom, prenom, sexe, contact, symptome, date_rdv, heure_rdv, medecin_id) 
                    VALUES (:nom, :prenom, :sexe, :contact, :symptome, :date_rdv, :heure_rdv, :medecin_id)";
        $stmt_rml = $pdo->prepare($sql_rml);
        $stmt_rml->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':sexe' => $sexe,
            ':contact' => $contact,
            ':symptome' => $symptome,
            ':date_rdv' => $date_rdv,
            ':heure_rdv' => $heure_rdv,
            ':medecin_id' => $medecin['id']
        ]);

        $messageConfirmation = "Votre rendez-vous est prévu pour le <strong>$date_rdv à $heure_rdv</strong> avec le médecin <strong>" . $medecin['nom'] . "</strong>. En attente de confirmation.";
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
    <a href="services.php">Retour</a>
</header>

<div class="container">
    <?php if (!empty($messageConfirmation)) {
        echo "<div style='text-align:center; padding:10px; color:green;'>$messageConfirmation</div>";
    } ?>

    <p style="text-align:center; font-weight:bold;">
        Connecté en tant que : <?= htmlspecialchars($_SESSION['nom']) ?> 
        (<?= htmlspecialchars($_SESSION['rôle']) ?>)
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







<?php
session_start();

// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
if (!isset($_SESSION['rôle'])) {
    $urlRedirection = urlencode($_SERVER['PHP_SELF']);
    header("Location: conn.php?redir=$urlRedirection");
    exit;
}

// Connexion à la base de données
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

// Heures disponibles pour chaque jour
$heures_possibles = ['08:00', '08:45', '09:30', '14:00', '14:45', '15:30'];

// Fonction pour trouver la prochaine date et heure disponibles
function prochainRdvDisponible($pdo, $heures_possibles) {
    $date = new DateTime();

    while (true) {
        $jourSemaine = $date->format('N'); // 1 (lundi) à 7 (dimanche)
        if ($jourSemaine < 6) {
            $date_rdv = $date->format('Y-m-d');

            $stmt = $pdo->prepare("SELECT heure_rdv FROM rendezvous WHERE date_rdv = ?");
            $stmt->execute([$date_rdv]);
            $heures_prises = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($heures_possibles as $heure) {
                if (!in_array($heure, $heures_prises)) {
                    return ['date' => $date_rdv, 'heure' => $heure];
                }
            }
        }
        $date->modify('+1 day');
    }
}

// Obtenir prochain créneau
$rdv = prochainRdvDisponible($pdo, $heures_possibles);
$date_rdv = $rdv['date'];
$heure_rdv = $rdv['heure'];

$messageConfirmation = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $prenom = htmlspecialchars($_POST["prenom"]);
    $sexe = htmlspecialchars($_POST["sexe"]);
    $contact = htmlspecialchars($_POST["contact"]);
    $specialiste = htmlspecialchars($_POST["specialiste"]);
    $motif = htmlspecialchars($_POST["motif"]);

    try {
        // Insertion dans la table rendezvous
        $sql_rml = "INSERT INTO rendezvous(nom,prenom,sexe,contact,specialiste,motif,date_rdv,heure_rdv) 
                    VALUES (:nom,:prenom,:sexe,:contact,:specialiste,:motif,:date_rdv,:heure_rdv)";
        $stmt_rml = $pdo->prepare($sql_rml);
        $stmt_rml->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':sexe' => $sexe,
            ':contact' => $contact,
            ':specialiste' => $specialiste,
            ':motif' => $motif,
            ':date_rdv' => $date_rdv,
            ':heure_rdv' => $heure_rdv
        ]);

        // Insertion dans la table patients
        $sql = "INSERT INTO patients(nom,prenom,sexe,contact,date_rdv) 
                VALUES (:nom,:prenom,:sexe,:contact,:date_rdv)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':sexe' => $sexe,
            ':contact' => $contact,
            ':date_rdv' => $date_rdv
        ]);

        $messageConfirmation = "Votre rendez-vous est prévu pour le $date_rdv à $heure_rdv.";
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
    <a href="services.php">Retour</a>
</header>

<div class="container">

    <!-- Message de confirmation -->
    <?php if (!empty($messageConfirmation)) {
        echo "<marquee behavior='scroll' direction='right'>$messageConfirmation</marquee>";
    } ?>

    <!-- Message d'accueil de l'utilisateur -->
    <p style="text-align:center; font-weight:bold;">
        Connecté en tant que : <?= htmlspecialchars($_SESSION['nom']) ?> 
        (<?= htmlspecialchars($_SESSION['rôle']) ?>)
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

        <label for="specialiste">Spécialiste en :</label>
        <select id="specialiste" name="specialiste" required>
            <option value="">Sélectionnez un spécialiste</option>
            <option value="gyneco-obstetrique">Gynécologie-obstétrique</option>
            <option value="echographie">Échographie</option>
            <option value="pediatrie">Pédiatrie</option>
            <option value="medecine">Médecine interne</option>
            <option value="Laboratoire">Laboratoire</option>
            <option value="vaccination">Vaccination</option>
            <option value="cps">CPS</option>
            <option value="cpn">CPN</option>
        </select><br><br>

        <label for="motif">Motif de la consultation :</label>
        <input type="text" id="motif" name="motif" required><br><br>

        <input type="submit" value="Confirmer">
    </form>
</div>

<footer>
    <p>&copy; 2025 Centre Médical crps_amani - Tous droits réservés</p>
</footer>
</body>
</html>
