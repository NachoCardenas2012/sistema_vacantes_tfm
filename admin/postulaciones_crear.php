<?php
$page_title = "Crear Postulación";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

// Obtener usuarios
$usuarios = $conn->query("SELECT id, nombre, apellido FROM usuarios ORDER BY nombre ASC");

// Obtener vacantes
$vacantes = $conn->query("SELECT id, titulo FROM vacantes WHERE estado = 'abierta' ORDER BY titulo ASC");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario_id = $_POST['usuario_id'];
    $vacante_id = $_POST['vacante_id'];
    $estado = $_POST['estado'];

    // Insertar postulación
    $stmt = $conn->prepare("
        INSERT INTO postulaciones (usuario_id, vacante_id, fecha_postulacion, estado)
        VALUES (?, ?, NOW(), ?)
    ");

    $stmt->bind_param("iis", $usuario_id, $vacante_id, $estado);
    $stmt->execute();
    $stmt->close();

    header("Location: postulaciones.php?mensaje=creado");
    exit;
}
?>

<div class="content">
    <div class="container">

        <h2 class="my-4">Crear Nueva Postulación</h2>

        <div class="card shadow">
            <div class="card-body">

                <form method="POST">

                    <!-- Usuario -->
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Usuario</label>
                        <select name="usuario_id" class="form-select" required>
                            <option value="">Seleccione un usuario</option>
                            <?php while ($u = $usuarios->fetch_assoc()): ?>
                                <option value="<?= $u['id'] ?>">
                                    <?= $u['nombre'] . " " . $u['apellido'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Vacante -->
                    <div class="mb-3">
                        <label class="form-label">Seleccionar Vacante</label>
                        <select name="vacante_id" class="form-select" required>
                            <option value="">Seleccione una vacante</option>
                            <?php while ($v = $vacantes->fetch_assoc()): ?>
                                <option value="<?= $v['id'] ?>">
                                    <?= $v['titulo'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="aceptado">Aceptado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Crear Postulación</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
