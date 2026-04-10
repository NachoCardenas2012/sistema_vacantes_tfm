<?php
session_start();

$page_title = "Editar Usuario";
include '../includes/header.php';
include '../includes/sidebar.php';

// Verificar que el usuario sea administrador
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

// Validar que se recibe un ID válido
if (!isset($_GET['id']) || intval($_GET['id']) <= 0) {
    header("Location: usuarios.php");
    exit;
}

$id   = intval($_GET['id']);
$error = "";

// Conexión a base de datos
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    die("Error de conexión a base de datos.");
}
$conn->set_charset("utf8");

// Buscar usuario por ID de forma segura
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Si no existe el usuario redirigir
if (!$user) {
    header("Location: usuarios.php?error=Usuario no encontrado");
    exit;
}

// Procesar formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $rol      = $_POST['rol']           ?? '';

    // Validar que todos los campos estén completos
    if (empty($nombre) || empty($apellido) || empty($email) || empty($rol)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar que el email no pertenezca a otro usuario
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "El email ya está registrado en otro usuario.";
        } else {
            // Validar que el rol sea permitido
            $roles_validos = ['admin', 'empleado'];
            if (!in_array($rol, $roles_validos)) {
                $error = "Rol seleccionado no válido.";
            } else {
                // Actualizar datos del usuario
                $stmt = $conn->prepare("
                    UPDATE usuarios 
                    SET nombre = ?, apellido = ?, email = ?, rol = ? 
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssi", $nombre, $apellido, $email, $rol, $id);

                if ($stmt->execute()) {
                    $stmt->close();
                    $check->close();
                    $conn->close();
                    header("Location: usuarios.php?success=update");
                    exit;
                } else {
                    $error = "Error al actualizar el usuario. Intente de nuevo.";
                }
                $stmt->close();
            }
        }
        $check->close();
    }
}

$conn->close();
?>

<div class="content">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center my-4">
            <h2>Editar Usuario</h2>
            <a href="usuarios.php" class="btn btn-secondary">← Regresar</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text"
                       name="nombre"
                       id="nombre"
                       class="form-control"
                       value="<?= htmlspecialchars($user['nombre']) ?>"
                       required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text"
                       name="apellido"
                       id="apellido"
                       class="form-control"
                       value="<?= htmlspecialchars($user['apellido']) ?>"
                       required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email"
                       name="email"
                       id="email"
                       class="form-control"
                       value="<?= htmlspecialchars($user['email']) ?>"
                       required>
            </div>
            <div class="mb-4">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="admin"    <?= $user['rol'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                    <option value="empleado" <?= $user['rol'] === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning">💾 Guardar Cambios</button>
                <a href="usuarios.php" class="btn btn-secondary">← Regresar</a>
            </div>
        </form>

    </div>
</div>

<?php include '../includes/footer.php'; ?>