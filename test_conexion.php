<?php
$conexion = new mysqli("localhost", "root", "root", "sistema_vacantes", 3306);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
} else {
    echo "Conexión OK!";
    $sql = "SELECT * FROM usuarios";
    $res = query($sql);
    echo $res;
}
?>
