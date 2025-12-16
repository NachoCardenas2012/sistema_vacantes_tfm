<?php
$page_title = "Gestionar Postulaciones";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

// Consulta para traer todas las postulaciones con info de usuario y vacante
$sql = "
    SELECT p.id, u.nombre AS usuario, v.titulo AS vacante, p.fecha_postulacion, p.estado, p.archivo
    FROM postulaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    INNER JOIN vacantes v ON p.vacante_id = v.id
    ORDER BY p.fecha_postulacion DESC
";

$result = $conn->query($sql);
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Postulaciones</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Vacante</th>
                        <th>Fecha de Postulación</th>
                        <th>Estado</th>
                        <th>Archivo CV</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['usuario']) ?></td>
                            <td><?= htmlspecialchars($row['vacante']) ?></td>
                            <td><?= $row['fecha_postulacion'] ?></td>
                            <td>
                                <span class="badge bg-<?= $row['estado'] === 'pendiente' ? 'warning' : ($row['estado'] === 'aprobada' ? 'success' : 'secondary') ?>">
                                    <?= ucfirst($row['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['archivo']): ?>
                                    <a href="../<?= $row['archivo'] ?>" target="_blank" class="btn btn-sm btn-primary">Ver CV</a>
                                <?php else: ?>
                                    Sin archivo
                                <?php endif; ?>
                            </td>

                            <td>
                                <!-- Opciones: cambiar estado -->
                                <a href="postulaciones_aprobar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-success">Aprobar</a>
                                <a href="postulaciones_rechazar.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger">Rechazar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No hay postulaciones registradas.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
