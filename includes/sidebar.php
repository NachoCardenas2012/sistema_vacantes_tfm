<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">
    <!-- Menú usuario -->
    <a href="/sistema_vacantes/home.php">🏠 Inicio</a>
    <a href="/sistema_vacantes/postular.php">📝 Postular</a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <!-- Menú administrador -->
        <a href="/sistema_vacantes/admin/index.php" <?= ($page_title == 'Dashboard' ? 'style="font-weight:bold;"' : '') ?>>📊 Dashboard</a>
        <a href="/sistema_vacantes/admin/usuarios.php" <?= ($page_title == 'Usuarios' ? 'style="font-weight:bold;"' : '') ?>>👥 Usuarios</a>
        <a href="/sistema_vacantes/admin/vacantes.php" <?= ($page_title == 'Vacantes' ? 'style="font-weight:bold;"' : '') ?>>💼 Vacantes</a>
        <a href="/sistema_vacantes/admin/postulaciones.php" <?= ($page_title == 'Postulaciones' ? 'style="font-weight:bold;"' : '') ?>>📝 Postulaciones</a>
    <?php endif; ?>

    <a href="/sistema_vacantes/cerrar_sesion.php">🚪 Cerrar Sesión</a>
</div>

<div class="content">
