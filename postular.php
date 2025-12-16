<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Postular a Vacante";

include 'includes/header.php'; // Aquí debe ir session_start() y la apertura del HTML
include 'includes/sidebar.php';

// Validar sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=" . urlencode("Debe iniciar sesión primero"));
    exit;
}

// Conexión BD
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "sistema_vacantes";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener vacantes
$sql = "SELECT * FROM vacantes WHERE estado = 'abierta'";
$result = $conn->query($sql);
?>

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center mb-4">Postular a una Vacante</h3>

            <!-- Mensajes de error -->
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <!-- Mensaje de éxito -->
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <form method="POST" action="procesar_postulacion.php" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="vacante" class="form-label">Selecciona una vacante</label>
                        <select name="vacante_id" id="vacante" class="form-select" required>
                            <option value="">Seleccione una vacante</option>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= htmlspecialchars($row['titulo']) ?> - <?= htmlspecialchars($row['descripcion']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="hoja_vida" class="form-label">Sube tu hoja de vida</label>
                        <input type="file" name="hoja_vida" id="hoja_vida" class="form-control" accept=".pdf, .doc, .docx" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Postularme</button>
                </form>

            <?php else: ?>
                <div class="alert alert-warning text-center">
                    No hay vacantes disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
