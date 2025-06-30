<?php
session_start();

// Vérification d'authentification
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
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
    die("Erreur de connexion : " . $e->getMessage());
}

// Statistiques
$nbPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$nbMedecins = $pdo->query("SELECT COUNT(*) FROM medecins")->fetchColumn();
$nbRdv = $pdo->query("SELECT COUNT(*) FROM rendezvous")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrateur</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<header>
    <h1>Tableau de Bord - Administrateur</h1>
    <div class="nav-links">
        <a href="logout.php">Déconnexion</a>
    </div>
</header>

<main class="container">
    <section class="stats">
        <div class="card">
            <h2>Patients</h2>
            <p><?= $nbPatients ?></p>
        </div>
        <div class="card">
            <h2>Médecins</h2>
            <p><?= $nbMedecins ?></p>
        </div>
        <div class="card">
            <h2>Rendez-vous</h2>
            <p><?= $nbRdv ?></p>
        </div>
    </section>

    <section class="actions">
        <h2>Gestion</h2>
        <div class="buttons">
            <a href="patients.php" class="btn">Gérer les Patients</a>
            <a href="medecins.php" class="btn">Gérer les Médecins</a>
            <a href="rendezvous.php" class="btn">Gérer les Rendez-vous</a>
        </div>
    </section>
</main>

<footer>
    <p>&copy; 2025 Hôpital Médica - Tous droits réservés</p>
</footer>

</body>
</html>
