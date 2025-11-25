<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}

// Verificar si el usuario es administrador
if ($_SESSION['rol'] !== 'admin') {
    header("Location: home.php?error=No tiene permisos para acceder a esta sección");
    exit;
}

$page_title = "Dashboard";
include '../includes/header.php';
include '../includes/sidebar.php';

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

// Obtener estadísticas para el dashboard
$usuarios_count = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$vacantes_count = $conn->query("SELECT COUNT(*) AS total FROM vacantes WHERE estado = 'abierta'")->fetch_assoc()['total'];
$postulaciones_count = $conn->query("SELECT COUNT(*) AS total FROM postulaciones")->fetch_assoc()['total'];

// Obtener postulaciones por estado
$postulaciones_estado = $conn->query("SELECT estado, COUNT(*) AS total FROM postulaciones GROUP BY estado");
$postulaciones_data = [];
while ($row = $postulaciones_estado->fetch_assoc()) {
    $postulaciones_data[$row['estado']] = $row['total'];
}
?>

<div class="content">
    <div class="container">

        <!-- Card Superior -->
        <div class="admin-card text-center">
            <h1>📊 Dashboard de Administración</h1>
            <p class="lead">Visión general de las estadísticas del sistema</p>
        </div>

        <!-- Estadísticas -->
        <div class="row my-4">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios Registrados</h5>
                        <p class="card-text"><?= $usuarios_count ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Vacantes Activas</h5>
                        <p class="card-text"><?= $vacantes_count ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Postulaciones</h5>
                        <p class="card-text"><?= $postulaciones_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Postulaciones por Estado</h5>
                        <canvas id="postulacionesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para el gráfico de postulaciones por estado
    const postulacionesData = {
        labels: ['Pendiente', 'Aceptado', 'Rechazado'],
        datasets: [{
            label: 'Postulaciones por Estado',
            data: [<?= isset($postulaciones_data['pendiente']) ? $postulaciones_data['pendiente'] : 0 ?>, 
                   <?= isset($postulaciones_data['aceptado']) ? $postulaciones_data['aceptado'] : 0 ?>, 
                   <?= isset($postulaciones_data['rechazado']) ? $postulaciones_data['rechazado'] : 0 ?>],
            backgroundColor: ['#ffcc00', '#28a745', '#dc3545'],
            borderColor: ['#ffcc00', '#28a745', '#dc3545'],
            borderWidth: 1
        }]
    };

    // Configuración del gráfico
    const ctx = document.getElementById('postulacionesChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',  // Tipo de gráfico: pastel
        data: postulacionesData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' postulaciones';
                        }
                    }
                }
            }
        }
    });
</script>

<?php include '../includes/footer.php'; ?>
