<?php
// controllers/AuthController.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'models/User.php';

class AuthController {

    private $userModel;
    private $conn;

    public function __construct() {
        $this->userModel = new User();

        $database = new Database();
        $this->conn = $database->getConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // ============================================
    // REGISTRO DE CIUDADANO
    // ============================================
    public function registroCiudadano() {

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: /yo_voto/registro");
            exit();
        }

        // Validar campos obligatorios
        if (
            empty($_POST['nombres']) ||
            empty($_POST['apellidos']) ||
            empty($_POST['carnet']) ||
            empty($_POST['fecha_nac'])
        ) {
            $_SESSION['error_registro'] =
                "Todos los campos obligatorios deben ser llenados";

            header("Location: /yo_voto/registro");
            exit();
        }

        $carnet = trim($_POST['carnet']);

        // Validar carnet
        if (strlen($carnet) !== 8 || !ctype_digit($carnet)) {

            $_SESSION['error_registro'] =
                "El carnet debe tener exactamente 8 dígitos numéricos";

            header("Location: /yo_voto/registro");
            exit();
        }

        // Verificar si ya existe
        $checkSql = "SELECT id FROM usuarios WHERE carnet = ?";

        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {

            $_SESSION['error_registro'] =
                "El carnet {$carnet} ya está registrado";

            header("Location: /yo_voto/registro");
            exit();
        }

        // Validar edad
        $fechaNac = new DateTime($_POST['fecha_nac']);
        $hoy = new DateTime();

        $edad = $hoy->diff($fechaNac)->y;

        if ($edad < 18) {

            $_SESSION['error_registro'] =
                "Debe ser mayor de 18 años";

            header("Location: /yo_voto/registro");
            exit();
        }

        // Validación facial
        $face_registered = $_POST['face_registered'] ?? '0';
        $face_descriptor = $_POST['face_descriptor'] ?? null;

        if ($face_registered !== '1' || empty($face_descriptor)) {

            $_SESSION['error_registro'] =
                "Debe registrar su rostro";

            header("Location: /yo_voto/registro");
            exit();
        }

        // Validar descriptor
        $descriptorJson = json_decode($face_descriptor, true);

        if (
            !$descriptorJson ||
            !is_array($descriptorJson) ||
            count($descriptorJson) !== 128
        ) {

            $_SESSION['error_registro'] =
                "Error en el descriptor facial";

            header("Location: /yo_voto/registro");
            exit();
        }

        // Datos
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];

        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        $email = !empty($_POST['email'])
            ? trim($_POST['email'])
            : $carnet . '@yovoto.com';

        // Contraseña temporal
        $primerNombre = strtolower(explode(' ', $nombres)[0]);

        $passwordTemp = $primerNombre . $fechaNac->format('dmY');

        $hashedPassword =
            password_hash($passwordTemp, PASSWORD_DEFAULT);

        // Generar registro
        $numeroRegistro =
            $this->generarNumeroRegistroUnico();

        // Insertar
        $insertSql = "
            INSERT INTO usuarios (
                numero_registro,
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
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                'usuario',
                0,
                0,
                1
            )
        ";

        $insertStmt = $this->conn->prepare($insertSql);

        $insertStmt->bind_param(
            "ssssssssss",
            $numeroRegistro,
            $nombres,
            $apellidos,
            $carnet,
            $fecha_nacimiento,
            $direccion,
            $telefono,
            $email,
            $hashedPassword,
            $face_descriptor
        );

        if ($insertStmt->execute()) {

            $_SESSION['success_registro'] =
                "✅ Registro exitoso<br>
                Número: <strong>{$numeroRegistro}</strong><br>
                Contraseña temporal:
                <strong>{$passwordTemp}</strong>";

        } else {

            $_SESSION['error_registro'] =
                "❌ Error: " . $this->conn->error;
        }

        header("Location: /yo_voto/registro");
        exit();
    }

    // ============================================
    // LOGIN ADMIN
    // ============================================
    public function login() {

        if (!isset($_SESSION['captcha_codigo'])) {
            $_SESSION['captcha_codigo'] =
                $this->generarCodigoCaptcha();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $captcha_usuario =
                strtoupper(trim($_POST['captcha'] ?? ''));

            $captcha_guardado =
                $_SESSION['captcha_codigo'] ?? '';

            // Validar captcha
            if (
                empty($captcha_usuario) ||
                $captcha_usuario !== $captcha_guardado
            ) {

                $error =
                    "❌ Código de seguridad incorrecto";

                $_SESSION['captcha_codigo'] =
                    $this->generarCodigoCaptcha();

            } else {

                $user =
                    $this->userModel->login($email, $password);

                if ($user && $user['rol'] == 'admin') {

                    $_SESSION['user'] = $user;

                    header("Location: /yo_voto/admin/dashboard");
                    exit();

                } else {

                    $error =
                        "❌ Email o contraseña incorrectos";

                    $_SESSION['captcha_codigo'] =
                        $this->generarCodigoCaptcha();
                }
            }
        }

        require_once 'views/auth/login.php';
    }

    // ============================================
    // CAPTCHA
    // ============================================
    private function generarCodigoCaptcha() {

        $caracteres =
            'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        $codigo = '';

        for ($i = 0; $i < 6; $i++) {

            $codigo .=
                $caracteres[rand(0, strlen($caracteres) - 1)];
        }

        return $codigo;
    }

    // ============================================
    // REGISTRO ÚNICO
    // ============================================
    private function generarNumeroRegistroUnico() {

        for ($i = 0; $i < 100; $i++) {

            $numero =
                str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

            $numeroRegistro = 'REG-' . $numero;

            $sql =
                "SELECT id FROM usuarios WHERE numero_registro = ?";

            $stmt = $this->conn->prepare($sql);

            $stmt->bind_param("s", $numeroRegistro);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return $numeroRegistro;
            }
        }

        return 'REG-' . date('Ymd') . rand(100, 999);
    }

    // ============================================
    // LOGOUT
    // ============================================
    public function logout() {

        session_destroy();

        header("Location: /yo_voto/login");
        exit();
    }

    // ============================================
    // LOGIN VOTANTE
    // ============================================
    public function loginVotante() {

        header("Location: /yo_voto/");
        exit();
    }
}
?>