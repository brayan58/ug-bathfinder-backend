<?php
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

// Query para obtener todos los baños con info de facultad
$query = "SELECT 
            b.id,
            b.codigo,
            b.nombre,
            b.piso,
            b.coordenada_lat,
            b.coordenada_lng,
            b.genero,
            b.accesibilidad,
            b.estado,
            b.horario_apertura,
            b.horario_cierre,
            b.ultimo_reporte_fecha,
            f.id as facultad_id,
            f.nombre as facultad_nombre,
            f.abreviatura as facultad_abreviatura
          FROM banos b
          LEFT JOIN facultades f ON b.facultad_id = f.id
          ORDER BY b.facultad_id, b.piso, b.nombre";

$stmt = $conn->prepare($query);
$stmt->execute();

$banos = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($banos),
    'data' => $banos
]);
?>