<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}

$vacante_id = $_POST['vacante_id'];
$hoja_vida = $_FILES['hoja_vida'];

$error = '';

// Validar archivo
if ($hoja_vida['error'] == 0) {
    // Obtener información del archivo
    $file_name = $hoja_vida['name'];
    $file_tmp = $hoja_vida['tmp_name'];
    $file_size = $hoja_vida['size'];
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);

    // Validar extensión y tamaño
    $allowed_extensions = ['pdf', 'doc', 'docx'];
    $max_size = 5 * 1024 * 1024; // 5MB máximo

    if (!in_array($file_type, $allowed_extensions)) {
        $error = "Solo se permiten archivos PDF, DOC o DOCX.";
    } elseif ($file_size > $max_size) {
        $error = "El archivo es demasiado grande. El tamaño máximo permitido es 5MB.";
    }

    // Si no hubo errores, mover el archivo a la carpeta 'uploads'
    if ($error === '') {
        $upload_dir = 'uploads/';
        $file_path = $upload_dir . uniqid() . '-' . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            // Conexión a la base de datos
            $servername = "localhost";
            $username = "root";
            $password = "root";
            $dbname = "sistema_vacantes";

            // Crear la conexión
            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                die("Conexión fallida: " . $conn->connect_error);
            }

            // Insertar la postulación en la base de datos
            $stmt = $conn->prepare("INSERT INTO postulaciones (vacante_id, usuario_id, archivo) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $vacante_id, $_SESSION['id'], $file_path);

            if ($stmt->execute()) {
                // Redirigir al usuario con un mensaje de éxito
                header("Location: postular.php?success=Postulación exitosa");
            } else {
                $error = "Error al registrar la postulación.";
            }

            $stmt->close();
            $conn->close();
        } else {
            $error = "Error al subir el archivo.";
        }
    }
} else {
    $error = "No se ha seleccionado ningún archivo.";
}

// Si hubo error, redirigir de nuevo con el mensaje de error
if ($error) {
    header("Location: postular.php?error=" . urlencode($error));
    exit;
}
?>
