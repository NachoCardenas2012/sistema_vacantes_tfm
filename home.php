<?php

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php?error=Debe iniciar sesión primero");
    exit;
}


$page_title = "Home";
include 'includes/header.php';
include 'conexion.php';

// Paginación
$vacantesPorPagina = 6;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina = max($pagina, 1);
$inicio = ($pagina - 1) * $vacantesPorPagina;

// Búsqueda
$keyword = isset($_GET['keyword']) ? $conexion->real_escape_string($_GET['keyword']) : '';
?>

<div class="content">
    <div class="container">

        <!-- Logo -->
        <div class="logo-header">
            <img src="assets/logo.jpeg" alt="Logo Consultoría CM">
            <h1>Consultoría CM</h1>
        </div>

        <!-- Welcome -->
        <div class="welcome-card">
            <h2>¡Bienvenido <?= htmlspecialchars($_SESSION['nombre']) ?>!</h2>
            <p class="lead">Explora las vacantes y candidatos disponibles</p>
        </div>

        <!-- Opciones -->
        <div class="home-options">
            <a href="postular.php" class="option-card">
                <h2>💼 Busco empleo</h2>
                <p>Explora las vacantes disponibles y postúlate a las que más te interesen.</p>
            </a>

            <a href="candidatos.php" class="option-card">
                <h2>🧑‍💼 Busco candidatos</h2>
                <p>Accede a los perfiles de candidatos que se han postulado.</p>
            </a>
        </div>

        <!-- Buscador -->
        <div class="buscador-principal">
            <form action="home.php" method="GET" style="width:100%; display:flex; flex-wrap:wrap; gap:10px; justify-content:center;">
                <input type="text" name="keyword" placeholder="Buscar vacantes..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>

        <!-- Listado de vacantes -->
        <div class="listado-vacantes">
            <?php
            $sql = "SELECT * FROM vacantes 
                    WHERE titulo LIKE '%$keyword%' 
                    ORDER BY fecha_publicacion DESC 
                    LIMIT $inicio, $vacantesPorPagina";
            $result = $conexion->query($sql);

            if ($result && $result->num_rows > 0):
                while ($vacante = $result->fetch_assoc()):
            ?>
                <a href="postular.php?id=<?= $vacante['id'] ?>" class="vacante-item">
                    <div>
                        <div class="vacante-titulo"><?= htmlspecialchars($vacante['titulo']) ?></div>
                        <div class="vacante-meta">
                            <span>📅 <?= date('d/m/Y', strtotime($vacante['fecha_publicacion'])) ?></span>
                        </div>
                        <div class="vacante-descripcion"><?= nl2br(htmlspecialchars($vacante['descripcion'])) ?></div>
                    </div>
                    <span class="vacante-btn">Postular</span>
                </a>
            <?php
                endwhile;
            else:
                echo "<p>No se encontraron vacantes.</p>";
            endif;
            ?>
        </div>

        <!-- Paginación -->
        <?php
        $sqlTotal = "SELECT COUNT(*) AS total FROM vacantes WHERE titulo LIKE '%$keyword%'";
        $totalResult = $conexion->query($sqlTotal);
        $totalVacantes = $totalResult->fetch_assoc()['total'];
        $totalPaginas = ceil($totalVacantes / $vacantesPorPagina);

        if ($totalPaginas > 1):
        ?>
            <div class="paginacion">
                <?php for ($i=1; $i <= $totalPaginas; $i++): ?>
                    <a href="home.php?keyword=<?= urlencode($keyword) ?>&pagina=<?= $i ?>" class="<?= $i==$pagina?'activo':'' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$conexion->close();
include 'includes/footer.php';
?>
