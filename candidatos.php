<?php
session_start();
$page_title = "Candidatos";
include 'includes/header.php';
include 'conexion.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}

// Opcional: si quieres que solo admin vea todo
// $is_admin = $_SESSION['rol'] === 'admin';
?>

<div class="content">
    <div class="container">
        <h2 class="my-4 text-center">Candidatos Registrados</h2>

        <?php
        // Obtener todos los candidatos con info de vacante
        $sql = "SELECT p.id, u.nombre AS candidato, u.email, p.archivo AS cv, v.titulo AS vacante
                FROM postulaciones p
                INNER JOIN usuarios u ON p.usuario_id = u.id
                INNER JOIN vacantes v ON p.vacante_id = v.id
                ORDER BY p.fecha_postulacion DESC";

        $result = $conexion->query($sql);
        ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['candidato']) ?></h5>
                                <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                                <p><strong>Vacante postulada:</strong> <?= htmlspecialchars($row['vacante']) ?></p>
                                <?php if (!empty($row['cv'])): ?>
                                    <a href="<?= htmlspecialchars($row['cv']) ?>" target="_blank" class="btn btn-sm btn-primary">Ver CV</a>
                                <?php else: ?>
                                    <span class="text-muted">CV no disponible</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No hay candidatos registrados.
            </div>
        <?php endif; ?>

        <!-- Botón para regresar -->
        <div class="text-center my-4">
            <button class="btn btn-secondary" onclick="window.history.back();">
                ← Regresar
            </button>
        </div>

    </div> <!-- Fin container -->
</div> <!-- Fin content -->

<?php
$conexion->close();
include 'includes/footer.php';
?>
