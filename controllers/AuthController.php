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

    public function loginVotante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /yo_voto/");
            exit();
        }

        //  Protección fuerza bruta 
        $intentos     = $_SESSION['login_intentos']     ?? 0;
        $bloqueadoHasta = $_SESSION['login_bloqueado_hasta'] ?? 0;

        if ($bloqueadoHasta > time()) {
            $segundos = $bloqueadoHasta - time();
            $_SESSION['error_login'] = " Demasiados intentos fallidos. Espera {$segundos} segundos para intentar de nuevo.";
            header("Location: /yo_voto/");
            exit();
        }

        $tokenEnviado = $_POST['csrf_token'] ?? '';
        $tokenSesion  = $_SESSION['csrf_token'] ?? '';
        if (empty($tokenEnviado) || $tokenEnviado !== $tokenSesion) {
            $_SESSION['error_login'] = " Token de seguridad inválido. Intenta de nuevo.";
            header("Location: /yo_voto/");
            exit();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        $carnet   = trim($_POST['carnet'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($carnet) || empty($password)) {
            $_SESSION['error_login'] = " Ingresa tu carnet y contraseña.";
            header("Location: /yo_voto/");
            exit();
        }

        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE carnet = ? AND rol = 'usuario' AND activo = 1");
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            // Contar intento fallido
            $_SESSION['login_intentos'] = $intentos + 1;
            if ($_SESSION['login_intentos'] >= 3) {
                $_SESSION['login_bloqueado_hasta'] = time() + 60; // bloqueo 60 seg
                $_SESSION['login_intentos'] = 0;
                $_SESSION['error_login'] = " Demasiados intentos fallidos. Espera 60 segundos.";
            } else {
                $restantes = 3 - $_SESSION['login_intentos'];
                $_SESSION['error_login'] = " Carnet no encontrado. Te quedan {$restantes} intentos.";
            }
            header("Location: /yo_voto/");
            exit();
        }

        if (!password_verify($password, $user['password'])) {
            // Contar intento fallido
            $_SESSION['login_intentos'] = $intentos + 1;
            if ($_SESSION['login_intentos'] >= 3) {
                $_SESSION['login_bloqueado_hasta'] = time() + 60;
                $_SESSION['login_intentos'] = 0;
                $_SESSION['error_login'] = " Demasiados intentos fallidos. Espera 60 segundos.";
            } else {
                $restantes = 3 - $_SESSION['login_intentos'];
                $_SESSION['error_login'] = " Contraseña incorrecta. Te quedan {$restantes} intentos.";
            }
            header("Location: /yo_voto/");
            exit();
        }

        if ($user['habilitado_voto'] != 1) {
            $_SESSION['error_login'] = " Tu cuenta aún no está habilitada para votar.";
            header("Location: /yo_voto/");
            exit();
        }

        // Login exitoso — limpiar contadores
        unset($_SESSION['login_intentos'], $_SESSION['login_bloqueado_hasta']);
        session_regenerate_id(true); // Prevenir session fixation
        $_SESSION['user'] = $user;
        header("Location: /yo_voto/mi-perfil");
        exit();
    }

    public function registroCiudadano() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: /yo_voto/registro");
            exit();
        }

        $config = [];
        $resConfig = $this->conn->query("SELECT clave, valor FROM configuracion");
        while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }

        $votacionActiva = $config['votacion_activa'] ?? '0';

        if ($votacionActiva == '1') {
            $_SESSION['error_registro'] = " El registro está cerrado. Las votaciones ya han comenzado.";
            header("Location: /yo_voto/registro");
            exit();
        }

        $tokenEnviado = $_POST['csrf_token'] ?? '';
        $tokenSesion  = $_SESSION['csrf_token'] ?? '';
        if (empty($tokenEnviado) || $tokenEnviado !== $tokenSesion) {
            $_SESSION['error_registro'] = " Token de seguridad inválido.";
            header("Location: /yo_voto/registro");
            exit();
        }
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

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

        $checkStmt = $this->conn->prepare("SELECT id FROM usuarios WHERE carnet = ?");
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            $_SESSION['error_registro'] = " El carnet {$carnet} ya está registrado.";
            header("Location: /yo_voto/registro");
            exit();
        }

        // Validar correo duplicado
        if (!empty($_POST['email'])) {
            $emailCheck = trim($_POST['email']);
            $emailStmt  = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $emailStmt->bind_param("s", $emailCheck);
            $emailStmt->execute();
            if ($emailStmt->get_result()->num_rows > 0) {
                $_SESSION['error_registro'] = " El correo electrónico ya está registrado.";
                header("Location: /yo_voto/registro");
                exit();
            }
        }

        $fechaNac = new DateTime($_POST['fecha_nac']);
        $edad = (new DateTime())->diff($fechaNac)->y;
        if ($edad < 18) {
            $_SESSION['error_registro'] = "Debe ser mayor de 18 años";
            header("Location: /yo_voto/registro");
            exit();
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if (strlen($password) !== 6) {
            $_SESSION['error_registro'] = "La contraseña debe tener exactamente 6 caracteres";
            header("Location: /yo_voto/registro");
            exit();
        }
        if ($password !== $confirm) {
            $_SESSION['error_registro'] = "Las contraseñas no coinciden";
            header("Location: /yo_voto/registro");
            exit();
        }

        // Guardar foto
        $foto_url = 'uploads/img/sin_foto.jpg';
        if (isset($_FILES['foto_rostro']) && $_FILES['foto_rostro']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['foto_rostro']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['foto_rostro']['size'] <= 5 * 1024 * 1024) {
                $uploadDir = 'uploads/img/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $nombreFoto = 'rostro_' . $carnet . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['foto_rostro']['tmp_name'], $uploadDir . $nombreFoto)) {
                    $foto_url = $uploadDir . $nombreFoto;
                }
            }
        }

        $nombres          = trim($_POST['nombres']);
        $apellidos        = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];
        $direccion        = trim($_POST['direccion'] ?? '');
        $telefono         = trim($_POST['telefono'] ?? '');
        $email            = !empty($_POST['email']) ? trim($_POST['email']) : $carnet . '@yovoto.com';
        $departamento     = trim($_POST['departamento'] ?? '');

        // Verificar que el correo fue verificado
        if (empty($_SESSION['reg_email_verificado']) || $_SESSION['reg_email'] !== $email) {
            $_SESSION['error_registro'] = " Debes verificar tu correo electrónico antes de registrarte.";
            header("Location: /yo_voto/registro");
            exit();
        }
        // Limpiar variables de verificación
        unset($_SESSION['reg_email_verificado'], $_SESSION['reg_email_codigo'], $_SESSION['reg_email_expira'], $_SESSION['reg_email']);
        $hashedPassword   = password_hash($password, PASSWORD_DEFAULT);
        $numeroRegistro   = $this->generarNumeroRegistroUnico();

        $sql  = "INSERT INTO usuarios (numero_registro, nombres, apellidos, carnet, fecha_nacimiento, direccion, telefono, email, password, foto_url, departamento, rol, habilitado_voto, ya_voto, activo)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 0, 0, 1)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssssssss",
            $numeroRegistro, $nombres, $apellidos, $carnet,
            $fecha_nacimiento, $direccion, $telefono, $email,
            $hashedPassword, $foto_url, $departamento
        );

        if ($stmt->execute()) {
            $_SESSION['success_registro'] = " ¡Registro exitoso!";
        } else {
            $_SESSION['error_registro'] = " Error al registrar: " . $this->conn->error;
        }

        header("Location: /yo_voto/registro");
        exit();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['codigo_verificacion'])) {
                $this->verificarCodigoEmail();
                return;
            }

            $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
            if (empty($hcaptchaResponse)) {
                $error = " Por favor complete el captcha de seguridad.";
                require_once 'views/auth/login.php';
                return;
            }

            //  Protección fuerza bruta admin 
            $intentosAdmin     = $_SESSION['admin_intentos']       ?? 0;
            $bloqueadoAdminHasta = $_SESSION['admin_bloqueado_hasta'] ?? 0;
            if ($bloqueadoAdminHasta > time()) {
                $seg = $bloqueadoAdminHasta - time();
                $error = " Demasiados intentos. Espera {$seg} segundos.";
                require_once 'views/auth/login.php';
                return;
            }

            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userModel->login($email, $password);
            if ($user && $user['rol'] == 'admin') {
                // Login correcto — limpiar contadores
                unset($_SESSION['admin_intentos'], $_SESSION['admin_bloqueado_hasta']);
                session_regenerate_id(true);

                $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $_SESSION['admin_codigo_email']  = $codigo;
                $_SESSION['admin_codigo_expira'] = time() + 300;
                $_SESSION['admin_user_temp']     = $user;

                $enviado = $this->enviarCodigoVerificacion($email, $user['nombres'] ?? 'Administrador', $codigo);
                if ($enviado) {
                    $_SESSION['admin_email_destino'] = $email;
                    require_once 'views/auth/verificar_codigo.php';
                } else {
                    $_SESSION['user'] = $user;
                    header("Location: /yo_voto/admin/dashboard");
                }
                exit();
            } else {
                // Intento fallido
                $_SESSION['admin_intentos'] = $intentosAdmin + 1;
                if ($_SESSION['admin_intentos'] >= 3) {
                    $_SESSION['admin_bloqueado_hasta'] = time() + 120; // 2 minutos
                    $_SESSION['admin_intentos'] = 0;
                    $error = " Demasiados intentos fallidos. Espera 2 minutos.";
                } else {
                    $restantes = 3 - $_SESSION['admin_intentos'];
                    $error = " Email o contraseña incorrectos. Te quedan {$restantes} intentos.";
                }
            }
        }
        require_once 'views/auth/login.php';
    }

    private function verificarCodigoEmail() {
        $codigoIngresado = trim($_POST['codigo_verificacion'] ?? '');
        $codigoGuardado  = $_SESSION['admin_codigo_email'] ?? '';
        $expira          = $_SESSION['admin_codigo_expira'] ?? 0;

        if (time() > $expira) {
            $error = " El código ha expirado.";
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
            $error = " Código incorrecto.";
            require_once 'views/auth/verificar_codigo.php';
        }
    }

    private function enviarCodigoVerificacion($email, $nombre, $codigo) {
        $asunto  = " Código de verificación - Yo Voto Admin";
        $mensaje = "
        <html><body style='font-family:Arial,sans-serif;background:#0a1628;color:#fff;padding:30px;'>
            <div style='max-width:480px;margin:0 auto;background:#0d2251;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);'>
                <div style='background:#FF6B00;padding:24px;text-align:center;'>
                    <h1 style='margin:0;font-size:24px;color:#fff;'> Yo Voto</h1>
                </div>
                <div style='padding:32px;'>
                    <p style='color:rgba(255,255,255,0.7);margin-bottom:24px;'>Hola <strong style='color:#fff;'>{$nombre}</strong>, tu código:</p>
                    <div style='background:#FF6B00;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;'>
                        <div style='font-size:42px;font-weight:900;letter-spacing:12px;color:#fff;font-family:monospace;'>{$codigo}</div>
                    </div>
                    <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center;'>Expira en <strong style='color:#FF8C38;'>5 minutos</strong>.</p>
                </div>
            </div>
        </body></html>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Yo Voto <no-reply@yovoto.com>\r\n";
        return mail($email, $asunto, $mensaje, $headers);
    }

    private function generarNumeroRegistroUnico() {
        for ($i = 0; $i < 100; $i++) {
            $numero = 'REG-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $stmt   = $this->conn->prepare("SELECT id FROM usuarios WHERE numero_registro = ?");
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
}
?>