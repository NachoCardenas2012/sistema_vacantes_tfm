<?php
$page_title = "Editar Postulación";
include '../includes/header.php';
include '../includes/sidebar.php';

if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: postulaciones.php");
    exit;
}

$id = $_GET['id'];

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

$sql = "
SELECT p.*, u.nombre, u.apellido, v.titulo
FROM postulaciones p
JOIN usuarios u ON u.id = p.usuario_id
JOIN vacantes v ON v.id = p.vacante_id
WHERE p.id = $id
";
$result = $conn->query($sql);
$post = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $estado = $_POST['estado'];

    $stmt = $conn->prepare("UPDATE postulaciones SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: postulaciones.php");
    exit;
}
?>

<div class="content">
    <div class="container">
        <h2 class="my-4">Editar Estado de Postulación</h2>

        <div class="card">
            <div class="card-body">

                <p><strong>Usuario:</strong> <?= $post['nombre'] . " " . $post['apellido'] ?></p>
                <p><strong>Vacante:</strong> <?= $post['titulo'] ?></p>
                <p><strong>Fecha:</strong> <?= $post['fecha_postulacion'] ?></p>

                <form method="POST">
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select mb-3" required>
                        <option value="pendiente" <?= $post['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="aceptado" <?= $post['estado'] == 'aceptado' ? 'selected' : '' ?>>Aceptado</option>
                        <option value="rechazado" <?= $post['estado'] == 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>

            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
