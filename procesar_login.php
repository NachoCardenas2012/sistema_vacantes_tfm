<?php
session_start();

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = "Todos los campos son obligatorios.";
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, password, rol FROM usuarios WHERE email = ?");
if (!$stmt) {
    die("Error SQL: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($usuario = $result->fetch_assoc()) {

    if (password_verify($password, $usuario['password'])) {

        // Login correcto
        $_SESSION['id']  = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];

        header("Location: home.php");
        exit;

    } else {
        $_SESSION['login_error'] = "Correo o contraseña incorrectos.";
    }

} else {
    $_SESSION['login_error'] = "Correo o contraseña incorrectos.";
}

$stmt->close();
$conn->close();

header("Location: login.php");
exit;
