<?php
require_once 'config/database.php';

class Configuracion {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function get($clave) {
        $sql = "SELECT valor FROM configuracion WHERE clave = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $clave);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['valor'] : null;
    }
    
    public function set($clave, $valor) {
        $sql = "UPDATE configuracion SET valor = ? WHERE clave = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $valor, $clave);
        return $stmt->execute();
    }
    
    public function getTipoEleccion() {
        return $this->get('tipo_eleccion_activa');
    }
    
    public function setTipoEleccion($tipo) {
        return $this->set('tipo_eleccion_activa', $tipo);
    }
}
?>