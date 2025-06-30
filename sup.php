<?php
if (!isset($_GET['id'])) {
    header("Location: patients.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=crps_amani;charset=utf8", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM patients WHERE id_patients = ?");
$stmt->execute([$id]);

header("Location: patients.php");
exit;
