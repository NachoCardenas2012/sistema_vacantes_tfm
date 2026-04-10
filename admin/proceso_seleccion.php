<?php
session_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) die("Error: " . $conn->connect_error);

$vacante_id = intval($_GET['vacante_id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM vacantes WHERE id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$vacante = $stmt->get_result()->fetch_assoc();

if (!$vacante) {
    header("Location: vacantes.php?error=Vacante no encontrada");
    exit;
}

$stmt = $conn->prepare("
    SELECT p.*, u.nombre, u.email, u.apellido
    FROM postulaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.vacante_id = ?
    ORDER BY p.puntaje DESC, p.fecha_postulacion ASC
");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$postulaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM proceso_seleccion WHERE vacante_id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$proceso_existente = $stmt->get_result()->fetch_assoc();

$conn->close();

$total_ganadores  = count(array_filter($postulaciones, fn($p) => $p['es_ganador']));
$total_pendientes = count(array_filter($postulaciones, fn($p) => $p['estado'] === 'pendiente'));
?>

<div class="content">
<div class="container pb-5">

    <div id="alertaMsg" class="d-none mt-3"></div>

    <div class="d-flex justify-content-between align-items-center my-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold">🏆 Proceso de Selección</h2>
            <p class="text-muted mb-0">
                Vacante: <strong><?= htmlspecialchars($vacante['titulo']) ?></strong>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="vacantes.php" class="btn btn-outline-secondary rounded-pill px-4">
                ← Volver
            </a>
            <?php if (!$proceso_existente): ?>
                <button type="button"
                        class="btn btn-success rounded-pill px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#modalIniciar">
                    ▶ Iniciar Proceso
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card stat-primary">
                <div class="stat-content">
                    <div class="stat-data">
                        <div class="stat-number"><?= count($postulaciones) ?></div>
                        <div class="stat-label">TOTAL POSTULANTES</div>
                    </div>
                    <div class="stat-icon">👥</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-data">
                        <div class="stat-number"><?= $total_ganadores ?></div>
                        <div class="stat-label">GANADORES</div>
                    </div>
                    <div class="stat-icon">🏆</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-warning">
                <div class="stat-content">
                    <div class="stat-data">
                        <div class="stat-number"><?= $total_pendientes ?></div>
                        <div class="stat-label">PENDIENTES</div>
                    </div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card stat-info">
                <div class="stat-content">
                    <div class="stat-data">
                        <div class="stat-number" style="font-size:1.1rem;">
                            <?= $proceso_existente
                                ? ucfirst($proceso_existente['estado'])
                                : 'Sin iniciar' ?>
                        </div>
                        <div class="stat-label">ESTADO PROCESO</div>
                    </div>
                    <div class="stat-icon">⚙️</div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">📋 Lista de Postulantes</h5>
            <a href="exportar_resultados.php?vacante_id=<?= $vacante_id ?>"
               class="btn btn-outline-primary btn-sm rounded-pill">
                📥 Exportar
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Postulante</th>
                            <th>Email</th>
                            <th>Fecha</th>
                            <th>Puntaje</th>
                            <th>Estado</th>
                            <?php if ($proceso_existente && $proceso_existente['estado'] === 'abierto'): ?>
                                <th class="text-center">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postulaciones as $index => $p): ?>
                            <tr class="<?= $p['es_ganador'] ? 'table-success' : '' ?>">

                                <td>
                                    <span class="badge bg-secondary"><?= $index + 1 ?></span>
                                    <?= $p['es_ganador'] ? ' 👑' : '' ?>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                             style="width:36px;height:36px;font-size:0.85rem;flex-shrink:0;">
                                            <?= strtoupper(substr($p['nombre'], 0, 1)) ?>
                                        </div>
                                        <strong>
                                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                        </strong>
                                    </div>
                                </td>

                                <td class="text-muted small">
                                    <?= htmlspecialchars($p['email']) ?>
                                </td>

                                <td class="small">
                                    <?= date('d/m/Y H:i', strtotime($p['fecha_postulacion'])) ?>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold">
                                            <?= number_format($p['puntaje'] ?? 0, 1) ?>/10
                                        </span>
                                        <div class="progress" style="width:60px;height:6px;">
                                            <div class="progress-bar bg-info"
                                                 style="width:<?= ($p['puntaje'] ?? 0) * 10 ?>%">
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($p['es_ganador']): ?>
                                        <span class="badge bg-success px-3 py-2">
                                            🏆 Ganador
                                        </span>
                                    <?php else: ?>
                                        <?php
                                        $badgeColor = match($p['estado']) {
                                            'aprobado'  => 'success',
                                            'aceptada'  => 'success',
                                            'rechazado' => 'danger',
                                            'rechazada' => 'danger',
                                            'pendiente' => 'warning text-dark',
                                            default     => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $badgeColor ?> px-3 py-2">
                                            <?= strtoupper($p['estado']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <?php if ($proceso_existente && $proceso_existente['estado'] === 'abierto'): ?>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">

                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                                onclick="abrirEvaluar(
                                                    <?= $p['id'] ?>,
                                                    '<?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido'], ENT_QUOTES) ?>'
                                                )">
                                            ⭐ Evaluar
                                        </button>

                                        <?php if (!$p['es_ganador']): ?>
                                            <button class="btn btn-sm btn-success rounded-pill px-3"
                                                    onclick="abrirGanador(
                                                        <?= $p['id'] ?>,
                                                        '<?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido'], ENT_QUOTES) ?>'
                                                    )">
                                                🏆 Ganador
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-danger rounded-pill px-3"
                                                    onclick="quitarGanador(<?= $p['id'] ?>)">
                                                ❌ Quitar
                                            </button>
                                        <?php endif; ?>

                                    </div>
                                </td>
                                <?php endif; ?>

                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($postulaciones)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    📭 No hay postulantes para esta vacante
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-3 mt-4 flex-wrap">

        <?php if ($proceso_existente && $proceso_existente['estado'] === 'abierto'): ?>
            <button class="btn btn-danger btn-lg rounded-pill px-5"
                    onclick="abrirFinalizar(<?= $proceso_existente['id'] ?>)">
                ✅ Finalizar Proceso
            </button>
        <?php endif; ?>

        <?php if ($proceso_existente): ?>
            <a href="reporte_ganador.php?vacante_id=<?= $vacante_id ?>"
               class="btn btn-warning btn-lg rounded-pill px-5">
                📋 Ver Reporte del Ganador
            </a>
        <?php endif; ?>

    </div>

</div>
</div>

<div class="modal fade" id="modalIniciar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">▶ Iniciar Proceso de Selección</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formIniciar">
                <div class="modal-body px-4">
                    <input type="hidden" name="vacante_id" value="<?= $vacante_id ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Número de ganadores</label>
                        <input type="number" class="form-control"
                               name="numero_ganadores" value="1" min="1" required>
                        <div class="form-text">Cuántos candidatos serán seleccionados al finalizar.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Criterios de selección</label>
                        <textarea class="form-control" name="criterios_seleccion"
                                  rows="4" style="text-align:left;resize:vertical;"
                                  placeholder="Ej: Se evaluará experiencia, nivel educativo y habilidades técnicas...">
                        </textarea>
                        <div class="form-text">Describe los criterios que usarás para evaluar candidatos.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnIniciar"
                            class="btn btn-success rounded-pill px-4">
                        ▶ Iniciar Proceso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEvaluar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">⭐ Evaluar Postulante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEvaluar">
                <div class="modal-body px-4">
                    <input type="hidden" name="postulacion_id" id="evalId">
                    <div class="alert alert-light border mb-3 text-start">
                        👤 Candidato: <strong id="evalNombre">—</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Puntaje (0 - 10)</label>
                        <input type="number" class="form-control form-control-lg"
                               name="puntaje" id="campoPuntaje"
                               min="0" max="10" step="0.1"
                               placeholder="Ej: 8.5" required>
                        <div class="mt-2">
                            <div class="progress" style="height:8px;">
                                <div id="barraPuntaje" class="progress-bar"
                                     style="width:0%;transition:all 0.3s;"></div>
                            </div>
                            <small id="textoPuntaje" class="fw-semibold mt-1 d-block"></small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Comentarios</label>
                        <textarea class="form-control" name="comentario"
                                  rows="3" style="text-align:left;resize:vertical;"
                                  placeholder="Observaciones del candidato..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnEvaluar"
                            class="btn btn-primary rounded-pill px-4">
                        💾 Guardar Evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalGanador" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center px-5 pb-2">
                <div style="font-size:3.5rem;">🏆</div>
                <h4 class="fw-bold text-success mb-2">¿Marcar como Ganador?</h4>
                <p class="text-muted mb-1">Estás a punto de seleccionar a:</p>
                <p class="fw-bold fs-5" id="ganadorNombre">—</p>
                <div class="alert alert-info rounded-3 text-start mt-3">
                    ℹ️ El candidato será marcado como ganador del proceso.
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-3 pb-4">
                <button type="button" class="btn btn-light px-5 rounded-pill"
                        data-bs-dismiss="modal">Cancelar</button>
                <button id="btnConfirmarGanador"
                        class="btn btn-success px-5 rounded-pill">
                    🏆 Sí, Marcar Ganador
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFinalizar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center px-4 pb-2">
                <div style="font-size:3rem;">⚙️</div>
                <h4 class="fw-bold text-danger mb-2">¿Finalizar el Proceso?</h4>
                <p class="text-muted mb-3">
                    Estás a punto de cerrar el proceso de selección
                </p>

                <div class="alert alert-info rounded-3 text-start py-2 px-3">
                    <p class="mb-0">
                        🤖 El sistema elegirá <strong>AUTOMÁTICAMENTE</strong>
                        al candidato con <strong>MAYOR PUNTAJE</strong> como ganador.
                    </p>
                </div>

                <div class="alert alert-warning rounded-3 text-start py-2 px-3 mb-0">
                    <p class="mb-0">
                        ⚠️ Esta acción <strong>NO se puede deshacer.</strong>
                    </p>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center gap-3 pb-4 pt-3">
                <button type="button"
                        class="btn btn-light px-4 rounded-pill"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button id="btnConfirmarFinalizar"
                        class="btn btn-danger px-4 rounded-pill">
                    ✅ Sí, Finalizar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
let _ganadorId  = null;
let _procesoId  = null;
let _mIniciar   = null;
let _mEvaluar   = null;
let _mGanador   = null;
let _mFinalizar = null;

document.addEventListener('DOMContentLoaded', function () {

    _mIniciar   = new bootstrap.Modal(document.getElementById('modalIniciar'));
    _mEvaluar   = new bootstrap.Modal(document.getElementById('modalEvaluar'));
    _mGanador   = new bootstrap.Modal(document.getElementById('modalGanador'));
    _mFinalizar = new bootstrap.Modal(document.getElementById('modalFinalizar'));

    document.getElementById('formIniciar').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn     = document.getElementById('btnIniciar');
        btn.disabled  = true;
        btn.innerHTML = '⏳ Iniciando...';

        fetch('procesar_seleccion.php?action=iniciar_proceso', {
            method: 'POST',
            body:   new FormData(this)
        })
        .then(r => r.text())
        .then(raw => {
            try {
                const data = JSON.parse(raw);
                if (data.success) {
                    _mIniciar.hide();
                    mostrarAlerta('success', '✅ ' + data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarAlerta('danger', '❌ ' + data.message);
                    btn.disabled  = false;
                    btn.innerHTML = '▶ Iniciar Proceso';
                }
            } catch (e) {
                mostrarAlerta('danger', '❌ Error servidor: ' + raw);
                btn.disabled  = false;
                btn.innerHTML = '▶ Iniciar Proceso';
            }
        })
        .catch(err => {
            mostrarAlerta('danger', '❌ Error: ' + err.message);
            btn.disabled  = false;
            btn.innerHTML = '▶ Iniciar Proceso';
        });
    });

    document.getElementById('campoPuntaje').addEventListener('input', function () {
        const val   = parseFloat(this.value) || 0;
        const barra = document.getElementById('barraPuntaje');
        const texto = document.getElementById('textoPuntaje');
        barra.style.width = (val * 10) + '%';
        if (val >= 8) {
            barra.className   = 'progress-bar bg-success';
            texto.textContent = '✅ Excelente puntuación';
            texto.style.color = '#16a34a';
        } else if (val >= 6) {
            barra.className   = 'progress-bar bg-warning';
            texto.textContent = '⚠️ Puntuación media';
            texto.style.color = '#d97706';
        } else if (val > 0) {
            barra.className   = 'progress-bar bg-danger';
            texto.textContent = '❌ Baja puntuación';
            texto.style.color = '#dc2626';
        } else {
            barra.className   = 'progress-bar';
            texto.textContent = '';
        }
    });

    document.getElementById('formEvaluar').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn     = document.getElementById('btnEvaluar');
        btn.disabled  = true;
        btn.innerHTML = '⏳ Guardando...';

        fetch('procesar_seleccion.php?action=evaluar', {
            method: 'POST',
            body:   new FormData(this)
        })
        .then(r => r.text())
        .then(raw => {
            try {
                const data = JSON.parse(raw);
                if (data.success) {
                    _mEvaluar.hide();
                    mostrarAlerta('success', '✅ Evaluación guardada correctamente');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    mostrarAlerta('danger', '❌ ' + data.message);
                    btn.disabled  = false;
                    btn.innerHTML = '💾 Guardar Evaluación';
                }
            } catch (e) {
                mostrarAlerta('danger', '❌ Error servidor: ' + raw);
                btn.disabled  = false;
                btn.innerHTML = '💾 Guardar Evaluación';
            }
        })
        .catch(err => {
            mostrarAlerta('danger', '❌ Error: ' + err.message);
            btn.disabled  = false;
            btn.innerHTML = '💾 Guardar Evaluación';
        });
    });

    document.getElementById('btnConfirmarGanador').addEventListener('click', function () {
        if (!_ganadorId) return;
        const btn     = this;
        btn.disabled  = true;
        btn.innerHTML = '⏳ Procesando...';

        fetch('procesar_seleccion.php?action=marcar_ganador', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ postulacion_id: _ganadorId })
        })
        .then(r => r.text())
        .then(raw => {
            try {
                const data = JSON.parse(raw);
                if (data.success) {
                    _mGanador.hide();
                    mostrarAlerta('success', '🏆 ¡Ganador marcado exitosamente!');
                    setTimeout(() => location.reload(), 1200);
                } else {
                    mostrarAlerta('danger', '❌ ' + data.message);
                    btn.disabled  = false;
                    btn.innerHTML = '🏆 Sí, Marcar Ganador';
                }
            } catch (e) {
                mostrarAlerta('danger', '❌ Error servidor: ' + raw);
                btn.disabled  = false;
                btn.innerHTML = '🏆 Sí, Marcar Ganador';
            }
        })
        .catch(err => {
            mostrarAlerta('danger', '❌ Error: ' + err.message);
            btn.disabled  = false;
            btn.innerHTML = '🏆 Sí, Marcar Ganador';
        });
    });

    document.getElementById('btnConfirmarFinalizar').addEventListener('click', function () {
        if (!_procesoId) return;
        const btn     = this;
        btn.disabled  = true;
        btn.innerHTML = '⏳ Finalizando...';

        fetch('procesar_seleccion.php?action=finalizar_proceso', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ proceso_id: _procesoId })
        })
        .then(r => r.text())
        .then(raw => {
            try {
                const data = JSON.parse(raw);
                if (data.success) {
                    _mFinalizar.hide();
                    mostrarAlerta('success', '🏆 ' + data.message);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    _mFinalizar.hide();
                    mostrarAlerta('danger', '❌ ' + data.message);
                    btn.disabled  = false;
                    btn.innerHTML = '✅ Sí, Finalizar';
                }
            } catch (e) {
                mostrarAlerta('danger', '❌ Error servidor: ' + raw);
                btn.disabled  = false;
                btn.innerHTML = '✅ Sí, Finalizar';
            }
        })
        .catch(err => {
            mostrarAlerta('danger', '❌ Error: ' + err.message);
            btn.disabled  = false;
            btn.innerHTML = '✅ Sí, Finalizar';
        });
    });

}); 

function abrirEvaluar(id, nombre) {
    document.getElementById('evalId').value             = id;
    document.getElementById('evalNombre').textContent   = nombre;
    document.getElementById('campoPuntaje').value       = '';
    document.getElementById('barraPuntaje').style.width = '0%';
    document.getElementById('barraPuntaje').className   = 'progress-bar';
    document.getElementById('textoPuntaje').textContent = '';
    const btn     = document.getElementById('btnEvaluar');
    btn.disabled  = false;
    btn.innerHTML = '💾 Guardar Evaluación';
    _mEvaluar.show();
}

function abrirGanador(id, nombre) {
    _ganadorId = id;
    document.getElementById('ganadorNombre').textContent = nombre;
    const btn     = document.getElementById('btnConfirmarGanador');
    btn.disabled  = false;
    btn.innerHTML = '🏆 Sí, Marcar Ganador';
    _mGanador.show();
}

function abrirFinalizar(id) {
    _procesoId = id;
    const btn     = document.getElementById('btnConfirmarFinalizar');
    btn.disabled  = false;
    btn.innerHTML = '✅ Sí, Finalizar';
    _mFinalizar.show();
}

function quitarGanador(id) {
    if (!confirm('¿Quitar el estatus de ganador a este candidato?')) return;
    fetch('procesar_seleccion.php?action=quitar_ganador', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ postulacion_id: id })
    })
    .then(r => r.text())
    .then(raw => {
        try {
            const data = JSON.parse(raw);
            if (data.success) {
                mostrarAlerta('warning', '⚠️ Estatus de ganador removido');
                setTimeout(() => location.reload(), 1000);
            } else {
                mostrarAlerta('danger', '❌ ' + data.message);
            }
        } catch (e) {
            mostrarAlerta('danger', '❌ Respuesta inválida: ' + raw);
        }
    })
    .catch(err => mostrarAlerta('danger', '❌ Error: ' + err.message));
}

function mostrarAlerta(tipo, mensaje) {
    const div     = document.getElementById('alertaMsg');
    div.className = `alert alert-${tipo} alert-dismissible fade show rounded-3 shadow-sm`;
    div.innerHTML = `
        <strong>${mensaje}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
    setTimeout(() => {
        div.classList.remove('show');
        div.classList.add('d-none');
    }, 4000);
}
</script>

<?php include '../includes/footer.php'; ?>