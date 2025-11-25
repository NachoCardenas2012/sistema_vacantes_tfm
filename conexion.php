<?php
$host = "localhost";
$user = "root";
$pass = "root";
$db   = "sistema_vacantes";

$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>


