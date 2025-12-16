<?php
// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Validar sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=" . urlencode("Debe iniciar sesión primero"));
    exit;
}

// Validar vacante
if (!isset($_POST['vacante_id'])) {
    header("Location: postular.php?error=" . urlencode("Vacante no recibida"));
    exit;
}

$vacante_id = intval($_POST['vacante_id']);
$usuario_id = intval($_SESSION['id']);

if (!isset($_FILES['hoja_vida'])) {
    header("Location: postular.php?error=" . urlencode("Debe subir un archivo"));
    exit;
}

$archivo = $_FILES['hoja_vida'];
$error = "";

// Validación de archivo
if ($archivo['error'] !== 0) {
    $error = "Error al subir el archivo. Código: " . $archivo['error'];
}

if ($error === "") {

    // Limpiar nombre archivo
    $file_name = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $archivo['name']);
    $file_tmp  = $archivo['tmp_name'];
    $file_size = $archivo['size'];
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $ext_permitidas = ['pdf', 'doc', 'docx'];
    $tam_max = 5 * 1024 * 1024; // 5 MB

    if (!in_array($extension, $ext_permitidas)) {
        $error = "Formato no permitido. Solo PDF, DOC o DOCX.";
    }

    if ($file_size > $tam_max) {
        $error = "El archivo supera los 5MB permitidos.";
    }

    if ($error === "") {

        // Crear carpeta si no existe
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Ruta final donde se guardará el archivo
        $ruta_final = $upload_dir . uniqid("cv_") . "_" . $file_name;

        if (!move_uploaded_file($file_tmp, $ruta_final)) {
            $error = "No se pudo guardar el archivo. Verifique permisos.";
        }

        if ($error === "") {

            // Conexión a la BD
            $conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

            if ($conn->connect_error) {
                die("Error al conectar a la BD: " . $conn->connect_error);
            }

            // Insertar en la DB
            $stmt = $conn->prepare("
                INSERT INTO postulaciones (usuario_id, vacante_id, fecha_postulacion, estado, archivo)
                VALUES (?, ?, NOW(), 'pendiente', ?)
            ");

            $stmt->bind_param("iis", $usuario_id, $vacante_id, $ruta_final);

            if ($stmt->execute()) {
                header("Location: postular.php?success=" . urlencode("Postulación enviada correctamente."));
                exit;
            } else {
                $error = "Error al guardar en la base de datos: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        }
    }
}

// Si hubo error:
header("Location: postular.php?error=" . urlencode($error));
exit;
?>
