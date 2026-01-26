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

$user_id = $tokenValidation['data']->user_id;

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión']);
    exit();
}

// Query para obtener reportes del usuario con info del baño
$query = "SELECT 
            r.id,
            r.tipo,
            r.descripcion,
            r.urgencia,
            r.estado,
            r.fecha_creacion,
            r.fecha_resolucion,
            r.nota_admin,
            -- Info del baño
            r.bano_id,
            b.codigo as bano_codigo,
            b.nombre as bano_nombre,
            b.piso as bano_piso,
            b.genero as bano_genero,
            fb.nombre as bano_facultad_nombre,
            fb.abreviatura as bano_facultad_abreviatura
          FROM reportes r
          LEFT JOIN banos b ON r.bano_id = b.id
          LEFT JOIN facultades fb ON b.facultad_id = fb.id
          WHERE r.usuario_id = :user_id
          ORDER BY r.fecha_creacion DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($reportes),
    'data' => $reportes
], JSON_UNESCAPED_UNICODE);
?>