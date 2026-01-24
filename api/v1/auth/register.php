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

// Obtener datos del body (JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar que vengan los datos requeridos
if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y contraseña son requeridos']);
    exit();
}

// Validar email
$emailValidation = Validation::validateUGEmail($data->email);
if (!$emailValidation['valid']) {
    http_response_code(400);
    echo json_encode(['error' => $emailValidation['error']]);
    exit();
}

// Validar contraseña
$passwordValidation = Validation::validatePassword($data->password);
if (!$passwordValidation['valid']) {
    http_response_code(400);
    echo json_encode(['error' => $passwordValidation['error']]);
    exit();
}

// Validar rol (opcional, por defecto 'estudiante')
$rol = isset($data->rol) ? $data->rol : 'estudiante';
$roles_permitidos = ['estudiante', 'admin'];

if (!in_array($rol, $roles_permitidos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Rol inválido. Use: estudiante o admin']);
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

// Verificar si el email ya existe
$query = "SELECT id FROM usuarios WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':email', $emailValidation['email']);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'El email ya está registrado']);
    exit();
}

// Hashear contraseña
$password_hash = password_hash($data->password, PASSWORD_BCRYPT);

// Insertar usuario CON EL ROL ESPECIFICADO
$query = "INSERT INTO usuarios (email, password_hash, nombre_completo, rol) 
          VALUES (:email, :password_hash, :nombre_completo, :rol)";
$stmt = $conn->prepare($query);

$nombre_completo = isset($data->nombre_completo) ? Validation::sanitizeString($data->nombre_completo) : null;

$stmt->bindParam(':email', $emailValidation['email']);
$stmt->bindParam(':password_hash', $password_hash);
$stmt->bindParam(':nombre_completo', $nombre_completo);
$stmt->bindParam(':rol', $rol);

if ($stmt->execute()) {
    $user_id = $conn->lastInsertId();
    
    // Generar token JWT con el rol correcto
    $token = JWTHelper::generateToken($user_id, $emailValidation['email'], $rol);
    
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado exitosamente',
        'token' => $token,
        'user' => [
            'id' => $user_id,
            'email' => $emailValidation['email'],
            'nombre_completo' => $nombre_completo,
            'rol' => $rol
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar usuario']);
}
?>