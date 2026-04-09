<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

// Recoger datos
$nombre             = trim($_POST['nombre']             ?? '');
$apellido           = trim($_POST['apellido']           ?? '');
$email              = trim($_POST['email']              ?? '');
$password           = trim($_POST['password']           ?? '');
$confirmar_password = trim($_POST['confirmar_password'] ?? '');

// ✅ El rol SIEMPRE es empleado desde registro público
// Solo admin puede asignar otro rol desde el dashboard
$rol = 'empleado';

// ===== VALIDACIONES =====
if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
    header("Location: register.php?error=" . urlencode("Todos los campos son obligatorios"));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: register.php?error=" . urlencode("El email no es válido"));
    exit;
}

if (strlen($password) < 6) {
    header("Location: register.php?error=" . urlencode("La contraseña debe tener mínimo 6 caracteres"));
    exit;
}

if ($password !== $confirmar_password) {
    header("Location: register.php?error=" . urlencode("Las contraseñas no coinciden"));
    exit;
}

// Conexión
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

if ($conn->connect_error) {
    header("Location: register.php?error=" . urlencode("Error de conexión a la base de datos"));
    exit;
}

// Verificar si el email ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: register.php?error=" . urlencode("Ya existe una cuenta con ese email"));
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Encriptar contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insertar usuario
$stmt = $conn->prepare("
    INSERT INTO usuarios (nombre, apellido, email, password, rol, fecha_registro) 
    VALUES (?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sssss", $nombre, $apellido, $email, $password_hash, $rol);

if ($stmt->execute()) {
    header("Location: login.php?success=" . urlencode("Cuenta creada correctamente. ¡Inicia sesión!"));
} else {
    header("Location: register.php?error=" . urlencode("Error al crear la cuenta: " . $stmt->error));
}

$stmt->close();
$conn->close();
exit;
?>