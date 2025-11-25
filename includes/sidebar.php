<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="sidebar">
    <a href="/sistema_vacantes/home.php">🏠 Inicio</a>
    <a href="/sistema_vacantes/postular.php">📝 Postular</a>

    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
        <a href="/sistema_vacantes/solo_admin.php">🔐 Panel Admin</a>
    <?php endif; ?>

    <a href="/sistema_vacantes/cerrar_sesion.php">🚪 Cerrar Sesión</a>
</div>


<div class="content">
