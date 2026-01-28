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

// Estadísticas de baños
$query_banos = "SELECT 
                  COUNT(*) as total,
                  SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                  SUM(CASE WHEN estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento,
                  SUM(CASE WHEN estado = 'cerrado' THEN 1 ELSE 0 END) as cerrados,
                  SUM(CASE WHEN accesibilidad = 1 THEN 1 ELSE 0 END) as accesibles
                FROM banos";
$stmt_banos = $conn->prepare($query_banos);
$stmt_banos->execute();
$stats_banos = $stmt_banos->fetch();

// Estadísticas de reportes
$query_reportes = "SELECT 
                     COUNT(*) as total,
                     SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                     SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                     SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos,
                     SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
                     SUM(CASE WHEN urgencia = 'alta' AND estado != 'resuelto' THEN 1 ELSE 0 END) as urgentes_pendientes
                   FROM reportes";
$stmt_reportes = $conn->prepare($query_reportes);
$stmt_reportes->execute();
$stats_reportes = $stmt_reportes->fetch();

// Total de usuarios
$query_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$stmt_usuarios = $conn->prepare($query_usuarios);
$stmt_usuarios->execute();
$stats_usuarios = $stmt_usuarios->fetch();

// Baños más reportados (top 5)
$query_top_reportados = "SELECT 
                           b.id,
                           b.codigo,
                           b.nombre,
                           COUNT(r.id) as total_reportes
                         FROM banos b
                         INNER JOIN reportes r ON b.id = r.bano_id
                         GROUP BY b.id
                         ORDER BY total_reportes DESC
                         LIMIT 5";
$stmt_top = $conn->prepare($query_top_reportados);
$stmt_top->execute();
$top_reportados = $stmt_top->fetchAll();

// Reportes por facultad
$query_por_facultad = "SELECT 
                         f.nombre as facultad,
                         COUNT(r.id) as total_reportes
                       FROM reportes r
                       INNER JOIN banos b ON r.bano_id = b.id
                       LEFT JOIN facultades f ON b.facultad_id = f.id
                       GROUP BY f.id
                       ORDER BY total_reportes DESC";
$stmt_facultad = $conn->prepare($query_por_facultad);
$stmt_facultad->execute();
$reportes_por_facultad = $stmt_facultad->fetchAll();

//Baños con 3 o más reportes pendientes
$query_banos_criticos = "SELECT 
                           b.id,
                           b.codigo,
                           b.nombre,
                           b.estado,
                           COUNT(r.id) as reportes_pendientes
                         FROM banos b
                         INNER JOIN reportes r ON b.id = r.bano_id
                         WHERE r.estado = 'pendiente'
                         GROUP BY b.id
                         HAVING COUNT(r.id) >= 3
                         ORDER BY reportes_pendientes DESC";
$stmt_criticos = $conn->prepare($query_banos_criticos);
$stmt_criticos->execute();
$banos_criticos = $stmt_criticos->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'data' => [
        'banos' => $stats_banos,
        'reportes' => $stats_reportes,
        'usuarios' => $stats_usuarios,
        'top_banos_reportados' => $top_reportados,
        'reportes_por_facultad' => $reportes_por_facultad,
        'banos_criticos' => $banos_criticos 
    ]
]);
?>