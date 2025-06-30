<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=crps_amani;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ajout d’un médecin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom     = $_POST['nom'] ?? '';
    $prenom     = $_POST['prenom'] ?? '';
    $specialiste = $_POST['specialiste'] ?? '';
    $contact   = $_POST['contact'] ?? '';

    if (!empty($nom) && !empty($specialiste)) {
        $stmt = $pdo->prepare("INSERT INTO medecins (nom, prenom, specialiste, contact) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $specialiste, $contact]);
    }
}

// Suppression
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $pdo->prepare("DELETE FROM medecins WHERE id_medecins = ?")->execute([$id]);
}

// Liste des médecins
$medecins = $pdo->query("SELECT * FROM medecins ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Médecins</title>
    <link rel="stylesheet" href="dash.css">
</head>
<body>
<header class="header">
    <h1>👨‍⚕️ Gestion des Médecins</h1>
    <a href="dash.php" class="logout-btn">← Retour au Dashboard</a>
</header>

<main class="container">
    <!-- Ajout d'un médecin -->
    <section class="ajout-form">
        <h2>➕ Ajouter un médecin</h2>
        <form method="post" class="form-ajout">
            <input type="text" name="nom" placeholder="Nom complet" required>
            <input type="text" name="prenom" placeholder="Prenom">
            <input type="text" name="specialiste" placeholder="Spécialiste" required>
            <input  type="int" name="contact" placeholder="Téléphone">
            <button type="submit" name="ajouter" class="btn btn-success">Ajouter</button>
        </form>
    </section>

    <!-- Liste des médecins -->
    <section class="liste">
        <h2>📋 Liste des médecins</h2>
        <?php if (empty($medecins)): ?>
            <p>Aucun médecin enregistré.</p>
        <?php else: ?>
            <table class="rdv-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                         <th>Prenom</th>
                        <th>Spécialiste</th>
                        <th>Téléphone</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($medecins as $med): ?>
                    <tr>
                        <td><?= htmlspecialchars($med['nom']) ?></td>
                        <td><?= htmlspecialchars($med['prenom']) ?></td>
                        <td><?= htmlspecialchars($med['specialiste']) ?></td>
                        <td><?= htmlspecialchars($med['contact']) ?></td>
                        <td>
                            <a href="?supprimer=<?= $med['id_medecins'] ?>" onclick="return confirm('Supprimer ce médecin ?');" class="btn btn-danger">🗑️ Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>&copy; 2025 - Centre médical CRPS</p>
</footer>
</body>
</html>
