<?php


require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';

// Solo PUT/PATCH
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
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

// Verificar que es admin
$rol = $tokenValidation['data']->rol;
if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Solo administradores']);
    exit();
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->puerta_id) || !isset($data->estado)) {
    http_response_code(400);
    echo json_encode(['error' => 'puerta_id y estado son requeridos']);
    exit();
}

$estados_permitidos = ['abierta', 'cerrada'];
if (!in_array($data->estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido. Valores permitidos: abierta, cerrada']);
    exit();
}

$puerta_id = intval($data->puerta_id);
$nuevo_estado = $data->estado;

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Verificar que la puerta existe
$query_check = "SELECT id, codigo, nombre, estado FROM puertas WHERE id = :id LIMIT 1";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bindParam(':id', $puerta_id);
$stmt_check->execute();

if ($stmt_check->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Puerta no encontrada']);
    exit();
}

$puerta = $stmt_check->fetch();
$estado_anterior = $puerta['estado'];

// Actualizar estado
$query = "UPDATE puertas SET estado = :estado WHERE id = :id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':estado', $nuevo_estado);
$stmt->bindParam(':id', $puerta_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Estado de puerta actualizado exitosamente',
        'puerta' => [
            'id' => $puerta_id,
            'codigo' => $puerta['codigo'],
            'nombre' => $puerta['nombre'],
            'estado_anterior' => $estado_anterior,
            'estado_nuevo' => $nuevo_estado
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar estado']);
}
?>