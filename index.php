<?php
// index.php - Router principal
// =====================================================
// CONFIGURACIÓN DE SESIÓN (DEBE IR AL PRINCIPIO)
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    ini_set('session.cookie_httponly', 1);
    session_name('YOVOTO_SESSION');
    session_start();
}

require_once 'config/database.php';

// =====================================================
// RUTAS DE LA API
// =====================================================
if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    require_once 'controllers/ApiController.php';
    $api = new ApiController();
    
    $url = $_SERVER['REQUEST_URI'];
    $url = str_replace('/yo_voto', '', $url);
    $url = parse_url($url, PHP_URL_PATH);
    
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
    }
    elseif (strpos($url, '/api/captcha') !== false) {
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
    } 
    exit();
}

// =====================================================
// OBTENER URL
// =====================================================
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = rtrim($url, '/');
$url = explode('/', $url);

$controller = isset($url[0]) && !empty($url[0]) ? $url[0] : 'public';
$method = isset($url[1]) ? $url[1] : 'index';
$param = isset($url[2]) ? $url[2] : null;
$param2 = isset($url[3]) ? $url[3] : null;

// =====================================================
// RUTAS PÚBLICAS
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

// Ruta para registro público de ciudadanos
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

// Ruta para verificar blockchain (pública)
if ($controller == 'blockchain-verificar') {
    require_once 'views/public/blockchain_verificar.php';
    exit();
}

// =====================================================
// RUTAS ADMIN (algunas no requieren login completo)
// =====================================================
if ($controller == 'propuestas') {
    require_once 'controllers/PropuestaController.php';
    $p = new PropuestaController();
    if ($method == 'editar' && $param && $param2) {
        $p->editar($param, $param2);
    } elseif ($method == 'eliminar' && $param && $param2) {
        $p->eliminar($param, $param2);
    } elseif (is_numeric($method)) {
        $p->index($method);
    } elseif ($param) {
        $p->index($param);
    } else {
        header("Location: /yo_voto/candidatos");
    }
    exit();
}

if ($controller == 'equipo') {
    require_once 'controllers/EquipoController.php';
    $e = new EquipoController();
    if ($method == 'eliminar' && $param && $param2) {
        $e->eliminar($param, $param2);
    } elseif ($method == 'editar' && $param && $param2) {
        $e->editar($param, $param2);
    } elseif (is_numeric($method)) {
        $e->index($method);
    } elseif ($param) {
        $e->index($param);
    } else {
        header("Location: /yo_voto/candidatos");
    }
    exit();
}

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

// Ruta para auditoría blockchain (admin)
if ($controller == 'admin' && $method == 'blockchain') {
    require_once 'views/admin/blockchain_auditoria.php';
    exit();
}

// =====================================================
// VERIFICAR AUTENTICACIÓN PARA EL RESTO DE RUTAS ADMIN
// =====================================================
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}

// =====================================================
// RUTAS PROTEGIDAS (requieren login de admin)
// =====================================================
switch ($controller) {
    case 'admin':
        if ($method == 'dashboard') {
            require_once 'views/admin/dashboard.php';
        }
        break;
    case 'candidatos':
        require_once 'controllers/CandidatoController.php';
        $c = new CandidatoController();
        if ($method == 'agregar') $c->agregar();
        elseif ($method == 'editar' && $param) $c->editar($param);
        elseif ($method == 'toggle' && $param) $c->toggle($param);
        elseif ($method == 'eliminar' && $param) $c->eliminar($param);
        elseif ($method == 'activar' && $param) $c->activarTipo($param);
        elseif ($method == 'resetear') $c->resetearTipo();
        elseif ($method == 'nacional' || $method == 'subnacional') $c->listarPorTipo($method);
        else $c->seleccionTipo();
        break;
    case 'jurados':
        require_once 'controllers/JuradoController.php';
        $j = new JuradoController();
        if ($method == 'eliminar' && $param) $j->eliminar($param);
        else $j->index();
        break;
    default:
        header("Location: /yo_voto/admin/dashboard");
        break;
}
?>