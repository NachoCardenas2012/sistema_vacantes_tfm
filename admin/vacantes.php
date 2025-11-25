<?php
$page_title = "Gestionar Vacantes";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

/************************************
 * CONFIGURACIÓN DE BD (PDO)
 ************************************/
$host = "localhost";
$dbname = "sistema_vacantes";
$username = "root";
$password = "root";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

/************************************
 * ELIMINAR VACANTE
 ************************************/
if (isset($_GET['eliminar'])) {

    $id = $_GET['eliminar'];
    $sql = "DELETE FROM vacantes WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    header("Location: vacantes.php?mensaje=eliminado");
    exit;
}

/************************************
 * OBTENER LISTA DE VACANTES
 ************************************/
$sql = "SELECT * FROM vacantes ORDER BY fecha_publicacion DESC";
$stmt = $conn->query($sql);
$vacantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <div class="container">

        <h2 class="my-4">Vacantes</h2>

        <a href="vacantes_crear.php" class="btn btn-primary mb-3">
            Crear Nueva Vacante
        </a>

        <!-- Mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
            <?php if ($_GET['mensaje'] == 'creado'): ?>
                <div class="alert alert-success">Vacante creada exitosamente.</div>
            <?php elseif ($_GET['mensaje'] == 'editado'): ?>
                <div class="alert alert-info">Vacante actualizada correctamente.</div>
            <?php elseif ($_GET['mensaje'] == 'eliminado'): ?>
                <div class="alert alert-danger">Vacante eliminada.</div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body">

                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Descripción</th>
                            <th>Requisitos</th>
                            <th>Departamento</th>
                            <th>Fecha Publicación</th>
                            <th>Estado</th>
                            <th style="width: 160px;">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($vacantes as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>
                                <td><?= htmlspecialchars($v['titulo']) ?></td>
                                <td><?= substr(htmlspecialchars($v['descripcion']), 0, 70) . "..." ?></td>
                                <td><?= substr(htmlspecialchars($v['requisitos']), 0, 70) . "..." ?></td>
                                <td><?= htmlspecialchars($v['departamento']) ?></td>
                                <td><?= $v['fecha_publicacion'] ?></td>

                                <td>
                                    <span class="badge bg-<?= 
                                        $v['estado'] == 'abierta' ? 'success' : 'secondary'
                                    ?>">
                                        <?= ucfirst($v['estado']) ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="vacantes_editar.php?id=<?= $v['id'] ?>"
                                       class="btn btn-sm btn-primary">
                                       Editar
                                    </a>

                                    <a href="vacantes.php?eliminar=<?= $v['id'] ?>"
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Seguro que deseas eliminar esta vacante?');">
                                       Eliminar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
