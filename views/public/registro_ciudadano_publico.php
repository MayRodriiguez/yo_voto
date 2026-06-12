<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();
$config = [];
$resConfig = $conn->query("SELECT clave, valor FROM configuracion");
while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }
$votacionActiva = $config['votacion_activa'] ?? '0';

// La votación se controla solo con el botón del dashboard

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error   = $_SESSION['error_registro'] ?? null;
$success = $_SESSION['success_registro'] ?? null;
unset($_SESSION['error_registro'], $_SESSION['success_registro']);

$extensiones   = ['LP','OR','CB','PT','CH','TJ','SC','BE','PD'];
$departamentos = ['Beni','Chuquisaca','Cochabamba','La Paz','Oruro','Pando','Potosí','Santa Cruz','Tarija'];
$maxDate = date('Y-m-d', strtotime('-18 years'));
$minDate = date('Y-m-d', strtotime('-99 years'));

$coordsDep = [
    'La Paz'     => [-16.5000, -68.1500],
    'Cochabamba' => [-17.3895, -66.1568],
    'Santa Cruz' => [-17.7833, -63.1822],
    'Oruro'      => [-17.9833, -67.1500],
    'Potosí'     => [-19.5836, -65.7531],
    'Chuquisaca' => [-19.0478, -65.2595],
    'Tarija'     => [-21.5355, -64.7295],
    'Beni'       => [-14.8333, -64.9000],
    'Pando'      => [-11.0267, -68.7667],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: rgba(10,22,50,0.97); backdrop-filter: blur(10px); height: 60px; display: flex; align-items: center; padding: 0 40px; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-nav { display: flex; align-items: center; gap: 6px; }
        .navbar-nav a { color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .main { min-height: 100vh; background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%); padding: 90px 24px 60px; display: flex; flex-direction: column; align-items: center; position: relative; }
        .main::before { content: ''; position: fixed; inset: 0; pointer-events: none; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 36px 36px; }
        .hero-text { text-align: center; margin-bottom: 32px; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35); color: #FF8C38; padding: 6px 18px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 40px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; width: 100%; max-width: 760px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4); position: relative; z-index: 1; }
        .card-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 22px 28px; display: flex; align-items: center; gap: 16px; }
        .card-head-icon { width: 46px; height: 46px; border-radius: 12px; background: #FF6B00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-head-icon i { font-size: 20px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 18px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 3px 0 0; }
        .card-body { padding: 30px 28px; }
        .alert { padding: 14px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 10px; }
        .alert-success { background: rgba(39,174,96,0.12); color: #5cdb95; border-left: 4px solid #27AE60; }
        .alert-danger { background: rgba(231,76,60,0.12); color: #ff6b6b; border-left: 4px solid #E74C3C; }
        .alert a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        .closed-box { text-align: center; padding: 50px 30px; }
        .closed-box i { font-size: 64px; color: rgba(255,107,0,0.4); margin-bottom: 20px; display: block; }
        .closed-box h3 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 24px; color: #fff; margin-bottom: 12px; }
        .closed-box p { color: rgba(255,255,255,0.45); font-size: 15px; line-height: 1.6; margin-bottom: 28px; }
        .btn-volver { display: inline-flex; align-items: center; gap: 9px; background: #FF6B00; color: #fff; padding: 13px 28px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 15px; text-decoration: none; box-shadow: 0 6px 24px rgba(255,107,0,0.35); transition: .2s; }
        .btn-volver:hover { background: #FF8C38; transform: translateY(-2px); }
        .sec-title { font-size: 11px; font-weight: 700; color: #FF6B00; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; margin-top: 26px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25); display: flex; align-items: center; gap: 8px; }
        .sec-title:first-child { margin-top: 0; }
        .form-grid { display: grid; gap: 14px; }
        .form-grid.grid-ciudad { grid-template-columns: 1fr 1fr; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-ci { grid-template-columns: 1fr 100px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.65); display: flex; align-items: center; gap: 6px; }
        .form-group label .req { color: #FF6B00; font-size: 14px; }
        .input-wrap { position: relative; }
        .input-wrap .ico { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.22); font-size: 13px; pointer-events: none; }
        .form-group input, .form-group select { width: 100%; padding: 11px 12px 11px 34px; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.06); color: #fff; transition: .2s; font-family: inherit; appearance: none; }
        .form-group select { cursor: pointer; }
        .form-group input::placeholder { color: rgba(255,255,255,0.22); }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.09); box-shadow: 0 0 0 3px rgba(255,107,0,0.1); }
        .form-group small { font-size: 11px; color: rgba(255,255,255,0.28); }
        .form-group select option { background: #0d2251; color: #fff; }
        .no-icon { padding-left: 12px !important; }
        .genero-pills { display: flex; gap: 10px; }
        .genero-pill { flex: 1; }
        .genero-pill input[type=radio] { display: none; }
        .genero-pill label { display: flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 8px; border-radius: 10px; border: 1.5px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); cursor: pointer; font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.45); transition: .2s; }
        .genero-pill input:checked + label { border-color: #FF6B00; background: rgba(255,107,0,0.12); color: #FF8C38; }
        .strength-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.07); overflow: hidden; margin-top: 6px; }
        .strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: .3s; }
        .strength-text { font-size: 11px; color: rgba(255,255,255,0.28); margin-top: 4px; }
        .mapa-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; overflow: hidden; margin-bottom: 14px; }
        .mapa-search { display: flex; gap: 8px; padding: 14px; }
        .mapa-search input { flex: 1; padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.06); color: #fff; font-family: inherit; }
        .mapa-search input::placeholder { color: rgba(255,255,255,0.3); }
        .mapa-search input:focus { outline: none; border-color: #FF6B00; }
        .mapa-search button { background: #FF6B00; color: #fff; border: none; padding: 10px 16px; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 700; white-space: nowrap; transition: .2s; }
        .mapa-search button:hover { background: #FF8C38; }
        #mapa { height: 280px; width: 100%; }
        .ubicacion-confirmada { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); border-left: 4px solid #27AE60; border-radius: 10px; padding: 14px 16px; margin: 14px; display: none; }
        .ubicacion-confirmada.visible { display: block; }
        .foto-box { background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,107,0,0.3); border-radius: 16px; padding: 28px; text-align: center; }
        .foto-preview-wrap { position: relative; width: 160px; height: 160px; margin: 0 auto 16px; }
        .foto-preview { width: 160px; height: 160px; border-radius: 50%; object-fit: cover; border: 4px solid #FF6B00; display: none; }
        .foto-placeholder { width: 160px; height: 160px; border-radius: 50%; background: rgba(255,255,255,0.05); border: 3px dashed rgba(255,107,0,0.4); display: flex; flex-direction: column; align-items: center; justify-content: center; color: rgba(255,255,255,0.3); cursor: pointer; transition: .2s; }
        .foto-placeholder:hover { border-color: #FF6B00; color: #FF8C38; background: rgba(255,107,0,0.05); }
        .foto-placeholder i { font-size: 36px; margin-bottom: 8px; }
        .foto-placeholder span { font-size: 12px; font-weight: 600; }
        .tips-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 16px; }
        .tip { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.04); border-radius: 8px; padding: 8px 12px; font-size: 12px; color: rgba(255,255,255,0.6); }
        .tip-ico { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; }
        .tip-ico.ok { background: rgba(39,174,96,0.2); color: #27AE60; }
        .tip-ico.no { background: rgba(231,76,60,0.2); color: #E74C3C; }
        .btn-upload { background: #FF6B00; color: #fff; padding: 10px 22px; border-radius: 10px; border: none; font-size: 13px; font-weight: 700; cursor: pointer; transition: .2s; display: inline-flex; align-items: center; gap: 7px; margin-top: 12px; }
        .btn-upload:hover { background: #FF8C38; }
        .btn-retake { background: rgba(255,255,255,0.07); color: #fff; padding: 10px 22px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.14); font-size: 13px; font-weight: 700; cursor: pointer; transition: .2s; display: none; align-items: center; gap: 7px; margin-top: 12px; margin-left: 8px; }
        .btn-retake:hover { background: rgba(255,255,255,0.12); }
        .foto-status { margin-top: 14px; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; display: none; }
        .foto-status.success { background: rgba(39,174,96,0.12); color: #5cdb95; border-left: 4px solid #27AE60; }
        .foto-status.error { background: rgba(231,76,60,0.12); color: #ff6b6b; border-left: 4px solid #E74C3C; }
        .foto-status.info { background: rgba(25,118,210,0.12); color: #7eb3ff; border-left: 4px solid #1976D2; }
        .btn-submit { width: 100%; padding: 14px; margin-top: 26px; background: #FF6B00; color: #fff; border: none; border-radius: 12px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px; cursor: pointer; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.35); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover:not(:disabled) { background: #FF8C38; transform: translateY(-2px); }
        .btn-submit:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }
        .login-link { text-align: center; margin-top: 18px; font-size: 14px; color: rgba(255,255,255,0.32); }
        .login-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        footer { text-align: center; padding: 24px; font-size: 12px; color: rgba(255,255,255,0.22); background: #070e1f; border-top: 1px solid rgba(255,255,255,0.05); }
        footer span { color: #FF6B00; font-weight: 700; }
        @media (max-width: 640px) {
            .grid-2 { grid-template-columns: 1fr; }
            .grid-ci { grid-template-columns: 1fr 88px; }
            .navbar { padding: 0 16px; }
            .card-body { padding: 22px 16px; }
            .genero-pills { flex-direction: column; }
            .tips-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope" style="color:#FF6B00;"></i> Yo <span>Voto</span></a>
    <nav class="navbar-nav">
        <a href="/yo_voto/"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
        <a href="/yo_voto/login"><i class="fas fa-lock"></i> Admin</a>
    </nav>
</header>

<div class="main">
    <div class="hero-text">
        <div class="hero-badge"><i class="fas fa-shield-alt"></i> Bolivia 2026</div>
        <h1>Crea tu <span>cuenta</span></h1>
        <p>Regístrate para participar en las elecciones Bolivia 2026</p>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h2>Formulario de Registro</h2>
                <p>Completa todos los campos obligatorios (*)</p>
            </div>
        </div>
        <div class="card-body">

            <?php if ($votacionActiva == '1'): ?>
                <div class="closed-box">
                    <i class="fas fa-lock"></i>
                    <h3>Registro no disponible</h3>
                    <p>El período de registro está cerrado.<br>Las votaciones aún no han sido habilitadas por el administrador.</p>
                    <a href="/yo_voto/" class="btn-volver"><i class="fas fa-home"></i> Volver al inicio</a>
                </div>

            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?= $success ?><br><br><a href="/yo_voto/">← Ir a iniciar sesión</a></div>
                </div>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/yo_voto/registro-ciudadano" id="registroForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                    <!-- DATOS PERSONALES -->
                    <div class="sec-title"><i class="fas fa-user"></i> Datos Personales</div>
                    <div class="form-grid grid-2" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Nombres <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-user"></i>
                                <input type="text" name="nombres" placeholder="Ej: Juan Carlos" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Apellidos <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-user"></i>
                                <input type="text" name="apellidos" placeholder="Ej: Mamani Quispe" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label><i class="fas fa-id-card"></i> Número de CI  <span class="req">*</span></label>
                        <div class="form-grid grid-ci">
                            <div class="input-wrap"><i class="ico fas fa-id-card"></i>
                                <input type="text" name="carnet" id="carnet" placeholder="Número de CI" maxlength="10" inputmode="numeric" required>
                            </div>
                            <div class="input-wrap">
                                <select name="extension" class="no-icon" required>
                                    <option value="">Ext.</option>
                                    <?php foreach($extensiones as $ext): ?>
                                        <option value="<?= $ext ?>"><?= $ext ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-grid grid-2" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Fecha de Nacimiento <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-calendar"></i>
                                <input type="date" name="fecha_nac" min="<?= $minDate ?>" max="<?= $maxDate ?>" required onkeydown="return false" style="cursor:pointer;">
                            </div>
                            <small>Debes ser mayor de 18 años</small>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Teléfono / Celular</label>
                            <div class="input-wrap"><i class="ico fas fa-phone"></i>
                                <input type="text" name="telefono" placeholder="Ej: 77712345" maxlength="8">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label><i class="fas fa-venus-mars"></i> Género <span class="req">*</span></label>
                        <div class="genero-pills">
                            <div class="genero-pill"><input type="radio" name="genero" id="g-m" value="Masculino" required><label for="g-m"><i class="fas fa-mars"></i> Masculino</label></div>
                            <div class="genero-pill"><input type="radio" name="genero" id="g-f" value="Femenino"><label for="g-f"><i class="fas fa-venus"></i> Femenino</label></div>
                        </div>
                    </div>

                    <!-- UBICACIÓN CON MAPA -->
                    <div class="sec-title"><i class="fas fa-map-marker-alt"></i> Tu Ubicación</div>

                    <div class="form-grid" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label><i class="fas fa-map"></i> Departamento <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-map"></i>
                                <select name="departamento" id="departamento" required onchange="cambiarDepartamento(this.value)">
                                    <option value=""> Selecciona tu departamento </option>
                                    <?php foreach($departamentos as $dep): ?>
                                        <option value="<?= $dep ?>"><?= $dep ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <!-- Ciudad: solo para La Paz -->
                        <div class="form-group" id="grupo-ciudad" style="display:none;">
                            <label><i class="fas fa-city"></i> Ciudad <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-city"></i>
                                <select name="ciudad" id="ciudad" style="padding-left:34px;width:100%;">
                                    <option value="" disabled selected> Selecciona una ciudad </option>
                                    <option value="La Paz">La Paz</option>
                                    <option value="El Alto">El Alto</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección con autocompletado -->
                    <div class="form-group" style="margin-bottom:6px;">
                        <label><i class="fas fa-map-pin"></i> Busca tu zona o dirección <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-search"></i>
                            <input type="text" id="zona-buscar" name="zona" placeholder="Ej: Miraflores, Villa Fátima, Calle Murillo..." autocomplete="off" oninput="autocompletarZona(this.value)" style="padding-left:34px;" required>
                        </div>
                        <div id="zona-sugerencias" style="display:none;background:#0d2251;border:1px solid rgba(255,107,0,0.3);border-radius:10px;margin-top:4px;overflow:hidden;max-height:200px;overflow-y:auto;z-index:999;position:relative;"></div>
                        <small>Escribe tu zona o barrio y selecciona de la lista</small>
                    </div>

                    <div class="form-group" style="margin-bottom:14px;">
                        <label><i class="fas fa-home"></i> Dirección exacta <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-home"></i>
                            <input type="text" name="direccion" id="direccion" placeholder="Ej: Calle Murillo #123" required oninput="actualizarBuscador()">
                        </div>
                    </div>

                    <!-- MAPA -->
                    <div class="mapa-box" style="margin-bottom:14px;">
                        <div class="mapa-search">
                            <input type="text" id="buscar-mapa" placeholder="Busca tu dirección en el mapa..." onkeypress="if(event.key==='Enter'){event.preventDefault();buscarEnMapa();}">
                            <button type="button" onclick="buscarEnMapa()"><i class="fas fa-search"></i> Buscar</button>
                            <button type="button" onclick="usarUbicacionActual()" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);" title="Usar mi ubicación actual"><i class="fas fa-crosshairs"></i></button>
                        </div>
                        <div style="background:rgba(25,118,210,0.1);border-left:3px solid #1976D2;padding:8px 14px;font-size:12px;color:rgba(255,255,255,0.55);display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-hand-pointer" style="color:#1976D2;"></i>
                            Haz clic en el mapa o arrastra el marcador para ajustar tu ubicación exacta
                        </div>
                        <div id="mapa"></div>
                        <div class="ubicacion-confirmada" id="ubicacion-confirmada">
                            <div style="font-family:'Montserrat',sans-serif;font-weight:800;color:#fff;font-size:14px;margin-bottom:4px;"><i class="fas fa-check-circle" style="color:#27AE60;margin-right:6px;"></i> Ubicación marcada</div>
                            <div id="ubicacion-texto" style="font-size:13px;color:rgba(255,255,255,0.5);"></div>
                        </div>
                    </div>

                    <input type="hidden" name="lat" id="lat-hidden" value="">
                    <input type="hidden" name="lng" id="lng-hidden" value="">

                    <!-- CONTACTO -->
                    <div class="sec-title"><i class="fas fa-envelope"></i> Contacto</div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="tu@correo.com" required>
                        </div>
                    </div>

                    <!-- SEGURIDAD -->
                    <div class="sec-title"><i class="fas fa-shield-alt"></i> Seguridad</div>
                    <div class="form-grid grid-2" style="margin-bottom:14px;">
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> Contraseña <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-lock"></i>
                                <input type="password" name="password" id="passInput" placeholder="Solo 6 caracteres" required maxlength="6">
                            </div>
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                            <div class="strength-text" id="strengthText"></div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-check-double"></i> Confirmar Contraseña <span class="req">*</span></label>
                            <div class="input-wrap"><i class="ico fas fa-check-double"></i>
                                <input type="password" name="confirm_password" id="confirmInput" placeholder="Repite tu contraseña" required maxlength="6">
                            </div>
                            <div class="strength-text" id="matchText"></div>
                        </div>
                    </div>

                    <!-- FOTO -->
                    <div class="sec-title"><i class="fas fa-camera"></i> Foto de Verificación</div>
                    <div class="foto-box">

                        <!-- Estado: sin foto -->
                        <div id="estado-inicial">
                            <div class="foto-placeholder" id="foto-placeholder" style="width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,0.05);border:3px dashed rgba(255,107,0,0.4);display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,0.3);margin:0 auto 16px;">
                                <i class="fas fa-user-circle" style="font-size:48px;margin-bottom:8px;"></i>
                                <span style="font-size:12px;font-weight:600;">Sin foto</span>
                            </div>
                            <p style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:16px;">Toma una foto con tu cámara o sube una imagen de tu rostro.<br>Aparecerá en tu certificado de sufragio.</p>
                            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                                <button type="button" class="btn-upload" id="btn-abrir-camara" onclick="abrirCamara()">
                                    <i class="fas fa-camera"></i> Usar Cámara
                                </button>
                                <button type="button" class="btn-upload" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);" onclick="document.getElementById('foto-input').click()">
                                    <i class="fas fa-upload"></i> Subir Foto
                                </button>
                            </div>
                        </div>

                        <!-- Estado: cámara activa -->
                        <div id="estado-camara" style="display:none;">
                            <div style="position:relative;width:280px;margin:0 auto 14px;">
                                <video id="camara-video" autoplay playsinline style="width:280px;height:280px;border-radius:50%;object-fit:cover;border:4px solid #FF6B00;display:block;background:#000;"></video>
                                <!-- Guía de rostro -->
                                <div style="position:absolute;inset:0;border-radius:50%;border:2px dashed rgba(255,255,255,0.3);pointer-events:none;"></div>
                            </div>
                            <p style="color:rgba(255,255,255,0.45);font-size:13px;margin-bottom:14px;">Centra tu rostro en el círculo y presiona <strong style="color:#FF6B00;">Tomar Foto</strong></p>
                            <div class="tips-grid" style="margin-bottom:16px;">
                                <div class="tip"><span class="tip-ico ok"><i class="fas fa-check"></i></span><span>Buena iluminación</span></div>
                                <div class="tip"><span class="tip-ico ok"><i class="fas fa-check"></i></span><span>Rostro de frente</span></div>
                                <div class="tip"><span class="tip-ico no"><i class="fas fa-times"></i></span><span>Sin mascarilla</span></div>
                            </div>
                            <div style="display:flex;gap:10px;justify-content:center;">
                                <button type="button" class="btn-upload" onclick="tomarFoto()"><i class="fas fa-camera"></i> Tomar Foto</button>
                                <button type="button" class="btn-upload" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);" onclick="cancelarCamara()"><i class="fas fa-times"></i> Cancelar</button>
                            </div>
                        </div>

                        <!-- Estado: foto tomada/cargada -->
                        <div id="estado-preview" style="display:none;">
                            <div style="position:relative;width:160px;height:160px;margin:0 auto 16px;">
                                <img id="foto-preview" style="width:160px;height:160px;border-radius:50%;object-fit:cover;border:4px solid #FF6B00;display:block;" src="" alt="Foto de perfil">
                                <div style="position:absolute;bottom:4px;right:4px;width:32px;height:32px;background:#27AE60;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-check" style="color:#fff;font-size:14px;"></i>
                                </div>
                            </div>
                            <p style="color:#5cdb95;font-size:14px;font-weight:700;margin-bottom:14px;"><i class="fas fa-check-circle"></i> Foto lista para el registro</p>
                            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                                <button type="button" class="btn-upload" onclick="abrirCamara()"><i class="fas fa-camera"></i> Nueva foto</button>
                                <button type="button" class="btn-upload" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);" onclick="document.getElementById('foto-input').click()"><i class="fas fa-upload"></i> Subir otra</button>
                            </div>
                        </div>

                        <!-- Canvas oculto para captura -->
                        <canvas id="foto-canvas" style="display:none;"></canvas>
                        <!-- Input oculto para el archivo final que se envía -->
                        <input type="file" id="foto-input" name="foto_rostro" accept="image/jpeg,image/png,image/jpg" style="display:none;" onchange="procesarArchivoFoto(this)">
                        <!-- Input hidden para foto capturada por cámara (base64 → blob) -->
                        <input type="hidden" id="foto-data-url" name="">

                        <div id="foto-status" class="foto-status" style="margin-top:12px;"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="submit-btn" disabled>
                        <i class="fas fa-user-plus"></i> Crear mi Cuenta
                    </button>
                    <p style="text-align:center;margin-top:10px;font-size:12px;color:rgba(255,255,255,0.28);"> Debes tomar o subir una foto antes de registrarte</p>
                </form>

            <?php endif; ?>

            <div class="login-link">¿Ya tienes cuenta? <a href="/yo_voto/">Iniciar sesión aquí</a></div>
        </div>
    </div>
</div>

<footer><p> <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

<script>
const coordsDep = <?= json_encode($coordsDep) ?>;
let mapa, marcadorUsuario, zonaTimer = null;

document.addEventListener('DOMContentLoaded', () => {
    mapa = L.map('mapa').setView([-16.5000, -68.1500], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(mapa);
    mapa.on('click', function(e) {
        ubicarEnMapa(e.latlng.lat, e.latlng.lng);
        obtenerDireccion(e.latlng.lat, e.latlng.lng);
    });
    document.getElementById('submit-btn').disabled = true;
});

function getCiudad() {
    const el = document.getElementById('ciudad');
    return (el && el.offsetParent !== null) ? el.value : '';
}

function cambiarDepartamento(dep) {
    const grupoCiudad = document.getElementById('grupo-ciudad');
    const selectCiudad = document.getElementById('ciudad');
    const grid = grupoCiudad.parentElement;
    if (dep === 'La Paz') {
        grupoCiudad.style.display = 'block';
        selectCiudad.required = true;
        selectCiudad.value = '';
        grid.classList.add('grid-ciudad');
    } else {
        grupoCiudad.style.display = 'none';
        selectCiudad.required = false;
        selectCiudad.value = '';
        grid.classList.remove('grid-ciudad');
    }
    if (coordsDep[dep]) mapa.setView(coordsDep[dep], 13);
}

function autocompletarZona(query) {
    const dep    = document.getElementById('departamento').value;
    const ciudad = getCiudad();
    const lista  = document.getElementById('zona-sugerencias');
    if (query.length < 3) { lista.style.display = 'none'; return; }
    clearTimeout(zonaTimer);
    zonaTimer = setTimeout(() => {
        const lugar = ciudad || dep || 'Bolivia';
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', ' + lugar + ', Bolivia')}&limit=6&addressdetails=1&countrycodes=bo`, {
            headers: { 'Accept-Language': 'es' }
        })
            .then(r => r.json())
            .then(data => {
                if (!data.length) { lista.style.display = 'none'; return; }
                lista.style.display = 'block';
                lista.innerHTML = data.map(item => {
                    const partes = item.display_name.split(',');
                    const nombre = partes.slice(0, 3).join(',').trim();
                    const safeNombre = item.display_name.replace(/'/g, "\'");
                    return '<div onclick="seleccionarZona(\'' + safeNombre + '\', ' + item.lat + ', ' + item.lon + ')" '
                         + 'style="padding:10px 14px;cursor:pointer;font-size:13px;color:rgba(255,255,255,0.8);border-bottom:1px solid rgba(255,255,255,0.06);transition:.15s;" '
                         + 'onmouseover="this.style.background=\'rgba(255,107,0,0.15)\'" '
                         + 'onmouseout="this.style.background=\'transparent\'">'
                         + '<i class="fas fa-map-marker-alt" style="color:#FF6B00;margin-right:6px;font-size:11px;"></i>' + nombre
                         + '</div>';
                }).join('');
            })
            .catch(() => { lista.style.display = 'none'; });
    }, 400);
}


function seleccionarZona(nombre, lat, lng) {
    const partes = nombre.split(',');
    document.getElementById('zona-buscar').value = partes[0].trim();
    document.getElementById('zona-sugerencias').style.display = 'none';
    ubicarEnMapa(parseFloat(lat), parseFloat(lng));
    obtenerDireccion(parseFloat(lat), parseFloat(lng));
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#zona-buscar') && !e.target.closest('#zona-sugerencias')) {
        const lista = document.getElementById('zona-sugerencias');
        if (lista) lista.style.display = 'none';
    }
});

function actualizarBuscador() {
    const dir    = document.getElementById('direccion').value;
    const ciudad = getCiudad();
    const dep    = document.getElementById('departamento').value;
    if (dir.length > 4) {
        document.getElementById('buscar-mapa').value = dir + (ciudad ? ', ' + ciudad : dep ? ', ' + dep : '') + ', Bolivia';
    }
}

function buscarEnMapa() {
    const query  = document.getElementById('buscar-mapa').value.trim();
    const dep    = document.getElementById('departamento').value || 'La Paz';
    const ciudad = getCiudad();
    if (!query) return;

    const btnBuscar = document.querySelector('.mapa-search button');
    btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
    btnBuscar.disabled = true;

    // Intentos en orden: con ciudad → con departamento → solo query + Bolivia
    const intentos = [
        ciudad ? `${query}, ${ciudad}, ${dep}, Bolivia` : null,
        `${query}, ${dep}, Bolivia`,
        `${query}, Bolivia`
    ].filter(Boolean);

    function intentar(idx) {
        if (idx >= intentos.length) {
            btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
            btnBuscar.disabled = false;
            alert('No se encontró "' + query + '" en el mapa.\nIntenta escribir la dirección completa, por ejemplo:\n"Av. Bautista Saavedra 123, Miraflores"');
            return;
        }
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(intentos[idx])}&limit=3&countrycodes=bo&addressdetails=1`, {
            headers: { 'Accept-Language': 'es' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data || !data.length) {
                intentar(idx + 1); // probar siguiente variante
                return;
            }
            const resultado = data[0];
            const lat = parseFloat(resultado.lat);
            const lon = parseFloat(resultado.lon);
            ubicarEnMapa(lat, lon);
            obtenerDireccion(lat, lon);
            // Centrar el mapa con más zoom
            mapa.setView([lat, lon], 17);
            btnBuscar.innerHTML = '<i class="fas fa-search"></i> Buscar';
            btnBuscar.disabled = false;
        })
        .catch(() => {
            intentar(idx + 1);
        });
    }

    intentar(0);
}

function usarUbicacionActual() {
    if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        ubicarEnMapa(pos.coords.latitude, pos.coords.longitude);
        obtenerDireccion(pos.coords.latitude, pos.coords.longitude);
    }, () => alert('No se pudo obtener tu ubicación.'));
}

function ubicarEnMapa(lat, lng) {
    mapa.setView([lat, lng], 16);
    if (marcadorUsuario) mapa.removeLayer(marcadorUsuario);
    const iconUsuario = L.divIcon({
        html: '<div style="background:#FF6B00;color:#fff;width:20px;height:20px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 10px rgba(0,0,0,0.5);cursor:grab;"></div>',
        className: '', iconAnchor: [10, 10]
    });
    marcadorUsuario = L.marker([lat, lng], { icon: iconUsuario, draggable: true })
        .addTo(mapa).bindPopup('📍 Tu ubicación').openPopup();
    marcadorUsuario.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        document.getElementById('lat-hidden').value = pos.lat;
        document.getElementById('lng-hidden').value = pos.lng;
        obtenerDireccion(pos.lat, pos.lng);
    });
    document.getElementById('lat-hidden').value = lat;
    document.getElementById('lng-hidden').value = lng;
}

function obtenerDireccion(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
        .then(r => r.json())
        .then(data => {
            if (data && data.display_name) {
                const a = data.address;
                const partes = [];
                if (a.road) partes.push(a.road);
                if (a.house_number) partes.push('#' + a.house_number);
                if (a.suburb || a.neighbourhood) partes.push(a.suburb || a.neighbourhood);
                if (a.city || a.town) partes.push(a.city || a.town);
                const dirCorta = partes.length ? partes.join(', ') : data.display_name.split(',').slice(0,3).join(',');
                const dirInput = document.getElementById('direccion');
                if (!dirInput.value) dirInput.value = dirCorta;
                document.getElementById('ubicacion-texto').innerHTML = '<i class="fas fa-map-marker-alt" style="color:#FF6B00;margin-right:4px;"></i>' + dirCorta;
                document.getElementById('ubicacion-confirmada').classList.add('visible');
            }
        }).catch(() => {});
}

// =====================================================
// CÁMARA / FOTO
// =====================================================
let streamActivo = null;

function abrirCamara() {
    mostrarStatus('', '');
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        mostrarStatus('Tu navegador no soporta acceso a la cámara. Usa la opción de subir foto.', 'error');
        return;
    }
    navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 640, facingMode: 'user' } })
        .then(function(stream) {
            streamActivo = stream;
            const video = document.getElementById('camara-video');
            video.srcObject = stream;
            document.getElementById('estado-inicial').style.display = 'none';
            document.getElementById('estado-preview').style.display = 'none';
            document.getElementById('estado-camara').style.display = 'block';
        })
        .catch(function(err) {
            if (err.name === 'NotAllowedError') {
                mostrarStatus('Permiso de cámara denegado. Por favor permite el acceso o usa la opción de subir foto.', 'error');
            } else {
                mostrarStatus('No se pudo acceder a la cámara. Usa la opción de subir foto.', 'error');
            }
        });
}

function cancelarCamara() {
    detenerStream();
    document.getElementById('estado-camara').style.display = 'none';
    const hayFoto = document.getElementById('foto-preview').src && document.getElementById('foto-preview').src !== window.location.href;
    document.getElementById('estado-preview').style.display = hayFoto ? 'block' : 'none';
    document.getElementById('estado-inicial').style.display = hayFoto ? 'none' : 'block';
}

function detenerStream() {
    if (streamActivo) {
        streamActivo.getTracks().forEach(t => t.stop());
        streamActivo = null;
    }
}

function tomarFoto() {
    const video  = document.getElementById('camara-video');
    const canvas = document.getElementById('foto-canvas');
    const size   = 400;
    canvas.width  = size;
    canvas.height = size;
    const ctx = canvas.getContext('2d');
    // Capturar cuadrado centrado del video
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    const lado = Math.min(vw, vh);
    const sx = (vw - lado) / 2;
    const sy = (vh - lado) / 2;
    ctx.drawImage(video, sx, sy, lado, lado, 0, 0, size, size);

    const dataURL = canvas.toDataURL('image/jpeg', 0.85);
    detenerStream();

    // Mostrar preview
    const preview = document.getElementById('foto-preview');
    preview.src = dataURL;
    document.getElementById('estado-camara').style.display = 'none';
    document.getElementById('estado-preview').style.display = 'block';
    document.getElementById('estado-inicial').style.display = 'none';
    document.getElementById('submit-btn').disabled = false;
    mostrarStatus('¡Foto tomada! Ya puedes registrarte.', 'success');

    // Convertir dataURL a Blob y asignarlo al input file para que el servidor lo reciba
    dataURLaBlob(dataURL, function(blob) {
        const dt = new DataTransfer();
        const archivo = new File([blob], 'foto_rostro_camara.jpg', { type: 'image/jpeg' });
        dt.items.add(archivo);
        document.getElementById('foto-input').files = dt.files;
    });
}

function dataURLaBlob(dataURL, callback) {
    const parts = dataURL.split(',');
    const mime  = parts[0].match(/:(.*?);/)[1];
    const bstr  = atob(parts[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    while (n--) u8arr[n] = bstr.charCodeAt(n);
    callback(new Blob([u8arr], { type: mime }));
}

function procesarArchivoFoto(input) {
    const file = input.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { mostrarStatus('La foto es muy grande. Máximo 5MB.', 'error'); return; }
    const reader = new FileReader();
    reader.onload = (e) => {
        const preview = document.getElementById('foto-preview');
        preview.src = e.target.result;
        document.getElementById('estado-camara').style.display = 'none';
        document.getElementById('estado-inicial').style.display = 'none';
        document.getElementById('estado-preview').style.display = 'block';
        document.getElementById('submit-btn').disabled = false;
        mostrarStatus('Foto cargada correctamente.', 'success');
    };
    reader.readAsDataURL(file);
}

function mostrarStatus(msg, type) {
    const s = document.getElementById('foto-status');
    if (!msg) { s.style.display = 'none'; return; }
    s.style.display = 'block';
    s.className = 'foto-status ' + type;
    s.innerHTML = msg;
    if (type === 'success') setTimeout(() => s.style.display = 'none', 4000);
}

// Detener cámara si el usuario sale de la página sin guardar
window.addEventListener('beforeunload', detenerStream);

document.getElementById('carnet').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

const passInput    = document.getElementById('passInput');
const confirmInput = document.getElementById('confirmInput');
const fill         = document.getElementById('strengthFill');
const txt          = document.getElementById('strengthText');
const matchTxt     = document.getElementById('matchText');

passInput.addEventListener('input', () => {
    const v = passInput.value;
    let s = 0;
    if (v.length >= 6) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    const levels = [{w:'33%',c:'#E74C3C',l:'Mala'},{w:'66%',c:'#F1C40F',l:'Buena'},{w:'100%',c:'#27AE60',l:'Muy buena'}];
    const lv = v.length > 0 ? (levels[Math.min(s-1,2)] || levels[0]) : {w:'0%',c:'transparent',l:''};
    fill.style.width = lv.w; fill.style.background = lv.c;
    txt.textContent = lv.l; txt.style.color = lv.c;
    checkMatch();
});

confirmInput.addEventListener('input', checkMatch);
function checkMatch() {
    if (!confirmInput.value) { matchTxt.textContent = ''; return; }
    if (passInput.value === confirmInput.value) { matchTxt.textContent = '✓ Las contraseñas coinciden'; matchTxt.style.color = '#27AE60'; }
    else { matchTxt.textContent = '✗ Las contraseñas no coinciden'; matchTxt.style.color = '#E74C3C'; }
}

document.getElementById('registroForm').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
});
</script>
</body>
</html>