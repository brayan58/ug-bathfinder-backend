<?php
// Permitir peticiones desde cualquier origen (necesario para que Flutter pueda comunicarse)
header("Access-Control-Allow-Origin: *");

// Métodos HTTP permitidos
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// Headers permitidos en las peticiones
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Tipo de contenido que devolvemos (siempre JSON)
header("Content-Type: application/json; charset=UTF-8");

// Si es una petición OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>