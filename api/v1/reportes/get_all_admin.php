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
            -- Info del baño (puede ser NULL)
            r.bano_id,
            b.codigo as bano_codigo,
            b.nombre as bano_nombre,
            fb.nombre as facultad_nombre,
            -- Info de la puerta (puede ser NULL)
            r.puerta_id,
            p.codigo as puerta_codigo,
            p.nombre as puerta_nombre,
            -- Info del usuario que reportó
            u.id as usuario_id,
            u.email as usuario_email,
            u.nombre_completo as usuario_nombre,
            -- Campo calculado para saber el tipo de recurso
            CASE 
                WHEN r.puerta_id IS NOT NULL THEN 'puerta'
                ELSE 'bano'
            END as tipo_recurso
          FROM reportes r
          LEFT JOIN banos b ON r.bano_id = b.id
          LEFT JOIN facultades fb ON b.facultad_id = fb.id
          LEFT JOIN puertas p ON r.puerta_id = p.id
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

$reportes = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($reportes),
    'data' => $reportes
]);
?>