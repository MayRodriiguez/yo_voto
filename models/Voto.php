<?php
require_once 'config/database.php';

class Voto {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function registrar($id_usuario, $id_candidato) {
        // Verificar si ya votó
        $sql_check = "SELECT * FROM votos WHERE id_usuario = ?";
        $stmt_check = $this->conn->prepare($sql_check);
        $stmt_check->bind_param("i", $id_usuario);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            return false;
        }
        
        $sql = "INSERT INTO votos (id_usuario, id_candidato) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $id_usuario, $id_candidato);
        return $stmt->execute();
    }
    
    public function yaVoto($id_usuario) {
        $sql = "SELECT * FROM votos WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    public function getTotalVotos() {
        $sql = "SELECT COUNT(*) as total FROM votos";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc()['total'];
    }
    
    public function getVotosPorCandidato($id_candidato) {
        $sql = "SELECT COUNT(*) as total FROM votos WHERE id_candidato = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_candidato);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }
}
?>