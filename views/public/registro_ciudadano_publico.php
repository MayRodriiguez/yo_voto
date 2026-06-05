<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Verificar votación activa + fecha y hora
$config = [];
$resConfig = $conn->query("SELECT clave, valor FROM configuracion");
while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }

$votacionActiva = $config['votacion_activa'] ?? '0';
$fechaVotacion  = $config['fecha_votacion']  ?? '';
$horaApertura   = $config['hora_apertura']   ?? '00:00';
$horaCierre     = $config['hora_cierre']     ?? '23:59';

if ($votacionActiva == '1' && $fechaVotacion) {
    $ahora        = new DateTime();
    $fechaHoyStr  = $ahora->format('Y-m-d');
    $horaAhoraStr = $ahora->format('H:i');
    if ($fechaHoyStr < $fechaVotacion) {
        $votacionActiva = '0'; // Aún no llega el día
    } elseif ($fechaHoyStr === $fechaVotacion) {
        if ($horaAhoraStr < $horaApertura || $horaAhoraStr > $horaCierre) {
            $votacionActiva = '0'; // Fuera de horario
        }
    }
    // Si ya pasó la fecha, el admin decide cuándo cerrar manualmente
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error   = $_SESSION['error_registro'] ?? null;
$success = $_SESSION['success_registro'] ?? null;
unset($_SESSION['error_registro'], $_SESSION['success_registro']);

$maxDate = date('Y-m-d', strtotime('-18 years'));
$minDate = date('Y-m-d', strtotime('-99 years'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: rgba(10,22,50,0.97); backdrop-filter: blur(10px); height: 60px; display: flex; align-items: center; padding: 0 40px; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-nav a { color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .main { min-height: 100vh; background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%); padding: 90px 24px 60px; display: flex; flex-direction: column; align-items: center; position: relative; }
        .main::before { content: ''; position: fixed; inset: 0; pointer-events: none; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 36px 36px; }
        .hero-text { text-align: center; margin-bottom: 32px; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35); color: #FF8C38; padding: 6px 18px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 40px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; width: 100%; max-width: 680px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4); position: relative; z-index: 1; }
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
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.65); display: flex; align-items: center; gap: 6px; }
        .form-group label .req { color: #FF6B00; font-size: 14px; }
        .input-wrap { position: relative; }
        .input-wrap .ico { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.22); font-size: 13px; pointer-events: none; }
        .form-group input { width: 100%; padding: 11px 12px 11px 34px; border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.06); color: #fff; transition: .2s; font-family: inherit; }
        .form-group input::placeholder { color: rgba(255,255,255,0.22); }
        .form-group input:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.09); box-shadow: 0 0 0 3px rgba(255,107,0,0.1); }
        .form-group small { font-size: 11px; color: rgba(255,255,255,0.28); }
        .strength-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.07); overflow: hidden; margin-top: 6px; }
        .strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: .3s; }
        .strength-text { font-size: 11px; color: rgba(255,255,255,0.28); margin-top: 4px; }
        .btn-submit { width: 100%; padding: 14px; margin-top: 26px; background: #FF6B00; color: #fff; border: none; border-radius: 12px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px; cursor: pointer; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.35); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover:not(:disabled) { background: #FF8C38; transform: translateY(-2px); }
        .btn-submit:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }
        .login-link { text-align: center; margin-top: 18px; font-size: 14px; color: rgba(255,255,255,0.32); }
        .login-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        footer { text-align: center; padding: 24px; font-size: 12px; color: rgba(255,255,255,0.22); background: #070e1f; border-top: 1px solid rgba(255,255,255,0.05); }
        footer span { color: #FF6B00; font-weight: 700; }
        @media (max-width: 640px) {
            .grid-2 { grid-template-columns: 1fr; }
            .navbar { padding: 0 16px; }
            .card-body { padding: 22px 16px; }
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

            <?php if ($votacionActiva != '1'): ?>
                <div class="closed-box">
                    <i class="fas fa-lock"></i>
                    <h3>Registro no disponible</h3>
                    <p>El período de registro está cerrado.<br>Las votaciones aún no han sido habilitadas por el administrador.</p>
                    <a href="/yo_voto/" class="btn-volver"><i class="fas fa-home"></i> Volver al inicio</a>
                </div>

            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle" style="font-size:22px;"></i>
                    <div>
                        <strong style="font-size:15px;">¡Registro exitoso!</strong><br>
                        <span style="font-size:13px;opacity:0.85;"><?= $success ?></span><br><br>
                        <a href="/yo_voto/" style="display:inline-flex;align-items:center;gap:8px;background:#FF6B00;color:#fff;padding:10px 22px;border-radius:10px;font-weight:700;text-decoration:none;font-size:14px;">
                            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/yo_voto/registro-ciudadano" id="registroForm" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

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
                        <label><i class="fas fa-id-card"></i> Número de CI <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-id-card"></i>
                            <input type="text" name="carnet" id="carnet" placeholder="Número de CI" maxlength="10" inputmode="numeric" required>
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
                        <label><i class="fas fa-home"></i> Dirección</label>
                        <div class="input-wrap"><i class="ico fas fa-home"></i>
                            <input type="text" name="direccion" placeholder="Ej: Calle Murillo #123, La Paz">
                        </div>
                    </div>

                    <div class="sec-title"><i class="fas fa-envelope"></i> Contacto</div>
                    <div class="form-group" style="margin-bottom:14px;">
                        <label><i class="fas fa-envelope"></i> Correo Electrónico <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="tu@correo.com" required>
                        </div>
                    </div>

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
document.getElementById('carnet')?.addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

const passInput    = document.getElementById('passInput');
const confirmInput = document.getElementById('confirmInput');
const fill         = document.getElementById('strengthFill');
const txt          = document.getElementById('strengthText');
const matchTxt     = document.getElementById('matchText');

if (passInput) {
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
}

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

document.getElementById('registroForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
});
</script>
</body>
</html>