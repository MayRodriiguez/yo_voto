<?php
require_once 'config/database.php';

class Candidato {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $sql = "SELECT * FROM candidatos ORDER BY fecha_postulacion DESC";
        $result = $this->conn->query($sql);
        $candidatos = [];
        while ($row = $result->fetch_assoc()) {
            $candidatos[] = $row;
        }
        return $candidatos;
    }
    
    public function getAllActivos() {
        $sql = "SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC";
        $result = $this->conn->query($sql);
        $candidatos = [];
        while ($row = $result->fetch_assoc()) {
            $candidatos[] = $row;
        }
        return $candidatos;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM candidatos WHERE id_candidato = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getResultados() {
        $sql = "SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC";
        $result = $this->conn->query($sql);
        $candidatos = [];
        while ($row = $result->fetch_assoc()) {
            $candidatos[] = $row;
        }
        return $candidatos;
    }
    
    // DESPUÉS
public function create($data, $foto_url) {
    $tipo = $data['tipo'] ?? 'nacional';
    $sql = "INSERT INTO candidatos (nombre, partido, cargo, biografia, foto_url, fecha_postulacion, estado, tipo) 
            VALUES (?, ?, ?, ?, ?, ?, 'activo', ?)";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sssssss", $data['nombre'], $data['partido'], $data['cargo'], 
                     $data['biografia'], $foto_url, $data['fecha_postulacion'], $tipo);
    return $stmt->execute();
}
    
    public function update($id, $data) {
        $sql = "UPDATE candidatos SET nombre=?, partido=?, cargo=?, biografia=?, fecha_postulacion=? WHERE id_candidato=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $data['nombre'], $data['partido'], $data['cargo'], 
                         $data['biografia'], $data['fecha_postulacion'], $id);
        return $stmt->execute();
    }
    
    public function toggleStatus($id) {
        $candidato = $this->getById($id);
        $nuevoEstado = $candidato['estado'] == 'activo' ? 'inactivo' : 'activo';
        $sql = "UPDATE candidatos SET estado = ? WHERE id_candidato = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $id);
        return $stmt->execute();
    }
    
    public function delete($id) {
        // Eliminar dependencias primero
        $this->conn->query("DELETE FROM equipo WHERE id_candidato = $id");
        $this->conn->query("DELETE FROM propuestas WHERE id_candidato = $id");
        
        $sql = "DELETE FROM candidatos WHERE id_candidato = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    public function getAllByTipo($tipo) {
    $sql = "SELECT * FROM candidatos WHERE tipo = ? ORDER BY fecha_postulacion DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $candidatos = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $candidatos[] = $row;
    }
    return $candidatos;
}
}
?>