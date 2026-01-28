<?php

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';


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

// Verificar que el usuario es admin
$rol = $tokenValidation['data']->rol;
if ($rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Solo administradores']);
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

// Query para obtener TODOS los reportes con info completa
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
            fb.nombre as facultad_nombre,
            fb.abreviatura as facultad_abreviatura,
            -- Info del usuario que reportó
            u.id as usuario_id,
            u.email as usuario_email,
            u.nombre_completo as usuario_nombre
          FROM reportes r
          LEFT JOIN banos b ON r.bano_id = b.id
          LEFT JOIN facultades fb ON b.facultad_id = fb.id
          INNER JOIN usuarios u ON r.usuario_id = u.id
          ORDER BY 
            CASE r.urgencia 
              WHEN 'alta' THEN 1
              WHEN 'media' THEN 2
              WHEN 'baja' THEN 3
            END,
            r.fecha_creacion DESC";

$stmt = $conn->prepare($query);
$stmt->execute();

$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($reportes),
    'data' => $reportes
], JSON_UNESCAPED_UNICODE);
?>