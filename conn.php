<?php
session_start();

$pdo = new PDO('mysql:host=localhost;dbname=crps_amani;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user']);
    $password = trim($_POST['mdp']);
    $action = $_POST['action'];

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE NOMS = ? AND mot_de_passe = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['NOMS'] = $user['NOMS'];
            $_SESSION['RÔLE'] = $user['RÔLE'] ?? 'utilisateur';

            if (strtolower($user['RÔLE']) === 'admin') {
                header("Location: dash.php");
            } else {
                header("Location: rdv.php");
            }
            exit;
        } else {
            $erreur = "Identifiants incorrects.";
        }
    } elseif ($action === 'register') {
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE nom = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $erreur = "Nom déjà utilisé.";
        } else {
            $insert = $pdo->prepare("INSERT INTO comptes(nom, mot_de_passe, rôle) VALUES (?, ?, ?)");
            $insert->execute([$username, $password, 'utilisateur']);
            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="conn.css">
</head>
<body>
    <form method="POST">
        <h2>Connectez-vous</h2>

        <?php if ($erreur): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <input type="text" name="user" placeholder="Nom d'utilisateur" required><br>
        <input type="text" name="PRENOM" placeholder="Prénom de l'utilisateur" required><br>
        <input type="password" name="mdp" placeholder="Mot de passe" required><br>

        <button type="submit" name="action" value="login">Se connecter</button>

        <p>Pas encore de compte ? <a href="insc.php">Créer votre compte</a></p>
        <a href="rdv.php">Retour vers la page précédente</a>
    
    </form>
</body>
</html>
