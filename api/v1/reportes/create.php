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

// Validar que vengan los campos requeridos
if (!isset($data->tipo) || !isset($data->urgencia) || !isset($data->bano_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'tipo, urgencia y bano_id son requeridos']);
    exit();
}

// Tipos permitidos
$tipos_permitidos = ['limpieza', 'dano_instalaciones', 'sin_papel', 'sin_agua', 'puerta_danada', 'sin_luz', 'otro'];
$urgencias_permitidas = ['baja', 'media', 'alta'];

if (!in_array($data->tipo, $tipos_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de reporte inválido']);
    exit();
}

if (!in_array($data->urgencia, $urgencias_permitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Urgencia inválida']);
    exit();
}

$bano_id = intval($data->bano_id);
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

// Verificar que el baño existe
$query_check = "SELECT id, estado FROM banos WHERE id = :id LIMIT 1";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bindParam(':id', $bano_id, PDO::PARAM_INT);
$stmt_check->execute();

if ($stmt_check->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Baño no encontrado']);
    exit();
}

// Iniciar transacción
$conn->beginTransaction();

try {
    // Insertar reporte (SIN puerta_id)
    $query = "INSERT INTO reportes (bano_id, usuario_id, tipo, descripcion, urgencia) 
              VALUES (:bano_id, :usuario_id, :tipo, :descripcion, :urgencia)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':bano_id', $bano_id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
    $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindParam(':urgencia', $urgencia, PDO::PARAM_STR);
    $stmt->execute();
    
    $reporte_id = $conn->lastInsertId();
    
    // Actualizar ultimo_reporte_fecha del baño
    $query_update = "UPDATE banos SET ultimo_reporte_fecha = CURRENT_TIMESTAMP WHERE id = :bano_id";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bindParam(':bano_id', $bano_id, PDO::PARAM_INT);
    $stmt_update->execute();
    
    // Si urgencia es ALTA, cambiar estado del baño a mantenimiento
    if ($urgencia === 'alta') {
        $query_estado = "UPDATE banos SET estado = 'mantenimiento' WHERE id = :bano_id";
        $stmt_estado = $conn->prepare($query_estado);
        $stmt_estado->bindParam(':bano_id', $bano_id, PDO::PARAM_INT);
        $stmt_estado->execute();
    }
    
    // Confirmar transacción
    $conn->commit();
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Reporte creado exitosamente',
        'reporte_id' => $reporte_id
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Si hay error, revertir cambios
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear reporte: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>