<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$id = intval($_GET['id'] ?? 0);

// Validaciones
if ($id <= 0) {
    header("Location: usuarios.php?error=ID inválido");
    exit;
}

if ($id === intval($_SESSION['id'])) {
    header("Location: usuarios.php?error=No puedes eliminarte a ti mismo");
    exit;
}

// Verificar que el usuario existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: usuarios.php?error=Usuario no encontrado");
    exit;
}
$stmt->close();

// Eliminar el usuario
$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: usuarios.php?success=delete");
    exit;
} else {
    $error = urlencode($stmt->error);
    $stmt->close();
    $conn->close();
    header("Location: usuarios.php?error=Error al eliminar");
    exit;
}
?>