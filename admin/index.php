<?php
session_start();

// ✅ Zona horaria correcta - Ecuador
date_default_timezone_set('America/Guayaquil');

// -----------------------------
// Validación de acceso
// -----------------------------
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php?error=Debe iniciar sesión primero");
    exit;
}

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php?error=No tiene permisos para acceder a esta sección");
    exit;
}

// -----------------------------
// Configuración y conexiones
// -----------------------------
$page_title = "Dashboard Administrativo";

include '../includes/header.php';
include '../includes/sidebar.php';

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// -----------------------------
// Estadísticas Básicas
// -----------------------------
$usuarios_count              = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$vacantes_activas_count      = $conn->query("SELECT COUNT(*) AS total FROM vacantes WHERE estado = 'abierta'")->fetch_assoc()['total'];
$vacantes_total_count        = $conn->query("SELECT COUNT(*) AS total FROM vacantes")->fetch_assoc()['total'];
$postulaciones_count         = $conn->query("SELECT COUNT(*) AS total FROM postulaciones")->fetch_assoc()['total'];
$departamentos_count         = $conn->query("SELECT COUNT(DISTINCT departamento) AS total FROM vacantes WHERE departamento IS NOT NULL AND departamento != ''")->fetch_assoc()['total'];

// -----------------------------
// Estadísticas Avanzadas
// -----------------------------

// 1. Usuarios por mes (últimos 6 meses)
$usuarios_por_mes = [];
$result_usuarios_mes = $conn->query("
    SELECT DATE_FORMAT(fecha_registro, '%Y-%m') as mes, COUNT(*) as total
    FROM usuarios 
    WHERE fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
    ORDER BY mes
");
while ($row = $result_usuarios_mes->fetch_assoc()) {
    $usuarios_por_mes[] = $row;
}

// 2. Vacantes por estado
$vacantes_por_estado = ['abierta' => 0, 'cerrada' => 0, 'pausada' => 0];
$result_vacantes_estado = $conn->query("SELECT estado, COUNT(*) AS total FROM vacantes WHERE estado IS NOT NULL GROUP BY estado");
while ($row = $result_vacantes_estado->fetch_assoc()) {
    $vacantes_por_estado[$row['estado']] = $row['total'];
}

// 3. Postulaciones por estado
$postulaciones_por_estado = ['pendiente' => 0, 'aprobado' => 0, 'rechazado' => 0];
$result_postulaciones_estado = $conn->query("SELECT estado, COUNT(*) AS total FROM postulaciones GROUP BY estado");
while ($row = $result_postulaciones_estado->fetch_assoc()) {
    $postulaciones_por_estado[$row['estado']] = $row['total'];
}

// 4. Top 5 vacantes con más postulaciones
$top_vacantes = [];
$result_top_vacantes = $conn->query("
    SELECT v.titulo, COUNT(p.id) as total_postulaciones
    FROM vacantes v
    LEFT JOIN postulaciones p ON v.id = p.vacante_id
    GROUP BY v.id, v.titulo
    ORDER BY total_postulaciones DESC
    LIMIT 5
");
while ($row = $result_top_vacantes->fetch_assoc()) {
    $top_vacantes[] = $row;
}

// 5. Postulaciones por día (última semana)
$postulaciones_semana = [];
$result_postulaciones_semana = $conn->query("
    SELECT DATE(fecha_postulacion) as dia, COUNT(*) as total
    FROM postulaciones 
    WHERE fecha_postulacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(fecha_postulacion)
    ORDER BY dia
");
while ($row = $result_postulaciones_semana->fetch_assoc()) {
    $postulaciones_semana[] = $row;
}

// 6. Usuarios por rol
$usuarios_por_rol = ['admin' => 0, 'empleado' => 0];
$result_usuarios_rol = $conn->query("SELECT rol, COUNT(*) AS total FROM usuarios WHERE rol IS NOT NULL GROUP BY rol");
while ($row = $result_usuarios_rol->fetch_assoc()) {
    $usuarios_por_rol[$row['rol']] = $row['total'];
}

// 7. Vacantes por departamento (top 5)
$vacantes_por_departamento = [];
$result_vacantes_departamento = $conn->query("
    SELECT departamento, COUNT(*) as total
    FROM vacantes 
    WHERE departamento IS NOT NULL AND departamento != ''
    GROUP BY departamento
    ORDER BY total DESC
    LIMIT 5
");
while ($row = $result_vacantes_departamento->fetch_assoc()) {
    $vacantes_por_departamento[] = $row;
}

// 8. Métricas de rendimiento
$promedio_postulaciones_por_vacante = $conn->query("
    SELECT ROUND(AVG(postulaciones_count), 1) as promedio
    FROM (
        SELECT v.id, COUNT(p.id) as postulaciones_count
        FROM vacantes v
        LEFT JOIN postulaciones p ON v.id = p.vacante_id
        GROUP BY v.id
    ) as subquery
")->fetch_assoc()['promedio'] ?? 0;

$tasa_aceptacion = 0;
if ($postulaciones_count > 0) {
    $aceptadas        = $postulaciones_por_estado['aprobado'] ?? 0;
    $tasa_aceptacion  = round(($aceptadas / $postulaciones_count) * 100, 1);
}

$usuarios_mes_actual = $conn->query("
    SELECT COUNT(*) as total FROM usuarios 
    WHERE MONTH(fecha_registro) = MONTH(CURDATE()) 
    AND YEAR(fecha_registro) = YEAR(CURDATE())
")->fetch_assoc()['total'] ?? 0;

$usuarios_mes_anterior = $conn->query("
    SELECT COUNT(*) as total FROM usuarios 
    WHERE MONTH(fecha_registro) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
    AND YEAR(fecha_registro) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
")->fetch_assoc()['total'] ?? 0;

$crecimiento_usuarios = 0;
if ($usuarios_mes_anterior > 0) {
    $crecimiento_usuarios = round((($usuarios_mes_actual - $usuarios_mes_anterior) / $usuarios_mes_anterior) * 100, 1);
}

$conn->close();
?>

<div class="content">
    <div class="container-fluid">

        <!-- Header del Dashboard -->
        <div class="dashboard-header fade-in">
            <div class="header-content">
                <div class="logo-section">
                    <img src="../assets/logo.jpeg" 
                         alt="Logo" 
                         class="company-logo" 
                         onerror="this.style.display='none'">
                    <div class="company-info">
                        <h1>Sistema de Gestión CM</h1>
                        <p class="company-subtitle">Panel de Administración</p>
                    </div>
                </div>
                <div class="user-welcome">
                    <h2>¡Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?>!</h2>
                    <div class="role-badge role-admin">
                        🛡️ Administrador
                    </div>
                    <!-- ✅ Fecha y hora corregida -->
                    <p class="last-access">
                        🕐 Último acceso: <?= date('d/m/Y H:i') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="stats-section">
            <div class="stats-grid">

                <div class="stat-card stat-primary slide-up">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $usuarios_count ?></div>
                            <div class="stat-label">Usuarios Totales</div>
                            <?php if ($crecimiento_usuarios != 0): ?>
                                <div class="stat-change <?= $crecimiento_usuarios > 0 ? 'positive' : 'negative' ?>">
                                    <?= $crecimiento_usuarios > 0 ? '▲' : '▼' ?>
                                    <?= abs($crecimiento_usuarios) ?>% vs mes anterior
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-footer">
                        <a href="usuarios.php" class="stat-link">Ver todos los usuarios</a>
                        <span><?= $usuarios_mes_actual ?> este mes</span>
                    </div>
                </div>

                <div class="stat-card stat-success slide-up">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $vacantes_activas_count ?></div>
                            <div class="stat-label">Vacantes Activas</div>
                        </div>
                        <div class="stat-icon">💼</div>
                    </div>
                    <div class="stat-footer">
                        <a href="vacantes.php" class="stat-link">Gestionar vacantes</a>
                        <span><?= $vacantes_total_count ?> en total</span>
                    </div>
                </div>

                <div class="stat-card stat-info slide-up">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $postulaciones_count ?></div>
                            <div class="stat-label">Postulaciones</div>
                        </div>
                        <div class="stat-icon">📄</div>
                    </div>
                    <div class="stat-footer">
                        <a href="postulaciones.php" class="stat-link">Ver postulaciones</a>
                        <span><?= $tasa_aceptacion ?>% tasa de aceptación</span>
                    </div>
                </div>

                <div class="stat-card stat-warning slide-up">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $promedio_postulaciones_por_vacante ?></div>
                            <div class="stat-label">Promedio por Vacante</div>
                        </div>
                        <div class="stat-icon">📊</div>
                    </div>
                    <div class="stat-footer">
                        <span class="stat-link">Métrica de rendimiento</span>
                        <span><?= $departamentos_count ?> departamentos</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Gráficos Principales -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📊 Postulaciones por Estado</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="postulacionesEstadoChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">💼 Vacantes por Estado</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="vacantesEstadoChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Secundarios -->
        <div class="row mb-5">
            <div class="col-lg-8 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📈 Crecimiento de Usuarios (Últimos 6 meses)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="usuariosMesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">👥 Usuarios por Rol</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="usuariosRolChart" style="max-height: 250px;"></canvas>
                        <div class="mt-3">
                            <?php foreach ($usuarios_por_rol as $rol => $count): ?>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-capitalize"><?= htmlspecialchars($rol) ?></small>
                                    <small class="fw-bold"><?= $count ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Análisis Avanzado -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🏆 Top 5 Vacantes Más Populares</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="topVacantesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">🏢 Vacantes por Departamento</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="departamentosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card fade-in">
                    <div class="card-header">
                        <h5 class="card-title mb-0">📅 Postulaciones de la Última Semana</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="postulacionesSemanaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs -->
        <div class="stats-section">
            <h3 class="section-title">📊 Indicadores Clave de Rendimiento</h3>
            <div class="stats-grid-small">

                <div class="stat-item stat-success">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $tasa_aceptacion ?>%</div>
                            <div class="stat-label">Tasa de Aceptación</div>
                        </div>
                    </div>
                </div>

                <div class="stat-item stat-info">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $promedio_postulaciones_por_vacante ?></div>
                            <div class="stat-label">Promedio Postulaciones/Vacante</div>
                        </div>
                    </div>
                </div>

                <div class="stat-item stat-warning">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $departamentos_count ?></div>
                            <div class="stat-label">Departamentos Activos</div>
                        </div>
                    </div>
                </div>

                <div class="stat-item stat-primary">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $usuarios_mes_actual ?></div>
                            <div class="stat-label">Nuevos Usuarios (Este Mes)</div>
                        </div>
                    </div>
                </div>

                <div class="stat-item stat-danger">
                    <div class="stat-content">
                        <div class="stat-data">
                            <div class="stat-number"><?= $postulaciones_por_estado['rechazado'] ?? 0 ?></div>
                            <div class="stat-label">Postulaciones Rechazadas</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const colors = {
    primary:   '#667eea',
    secondary: '#764ba2',
    success:   '#48bb78',
    warning:   '#ed8936',
    danger:    '#f56565',
    info:      '#4299e1',
    gray:      '#6b7280'
};

// 1. Postulaciones por Estado
new Chart(document.getElementById('postulacionesEstadoChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pendiente', 'Aprobado', 'Rechazado'],
        datasets: [{
            data: [
                <?= $postulaciones_por_estado['pendiente'] ?>,
                <?= $postulaciones_por_estado['aprobado']  ?>,
                <?= $postulaciones_por_estado['rechazado'] ?>
            ],
            backgroundColor: [colors.warning, colors.success, colors.danger],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
        }
    }
});

// 2. Vacantes por Estado
new Chart(document.getElementById('vacantesEstadoChart'), {
    type: 'pie',
    data: {
        labels: ['Abiertas', 'Cerradas', 'Pausadas'],
        datasets: [{
            data: [
                <?= $vacantes_por_estado['abierta']  ?>,
                <?= $vacantes_por_estado['cerrada']  ?>,
                <?= $vacantes_por_estado['pausada']  ?>
            ],
            backgroundColor: [colors.success, colors.danger, colors.gray],
            borderWidth: 2,
            borderColor: '#ffffff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
        }
    }
});

// 3. Usuarios por Mes
new Chart(document.getElementById('usuariosMesChart'), {
    type: 'line',
    data: {
        labels: [<?= '"' . implode('","', array_column($usuarios_por_mes, 'mes')) . '"' ?>],
        datasets: [{
            label: 'Nuevos Usuarios',
            data: [<?= implode(',', array_column($usuarios_por_mes, 'total')) ?>],
            borderColor: colors.primary,
            backgroundColor: colors.primary + '20',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.primary,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 6
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { display: false } }
    }
});

// 4. Usuarios por Rol
new Chart(document.getElementById('usuariosRolChart'), {
    type: 'doughnut',
    data: {
        labels: [<?= '"' . implode('","', array_keys($usuarios_por_rol)) . '"' ?>],
        datasets: [{
            data: [<?= implode(',', array_values($usuarios_por_rol)) ?>],
            backgroundColor: [colors.danger, colors.primary],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '60%'
    }
});

// 5. Top Vacantes
new Chart(document.getElementById('topVacantesChart'), {
    type: 'bar',
    data: {
        labels: [<?= '"' . implode('","', array_column($top_vacantes, 'titulo')) . '"' ?>],
        datasets: [{
            label: 'Postulaciones',
            data: [<?= implode(',', array_column($top_vacantes, 'total_postulaciones')) ?>],
            backgroundColor: [colors.primary, colors.secondary, colors.success, colors.warning, colors.info],
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 6. Departamentos
new Chart(document.getElementById('departamentosChart'), {
    type: 'bar',
    data: {
        labels: [<?= '"' . implode('","', array_column($vacantes_por_departamento, 'departamento')) . '"' ?>],
        datasets: [{
            label: 'Vacantes',
            data: [<?= implode(',', array_column($vacantes_por_departamento, 'total')) ?>],
            backgroundColor: colors.info,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// 7. Postulaciones Semana
new Chart(document.getElementById('postulacionesSemanaChart'), {
    type: 'line',
    data: {
        labels: [<?= '"' . implode('","', array_column($postulaciones_semana, 'dia')) . '"' ?>],
        datasets: [{
            label: 'Postulaciones por Día',
            data: [<?= implode(',', array_column($postulaciones_semana, 'total')) ?>],
            borderColor: colors.success,
            backgroundColor: colors.success + '30',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.success,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        plugins: { legend: { display: false } }
    }
});
</script>

<?php include '../includes/footer.php'; ?>