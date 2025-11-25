<?php
$page_title = "Gestionar Vacantes";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

$sql = "SELECT id, titulo, descripcion, requisitos, departamento, fecha_publicacion, estado FROM vacantes ORDER BY fecha_publicacion DESC";
$result = $conn->query($sql);
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Vacantes</h2>

        <a href="vacantes_crear.php" class="btn btn-primary mb-3">Crear Nueva Vacante</a>

        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Descripción</th>
                    <th>Requisitos</th>
                    <th>Departamento</th>
                    <th>Fecha de Publicación</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['titulo']) ?></td>
                        <td><?= htmlspecialchars(substr($row['descripcion'], 0, 70)) ?>...</td>
                        <td><?= htmlspecialchars(substr($row['requisitos'], 0, 70)) ?>...</td>
                        <td><?= htmlspecialchars($row['departamento']) ?></td>
                        <td><?= $row['fecha_publicacion'] ?></td>
                        <td>
                            <span class="badge bg-<?= $row['estado'] === 'abierta' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($row['estado']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="vacantes_editar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Editar</a>
                            <a href="vacantes_eliminar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar esta vacante?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
