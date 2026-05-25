<?php
require_once 'config/database.php';

class Propuesta {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getByCandidato($id_candidato) {
        $sql = "SELECT * FROM propuestas WHERE id_candidato = ? ORDER BY categoria ASC, id_propuesta DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_candidato);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getById($id_propuesta) {
        $sql = "SELECT * FROM propuestas WHERE id_propuesta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_propuesta);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function add($id_candidato, $titulo, $descripcion, $categoria) {
        $sql = "INSERT INTO propuestas (id_candidato, titulo, descripcion, categoria) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("isss", $id_candidato, $titulo, $descripcion, $categoria);
        return $stmt->execute();
    }
    
    public function update($id_propuesta, $titulo, $descripcion, $categoria) {
        $sql = "UPDATE propuestas SET titulo = ?, descripcion = ?, categoria = ? WHERE id_propuesta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $titulo, $descripcion, $categoria, $id_propuesta);
        return $stmt->execute();
    }
    
    public function delete($id_propuesta) {
        $sql = "DELETE FROM propuestas WHERE id_propuesta = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_propuesta);
        return $stmt->execute();
    }
}
?>