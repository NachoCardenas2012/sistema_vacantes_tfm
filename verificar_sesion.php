<?php
// Iniciar la sesión
session_start();

// Establecer el tiempo máximo de inactividad en segundos (por ejemplo, 15 minutos)
$tiempo_inactividad_maximo = 15 * 60;  // 15 minutos en segundos

// Verificar si la sesión del usuario está activa
if (!isset($_SESSION['usuario_id'])) {
    // Si la sesión no está activa, redirigir al login
    header('Location: login.php');
    exit();
}

// Obtener los datos del usuario desde la sesión
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];

// Verificar si la sesión ha expirado por inactividad
if (isset($_SESSION['ultimo_acceso'])) {
    $tiempo_inactividad = time() - $_SESSION['ultimo_acceso'];

    // Si ha pasado más tiempo del permitido, destruir la sesión y redirigir al login
    if ($tiempo_inactividad > $tiempo_inactividad_maximo) {
        // Destruir la sesión por inactividad
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit();
    }
}

// Actualizar la hora del último acceso
$_SESSION['ultimo_acceso'] = time();

// Mostrar el último login del usuario (si lo deseas)
if (isset($_SESSION['ultimo_login'])) {
    $ultimo_login = $_SESSION['ultimo_login'];
    $ultimo_login_formateado = date('d/m/Y H:i:s', $ultimo_login);  // Formato: dd/mm/aaaa hh:mm:ss
    echo "<p>Último acceso: $ultimo_login_formateado</p>";
}

// Actualizar el último login con la hora actual (si lo deseas)
$_SESSION['ultimo_login'] = time();

?>
