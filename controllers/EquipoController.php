<?php
// controllers/EquipoController.php - VERSIÓN CORREGIDA
require_once 'models/Equipo.php';
require_once 'models/Candidato.php';

class EquipoController {
    private $equipoModel;
    private $candidatoModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        $this->equipoModel = new Equipo();
        $this->candidatoModel = new Candidato();
    }
    
    public function index($id_candidato) {
        $candidato = $this->candidatoModel->getById($id_candidato);
        if (!$candidato) {
            header("Location: /yo_voto/candidatos");
            exit();
        }
        
        $niveles = $this->equipoModel->getByCandidato($id_candidato);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $cargo = $_POST['cargo'] ?? '';
            $nivel = $_POST['nivel'] ?? 1;
            
            if ($this->equipoModel->add($id_candidato, $nombre, $cargo, $nivel)) {
                $_SESSION['mensaje'] = " Integrante agregado exitosamente";
            } else {
                $_SESSION['error'] = " Error al agregar integrante";
            }
            header("Location: /yo_voto/equipo/$id_candidato");
            exit();
        }
        
        // Pasar variables a la vista
        $param = $id_candidato;
        require_once 'views/admin/equipo.php';
    }
    
    public function eliminar($id_integrante, $id_candidato) {
        if ($this->equipoModel->delete($id_integrante)) {
            $_SESSION['mensaje'] = " Integrante eliminado exitosamente";
        } else {
            $_SESSION['error'] = " Error al eliminar integrante";
        }
        header("Location: /yo_voto/equipo/$id_candidato");
        exit();
    }
    public function editar($id_integrante, $id_candidato) {
    $integrante = $this->equipoModel->getById($id_integrante);
    if (!$integrante) {
        header("Location: /yo_voto/candidatos");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nombre = $_POST['nombre'] ?? '';
        $cargo  = $_POST['cargo'] ?? '';
        $nivel  = $_POST['nivel'] ?? 1;

        if ($this->equipoModel->update($id_integrante, $nombre, $cargo, $nivel)) {
            $_SESSION['mensaje'] = " Integrante actualizado exitosamente";
        } else {
            $_SESSION['error'] = " Error al actualizar integrante";
        }
        header("Location: /yo_voto/equipo/$id_candidato");
        exit();
    }

    $candidato = $this->candidatoModel->getById($id_candidato);
    require_once 'views/admin/editar_integrante.php';
}
}