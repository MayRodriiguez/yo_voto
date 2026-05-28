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
            $sql = "SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY id_candidato ASC";
            $result = $this->conn->query($sql);
            $candidatos = [];
            while ($row = $result->fetch_assoc()) {
                $candidatos[] = $row;
            }
            echo json_encode($candidatos);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getCandidato($id) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        try {
            $sql = "SELECT * FROM candidatos WHERE id_candidato = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getEquipo($id_candidato) {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        try {
            $sql = "SELECT * FROM equipo WHERE id_candidato = ? ORDER BY nivel ASC, id_integrante ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $id_candidato);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $integrantes = [];
            while ($row = $result->fetch_assoc()) {
                $integrantes[$row['nivel']][] = $row;
            }
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
                $sql = "SELECT * FROM propuestas WHERE id_candidato = ? ORDER BY id_propuesta DESC";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $id_candidato);
                $stmt->execute();
                $result = $stmt->get_result();
            } else {
                $sql = "SELECT p.*, c.nombre as candidato_nombre, c.partido as candidato_partido 
                        FROM propuestas p 
                        JOIN candidatos c ON p.id_candidato = c.id_candidato 
                        WHERE c.estado = 'activo'
                        ORDER BY p.id_propuesta DESC LIMIT 20";
                $result = $this->conn->query($sql);
            }
            
            $propuestas = [];
            while ($row = $result->fetch_assoc()) {
                $propuestas[] = $row;
            }
            echo json_encode($propuestas);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getResultados() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        try {
            $sql = "SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC";
            $result = $this->conn->query($sql);
            $candidatos = [];
            while ($row = $result->fetch_assoc()) {
                $candidatos[] = $row;
            }
            $totalResult = $this->conn->query("SELECT COUNT(*) as total FROM votos");
            $totalVotos = $totalResult->fetch_assoc()['total'];
            echo json_encode(['candidatos' => $candidatos, 'total_votos' => $totalVotos]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public function getCaptcha() {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    
    // Generar código aleatorio de 6 caracteres
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    
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
        
        $sql = "SELECT * FROM usuarios WHERE carnet = ? AND activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password']) && $user['rol'] == 'usuario') {
                $_SESSION['user'] = $user;
                echo json_encode(['success' => true]);
                return;
            }
        }
        
        echo json_encode(['success' => false, 'error' => 'Carnet o contraseña incorrectos']);
    }
    
    public function verificarJurado() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        $carnet = $_GET['carnet'] ?? '';
        
        if (empty($carnet) || strlen($carnet) !== 8 || !ctype_digit($carnet)) {
            echo json_encode(['es_jurado' => false, 'error' => 'Carnet inválido']);
            return;
        }
        
        $sql = "SELECT j.*, u.nombres, u.apellidos, u.fecha_nacimiento 
                FROM jurados j 
                JOIN usuarios u ON j.id_usuario = u.id 
                WHERE u.carnet = ? AND j.activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $carnet);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($jurado = $result->fetch_assoc()) {
            $sqlUser = "SELECT * FROM usuarios WHERE carnet = ?";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->bind_param("s", $carnet);
            $stmtUser->execute();
            $user = $stmtUser->get_result()->fetch_assoc();
            
            $passwordTemp = '';
            if ($user && $user['fecha_nacimiento']) {
                $date = new DateTime($user['fecha_nacimiento']);
                $nombreParte = explode(' ', strtolower($user['nombres']));
                $primerNombre = $nombreParte[0];
                $passwordTemp = $primerNombre . $date->format('dmY');
            }
            
            echo json_encode([
                'es_jurado' => true,
                'codigo_jurado' => $jurado['codigo_jurado'],
                'id_mesa' => $jurado['id_mesa'],
                'cargo_jurado' => $jurado['cargo_jurado'],
                'carnet' => $carnet,
                'nombres' => $jurado['nombres'] . ' ' . $jurado['apellidos'],
                'password' => $passwordTemp ?: 'usuario123'
            ]);
        } else {
            $sqlUser = "SELECT nombres, apellidos FROM usuarios WHERE carnet = ?";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->bind_param("s", $carnet);
            $stmtUser->execute();
            $user = $stmtUser->get_result()->fetch_assoc();
            
            echo json_encode([
                'es_jurado' => false,
                'nombres' => $user ? $user['nombres'] . ' ' . $user['apellidos'] : 'el ciudadano'
            ]);
        }
    }
    public function registrarVoto() {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Debe iniciar sesión']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $id_usuario = $_SESSION['user']['id'];
    $id_candidato = $data['id_candidato'] ?? 0;
    
    // Verificar si ya votó
    $checkStmt = $this->conn->prepare("SELECT id FROM votos WHERE id_usuario = ?");
    $checkStmt->bind_param("i", $id_usuario);
    $checkStmt->execute();
    $check = $checkStmt->get_result();
    if ($check->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Ya votó']);
        return;
    }
    
    // Usar blockchain para registrar
    require_once 'models/BlockchainVote.php';
    $blockchainVote = new BlockchainVote($this->conn);
    $carnet = $_SESSION['user']['carnet'];
    
    $result = $blockchainVote->registrarVotoBlockchain($id_usuario, $id_candidato, $carnet);
    
    if ($result['success']) {
        $_SESSION['user']['ya_voto'] = 1;
        echo json_encode([
            'success' => true, 
            'block_hash' => $result['bloque']['hash'],
            'block_index' => $result['bloque']['indice']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Error al registrar voto']);
    }
}
    
    public function getEstadisticas() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        $totalUsuarios = $this->conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'")->fetch_assoc()['total'];
        $totalVotos = $this->conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
        $totalJurados = $this->conn->query("SELECT COUNT(*) as total FROM jurados")->fetch_assoc()['total'];
        $totalCandidatos = $this->conn->query("SELECT COUNT(*) as total FROM candidatos WHERE estado = 'activo'")->fetch_assoc()['total'];
        
        echo json_encode([
            'total_usuarios' => $totalUsuarios,
            'total_votos' => $totalVotos,
            'total_jurados' => $totalJurados,
            'total_candidatos' => $totalCandidatos,
            'participacion' => $totalUsuarios > 0 ? round(($totalVotos / $totalUsuarios) * 100, 1) : 0
        ]);
    }
}
?>