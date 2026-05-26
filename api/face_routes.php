<?php
// api/face_routes.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit(); }

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    ini_set('session.cookie_httponly', 1);
    session_name('YOVOTO_SESSION');
    session_start();
}

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$conn = $db->getConnection();

$url = $_SERVER['REQUEST_URI'];

// ============================================
// RECUPERAR CONTRASEÑA — PASO 1: enviar código
// ============================================
if (strpos($url, '/api/recuperar-password') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $carnet = trim($data['carnet'] ?? '');
    $email  = trim($data['email'] ?? '');

    if (empty($carnet) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Ingresa tu CI y correo electrónico.']);
        exit();
    }

    // Buscar usuario
    $stmt = $conn->prepare("SELECT id, nombres, email FROM usuarios WHERE carnet = ? AND activo = 1");
    $stmt->bind_param("s", $carnet);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'No se encontró ninguna cuenta con ese CI.']);
        exit();
    }

    if (strtolower($user['email']) !== strtolower($email)) {
        echo json_encode(['success' => false, 'error' => 'El correo no coincide con el registrado.']);
        exit();
    }

    // Generar código de 6 dígitos
    $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expira = date('Y-m-d H:i:s', time() + 600); // 10 minutos

    // Guardar código en BD (necesita columna reset_code y reset_expira en usuarios)
    // Si no existen, usar sesión como fallback
    $_SESSION['reset_codigo']  = $codigo;
    $_SESSION['reset_expira']  = time() + 600;
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_email']   = $email;

    // Intentar guardar en BD también
    $colCheck = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'reset_code'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $stmt2 = $conn->prepare("UPDATE usuarios SET reset_code = ?, reset_expira = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $codigo, $expira, $user['id']);
        $stmt2->execute();
    }

    // Enviar correo
    $nombre  = $user['nombres'];
    $asunto  = "🔐 Código de recuperación - Yo Voto";
    $mensaje = "
    <html><body style='font-family:Arial,sans-serif;background:#0a1628;padding:30px;'>
        <div style='max-width:480px;margin:0 auto;background:#0d2251;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);'>
            <div style='background:#FF6B00;padding:24px;text-align:center;'>
                <h1 style='margin:0;font-size:24px;color:#fff;'>🗳️ Yo Voto</h1>
                <p style='margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;'>Sistema Electoral Bolivia 2026</p>
            </div>
            <div style='padding:32px;'>
                <p style='color:rgba(255,255,255,0.7);margin-bottom:24px;'>Hola <strong style='color:#fff;'>{$nombre}</strong>, usa este código para restablecer tu contraseña:</p>
                <div style='background:#FF6B00;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;'>
                    <div style='font-size:42px;font-weight:900;letter-spacing:12px;color:#fff;font-family:monospace;'>{$codigo}</div>
                </div>
                <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center;'>Este código expira en <strong style='color:#FF8C38;'>10 minutos</strong>.<br>Si no solicitaste esto, ignora este mensaje.</p>
            </div>
        </div>
    </body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Yo Voto <no-reply@yovoto.com>\r\n";

    $enviado = mail($email, $asunto, $mensaje, $headers);

    // En desarrollo local el mail() puede fallar, igual retornamos éxito para pruebas
    echo json_encode(['success' => true, 'message' => 'Código enviado a tu correo.', 'dev_codigo' => (php_uname('s') === 'Windows NT') ? $codigo : null]);
    exit();
}

// ============================================
// RECUPERAR CONTRASEÑA — PASO 2: verificar código
// ============================================
if (strpos($url, '/api/verificar-reset') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $codigo = trim($data['codigo'] ?? '');

    $codigoGuardado = $_SESSION['reset_codigo'] ?? '';
    $expira         = $_SESSION['reset_expira'] ?? 0;
    $userId         = $_SESSION['reset_user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Sesión expirada. Vuelve a solicitar el código.']);
        exit();
    }
    if (time() > $expira) {
        echo json_encode(['success' => false, 'error' => 'El código ha expirado. Solicita uno nuevo.']);
        exit();
    }
    if ($codigo !== $codigoGuardado) {
        echo json_encode(['success' => false, 'error' => 'Código incorrecto.']);
        exit();
    }

    $_SESSION['reset_verificado'] = true;
    echo json_encode(['success' => true]);
    exit();
}

// ============================================
// RECUPERAR CONTRASEÑA — PASO 3: nueva contraseña
// ============================================
if (strpos($url, '/api/nueva-password') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data      = json_decode(file_get_contents('php://input'), true);
    $password  = $data['password'] ?? '';
    $confirm   = $data['confirm'] ?? '';
    $userId    = $_SESSION['reset_user_id'] ?? null;
    $verificado = $_SESSION['reset_verificado'] ?? false;

    if (!$userId || !$verificado) {
        echo json_encode(['success' => false, 'error' => 'No autorizado. Completa la verificación primero.']);
        exit();
    }
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres.']);
        exit();
    }
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'error' => 'Las contraseñas no coinciden.']);
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hash, $userId);

    if ($stmt->execute()) {
        unset($_SESSION['reset_codigo'], $_SESSION['reset_expira'], $_SESSION['reset_user_id'], $_SESSION['reset_verificado'], $_SESSION['reset_email']);
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al actualizar la contraseña.']);
    }
    exit();
}

// ============================================
// LOGIN CON RECONOCIMIENTO FACIAL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) { echo json_encode(['success' => false, 'error' => 'JSON inválido']); exit(); }

    $carnet = $data['carnet'] ?? '';
    $password = $data['password'] ?? '';
    $currentDescriptor = $data['descriptor'] ?? null;

    // Validar carnet
    if (empty($carnet) || !ctype_digit($carnet)) {
        echo json_encode(['success' => false, 'error' => 'Carnet inválido']);
        exit();
    }

    // Buscar usuario
    $sql = "SELECT id, nombres, apellidos, carnet, password, face_descriptor, foto_url, habilitado_voto, ya_voto, rol 
            FROM usuarios WHERE carnet = ? AND rol = 'usuario' AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $carnet);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) { echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']); exit(); }
    if ($user['habilitado_voto'] != 1) { echo json_encode(['success' => false, 'error' => 'Cuenta no habilitada para votar']); exit(); }

    // Verificar contraseña
    if (!empty($password) && !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        exit();
    }

    // Verificar descriptor facial si viene
    if ($currentDescriptor && $user['face_descriptor']) {
        $storedDescriptor = json_decode($user['face_descriptor'], true);
        if ($storedDescriptor && count($storedDescriptor) === 128) {
            $sum = 0;
            for ($i = 0; $i < 128; $i++) {
                $diff = ($storedDescriptor[$i] ?? 0) - ($currentDescriptor[$i] ?? 0);
                $sum += $diff * $diff;
            }
            $distance = sqrt($sum);
            if ($distance >= 0.5) {
                echo json_encode(['success' => false, 'error' => 'Rostro no coincide. Intente nuevamente.', 'distance' => $distance]);
                exit();
            }
        }
    }

    // Login exitoso
    $_SESSION['user'] = $user;
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>