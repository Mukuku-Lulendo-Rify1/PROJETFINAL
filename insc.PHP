<?php
session_start();

$pdo = new PDO('mysql:host=localhost;dbname=crps_amani;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$erreur = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $noms = trim($_POST['NOMS']);
    $prenoms= trim($_POST['PRENOM']);
    $sexe= trim($_POST['SEXE']);
    $contact= trim($_POST['CONTACT']);
    $password = trim($_POST['mdp']);
    $action = $_POST['action'];

    if ($action === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE NOMS = ?");
        $stmt->execute([$noms]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['MOT_DE_PASSE'])) {
            $_SESSION['NOMS'] = $user['NOMS'];
            $_SESSION['RÔLE'] = $user['RÔLE'];

            if ($user['RÔLE'] === 'admin') {
                header('Location: dash.php');
                exit;
            } else {
                header("Location: accueil.php");
                exit;
            }
        } else {
            $erreur = "Identifiants incorrects.";
        }
    } elseif ($action === 'register') {
        $stmt = $pdo->prepare("SELECT * FROM comptes WHERE NOMS = ?");
        $stmt->execute([$noms]);

        if ($stmt->fetch()) {
            $erreur = "Nom déjà utilisé.";
        } else {
            $insert = $pdo->prepare("INSERT INTO comptes (NOMS,PRENOM,SEXE,CONTACT, MOT_DE_PASSE, RÔLE) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$noms, $prenoms, $sexe, $contact, $password, 'utilisateur']);
            $success = "Compte créé avec succès ! Vous pouvez vous connecter.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte ou se connecter</title>
    <link rel="stylesheet" href="conn.css">
</head>
<body>
    <form method="POST">
        <h2>Créer un compte</h2>

        <?php if ($erreur): ?>
            <div class="erreur"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <input type="text" name="NOMS" placeholder="Nom d'utilisateur" required><br>
        <input type="text" name="PRENOM" placeholder="Prenom de l'utilisateur" required><br>
         <label for="sexe">Sexe :</label>
        <select id="sexe" name="SEXE" required>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
        </select><br><br>
         <label for="contact">Contact :</label>
        <input type="tel" id="contact" name="CONTACT" required><br><br>

        <input type="password" name="mdp" placeholder="Mot de passe" required><br>
        <p>Vous avez déjà un compte ? <a href="conn.php">Se connecter</a></p>
        <p><a href="conn.php">Retour vers la page précédente</a></p>
        <button type="submit" name="action" value="register">Créer un compte</button>
        
    </form>
</body>
</html>
