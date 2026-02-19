<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : "Consultoría CM" ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="/sistema_vacantes/css/app.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container d-flex justify-content-center align-items-center">
        <!-- Título centrado con letra blanca -->
        <div class="text-center text-white">
            <strong>Postulación de Vacantes Consultoría CM</strong>
        </div>

        <!-- Usuario / Logout -->
        <?php if (isset($_SESSION['id'])): ?>
            <div class="d-flex align-items-center position-absolute end-0 me-3">
                <span class="text-white me-3">
                    👤 <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                </span>
                <a href="/sistema_vacantes/logout.php" class="btn btn-danger btn-sm">
                    Salir
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Sidebar -->
<?php if (isset($_SESSION['id'])): ?>
<aside class="sidebar">
    <h5 class="sidebar-title">Sistema Vacantes</h5>

    <a href="/sistema_vacantes/home.php" class="sidebar-link">🏠 Inicio</a>
    <a href="/sistema_vacantes/postular.php" class="sidebar-link">📝 Postular</a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="/sistema_vacantes/admin/index.php" class="sidebar-link <?= ($page_title === 'Dashboard Administrativo' ? 'active' : '') ?>">📊 Dashboard</a>
        <a href="/sistema_vacantes/admin/usuarios.php" class="sidebar-link <?= ($page_title === 'Gestionar Usuarios' ? 'active' : '') ?>">👥 Usuarios</a>
        <a href="/sistema_vacantes/admin/vacantes.php" class="sidebar-link <?= ($page_title === 'Gestionar Vacantes' ? 'active' : '') ?>">💼 Vacantes</a>
        <a href="/sistema_vacantes/admin/postulaciones.php" class="sidebar-link <?= ($page_title === 'Gestionar Postulaciones' ? 'active' : '') ?>">📝 Postulaciones</a>
    <?php endif; ?>

    <a href="/sistema_vacantes/logout.php" class="sidebar-link">🚪 Cerrar Sesión</a>
</aside>
<?php endif; ?>

<!-- Contenido -->
<div class="content <?= isset($_SESSION['id']) ? '' : 'register-content' ?>">
