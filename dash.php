



<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=crps_amani;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Statistiques globales
$nbPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$nbMedecins = $pdo->query("SELECT COUNT(*) FROM medecins")->fetchColumn();
$nbRdv = $pdo->query("SELECT COUNT(*) FROM rendezvous")->fetchColumn();


// Statistiques par période pour le graphique
$stats = [
    'jour' => $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE DATE(date_soumission) = CURDATE()")->fetchColumn(),
    'semaine' => $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE YEARWEEK(date_rdv, 1) = YEARWEEK(NOW(), 1)")->fetchColumn(),
    'mois'    => $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE MONTH(date_rdv)=MONTH(NOW()) AND YEAR(date_rdv)=YEAR(NOW())")->fetchColumn(),
    'annee'   => $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE YEAR(date_rdv)=YEAR(NOW())")->fetchColumn(),
];

// Gestion actions rendez-vous
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if (isset($_POST['confirmer'])) {
        $pdo->prepare("UPDATE rendezvous SET statut = 'confirmer' WHERE id = ?")->execute([$id]);
    } elseif (isset($_POST['annuler'])) {
        $pdo->prepare("UPDATE rendezvous SET statut = 'annuler' WHERE id = ?")->execute([$id]);
    } elseif (isset($_POST['attente'])) {
        $pdo->prepare("UPDATE rendezvous SET statut = 'en attente' WHERE id = ?")->execute([$id]);
    }
}

// Récupérer rendez-vous selon statut
$rdvsEnAttente = $pdo->query("SELECT * FROM rendezvous WHERE statut = 'en attente' ORDER BY date_rdv DESC")->fetchAll(PDO::FETCH_ASSOC);
$rdvsConfirmes = $pdo->query("SELECT * FROM rendezvous WHERE statut = 'confirmer' ORDER BY date_rdv DESC")->fetchAll(PDO::FETCH_ASSOC);
$rdvsAnnules = $pdo->query("SELECT * FROM rendezvous WHERE statut = 'annuler' ORDER BY date_rdv DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Dashboard Administrateur</title>
    <link rel="stylesheet" href="dash.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
<header class="header">
    <h1>🩺 Tableau de bord Administrateur</h1>
    <a href="deconn.php" class="logout-btn">Déconnexion</a>
</header>

<main class="container">
    <!-- Statistiques globales -->
    <section class="stats">
        <div class="card"><h2>Patients</h2><p><?= $nbPatients ?></p></div>
        <div class="card"><h2>Médecins</h2><p><?= $nbMedecins ?></p></div>
        <div class="card"><h2>Rendez-vous</h2><p><?= $nbRdv ?></p></div>
    </section>

    <!-- Graphique ligne -->
    <section class="graph">
        <h2>📈 Évolution des Rendez-vous</h2>
        <canvas id="rdvChart" height="150"></canvas>
        <script>
            const ctx = document.getElementById('rdvChart').getContext('2d');

            const gradient = ctx.createLinearGradient(0, 0, 0, 150);
            gradient.addColorStop(0, 'rgba(0, 123, 255, 0.7)');
            gradient.addColorStop(1, 'rgba(0, 123, 255, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Aujourd\'hui','Cette semaine', 'Ce mois', 'Cette année'],
                    datasets: [{
                        label: 'Rendez-vous',
                        data: [<?= $stats['jour'] ?>,<?= $stats['semaine'] ?>, <?= $stats['mois'] ?>, <?= $stats['annee'] ?>],
                        fill: true,
                        backgroundColor: gradient,
                        borderColor: '#007BFF',
                        tension: 0.3,
                        pointBackgroundColor: '#007BFF',
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        borderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Statistiques des Rendez-vous',
                            font: { size: 18, weight: 'bold' }
                        },
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        </script>
    </section>

    <!-- Gestion patients, medecins, rdv -->
    <section class="gestion">
        <h2>🛠️ Gestion</h2>
        <div class="buttons">
            <a href="patients.php" class="btn">👥 Patients</a>
            <a href="medecins.php" class="btn">👨‍⚕️ Médecins</a>
    
        </div>
    </section>

    <!-- Rendez-vous en attente -->
    <section class="rdvs-pending">
        <h1> 📅 Rendez-vous</h1>
        <h2>⏳ Rendez-vous en attente</h2>
        <?php if (empty($rdvsEnAttente)): ?>
            <p>Aucun rendez-vous en attente.</p>
        <?php else: ?>
            <table class="rdv-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Symptômes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rdvsEnAttente as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['nom']) ?></td>
                        <td><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                        <td><?= htmlspecialchars($rdv['symptome']) ?></td>
                        <td>
                            <form method="post" class="rdv-actions">
                                <input type="hidden" name="id" value="<?= $rdv['id'] ?>">
                                <button type="submit" name="confirmer" class="btn btn-success">✅ Confirmer</button>
                                <button type="submit" name="annuler" class="btn btn-danger" onclick="return confirm('Confirmer l’annulation ?');">❌ Annuler</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- Rendez-vous confirmés -->
    <section class="rdvs-confirmed">
        <h2>✔️ Rendez-vous confirmés</h2>
        <?php if (empty($rdvsConfirmes)): ?>
            <p>Aucun rendez-vous confirmé.</p>
        <?php else: ?>
            <table class="rdv-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Symptômes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rdvsConfirmes as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['nom']) ?></td>
                        <td><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                        <td><?= htmlspecialchars($rdv['symptome']) ?></td>
                        <td>
                            <form method="post" class="rdv-actions">
                                <input type="hidden" name="id" value="<?= $rdv['id'] ?>">
                                <button type="submit" name="attente" class="btn btn-warning">⏳ Remettre en attente</button>
                                <button type="submit" name="annuler" class="btn btn-danger" onclick="return confirm('Confirmer l’annulation ?');">❌ Annuler</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>

    <!-- Rendez-vous annulés -->
    <section class="rdvs-cancelled">
        <h2>❌ Rendez-vous annulés</h2>
        <?php if (empty($rdvsAnnules)): ?>
            <p>Aucun rendez-vous annulé.</p>
        <?php else: ?>
            <table class="rdv-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Symptômes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rdvsAnnules as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['nom']) ?></td>
                        <td><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                        <td><?= htmlspecialchars($rdv['symptome']) ?></td>
                        <td>
                            <form method="post" class="rdv-actions">
                                <input type="hidden" name="id" value="<?= $rdv['id'] ?>">
                                <button type="submit" name="attente" class="btn btn-warning">⏳ Remettre en attente</button>
                                <button type="submit" name="confirmer" class="btn btn-success">✅ Confirmer</button>
                            </form>
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
