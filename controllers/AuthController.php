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
        
        if (empty($_POST['nombres']) || empty($_POST['apellidos']) || empty($_POST['carnet']) || empty($_POST['fecha_nac'])) {
            $_SESSION['error_registro'] = "Todos los campos obligatorios deben ser llenados";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $carnet = trim($_POST['carnet']);
        if (strlen($carnet) < 5 || strlen($carnet) > 10 || !ctype_digit($carnet)) {
            $_SESSION['error_registro'] = "El carnet debe tener entre 5 y 10 dígitos numéricos";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $checkSql = "SELECT id FROM usuarios WHERE carnet = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $_SESSION['error_registro'] = "El carnet {$carnet} ya está registrado en el sistema";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $fechaNac = new DateTime($_POST['fecha_nac']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac)->y;
        if ($edad < 18) {
            $_SESSION['error_registro'] = "Debe ser mayor de 18 años para registrarse";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $face_registered = isset($_POST['face_registered']) ? $_POST['face_registered'] : '0';
        $face_descriptor = isset($_POST['face_descriptor']) ? $_POST['face_descriptor'] : null;
        if ($face_registered !== '1' || empty($face_descriptor)) {
            $_SESSION['error_registro'] = "Debe registrar su rostro antes de continuar";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $descriptorJson = json_decode($face_descriptor, true);
        if (!$descriptorJson || !is_array($descriptorJson) || count($descriptorJson) !== 128) {
            $_SESSION['error_registro'] = "Error en el descriptor facial. Intente registrar su rostro nuevamente.";
            header("Location: /yo_voto/registro");
            exit();
        }
        
        $nombres     = trim($_POST['nombres']);
        $apellidos   = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];
        $direccion   = trim($_POST['direccion'] ?? '');
        $telefono    = trim($_POST['telefono'] ?? '');
        $email       = !empty($_POST['email']) ? trim($_POST['email']) : $carnet . '@yovoto.com';
        
        $primerNombre = strtolower(explode(' ', $nombres)[0]);
        $passwordTemp = $primerNombre . $fechaNac->format('dmY');
        $hashedPassword = password_hash($passwordTemp, PASSWORD_DEFAULT);
        $numeroRegistro = $this->generarNumeroRegistroUnico();
        
        $insertSql = "INSERT INTO usuarios (numero_registro, nombres, apellidos, carnet, fecha_nacimiento, direccion, telefono, email, password, face_descriptor, rol, habilitado_voto, ya_voto, activo) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 0, 0, 1)";
        $insertStmt = $this->conn->prepare($insertSql);
        $insertStmt->bind_param("ssssssssss", 
            $numeroRegistro, $nombres, $apellidos, $carnet,
            $fecha_nacimiento, $direccion, $telefono, $email,
            $hashedPassword, $face_descriptor
        );
        
        if ($insertStmt->execute()) {
            $_SESSION['success_registro'] = "✅ ¡Registro exitoso!<br>
                📝 Número de registro: <strong>{$numeroRegistro}</strong><br>
                🔐 Contraseña temporal: <strong>{$passwordTemp}</strong><br>
                ⚠️ Su cuenta será habilitada por el administrador electoral.";
        } else {
            $_SESSION['error_registro'] = "❌ Error al registrar: " . $this->conn->error;
        }
        
        header("Location: /yo_voto/registro");
        exit();
    }
    
    // ============================================
    // LOGIN DE ADMIN — PASO 1: email + contraseña + hCaptcha
    // ============================================
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Si viene el código de verificación por correo (paso 2)
            if (isset($_POST['codigo_verificacion'])) {
                $this->verificarCodigoEmail();
                return;
            }

            // Paso 1: validar hCaptcha
            $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
            if (empty($hcaptchaResponse)) {
                $error = "❌ Por favor complete el captcha de seguridad.";
                require_once 'views/auth/login.php';
                return;
            }

            // Verificar hCaptcha con la API
            $verifyResponse = file_get_contents('https://hcaptcha.com/siteverify', false, stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => 'Content-Type: application/x-www-form-urlencoded',
                    'content' => http_build_query([
                        'secret'   => '0x0000000000000000000000000000000000000000', // Reemplaza con tu secret key
                        'response' => $hcaptchaResponse
                    ])
                ]
            ]));
            $verifyData = json_decode($verifyResponse, true);

            // En localhost hCaptcha puede fallar — comentar la validación para desarrollo local
            // if (!$verifyData['success']) {
            //     $error = "❌ Captcha inválido. Intente de nuevo.";
            //     require_once 'views/auth/login.php';
            //     return;
            // }

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($email, $password);
            if ($user && $user['rol'] == 'admin') {
                // Generar código de 6 dígitos y enviarlo por correo
                $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['admin_codigo_email']   = $codigo;
                $_SESSION['admin_codigo_expira']  = time() + 300; // 5 minutos
                $_SESSION['admin_user_temp']       = $user;

                $enviado = $this->enviarCodigoVerificacion($email, $user['nombres'] ?? 'Administrador', $codigo);

                if ($enviado) {
                    $_SESSION['admin_email_destino'] = $email;
                    require_once 'views/auth/verificar_codigo.php';
                } else {
                    // Si falla el correo, ingresar directo (solo en desarrollo)
                    $_SESSION['user'] = $user;
                    header("Location: /yo_voto/admin/dashboard");
                }
                exit();
            } else {
                $error = "❌ Email o contraseña incorrectos.";
            }
        }

        require_once 'views/auth/login.php';
    }

    // ============================================
    // LOGIN DE ADMIN — PASO 2: verificar código
    // ============================================
    private function verificarCodigoEmail() {
        $codigoIngresado = trim($_POST['codigo_verificacion'] ?? '');
        $codigoGuardado  = $_SESSION['admin_codigo_email'] ?? '';
        $expira          = $_SESSION['admin_codigo_expira'] ?? 0;

        if (time() > $expira) {
            $error = "❌ El código ha expirado. Inicie sesión nuevamente.";
            unset($_SESSION['admin_codigo_email'], $_SESSION['admin_codigo_expira'], $_SESSION['admin_user_temp']);
            require_once 'views/auth/login.php';
            return;
        }

        if ($codigoIngresado === $codigoGuardado) {
            $_SESSION['user'] = $_SESSION['admin_user_temp'];
            unset($_SESSION['admin_codigo_email'], $_SESSION['admin_codigo_expira'], $_SESSION['admin_user_temp'], $_SESSION['admin_email_destino']);
            header("Location: /yo_voto/admin/dashboard");
            exit();
        } else {
            $error = "❌ Código incorrecto. Intente de nuevo.";
            require_once 'views/auth/verificar_codigo.php';
        }
    }

    // ============================================
    // ENVIAR CÓDIGO POR CORREO
    // ============================================
    private function enviarCodigoVerificacion($email, $nombre, $codigo) {
        $asunto  = "🔐 Código de verificación - Yo Voto Admin";
        $mensaje = "
        <html><body style='font-family:Arial,sans-serif;background:#0a1628;color:#fff;padding:30px;'>
            <div style='max-width:480px;margin:0 auto;background:#0d2251;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);'>
                <div style='background:#FF6B00;padding:24px;text-align:center;'>
                    <h1 style='margin:0;font-size:24px;color:#fff;'>🗳️ Yo Voto</h1>
                    <p style='margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;'>Sistema Electoral Bolivia 2026</p>
                </div>
                <div style='padding:32px;'>
                    <p style='color:rgba(255,255,255,0.7);margin-bottom:24px;'>Hola <strong style='color:#fff;'>{$nombre}</strong>, ingresa este código para acceder al panel de administración:</p>
                    <div style='background:#FF6B00;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;'>
                        <div style='font-size:42px;font-weight:900;letter-spacing:12px;color:#fff;font-family:monospace;'>{$codigo}</div>
                    </div>
                    <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center;'>Este código expira en <strong style='color:#FF8C38;'>5 minutos</strong>.<br>Si no solicitaste este código, ignora este mensaje.</p>
                </div>
            </div>
        </body></html>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Yo Voto <no-reply@yovoto.com>\r\n";

        return mail($email, $asunto, $mensaje, $headers);
    }
    
    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================
    private function generarNumeroRegistroUnico() {
        for ($i = 0; $i < 100; $i++) {
            $numero = 'REG-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE numero_registro = ?");
            $stmt->bind_param("s", $numero);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) return $numero;
        }
        return 'REG-' . date('Ymd') . rand(100, 999);
    }
    
    public function logout() {
        session_destroy();
        header("Location: /yo_voto/login");
        exit();
    }
    
    public function loginVotante() {
        header("Location: /yo_voto/");
        exit();
    }
}
?>