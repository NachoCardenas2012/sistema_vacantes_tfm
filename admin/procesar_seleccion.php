<?php
session_start();
ob_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

// ✅ MOSTRAR ERRORES TEMPORALMENTE
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8");

$action = $_GET['action'] ?? '';

ob_end_clean();

switch ($action) {
    case 'iniciar_proceso':   iniciarProceso($conn);   break;
    case 'evaluar':           evaluarPostulante($conn); break;
    case 'marcar_ganador':    marcarGanador($conn);     break;
    case 'quitar_ganador':    quitarGanador($conn);     break;
    case 'finalizar_proceso': finalizarProceso($conn);  break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida: ' . htmlspecialchars($action)
        ]);
}

$conn->close();

/* ============================================================
   INICIAR PROCESO
   ============================================================ */
function iniciarProceso($conn) {
    $vacante_id          = intval($_POST['vacante_id']          ?? 0);
    $numero_ganadores    = intval($_POST['numero_ganadores']    ?? 1);
    $criterios_seleccion = trim($_POST['criterios_seleccion']   ?? '');
    $admin_id            = $_SESSION['id'];

    if ($vacante_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de vacante inválido']);
        return;
    }

    // Verificar si ya existe proceso
    $stmt = $conn->prepare("SELECT id FROM proceso_seleccion WHERE vacante_id = ?");
    $stmt->bind_param("i", $vacante_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Ya existe un proceso para esta vacante']);
        return;
    }
    $stmt->close();

    // Insertar proceso
    $stmt = $conn->prepare("
        INSERT INTO proceso_seleccion 
            (vacante_id, numero_ganadores, criterios_seleccion, admin_id, estado, fecha_inicio) 
        VALUES (?, ?, ?, ?, 'abierto', NOW())
    ");
    $stmt->bind_param("iisi", $vacante_id, $numero_ganadores, $criterios_seleccion, $admin_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Proceso iniciado correctamente. Ahora evalúa a los candidatos.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $stmt->error]);
    }
    $stmt->close();
}

/* ============================================================
   EVALUAR POSTULANTE
   ============================================================ */
function evaluarPostulante($conn) {
    $postulacion_id = intval($_POST['postulacion_id'] ?? 0);
    $puntaje        = floatval($_POST['puntaje']       ?? 0);
    $comentario     = trim($_POST['comentario']        ?? '');

    if ($postulacion_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }

    if ($puntaje < 0 || $puntaje > 10) {
        echo json_encode(['success' => false, 'message' => 'Puntaje debe ser 0-10']);
        return;
    }

    // ✅ fecha_evaluacion = NOW() DEBE ESTAR AQUÍ
    $stmt = $conn->prepare("
        UPDATE postulaciones 
        SET puntaje              = ?, 
            comentario_seleccion = ?,
            estado               = 'aprobado',
            fecha_evaluacion     = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("dsi", $puntaje, $comentario, $postulacion_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Evaluación guardada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No se encontró la postulación ID: ' . $postulacion_id
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error SQL: ' . $stmt->error
        ]);
    }
    $stmt->close();
}

/* ============================================================
   MARCAR GANADOR MANUAL
   ============================================================ */
function marcarGanador($conn) {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true);

    if (!$input || !isset($input['postulacion_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos: ' . $rawInput
        ]);
        return;
    }

    $postulacion_id = intval($input['postulacion_id']);

    if ($postulacion_id <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de postulación inválido: ' . $postulacion_id
        ]);
        return;
    }

    // Verificar que existe
    $stmt = $conn->prepare("SELECT id FROM postulaciones WHERE id = ?");
    $stmt->bind_param("i", $postulacion_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode([
            'success' => false,
            'message' => 'Postulación no encontrada ID: ' . $postulacion_id
        ]);
        return;
    }
    $stmt->close();

    // Marcar como ganador
    $stmt = $conn->prepare("
        UPDATE postulaciones 
        SET es_ganador      = 1, 
            fecha_seleccion = NOW(), 
            estado          = 'aceptada' 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $postulacion_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => '¡Ganador marcado exitosamente!'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $stmt->error]);
    }
    $stmt->close();
}

/* ============================================================
   QUITAR GANADOR
   ============================================================ */
function quitarGanador($conn) {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true);

    if (!$input || !isset($input['postulacion_id'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }

    $postulacion_id = intval($input['postulacion_id']);

    $stmt = $conn->prepare("
        UPDATE postulaciones 
        SET es_ganador      = 0, 
            fecha_seleccion = NULL,
            estado          = 'aprobado'
        WHERE id = ?
    ");
    $stmt->bind_param("i", $postulacion_id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Estatus de ganador removido correctamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $stmt->error]);
    }
    $stmt->close();
}

/* ============================================================
   FINALIZAR PROCESO — AUTO SELECCIONA GANADOR POR MAYOR PUNTAJE
   ============================================================ */
function finalizarProceso($conn) {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true);

    if (!$input || !isset($input['proceso_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos: ' . $rawInput
        ]);
        return;
    }

    $proceso_id = intval($input['proceso_id']);

    if ($proceso_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID proceso inválido: ' . $proceso_id]);
        return;
    }

    // 1. Obtener datos del proceso
    $stmt = $conn->prepare("
        SELECT vacante_id, numero_ganadores 
        FROM proceso_seleccion 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $proceso_id);
    $stmt->execute();
    $proceso = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$proceso) {
        echo json_encode(['success' => false, 'message' => 'Proceso no encontrado ID: ' . $proceso_id]);
        return;
    }

    $vacante_id    = intval($proceso['vacante_id']);
    $num_ganadores = intval($proceso['numero_ganadores'] ?? 1);

    // 2. Verificar puntajes
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS con_puntaje 
        FROM postulaciones 
        WHERE vacante_id = ? AND puntaje > 0
    ");
    $stmt->bind_param("i", $vacante_id);
    $stmt->execute();
    $check = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($check['con_puntaje'] == 0) {
        echo json_encode([
            'success' => false,
            'message' => '⚠️ Debes evaluar al menos un candidato antes de finalizar'
        ]);
        return;
    }

    // 3. Resetear ganadores
    $stmt = $conn->prepare("UPDATE postulaciones SET es_ganador = 0 WHERE vacante_id = ?");
    $stmt->bind_param("i", $vacante_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error paso 3: ' . $stmt->error]);
        return;
    }
    $stmt->close();

    // 4. Seleccionar ganadores por mayor puntaje
    $stmt = $conn->prepare("
        SELECT id FROM postulaciones 
        WHERE vacante_id = ? 
        ORDER BY puntaje DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $vacante_id, $num_ganadores);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error paso 4: ' . $stmt->error]);
        return;
    }
    $ganadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($ganadores)) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron candidatos']);
        return;
    }

    // 5. Marcar ganadores
    foreach ($ganadores as $g) {
        $stmt = $conn->prepare("
            UPDATE postulaciones 
            SET es_ganador = 1, estado = 'aceptada', fecha_seleccion = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $g['id']);
        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error paso 5: ' . $stmt->error]);
            return;
        }
        $stmt->close();
    }

    // 6. Rechazar el resto
    $stmt = $conn->prepare("
        UPDATE postulaciones 
        SET estado = 'rechazada' 
        WHERE vacante_id = ? AND es_ganador = 0
    ");
    $stmt->bind_param("i", $vacante_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error paso 6: ' . $stmt->error]);
        return;
    }
    $stmt->close();

    // 7. Cerrar proceso
    $stmt = $conn->prepare("
        UPDATE proceso_seleccion 
        SET estado = 'finalizado', fecha_cierre = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $proceso_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error paso 7: ' . $stmt->error]);
        return;
    }
    $stmt->close();

    // 8. Obtener info del ganador
    $stmt = $conn->prepare("
        SELECT u.nombre, u.apellido, p.puntaje
        FROM postulaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.vacante_id = ? AND p.es_ganador = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $vacante_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error paso 8: ' . $stmt->error]);
        return;
    }
    $ganador_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$ganador_info) {
        echo json_encode(['success' => false, 'message' => 'Error: No se pudo obtener info del ganador']);
        return;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Proceso finalizado. Ganador: ' .
                     $ganador_info['nombre'] . ' ' .
                     $ganador_info['apellido'] .
                     ' — Puntaje: ' . $ganador_info['puntaje'] . '/10',
        'ganador' => $ganador_info
    ]);
}
?>