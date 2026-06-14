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
        $this->votoModel      = new Voto();
        $this->userModel      = new User();
        $this->candidatoModel = new Candidato();
        $this->blockchainVote = new BlockchainVote($this->conn);
    }
    
    public function votar() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
            header("Location: /yo_voto/");
            exit();
        }

        $config = [];
        $resConfig = $this->conn->query("SELECT clave, valor FROM configuracion");
        while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }

        $votacionActiva = $config['votacion_activa'] ?? '0';
        $fechaVotacion  = $config['fecha_votacion']  ?? '';
        $horaApertura   = $config['hora_apertura']   ?? '00:00';
        $horaCierre     = $config['hora_cierre']     ?? '23:59';

        if ($votacionActiva != '1') {
            $_SESSION['error_login'] = " La votación no está habilitada en este momento.";
            header("Location: /yo_voto/");
            exit();
        }

        // Validar fecha y hora actual
        $ahora      = new DateTime('now');
        $fechaHoy   = $ahora->format('Y-m-d');
        $horaActual = $ahora->format('H:i');

        if (!empty($fechaVotacion) && $fechaHoy !== $fechaVotacion) {
            $fechaFormateada = date('d/m/Y', strtotime($fechaVotacion));
            $_SESSION['error_login'] = " La votación está programada para el {$fechaFormateada}. Hoy no es día de votación.";
            header("Location: /yo_voto/");
            exit();
        }

        if (!empty($horaApertura) && $horaActual < $horaApertura) {
            $_SESSION['error_login'] = " La votación aún no ha comenzado. Apertura: {$horaApertura}.";
            header("Location: /yo_voto/");
            exit();
        }

        if (!empty($horaCierre) && $horaActual > $horaCierre) {
            $_SESSION['error_login'] = " La votación ha cerrado Horario: {$horaApertura} - {$horaCierre}.";
            header("Location: /yo_voto/");
            exit();
        }

        $user    = $_SESSION['user'];
        $yaVoto  = $this->userModel->yaVoto($user['id']);
        $error   = '';
        $mensaje = '';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // verifica la hora real del servidor justo cuando el usuario presiona votar
            $ahoraPost      = new DateTime('now');         // obtiene fecha y hora actual del servidor
            $fechaHoyPost   = $ahoraPost->format('Y-m-d'); // convierte a formato año,mes,día para comparar
            $horaActualPost = $ahoraPost->format('H:i');   // convierte a formato hora-minuto para comparar

            // si hoy no es el dia configurado para votar en el panel admin
            if (!empty($fechaVotacion) && $fechaHoyPost !== $fechaVotacion) {
                $error = " Hoy no es día de votación.";

            // si todavia no llego la hora de apertura configurada
            } elseif (!empty($horaApertura) && $horaActualPost < $horaApertura) {
                $error = " La votación aún no ha comenzado. Apertura: {$horaApertura}.";

            // si ya paso la hora de cierre configurada
            } elseif (!empty($horaCierre) && $horaActualPost > $horaCierre) {
                $error = " El horario de votación ha cerrado. Horario: {$horaApertura} - {$horaCierre}.";

            // si el usuario ya emitio su voto anteriormente
            } elseif ($yaVoto) {
                $error = " USTED YA HA EMITIDO SU VOTO. Los votos son inmutables y no pueden modificarse.";
            } else {
                $id_candidato = intval($_POST['id_candidato'] ?? 0);
                $carnet       = $user['carnet'];

                // validar que el candidato existe y esta activo
                if ($id_candidato <= 0) {
                    $error = " Selecciona un candidato válido.";
                } else {
                    $stmtCheck = $this->conn->prepare("SELECT id_candidato FROM candidatos WHERE id_candidato = ? AND estado = 'activo'");
                    $stmtCheck->bind_param("i", $id_candidato);
                    $stmtCheck->execute();
                    if ($stmtCheck->get_result()->num_rows === 0) {
                        $error = " El candidato seleccionado no es válido.";
                    } else {
                        $result = $this->blockchainVote->registrarVotoBlockchain($user['id'], $id_candidato, $carnet);
                if ($result['success']) {
                    $_SESSION['user']['ya_voto'] = 1;
                    $_SESSION['bloque_voto']     = $result['bloque'];
                    $mensaje = " ¡GRACIAS POR VOTAR!<br>
                                Hash: <strong>" . substr($result['bloque']['hash'], 0, 20) . "...</strong><br>
                                Bloque #" . $result['bloque']['indice'] . "<br>
                                Su voto es inmutable y no puede ser modificado.";
                        $yaVoto = true;
                    } else {
                        $error = " Error al registrar su voto: " . ($result['error'] ?? 'Intente nuevamente');
                    }
                    } 
                } 
            }
        }
        
        $candidatos = $this->candidatoModel->getAllActivos();
        require_once 'views/public/votar.php';
    }
}
?>