<?php

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';
require_once '../../helpers/validation.php';

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Validar token JWT
$token = JWTHelper::getBearerToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token no proporcionado']);
    exit();
}

$tokenValidation = JWTHelper::validateToken($token);
if (!$tokenValidation['valid']) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido o expirado']);
    exit();
}

$user_id = $tokenValidation['data']->user_id;

// Obtener datos del body
$data = json_decode(file_get_contents("php://input"));

// Validar que venga tipo y urgencia siempre
if (!isset($data->tipo) || !isset($data->urgencia)) {
    http_response_code(400);
    echo json_encode(['error' => 'tipo y urgencia son requeridos']);
    exit();
}

// Tipos permitidos (ahora incluye puerta_cerrada)
$tipos_bano = ['limpieza', 'dano_instalaciones', 'sin_papel', 'sin_agua', 'puerta_danada', 'sin_luz', 'otro'];
$tipos_puerta = ['puerta_cerrada'];
$todos_los_tipos = array_merge($tipos_bano, $tipos_puerta);
$urgencias_permitidas = ['baja', 'media', 'alta'];

if (!in_array($data->tipo, $todos_los_tipos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de reporte inválido']);
    exit();
}

if (!in_array($data->urgencia, $urgencias_permitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Urgencia inválida']);
    exit();
}

// Determinar si es reporte de baño o de puerta
$es_reporte_puerta = in_array($data->tipo, $tipos_puerta);

// Validar que venga el ID correcto según el tipo
if ($es_reporte_puerta) {
    if (!isset($data->puerta_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'puerta_id es requerido para reportes de tipo puerta_cerrada']);
        exit();
    }
    $puerta_id = intval($data->puerta_id);
    $bano_id = null;
} else {
    if (!isset($data->bano_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'bano_id es requerido para este tipo de reporte']);
        exit();
    }
    $bano_id = intval($data->bano_id);
    $puerta_id = null;
}

$tipo = $data->tipo;
$urgencia = $data->urgencia;
$descripcion = isset($data->descripcion) ? Validation::sanitizeString($data->descripcion) : null;

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Verificar que el recurso (baño o puerta) existe
if ($es_reporte_puerta) {
    $query_check = "SELECT id, estado FROM puertas WHERE id = :id LIMIT 1";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':id', $puerta_id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Puerta no encontrada']);
        exit();
    }
} else {
    $query_check = "SELECT id, estado FROM banos WHERE id = :id LIMIT 1";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':id', $bano_id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Baño no encontrado']);
        exit();
    }
}

// Iniciar transacción
$conn->beginTransaction();

try {
    // Insertar reporte
    $query = "INSERT INTO reportes (bano_id, puerta_id, usuario_id, tipo, descripcion, urgencia) 
              VALUES (:bano_id, :puerta_id, :usuario_id, :tipo, :descripcion, :urgencia)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':bano_id', $bano_id);
    $stmt->bindParam(':puerta_id', $puerta_id);
    $stmt->bindParam(':usuario_id', $user_id);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':urgencia', $urgencia);
    $stmt->execute();
    
    $reporte_id = $conn->lastInsertId();
    
    if ($es_reporte_puerta) {
        // Si es reporte de puerta con urgencia alta, cambiar estado a cerrada
        if ($urgencia === 'alta') {
            $query_estado = "UPDATE puertas SET estado = 'cerrada' WHERE id = :puerta_id";
            $stmt_estado = $conn->prepare($query_estado);
            $stmt_estado->bindParam(':puerta_id', $puerta_id);
            $stmt_estado->execute();
        }
    } else {
        // Si es reporte de baño, actualizar ultimo_reporte_fecha
        $query_update = "UPDATE banos SET ultimo_reporte_fecha = CURRENT_TIMESTAMP WHERE id = :bano_id";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bindParam(':bano_id', $bano_id);
        $stmt_update->execute();
        
        // Si urgencia es ALTA, cambiar estado del baño a mantenimiento
        if ($urgencia === 'alta') {
            $query_estado = "UPDATE banos SET estado = 'mantenimiento' WHERE id = :bano_id";
            $stmt_estado = $conn->prepare($query_estado);
            $stmt_estado->bindParam(':bano_id', $bano_id);
            $stmt_estado->execute();
        }
    }
    
    // Confirmar transacción
    $conn->commit();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Reporte creado exitosamente',
        'reporte_id' => $reporte_id,
        'tipo_recurso' => $es_reporte_puerta ? 'puerta' : 'bano'
    ]);
    
} catch (Exception $e) {
    // Si hay error, revertir cambios
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear reporte: ' . $e->getMessage()]);
}
?>