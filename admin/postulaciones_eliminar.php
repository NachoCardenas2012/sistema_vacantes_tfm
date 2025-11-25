<?php
session_start();
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: postulaciones.php");
    exit;
}

$id = $_GET['id'];

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

$stmt = $conn->prepare("DELETE FROM postulaciones WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: postulaciones.php");
exit;
?>
