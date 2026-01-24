<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper {
    // Clave secreta (CAMBIAR en producción)
    private static $secret_key = "tu_clave_secreta_super_segura_2025";
    private static $issuer = "ug-bathfinder-api";
    private static $audience = "ug-bathfinder-app";
    private static $expiration_time = 604800; // 7 días en segundos
    
    // Generar token JWT
    public static function generateToken($user_id, $email, $rol) {
        $issued_at = time();
        $expiration = $issued_at + self::$expiration_time;
        
        $payload = [
            'iss' => self::$issuer,
            'aud' => self::$audience,
            'iat' => $issued_at,
            'exp' => $expiration,
            'data' => [
                'user_id' => $user_id,
                'email' => $email,
                'rol' => $rol
            ]
        ];
        
        return JWT::encode($payload, self::$secret_key, 'HS256');
    }
    
    // Validar y decodificar token
    public static function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key(self::$secret_key, 'HS256'));
            return [
                'valid' => true,
                'data' => $decoded->data
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Extraer token del header Authorization
    public static function getBearerToken() {
        $token = null;
        
        // Método 1: getallheaders() (funciona en Apache con mod_php)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            
            if (isset($headers['Authorization'])) {
                $token = self::extractTokenFromHeader($headers['Authorization']);
            } elseif (isset($headers['authorization'])) {
                $token = self::extractTokenFromHeader($headers['authorization']);
            }
        }
        
        // Método 2: $_SERVER (más confiable para FastCGI/FPM)
        if (!$token) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $token = self::extractTokenFromHeader($_SERVER['HTTP_AUTHORIZATION']);
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $token = self::extractTokenFromHeader($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            }
        }
        
        // Método 3: apache_request_headers()
        if (!$token && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $token = self::extractTokenFromHeader($headers['Authorization']);
            } elseif (isset($headers['authorization'])) {
                $token = self::extractTokenFromHeader($headers['authorization']);
            }
        }
        
        return $token;
    }
    
    // Método auxiliar para extraer el token del header Authorization
    private static function extractTokenFromHeader($authHeader) {
        if (empty($authHeader)) {
            return null;
        }
        
        // Buscar patrón "Bearer TOKEN"
        $matches = [];
        if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
?>