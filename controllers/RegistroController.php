<?php
// controllers/RegistroController.php

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

        if (strlen($carnet) !== 8 || !ctype_digit($carnet)) {

            $_SESSION['error'] = "El carnet debe tener exactamente 8 dígitos numéricos";

            header("Location: /yo_voto/admin/registro");

            exit();
        }

        $checkSql = "SELECT id FROM usuarios WHERE carnet = ?";

        $checkStmt = $this->conn->prepare($checkSql);

        $checkStmt->bind_param("s", $carnet);

        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {

            $_SESSION['error'] = "El carnet ya está registrado";

            header("Location: /yo_voto/admin/registro");

            exit();
        }

        $fechaNac = new DateTime($_POST['fecha_nac']);

        $hoy = new DateTime();

        $edad = $hoy->diff($fechaNac)->y;

        if ($edad < 18) {

            $_SESSION['error'] = "El ciudadano debe ser mayor de 18 años";

            header("Location: /yo_voto/admin/registro");

            exit();
        }

        $face_registered = $_POST['face_registered'] ?? '0';

        $face_descriptor = $_POST['face_descriptor'] ?? null;

        if ($face_registered !== '1' || empty($face_descriptor)) {

            $_SESSION['error'] = "Debe registrar el rostro del ciudadano";

            header("Location: /yo_voto/admin/registro");

            exit();
        }

        $descriptorJson = json_decode($face_descriptor, true);

        if (
            !$descriptorJson ||
            !is_array($descriptorJson) ||
            count($descriptorJson) !== 128
        ) {

            $_SESSION['error'] = "Error en reconocimiento facial";

            header("Location: /yo_voto/admin/registro");

            exit();
        }

        $nombres = trim($_POST['nombres']);

        $apellidos = trim($_POST['apellidos']);

        $fecha_nacimiento = $_POST['fecha_nac'];

        $direccion = trim($_POST['direccion'] ?? '');

        $telefono = trim($_POST['telefono'] ?? '');

        $email = !empty($_POST['email'])
            ? trim($_POST['email'])
            : $carnet . '@yovoto.com';

        $primerNombre = strtolower(explode(' ', $nombres)[0]);

        $passwordTemp = $primerNombre . $fechaNac->format('dmY');

        $hashedPassword = password_hash($passwordTemp, PASSWORD_DEFAULT);

        $habilitado_voto = isset($_POST['habilitar_voto']) ? 1 : 0;

        $insertSql = "INSERT INTO usuarios (
            nombres,
            apellidos,
            carnet,
            fecha_nacimiento,
            direccion,
            telefono,
            email,
            password,
            face_descriptor,
            rol,
            habilitado_voto,
            ya_voto,
            activo
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', ?, 0, 1
        )";

        $insertStmt = $this->conn->prepare($insertSql);

        $insertStmt->bind_param(
            "sssssssssi",
            $nombres,
            $apellidos,
            $carnet,
            $fecha_nacimiento,
            $direccion,
            $telefono,
            $email,
            $hashedPassword,
            $face_descriptor,
            $habilitado_voto
        );

        if ($insertStmt->execute()) {

            $_SESSION['mensaje'] =
                "Ciudadano registrado correctamente";

        } else {

            $_SESSION['error'] =
                "Error al registrar: " . $this->conn->error;
        }

        header("Location: /yo_voto/admin/registro");

        exit();
    }

    public function editar($id) {

        $sql = "SELECT * FROM usuarios WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

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

        $sql = "UPDATE usuarios
                SET nombres = ?,
                    apellidos = ?,
                    carnet = ?,
                    direccion = ?,
                    telefono = ?,
                    email = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param(
            "ssssssi",
            $_POST['nombres'],
            $_POST['apellidos'],
            $_POST['carnet'],
            $_POST['direccion'],
            $_POST['telefono'],
            $_POST['email'],
            $id
        );

        if ($stmt->execute()) {

            $_SESSION['mensaje'] =
                "Ciudadano actualizado correctamente";

        } else {

            $_SESSION['error'] =
                "No se pudo actualizar";
        }

        header("Location: /yo_voto/admin/registro");

        exit();
    }

}
?>