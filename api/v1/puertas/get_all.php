<?php
/**
 * GET /api/v1/puertas/get_all.php
 * Obtiene todas las puertas de la ciudadela
 * 
 * Ubicación: C:\xampp\htdocs\ug-bathfinder\api\v1\puertas\get_all.php
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';

// Solo GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Query para obtener todas las puertas
$query = "SELECT 
            id,
            codigo,
            nombre,
            descripcion,
            coordenada_lat,
            coordenada_lng,
            estado,
            horario_apertura,
            horario_cierre,
            es_principal,
            created_at,
            updated_at
          FROM puertas
          ORDER BY es_principal DESC, codigo ASC";

$stmt = $conn->prepare($query);
$stmt->execute();

$puertas = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($puertas),
    'data' => $puertas
]);
?>