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
    // REGISTRO DE CIUDADANO (PÚBLICO)
    // ============================================
    public function registroCiudadano() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Validar datos requeridos
        if (empty($_POST['nombres']) || empty($_POST['apellidos']) || empty($_POST['carnet']) || empty($_POST['fecha_nac'])) {
            $_SESSION['error_registro'] = "Todos los campos obligatorios deben ser llenados";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $carnet = trim($_POST['carnet']);
        if (strlen($carnet) !== 8 || !ctype_digit($carnet)) {
            $_SESSION['error_registro'] = "El carnet debe tener exactamente 8 dígitos numéricos";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Verificar si el carnet ya existe
        $checkSql = "SELECT id FROM usuarios WHERE carnet = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['error_registro'] = "El carnet {$carnet} ya está registrado en el sistema";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Validar edad (mayor de 18 años)
        $fechaNac = new DateTime($_POST['fecha_nac']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac)->y;
        
        if ($edad < 18) {
            $_SESSION['error_registro'] = "Debe ser mayor de 18 años para registrarse";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Validar registro facial
        $face_registered = isset($_POST['face_registered']) ? $_POST['face_registered'] : '0';
        $face_descriptor = isset($_POST['face_descriptor']) ? $_POST['face_descriptor'] : null;
        
        if ($face_registered !== '1' || empty($face_descriptor)) {
            $_SESSION['error_registro'] = "Debe registrar su rostro antes de continuar";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Validar descriptor facial
        $descriptorJson = json_decode($face_descriptor, true);
        if (!$descriptorJson || !is_array($descriptorJson) || count($descriptorJson) !== 128) {
            $_SESSION['error_registro'] = "Error en el descriptor facial. Intente registrar su rostro nuevamente.";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        // Preparar datos
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = !empty($_POST['email']) ? trim($_POST['email']) : $carnet . '@yovoto.com';
        
        // Generar contraseña automática (para compatibilidad, pero no se usará)
        $primerNombre = strtolower(explode(' ', $nombres)[0]);
        $passwordTemp = $primerNombre . $fechaNac->format('dmY');
        $hashedPassword = password_hash($passwordTemp, PASSWORD_DEFAULT);
        
        // Generar número de registro único
        $numeroRegistro = $this->generarNumeroRegistroUnico();
        
        // Insertar usuario (por defecto habilitado_voto = 0, requiere aprobación del admin)
        $insertSql = "INSERT INTO usuarios (numero_registro, nombres, apellidos, carnet, fecha_nacimiento, direccion, telefono, email, password, face_descriptor, rol, habilitado_voto, ya_voto, activo) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 0, 0, 1)";
        
        $insertStmt = $this->conn->prepare($insertSql);
        $insertStmt->bind_param("ssssssssss", 
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
            $_SESSION['success_registro'] = "✅ ¡Registro exitoso!<br>
                                             📝 Su número de registro: <strong>{$numeroRegistro}</strong><br>
                                             🔐 Su contraseña temporal: <strong>{$passwordTemp}</strong><br>
                                             ⚠️ Su cuenta será habilitada por el administrador electoral.<br>
                                             Podrá votar una vez sea habilitado.";
        } else {
            $_SESSION['error_registro'] = "❌ Error al registrar: " . $this->conn->error;
        }
        
        header("Location: /yo_voto/registro");
        exit();
    }
    
    // ============================================
    // LOGIN DE ADMIN (EMAIL + CONTRASEÑA + CAPTCHA TEXTO)
    // ============================================
    public function login() {
        // Generar CAPTCHA si no existe
        if (!isset($_SESSION['captcha_codigo'])) {
            $_SESSION['captcha_codigo'] = $this->generarCodigoCaptcha();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $captcha_usuario = strtoupper(trim($_POST['captcha'] ?? ''));
            $captcha_guardado = $_SESSION['captcha_codigo'] ?? '';
            
            // Validar CAPTCHA
            if (empty($captcha_usuario) || $captcha_usuario !== $captcha_guardado) {
                $error = "❌ Código de seguridad incorrecto";
                $_SESSION['captcha_codigo'] = $this->generarCodigoCaptcha();
            } else {
                $user = $this->userModel->login($email, $password);
                if ($user && $user['rol'] == 'admin') {
                    $_SESSION['user'] = $user;
                    header("Location: /yo_voto/admin/dashboard");
                    exit();
                } else {
                    $error = "❌ Email o contraseña incorrectos";
                    $_SESSION['captcha_codigo'] = $this->generarCodigoCaptcha();
                }
            }
        }
        require_once 'views/auth/login.php';
    }
    
    // ============================================
    // LOGIN CON RECONOCIMIENTO FACIAL (API)
    // ============================================
    public function loginFacial() {
        // Este método ahora está en api/face_routes.php
        // Se maneja via API
    }
    
    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    
    /**
     * Genera código CAPTCHA de 6 caracteres (letras mayúsculas + números)
     */
    private function generarCodigoCaptcha() {
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $codigo = '';
        for ($i = 0; $i < 6; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }
    
    private function generarNumeroRegistroUnico() {
        $maxIntentos = 100;
        
        for ($i = 0; $i < $maxIntentos; $i++) {
            $numero = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $numeroRegistro = 'REG-' . $numero;
            
            $sql = "SELECT id FROM usuarios WHERE numero_registro = ?";
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
    
    public function logout() {
        session_destroy();
        header("Location: /yo_voto/login");
        exit();
    }
    
    public function loginVotante() {
        // Método legacy - ya no se usa, ahora es solo facial
        header("Location: /yo_voto/");
        exit();
    }
}
?>