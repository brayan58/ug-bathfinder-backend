<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';

require_once '../../helpers/jwt_helper.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->codigo)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y código son requeridos']);
    exit();
}

$email = trim($data->email);
$codigo = trim($data->codigo);

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit();
}


$query = "SELECT id, nombre_completo, rol, estado, codigo_verificacion, expiracion_codigo 
          FROM usuarios WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuario no encontrado']);
    exit();
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);


if ($row['estado'] === 'activo') {
    http_response_code(400);
    echo json_encode(['error' => 'Esta cuenta ya está verificada. Por favor, inicie sesión.']);
    exit();
}


if ($row['codigo_verificacion'] !== $codigo) {
    http_response_code(400);
    echo json_encode(['error' => 'El código de verificación es incorrecto. Intente de nuevo.']);
    exit();
}


date_default_timezone_set('America/Guayaquil'); 
$ahora = date('Y-m-d H:i:s');

if ($ahora > $row['expiracion_codigo']) {
    http_response_code(400);
    echo json_encode(['error' => 'El código de verificación ha expirado. Solicite uno nuevo.']);
    exit();
}


$update_query = "UPDATE usuarios 
                 SET estado = 'activo', codigo_verificacion = NULL, expiracion_codigo = NULL 
                 WHERE id = :id";
$update_stmt = $conn->prepare($update_query);
$update_stmt->bindParam(':id', $row['id']);

if ($update_stmt->execute()) {
    

    $token = JWTHelper::generateToken($row['id'], $email, $row['rol']);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Cuenta verificada y activada exitosamente',
        'token' => $token,
        'user' => [
            'id' => $row['id'],
            'email' => $email,
            'nombre_completo' => $row['nombre_completo'],
            'rol' => $row['rol']
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al activar la cuenta en la base de datos']);
}
?>