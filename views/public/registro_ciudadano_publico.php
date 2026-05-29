<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = $_SESSION['error_registro'] ?? null;
$success = $_SESSION['success_registro'] ?? null;
unset($_SESSION['error_registro'], $_SESSION['success_registro']);

$extensiones = ['LP','OR','CB','PT','CH','TJ','SC','BE','PD'];
$departamentos = ['Beni','Chuquisaca','Cochabamba','La Paz','Oruro','Pando','Potosí','Santa Cruz','Tarija'];
$maxDate = date('Y-m-d', strtotime('-18 years'));
$minDate = date('Y-m-d', strtotime('-99 years'));

$recintos = [
    'La Paz' => [
        ['nombre' => 'U.E. Franco Boliviano', 'lat' => -16.5000, 'lng' => -68.1500, 'mesa' => 12],
        ['nombre' => 'U.E. Colegio Nacional Ayacucho', 'lat' => -16.4955, 'lng' => -68.1336, 'mesa' => 24],
        ['nombre' => 'U.E. Simón Bolívar', 'lat' => -16.5100, 'lng' => -68.1200, 'mesa' => 36],
        ['nombre' => 'U.E. Gualberto Villarroel', 'lat' => -16.5200, 'lng' => -68.1400, 'mesa' => 48],
    ],
    'Cochabamba' => [
        ['nombre' => 'U.E. Sucre', 'lat' => -17.3895, 'lng' => -66.1568, 'mesa' => 15],
        ['nombre' => 'U.E. Ayacucho', 'lat' => -17.3800, 'lng' => -66.1600, 'mesa' => 28],
    ],
    'Santa Cruz' => [
        ['nombre' => 'U.E. Santa Cruz de la Sierra', 'lat' => -17.7833, 'lng' => -63.1822, 'mesa' => 20],
        ['nombre' => 'U.E. Andrés Ibáñez', 'lat' => -17.7900, 'lng' => -63.1700, 'mesa' => 35],
    ],
    'Oruro' => [
        ['nombre' => 'U.E. Oruro', 'lat' => -17.9833, 'lng' => -67.1500, 'mesa' => 10],
    ],
    'Potosí' => [
        ['nombre' => 'U.E. Potosí', 'lat' => -19.5836, 'lng' => -65.7531, 'mesa' => 8],
    ],
    'Chuquisaca' => [
        ['nombre' => 'U.E. Sucre Central', 'lat' => -19.0478, 'lng' => -65.2595, 'mesa' => 14],
    ],
    'Tarija' => [
        ['nombre' => 'U.E. Tarija', 'lat' => -21.5355, 'lng' => -64.7295, 'mesa' => 6],
    ],
    'Beni' => [
        ['nombre' => 'U.E. Trinidad', 'lat' => -14.8333, 'lng' => -64.9000, 'mesa' => 5],
    ],
    'Pando' => [
        ['nombre' => 'U.E. Cobija', 'lat' => -11.0267, 'lng' => -68.7667, 'mesa' => 3],
    ],
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
        .sec-title { font-size: 11px; font-weight: 700; color: #FF6B00; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; margin-top: 26px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25); display: flex; align-items: center; gap: 8px; }
        .sec-title:first-child { margin-top: 0; }
        .form-grid { display: grid; gap: 14px; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-3 { grid-template-columns: 1fr 1fr 1fr; }
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
        .recinto-asignado { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); border-left: 4px solid #27AE60; border-radius: 10px; padding: 14px 16px; margin: 14px; display: none; }
        .recinto-asignado.visible { display: block; }
        .recinto-nombre { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 15px; margin-bottom: 6px; }
        .recinto-detalle { font-size: 13px; color: rgba(255,255,255,0.5); display: flex; gap: 16px; }
        .recinto-detalle span { display: flex; align-items: center; gap: 6px; }
        /* Badge CSRF */
        .csrf-badge { display: flex; align-items: center; gap: 10px; background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); border-left: 4px solid #27AE60; border-radius: 10px; padding: 12px 16px; margin-bottom: 14px; font-size: 13px; color: rgba(255,255,255,0.6); }
        .csrf-badge i { color: #27AE60; font-size: 16px; flex-shrink: 0; }
        .csrf-badge strong { color: #5cdb95; }
        .btn-submit { width: 100%; padding: 14px; margin-top: 26px; background: #FF6B00; color: #fff; border: none; border-radius: 12px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px; cursor: pointer; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.35); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover:not(:disabled) { background: #FF8C38; transform: translateY(-2px); }
        .btn-submit:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }
        .login-link { text-align: center; margin-top: 18px; font-size: 14px; color: rgba(255,255,255,0.32); }
        .login-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        footer { text-align: center; padding: 24px; font-size: 12px; color: rgba(255,255,255,0.22); background: #070e1f; border-top: 1px solid rgba(255,255,255,0.05); }
        footer span { color: #FF6B00; font-weight: 700; }
        @media (max-width: 640px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .grid-ci { grid-template-columns: 1fr 88px; }
            .navbar { padding: 0 16px; }
            .card-body { padding: 22px 16px; }
            .genero-pills { flex-direction: column; }
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

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?= $success ?><br><br><a href="/yo_voto/">← Ir a iniciar sesión</a></div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="/yo_voto/registro-ciudadano" id="registroForm" enctype="multipart/form-data">

                <!-- TOKEN CSRF — SEGURIDAD -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <!-- Campos requeridos por el controlador (sin facial) -->
                <input type="hidden" name="face_registered" value="1">
                <input type="hidden" name="face_descriptor" value="[]">

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
                    <label><i class="fas fa-id-card"></i> Número de CI + Extensión <span class="req">*</span></label>
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
                            <input type="date" name="fecha_nac" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>
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
                        <div class="genero-pill"><input type="radio" name="genero" id="g-o" value="Prefiero no decir"><label for="g-o"><i class="fas fa-genderless"></i> Prefiero no decir</label></div>
                    </div>
                </div>

                <!-- UBICACIÓN CON MAPA -->
                <div class="sec-title"><i class="fas fa-map-marker-alt"></i> Ubicación y Recinto Electoral</div>

                <div class="form-grid grid-3" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label><i class="fas fa-map"></i> Departamento <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-map"></i>
                            <select name="departamento" id="departamento" required onchange="cambiarDepartamento(this.value)">
                                <option value="">Seleccionar</option>
                                <?php foreach($departamentos as $dep): ?>
                                    <option value="<?= $dep ?>"><?= $dep ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> Ciudad</label>
                        <div class="input-wrap"><i class="ico fas fa-city"></i>
                            <input type="text" name="ciudad" id="ciudad" placeholder="Ej: La Paz">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-pin"></i> Zona / Av</label>
                        <div class="input-wrap"><i class="ico fas fa-map-pin"></i>
                            <input type="text" name="zona" placeholder="Ej: Miraflores">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:14px;">
                    <label><i class="fas fa-home"></i> Dirección</label>
                    <div class="input-wrap"><i class="ico fas fa-home"></i>
                        <input type="text" name="direccion" id="direccion" placeholder="Ej: Calle Murillo #123" oninput="buscarDireccionAuto()">
                    </div>
                </div>

                <!-- MAPA -->
                <div class="mapa-box" style="margin-bottom:14px;">
                    <div class="mapa-search">
                        <input type="text" id="buscar-mapa" placeholder="Busca tu dirección en el mapa..." onkeypress="if(event.key==='Enter'){event.preventDefault();buscarEnMapa();}">
                        <button type="button" onclick="buscarEnMapa()"><i class="fas fa-search"></i> Buscar</button>
                        <button type="button" onclick="usarUbicacionActual()" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);"><i class="fas fa-crosshairs"></i></button>
                    </div>
                    <div id="mapa"></div>
                    <div class="recinto-asignado" id="recinto-asignado">
                        <div class="recinto-nombre" id="recinto-nombre">—</div>
                        <div class="recinto-detalle">
                            <span><i class="fas fa-table" style="color:#FF6B00;"></i> Mesa: <strong id="recinto-mesa">—</strong></span>
                            <span><i class="fas fa-map-marker-alt" style="color:#FF6B00;"></i> <span id="recinto-dist">—</span></span>
                        </div>
                    </div>
                </div>

                <!-- Campos ocultos para recinto y mesa -->
                <input type="hidden" name="recinto" id="recinto-hidden" value="">
                <input type="hidden" name="numero_mesa" id="mesa-hidden" value="">
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
                            <input type="password" name="password" id="passInput" placeholder="Mín. 6 caracteres" required>
                        </div>
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-double"></i> Confirmar Contraseña <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-check-double"></i>
                            <input type="password" name="confirm_password" id="confirmInput" placeholder="Repite tu contraseña" required>
                        </div>
                        <div class="strength-text" id="matchText"></div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submit-btn">
                    <i class="fas fa-user-plus"></i> Crear mi Cuenta
                </button>

            </form>
            <?php endif; ?>

            <div class="login-link">¿Ya tienes cuenta? <a href="/yo_voto/">Iniciar sesión aquí</a></div>
        </div>
    </div>
</div>

<footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

<script>
const recintos = <?= json_encode($recintos) ?>;

let mapa, marcadorUsuario, marcadoresRecintos = [];

document.addEventListener('DOMContentLoaded', () => {
    mapa = L.map('mapa').setView([-16.5000, -68.1500], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);
});

function cambiarDepartamento(dep) {
    if (!dep || !recintos[dep]) return;
    const r = recintos[dep];
    mapa.setView([r[0].lat, r[0].lng], 13);
    mostrarRecintos(dep);
}

function mostrarRecintos(dep) {
    marcadoresRecintos.forEach(m => mapa.removeLayer(m));
    marcadoresRecintos = [];
    if (!recintos[dep]) return;
    const iconRecinto = L.divIcon({
        html: '<div style="background:#FF6B00;color:#fff;padding:4px 8px;border-radius:8px;font-size:11px;font-weight:700;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3);">📍 Recinto</div>',
        className: '', iconAnchor: [30, 10]
    });
    recintos[dep].forEach(r => {
        const m = L.marker([r.lat, r.lng], {icon: iconRecinto})
            .addTo(mapa)
            .bindPopup(`<strong>${r.nombre}</strong><br>Mesa N° ${r.mesa}`);
        marcadoresRecintos.push(m);
    });
}

function buscarEnMapa() {
    const query = document.getElementById('buscar-mapa').value.trim();
    const dep = document.getElementById('departamento').value;
    if (!query) return;
    const busqueda = query + (dep ? ', ' + dep : '') + ', Bolivia';
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(busqueda)}&limit=1`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) { alert('No se encontró la dirección.'); return; }
            ubicarEnMapa(parseFloat(data[0].lat), parseFloat(data[0].lon), data[0].display_name);
        })
        .catch(() => alert('Error al buscar. Verifica tu conexión.'));
}

function buscarDireccionAuto() {
    const dir = document.getElementById('direccion').value;
    const ciudad = document.getElementById('ciudad').value;
    if (dir.length > 5) {
        document.getElementById('buscar-mapa').value = dir + (ciudad ? ', ' + ciudad : '');
    }
}

function usarUbicacionActual() {
    if (!navigator.geolocation) { alert('Tu navegador no soporta geolocalización.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        ubicarEnMapa(pos.coords.latitude, pos.coords.longitude, 'Tu ubicación actual');
    }, () => alert('No se pudo obtener tu ubicación.'));
}

function ubicarEnMapa(lat, lng, nombre) {
    mapa.setView([lat, lng], 15);
    if (marcadorUsuario) mapa.removeLayer(marcadorUsuario);
    const iconUsuario = L.divIcon({
        html: '<div style="background:#1976D2;color:#fff;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.4);"></div>',
        className: '', iconAnchor: [8, 8]
    });
    marcadorUsuario = L.marker([lat, lng], {icon: iconUsuario})
        .addTo(mapa).bindPopup('📍 Tu ubicación').openPopup();
    document.getElementById('lat-hidden').value = lat;
    document.getElementById('lng-hidden').value = lng;
    const dep = document.getElementById('departamento').value;
    asignarRecintoMasCercano(lat, lng, dep);
}

function asignarRecintoMasCercano(lat, lng, dep) {
    let todosBuscar = [];
    if (dep && recintos[dep]) {
        todosBuscar = recintos[dep];
    } else {
        Object.values(recintos).forEach(arr => todosBuscar = todosBuscar.concat(arr));
    }
    if (!todosBuscar.length) return;
    let menor = Infinity, cercano = null;
    todosBuscar.forEach(r => {
        const dist = Math.sqrt(Math.pow(lat - r.lat, 2) + Math.pow(lng - r.lng, 2));
        if (dist < menor) { menor = dist; cercano = r; }
    });
    if (cercano) {
        const distKm = (menor * 111).toFixed(1);
        document.getElementById('recinto-nombre').textContent = cercano.nombre;
        document.getElementById('recinto-mesa').textContent = cercano.mesa;
        document.getElementById('recinto-dist').textContent = distKm + ' km aprox.';
        document.getElementById('recinto-asignado').classList.add('visible');
        document.getElementById('recinto-hidden').value = cercano.nombre;
        document.getElementById('mesa-hidden').value = cercano.mesa;
    }
}

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
    if (v.length >= 6)  s++;
    if (v.length >= 10) s++;
    if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++;
    if (/[^A-Za-z0-9]/.test(v)) s++;
    const levels = [
        {w:'20%',c:'#E74C3C',l:'Muy débil'},
        {w:'40%',c:'#E67E22',l:'Débil'},
        {w:'60%',c:'#F1C40F',l:'Regular'},
        {w:'80%',c:'#27AE60',l:'Buena'},
        {w:'100%',c:'#1ABC9C',l:'Muy segura'}
    ];
    const lv = v.length > 0 ? (levels[Math.min(s-1,4)] || levels[0]) : {w:'0%',c:'transparent',l:''};
    fill.style.width = lv.w; fill.style.background = lv.c;
    txt.textContent = lv.l; txt.style.color = lv.c;
    checkMatch();
});

confirmInput.addEventListener('input', checkMatch);
function checkMatch() {
    if (!confirmInput.value) { matchTxt.textContent = ''; return; }
    if (passInput.value === confirmInput.value) {
        matchTxt.textContent = '✓ Las contraseñas coinciden';
        matchTxt.style.color = '#27AE60';
    } else {
        matchTxt.textContent = '✗ Las contraseñas no coinciden';
        matchTxt.style.color = '#E74C3C';
    }
}

document.getElementById('registroForm').addEventListener('submit', function(e) {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
});
</script>
</body>
</html>