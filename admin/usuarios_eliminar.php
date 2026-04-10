<?php
session_start();
ob_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    ob_end_clean();
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    ob_end_clean();
    die("Error: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    ob_end_clean();
    header("Location: usuarios.php?error=ID inválido");
    exit;
}

if ($id === intval($_SESSION['id'])) {
    ob_end_clean();
    header("Location: usuarios.php?error=No puedes eliminarte a ti mismo");
    exit;
}

$stmt = $conn->prepare("SELECT id, nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario) {
    ob_end_clean();
    header("Location: usuarios.php?error=Usuario no encontrado");
    exit;
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    ob_end_clean();
    header("Location: usuarios.php?success=Usuario eliminado correctamente");
    exit;
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    ob_end_clean();
    header("Location: usuarios.php?error=Error al eliminar: " . urlencode($error));
    exit;
}
?>