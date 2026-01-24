<?php
class Validation {
    
    // Validar email institucional UG
    public static function validateUGEmail($email) {
        $email = trim(strtolower($email));
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Email inválido'];
        }
        
        if (!str_ends_with($email, '@ug.edu.ec')) {
            return ['valid' => false, 'error' => 'Debe usar correo institucional @ug.edu.ec'];
        }
        
        return ['valid' => true, 'email' => $email];
    }
    
    // Validar contraseña (mínimo 6 caracteres)
    public static function validatePassword($password) {
        if (strlen($password) < 6) {
            return ['valid' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        return ['valid' => true];
    }
    
    // Sanitizar entrada de texto
    public static function sanitizeString($string) {
        return htmlspecialchars(strip_tags(trim($string)));
    }
    
    // Validar coordenadas GPS
    public static function validateCoordinates($lat, $lng) {
        if (!is_numeric($lat) || !is_numeric($lng)) {
            return false;
        }
        
        // Rango válido para Ecuador
        if ($lat < -5 || $lat > 2 || $lng < -82 || $lng > -75) {
            return false;
        }
        
        return true;
    }
}
?>