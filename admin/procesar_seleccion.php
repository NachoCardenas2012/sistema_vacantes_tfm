<?php
session_start();
ob_start();

header('Content-Type: application/json; charset=utf-8');

// Verificar sesión y rol admin
if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'admin') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// Conexión a base de datos
$conn = new mysqli("localhost", "root", "root", "sistema_vacantes");
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}
$conn->set_charset("utf8");

$action = $_GET['action'] ?? '';

ob_end_clean();

// Enrutar acción recibida
switch ($action) {
    case 'iniciar_proceso':   iniciarProceso($conn);    break;
    case 'evaluar':           evaluarPostulante($conn);  break;
    case 'marcar_ganador':    marcarGanador($conn);      break;
    case 'quitar_ganador':    quitarGanador($conn);      break;
    case 'finalizar_proceso': finalizarProceso($conn);   break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
}

$conn->close();

/* ============================================================
   INICIAR PROCESO DE SELECCIÓN
   ============================================================ */
function iniciarProceso($conn) {
    $vacante_id          = intval($_POST['vacante_id']       ?? 0);
    $numero_ganadores    = intval($_POST['numero_ganadores'] ?? 1);
    $criterios_seleccion = trim($_POST['criterios_seleccion'] ?? '');
    $admin_id            = $_SESSION['id'];

    if ($vacante_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de vacante inválido']);
        return;
    }

    // Verificar si ya existe proceso para esta vacante
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

    // Insertar nuevo proceso
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
        echo json_encode(['success' => false, 'message' => 'Error al iniciar proceso']);
    }
    $stmt->close();
}

/* ============================================================
   EVALUAR POSTULANTE — Guarda puntaje y fecha de evaluación
   ============================================================ */
function evaluarPostulante($conn) {
    $postulacion_id = intval($_POST['postulacion_id'] ?? 0);
    $puntaje        = floatval($_POST['puntaje']       ?? 0);
    $comentario     = trim($_POST['comentario']        ?? '');

    if ($postulacion_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de postulación inválido']);
        return;
    }

    if ($puntaje < 0 || $puntaje > 10) {
        echo json_encode(['success' => false, 'message' => 'El puntaje debe estar entre 0 y 10']);
        return;
    }

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
            echo json_encode(['success' => true, 'message' => 'Evaluación guardada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la postulación']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar evaluación']);
    }
    $stmt->close();
}

/* ============================================================
   MARCAR GANADOR MANUAL
   ============================================================ */
function marcarGanador($conn) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['postulacion_id'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }

    $postulacion_id = intval($input['postulacion_id']);

    if ($postulacion_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de postulación inválido']);
        return;
    }

    // Verificar que la postulación existe
    $stmt = $conn->prepare("SELECT id FROM postulaciones WHERE id = ?");
    $stmt->bind_param("i", $postulacion_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Postulación no encontrada']);
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
        echo json_encode(['success' => true, 'message' => '¡Ganador marcado exitosamente!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al marcar ganador']);
    }
    $stmt->close();
}

/* ============================================================
   QUITAR GANADOR
   ============================================================ */
function quitarGanador($conn) {
    $input = json_decode(file_get_contents('php://input'), true);

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
        echo json_encode(['success' => true, 'message' => 'Ganador removido correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al quitar ganador']);
    }
    $stmt->close();
}

/* ============================================================
   FINALIZAR PROCESO — Auto selecciona ganador por mayor puntaje
   ============================================================ */
function finalizarProceso($conn) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['proceso_id'])) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        return;
    }

    $proceso_id = intval($input['proceso_id']);

    if ($proceso_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de proceso inválido']);
        return;
    }

    // Obtener datos del proceso
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
        echo json_encode(['success' => false, 'message' => 'Proceso no encontrado']);
        return;
    }

    $vacante_id    = intval($proceso['vacante_id']);
    $num_ganadores = intval($proceso['numero_ganadores'] ?? 1);

    // Verificar que existan candidatos evaluados
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
            'message' => 'Debes evaluar al menos un candidato antes de finalizar'
        ]);
        return;
    }

    // Resetear ganadores anteriores
    $stmt = $conn->prepare("UPDATE postulaciones SET es_ganador = 0 WHERE vacante_id = ?");
    $stmt->bind_param("i", $vacante_id);
    $stmt->execute();
    $stmt->close();

    // Seleccionar ganadores por mayor puntaje
    $stmt = $conn->prepare("
        SELECT id FROM postulaciones 
        WHERE vacante_id = ? 
        ORDER BY puntaje DESC 
        LIMIT ?
    ");
    $stmt->bind_param("ii", $vacante_id, $num_ganadores);
    $stmt->execute();
    $ganadores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($ganadores)) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron candidatos']);
        return;
    }

    // Marcar ganadores
    foreach ($ganadores as $g) {
        $stmt = $conn->prepare("
            UPDATE postulaciones 
            SET es_ganador      = 1, 
                estado          = 'aceptada', 
                fecha_seleccion = NOW() 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $g['id']);
        $stmt->execute();
        $stmt->close();
    }

    // Rechazar el resto de candidatos
    $stmt = $conn->prepare("
        UPDATE postulaciones 
        SET estado = 'rechazada' 
        WHERE vacante_id = ? AND es_ganador = 0
    ");
    $stmt->bind_param("i", $vacante_id);
    $stmt->execute();
    $stmt->close();

    // Cerrar el proceso
    $stmt = $conn->prepare("
        UPDATE proceso_seleccion 
        SET estado = 'finalizado', fecha_cierre = NOW() 
        WHERE id = ?
    ");
    $stmt->bind_param("i", $proceso_id);
    $stmt->execute();
    $stmt->close();

    // Obtener información del ganador para mostrar
    $stmt = $conn->prepare("
        SELECT u.nombre, u.apellido, p.puntaje
        FROM postulaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.vacante_id = ? AND p.es_ganador = 1
        LIMIT 1
    ");
    $stmt->bind_param("i", $vacante_id);
    $stmt->execute();
    $ganador_info = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$ganador_info) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener información del ganador']);
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