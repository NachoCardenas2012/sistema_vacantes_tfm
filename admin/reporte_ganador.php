<?php
session_start();
date_default_timezone_set('America/Guayaquil');

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) die("Error: " . $conn->connect_error);

$vacante_id = intval($_GET['vacante_id'] ?? 0);

if (!$vacante_id) {
    header("Location: vacantes.php?error=Vacante no especificada");
    exit;
}

// Vacante
$stmt = $conn->prepare("SELECT * FROM vacantes WHERE id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$vacante = $stmt->get_result()->fetch_assoc();

if (!$vacante) {
    header("Location: vacantes.php?error=Vacante no encontrada");
    exit;
}

// Proceso
$stmt = $conn->prepare("SELECT * FROM proceso_seleccion WHERE vacante_id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$proceso = $stmt->get_result()->fetch_assoc();

// Ganador
$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.apellido, u.email
    FROM postulaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.vacante_id = ? AND p.es_ganador = 1
    LIMIT 1
");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$ganador = $stmt->get_result()->fetch_assoc();

// Todos los postulantes
$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.apellido, u.email
    FROM postulaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.vacante_id = ?
    ORDER BY p.es_ganador DESC, p.puntaje DESC
");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$todos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

$fecha_reporte = date('d/m/Y');
$hora_reporte  = date('H:i');
?>

<div class="content">
<div class="container pb-5">

    <!-- BOTONES -->
    <div class="d-flex justify-content-between align-items-center my-4 no-print flex-wrap gap-2">
        <a href="proceso_seleccion.php?vacante_id=<?= $vacante_id ?>"
           class="btn btn-outline-secondary rounded-pill px-4">
            ← Volver al Proceso
        </a>
        <div class="d-flex gap-2 flex-wrap">
            <a href="exportar_resultados.php?vacante_id=<?= $vacante_id ?>"
               class="btn btn-outline-success rounded-pill px-4">
                📥 Exportar CSV
            </a>
            <button onclick="window.print()"
                    class="btn btn-primary rounded-pill px-4">
                🖨️ Imprimir Reporte
            </button>
        </div>
    </div>

    <!-- REPORTE -->
    <div class="reporte-documento" id="reporte-imprimir">

        <!-- ENCABEZADO -->
        <div class="reporte-header">
            <img src="../assets/img/logo.png"
                 alt="Logo" class="reporte-logo"
                 onerror="this.style.display='none'">
            <h2 class="reporte-titulo">Consultoría CM</h2>
            <p class="text-muted mb-0">Sistema de Gestión de Vacantes</p>
            <hr>
            <h4 class="mt-2 fw-bold">📋 Reporte de Proceso de Selección</h4>
            <p class="text-muted mb-0">
                Generado el <?= $fecha_reporte ?> a las <?= $hora_reporte ?>
            </p>
        </div>

        <!-- INFO VACANTE -->
        <div class="mb-4">
            <h5 class="reporte-seccion-titulo">💼 Información de la Vacante</h5>
            <div class="table-responsive">
                <table class="tabla-info w-100">
                    <tr>
                        <td class="label">Puesto</td>
                        <td><?= htmlspecialchars($vacante['titulo']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Descripción</td>
                        <td><?= htmlspecialchars($vacante['descripcion'] ?? 'N/A') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Publicación</td>
                        <td>
                            <?= isset($vacante['fecha_publicacion'])
                                ? date('d/m/Y', strtotime($vacante['fecha_publicacion']))
                                : 'N/A' ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Estado Vacante</td>
                        <td>
                            <span class="badge bg-<?= $vacante['estado'] === 'abierta' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($vacante['estado']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Total Postulantes</td>
                        <td><?= count($todos) ?> candidatos</td>
                    </tr>
                    <?php if ($proceso): ?>
                    <tr>
                        <td class="label">Estado Proceso</td>
                        <td>
                            <span class="badge bg-<?= $proceso['estado'] === 'finalizado' ? 'success' : 'warning text-dark' ?>">
                                <?= ucfirst($proceso['estado']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Inicio</td>
                        <td>
                            <?= $proceso['fecha_inicio']
                                ? date('d/m/Y H:i', strtotime($proceso['fecha_inicio']))
                                : 'N/A' ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Fecha Cierre</td>
                        <td>
                            <?= $proceso['fecha_cierre']
                                ? date('d/m/Y H:i', strtotime($proceso['fecha_cierre']))
                                : 'En proceso' ?>
                        </td>
                    </tr>
                    <?php if (!empty($proceso['criterios_seleccion'])): ?>
                    <tr>
                        <td class="label">Criterios</td>
                        <td><?= htmlspecialchars($proceso['criterios_seleccion']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- GANADOR -->
        <?php if ($ganador): ?>
        <div class="mb-4">
            <h5 class="reporte-seccion-titulo">🏆 Candidato Seleccionado</h5>
            <div class="ganador-card">
                <div class="ganador-icono">🏆</div>
                <div class="ganador-info">
                    <h3 class="ganador-nombre">
                        <?= htmlspecialchars($ganador['nombre'] . ' ' . $ganador['apellido']) ?>
                    </h3>
                    <p>
                        <strong>Vacante:</strong>
                        <?= htmlspecialchars($vacante['titulo']) ?>
                    </p>
                    <p>
                        <strong>Email:</strong>
                        <?= htmlspecialchars($ganador['email']) ?>
                    </p>
                    <p>
                        <strong>Puntaje obtenido:</strong>
                        <span class="badge bg-success px-3 py-2 ms-1">
                            <?= number_format($ganador['puntaje'] ?? 0, 1) ?>/10
                        </span>
                    </p>
                    <?php if (!empty($ganador['comentario_seleccion'])): ?>
                    <p>
                        <strong>Comentarios:</strong>
                        <?= htmlspecialchars($ganador['comentario_seleccion']) ?>
                    </p>
                    <?php endif; ?>
                    <p>
                        <strong>Fecha de selección:</strong>
                        <?= $ganador['fecha_seleccion']
                            ? date('d/m/Y H:i', strtotime($ganador['fecha_seleccion']))
                            : $fecha_reporte ?>
                    </p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning mb-4">
            ⚠️ Aún no se ha seleccionado un ganador para esta vacante.
        </div>
        <?php endif; ?>

        <!-- TABLA POSTULANTES -->
        <div class="mb-4">
            <h5 class="reporte-seccion-titulo">👥 Resumen de Postulantes</h5>
            <div class="tabla-wrapper">
                <table class="tabla-postulantes">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Candidato</th>
                            <th>Email</th>
                            <th>Puntaje</th>
                            <th>Estado</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todos as $index => $p): ?>
                            <tr class="<?= $p['es_ganador'] ? 'ganador-row' : '' ?>">
                                <td>
                                    <?= $index + 1 ?>
                                    <?= $p['es_ganador'] ? ' 👑' : '' ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['email']) ?>
                                </td>
                                <td>
                                    <?php if ($p['puntaje'] > 0): ?>
                                        <strong><?= number_format($p['puntaje'], 1) ?>/10</strong>
                                    <?php else: ?>
                                        <span class="text-muted">Sin evaluar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $color = match($p['estado']) {
                                        'aprobado'  => 'success',
                                        'aceptada'  => 'success',
                                        'rechazado' => 'danger',
                                        'rechazada' => 'danger',
                                        'pendiente' => 'warning text-dark',
                                        default     => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst($p['estado']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($p['es_ganador']): ?>
                                        <span class="badge bg-success px-2 py-1">
                                            🏆 Ganador
                                        </span>
                                    <?php elseif (in_array($p['estado'], ['rechazado','rechazada'])): ?>
                                        <span class="badge bg-danger px-2 py-1">
                                            No seleccionado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-2 py-1">
                                            En evaluación
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FIRMA -->
        <div class="reporte-firma">
            <div class="row">
                <div class="col-md-6 text-center mb-4">
                    <div class="reporte-linea"></div>
                    <p class="mb-0"><strong>Responsable del Proceso</strong></p>
                    <p class="text-muted small">Administrador del Sistema</p>
                </div>
                <div class="col-md-6 text-center mb-4">
                    <div class="reporte-linea"></div>
                    <p class="mb-0"><strong>Jefe de Recursos Humanos</strong></p>
                    <p class="text-muted small">Consultoría CM</p>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="reporte-footer">
            <p class="mb-0">
                © <?= date('Y') ?> Consultoría CM —
                Documento generado el <?= $fecha_reporte ?> a las <?= $hora_reporte ?>
            </p>
        </div>

    </div><!-- FIN REPORTE -->

</div>
</div>

<?php include '../includes/footer.php'; ?>