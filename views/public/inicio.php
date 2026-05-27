<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error_login = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yo Voto - Sistema Electoral Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@2.8.6/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }

        /* INTRO SPLASH */
        #intro-splash {
            position: fixed; inset: 0; z-index: 9999;
            background: linear-gradient(160deg, #0a1628 0%, #0d2251 50%, #0a1628 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity .6s ease, visibility .6s ease;
        }
        #intro-splash.oculto { opacity: 0; visibility: hidden; }
        .splash-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 52px; color: #fff; display: flex; align-items: center; gap: 12px; animation: splashEntrada .8s ease forwards; opacity: 0; }
        .splash-logo span { color: #FF6B00; }
        .splash-logo i { color: #FF6B00; font-size: 46px; }
        .splash-sub { font-size: 15px; color: rgba(255,255,255,0.45); margin-top: 14px; letter-spacing: 3px; text-transform: uppercase; animation: splashEntrada .8s ease .2s forwards; opacity: 0; }
        .splash-bar-wrap { width: 220px; height: 3px; background: rgba(255,255,255,0.08); border-radius: 10px; margin-top: 40px; overflow: hidden; animation: splashEntrada .8s ease .3s forwards; opacity: 0; }
        .splash-bar { height: 100%; width: 0%; background: #FF6B00; border-radius: 10px; animation: splashLoad 1.8s ease .5s forwards; }
        .splash-badge { margin-top: 20px; font-size: 11px; color: rgba(255,255,255,0.3); letter-spacing: 1.5px; text-transform: uppercase; animation: splashEntrada .8s ease .4s forwards; opacity: 0; }
        @keyframes splashEntrada { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes splashLoad { from { width:0%; } to { width:100%; } }

        /* NAVBAR */
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: rgba(10,22,50,0.95); backdrop-filter: blur(10px); height: 60px; display: flex; align-items: center; padding: 0 40px; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-logo i { color: #FF6B00; font-size: 18px; }
        .navbar-nav { display: flex; align-items: center; gap: 6px; }
        .navbar-nav a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; display: flex; align-items: center; gap: 6px; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .navbar-nav .btn-registro { background: #FF6B00; color: #fff; padding: 7px 18px; border-radius: 8px; }
        .navbar-nav .btn-registro:hover { background: #FF8C38; }
        .navbar-nav .user-name { color: #FF8C38; font-size: 14px; font-weight: 600; }

        /* HERO */
        .hero { min-height: 100vh; background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 80px 24px 40px; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 80% 60% at 50% 40%, rgba(26,58,122,0.6) 0%, transparent 70%), radial-gradient(ellipse 40% 40% at 20% 80%, rgba(255,107,0,0.08) 0%, transparent 60%), radial-gradient(ellipse 40% 40% at 80% 20%, rgba(255,107,0,0.06) 0%, transparent 60%); pointer-events: none; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35); color: #FF8C38; padding: 7px 20px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 32px; position: relative; z-index: 1; }
        .btn-votar { background: #FF6B00; color: #fff; padding: 14px 32px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 15px; display: inline-flex; align-items: center; gap: 9px; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.4); border: none; cursor: pointer; text-decoration: none; }
        .btn-votar:hover { background: #FF8C38; transform: translateY(-2px); color: #fff; }
        .btn-outline { background: rgba(255,255,255,0.08); color: #fff; padding: 14px 32px; border-radius: 10px; text-decoration: none; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 15px; display: inline-flex; align-items: center; gap: 9px; transition: .25s; border: 1.5px solid rgba(255,255,255,0.2); cursor: pointer; }
        .btn-outline:hover { background: rgba(255,255,255,0.15); transform: translateY(-2px); color: #fff; }

        /* MÓDULOS */
        .modules-section { background: linear-gradient(180deg, #0d2251 0%, #0a1a3e 100%); padding: 60px 24px; }
        .modules-grid { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .module-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 36px 24px; text-align: center; text-decoration: none; transition: .25s; cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 14px; }
        .module-card:hover { background: rgba(255,255,255,0.1); transform: translateY(-4px); border-color: rgba(255,107,0,0.3); box-shadow: 0 12px 32px rgba(0,0,0,0.3); }
        .module-icon-wrap { width: 64px; height: 64px; border-radius: 16px; background: #FF6B00; display: flex; align-items: center; justify-content: center; }
        .module-icon-wrap i { font-size: 26px; color: #fff; }
        .module-name { font-family: 'Montserrat', sans-serif; font-size: 18px; font-weight: 800; color: #fff; }
        .module-desc { font-size: 13px; color: rgba(255,255,255,0.5); }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; inset: 0; z-index: 500; background: rgba(0,10,30,0.85); backdrop-filter: blur(8px); overflow-y: auto; padding: 40px 20px; }
        .modal-box { background: #111e3a; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; max-width: 520px; margin: 0 auto; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.5); animation: slideUp .3s ease; }
        .modal-box-lg { max-width: 820px; }
        @keyframes slideUp { from { opacity:0; transform:translateY(40px); } to { opacity:1; transform:translateY(0); } }
        .modal-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #FF6B00; }
        .modal-head h2 { font-family: 'Montserrat', sans-serif; font-size: 18px; font-weight: 800; color: #fff; }
        .modal-head h2 i { color: #FF6B00; margin-right: 8px; }
        .btn-close-modal { background: rgba(255,255,255,0.1); border: none; color: #fff; width: 32px; height: 32px; border-radius: 8px; font-size: 18px; cursor: pointer; transition: .2s; display: flex; align-items: center; justify-content: center; }
        .btn-close-modal:hover { background: #FF6B00; }
        .modal-body { padding: 28px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.7); margin-bottom: 6px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1.5px solid rgba(255,255,255,0.12); border-radius: 10px; font-size: 14px; background: rgba(255,255,255,0.07); color: #fff; transition: .2s; }
        .form-group input::placeholder { color: rgba(255,255,255,0.3); }
        .form-group input:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.1); }
        .btn-facial { background: rgba(255,255,255,0.1); color: #fff; padding: 10px 22px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.2); font-size: 13px; font-weight: 700; cursor: pointer; transition: .2s; display: inline-flex; align-items: center; gap: 7px; }
        .btn-facial:hover { background: #FF6B00; border-color: #FF6B00; }
        .btn-facial-stop { background: rgba(231,76,60,0.2); border-color: #E74C3C; }
        .btn-facial-stop:hover { background: #E74C3C; }
        .face-preview { width: 100%; max-width: 280px; border-radius: 12px; margin: 12px auto; display: none; border: 3px solid #FF6B00; }
        .face-status { margin-top: 12px; padding: 10px 14px; border-radius: 10px; font-weight: 700; font-size: 13px; display: none; }
        .face-status.success { background: rgba(39,174,96,0.15); color: #5cdb95; border-left: 4px solid #27AE60; }
        .face-status.error { background: rgba(231,76,60,0.15); color: #ff6b6b; border-left: 4px solid #E74C3C; }
        .face-status.info { background: rgba(26,58,122,0.4); color: #7eb3ff; border-left: 4px solid #1976D2; }
        .alert-error { background: rgba(231,76,60,0.15); color: #ff6b6b; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 13px; border-left: 4px solid #E74C3C; display: none; }
        .divider { border: none; height: 1px; background: rgba(255,255,255,0.1); margin: 20px 0; }
        .btn-admin-link { display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); padding: 11px 18px; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 700; border: 1px solid rgba(255,255,255,0.1); transition: .2s; }
        .btn-admin-link:hover { background: #FF6B00; color: #fff; border-color: #FF6B00; }
        .link-registro { text-align: center; margin-top: 14px; font-size: 13px; color: rgba(255,255,255,0.4); }
        .link-registro a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        .modal-candidato-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 16px; display: block; border: 4px solid #FF6B00; }
        .propuesta-item { background: rgba(255,255,255,0.05); padding: 14px; border-radius: 10px; margin-bottom: 10px; border-left: 4px solid #FF6B00; }
        .propuesta-item strong { color: #fff; display: block; margin-bottom: 4px; }
        .propuesta-item p { font-size: 13px; color: rgba(255,255,255,0.5); margin: 0; }
        .equipo-item { background: rgba(255,255,255,0.05); padding: 10px 14px; border-radius: 8px; margin-bottom: 8px; border-left: 3px solid #1976D2; }
        .equipo-item strong { color: #fff; }
        .equipo-item span { color: rgba(255,255,255,0.4); font-size: 12px; }
        .seccion-titulo { font-family: 'Montserrat', sans-serif; font-size: 13px; font-weight: 800; color: #FF6B00; text-transform: uppercase; letter-spacing: 1.5px; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 1px solid rgba(255,107,0,0.3); }
        .loading { text-align: center; padding: 60px; color: rgba(255,255,255,0.3); font-size: 15px; }

        /* RECUPERAR CONTRASEÑA */
        .digit-input {
            width: 44px; height: 52px; text-align: center; font-size: 22px; font-weight: 800;
            border: 1.5px solid rgba(255,255,255,0.15); border-radius: 10px;
            background: rgba(255,255,255,0.07); color: #fff; font-family: monospace;
            transition: .2s;
        }
        .digit-input:focus { outline: none; border-color: #FF6B00; background: rgba(255,107,0,0.1); }

        /* FOOTER */
        footer { background: #070e1f; color: rgba(255,255,255,0.3); text-align: center; padding: 32px; font-size: 14px; border-top: 1px solid rgba(255,255,255,0.06); }
        .footer-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; margin-bottom: 8px; }
        .footer-logo span { color: #FF6B00; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .modules-grid { grid-template-columns: 1fr; }
            .hero h1 { font-size: 40px; }
        }
    </style>
</head>
<body>

<!-- INTRO SPLASH -->
<div id="intro-splash">
    <div class="splash-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></div>
    <div class="splash-sub">Sistema Electoral Bolivia</div>
    <div class="splash-bar-wrap"><div class="splash-bar"></div></div>
    <div class="splash-badge">🗳️ Elecciones Generales 2026</div>
</div>

<!-- NAVBAR -->
<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></a>
    <nav class="navbar-nav">
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] == 'usuario'): ?>
            <span class="user-name"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
            <a href="/yo_voto/mi-perfil"><i class="fas fa-home"></i> Mi Perfil</a>
            <?php if ($_SESSION['user']['ya_voto'] == 0): ?>
                <a href="/yo_voto/votar"><i class="fas fa-vote-yea"></i> Votar</a>
            <?php endif; ?>
            <a href="/yo_voto/logout-votante"><i class="fas fa-sign-out-alt"></i> Salir</a>
        <?php else: ?>
            <a href="#" onclick="mostrarModalLogin()"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
            <a href="/yo_voto/registro" class="btn-registro"><i class="fas fa-user-plus"></i> Registrarse</a>
            <a href="/yo_voto/login">Admin</a>
        <?php endif; ?>
    </nav>
</header>

<!-- HERO -->
<section class="hero">
    <div style="position:relative;z-index:1;width:100%;max-width:1200px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;text-align:left;">
        <div>
            <div class="hero-badge"><i class="fas fa-shield-alt"></i> Elecciones Generales Bolivia 2026</div>
            <h1 style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:clamp(42px,5vw,70px);color:#fff;line-height:1.05;margin-bottom:18px;position:relative;z-index:1;">Tu Voto es<span style="color:#FF6B00;display:block;">tu Voz</span></h1>
            <p style="font-size:17px;color:rgba(255,255,255,0.55);margin-bottom:36px;line-height:1.6;position:relative;z-index:1;">Participa y sé parte de la democracia boliviana. Un sistema seguro, transparente y accesible.</p>
            <div style="display:flex;gap:14px;flex-wrap:wrap;position:relative;z-index:1;">
                <?php if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario'): ?>
                    <button class="btn-votar" onclick="mostrarModalLogin()"><i class="fas fa-vote-yea"></i> Votar Ahora</button>
                <?php elseif ($_SESSION['user']['ya_voto'] == 0): ?>
                    <a href="/yo_voto/votar" class="btn-votar"><i class="fas fa-vote-yea"></i> Votar Ahora</a>
                <?php else: ?>
                    <span class="btn-votar" style="background:#27AE60;cursor:default;"><i class="fas fa-check"></i> Ya Votaste</span>
                <?php endif; ?>
                <a href="/yo_voto/registro" class="btn-outline"><i class="fas fa-user-plus"></i> Registrarse</a>
                <a href="/yo_voto/login" class="btn-outline"><i class="fas fa-lock"></i> Panel Admin</a>
            </div>
        </div>
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div style="font-family:'Montserrat',sans-serif;font-size:15px;font-weight:800;color:#fff;display:flex;align-items:center;gap:8px;position:relative;z-index:1;">
                    <i class="fas fa-users" style="color:#FF6B00;"></i> Candidatos
                </div>
                <div id="candidatos-count" style="background:rgba(255,107,0,0.15);color:#FF8C38;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:700;position:relative;z-index:1;"></div>
            </div>
            <div id="candidatos-grid" style="display:flex;flex-direction:column;gap:12px;max-height:420px;overflow-y:auto;padding-right:4px;position:relative;z-index:1;">
                <div style="text-align:center;padding:40px;color:rgba(255,255,255,0.3);"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            </div>
        </div>
    </div>
</section>

<!-- MÓDULOS -->
<section class="modules-section">
    <div class="modules-grid">
        <div class="module-card" onclick="mostrarModalLogin()">
            <div class="module-icon-wrap"><i class="fas fa-vote-yea"></i></div>
            <div class="module-name">Votar</div>
            <div class="module-desc">Emite tu sufragio</div>
        </div>
        <a href="/yo_voto/resultados" class="module-card">
            <div class="module-icon-wrap"><i class="fas fa-chart-bar"></i></div>
            <div class="module-name">Resultados</div>
            <div class="module-desc">Ver conteo en vivo</div>
        </a>
        <div class="module-card" onclick="window.scrollTo({top:0,behavior:'smooth'})">
            <div class="module-icon-wrap"><i class="fas fa-users"></i></div>
            <div class="module-name">Candidatos</div>
            <div class="module-desc">Conoce a los postulantes</div>
        </div>
    </div>
</section>

<!-- MODAL LOGIN -->
<div id="modalLogin" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="modal-login-titulo"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
            <button class="btn-close-modal" onclick="cerrarModalLogin()">×</button>
        </div>
        <div class="modal-body">

            <!-- PASO 1: LOGIN NORMAL -->
            <div id="paso-login">
                <div id="login-error-message" class="alert-error"></div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><i class="fas fa-id-card"></i> Número de Carnet *</label>
                    <input type="text" id="face-carnet" placeholder="Ej: 12345678" maxlength="10">
                </div>
                <div class="form-group" style="margin-bottom:6px;">
                    <label><i class="fas fa-lock"></i> Contraseña *</label>
                    <input type="password" id="face-password" placeholder="Tu contraseña">
                </div>
                <div style="text-align:right;margin-bottom:14px;">
                    <a href="#" onclick="mostrarRecuperar()" style="color:#FF6B00;font-size:12px;font-weight:700;text-decoration:none;">¿Olvidaste tu contraseña?</a>
                </div>
                <div style="display:flex;justify-content:center;margin-bottom:16px;">
                    <div class="h-captcha" data-sitekey="a22ee458-031e-489e-93ee-1aa2545e7aa2"></div>
                </div>
                <div style="text-align:center;margin-bottom:10px;">
                    <p style="color:rgba(255,255,255,0.4);font-size:12px;margin-bottom:10px;"><i class="fas fa-face-smile" style="color:#FF6B00;"></i> Verificación facial</p>
                    <video id="login-video" class="face-preview" autoplay muted playsinline></video>
                    <div style="margin-top:10px;display:flex;gap:10px;justify-content:center;">
                        <button type="button" id="start-face-login-btn" class="btn-facial" onclick="startFaceLogin()"><i class="fas fa-camera"></i> Activar Cámara y Verificar</button>
                        <button type="button" id="stop-face-login-btn" class="btn-facial btn-facial-stop" onclick="stopFaceLogin()" style="display:none;"><i class="fas fa-stop"></i> Detener</button>
                    </div>
                </div>
                <div id="face-login-status" class="face-status"></div>
                <hr class="divider">
                <a href="/yo_voto/login" class="btn-admin-link"><i class="fas fa-user-shield"></i> ¿Eres Administrador? Haz clic aquí</a>
                <div class="link-registro">¿No tienes cuenta? <a href="/yo_voto/registro">Regístrate aquí</a></div>
            </div>

            <!-- PASO 2: SOLICITAR CÓDIGO -->
            <div id="paso-recuperar" style="display:none;">
                <div style="text-align:center;margin-bottom:20px;">
                    <i class="fas fa-key" style="font-size:40px;color:#FF6B00;display:block;margin-bottom:10px;"></i>
                    <h3 style="color:#fff;font-family:'Montserrat',sans-serif;font-size:16px;margin-bottom:6px;">Recuperar Contraseña</h3>
                    <p style="color:rgba(255,255,255,0.4);font-size:13px;">Ingresa tu CI y correo para recibir un código de verificación.</p>
                </div>
                <div id="recuperar-error" class="alert-error"></div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><i class="fas fa-id-card"></i> Número de Carnet *</label>
                    <input type="text" id="rec-carnet" placeholder="Ej: 12345678" maxlength="10">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico *</label>
                    <input type="email" id="rec-email" placeholder="tu@correo.com">
                </div>
                <button type="button" onclick="enviarCodigoRecuperar()" style="width:100%;padding:13px;background:#FF6B00;color:#fff;border:none;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:15px;font-weight:800;cursor:pointer;">
                    <i class="fas fa-paper-plane"></i> Enviar Código
                </button>
                <div style="text-align:center;margin-top:14px;">
                    <a href="#" onclick="mostrarLogin()" style="color:rgba(255,255,255,0.4);font-size:13px;text-decoration:none;"><i class="fas fa-arrow-left"></i> Volver al login</a>
                </div>
            </div>

            <!-- PASO 3: INGRESAR CÓDIGO -->
            <div id="paso-codigo" style="display:none;">
                <div style="text-align:center;margin-bottom:20px;">
                    <i class="fas fa-envelope-open-text" style="font-size:40px;color:#FF6B00;display:block;margin-bottom:10px;"></i>
                    <h3 style="color:#fff;font-family:'Montserrat',sans-serif;font-size:16px;margin-bottom:6px;">Código Enviado</h3>
                    <p style="color:rgba(255,255,255,0.4);font-size:13px;">Revisa tu correo e ingresa el código de 6 dígitos.</p>
                </div>
                <div id="codigo-error" class="alert-error"></div>
                <div style="display:flex;gap:8px;justify-content:center;margin-bottom:20px;">
                    <input type="text" maxlength="1" class="digit-input" id="c1" inputmode="numeric">
                    <input type="text" maxlength="1" class="digit-input" id="c2" inputmode="numeric">
                    <input type="text" maxlength="1" class="digit-input" id="c3" inputmode="numeric">
                    <input type="text" maxlength="1" class="digit-input" id="c4" inputmode="numeric">
                    <input type="text" maxlength="1" class="digit-input" id="c5" inputmode="numeric">
                    <input type="text" maxlength="1" class="digit-input" id="c6" inputmode="numeric">
                </div>
                <button type="button" onclick="verificarCodigoRecuperar()" style="width:100%;padding:13px;background:#FF6B00;color:#fff;border:none;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:15px;font-weight:800;cursor:pointer;">
                    <i class="fas fa-check-circle"></i> Verificar Código
                </button>
                <div style="text-align:center;margin-top:12px;font-size:12px;color:rgba(255,255,255,0.3);">Expira en <span id="countdown-rec">10:00</span></div>
            </div>

            <!-- PASO 4: NUEVA CONTRASEÑA -->
            <div id="paso-nueva-pass" style="display:none;">
                <div style="text-align:center;margin-bottom:20px;">
                    <i class="fas fa-lock-open" style="font-size:40px;color:#27AE60;display:block;margin-bottom:10px;"></i>
                    <h3 style="color:#fff;font-family:'Montserrat',sans-serif;font-size:16px;margin-bottom:6px;">Nueva Contraseña</h3>
                    <p style="color:rgba(255,255,255,0.4);font-size:13px;">Ingresa tu nueva contraseña.</p>
                </div>
                <div id="pass-error" class="alert-error"></div>
                <div class="form-group" style="margin-bottom:14px;">
                    <label><i class="fas fa-lock"></i> Nueva Contraseña *</label>
                    <input type="password" id="nueva-pass" placeholder="Mín. 6 caracteres">
                </div>
                <div class="form-group" style="margin-bottom:20px;">
                    <label><i class="fas fa-check-double"></i> Confirmar Contraseña *</label>
                    <input type="password" id="confirmar-pass" placeholder="Repite la contraseña">
                </div>
                <button type="button" onclick="guardarNuevaPassword()" style="width:100%;padding:13px;background:#27AE60;color:#fff;border:none;border-radius:10px;font-family:'Montserrat',sans-serif;font-size:15px;font-weight:800;cursor:pointer;">
                    <i class="fas fa-save"></i> Guardar Contraseña
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL CANDIDATO -->
<div id="modalCandidato" class="modal-overlay">
    <div class="modal-box modal-box-lg">
        <div class="modal-head">
            <h2 id="modal-titulo"><i class="fas fa-user"></i> Candidato</h2>
            <button class="btn-close-modal" onclick="cerrarModal()">×</button>
        </div>
        <div class="modal-body" id="modal-body"><div class="loading">Cargando...</div></div>
    </div>
</div>

<!-- SECCIÓN INFORMATIVA -->
<section style="background: linear-gradient(180deg, #0a1628 0%, #070e1f 100%); padding: 70px 24px;">
    <div style="max-width:1100px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:52px;">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,107,0,0.12);border:1px solid rgba(255,107,0,0.3);color:#FF8C38;padding:6px 18px;border-radius:50px;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:18px;">
                <i class="fas fa-info-circle"></i> Información Electoral
            </div>
            <h2 style="font-family:'Montserrat',sans-serif;font-weight:900;font-size:34px;color:#fff;margin-bottom:12px;">¿Cómo funciona <span style="color:#FF6B00;">Yo Voto</span>?</h2>
            <p style="color:rgba(255,255,255,0.45);font-size:16px;max-width:560px;margin:0 auto;">Un sistema electoral digital seguro, transparente y accesible para todos los ciudadanos bolivianos.</p>
        </div>

        <!-- PASOS -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;margin-bottom:60px;">
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:28px 22px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#FF6B00,#FF8C38);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-user-plus" style="font-size:22px;color:#fff;"></i>
                </div>
                <div style="font-family:'Montserrat',sans-serif;font-size:13px;font-weight:700;color:#FF8C38;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">Paso 1</div>
                <div style="font-family:'Montserrat',sans-serif;font-size:17px;font-weight:800;color:#fff;margin-bottom:8px;">Regístrate</div>
                <p style="font-size:13px;color:rgba(255,255,255,0.45);line-height:1.6;">Crea tu cuenta con tu CI y registra tu rostro para verificación biométrica.</p>
            </div>
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:28px 22px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#FF6B00,#FF8C38);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-face-smile" style="font-size:22px;color:#fff;"></i>
                </div>
                <div style="font-family:'Montserrat',sans-serif;font-size:13px;font-weight:700;color:#FF8C38;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">Paso 2</div>
                <div style="font-family:'Montserrat',sans-serif;font-size:17px;font-weight:800;color:#fff;margin-bottom:8px;">Verifica tu identidad</div>
                <p style="font-size:13px;color:rgba(255,255,255,0.45);line-height:1.6;">Inicia sesión con tu carnet, contraseña y reconocimiento facial seguro.</p>
            </div>
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:28px 22px;text-align:center;">
                <div style="width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#FF6B00,#FF8C38);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                    <i class="fas fa-vote-yea" style="font-size:22px;color:#fff;"></i>
                </div>
                <div style="font-family:'Montserrat',sans-serif;font-size:13px;font-weight:700;color:#FF8C38;letter-spacing:1px;text-transform:uppercase;margin-bottom:8px;">Paso 3</div>
                <div style="font-family:'Montserrat',sans-serif;font-size:17px;font-weight:800;color:#fff;margin-bottom:8px;">Emite tu voto</div>
                <p style="font-size:13px;color:rgba(255,255,255,0.45);line-height:1.6;">Elige a tu candidato preferido. Tu voto es secreto, único e irrepetible.</p>
            </div>
        </div>

        <!-- GARANTÍAS -->
        <div style="background:rgba(255,107,0,0.06);border:1px solid rgba(255,107,0,0.15);border-radius:20px;padding:40px;margin-bottom:60px;">
            <h3 style="font-family:'Montserrat',sans-serif;font-size:22px;font-weight:800;color:#fff;text-align:center;margin-bottom:32px;"><i class="fas fa-shield-alt" style="color:#FF6B00;margin-right:10px;"></i> Garantías del Sistema</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,107,0,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-lock" style="color:#FF6B00;font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;color:#fff;font-size:14px;margin-bottom:4px;">Voto Secreto</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.5;">Nadie puede saber por quién votaste.</div>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,107,0,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-camera" style="color:#FF6B00;font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;color:#fff;font-size:14px;margin-bottom:4px;">Un voto por persona</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.5;">Verificación con foto registrada evita duplicados.</div>
                    </div>
                </div>
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,107,0,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-chart-bar" style="color:#FF6B00;font-size:15px;"></i>
                    </div>
                    <div>
                        <div style="font-family:'Montserrat',sans-serif;font-weight:700;color:#fff;font-size:14px;margin-bottom:4px;">Resultados en tiempo real</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.5;">Conteo transparente y actualizado.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div style="text-align:center;">
            <h3 style="font-family:'Montserrat',sans-serif;font-size:26px;font-weight:900;color:#fff;margin-bottom:12px;">¿Listo para participar?</h3>
            <p style="color:rgba(255,255,255,0.45);margin-bottom:28px;">Únete a miles de ciudadanos bolivianos que ya están registrados.</p>
            <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
                <a href="/yo_voto/registro" style="background:#FF6B00;color:#fff;padding:14px 32px;border-radius:10px;font-family:'Montserrat',sans-serif;font-weight:800;font-size:15px;text-decoration:none;display:inline-flex;align-items:center;gap:9px;box-shadow:0 6px 24px rgba(255,107,0,0.35);">
                    <i class="fas fa-user-plus"></i> Crear mi cuenta
                </a>
                <a href="/yo_voto/resultados" style="background:rgba(255,255,255,0.08);color:#fff;padding:14px 32px;border-radius:10px;font-family:'Montserrat',sans-serif;font-weight:700;font-size:15px;text-decoration:none;display:inline-flex;align-items:center;gap:9px;border:1.5px solid rgba(255,255,255,0.2);">
                    <i class="fas fa-chart-bar"></i> Ver resultados
                </a>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-logo">🗳️ Yo <span>Voto</span></div>
    <p>Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p>
</footer>

<script>
    let loginStream = null, faceModelsLoaded = false, recognitionInterval = null, countdownTimer = null;

    // ── RECUPERAR CONTRASEÑA ──
    function mostrarRecuperar() {
        document.getElementById('paso-login').style.display = 'none';
        document.getElementById('paso-recuperar').style.display = 'block';
        document.getElementById('modal-login-titulo').innerHTML = '<i class="fas fa-key"></i> Recuperar Contraseña';
    }
    function mostrarLogin() {
        ['paso-recuperar','paso-codigo','paso-nueva-pass'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        document.getElementById('paso-login').style.display = 'block';
        document.getElementById('modal-login-titulo').innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
        if (countdownTimer) clearInterval(countdownTimer);
    }

    async function enviarCodigoRecuperar() {
        const carnet = document.getElementById('rec-carnet').value.trim();
        const email  = document.getElementById('rec-email').value.trim();
        const errDiv = document.getElementById('recuperar-error');
        errDiv.style.display = 'none';
        if (!carnet || !email) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Completa todos los campos.'; return; }
        try {
            const res = await fetch('/yo_voto/api/recuperar-password', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ carnet, email })
            });
            const r = await res.json();
            if (r.success) {
                document.getElementById('paso-recuperar').style.display = 'none';
                document.getElementById('paso-codigo').style.display = 'block';
                document.getElementById('modal-login-titulo').innerHTML = '<i class="fas fa-envelope-open-text"></i> Verificar Código';
                iniciarCountdown();
                document.getElementById('c1').focus();
                if (r.dev_codigo) console.log('🔑 Código de prueba:', r.dev_codigo);
            } else {
                errDiv.style.display = 'block'; errDiv.innerHTML = '❌ ' + r.error;
            }
        } catch(e) { errDiv.style.display = 'block'; errDiv.innerHTML = '❌ Error de conexión.'; }
    }

    async function verificarCodigoRecuperar() {
        const codigo = ['c1','c2','c3','c4','c5','c6'].map(id => document.getElementById(id)?.value || '').join('');
        const errDiv = document.getElementById('codigo-error');
        errDiv.style.display = 'none';
        if (codigo.length !== 6) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Ingresa los 6 dígitos.'; return; }
        try {
            const res = await fetch('/yo_voto/api/verificar-reset', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ codigo })
            });
            const r = await res.json();
            if (r.success) {
                document.getElementById('paso-codigo').style.display = 'none';
                document.getElementById('paso-nueva-pass').style.display = 'block';
                document.getElementById('modal-login-titulo').innerHTML = '<i class="fas fa-lock-open"></i> Nueva Contraseña';
                if (countdownTimer) clearInterval(countdownTimer);
            } else {
                errDiv.style.display = 'block'; errDiv.innerHTML = '❌ ' + r.error;
            }
        } catch(e) { errDiv.style.display = 'block'; errDiv.innerHTML = '❌ Error de conexión.'; }
    }

    async function guardarNuevaPassword() {
        const password = document.getElementById('nueva-pass').value;
        const confirm  = document.getElementById('confirmar-pass').value;
        const errDiv   = document.getElementById('pass-error');
        errDiv.style.display = 'none';
        if (password.length < 6) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Mínimo 6 caracteres.'; return; }
        if (password !== confirm) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Las contraseñas no coinciden.'; return; }
        try {
            const res = await fetch('/yo_voto/api/nueva-password', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ password, confirm })
            });
            const r = await res.json();
            if (r.success) {
                document.getElementById('paso-nueva-pass').innerHTML = `
                    <div style="text-align:center;padding:20px;">
                        <i class="fas fa-check-circle" style="font-size:50px;color:#27AE60;display:block;margin-bottom:16px;"></i>
                        <h3 style="color:#fff;font-family:'Montserrat',sans-serif;margin-bottom:8px;">¡Contraseña actualizada!</h3>
                        <p style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:20px;">Ya puedes iniciar sesión.</p>
                        <button onclick="mostrarLogin()" style="background:#FF6B00;color:#fff;padding:10px 24px;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-family:'Montserrat',sans-serif;">Ir al Login</button>
                    </div>`;
            } else {
                errDiv.style.display = 'block'; errDiv.innerHTML = '❌ ' + r.error;
            }
        } catch(e) { errDiv.style.display = 'block'; errDiv.innerHTML = '❌ Error de conexión.'; }
    }

    function iniciarCountdown() {
        let seg = 600;
        const el = document.getElementById('countdown-rec');
        if (countdownTimer) clearInterval(countdownTimer);
        countdownTimer = setInterval(() => {
            seg--;
            const m = Math.floor(seg / 60), s = seg % 60;
            if (el) el.textContent = m + ':' + String(s).padStart(2,'0');
            if (seg <= 0) { clearInterval(countdownTimer); if (el) el.style.color = '#E74C3C'; }
        }, 1000);
    }

    // Auto-avanzar inputs del código
    document.addEventListener('DOMContentLoaded', () => {
        ['c1','c2','c3','c4','c5','c6'].forEach((id, i, arr) => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', () => {
                el.value = el.value.replace(/[^0-9]/g, '');
                if (el.value && i < arr.length - 1) document.getElementById(arr[i+1]).focus();
            });
            el.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !el.value && i > 0) document.getElementById(arr[i-1]).focus();
            });
        });
    });

    async function waitForFaceAPI() {
        return new Promise(resolve => {
            let checks = 0;
            const iv = setInterval(() => {
                checks++;
                if (typeof faceapi !== 'undefined' && faceapi.nets) { clearInterval(iv); resolve(true); }
                else if (checks > 30) { clearInterval(iv); resolve(false); }
            }, 500);
        });
    }

    async function loadFaceModels() {
        const s = document.getElementById('face-login-status');
        s.style.display = 'block'; s.className = 'face-status info';
        s.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inicializando sistema facial...';
        const ok = await waitForFaceAPI();
        if (!ok) { s.className = 'face-status error'; s.innerHTML = '❌ No se pudo cargar FaceAPI'; return; }
        try {
            await tf.setBackend('cpu');
            await tf.ready();
            const M = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
            s.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando modelos...';
            await faceapi.nets.ssdMobilenetv1.loadFromUri(M);
            await faceapi.nets.faceLandmark68Net.loadFromUri(M);
            await faceapi.nets.faceRecognitionNet.loadFromUri(M);
            faceModelsLoaded = true;
            s.className = 'face-status success'; s.innerHTML = '✅ Sistema listo';
            setTimeout(() => s.style.display = 'none', 2000);
        } catch (e) { s.className = 'face-status error'; s.innerHTML = '❌ Error: ' + e.message; }
    }

    async function startFaceLogin() {
        const carnet = document.getElementById('face-carnet').value;
        const password = document.getElementById('face-password').value;
        const errDiv = document.getElementById('login-error-message');
        errDiv.style.display = 'none';
        if (!carnet || carnet.length < 5) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Ingrese un carnet válido'; return; }
        if (!password) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Ingrese su contraseña'; return; }
        const hcaptchaResponse = document.querySelector('[name="h-captcha-response"]');
        if (!hcaptchaResponse || !hcaptchaResponse.value) { errDiv.style.display = 'block'; errDiv.innerHTML = '⚠️ Complete el captcha de seguridad'; return; }
        if (!faceModelsLoaded) await loadFaceModels();
        if (!faceModelsLoaded) return;
        try {
            loginStream = await navigator.mediaDevices.getUserMedia({ video: true });
            const v = document.getElementById('login-video');
            v.srcObject = loginStream; v.style.display = 'block';
            document.getElementById('start-face-login-btn').style.display = 'none';
            document.getElementById('stop-face-login-btn').style.display = 'inline-flex';
            const s = document.getElementById('face-login-status');
            s.style.display = 'block'; s.className = 'face-status info'; s.innerHTML = '🔍 Mire a la cámara...';
            startFaceRecognition(carnet, password, hcaptchaResponse.value);
        } catch (e) { errDiv.style.display = 'block'; errDiv.innerHTML = '❌ Error de cámara: ' + e.message; }
    }

    async function startFaceRecognition(carnet, password, hcaptcha) {
        const v = document.getElementById('login-video');
        const s = document.getElementById('face-login-status');
        if (recognitionInterval) clearInterval(recognitionInterval);
        recognitionInterval = setInterval(async () => {
            try {
                const det = await faceapi.detectSingleFace(v).withFaceLandmarks().withFaceDescriptor();
                if (det) {
                    s.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
                    const res = await fetch('/yo_voto/api/face/login', {
                        method: 'POST', headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ carnet, password, hcaptcha, descriptor: Array.from(det.descriptor) })
                    });
                    const r = await res.json();
                    if (r.success) {
                        s.className = 'face-status success'; s.innerHTML = '✅ ¡Identificado! Redirigiendo...';
                        clearInterval(recognitionInterval);
                        setTimeout(() => window.location.href = '/yo_voto/mi-perfil', 1500);
                    } else {
                        s.className = 'face-status error'; s.innerHTML = '❌ ' + (r.error || 'Verificación fallida');
                    }
                }
            } catch (e) { s.className = 'face-status error'; s.innerHTML = '❌ ' + e.message; }
        }, 2000);
    }

    function stopFaceLogin() {
        if (recognitionInterval) clearInterval(recognitionInterval);
        if (loginStream) loginStream.getTracks().forEach(t => t.stop());
        const v = document.getElementById('login-video');
        if (v) { v.srcObject = null; v.style.display = 'none'; }
        document.getElementById('start-face-login-btn').style.display = 'inline-flex';
        document.getElementById('stop-face-login-btn').style.display = 'none';
    }

    function mostrarModalLogin() {
        document.getElementById('modalLogin').style.display = 'block';
        document.getElementById('face-carnet').value = '';
        document.getElementById('face-password').value = '';
        stopFaceLogin();
        if (typeof hcaptcha !== 'undefined') hcaptcha.reset();
    }
    function cerrarModalLogin() { document.getElementById('modalLogin').style.display = 'none'; stopFaceLogin(); }
    function cerrarModal() { document.getElementById('modalCandidato').style.display = 'none'; }
    function escapeHtml(t) { if (!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

    async function cargarCandidatos() {
        const g = document.getElementById('candidatos-grid');
        try {
            const res = await fetch('/yo_voto/api/candidatos');
            const data = await res.json();
            if (!data.length) {
                g.innerHTML = '<div style="text-align:center;padding:30px;color:rgba(255,255,255,0.3);"><i class="fas fa-users" style="font-size:36px;display:block;margin-bottom:12px;color:rgba(255,107,0,0.3);"></i><p style="font-size:13px;">Aún no hay candidatos registrados.</p></div>';
                return;
            }
            document.getElementById('candidatos-count').textContent = data.length + ' registrados';
            g.innerHTML = data.map(c => `
                <div onclick="verCandidato(${c.id_candidato})" style="display:flex;align-items:center;gap:14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:14px;padding:14px 16px;cursor:pointer;transition:.2s;" onmouseover="this.style.borderColor='#FF6B00';this.style.background='rgba(255,107,0,0.08)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.08)';this.style.background='rgba(255,255,255,0.05)'">
                    <img src="/yo_voto/${c.foto_url}" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid #FF6B00;flex-shrink:0;">
                    <div style="flex:1;text-align:left;">
                        <div style="font-family:'Montserrat',sans-serif;font-weight:800;color:#fff;font-size:14px;">${escapeHtml(c.nombre)}</div>
                        <div style="color:#FF8C38;font-size:12px;">${escapeHtml(c.partido)}</div>
                        <div style="color:rgba(255,255,255,0.35);font-size:11px;">Candidato a ${escapeHtml(c.cargo)}</div>
                    </div>
                    <i class="fas fa-chevron-right" style="color:rgba(255,255,255,0.2);font-size:12px;"></i>
                </div>
            `).join('');
        } catch(e) {
            g.innerHTML = '<div style="text-align:center;padding:30px;color:rgba(255,255,255,0.3);"><i class="fas fa-wifi" style="font-size:32px;display:block;margin-bottom:10px;color:rgba(255,107,0,0.3);"></i><p style="font-size:13px;">No se pudo conectar con el servidor.</p></div>';
        }
    }

    async function verCandidato(id) {
        document.getElementById('modalCandidato').style.display = 'block';
        document.getElementById('modal-body').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
        try {
            const [c, eq, prop] = await Promise.all([
                fetch(`/yo_voto/api/candidato/${id}`).then(r=>r.json()),
                fetch(`/yo_voto/api/equipo/${id}`).then(r=>r.json()),
                fetch(`/yo_voto/api/propuestas/${id}`).then(r=>r.json())
            ]);
            document.getElementById('modal-titulo').innerHTML = `<i class="fas fa-user"></i> ${escapeHtml(c.nombre)}`;
            let eqHtml = '<p style="color:rgba(255,255,255,0.3);font-size:13px;">Sin equipo registrado.</p>';
            if (eq && Object.keys(eq).length) {
                eqHtml = Object.entries(eq).map(([nivel, ints]) => `
                    <div style="margin-bottom:12px;"><strong style="color:#FF8C38;font-size:11px;text-transform:uppercase;letter-spacing:1px;">Nivel ${nivel}</strong>
                    ${ints.map(i=>`<div class="equipo-item"><strong>${escapeHtml(i.nombre)}</strong> <span>· ${escapeHtml(i.cargo)}</span></div>`).join('')}</div>
                `).join('');
            }
            let propHtml = '<p style="color:rgba(255,255,255,0.3);font-size:13px;">Sin propuestas registradas.</p>';
            if (prop && prop.length) {
                propHtml = prop.map(p=>`<div class="propuesta-item"><strong>${escapeHtml(p.titulo)}</strong><p>${escapeHtml(p.descripcion)}</p></div>`).join('');
            }
            document.getElementById('modal-body').innerHTML = `
                <img src="/yo_voto/${c.foto_url}" class="modal-candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                <h3 style="text-align:center;color:#fff;font-family:'Montserrat',sans-serif;margin-bottom:4px;">${escapeHtml(c.nombre)}</h3>
                <p style="text-align:center;color:#FF8C38;font-size:14px;margin-bottom:4px;">${escapeHtml(c.partido)}</p>
                <p style="text-align:center;color:rgba(255,255,255,0.4);font-size:12px;margin-bottom:16px;">Candidato a ${escapeHtml(c.cargo)}</p>
                ${c.biografia ? `<p style="font-size:14px;color:rgba(255,255,255,0.5);margin-bottom:16px;">${escapeHtml(c.biografia)}</p>` : ''}
                <div class="seccion-titulo"><i class="fas fa-users"></i> Equipo</div>${eqHtml}
                <div class="seccion-titulo"><i class="fas fa-list-check"></i> Propuestas</div>${propHtml}
            `;
        } catch(e) { document.getElementById('modal-body').innerHTML = '<p style="color:#ff6b6b;">Error al cargar los datos.</p>'; }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            const splash = document.getElementById('intro-splash');
            splash.classList.add('oculto');
            setTimeout(() => splash.remove(), 700);
        }, 2500);
        cargarCandidatos();
        loadFaceModels();
    });

    window.onclick = e => {
        if (e.target === document.getElementById('modalCandidato')) cerrarModal();
        if (e.target === document.getElementById('modalLogin')) cerrarModalLogin();
    };

    <?php if ($error_login): ?>
    document.addEventListener('DOMContentLoaded', () => {
        mostrarModalLogin();
        const e = document.getElementById('login-error-message');
        e.innerHTML = '<?= addslashes($error_login) ?>';
        e.style.display = 'block';
    });
    <?php endif; ?>
</script>
<!-- ============================================ -->
<!-- CHATBOT CON FIREBASE REALTIME DATABASE      -->
<!-- ============================================ -->
<style>
    #chatbot-widget {
        position: fixed; bottom: 20px; right: 20px; z-index: 9999;
        font-family: 'Open Sans', sans-serif;
    }
    #chatbot-toggle {
        background: #FF6B00; color: white; border: none; border-radius: 50%;
        width: 60px; height: 60px; font-size: 24px; cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: transform 0.3s;
    }
    #chatbot-toggle:hover { transform: scale(1.1); }
    
    #chatbot-window {
        display: none; width: 360px; height: 500px; background: #0d2251;
        border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.3);
        flex-direction: column; overflow: hidden;
        position: absolute; bottom: 80px; right: 0;
        border: 1px solid rgba(255,255,255,0.1);
    }
    #chatbot-header {
        background: #FF6B00; color: white; padding: 15px; font-weight: bold;
        display: flex; justify-content: space-between; align-items: center;
    }
    #chatbot-close { background: none; border: none; color: white; cursor: pointer; font-size: 18px; }
    
    #chatbot-messages {
        flex: 1; padding: 15px; overflow-y: auto; background: #0a1628;
        display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth;
    }
    .msg-bubble { padding: 10px 14px; border-radius: 15px; max-width: 85%; font-size: 13px; line-height: 1.5; word-wrap: break-word; }
    .msg-user { background: #FF6B00; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
    .msg-bot { background: #1a2a4a; color: #e2e8f0; align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid rgba(255,107,0,0.3); }
    
    .quick-buttons {
        padding: 12px; background: #0d2251; border-top: 1px solid rgba(255,255,255,0.1);
        display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
    }
    .chat-btn-option {
        background: rgba(255,107,0,0.1); border: 1px solid rgba(255,107,0,0.3); color: #FF8C38;
        padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all 0.2s ease;
    }
    .chat-btn-option:hover { background: #FF6B00; color: white; transform: translateY(-2px); }
</style>

<div id="chatbot-widget">
    <button id="chatbot-toggle"><i class="fas fa-headset"></i></button>
    <div id="chatbot-window">
        <div id="chatbot-header">
            <span>🤖 Soporte Yo Voto</span>
            <button id="chatbot-close"><i class="fas fa-times"></i></button>
        </div>
        <div id="chatbot-messages">
            <div class="msg-bubble msg-bot">👋 ¡Hola! Soy el asistente virtual de Yo Voto. ¿En qué puedo ayudarte?</div>
        </div>
        <div class="quick-buttons">
            <button class="chat-btn-option" data-opcion="como_votar">🗳️ ¿Cómo votar?</button>
            <button class="chat-btn-option" data-opcion="candidatos">👥 Candidatos</button>
            <button class="chat-btn-option" data-opcion="blockchain">🔒 Blockchain</button>
            <button class="chat-btn-option" data-opcion="reconocimiento_facial">👤 Facial</button>
            <button class="chat-btn-option" data-opcion="habilitacion">⏳ Habilitación</button>
        </div>
    </div>
</div>

<!-- Firebase SDKs -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>

<script>
// CHATBOT CON FIREBASE REALTIME DATABASE
document.addEventListener('DOMContentLoaded', function() {
    const windowEl = document.getElementById('chatbot-window');
    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const messagesEl = document.getElementById('chatbot-messages');
    const optionButtons = document.querySelectorAll('.chat-btn-option');
    
    // Configuración de Firebase (Realtime Database)
    const firebaseConfig = {
        apiKey: "AIzaSyC_0G2wLZF_m0bYRpuBXVsMNbbwr_F1rPw",
        authDomain: "yo-voto-chat.firebaseapp.com",
        projectId: "yo-voto-chat",
        storageBucket: "yo-voto-chat.firebasestorage.app",
        messagingSenderId: "840603390342",
        appId: "1:840603390342:web:b2ca302b5e78f4995edc07",
        databaseURL: "https://yo-voto-chat-default-rtdb.firebaseio.com/"  // ⚠️ CAMBIA ESTO POR TU URL REAL
    };
    
    // Inicializar Firebase
    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    const database = firebase.database();
    
    // Identificador único del chat (por usuario o sesión)
    const chatUserId = "<?php echo isset($_SESSION['user']['id']) ? 'user_' . $_SESSION['user']['id'] : 'guest_' . uniqid(); ?>";
    const chatRef = database.ref('chats/' + chatUserId);
    
    // Escuchar nuevos mensajes en tiempo real
    chatRef.on('child_added', (snapshot) => {
        const msg = snapshot.val();
        appendMessageUI(msg.texto, msg.emisor);
        scrollToBottom();
    });
    
    // Función para agregar mensaje al DOM
    function appendMessageUI(text, emisor) {
        const div = document.createElement('div');
        div.className = 'msg-bubble ' + (emisor === 'user' ? 'msg-user' : 'msg-bot');
        div.innerHTML = text;
        messagesEl.appendChild(div);
    }
    
    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    
    // Función para guardar mensaje en Firebase
    async function saveMessage(text, emisor) {
        const newMsgRef = chatRef.push();
        await newMsgRef.set({
            texto: text,
            emisor: emisor,
            timestamp: firebase.database.ServerValue.TIMESTAMP
        });
    }
    
    // Función para enviar consulta al bot (backend PHP)
    async function askBot(opcion) {
        const btn = document.querySelector(`.chat-btn-option[data-opcion="${opcion}"]`);
        const displayText = btn ? btn.innerText : opcion;
        
        // Guardar mensaje del usuario
        await saveMessage(displayText, 'user');
        
        // Indicador de escritura
        const loadingId = Date.now();
        const tempMsgRef = chatRef.push();
        await tempMsgRef.set({
            texto: '<i class="fas fa-spinner fa-spin"></i> Pensando...',
            emisor: 'bot',
            temporal: true
        });
        
        try {
            const response = await fetch('/yo_voto/api/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ opcion: opcion })
            });
            const data = await response.json();
            
            // Eliminar mensaje temporal
            await tempMsgRef.remove();
            
            if (data.respuesta) {
                await saveMessage(data.respuesta, 'bot');
            } else {
                await saveMessage('❌ No se pudo obtener respuesta. Intenta de nuevo.', 'bot');
            }
        } catch(error) {
            await tempMsgRef.remove();
            await saveMessage('❌ Error de conexión. Verifica tu internet.', 'bot');
            console.error('Chatbot error:', error);
        }
    }
    
    // Asignar eventos a los botones
    optionButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const opcion = this.getAttribute('data-opcion');
            askBot(opcion);
        });
    });
    
    // Abrir/cerrar chat
    toggleBtn.addEventListener('click', () => {
        const isVisible = windowEl.style.display === 'flex';
        windowEl.style.display = isVisible ? 'none' : 'flex';
    });
    closeBtn.addEventListener('click', () => {
        windowEl.style.display = 'none';
    });
});
</script>
</script>

</body>
</html>