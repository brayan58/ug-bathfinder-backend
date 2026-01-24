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

// Obtener ID del baño desde query params (?id=1)
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID del baño es requerido']);
    exit();
}

$bano_id = intval($_GET['id']);

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Query para obtener baño específico
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
          WHERE b.id = :id
          LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bindParam(':id', $bano_id);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Baño no encontrado']);
    exit();
}

$bano = $stmt->fetch();

http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => $bano
]);
?>