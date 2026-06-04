<?php
require_once 'config/database.php';

class User {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function generarNumeroRegistro() {
        for ($i = 0; $i < 100; $i++) {
            $numero = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE numero_registro = ?");
            $stmt->bind_param("s", $numero);
            $stmt->execute();
            if ($stmt->get_result()->num_rows == 0) return $numero;
        }
        return date('YmdHis') . rand(10, 99);
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($user = $stmt->get_result()->fetch_assoc()) {
            if (password_verify($password, $user['password'])) return $user;
        }
        return false;
    }

    public function loginByCarnet($carnet, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE carnet = ? AND activo = 1");
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        if ($user = $stmt->get_result()->fetch_assoc()) {
            if (password_verify($password, $user['password'])) return $user;
        }
        return false;
    }

    public function yaVoto($id_usuario) {
        $stmt = $this->conn->prepare("SELECT ya_voto FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        return $user['ya_voto'] == 1;
    }

    public function marcarComoVotado($id) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET ya_voto = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM usuarios ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateVoteStatus($id, $habilitado) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET habilitado_voto = ? WHERE id = ?");
        $stmt->bind_param("ii", $habilitado, $id);
        return $stmt->execute();
    }

    public function getUserByCarnet($carnet) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE carnet = ?");
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByNumeroRegistro($numeroRegistro) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE numero_registro = ?");
        $stmt->bind_param("s", $numeroRegistro);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>