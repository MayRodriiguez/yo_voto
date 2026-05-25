<?php
// controllers/PropuestaController.php - VERSIÓN CORREGIDA
require_once 'models/Propuesta.php';
require_once 'models/Candidato.php';

class PropuestaController {
    private $propuestaModel;
    private $candidatoModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->propuestaModel = new Propuesta();
        $this->candidatoModel = new Candidato();
    }
    
    public function index($id_candidato) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        
        $candidato = $this->candidatoModel->getById($id_candidato);
        if (!$candidato) {
            header("Location: /yo_voto/candidatos");
            exit();
        }
        
        $propuestas = $this->propuestaModel->getByCandidato($id_candidato);
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            
            if ($this->propuestaModel->add($id_candidato, $titulo, $descripcion, $categoria)) {
                $_SESSION['mensaje'] = "Propuesta agregada exitosamente";
            } else {
                $_SESSION['error'] = "Error al agregar propuesta";
            }
            header("Location: /yo_voto/propuestas/$id_candidato");
            exit();
        }
        
        require_once 'views/admin/propuestas.php';
    }
    
    public function editar($id_propuesta, $id_candidato) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            
            if ($this->propuestaModel->update($id_propuesta, $titulo, $descripcion, $categoria)) {
                $_SESSION['mensaje'] = "Propuesta actualizada exitosamente";
            } else {
                $_SESSION['error'] = " Error al actualizar propuesta";
            }
            header("Location: /yo_voto/propuestas/$id_candidato");
            exit();
        }
        
        $propuesta = $this->propuestaModel->getById($id_propuesta);
        $candidato = $this->candidatoModel->getById($id_candidato);
        require_once 'views/admin/editar_propuesta.php';
    }
    
    public function eliminar($id_propuesta, $id_candidato) {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        
        if ($this->propuestaModel->delete($id_propuesta)) {
            $_SESSION['mensaje'] = "Propuesta eliminada exitosamente";
        } else {
            $_SESSION['error'] = " Error al eliminar propuesta";
        }
        header("Location: /yo_voto/propuestas/$id_candidato");
        exit();
    }
}