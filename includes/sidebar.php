<?php if (isset($_SESSION['id'])): ?>

<aside class="sidebar">

    <h5 class="sidebar-title">Sistema Vacantes</h5>

    <a href="/sistema_vacantes/home.php" class="sidebar-link">🏠 Inicio</a>
    <a href="/sistema_vacantes/postular.php" class="sidebar-link">📝 Postular</a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>

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
           📝 Postulaciones
        </a>

    <?php endif; ?>

    <a href="/sistema_vacantes/logout.php" class="sidebar-link text-danger">
        🚪 Cerrar Sesión
    </a>

</aside>

<?php endif; ?>
