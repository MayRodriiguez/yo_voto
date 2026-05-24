<?php
require_once 'config/database.php';

class Equipo {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getByCandidato($id_candidato) {
        $sql = "SELECT * FROM equipo WHERE id_candidato = ? ORDER BY nivel ASC, id_integrante ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_candidato);
        $stmt->execute();
        $result = $stmt->get_result();
        $integrantes = [];
        while ($row = $result->fetch_assoc()) {
            $integrantes[$row['nivel']][] = $row;
        }
        return $integrantes;
    }
    
    public function getAllByCandidato($id_candidato) {
        $sql = "SELECT * FROM equipo WHERE id_candidato = ? ORDER BY nivel ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_candidato);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function add($id_candidato, $nombre, $cargo, $nivel) {
        $sql = "INSERT INTO equipo (id_candidato, nombre, cargo, nivel) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $id_candidato, $nombre, $cargo, $nivel);
        return $stmt->execute();
    }
    
    public function delete($id_integrante) {
        $sql = "DELETE FROM equipo WHERE id_integrante = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_integrante);
        return $stmt->execute();
    }
    public function getById($id_integrante) {
    $sql = "SELECT * FROM equipo WHERE id_integrante = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id_integrante);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

public function update($id_integrante, $nombre, $cargo, $nivel) {
    $sql = "UPDATE equipo SET nombre = ?, cargo = ?, nivel = ? WHERE id_integrante = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssii", $nombre, $cargo, $nivel, $id_integrante);
    return $stmt->execute();
}
}
?>