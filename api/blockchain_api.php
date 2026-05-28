<?php
// api/blockchain_api.php - API para consultar blockchain
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/BlockchainVote.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Database();
$conn = $db->getConnection();
$blockchainVote = new BlockchainVote($conn);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'estadisticas':
        echo json_encode($blockchainVote->getEstadisticas());
        break;
        
    case 'verificar':
        $esValida = $blockchainVote->verificarIntegridad();
        echo json_encode(['cadena_valida' => $esValida]);
        break;
        
    case 'cadena':
        $limit = $_GET['limit'] ?? 50;
        $chain = $blockchainVote->getFullChain();
        // Limitar resultados para no sobrecargar
        $chain = array_slice($chain, -$limit);
        echo json_encode($chain);
        break;
        
    case 'buscar':
        $hash = $_GET['hash'] ?? '';
        if ($hash) {
            $bloque = $blockchainVote->verificarVotoPorHash($hash);
            echo json_encode($bloque ?: ['error' => 'Bloque no encontrado']);
        } else {
            echo json_encode(['error' => 'Hash requerido']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Acción no válida', 'acciones' => ['estadisticas', 'verificar', 'cadena', 'buscar']]);
}
?>