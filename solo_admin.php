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

$page_title = "Panel de Administración";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
.admin-card {
    background: linear-gradient(135deg, #0F2027, #203A43, #2C5364);
    color: white;
    padding: 40px;
    border-radius: 15px;
    margin-top: 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
.section-box {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 25px;
}
.btn-admin {
    padding: 14px;
    font-size: 17px;
    font-weight: bold;
}
</style>

<div class="content">
    <div class="container">

        <!-- Card Superior -->
        <div class="admin-card text-center">
            <h1>🔐 Panel de Administración</h1>
            <p class="lead">Gestión completa del sistema</p>
        </div>

        <!-- Opciones Admin -->
        <div class="section-box">
            <h4>⚙️ Opciones del Administrador</h4>
            <hr>

            <div class="d-grid gap-3">

                <!-- Usuarios -->
                <a href="admin/usuarios.php" class="btn btn-primary btn-admin">
                    👥 Gestión de Usuarios
                </a>

                <!-- Vacantes -->
                <a href="admin/vacantes.php" class="btn btn-secondary btn-admin">
                    📄 Gestión de Vacantes
                </a>

                <!-- Postulaciones -->
                <a href="admin/postulaciones.php" class="btn btn-warning btn-admin">
                    📝 Gestión de Postulaciones
                </a>

                <a href="home.php" class="btn btn-outline-dark btn-admin">
                    ⬅ Volver al Home
                </a>

            </div>
        </div>

        <!-- Debug -->
        <div class="alert alert-info mt-4">
            <strong>Debug:</strong><br>
            Rol: <?= $_SESSION['rol'] ?><br>
            Usuario: <?= $_SESSION['nombre'] ?><br>
            ID: <?= $_SESSION['id'] ?>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
