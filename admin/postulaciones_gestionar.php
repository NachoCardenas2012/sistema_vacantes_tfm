<?php
$page_title = "Gestionar Postulaciones";
include '../includes/header.php';
include '../includes/sidebar.php';

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

$sql = "
SELECT p.id, p.fecha_postulacion, p.estado,
       u.nombre, u.apellido, u.email,
       v.titulo AS vacante
FROM postulaciones p
JOIN usuarios u ON u.id = p.usuario_id
JOIN vacantes v ON v.id = p.vacante_id
ORDER BY p.fecha_postulacion DESC
";

$result = $conn->query($sql);
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Postulaciones</h2>

        <a href="postulaciones_crear.php" class="btn btn-primary mb-3">Crear Nueva Postulación</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Vacante</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['nombre'] . " " . $row['apellido'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['vacante'] ?></td>
                        <td><?= $row['fecha_postulacion'] ?></td>

                        <td>
                            <span class="badge bg-<?= 
                                $row['estado'] == 'pendiente' ? 'warning' : 
                                ($row['estado'] == 'aceptado' ? 'success' : 'danger')
                            ?>">
                            <?= ucfirst($row['estado']) ?>
                            </span>
                        </td>

                        <td>
                            <a href="postulaciones_editar.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="postulaciones_eliminar.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
