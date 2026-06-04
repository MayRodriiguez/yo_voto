<?php

require_once 'config/database.php';
require_once 'models/User.php';

class RegistroController {

    private $userModel;
    private $conn;

    public function __construct() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }

        $this->userModel = new User();

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function index() {
        $usuarios = $this->userModel->getAll();
        require_once 'views/admin/registro_ciudadano.php';
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        if (
            empty($_POST['nombres']) ||
            empty($_POST['apellidos']) ||
            empty($_POST['carnet']) ||
            empty($_POST['fecha_nac'])
        ) {
            $_SESSION['error'] = "Todos los campos obligatorios deben ser llenados";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        $carnet = trim($_POST['carnet']);

        if (strlen($carnet) < 5 || strlen($carnet) > 10 || !ctype_digit($carnet)) {
            $_SESSION['error'] = "El carnet debe tener entre 5 y 10 dígitos numéricos";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        $checkStmt = $this->conn->prepare("SELECT id FROM usuarios WHERE carnet = ?");
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();

        if ($checkStmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "El carnet ya está registrado";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        $fechaNac = new DateTime($_POST['fecha_nac']);
        $edad = (new DateTime())->diff($fechaNac)->y;

        if ($edad < 18) {
            $_SESSION['error'] = "El ciudadano debe ser mayor de 18 años";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        $nombres         = trim($_POST['nombres']);
        $apellidos       = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];
        $direccion       = trim($_POST['direccion'] ?? '');
        $telefono        = trim($_POST['telefono'] ?? '');
        $email           = !empty($_POST['email']) ? trim($_POST['email']) : $carnet . '@yovoto.com';

        $primerNombre   = strtolower(explode(' ', $nombres)[0]);
        $passwordTemp   = $primerNombre . $fechaNac->format('dmY');
        $hashedPassword = password_hash($passwordTemp, PASSWORD_DEFAULT);

        $habilitado_voto = isset($_POST['habilitar_voto']) ? 1 : 0;

        $numeroRegistro = $this->generarNumeroRegistroUnico();

        $insertSql = "INSERT INTO usuarios (
            numero_registro, nombres, apellidos, carnet,
            fecha_nacimiento, direccion, telefono, email,
            password, rol, habilitado_voto, ya_voto, activo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', ?, 0, 1)";

        $insertStmt = $this->conn->prepare($insertSql);
        $insertStmt->bind_param(
            "ssssssssi",
            $numeroRegistro, $nombres, $apellidos, $carnet,
            $fecha_nacimiento, $direccion, $telefono, $email,
            $hashedPassword, $habilitado_voto
        );

        if ($insertStmt->execute()) {
            $_SESSION['mensaje'] = "Ciudadano registrado correctamente. Contraseña temporal: " . $passwordTemp;
        } else {
            $_SESSION['error'] = "Error al registrar: " . $this->conn->error;
        }

        header("Location: /yo_voto/admin/registro");
        exit();
    }

    public function editar($id) {

        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();

        if (!$usuario) {
            $_SESSION['error'] = "Ciudadano no encontrado";
            header("Location: /yo_voto/admin/registro");
            exit();
        }

        require_once 'views/admin/editar_ciudadano.php';
    }

    public function actualizar($id) {

        $sql = "UPDATE usuarios SET
                    nombres = ?, apellidos = ?, carnet = ?,
                    direccion = ?, telefono = ?, email = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "ssssssi",
            $_POST['nombres'], $_POST['apellidos'], $_POST['carnet'],
            $_POST['direccion'], $_POST['telefono'], $_POST['email'],
            $id
        );

        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Ciudadano actualizado correctamente";
        } else {
            $_SESSION['error'] = "No se pudo actualizar";
        }

        header("Location: /yo_voto/admin/registro");
        exit();
    }

    private function generarNumeroRegistroUnico() {

        for ($i = 0; $i < 100; $i++) {
            $numeroRegistro = 'REG-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE numero_registro = ?");
            $stmt->bind_param("s", $numeroRegistro);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) return $numeroRegistro;
        }

        return 'REG-' . date('Ymd') . rand(100, 999);
    }
}
?>