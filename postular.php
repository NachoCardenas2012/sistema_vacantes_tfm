<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Postular a Vacante";

include 'includes/header.php';
include 'includes/sidebar.php';

// Validar sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=" . urlencode("Debe iniciar sesión primero"));
    exit;
}

// Conexión BD
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// ✅ Si viene ?id= mostrar detalle de UNA vacante
if (isset($_GET['id'])) {

    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM vacantes WHERE id = ? AND estado = 'abierta'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vacante = $result->fetch_assoc();

    if (!$vacante) {
        header("Location: postular.php?error=" . urlencode("Vacante no encontrada o no disponible"));
        exit;
    }
?>

<div class="content">
    <div class="container">

        <!-- Botón volver -->
        <a href="postular.php" class="btn btn-secondary mb-4">
            ← Volver a vacantes
        </a>

        <!-- Mensajes -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <!-- Detalle de la vacante -->
        <div class="vacante-card">
            <div class="vacante-titulo">
                <?= htmlspecialchars($vacante['titulo']) ?>
            </div>
            <div class="vacante-fecha">
                📅 <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?>
            </div>
            <div class="vacante-descripcion">
                <?= htmlspecialchars($vacante['descripcion']) ?>
            </div>

            <hr>

            <!-- Formulario postulación -->
            <h5 class="mb-3">📎 Sube tu Hoja de Vida</h5>
            <form action="procesar_postulacion.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="vacante_id" value="<?= $vacante['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Archivo CV <span class="text-danger">*</span></label>
                    <input type="file" 
                           name="hoja_vida" 
                           class="form-control" 
                           accept=".pdf,.doc,.docx" 
                           required>
                    <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX. Máximo 5MB</small>
                </div>
                <button type="submit" class="vacante-btn w-100">
                    🚀 Postularme a esta vacante
                </button>
            </form>
        </div>

    </div>
</div>

<?php

// ✅ Si NO viene ?id= mostrar TODAS las vacantes
} else {

    // Búsqueda
    $keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';

    // Consulta vacantes
    $sql = "SELECT * FROM vacantes 
            WHERE estado = 'abierta'
            AND titulo LIKE '%$keyword%'
            ORDER BY fecha_publicacion DESC";

    $result = $conn->query($sql);
?>

<div class="content">
    <div class="container">

        <h2 class="text-center mb-4">Postular a una Vacante</h2>

        <!-- Buscador -->
        <div class="buscador-principal">
            <form action="postular.php" method="GET">
                <input type="text" 
                       name="keyword" 
                       placeholder="Buscar vacantes..." 
                       value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Mensajes -->
        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <!-- Grid de vacantes -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="vacantes-grid">
                <?php while ($vacante = $result->fetch_assoc()): ?>
                    <div class="vacante-card">
                        <div>
                            <div class="vacante-titulo">
                                <?= htmlspecialchars($vacante['titulo']) ?>
                            </div>
                            <div class="vacante-fecha">
                                📅 <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?>
                            </div>
                            <div class="vacante-descripcion">
                                <?= htmlspecialchars($vacante['descripcion']) ?>
                            </div>
                        </div>

                        <!-- ✅ Botón que lleva al detalle de la vacante -->
                        <a href="postular.php?id=<?= $vacante['id'] ?>" 
                           class="vacante-btn w-100 text-center text-decoration-none d-block">
                            Ver y Postularme →
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="alert alert-warning text-center">
                <p>No se encontraron vacantes disponibles.</p>
                <a href="postular.php" class="btn btn-primary mt-3">
                    Ver todas las vacantes
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php } ?>

<?php
$conn->close();
include 'includes/footer.php';
?>