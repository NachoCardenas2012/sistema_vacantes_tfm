<?php
$page_title = "Editar Vacante";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: vacantes.php");
    exit;
}

$id = $_GET['id'];

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

$sql = "SELECT * FROM vacantes WHERE id = $id";
$result = $conn->query($sql);
$vac = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $requisitos = $_POST['requisitos'];
    $departamento = $_POST['departamento'];
    $estado = $_POST['estado'];

    $stmt = $conn->prepare("
        UPDATE vacantes
        SET titulo = ?, descripcion = ?, requisitos = ?, departamento = ?, estado = ?
        WHERE id = ?
    ");

    $stmt->bind_param("sssssi", $titulo, $descripcion, $requisitos, $departamento, $estado, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: vacantes.php?mensaje=editado");
    exit;
}

?>

<div class="content">
    <div class="container">

        <h2 class="my-4">Editar Vacante</h2>

        <div class="card">
            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control"
                               value="<?= htmlspecialchars($vac['titulo']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" required><?= htmlspecialchars($vac['descripcion']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Requisitos</label>
                        <textarea name="requisitos" class="form-control" rows="4" required><?= htmlspecialchars($vac['requisitos']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Departamento</label>
                        <input type="text" name="departamento" class="form-control"
                               value="<?= htmlspecialchars($vac['departamento']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="abierta" <?= $vac['estado'] == 'abierta' ? 'selected' : '' ?>>Abierta</option>
                            <option value="cerrada" <?= $vac['estado'] == 'cerrada' ? 'selected' : '' ?>>Cerrada</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>

                </form>

            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
