<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Postular a Vacante";
include 'includes/header.php';  // Aquí se llama a session_start()
include 'includes/sidebar.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}

// Conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambiar según tu configuración
$password = "root"; // Cambiar según tu configuración
$dbname = "sistema_vacantes"; // Cambiar según tu base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener las vacantes disponibles desde la base de datos
$sql = "SELECT * FROM vacantes WHERE estado = 'abierta'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postular a Vacante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="text-center mb-4">Postular a una Vacante</h3>
            <?php if (!empty($_GET['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <?php if ($result->num_rows > 0): ?>
                <form method="POST" action="procesar_postulacion.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="vacante" class="form-label">Selecciona una vacante</label>
                        <select name="vacante_id" id="vacante" class="form-select" required>
                            <option value="">Seleccione una vacante</option>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?> - <?= htmlspecialchars($row['descripcion']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="hoja_vida" class="form-label">Sube tu hoja de vida</label>
                        <input type="file" name="hoja_vida" id="hoja_vida" class="form-control" accept=".pdf, .doc, .docx" required>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-success w-100">Postularme</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">
                    No hay vacantes disponibles en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>

<?php include 'includes/footer.php'; ?>
