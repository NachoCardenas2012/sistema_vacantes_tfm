<?php
// ==========================
// DEPURACIÓN DE ERRORES
// ==========================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================
// INCLUDES Y VERIFICACIÓN DE ROL
// ==========================
$page_title = "Editar Usuario";
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

// ==========================
// VARIABLES PARA MENSAJES
// ==========================
$error = "";
$exito = "";

// ==========================
// VALIDACIÓN Y PROCESAMIENTO DEL FORMULARIO
// ==========================
if (!isset($_GET['id'])) {
    header("Location: usuarios.php");
    exit;
}

$id = $_GET['id'];
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
$sql = "SELECT * FROM usuarios WHERE id = $id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];

    // Validación de campos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($rol)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si el email ya está registrado
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "El email ya está registrado en otro usuario.";
        } else {
            // Actualizar usuario
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, rol = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $apellido, $email, $rol, $id);

            if ($stmt->execute()) {
                $exito = "¡Usuario actualizado exitosamente!";
                header("Location: usuarios.php?success=update");
                exit;
            } else {
                $error = "Error al actualizar usuario: " . $stmt->error;
            }
            $stmt->close();
        }

        $check->close();
    }

    $conn->close();
}
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Editar Usuario</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alert alert-success"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($user['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" name="apellido" id="apellido" class="form-control" value="<?= htmlspecialchars($user['apellido']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="admin" <?= $user['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="empleado" <?= $user['rol'] === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-warning">Guardar Cambios</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
