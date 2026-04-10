<?php
session_start();

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8");

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

// ===== VALIDAR CAMPOS VACÍOS =====
if ($email === '' || $password === '') {
    header("Location: login.php?error=Todos los campos son obligatorios");
    exit;
}

// ===== VALIDAR FORMATO EMAIL =====
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?error=El formato del email no es válido");
    exit;
}

// ===== BUSCAR USUARIO =====
$stmt = $conn->prepare("
    SELECT id, nombre, apellido, email, password, rol 
    FROM usuarios 
    WHERE email = ?
");

if (!$stmt) {
    header("Location: login.php?error=Error interno del servidor");
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result  = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
$conn->close();

// ===== VERIFICAR CONTRASEÑA =====
if ($usuario && password_verify($password, $usuario['password'])) {

    // ✅ Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    // ✅ Guardar todos los datos en sesión
    $_SESSION['id']       = $usuario['id'];
    $_SESSION['nombre']   = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['email']    = $usuario['email'];
    $_SESSION['rol']      = $usuario['rol'];

    // ✅ Redirigir según rol
    if ($usuario['rol'] === 'admin') {
        header("Location: home.php");
    } else {
        header("Location: home.php");
    }
    exit;

} else {
    header("Location: login.php?error=Correo o contraseña incorrectos");
    exit;
}
?>