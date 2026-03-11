<?php
require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/validation.php';
require_once '../../../libs/src/Exception.php';
require_once '../../../libs/src/PHPMailer.php';
require_once '../../../libs/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
$nombre_completo = isset($data->nombre_completo) ? Validation::sanitizeString($data->nombre_completo) : null;


$estado = 'pendiente';
$codigo_verificacion = sprintf("%06d", mt_rand(1, 999999)); // Código de 6 dígitos
$expiracion_codigo = date('Y-m-d H:i:s', strtotime('+15 minutes')); // Expira en 15 mins


$query = "INSERT INTO usuarios (email, password_hash, nombre_completo, rol, estado, codigo_verificacion, expiracion_codigo) 
          VALUES (:email, :password_hash, :nombre_completo, :rol, :estado, :codigo_verificacion, :expiracion_codigo)";
$stmt = $conn->prepare($query);

$stmt->bindParam(':email', $emailValidation['email']);
$stmt->bindParam(':password_hash', $password_hash);
$stmt->bindParam(':nombre_completo', $nombre_completo);
$stmt->bindParam(':rol', $rol);
$stmt->bindParam(':estado', $estado);
$stmt->bindParam(':codigo_verificacion', $codigo_verificacion);
$stmt->bindParam(':expiracion_codigo', $expiracion_codigo);

if ($stmt->execute()) {
    // Si se guardó en la BD, procedemos a enviar el correo
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        
        $mail->Username   = 'cashless898@gmail.com'; 
        $mail->Password   = 'ryogrgfnhkfivkep'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Remitente y Destinatario
        $mail->setFrom('cashless898@gmail.com', 'UG BathFinder');
        $mail->addAddress($emailValidation['email'], $nombre_completo ?? 'Estudiante');

        // Contenido del correo
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Código de Verificación - UG BathFinder';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                <h2 style='color: #0056b3;'>Hola, " . ($nombre_completo ?? 'Estudiante') . "</h2>
                <p>Gracias por registrarte en la aplicación <b>UG BathFinder</b>.</p>
                <p>Para activar tu cuenta, ingresa el siguiente código de verificación en la aplicación:</p>
                <h1 style='background-color: #f4f4f4; padding: 10px; text-align: center; letter-spacing: 5px; color: #333; border-radius: 5px;'>$codigo_verificacion</h1>
                <p><i>⚠️ Este código expirará en 15 minutos por tu seguridad.</i></p>
            </div>
        ";

        $mail->send();
        
        // Respuesta final exitosa
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario registrado. Se ha enviado un código de verificación a su correo institucional.',
            'status' => 'pending_verification',
            'email' => $emailValidation['email'] // Devolvemos el correo para usarlo en la app al validar
        ]);

    } catch (Exception $e) {
        // Si falla el correo, el usuario se creó pero no sabrá su código
        http_response_code(500);
        echo json_encode(['error' => 'Usuario registrado, pero hubo un error al enviar el correo. Contacte soporte.']);
    }

} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar usuario en la base de datos']);
}
?>