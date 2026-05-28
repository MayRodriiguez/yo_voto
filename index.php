<?php
// index.php - Router principal

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    ini_set('session.cookie_httponly', 1);
    session_name('YOVOTO_SESSION');
    session_start();
}

require_once 'config/database.php';

// =====================================================
// API - CHATBOT (Debe ir PRIMERO antes que cualquier otra API)
// =====================================================

if (strpos($_SERVER['REQUEST_URI'], '/api/chatbot') !== false) {
    require_once 'controllers/ChatbotController.php';
    $chatbot = new ChatbotController();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $chatbot->procesarMensaje();
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'opciones' => [
                'como_votar' => '🗳️ ¿Cómo votar?',
                'candidatos' => '👥 Ver Candidatos',
                'blockchain' => '🔒 Seguridad Blockchain',
                'reconocimiento_facial' => '👤 Reconocimiento Facial',
                'habilitacion' => '⏳ Mi cuenta no está habilitada'
            ]
        ]);
    }
    exit();
}

// =====================================================
// API
// =====================================================

if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {

    require_once 'controllers/ApiController.php';
    $api = new ApiController();

    $url = $_SERVER['REQUEST_URI'];
    $url = str_replace('/yo_voto', '', $url);
    $url = parse_url($url, PHP_URL_PATH) ?? '';

    if (strpos($url, '/api/candidatos') !== false && strpos($url, '/candidato/') === false) {
        $api->getCandidatos();
    } elseif (preg_match('/\/api\/candidato\/(\d+)/', $url, $matches)) {
        $api->getCandidato($matches[1]);
    } elseif (preg_match('/\/api\/equipo\/(\d+)/', $url, $matches)) {
        $api->getEquipo($matches[1]);
    } elseif (preg_match('/\/api\/propuestas(?:\/(\d+))?/', $url, $matches)) {
        $api->getPropuestas($matches[1] ?? null);
    } elseif (strpos($url, '/api/resultados') !== false) {
        $api->getResultados();
    } elseif (strpos($url, '/api/captcha-imagen') !== false) {
        require_once 'api/captcha_imagen.php';
        exit();
    } elseif (strpos($url, '/api/captcha') !== false) {
        $api->getCaptcha();
    } elseif (strpos($url, '/api/verificar-jurado') !== false) {
        $api->verificarJurado();
    } elseif (strpos($url, '/api/registrar-voto') !== false) {
        $api->registrarVoto();
    } elseif (strpos($url, '/api/estadisticas') !== false) {
        $api->getEstadisticas();
    } elseif (strpos($url, '/api/admin/habilitar') !== false) {
        require_once 'api/admin.php';
        exit();
    } elseif (strpos($url, '/api/face/register') !== false || strpos($url, '/api/face/login') !== false) {
        require_once 'api/face_routes.php';
        exit();
    } elseif (strpos($url, '/api/blockchain') !== false) {
        require_once 'api/blockchain_api.php';
        exit();
    } elseif (strpos($url, '/api/recuperar-password') !== false || 
              strpos($url, '/api/verificar-reset') !== false || 
              strpos($url, '/api/nueva-password') !== false) {
        require_once 'api/face_routes.php';
        exit();
    }

    exit();
}

// =====================================================
// URL
// =====================================================

$urlPath = isset($_GET['url']) ? $_GET['url'] : '';
$urlPath = rtrim($urlPath, '/');
$parts = explode('/', $urlPath);

$controller = isset($parts[0]) && !empty($parts[0]) ? $parts[0] : 'public';
$method = isset($parts[1]) ? $parts[1] : 'index';
$param = isset($parts[2]) ? $parts[2] : null;
$param2 = isset($parts[3]) ? $parts[3] : null;

// =====================================================
// PÚBLICO
// =====================================================

if ($controller == 'public' || $controller == '') {
    require_once 'views/public/inicio.php';
    exit();
}

if ($controller == 'login-votante') {
    require_once 'controllers/AuthController.php';
    $auth = new AuthController();
    $auth->loginVotante();
    exit();
}

if ($controller == 'logout-votante') {
    session_destroy();
    header("Location: /yo_voto/");
    exit();
}

if ($controller == 'votar') {
    require_once 'controllers/VotoController.php';
    $voto = new VotoController();
    $voto->votar();
    exit();
}

if ($controller == 'mi-perfil') {
    require_once 'views/user/dashboard.php';
    exit();
}

if ($controller == 'login') {
    require_once 'controllers/AuthController.php';
    $auth = new AuthController();
    $auth->login();
    exit();
}

if ($controller == 'logout') {
    session_destroy();
    header("Location: /yo_voto/login");
    exit();
}

if ($controller == 'resultados') {
    require_once 'views/public/resultados.php';
    exit();
}

if ($controller == 'capturar_huella') {
    require_once 'capturar_huella_ajax.php';
    exit();
}

if ($controller == 'registro') {
    require_once 'views/public/registro_ciudadano_publico.php';
    exit();
}

if ($controller == 'registro-ciudadano') {
    require_once 'controllers/AuthController.php';
    $auth = new AuthController();
    $auth->registroCiudadano();
    exit();
}

if ($controller == 'blockchain-verificar') {
    require_once 'views/public/blockchain_verificar.php';
    exit();
}

// =====================================================
// ADMIN ESPECIALES
// =====================================================

if ($controller == 'admin' && $method == 'registro') {
    require_once 'controllers/RegistroController.php';
    $reg = new RegistroController();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $reg->guardar();
    } else {
        $reg->index();
    }
    exit();
}

if ($controller == 'admin' && $method == 'editar-ciudadano' && $param) {
    require_once 'controllers/RegistroController.php';
    $reg = new RegistroController();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $reg->actualizar($param);
    } else {
        $reg->editar($param);
    }
    exit();
}

if ($controller == 'admin' && $method == 'blockchain') {
    require_once 'views/admin/blockchain_auditoria.php';
    exit();
}

if ($controller == 'admin' && $method == 'resultados') {
    require_once 'views/admin/resultados.php';
    exit();
}

// =====================================================
// VERIFICAR LOGIN ADMIN
// =====================================================

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}

// =====================================================
// RUTAS PROTEGIDAS
// =====================================================

switch ($controller) {

    case 'admin':
        if ($method == 'dashboard') {
            require_once 'views/admin/dashboard.php';
        } elseif ($method == 'resultados') {
            require_once 'views/admin/resultados.php';
        } else {
            require_once 'views/admin/dashboard.php';
        }
        break;

    case 'candidatos':
        require_once 'controllers/CandidatoController.php';
        $c = new CandidatoController();
        if ($method == 'agregar') {
            $c->agregar();
        } elseif ($method == 'editar' && $param) {
            $c->editar($param);
        } elseif ($method == 'toggle' && $param) {
            $c->toggle($param);
        } elseif ($method == 'eliminar' && $param) {
            $c->eliminar($param);
        } elseif ($method == 'activar' && $param) {
            $c->activarTipo($param);
        } elseif ($method == 'resetear') {
            $c->resetearTipo();
        } elseif ($method == 'nacional' || $method == 'subnacional') {
            $c->listarPorTipo($method);
        } else {
            $c->seleccionTipo();
        }
        break;

    case 'propuestas':
        require_once 'controllers/PropuestaController.php';
        $p = new PropuestaController();
        if ($method == 'editar' && $param && $param2) {
            $p->editar($param, $param2);
        } elseif ($method == 'eliminar' && $param && $param2) {
            $p->eliminar($param, $param2);
        } elseif ($param) {
            $p->index($param);
        } else {
            header("Location: /yo_voto/candidatos");
        }
        break;

    case 'jurados':
        require_once 'controllers/JuradoController.php';
        $j = new JuradoController();
        if ($method == 'eliminar' && $param) {
            $j->eliminar($param);
        } else {
            $j->index();
        }
        break;

    case 'equipo':
        require_once 'controllers/EquipoController.php';
        $eq = new EquipoController();
        if ($method == 'eliminar' && $param && $param2) {
            $eq->eliminar($param, $param2);
        } elseif ($method == 'editar' && $param && $param2) {
            $eq->editar($param, $param2);
        } elseif ($param) {
            $eq->index($param);
        } else {
            header("Location: /yo_voto/candidatos");
        }
        break;

    default:
        header("Location: /yo_voto/admin/dashboard");
        break;
}
?>