<?php
session_start();
session_destroy();

// Detecta automáticamente host y puerto
$host = $_SERVER['HTTP_HOST'];
header("Location: http://" . $host . "/sistema_vacantes/login.php");
exit;
?>