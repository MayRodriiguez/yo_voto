<?php
// api/admin.php - Endpoints para administración
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configurar sesión ANTES de iniciarla
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    ini_set('session.cookie_httponly', 1);
    session_name('YOVOTO_SESSION');
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Verificar que sea admin
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado', 'session' => $_SESSION]);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Aceptar GET y POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? 0;
} else {
    $id = $_GET['id'] ?? 0;
}

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID no válido']);
    exit();
}

$sql = "UPDATE usuarios SET habilitado_voto = 1 WHERE id = ? AND rol = 'usuario'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar: ' . $conn->error]);
}
?>