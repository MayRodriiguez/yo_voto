<?php
require_once 'models/Voto.php';
require_once 'models/User.php';
require_once 'models/Candidato.php';
require_once 'models/BlockchainVote.php';

class VotoController {
    private $votoModel;
    private $userModel;
    private $candidatoModel;
    private $blockchainVote;
    private $conn;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $database = new Database();
        $this->conn = $database->getConnection();
        
        $this->votoModel = new Voto();
        $this->userModel = new User();
        $this->candidatoModel = new Candidato();  // ✅ ESTÁ BIEN
        $this->blockchainVote = new BlockchainVote($this->conn);
    }
    
    public function votar() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
            header("Location: /yo_voto/");
            exit();
        }
        
        $user = $_SESSION['user'];
        $yaVoto = $this->userModel->yaVoto($user['id']);
        $error = '';
        $mensaje = '';
        
        // Verificar si ya votó ANTES de procesar el formulario
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($yaVoto) {
                $error = "⚠️ USTED YA HA EMITIDO SU VOTO. Los votos son inmutables y no pueden modificarse.";
            } else {
                $id_candidato = $_POST['id_candidato'] ?? 0;
                $carnet = $user['carnet'];
                $hashHuella = $user['huella_digital'] ?? null;
                
                $result = $this->blockchainVote->registrarVotoBlockchain($user['id'], $id_candidato, $carnet, $hashHuella);
                
                if ($result['success']) {
                    $_SESSION['user']['ya_voto'] = 1;
                    $_SESSION['bloque_voto'] = $result['bloque'];
                    $mensaje = "✅ ¡GRACIAS POR VOTAR!<br>
                                🔗 Hash de transacción: <strong>" . substr($result['bloque']['hash'], 0, 20) . "...</strong><br>
                                📦 Bloque #" . $result['bloque']['indice'] . "<br>
                                ⚠️ IMPORTANTE: Su voto es inmutable y no puede ser modificado.";
                    $yaVoto = true;
                } else {
                    $error = "❌ Error al registrar su voto: " . ($result['error'] ?? 'Intente nuevamente');
                }
            }
        }
        
        // ✅ OBTENER CANDIDATOS USANDO EL MODELO
        $candidatos = $this->candidatoModel->getAllActivos();
        
        require_once 'views/public/votar.php';
    }
}
?>