<?php

// ARCHIVO DE DIAGNÓSTICO - Coloca esto temporalmente en procesar_login.php
 
session_start();

include "conexion.php";
 
echo "<h2>Debug del Login</h2>";
 
// 1. Verificar datos recibidos

echo "<h3>1. Datos POST recibidos:</h3>";

echo "Email recibido: " . (isset($_POST['email']) ? $_POST['email'] : 'NO RECIBIDO') . "<br>";

echo "Password recibido: " . (isset($_POST['password']) ? 'SÍ (oculto por seguridad)' : 'NO RECIBIDO') . "<br>";

echo "<hr>";
 
// Validar campos vacíos

if (empty($_POST['email']) || empty($_POST['password'])) {

    echo "<div style='color:red'>ERROR: Campos vacíos detectados</div>";

    exit;

}
 
$email = trim($_POST['email']);

$password = $_POST['password'];
 
echo "<h3>2. Validación de email:</h3>";

// Validar formato de email

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo "<div style='color:red'>ERROR: Formato de email inválido</div>";

    exit;

} else {

    echo "<div style='color:green'>✓ Email válido: $email</div>";

}

echo "<hr>";
 
// Preparar consulta segura

echo "<h3>3. Consultando base de datos:</h3>";

$stmt = $conexion->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE email = ? LIMIT 1");

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();
 
echo "Registros encontrados: " . $result->num_rows . "<br>";
 
// Validar que exista el usuario

if ($result->num_rows === 1) {

    $row = $result->fetch_assoc();

    echo "<div style='color:green'>✓ Usuario encontrado</div>";

    echo "ID: " . $row['id'] . "<br>";

    echo "Nombre: " . $row['nombre'] . "<br>";

    echo "Rol: " . $row['rol'] . "<br>";

    echo "Hash en BD: " . substr($row['password'], 0, 20) . "...<br>";

    echo "<hr>";
 
    // Verificar contraseña

    echo "<h3>4. Verificación de contraseña:</h3>";

    if (password_verify($password, $row['password'])) {

        echo "<div style='color:green; font-weight:bold'>✓ CONTRASEÑA CORRECTA</div><br>";

        // Guardar datos en sesión

        $_SESSION['id'] = $row['id'];

        $_SESSION['nombre'] = $row['nombre'];

        $_SESSION['rol'] = $row['rol'];

        echo "<h3>5. Sesión creada:</h3>";

        echo "Session ID actual: " . session_id() . "<br>";

        echo "Datos en sesión:<br>";

        echo "- ID: " . $_SESSION['id'] . "<br>";

        echo "- Nombre: " . $_SESSION['nombre'] . "<br>";

        echo "- Rol: " . $_SESSION['rol'] . "<br>";

        echo "<hr>";

        echo "<h3>6. Intentando redireccionar...</h3>";

        echo "<a href='home.php'>Ir a home.php manualmente</a><br>";

        echo "<p>Si no se redirige automáticamente, verifica que no haya salida de texto antes de session_start() o header()</p>";

        // Descomentar para redirección real:

        // header("Location: home.php");

        // exit;

    } else {

        echo "<div style='color:red; font-weight:bold'>✗ CONTRASEÑA INCORRECTA</div><br>";

        echo "Password ingresado no coincide con el hash<br>";

    }

} else {

    echo "<div style='color:red'>✗ Usuario no encontrado con ese email</div>";

}
 
echo "<hr><h3>7. Variables de sesión actuales:</h3>";

echo "<pre>";

print_r($_SESSION);

echo "</pre>";

?>
 