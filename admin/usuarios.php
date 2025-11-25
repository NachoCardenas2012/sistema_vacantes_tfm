<?php
$page_title = "Gestionar Usuarios";
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) die("Conexión fallida: " . $conn->connect_error);

$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);

// Capturar mensaje de éxito
$mensaje_exito = "";
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'create') {
        $mensaje_exito = "¡Usuario creado exitosamente!";
    } elseif ($_GET['success'] == 'update') {
        $mensaje_exito = "¡Usuario actualizado exitosamente!";
    } elseif ($_GET['success'] == 'delete') {
        $mensaje_exito = "¡Usuario eliminado exitosamente!";
    }
}
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Usuarios Registrados</h2>

        <?php if ($mensaje_exito): ?>
            <div id="alert-success" class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje_exito) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <a href="usuarios_crear.php" class="btn btn-primary mb-3">Crear Nuevo Usuario</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['nombre'] . " " . $row['apellido']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['rol']) ?></td>
                        <td><?= htmlspecialchars($row['fecha_registro']) ?></td>
                        <td>
                            <a href="usuarios_editar.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="usuarios_eliminar.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    setTimeout(function() {
        var alert = document.getElementById('alert-success');
        if (alert) alert.classList.remove('show');
    }, 5000);
</script>

<?php include '../includes/footer.php'; ?>
