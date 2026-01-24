<?php
/**
 * Este archivo se incluirá en todos los endpoints que necesiten acceso a MySQL
 */

class Database {
    // Configuración de la base de datos
    private $host = "localhost";        
    private $database_name = "ug_bathfinder";  
    private $username = "root";         
    private $password = "";             
    private $charset = "utf8mb4";      
    
    public $conn;  // Variable que guardará la conexión

    public function getConnection() {
        $this->conn = null;

        try {
            // DSN = Data Source Name (cadena de conexión)
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->database_name . ";charset=" . $this->charset;
            
            // Crear conexión PDO 
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configurar PDO para que lance excepciones en caso de errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configurar PDO para que devuelva resultados como arrays asociativos
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
          
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>