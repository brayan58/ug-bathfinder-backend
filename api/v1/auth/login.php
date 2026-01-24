<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/validation.php';
require_once '../../helpers/jwt_helper.php';

// Solo aceptar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// Obtener datos del body
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y contraseña son requeridos']);
    exit();
}

// Validar formato de email
$emailValidation = Validation::validateUGEmail($data->email);
if (!$emailValidation['valid']) {
    http_response_code(400);
    echo json_encode(['error' => $emailValidation['error']]);
    exit();
}

// Conectar a BD
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}

// Buscar usuario por email
$query = "SELECT id, email, password_hash, nombre_completo, rol 
          FROM usuarios 
          WHERE email = :email 
          LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $emailValidation['email']);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Email o contraseña incorrectos']);
    exit();
}

$user = $stmt->fetch();

// Verificar contraseña
if (!password_verify($data->password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Email o contraseña incorrectos']);
    exit();
}

// Actualizar último acceso
$update_query = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bindParam(':id', $user['id']);
$update_stmt->execute();

// Generar token JWT
$token = JWTHelper::generateToken($user['id'], $user['email'], $user['rol']);

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Login exitoso',
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'nombre_completo' => $user['nombre_completo'],
        'rol' => $user['rol']
    ]
]);
?>