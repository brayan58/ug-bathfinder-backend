<?php


header("Content-Type: application/json");

$debug_info = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'getallheaders_available' => function_exists('getallheaders'),
    'apache_request_headers_available' => function_exists('apache_request_headers'),
];

// Método 1: getallheaders()
if (function_exists('getallheaders')) {
    $headers1 = getallheaders();
    $debug_info['method_1_getallheaders'] = $headers1;
} else {
    $debug_info['method_1_getallheaders'] = 'Function not available';
}

// Método 2: $_SERVER
$server_auth_keys = [
    'HTTP_AUTHORIZATION',
    'REDIRECT_HTTP_AUTHORIZATION',
    'PHP_AUTH_USER',
    'PHP_AUTH_PW',
];

$debug_info['method_2_server'] = [];
foreach ($server_auth_keys as $key) {
    if (isset($_SERVER[$key])) {
        $debug_info['method_2_server'][$key] = $_SERVER[$key];
    }
}

// Método 3: apache_request_headers()
if (function_exists('apache_request_headers')) {
    $headers3 = apache_request_headers();
    $debug_info['method_3_apache_request_headers'] = $headers3;
} else {
    $debug_info['method_3_apache_request_headers'] = 'Function not available';
}

// Todos los $_SERVER que contienen HTTP_
$debug_info['all_http_server_vars'] = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0 || strpos($key, 'REDIRECT_HTTP_') === 0) {
        $debug_info['all_http_server_vars'][$key] = $value;
    }
}

// Buscar Authorization en cualquier lugar
$found_authorization = null;
if (function_exists('getallheaders')) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $found_authorization = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $found_authorization = $headers['authorization'];
    }
}

if (!$found_authorization && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $found_authorization = $_SERVER['HTTP_AUTHORIZATION'];
}

if (!$found_authorization && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $found_authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

if (!$found_authorization && function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    if (isset($headers['Authorization'])) {
        $found_authorization = $headers['Authorization'];
    } elseif (isset($headers['authorization'])) {
        $found_authorization = $headers['authorization'];
    }
}

$debug_info['authorization_found'] = $found_authorization !== null;
$debug_info['authorization_value'] = $found_authorization;

// Extraer token si existe
if ($found_authorization && preg_match('/Bearer\s+(\S+)/i', $found_authorization, $matches)) {
    $debug_info['token_extracted'] = true;
    $debug_info['token_preview'] = substr($matches[1], 0, 30) . '...';
} else {
    $debug_info['token_extracted'] = false;
}

echo json_encode($debug_info, JSON_PRETTY_PRINT);
?>