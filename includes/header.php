<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($page_title) ? $page_title : "Intranet XYZ" ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            height: 100vh;
            width: 230px;
            position: fixed;
            top: 0;
            left: 0;
            background: #212529;
            padding-top: 20px;
        }
        .sidebar a {
            color: #ddd;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
            color: #fff;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>

<body>

<?php if (!isset($no_sidebar)): ?>
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="home.php"><strong>Sistema Vacantes XYZ</strong></a>

    <div>
        <?php if (isset($_SESSION['id'])): ?>
            <span class="text-white me-3">👤 <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <a href="cerrar_sesion.php" class="btn btn-danger btn-sm">Salir</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
