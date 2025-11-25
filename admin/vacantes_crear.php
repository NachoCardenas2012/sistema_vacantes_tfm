<?php
$page_title = "Crear Vacante";
include '../includes/header.php';
include '../includes/sidebar.php';

// Validar rol admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: ../home.php");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $requisitos = $_POST['requisitos'];
    $departamento = $_POST['departamento'];
    $estado = $_POST['estado'];

    $stmt = $conn->prepare("
        INSERT INTO vacantes (titulo, descripcion, requisitos, departamento, fecha_publicacion, estado)
        VALUES (?, ?, ?, ?, NOW(), ?)
    ");

    $stmt->bind_param("sssss", $titulo, $descripcion, $requisitos, $departamento, $estado);
    $stmt->execute();
    $stmt->close();

    header("Location: vacantes.php?mensaje=creado");
    exit;
}
?>

<div class="content">
    <div class="container">

        <h2 class="my-4">Crear Nueva Vacante</h2>

        <div class="card shadow">
            <div class="card-body">

                <form method="POST">

                    <!-- Titulo -->
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                    </div>

                    <!-- Requisitos -->
                    <div class="mb-3">
                        <label class="form-label">Requisitos</label>
                        <textarea name="requisitos" class="form-control" rows="4" required></textarea>
                    </div>

                    <!-- Departamento -->
                    <div class="mb-3">
                        <label class="form-label">Departamento</label>
                        <input type="text" name="departamento" class="form-control" required>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="abierta">Abierta</option>
                            <option value="cerrada">Cerrada</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Crear Vacante</button>

                </form>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
