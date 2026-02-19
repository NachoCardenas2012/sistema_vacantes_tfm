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

// Búsqueda
$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : '';

// Consulta vacantes
$sql = "SELECT * FROM vacantes 
        WHERE estado = 'abierta'
        AND titulo LIKE '%$keyword%'
        ORDER BY fecha_publicacion DESC";

$result = $conn->query($sql);
?>

<style>
/* ===== Buscador ===== */
.buscador-principal {
    background: #fff;
    padding: 20px 25px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    margin-bottom: 35px;
}

.buscador-principal form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
}

.buscador-principal input {
    width: 70%;
    padding: 15px 20px;
    border-radius: 12px;
    border: 1px solid #ccc;
    font-size: 1rem;
}

.buscador-principal input:focus {
    border-color: #667eea;
    outline: none;
}

.buscador-principal button {
    background: #667eea;
    color: #fff;
    border: none;
    padding: 15px 30px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
}

.buscador-principal button:hover {
    background: #564ab1;
}

/* ===== Cards ===== */
.vacantes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 25px;
}

.vacante-card {
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: 0.3s;
}

.vacante-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 35px rgba(0,0,0,0.15);
}

.vacante-titulo {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.vacante-fecha {
    font-size: 0.9rem;
    color: #555;
    margin-bottom: 15px;
}

.vacante-descripcion {
    font-size: 0.95rem;
    color: #666;
    white-space: pre-line;
    margin-bottom: 20px;
}

.vacante-btn {
    background: #28a745;
    color: #fff;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
}

.vacante-btn:hover {
    background: #218838;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .buscador-principal input {
        width: 100%;
    }
}
</style>

<div class="content">
    <div class="container">

        <h2 class="text-center mb-4">Postular a una Vacante</h2>

        <!-- Buscador -->
        <div class="buscador-principal">
            <form action="postular.php" method="GET">
                <input type="text" name="keyword" placeholder="Buscar vacantes..." value="<?= htmlspecialchars($keyword) ?>">
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

        <!-- Vacantes -->
        <?php if ($result && $result->num_rows > 0): ?>
            <div class="vacantes-grid">
                <?php while ($vacante = $result->fetch_assoc()): ?>
                    <div class="vacante-card">
                        <div>
                            <div class="vacante-titulo"><?= htmlspecialchars($vacante['titulo']) ?></div>
                            <div class="vacante-fecha">
                                📅 <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?>
                            </div>
                            <div class="vacante-descripcion">
                                <?= htmlspecialchars($vacante['descripcion']) ?>
                            </div>
                        </div>

                        <form action="procesar_postulacion.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="vacante_id" value="<?= $vacante['id'] ?>">
                            <div class="mb-3">
                                <input type="file" name="hoja_vida" class="form-control" accept=".pdf,.doc,.docx" required>
                            </div>
                            <button type="submit" class="vacante-btn w-100">Postularme</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <p>No se encontraron vacantes que coincidan con tu búsqueda.</p>
                <a href="postular.php" class="btn btn-primary mt-3">Ver todas las vacantes</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$conn->close();
include 'includes/footer.php';
?>
