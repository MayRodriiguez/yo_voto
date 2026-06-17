<?php
// config/database.php
// NO configures sesión aquí, solo la base de datos

date_default_timezone_set('America/La_Paz');

class Database {
    private $host = "localhost";
    private $db_name = "yo_voto02";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8");
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
        return $this->conn;
    }
}
?>