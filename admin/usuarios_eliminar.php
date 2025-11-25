<?php
session_start();
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = $_GET['id'];
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

// Eliminar usuario
$sql = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

// Redirigir con mensaje de éxito de eliminación
header("Location: usuarios.php?success=delete");
exit;
?>

