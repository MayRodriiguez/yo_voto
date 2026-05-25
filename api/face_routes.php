<?php
// api/face_routes.php - Endpoints para reconocimiento facial
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Debug - escribir en archivo de log
file_put_contents(__DIR__ . '/debug.log', date('Y-m-d H:i:s') . ' - ' . $_SERVER['REQUEST_URI'] . ' - ' . $_SERVER['REQUEST_METHOD'] . PHP_EOL, FILE_APPEND);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurar sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    ini_set('session.cookie_httponly', 1);
    session_name('YOVOTO_SESSION');
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// ============================================
// LOGIN CON RECONOCIMIENTO FACIAL
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/debug.log', 'Input: ' . $input . PHP_EOL, FILE_APPEND);
    
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'error' => 'JSON inválido']);
        exit();
    }
    
    $carnet = $data['carnet'] ?? '';
    $captcha = $data['captcha'] ?? '';
    $currentDescriptor = $data['descriptor'] ?? null;
    
    // Validar captcha (ahora es texto)
$captcha_usuario = strtoupper(trim($captcha ?? ''));
$captcha_guardado = $_SESSION['captcha_codigo'] ?? '';

if (empty($captcha_usuario) || $captcha_usuario !== $captcha_guardado) {
    echo json_encode(['success' => false, 'error' => 'Código de seguridad incorrecto']);
    exit();
}
    
    // Validar carnet
    if (empty($carnet) || strlen($carnet) !== 8 || !ctype_digit($carnet)) {
        echo json_encode(['success' => false, 'error' => 'Carnet inválido (8 dígitos)']);
        exit();
    }
    
    // Buscar usuario
    $sql = "SELECT id, nombres, apellidos, carnet, face_descriptor, habilitado_voto, ya_voto, rol 
            FROM usuarios WHERE carnet = ? AND rol = 'usuario' AND activo = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $carnet);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
        exit();
    }
    
    // Verificar habilitación
    if ($user['habilitado_voto'] != 1) {
        echo json_encode(['success' => false, 'error' => 'Cuenta no habilitada para votar']);
        exit();
    }
    
    // Verificar descriptor
    if (!$user['face_descriptor']) {
        echo json_encode(['success' => false, 'error' => 'No hay rostro registrado para este usuario']);
        exit();
    }
    
    if (!$currentDescriptor) {
        echo json_encode(['success' => false, 'error' => 'No se pudo capturar el rostro']);
        exit();
    }
    
    $storedDescriptor = json_decode($user['face_descriptor'], true);
    
    // Función para calcular distancia euclidiana
    function euclideanDistance($a, $b) {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $diff = $a[$i] - $b[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }
    
    $distance = euclideanDistance($storedDescriptor, $currentDescriptor);
    $threshold = 0.5;
    
    if ($distance < $threshold) {
        $_SESSION['user'] = $user;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Rostro no coincide', 'distance' => $distance]);
    }
    exit();
}

// Si no es POST
echo json_encode(['success' => false, 'error' => 'Método no permitido']);
?>