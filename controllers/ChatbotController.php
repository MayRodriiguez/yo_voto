<?php
require_once __DIR__ . '/../config/database.php';

class ChatbotController {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    public function procesarMensaje() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
        
        $input = file_get_contents('php://input');
        $data  = json_decode($input, true);
        $opcion = $data['opcion'] ?? '';
        
        echo json_encode(['respuesta' => $this->getRespuesta($opcion)]);
        exit();
    }
    
    private function getRespuesta($opcion) {
        switch ($opcion) {
            case 'como_votar':
                return " <strong>Pasos para votar:</strong><br><br>1️ Regístrate con tu CI y contraseña.<br>2️ Espera habilitación del administrador.<br>3️ Inicia sesión con tu carnet y contraseña.<br>4️⃣ Ve a 'Votar' y elige tu candidato.<br><br> Voto secreto, único e inmutable (Blockchain).";
                
            case 'candidatos':
                $result = $this->conn->query("SELECT nombre, partido, cargo FROM candidatos WHERE estado = 'activo' LIMIT 5");
                if ($result && $result->num_rows > 0) {
                    $lista = [];
                    while ($row = $result->fetch_assoc()) {
                        $lista[] = "• <strong>" . htmlspecialchars($row['nombre']) . "</strong> (" . htmlspecialchars($row['partido']) . " - " . htmlspecialchars($row['cargo']) . ")";
                    }
                    return " <strong>Candidatos activos:</strong><br><br>" . implode("<br>", $lista);
                }
                return " No hay candidatos activos actualmente.";
                
            case 'blockchain':
                return " <strong>Seguridad Blockchain:</strong><br><br> Voto en bloque SHA-256.<br> Cadena inmutable.<br> Nadie puede modificar tu voto.<br><br> Verifica en 'Auditoría Blockchain' pública.";
                
            case 'habilitacion':
                return " <strong>¿Cuenta no habilitada?</strong><br><br> Posibles causas:<br>• Revisión de datos por administrador.<br>• Registro incompleto.<br><br> Si pasa más de 48h, contacta al administrador.";
                
            default:
                return " Selecciona una opción del menú: '¿Cómo votar?', 'Candidatos', 'Blockchain' o 'Habilitación'.";
        }
    }
}
?>