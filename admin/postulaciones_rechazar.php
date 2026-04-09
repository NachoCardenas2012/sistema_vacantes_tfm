<?php
session_start();

// Validar sesión y rol admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: postulaciones.php?error=ID inválido");
    exit;
}

// Conexión igual que tu archivo
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Actualizar estado
$stmt = $conn->prepare("UPDATE postulaciones SET estado = 'rechazado' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: postulaciones.php?msg=rechazada");
} else {
    header("Location: postulaciones.php?error=" . urlencode($stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>