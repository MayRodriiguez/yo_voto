<?php
require_once 'config/database.php';

class ApiController {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public function getCandidatos() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        try {
            $result = $this->conn->query("SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY id_candidato ASC");
            $candidatos = [];
            while ($row = $result->fetch_assoc()) $candidatos[] = $row;
            echo json_encode($candidatos);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getCandidato($id) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        try {
            $stmt = $this->conn->prepare("SELECT * FROM candidatos WHERE id_candidato = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_assoc());
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getEquipo($id_candidato) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        try {
            $stmt = $this->conn->prepare("SELECT * FROM equipo WHERE id_candidato = ? ORDER BY nivel ASC, id_integrante ASC");
            $stmt->bind_param("i", $id_candidato);
            $stmt->execute();
            $result = $stmt->get_result();
            $integrantes = [];
            while ($row = $result->fetch_assoc()) $integrantes[$row['nivel']][] = $row;
            echo json_encode($integrantes);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getPropuestas($id_candidato = null) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        try {
            if ($id_candidato) {
                $stmt = $this->conn->prepare("SELECT * FROM propuestas WHERE id_candidato = ? ORDER BY id_propuesta DESC");
                $stmt->bind_param("i", $id_candidato);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $result = $this->conn->query("SELECT p.*, c.nombre as candidato_nombre, c.partido as candidato_partido 
                    FROM propuestas p JOIN candidatos c ON p.id_candidato = c.id_candidato 
                    WHERE c.estado = 'activo' ORDER BY p.id_propuesta DESC LIMIT 20");
            }
            $propuestas = [];
            while ($row = $result->fetch_assoc()) $propuestas[] = $row;
            echo json_encode($propuestas);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getResultados() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        try {
            $result = $this->conn->query("SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC");
            $candidatos = [];
            while ($row = $result->fetch_assoc()) $candidatos[] = $row;
            $totalVotos = $this->conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
            echo json_encode(['candidatos' => $candidatos, 'total_votos' => $totalVotos]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getCaptcha() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $codigo = '';
        for ($i = 0; $i < 6; $i++) $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        $_SESSION['captcha_codigo'] = $codigo;
        echo json_encode(['captcha' => $codigo]);
    }

    public function login() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['captcha']) || !isset($_SESSION['captcha_resultado']) || $data['captcha'] != $_SESSION['captcha_resultado']) {
            echo json_encode(['success' => false, 'error' => 'Código de seguridad incorrecto']);
            return;
        }
        $carnet = $data['carnet'] ?? '';
        $password = $data['password'] ?? '';
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE carnet = ? AND activo = 1");
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        if ($user = $stmt->get_result()->fetch_assoc()) {
            if (password_verify($password, $user['password']) && $user['rol'] == 'usuario') {
                $_SESSION['user'] = $user;
                echo json_encode(['success' => true]);
                return;
            }
        }
        echo json_encode(['success' => false, 'error' => 'Carnet o contraseña incorrectos']);
    }

    public function registrarVoto() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user'])) {
            echo json_encode(['success' => false, 'error' => 'Debe iniciar sesión']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $id_usuario  = $_SESSION['user']['id'];
        $id_candidato = $data['id_candidato'] ?? 0;
        $checkStmt = $this->conn->prepare("SELECT id FROM votos WHERE id_usuario = ?");
        $checkStmt->bind_param("i", $id_usuario);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            echo json_encode(['success' => false, 'error' => 'Ya votó']);
            return;
        }
        require_once 'models/BlockchainVote.php';
        $blockchainVote = new BlockchainVote($this->conn);
        $result = $blockchainVote->registrarVotoBlockchain($id_usuario, $id_candidato, $_SESSION['user']['carnet']);
        if ($result['success']) {
            $_SESSION['user']['ya_voto'] = 1;
            echo json_encode(['success' => true, 'block_hash' => $result['bloque']['hash'], 'block_index' => $result['bloque']['indice']]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Error al registrar voto']);
        }
    }
    
    public function getEstadisticas() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        $totalUsuarios   = $this->conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'")->fetch_assoc()['total'];
        $totalVotos      = $this->conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
        $totalCandidatos = $this->conn->query("SELECT COUNT(*) as total FROM candidatos WHERE estado = 'activo'")->fetch_assoc()['total'];
        echo json_encode([
            'total_usuarios'  => $totalUsuarios,
            'total_votos'     => $totalVotos,
            'total_candidatos'=> $totalCandidatos,
            'participacion'   => $totalUsuarios > 0 ? round(($totalVotos / $totalUsuarios) * 100, 1) : 0
        ]);
    }
}
?>