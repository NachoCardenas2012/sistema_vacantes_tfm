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
$page_title = "Crear Usuario";
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

// ==========================
// PROCESAR FORMULARIO
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'];

    // Validar contraseña mínima
    if (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        // Verificar email duplicado
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        if (!$check) die("Error en consulta: " . $conn->error);
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "El email ya está registrado.";
        } else {
            // Validar rol
            $roles_validos = ['admin', 'empleado'];
            if (!in_array($rol, $roles_validos)) {
                $error = "Rol seleccionado no válido.";
            } else {
                // Insertar usuario
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) die("Error en INSERT: " . $conn->error);
                $stmt->bind_param("sssss", $nombre, $apellido, $email, $hashed_password, $rol);

                if ($stmt->execute()) {
                    header("Location: usuarios.php?success=create");
                    exit;
                } else {
                    $error = "Error al crear usuario: " . $stmt->error;
                }
                $stmt->close();
            }
        }

        $check->close();
        $conn->close();
    }
}
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Crear Usuario</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?= isset($nombre) ? htmlspecialchars($nombre) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" name="apellido" id="apellido" class="form-control" value="<?= isset($apellido) ? htmlspecialchars($apellido) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="admin" <?= (isset($rol) && $rol === 'admin') ? 'selected' : '' ?>>Admin</option>
                    <option value="empleado" <?= (isset($rol) && $rol === 'empleado') ? 'selected' : '' ?>>Empleado</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Crear Usuario</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
