<?php
// config/database.php
// NO configures sesión aquí, solo la base de datos

class Database {
    private $host = "localhost";
<<<<<<< HEAD
    private $db_name = "yo_voto010";
=======
    private $db_name = "yo_voto";
>>>>>>> 14bf65808c01528e1449c8356f81b4b5f8f1154f
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