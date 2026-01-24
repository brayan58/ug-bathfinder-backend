<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$token = JWTHelper::getBearerToken();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token no proporcionado']);
    exit();
}

$tokenValidation = JWTHelper::validateToken($token);
if (!$tokenValidation['valid']) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT id, nombre, abreviatura FROM facultades ORDER BY nombre";
$stmt = $conn->prepare($query);
$stmt->execute();

$facultades = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'success' => true,
    'count' => count($facultades),
    'data' => $facultades
]);
?>