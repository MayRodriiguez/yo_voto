<?php
require_once 'config/database.php';

class Jurado {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAll() {
        $sql = "SELECT j.*, u.nombres, u.apellidos, u.email, u.carnet 
                FROM jurados j 
                LEFT JOIN usuarios u ON j.id_usuario = u.id 
                ORDER BY j.id DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function generateCode() {
        return "JUR-" . strtoupper(substr(md5(uniqid()), 0, 8));
    }
    
    public function assign($id_usuario, $id_mesa, $cargo) {
        $codigo = $this->generateCode();
        $sql = "INSERT INTO jurados (codigo_jurado, id_usuario, id_mesa, cargo_jurado, fecha_asignacion, confirmado, activo) 
                VALUES (?, ?, ?, ?, CURDATE(), 0, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siis", $codigo, $id_usuario, $id_mesa, $cargo);
        return $stmt->execute();
    }
    
    public function delete($id) {
        $sql = "DELETE FROM jurados WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function getUsuariosSinJurado() {
        $sql = "SELECT u.id, u.nombres, u.apellidos, u.carnet 
                FROM usuarios u 
                WHERE u.id NOT IN (SELECT id_usuario FROM jurados WHERE id_usuario IS NOT NULL)
                AND u.rol = 'usuario'";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>