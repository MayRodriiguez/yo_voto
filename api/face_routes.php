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
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$db = new Database();
$conn = $db->getConnection();

$url = $_SERVER['REQUEST_URI'];

// ============================================
// RECUPERAR CONTRASEÑA — PASO 1: enviar código
// ============================================
if (strpos($url, '/api/recuperar-password') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $carnet = trim($data['carnet'] ?? '');
    $email  = trim($data['email'] ?? '');

    if (empty($carnet) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Ingresa tu CI y correo electrónico.']);
        exit();
    }

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

    $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expira = date('Y-m-d H:i:s', time() + 600);

    $_SESSION['reset_codigo']  = $codigo;
    $_SESSION['reset_expira']  = time() + 600;
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_email']   = $email;

    $colCheck = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'reset_code'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $stmt2 = $conn->prepare("UPDATE usuarios SET reset_code = ?, reset_expira = ? WHERE id = ?");
        $stmt2->bind_param("ssi", $codigo, $expira, $user['id']);
        $stmt2->execute();
    }

    // Enviar con PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'angelamarianagonzales@gmail.com';
        $mail->Password   = 'mwew onrj cxpd wvpk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('angelamarianagonzales@gmail.com', 'Yo Voto Bolivia');
        $mail->addAddress($email, $user['nombres']);

        $mail->isHTML(true);
        $mail->Subject = '🔐 Código de recuperación - Yo Voto';
        $mail->Body    = "
        <html><body style='font-family:Arial,sans-serif;background:#0a1628;padding:30px;'>
            <div style='max-width:480px;margin:0 auto;background:#0d2251;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);'>
                <div style='background:#FF6B00;padding:24px;text-align:center;'>
                    <h1 style='margin:0;font-size:24px;color:#fff;'>🗳️ Yo Voto</h1>
                    <p style='margin:6px 0 0;color:rgba(255,255,255,0.8);font-size:13px;'>Sistema Electoral Bolivia 2026</p>
                </div>
                <div style='padding:32px;'>
                    <p style='color:rgba(255,255,255,0.7);margin-bottom:24px;'>Hola <strong style='color:#fff;'>{$user['nombres']}</strong>, usa este código para restablecer tu contraseña:</p>
                    <div style='background:#FF6B00;border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;'>
                        <div style='font-size:42px;font-weight:900;letter-spacing:12px;color:#fff;font-family:monospace;'>{$codigo}</div>
                    </div>
                    <p style='color:rgba(255,255,255,0.4);font-size:12px;text-align:center;'>Este código expira en <strong style='color:#FF8C38;'>10 minutos</strong>.<br>Si no solicitaste esto, ignora este mensaje.</p>
                </div>
            </div>
        </body></html>";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Código enviado a tu correo.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
    }
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
// LOGIN NORMAL (sin facial)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) { echo json_encode(['success' => false, 'error' => 'JSON inválido']); exit(); }

    $carnet   = $data['carnet'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($carnet) || !ctype_digit($carnet)) {
        echo json_encode(['success' => false, 'error' => 'Carnet inválido']);
        exit();
    }

    $sql = "SELECT id, nombres, apellidos, carnet, password, foto_url, habilitado_voto, ya_voto, rol 
            FROM usuarios WHERE carnet = ? AND rol = 'usuario' AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $carnet);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) { echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']); exit(); }
    if ($user['habilitado_voto'] != 1) { echo json_encode(['success' => false, 'error' => 'Cuenta no habilitada para votar']); exit(); }
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Contraseña incorrecta']);
        exit();
    }

    $_SESSION['user'] = $user;
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>