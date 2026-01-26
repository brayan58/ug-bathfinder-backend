<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../../config/database.php';
require_once '../../config/cors.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Consulta para obtener todas las puertas
    $query = "SELECT 
                id,
                codigo,
                nombre,
                descripcion,
                coordenada_lat,
                coordenada_lng,
                estado,
                horario_apertura,
                horario_cierre,
                es_principal,
                fecha_creacion
              FROM puertas 
              ORDER BY codigo ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $puertas = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $puertas[] = [
            'id' => (int)$row['id'],
            'codigo' => $row['codigo'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'],
            'coordenada_lat' => (float)$row['coordenada_lat'],
            'coordenada_lng' => (float)$row['coordenada_lng'],
            'estado' => $row['estado'],
            'horario_apertura' => $row['horario_apertura'],
            'horario_cierre' => $row['horario_cierre'],
            'es_principal' => (int)$row['es_principal'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $puertas,
        'total' => count($puertas)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error del servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}