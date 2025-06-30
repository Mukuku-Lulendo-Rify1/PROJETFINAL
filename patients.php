<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=crps_amani;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Traitement de la recherche
$search = $_GET['search'] ?? '';

if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE nom LIKE ? OR prenom LIKE ? ");
    $stmt->execute(["%$search%", "%$search%"]);
    $patients = $stmt->fetchAll();
} else {
    $patients = $pdo->query("SELECT * FROM patients ")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Patients</title>
    <link rel="stylesheet" href="patients.css">
</head>
<body>

<header>
    <h1>Gestion des Patients</h1>
    <a href="dash.php">⬅ Retour au Dashboard</a>
</header>

<div class="container">
    <!-- Formulaire de recherche -->
    <form method="get" action="">
        <input type="text" name="search" placeholder="Rechercher un patient" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Rechercher</button>
        <?php if ($search): ?>
            <a href="patients.php" style="margin-left:10px;">Réinitialiser</a>
        <?php endif; ?>
    </form>

    <!-- Tableau des patients -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Sexe</th>
                <th>Contact</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($patients) === 0): ?>
            <tr><td colspan="6">Aucun patient trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($patients as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['id_patients']) ?></td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars($p['prenom']) ?></td>
                    <td><?= htmlspecialchars($p['sexe']) ?></td>
                    <td><?= htmlspecialchars($p['contact']) ?></td>
                    <td>
                        <a href="sup.php?id=<?= $p['id_patients'] ?>"
                           class="btn-delete"
                           onclick="return confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce patient ?');">
                           Supprimer
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
