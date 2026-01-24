<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';
require_once '../../helpers/validation.php';

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
$admin_id = $tokenValidation['data']->user_id;

if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Solo administradores']);
    exit();
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->reporte_id) || !isset($data->estado)) {
    http_response_code(400);
    echo json_encode(['error' => 'reporte_id y estado son requeridos']);
    exit();
}

$estados_permitidos = ['pendiente', 'en_proceso', 'resuelto', 'rechazado'];
if (!in_array($data->estado, $estados_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Estado inválido']);
    exit();
}

$reporte_id = intval($data->reporte_id);
$nuevo_estado = $data->estado;
$nota_admin = isset($data->nota_admin) ? Validation::sanitizeString($data->nota_admin) : null;

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Verificar que el reporte existe
$query_check = "SELECT id FROM reportes WHERE id = :id LIMIT 1";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bindParam(':id', $reporte_id);
$stmt_check->execute();

if ($stmt_check->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Reporte no encontrado']);
    exit();
}

// Actualizar estado del reporte
$query = "UPDATE reportes 
          SET estado = :estado,
              nota_admin = :nota_admin,
              resuelto_por_admin_id = :admin_id,
              fecha_resolucion = CASE WHEN :estado IN ('resuelto', 'rechazado') THEN CURRENT_TIMESTAMP ELSE fecha_resolucion END
          WHERE id = :id";

$stmt = $conn->prepare($query);
$stmt->bindParam(':estado', $nuevo_estado);
$stmt->bindParam(':nota_admin', $nota_admin);
$stmt->bindParam(':admin_id', $admin_id);
$stmt->bindParam(':id', $reporte_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Estado del reporte actualizado exitosamente'
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar reporte']);
}
?>