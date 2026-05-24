<?php
// controllers/RegistroController.php - Versión con Reconocimiento Facial
require_once 'config/database.php';
require_once 'models/User.php';

class RegistroController {
    private $userModel;
    private $conn;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        
        $this->userModel = new User();
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function index() {
        $usuarios = $this->userModel->getAll();
        require_once 'views/admin/registro_ciudadano.php';
    }
    
    public function guardar() {
        // Verificar que es POST
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $_SESSION['error'] = "Método no permitido";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Validar datos requeridos
        if (empty($_POST['nombres']) || empty($_POST['apellidos']) || empty($_POST['carnet']) || empty($_POST['fecha_nac'])) {
            $_SESSION['error'] = " Todos los campos obligatorios deben ser llenados";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Validar formato de carnet
        $carnet = trim($_POST['carnet']);
        if (strlen($carnet) !== 8 || !ctype_digit($carnet)) {
            $_SESSION['error'] = " El carnet debe tener exactamente 8 dígitos numéricos";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Verificar si el carnet ya existe
        $checkSql = "SELECT id FROM usuarios WHERE carnet = ?";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("s", $carnet);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['error'] = " El carnet {$carnet} ya está registrado en el sistema";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Validar edad
        $fechaNac = new DateTime($_POST['fecha_nac']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac)->y;
        
        if ($edad < 18) {
            $_SESSION['error'] = " El ciudadano debe ser mayor de 18 años";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // ============================================================
        // VALIDAR QUE SE REGISTRÓ EL ROSTRO FACIAL
        // ============================================================
        $face_registered = isset($_POST['face_registered']) ? $_POST['face_registered'] : '0';
        $face_descriptor = isset($_POST['face_descriptor']) ? $_POST['face_descriptor'] : null;
        
        if ($face_registered !== '1' || empty($face_descriptor)) {
            $_SESSION['error'] = " Debe registrar el rostro del ciudadano antes de continuar";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Validar que el descriptor facial sea un JSON válido
        $descriptorJson = json_decode($face_descriptor, true);
        if (!$descriptorJson || !is_array($descriptorJson) || count($descriptorJson) !== 128) {
            $_SESSION['error'] = " Error en el descriptor facial. Intente registrar el rostro nuevamente.";
            header("Location: /yo_voto/admin/registro");
            exit();
        }
        
        // Preparar datos
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $fecha_nacimiento = $_POST['fecha_nac'];
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        
        // Generar email
        $email = !empty($_POST['email']) ? trim($_POST['email']) : $carnet . '@yovoto.com';
        
        // Generar contraseña temporal
        $primerNombre = strtolower(explode(' ', $nombres)[0]);
        $passwordTemp = $primerNombre . $fechaNac->format('dmY');
        $hashedPassword = password_hash($passwordTemp, PASSWORD_DEFAULT);
        
        // Habilitaciones
        $habilitado_voto = isset($_POST['habilitar_voto']) ? 1 : 0;
        $es_jurado = isset($_POST['es_jurado']) ? 1 : 0;
        
        // Generar número de registro único
        $numeroRegistro = $this->generarNumeroRegistroUnico();
        
        // ============================================================
        // INSERTAR USUARIO CON DESCRIPTOR FACIAL (reemplaza huella_digital)
        // ============================================================
        $insertSql = "INSERT INTO usuarios (numero_registro, nombres, apellidos, carnet, fecha_nacimiento, direccion, telefono, email, password, face_descriptor, rol, habilitado_voto, ya_voto, activo) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', ?, 0, 1)";
        
        $insertStmt = $this->conn->prepare($insertSql);
        $insertStmt->bind_param("ssssssssssi", 
            $numeroRegistro,
            $nombres, 
            $apellidos, 
            $carnet, 
            $fecha_nacimiento, 
            $direccion, 
            $telefono, 
            $email, 
            $hashedPassword, 
            $face_descriptor,  // ← Ahora guarda el descriptor facial en lugar de la huella
            $habilitado_voto
        );
        
        if ($insertStmt->execute()) {
            $id_usuario = $this->conn->insert_id;
            
            $_SESSION['mensaje'] = "✅ Ciudadano registrado exitosamente!<br>
                                     📝 N° Registro: <strong>{$numeroRegistro}</strong><br>
                                     📧 Email: {$email}<br>
                                     🔐 Contraseña temporal: <strong>{$passwordTemp}</strong><br>
                                     🆔 ID interno: {$id_usuario}<br>
                                     🎭 Registro facial: <strong>COMPLETADO</strong>";
            
            // Si es jurado, asignar
            if ($es_jurado == 1) {
                require_once 'models/Jurado.php';
                $juradoModel = new Jurado();
                $mesa = rand(1, 20);
                $cargoJurado = 'Vocal';
                $juradoModel->assign($id_usuario, $mesa, $cargoJurado);
                $_SESSION['mensaje'] .= "<br>⚖️ Designado como Jurado Electoral (Mesa {$mesa})";
            }
        } else {
            $_SESSION['error'] = "❌ Error al registrar: " . $this->conn->error;
        }
        
        header("Location: /yo_voto/admin/registro");
        exit();
    }
    
    /**
     * Genera un número de registro único y aleatorio
     * Formato: REG-XXXXXX (ejemplo: REG-123456)
     */
    private function generarNumeroRegistroUnico() {
        $maxIntentos = 100;
        
        for ($i = 0; $i < $maxIntentos; $i++) {
            $numero = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $numeroRegistro = 'REG-' . $numero;
            
            $sql = "SELECT id FROM usuarios WHERE numero_registro = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $numeroRegistro);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return $numeroRegistro;
            }
        }
        
        // Fallback: usar timestamp + random
        return 'REG-' . date('Ymd') . rand(100, 999);
    }
}  
?>