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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/sistema_vacantes/assets/css/app_nuevo.css">
</head>

<body class="<?= (isset($no_sidebar) && $no_sidebar) ? 'no-sidebar' : '' ?>">

<!-- ===== NAVBAR ===== -->

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid d-flex justify-content-center align-items-center position-relative px-3">

        <!-- Título centrado -->
        <strong class="text-white">
            Postulación de Vacantes Consultoría CM
        </strong>

        <!-- Salir: SOLO si hay sesión iniciada -->
        <?php if (isset($_SESSION['id'])): ?>
            <div class="d-flex align-items-center position-absolute end-0 me-3 gap-2">
                <span class="text-white d-none d-md-inline" style="font-size: 0.9rem;">
                    👤 <?= htmlspecialchars($_SESSION['nombre'] ?? '') ?>
                </span>
                <a href="/sistema_vacantes/logout.php" class="btn btn-danger btn-sm rounded-pill px-3">
                    🚪 Salir
                </a>
            </div>
        <?php endif; ?>

    </div>
</nav>

<!-- ===== SIDEBAR: Solo si hay sesión Y no es página sin sidebar ===== -->
<?php if (isset($_SESSION['id']) && !(isset($no_sidebar) && $no_sidebar)): ?>
<aside class="sidebar">
    <h5 class="sidebar-title">Sistema Vacantes</h5>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>

        <!-- ADMIN -->
        <a href="/sistema_vacantes/home.php"
           class="sidebar-link <?= ($page_title === 'Home' ? 'active' : '') ?>">
           🏠 Inicio
        </a>
        <a href="/sistema_vacantes/postular.php"
           class="sidebar-link <?= ($page_title === 'Postular a Vacante' ? 'active' : '') ?>">
           📝 Postular
        </a>
        <a href="/sistema_vacantes/admin/index.php"
           class="sidebar-link <?= ($page_title === 'Dashboard Administrativo' ? 'active' : '') ?>">
           📊 Dashboard
        </a>
        <a href="/sistema_vacantes/admin/usuarios.php"
           class="sidebar-link <?= ($page_title === 'Gestionar Usuarios' ? 'active' : '') ?>">
           👥 Usuarios
        </a>
        <a href="/sistema_vacantes/admin/vacantes.php"
           class="sidebar-link <?= ($page_title === 'Gestionar Vacantes' ? 'active' : '') ?>">
           💼 Vacantes
        </a>
        <a href="/sistema_vacantes/admin/postulaciones.php"
           class="sidebar-link <?= ($page_title === 'Gestionar Postulaciones' ? 'active' : '') ?>">
           📋 Postulaciones
        </a>

    <?php else: ?>

        <!-- USUARIO NORMAL -->
        <a href="/sistema_vacantes/home.php"
           class="sidebar-link <?= ($page_title === 'Home' ? 'active' : '') ?>">
           🏠 Inicio
        </a>
        <a href="/sistema_vacantes/postular.php"
           class="sidebar-link <?= ($page_title === 'Postular a Vacante' ? 'active' : '') ?>">
           📝 Postularme
        </a>

    <?php endif; ?>

    <!-- Cerrar sesión siempre visible -->
    <a href="/sistema_vacantes/logout.php" class="sidebar-link" style="color:#e55353; font-weight:bold; margin-top: auto;">
        🚪 Cerrar Sesión
    </a>

</aside>
<?php endif; ?>