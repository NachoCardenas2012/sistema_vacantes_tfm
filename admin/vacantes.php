<?php
// ✅ TEMPORAL - Para ver errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = "Gestionar Vacantes";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$host     = "localhost";
$dbname   = "sistema_vacantes";
$username = "root";
$password = "root";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Eliminar vacante
if (isset($_GET['eliminar'])) {
    $id   = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM vacantes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: vacantes.php?mensaje=eliminado");
    exit;
}

// Obtener vacantes con conteo de postulaciones
// ✅ QUERY CORREGIDA
$sql = "
    SELECT 
        v.id,
        v.titulo,
        v.descripcion,
        v.requisitos,
        v.departamento,
        v.fecha_publicacion,
        v.estado,
        COUNT(p.id)          AS total_postulaciones,
        MAX(ps.estado)       AS estado_proceso
    FROM vacantes v
    LEFT JOIN postulaciones p       ON v.id = p.vacante_id
    LEFT JOIN proceso_seleccion ps  ON v.id = ps.vacante_id
    GROUP BY 
        v.id,
        v.titulo,
        v.descripcion,
        v.requisitos,
        v.departamento,
        v.fecha_publicacion,
        v.estado
    ORDER BY v.fecha_publicacion DESC
";
$stmt     = $conn->query($sql);
$vacantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content">
    <div class="container">

        <div class="d-flex justify-content-between align-items-center my-4">
            <h2 class="mb-0">💼 Gestionar Vacantes</h2>
            <a href="vacantes_crear.php" class="btn btn-primary">
                ➕ Crear Nueva Vacante
            </a>
        </div>

        <!-- Mensajes -->
        <?php if (isset($_GET['mensaje'])): ?>
            <?php if ($_GET['mensaje'] == 'creado'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ Vacante creada exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['mensaje'] == 'editado'): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    ✏️ Vacante actualizada correctamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['mensaje'] == 'eliminado'): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    🗑️ Vacante eliminada.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body p-0">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Departamento</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-center">Postulantes</th>
                            <th class="text-center">Proceso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vacantes as $v): ?>
                            <tr>
                                <td><?= $v['id'] ?></td>

                                <td>
                                    <strong><?= htmlspecialchars($v['titulo']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= substr(htmlspecialchars($v['descripcion']), 0, 60) ?>...
                                    </small>
                                </td>

                                <td><?= htmlspecialchars($v['departamento']) ?></td>

                                <td><?= date('d/m/Y', strtotime($v['fecha_publicacion'])) ?></td>

                                <td>
                                    <span class="badge bg-<?= 
                                        $v['estado'] === 'abierta'  ? 'success'   : 
                                        ($v['estado'] === 'pausada' ? 'warning'   : 
                                        'secondary') ?>">
                                        <?= ucfirst($v['estado']) ?>
                                    </span>
                                </td>

                                <!-- Total postulantes -->
                                <td class="text-center">
                                    <span class="badge bg-info text-dark fs-6">
                                        👥 <?= $v['total_postulaciones'] ?>
                                    </span>
                                </td>

                                <!-- Estado del proceso de selección -->
                                <td class="text-center">
                                    <?php if ($v['estado_proceso'] === 'abierto'): ?>
                                        <span class="badge bg-warning text-dark">
                                            🔄 En Proceso
                                        </span>
                                    <?php elseif ($v['estado_proceso'] === 'finalizado'): ?>
                                        <span class="badge bg-success">
                                            ✅ Finalizado
                                        </span>
                                    <?php elseif ($v['estado_proceso'] === 'cerrado'): ?>
                                        <span class="badge bg-secondary">
                                            🔒 Cerrado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted">
                                            ⏳ Sin iniciar
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Acciones -->
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap">

                                        <!-- Editar -->
                                        <a href="vacantes_editar.php?id=<?= $v['id'] ?>"
                                           class="btn btn-sm btn-primary"
                                           title="Editar vacante">
                                           ✏️
                                        </a>

                                        <!-- ✅ PROCESO DE SELECCIÓN -->
                                        <?php if ($v['total_postulaciones'] > 0): ?>
                                            <a href="proceso_seleccion.php?vacante_id=<?= $v['id'] ?>"
                                               class="btn btn-sm btn-<?= $v['estado_proceso'] === 'finalizado' ? 'success' : 'warning' ?>"
                                               title="Proceso de Selección">
                                               🏆
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" 
                                                    disabled 
                                                    title="Sin postulantes aún">
                                                🏆
                                            </button>
                                        <?php endif; ?>

                                        <!-- Ver Reporte (solo si hay proceso finalizado) -->
                                        <?php if ($v['estado_proceso'] === 'finalizado'): ?>
                                            <a href="reporte_ganador.php?vacante_id=<?= $v['id'] ?>"
                                               class="btn btn-sm btn-info"
                                               title="Ver Reporte del Ganador">
                                               📋
                                            </a>
                                        <?php endif; ?>

                                        <!-- Eliminar -->
                                        <a href="vacantes.php?eliminar=<?= $v['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirmarEliminar(<?= $v['id'] ?>, '<?= htmlspecialchars($v['titulo']) ?>')"
                                           title="Eliminar vacante">
                                           🗑️
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($vacantes)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    📭 No hay vacantes registradas
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-5 pb-2">
                <div style="font-size: 3.5rem;">🗑️</div>
                <h4 class="fw-bold text-danger mb-2">¿Eliminar Vacante?</h4>
                <p class="text-muted mb-1">Estás a punto de eliminar:</p>
                <p class="fw-bold fs-5" id="modal-titulo-vacante">—</p>
                <div class="alert alert-warning rounded-3 text-start mt-3">
                    ⚠️ Se eliminarán también todas las postulaciones asociadas.
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-3 pb-4">
                <button type="button"
                        class="btn btn-light px-4 rounded-pill"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>
                <a id="btn-confirmar-eliminar"
                   href="#"
                   class="btn btn-danger px-4 rounded-pill">
                    🗑️ Sí, Eliminar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id, titulo) {
    document.getElementById('modal-titulo-vacante').textContent   = titulo;
    document.getElementById('btn-confirmar-eliminar').href        = 
        'vacantes.php?eliminar=' + id;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
    return false; // Evita el onclick directo
}
</script>

<?php include '../includes/footer.php'; ?>