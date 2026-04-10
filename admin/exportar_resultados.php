<?php
session_start();
ob_start();

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    ob_end_clean();
    header("Location: ../login.php");
    exit;
}

$vacante_id = intval($_GET['vacante_id'] ?? 0);

if ($vacante_id <= 0) {
    ob_end_clean();
    header("Location: vacantes.php?error=Vacante no especificada");
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    ob_end_clean();
    die("Error BD: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Vacante
$stmt = $conn->prepare("SELECT * FROM vacantes WHERE id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$vacante = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vacante) {
    ob_end_clean();
    header("Location: vacantes.php?error=Vacante no encontrada");
    exit;
}

// Postulaciones
$stmt = $conn->prepare("
    SELECT 
        u.nombre,
        u.apellido,
        u.email,
        p.fecha_postulacion,
        p.puntaje,
        p.estado,
        p.es_ganador,
        p.comentario_seleccion,
        p.fecha_evaluacion,
        p.fecha_seleccion
    FROM postulaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.vacante_id = ?
    ORDER BY p.puntaje DESC
");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$postulaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Proceso
$stmt = $conn->prepare("SELECT * FROM proceso_seleccion WHERE vacante_id = ?");
$stmt->bind_param("i", $vacante_id);
$stmt->execute();
$proceso = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conn->close();

// ✅ LIMPIAR BUFFER Y ENVIAR CSV
ob_end_clean();

$fecha    = date('Y-m-d');
$titulo   = preg_replace('/[^a-zA-Z0-9_]/', '_', $vacante['titulo']);
$filename = "resultados_{$titulo}_{$fecha}.csv";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// BOM para Excel con tildes
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// ===== ENCABEZADO =====
fputcsv($output, ['REPORTE DE PROCESO DE SELECCION'], ';');
fputcsv($output, [''], ';');
fputcsv($output, ['Vacante:'          , $vacante['titulo']], ';');
fputcsv($output, ['Estado Vacante:'   , strtoupper($vacante['estado'])], ';');
fputcsv($output, ['Fecha Exportacion:', date('d/m/Y H:i')], ';');
fputcsv($output, ['Total Postulantes:', count($postulaciones)], ';');

if ($proceso) {
    fputcsv($output, ['Estado Proceso:', ucfirst($proceso['estado'])], ';');
    fputcsv($output, [
        'Fecha Inicio:',
        $proceso['fecha_inicio']
            ? date('d/m/Y H:i', strtotime($proceso['fecha_inicio']))
            : 'N/A'
    ], ';');
    fputcsv($output, [
        'Fecha Cierre:',
        $proceso['fecha_cierre']
            ? date('d/m/Y H:i', strtotime($proceso['fecha_cierre']))
            : 'En proceso'
    ], ';');
    fputcsv($output, [
        'Criterios:',
        $proceso['criterios_seleccion'] ?? 'N/A'
    ], ';');
}

fputcsv($output, [''], ';');

// ===== ENCABEZADOS TABLA =====
fputcsv($output, [
    '#',
    'Nombre',
    'Apellido',
    'Email',
    'Fecha Postulacion',
    'Puntaje',
    'Estado',
    'Ganador',
    'Fecha Evaluacion',
    'Fecha Seleccion',
    'Comentario'
], ';');

// ===== FILAS =====
foreach ($postulaciones as $i => $p) {
    fputcsv($output, [
        $i + 1,
        $p['nombre'],
        $p['apellido'],
        $p['email'],
        $p['fecha_postulacion']
            ? date('d/m/Y H:i', strtotime($p['fecha_postulacion']))
            : 'N/A',
        number_format($p['puntaje'] ?? 0, 1) . '/10',
        strtoupper($p['estado']),
        $p['es_ganador'] ? 'SI - GANADOR' : 'NO',
        $p['fecha_evaluacion']
            ? date('d/m/Y H:i', strtotime($p['fecha_evaluacion']))
            : 'Sin evaluar',
        $p['fecha_seleccion']
            ? date('d/m/Y H:i', strtotime($p['fecha_seleccion']))
            : 'N/A',
        $p['comentario_seleccion'] ?? ''
    ], ';');
}

// ===== GANADOR DESTACADO =====
$ganadores = array_filter($postulaciones, fn($p) => $p['es_ganador']);

if (!empty($ganadores)) {
    $g = reset($ganadores);
    fputcsv($output, [''], ';');
    fputcsv($output, [''], ';');
    fputcsv($output, ['=== CANDIDATO SELECCIONADO ==='], ';');
    fputcsv($output, ['Nombre:'          , $g['nombre'] . ' ' . $g['apellido']], ';');
    fputcsv($output, ['Email:'           , $g['email']], ';');
    fputcsv($output, ['Puntaje:'         , number_format($g['puntaje'] ?? 0, 1) . '/10'], ';');
    fputcsv($output, [
        'Fecha Seleccion:',
        $g['fecha_seleccion']
            ? date('d/m/Y H:i', strtotime($g['fecha_seleccion']))
            : 'N/A'
    ], ';');
}

fclose($output);
exit;
?>