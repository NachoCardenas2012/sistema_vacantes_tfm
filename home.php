<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}

$page_title = "Home";
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<style>
.welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    margin-top: 30px;
}
.info-card {
    padding: 20px;
    border-radius: 10px;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-top: 20px;
}
</style>

<div class="content">
    <div class="container">

        <div class="welcome-card text-center">
            <h1 class="mb-3">¡Bienvenido al Sistema!</h1>
            <p class="lead mb-0">Has iniciado sesión exitosamente</p>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="info-card">
                    <h5>📋 Información de tu cuenta</h5>
                    <hr>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($_SESSION['nombre']) ?></p>
                    <p><strong>Rol:</strong>
                        <span class="badge bg-primary"><?= htmlspecialchars($_SESSION['rol']) ?></span>
                    </p>
                    <p><strong>ID Usuario:</strong> <?= htmlspecialchars($_SESSION['id']) ?></p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="info-card">
                    <h5>🔧 Acciones disponibles</h5>
                    <hr>

                    <div class="d-grid gap-2">
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <a href="solo_admin.php" class="btn btn-primary">🔐 Panel de Administración</a>
                        <?php endif; ?>

                        <a href="postular.php" class="btn btn-success">📝 Postular a Vacante</a>

                        <a href="cerrar_sesion.php" class="btn btn-outline-danger">🚪 Cerrar Sesión</a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Debug info (puedes eliminar esto después) -->
        <div class="mt-4">
            <div class="alert alert-info">
                <strong>🔍 Debug - Variables de Sesión:</strong><br>
                <small>
                    ID: <?= $_SESSION['id'] ?><br>
                    Nombre: <?= $_SESSION['nombre'] ?><br>
                    Rol: <?= $_SESSION['rol'] ?>
                </small>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>
