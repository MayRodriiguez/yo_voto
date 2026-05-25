<?php
require_once 'models/Jurado.php';
require_once 'models/User.php';

class JuradoController {
    private $juradoModel;
    private $userModel;
    
    public function __construct() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        $this->juradoModel = new Jurado();
        $this->userModel = new User();
    }
    
    public function index() {
        $jurados = $this->juradoModel->getAll();
        $usuarios = $this->juradoModel->getUsuariosSinJurado();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->juradoModel->assign($_POST['id_usuario'], $_POST['id_mesa'], $_POST['cargo']);
            header("Location: /yo_voto/jurados");
            exit();
        }
        
        require_once 'views/admin/jurados.php';
    }
    
    public function eliminar($id) {
        $this->juradoModel->delete($id);
        header("Location: /yo_voto/jurados");
        exit();
    }
}
?>