<?php
session_start();

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
// Estadísticas
// -----------------------------
$usuarios_count = $conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'];
$vacantes_count = $conn->query("SELECT COUNT(*) AS total FROM vacantes WHERE estado = 'abierta'")->fetch_assoc()['total'];
$postulaciones_count = $conn->query("SELECT COUNT(*) AS total FROM postulaciones")->fetch_assoc()['total'];

// Postulaciones
$postulaciones_estado = $conn->query("SELECT estado, COUNT(*) AS total FROM postulaciones GROUP BY estado");

$postulaciones_data = [
    'pendiente' => 0,
    'aceptado' => 0,
    'rechazado' => 0
];

while ($row = $postulaciones_estado->fetch_assoc()) {
    $postulaciones_data[$row['estado']] = $row['total'];
}

$conn->close();
?>

<div class="content">
    <div class="container">

        <div class="admin-card text-center my-4">
            <h1>📊 Dashboard de Administración</h1>
            <p class="lead">Panel de control general del sistema</p>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row my-4">
            <div class="col-md-4">
                <div class="card shadow text-center">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios Registrados</h5>
                        <p class="card-text fs-3"><?= $usuarios_count ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow text-center">
                    <div class="card-body">
                        <h5 class="card-title">Vacantes Activas</h5>
                        <p class="card-text fs-3"><?= $vacantes_count ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Postulaciones</h5>
                        <p class="card-text fs-3"><?= $postulaciones_count ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
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
const postulacionesData = {
    labels: ['Pendiente', 'Aceptado', 'Rechazado'],
    datasets: [{
        label: 'Postulaciones por Estado',
        data: [
            <?= $postulaciones_data['pendiente'] ?>,
            <?= $postulaciones_data['aceptado'] ?>,
            <?= $postulaciones_data['rechazado'] ?>
        ],
        backgroundColor: ['#ffcc00', '#28a745', '#dc3545'],
        borderWidth: 1
    }]
};

new Chart(document.getElementById('postulacionesChart'), {
    type: 'pie',
    data: postulacionesData,
    options: { responsive: true }
});
</script>

<?php include '../includes/footer.php'; ?>
