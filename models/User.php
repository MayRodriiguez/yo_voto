<?php
require_once 'config/database.php';

class User {

    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function generarNumeroRegistro() {

        $maxIntentos = 100;

        for ($i = 0; $i < $maxIntentos; $i++) {

            $numero = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $sql = "SELECT id FROM usuarios WHERE numero_registro = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $numero);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                return $numero;
            }
        }

        return date('YmdHis') . rand(10,99);
    }

    public function login($email, $password) {

        $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {

            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }

        return false;
    }

    public function loginByCarnet($carnet, $password) {

        $sql = "SELECT * FROM usuarios WHERE carnet = ? AND activo = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $carnet);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {

            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }

        return false;
    }

    public function registerComplete($data, $huella = null) {

        $numeroRegistro = $this->generarNumeroRegistro();

        $sql = "INSERT INTO usuarios (
                    numero_registro,
                    nombres,
                    apellidos,
                    carnet,
                    fecha_nacimiento,
                    direccion,
                    telefono,
                    email,
                    password,
                    huella_digital,
                    rol,
                    habilitado_voto,
                    ya_voto,
                    activo
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', ?, 0, 1
                )";

        $stmt = $this->conn->prepare($sql);

        $habilitado = isset($data['habilitar_voto']) ? 1 : 0;

        $stmt->bind_param(
            "ssssssssssi",
            $numeroRegistro,
            $data['nombres'],
            $data['apellidos'],
            $data['carnet'],
            $data['fecha_nac'],
            $data['direccion'],
            $data['telefono'],
            $data['email'],
            $data['password'],
            $huella,
            $habilitado
        );

        if ($stmt->execute()) {
            return $numeroRegistro;
        }

        return false;
    }

    public function yaVoto($id_usuario) {

        $sql = "SELECT ya_voto FROM usuarios WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        return $user['ya_voto'] == 1;
    }

    public function marcarComoVotado($id) {

        $sql = "UPDATE usuarios SET ya_voto = 1 WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function getAll() {

        $sql = "SELECT * FROM usuarios ORDER BY id DESC";

        $result = $this->conn->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {

        $sql = "SELECT * FROM usuarios WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function updateVoteStatus($id, $habilitado) {

        $sql = "UPDATE usuarios SET habilitado_voto = ? WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $habilitado, $id);

        return $stmt->execute();
    }

    public function getUserByCarnet($carnet) {

        $sql = "SELECT * FROM usuarios WHERE carnet = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $carnet);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByNumeroRegistro($numeroRegistro) {

        $sql = "SELECT * FROM usuarios WHERE numero_registro = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $numeroRegistro);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }
}
?>