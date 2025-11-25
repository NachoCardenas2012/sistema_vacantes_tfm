<?php
session_start();
include "conexion.php";



$email = trim($_POST['email']);
$password = $_POST['password'];

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?error=Formato de email inválido");
    exit;
}

// Preparar consulta segura
$stmt = $conexion->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Validar que exista el usuario
if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Verificar contraseña
    if (password_verify($password, $row['password'])) {

        // Guardar datos en sesión
        $_SESSION['id'] = $row['id'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['rol'] = $row['rol'];
        
        header("Location: home.php");
        exit;
    }
}

// Si llega aquí, usuario o contraseña incorrectos
header("Location: login.php?error=Credenciales incorrectas");
exit;
