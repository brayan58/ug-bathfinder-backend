<?php


require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../helpers/jwt_helper.php';


if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Verificar que sea admin
$user_rol = $tokenValidation['data']->rol;
if ($user_rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Solo administradores.']);
    exit();
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->reporte_id) || !isset($data->urgencia)) {
    http_response_code(400);
    echo json_encode(['error' => 'reporte_id y urgencia son requeridos']);
    exit();
}

$reporte_id = intval($data->reporte_id);
$urgencia = $data->urgencia;

// Parámetro opcional para cambiar estado del baño
$cambiar_estado_bano = isset($data->cambiar_estado_bano) ? (bool)$data->cambiar_estado_bano : false;

$urgencias_permitidas = ['baja', 'media', 'alta'];
if (!in_array($urgencia, $urgencias_permitidas)) {
    http_response_code(400);
    echo json_encode(['error' => 'Urgencia inválida. Valores permitidos: baja, media, alta']);
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

// Iniciar transacción
$conn->beginTransaction();

try {
    // Verificar que el reporte existe y obtener info del baño
    $query_check = "SELECT r.id, r.bano_id, r.estado, b.estado as bano_estado, b.nombre as bano_nombre 
                    FROM reportes r
                    LEFT JOIN banos b ON r.bano_id = b.id
                    WHERE r.id = :id 
                    LIMIT 1";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':id', $reporte_id);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() === 0) {
        $conn->rollBack();
        http_response_code(404);
        echo json_encode(['error' => 'Reporte no encontrado']);
        exit();
    }
    
    $reporte = $stmt_check->fetch(PDO::FETCH_ASSOC);
    $bano_id = $reporte['bano_id'];
    
    // Actualizar urgencia del reporte
    $query = "UPDATE reportes SET urgencia = :urgencia WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':urgencia', $urgencia);
    $stmt->bindParam(':id', $reporte_id);
    $stmt->execute();
    
    
    $bano_actualizado = false;
    $bano_estado_anterior = null;
    
    if ($urgencia === 'alta' && $cambiar_estado_bano && $bano_id !== null) {
        $bano_estado_anterior = $reporte['bano_estado'];
        
        // Solo cambiar si el baño NO está ya en mantenimiento
        if ($bano_estado_anterior !== 'mantenimiento') {
            $query_bano = "UPDATE banos SET estado = 'mantenimiento' WHERE id = :bano_id";
            $stmt_bano = $conn->prepare($query_bano);
            $stmt_bano->bindParam(':bano_id', $bano_id);
            $stmt_bano->execute();
            
            $bano_actualizado = true;
        }
    }
    
    // Confirmar transacción
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => 'Urgencia actualizada exitosamente',
        'reporte_id' => $reporte_id,
        'nueva_urgencia' => $urgencia
    ];
    
    // Agregar info adicional si se cambió el baño
    if ($bano_actualizado) {
        $response['bano_actualizado'] = true;
        $response['bano_nombre'] = $reporte['bano_nombre'];
        $response['bano_estado_anterior'] = $bano_estado_anterior;
        $response['bano_estado_nuevo'] = 'mantenimiento';
    } else if ($urgencia === 'alta' && $cambiar_estado_bano && $bano_id !== null) {
        $response['bano_actualizado'] = false;
        $response['mensaje_adicional'] = 'El baño ya estaba en mantenimiento';
    }
    
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    // Si hay error, revertir cambios
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar urgencia: ' . $e->getMessage()]);
}
?>