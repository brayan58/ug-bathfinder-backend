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

// Query para obtener reportes del usuario con info del baño Y puerta
$query = "SELECT 
            r.id,
            r.tipo,
            r.descripcion,
            r.urgencia,
            r.estado,
            r.fecha_creacion,
            r.fecha_resolucion,
            r.nota_admin,
            -- Info del baño (puede ser NULL)
            r.bano_id,
            b.codigo as bano_codigo,
            b.nombre as bano_nombre,
            fb.nombre as bano_facultad_nombre,
            -- Info de la puerta (puede ser NULL)
            r.puerta_id,
            p.codigo as puerta_codigo,
            p.nombre as puerta_nombre,
            -- Campo calculado para saber el tipo de recurso
            CASE 
                WHEN r.puerta_id IS NOT NULL THEN 'puerta'
                ELSE 'bano'
            END as tipo_recurso
          FROM reportes r
          LEFT JOIN banos b ON r.bano_id = b.id
          LEFT JOIN facultades fb ON b.facultad_id = fb.id
          LEFT JOIN puertas p ON r.puerta_id = p.id
          WHERE r.usuario_id = :user_id
          ORDER BY r.fecha_creacion DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$reportes = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($reportes),
    'data' => $reportes
]);
?>