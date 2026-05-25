<?php
require_once 'models/Candidato.php';

class CandidatoController {
    private $candidatoModel;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
            header("Location: /yo_voto/login");
            exit();
        }
        $this->candidatoModel = new Candidato();
    }
    
    public function index() {
        $candidatos = $this->candidatoModel->getAll();
        require_once 'views/admin/candidatos.php';
    }
    
    public function agregar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $foto_url = "uploads/img/sin_foto.jpg";
            
            // Manejar la subida de la foto
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $filename = $_FILES['foto']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $nombre_foto = time() . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', basename($filename));
                    $upload_dir = "uploads/img/";
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $nombre_foto)) {
                        $foto_url = $upload_dir . $nombre_foto;
                    }
                }
            }
            
            if ($this->candidatoModel->create($_POST, $foto_url)) {
                $_SESSION['mensaje'] = "Candidato agregado exitosamente";
            } else {
                $_SESSION['error'] = "Error al agregar candidato";
            }
            header("Location: /yo_voto/candidatos");
            exit();
        }
        require_once 'views/admin/agregar_candidato.php';
    }
    
    public function editar($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'nombre' => $_POST['nombre'],
                'partido' => $_POST['partido'],
                'cargo' => $_POST['cargo'],
                'biografia' => $_POST['biografia'],
                'fecha_postulacion' => $_POST['fecha_postulacion']
            ];
            
            if ($this->candidatoModel->update($id, $data)) {
                $_SESSION['mensaje'] = " Candidato actualizado exitosamente";
            } else {
                $_SESSION['error'] = " Error al actualizar candidato";
            }
            header("Location: /yo_voto/candidatos");
            exit();
        }
        $candidato = $this->candidatoModel->getById($id);
        require_once 'views/admin/editar_candidato.php';
    }
    
    public function toggle($id) {
        if ($this->candidatoModel->toggleStatus($id)) {
            $_SESSION['mensaje'] = " Estado del candidato actualizado";
        } else {
            $_SESSION['error'] = " Error al cambiar estado";
        }
        header("Location: /yo_voto/candidatos");
        exit();
    }
    
    public function eliminar($id) {
        if ($this->candidatoModel->delete($id)) {
            $_SESSION['mensaje'] = "Candidato eliminado exitosamente";
        } else {
            $_SESSION['error'] = " Error al eliminar candidato";
        }
        header("Location: /yo_voto/candidatos");
        exit();
    }
    public function seleccionTipo() {
    require_once 'models/Configuracion.php';
    $config = new Configuracion();
    $tipoActivo = $config->getTipoEleccion();
    require_once 'views/admin/seleccion_tipo.php';
}

public function activarTipo($tipo) {
    if (!in_array($tipo, ['nacional', 'subnacional'])) {
        header("Location: /yo_voto/candidatos");
        exit();
    }
    require_once 'models/Configuracion.php';
    $config = new Configuracion();
    $config->setTipoEleccion($tipo);
    $_SESSION['mensaje'] = "✅ Elecciones " . ucfirst($tipo) . "es activadas correctamente";
    header("Location: /yo_voto/candidatos/$tipo");
    exit();
}

public function resetearTipo() {
    require_once 'models/Configuracion.php';
    $config = new Configuracion();
    $config->setTipoEleccion('ninguna');
    $_SESSION['mensaje'] = "✅ Tipo de elección reiniciado correctamente";
    header("Location: /yo_voto/candidatos");
    exit();
}

public function listarPorTipo($tipo) {
    require_once 'models/Configuracion.php';
    $config = new Configuracion();
    $tipoActivo = $config->getTipoEleccion();
    
    // Si el tipo no coincide con el activo, redirigir
    if ($tipoActivo !== $tipo) {
        header("Location: /yo_voto/candidatos");
        exit();
    }
    
    $candidatos = $this->candidatoModel->getAllByTipo($tipo);
    require_once 'views/admin/candidatos.php';
}
}
?>