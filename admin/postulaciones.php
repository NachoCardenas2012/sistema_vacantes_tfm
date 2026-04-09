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

$sql = "
    SELECT p.id, u.nombre AS usuario, v.titulo AS vacante, 
           p.fecha_postulacion, p.estado, p.archivo
    FROM postulaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    INNER JOIN vacantes v ON p.vacante_id = v.id
    ORDER BY p.fecha_postulacion DESC
";

$result = $conn->query($sql);
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">📋 Gestionar Postulaciones</h2>

        <!-- Mensajes -->
        <?php if (isset($_GET['msg'])): ?>
            <?php if ($_GET['msg'] === 'aprobado'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    ✅ Postulación <strong>aprobada</strong> correctamente
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php elseif ($_GET['msg'] === 'rechazado'): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    ❌ Postulación <strong>rechazada</strong> correctamente
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-warning alert-dismissible fade show">
                ⚠️ <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Vacante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>CV</th>
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
                                <span class="badge bg-<?= 
                                    $row['estado'] === 'pendiente' ? 'warning text-dark' : 
                                    ($row['estado'] === 'aprobado' ? 'success' : 'danger') ?>">
                                    <?= ucfirst($row['estado']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($row['archivo']): ?>
                                    <a href="../<?= htmlspecialchars($row['archivo']) ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                       📄 Ver CV
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Sin archivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($row['estado'] === 'pendiente'): ?>
                                    <div class="d-flex gap-2">
                                        <!-- Aprobar con modal -->
                                        <button class="btn btn-sm btn-success"
                                                onclick="confirmarAprobar(<?= $row['id'] ?>, '<?= htmlspecialchars($row['usuario']) ?>', '<?= htmlspecialchars($row['vacante']) ?>')">
                                            ✅ Aprobar
                                        </button>
                                        <!-- Rechazar con modal -->
                                        <button class="btn btn-sm btn-danger"
                                                onclick="confirmarRechazar(<?= $row['id'] ?>, '<?= htmlspecialchars($row['usuario']) ?>', '<?= htmlspecialchars($row['vacante']) ?>')">
                                            ❌ Rechazar
                                        </button>
                                    </div>
                                <?php elseif ($row['estado'] === 'aprobado'): ?>
                                    <span class="text-success fw-bold">✅ Aprobado</span>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">❌ Rechazado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info text-center">
                📭 No hay postulaciones registradas.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MODAL APROBAR ===== -->
<div class="modal fade" id="modalAprobar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center px-5 pb-2">
                <div class="modal-icono-aprobar mb-3">✅</div>
                <h4 class="fw-bold text-success mb-2">¿Aprobar Postulación?</h4>
                <p class="text-muted mb-1">Estás a punto de aprobar a:</p>
                <p class="fw-bold fs-5 mb-1" id="aprobar-usuario">—</p>
                <p class="text-muted mb-0">Para la vacante:</p>
                <p class="fw-bold text-success" id="aprobar-vacante">—</p>
            </div>

            <div class="modal-footer border-0 justify-content-center gap-3 pb-4">
                <button type="button" 
                        class="btn btn-light px-4 rounded-pill" 
                        data-bs-dismiss="modal">
                    Cancelar
                </button>
                <a id="btn-confirmar-aprobar" 
                   href="#" 
                   class="btn btn-success px-4 rounded-pill">
                    ✅ Sí, Aprobar
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ===== MODAL RECHAZAR ===== -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center px-5 pb-2">
                <div class="modal-icono-rechazar mb-3">❌</div>
                <h4 class="fw-bold text-danger mb-2">¿Rechazar Postulación?</h4>
                <p class="text-muted mb-1">Estás a punto de rechazar a:</p>
                <p class="fw-bold fs-5 mb-1" id="rechazar-usuario">—</p>
                <p class="text-muted mb-0">Para la vacante:</p>
                <p class="fw-bold text-danger" id="rechazar-vacante">—</p>
                <div class="alert alert-warning mt-3 text-start rounded-3">
                    ⚠️ Esta acción notificará al candidato que no fue seleccionado.
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center gap-3 pb-4">
                <button type="button" 
                        class="btn btn-light px-4 rounded-pill" 
                        data-bs-dismiss="modal">
                    Cancelar
                </button>
                <a id="btn-confirmar-rechazar" 
                   href="#" 
                   class="btn btn-danger px-4 rounded-pill">
                    ❌ Sí, Rechazar
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ===== JAVASCRIPT ===== -->
<script>
// Abrir modal APROBAR
function confirmarAprobar(id, usuario, vacante) {
    document.getElementById('aprobar-usuario').textContent = usuario;
    document.getElementById('aprobar-vacante').textContent  = vacante;
    document.getElementById('btn-confirmar-aprobar').href   = 
        'postulaciones_aprobar.php?id=' + id;
    new bootstrap.Modal(document.getElementById('modalAprobar')).show();
}

// Abrir modal RECHAZAR
function confirmarRechazar(id, usuario, vacante) {
    document.getElementById('rechazar-usuario').textContent = usuario;
    document.getElementById('rechazar-vacante').textContent  = vacante;
    document.getElementById('btn-confirmar-rechazar').href   = 
        'postulaciones_rechazar.php?id=' + id;
    new bootstrap.Modal(document.getElementById('modalRechazar')).show();
}
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>