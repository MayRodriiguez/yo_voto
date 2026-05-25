<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_SESSION['error_registro'] ?? null;
$success = $_SESSION['success_registro'] ?? null;
unset($_SESSION['error_registro'], $_SESSION['success_registro']);

$extensiones = ['LP','OR','CB','PT','CH','TJ','SC','BE','PD'];
$departamentos = ['Beni','Chuquisaca','Cochabamba','La Paz','Oruro','Pando','Potosí','Santa Cruz','Tarija'];
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
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@3.21.0/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }

        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(10,22,50,0.97); backdrop-filter: blur(10px);
            height: 60px; display: flex; align-items: center;
            padding: 0 40px; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-nav { display: flex; align-items: center; gap: 6px; }
        .navbar-nav a { color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }

        /* MAIN */
        .main {
            min-height: 100vh;
            background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%);
            padding: 90px 24px 60px;
            display: flex; flex-direction: column; align-items: center;
            position: relative;
        }
        .main::before {
            content: ''; position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 36px 36px;
        }

        /* HERO TEXT */
        .hero-text { text-align: center; margin-bottom: 32px; position: relative; z-index: 1; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35);
            color: #FF8C38; padding: 6px 18px; border-radius: 50px;
            font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            margin-bottom: 18px;
        }
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 40px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }

        /* CARD */
        .card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; width: 100%; max-width: 760px;
            overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4);
            position: relative; z-index: 1;
        }
        .card-head {
            background: linear-gradient(135deg, #0d2251, #1a3a7a);
            border-bottom: 2px solid #FF6B00;
            padding: 22px 28px; display: flex; align-items: center; gap: 16px;
        }
        .card-head-icon { width: 46px; height: 46px; border-radius: 12px; background: #FF6B00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-head-icon i { font-size: 20px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 18px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 3px 0 0; }
        .card-body { padding: 30px 28px; }

        /* ALERTS */
        .alert { padding: 14px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 10px; }
        .alert-success { background: rgba(39,174,96,0.12); color: #5cdb95; border-left: 4px solid #27AE60; }
        .alert-danger { background: rgba(231,76,60,0.12); color: #ff6b6b; border-left: 4px solid #E74C3C; }
        .alert a { color: #FF6B00; font-weight: 700; text-decoration: none; }

        /* SECTION TITLE */
        .sec-title {
            font-size: 11px; font-weight: 700; color: #FF6B00;
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 16px; margin-top: 26px;
            padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25);
            display: flex; align-items: center; gap: 8px;
        }
        .sec-title:first-child { margin-top: 0; }

        /* FORM */
        .form-grid { display: grid; gap: 14px; }
        .grid-2 { grid-template-columns: 1fr 1fr; }
        .grid-3 { grid-template-columns: 1fr 1fr 1fr; }
        .grid-ci { grid-template-columns: 1fr 100px; }

        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group label { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.65); display: flex; align-items: center; gap: 6px; }
        .form-group label .req { color: #FF6B00; font-size: 14px; }
        .input-wrap { position: relative; }
        .input-wrap .ico { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.22); font-size: 13px; pointer-events: none; }
        .form-group input,
        .form-group select {
            width: 100%; padding: 11px 12px 11px 34px;
            border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px;
            font-size: 14px; background: rgba(255,255,255,0.06);
            color: #fff; transition: .2s; font-family: inherit; appearance: none;
        }
        .form-group select { cursor: pointer; }
        .form-group input::placeholder { color: rgba(255,255,255,0.22); }
        .form-group input:focus,
        .form-group select:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.09); box-shadow: 0 0 0 3px rgba(255,107,0,0.1); }
        .form-group small { font-size: 11px; color: rgba(255,255,255,0.28); }
        .form-group select option { background: #0d2251; color: #fff; }
        .no-icon { padding-left: 12px !important; }

        /* GÉNERO PILLS */
        .genero-pills { display: flex; gap: 10px; }
        .genero-pill { flex: 1; }
        .genero-pill input[type=radio] { display: none; }
        .genero-pill label {
            display: flex; align-items: center; justify-content: center; gap: 7px;
            padding: 11px 8px; border-radius: 10px;
            border: 1.5px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04); cursor: pointer;
            font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.45); transition: .2s;
        }
        .genero-pill input:checked + label { border-color: #FF6B00; background: rgba(255,107,0,0.12); color: #FF8C38; }
        .genero-pill label:hover { border-color: rgba(255,107,0,0.35); color: rgba(255,255,255,0.75); }

        /* STRENGTH */
        .strength-bar { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.07); overflow: hidden; margin-top: 6px; }
        .strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: .3s; }
        .strength-text { font-size: 11px; color: rgba(255,255,255,0.28); margin-top: 4px; }

        /* FACIAL */
        .biometric-box {
            background: rgba(255,255,255,0.03);
            border: 2px dashed rgba(255,107,0,0.3);
            border-radius: 16px; padding: 28px; text-align: center;
        }
        .biometric-box > i.big { font-size: 48px; color: rgba(255,107,0,0.45); display: block; margin-bottom: 10px; }
        .biometric-box > p { color: rgba(255,255,255,0.38); font-size: 14px; margin-bottom: 18px; }
        .tips-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 16px; }
        .tip { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.04); border-radius: 8px; padding: 8px 12px; font-size: 12px; color: rgba(255,255,255,0.6); }
        .tip-ico { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; }
        .tip-ico.ok { background: rgba(39,174,96,0.2); color: #27AE60; }
        .tip-ico.no { background: rgba(231,76,60,0.2); color: #E74C3C; }
        .face-preview { width: 100%; max-width: 320px; border-radius: 12px; margin: 12px auto; display: none; border: 3px solid #FF6B00; }
        .btn-facial { background: rgba(255,255,255,0.07); color: #fff; padding: 10px 18px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.14); font-size: 13px; font-weight: 700; cursor: pointer; transition: .2s; display: inline-flex; align-items: center; gap: 7px; margin: 4px; }
        .btn-facial:hover { background: #FF6B00; border-color: #FF6B00; }
        .btn-facial-stop { background: rgba(231,76,60,0.12); border-color: rgba(231,76,60,0.35); }
        .btn-facial-stop:hover { background: #E74C3C; border-color: #E74C3C; }
        .btn-facial-capture { background: rgba(39,174,96,0.12); border-color: rgba(39,174,96,0.35); }
        .btn-facial-capture:hover { background: #27AE60; border-color: #27AE60; }
        .face-status { margin-top: 14px; padding: 10px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; display: none; }
        .face-status.success { background: rgba(39,174,96,0.12); color: #5cdb95; border-left: 4px solid #27AE60; }
        .face-status.error { background: rgba(231,76,60,0.12); color: #ff6b6b; border-left: 4px solid #E74C3C; }
        .face-status.info { background: rgba(25,118,210,0.12); color: #7eb3ff; border-left: 4px solid #1976D2; }

        /* SUBMIT */
        .btn-submit {
            width: 100%; padding: 14px; margin-top: 26px;
            background: #FF6B00; color: #fff; border: none; border-radius: 12px;
            font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px;
            cursor: pointer; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.35);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-submit:hover:not(:disabled) { background: #FF8C38; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(255,107,0,0.5); }
        .btn-submit:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }
        .btn-note { text-align: center; margin-top: 10px; font-size: 12px; color: rgba(255,255,255,0.28); }
        .login-link { text-align: center; margin-top: 18px; font-size: 14px; color: rgba(255,255,255,0.32); }
        .login-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }

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

<!-- NAVBAR -->
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
                    <div><?= htmlspecialchars($success) ?><br><br><a href="/yo_voto/">← Ir a iniciar sesión</a></div>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="/yo_voto/registro-ciudadano" id="registroForm">

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

                <!-- CI -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label><i class="fas fa-id-card"></i> Número de CI <span class="req">*</span></label>
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
                            <input type="date" name="fecha_nac" id="fecha_nac" min="<?= $minDate ?>" max="<?= $maxDate ?>" required>
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

                <!-- UBICACIÓN -->
                <div class="sec-title"><i class="fas fa-map-marker-alt"></i> Información de Ubicación</div>
                <div class="form-grid grid-3" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label><i class="fas fa-map"></i> Departamento <span class="req">*</span></label>
                        <div class="input-wrap"><i class="ico fas fa-map"></i>
                            <select name="departamento" required>
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
                            <input type="text" name="ciudad" placeholder="Ej: La Paz">
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
                        <input type="text" name="direccion" placeholder="Ej: Calle Murillo #123">
                    </div>
                </div>

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

                <!-- REGISTRO FACIAL -->
                <div class="sec-title"><i class="fas fa-face-smile"></i> Registro Facial</div>
                <div class="biometric-box">
                    <div id="face-instrucciones">
                        <i class="fas fa-camera big"></i>
                        <p style="color:rgba(255,255,255,0.5);margin-bottom:16px;">Registre su rostro para iniciar sesión con reconocimiento facial</p>
                        <div class="tips-grid">
                            <div class="tip"><span class="tip-ico ok"><i class="fas fa-check"></i></span><span>Buena iluminación</span></div>
                            <div class="tip"><span class="tip-ico ok"><i class="fas fa-check"></i></span><span>Mire de frente</span></div>
                            <div class="tip"><span class="tip-ico no"><i class="fas fa-times"></i></span><span>Sin gorro ni gorrita</span></div>
                            <div class="tip"><span class="tip-ico no"><i class="fas fa-times"></i></span><span>Sin lentes oscuros</span></div>
                            <div class="tip"><span class="tip-ico no"><i class="fas fa-times"></i></span><span>Sin mascarilla</span></div>
                            <div class="tip"><span class="tip-ico ok"><i class="fas fa-check"></i></span><span>Rostro descubierto</span></div>
                        </div>
                    </div>
                    <video id="register-video" class="face-preview" autoplay muted playsinline></video>
                    <div style="margin-top:14px;">
                        <button type="button" id="start-camera-btn" class="btn-facial" onclick="startRegisterCamera()">
                            <i class="fas fa-camera"></i> Activar Cámara
                        </button>
                        <button type="button" id="capture-face-btn" class="btn-facial btn-facial-capture" onclick="captureAndRegisterFace()" style="display:none;">
                            <i class="fas fa-camera-retro"></i> Capturar Rostro
                        </button>
                        <button type="button" id="stop-camera-btn" class="btn-facial btn-facial-stop" onclick="stopRegisterCamera()" style="display:none;">
                            <i class="fas fa-stop"></i> Detener
                        </button>
                    </div>
                    <div id="face-status" class="face-status">
                        <span id="face-status-text"></span>
                    </div>
                    <input type="hidden" id="face_registered" name="face_registered" value="0">
                    <input type="hidden" id="face_descriptor" name="face_descriptor" value="">
                </div>

                <button type="submit" class="btn-submit" id="submit-btn" disabled>
                    <i class="fas fa-user-plus"></i> Crear mi Cuenta
                </button>
                <p class="btn-note">⚠️ Debe capturar su rostro antes de registrarse</p>
            </form>
            <?php endif; ?>

            <div class="login-link">¿Ya tienes cuenta? <a href="/yo_voto/">Iniciar sesión aquí</a></div>
        </div>
    </div>
</div>

<footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

<script>
    let registerStream = null, faceDescriptor = null, modelsLoaded = false;

    // ── FACE API ──
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
        showFaceStatus('<i class="fas fa-spinner fa-spin"></i> Inicializando sistema facial...', 'info');
        const ok = await waitForFaceAPI();
        if (!ok) { showFaceStatus('❌ No se pudo cargar FaceAPI. Verifica tu conexión.', 'error'); return; }
        try {
            // Forzar backend cpu (compatible con todos los dispositivos)
            await tf.setBackend('cpu');
            await tf.ready();
            console.log('✅ Backend TF:', tf.getBackend());

            const M = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
            showFaceStatus('<i class="fas fa-spinner fa-spin"></i> Cargando modelos faciales (puede tardar unos segundos)...', 'info');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(M);
            await faceapi.nets.faceLandmark68Net.loadFromUri(M);
            await faceapi.nets.faceRecognitionNet.loadFromUri(M);
            modelsLoaded = true;
            showFaceStatus('✅ Sistema listo. Ya puedes activar la cámara.', 'success');
            setTimeout(() => document.getElementById('face-status').style.display = 'none', 3000);
        } catch (e) {
            console.error('Error cargando modelos:', e);
            showFaceStatus('❌ Error: ' + e.message, 'error');
        }
    }

    async function startRegisterCamera() {
        if (!modelsLoaded) await loadFaceModels();
        if (!modelsLoaded) { showFaceStatus('❌ Los modelos no se cargaron. Recarga la página.', 'error'); return; }
        try {
            registerStream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480 } });
            const v = document.getElementById('register-video');
            v.srcObject = registerStream; v.style.display = 'block';
            document.getElementById('face-instrucciones').style.display = 'none';
            document.getElementById('start-camera-btn').style.display = 'none';
            document.getElementById('capture-face-btn').style.display = 'inline-flex';
            document.getElementById('stop-camera-btn').style.display = 'inline-flex';
            showFaceStatus('🔍 Cámara activa — mire de frente, sin gorro ni lentes, y presione Capturar.', 'info');
        } catch (e) { showFaceStatus('❌ Error al acceder a la cámara: ' + e.message, 'error'); }
    }

    async function captureAndRegisterFace() {
        const video = document.getElementById('register-video');
        if (!modelsLoaded) { showFaceStatus('⚠️ Espere a que los modelos terminen de cargar', 'error'); return; }
        showFaceStatus('<i class="fas fa-spinner fa-spin"></i> Detectando rostro...', 'info');
        try {
            const det = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
            if (!det) { showFaceStatus('❌ No se detectó ningún rostro. Asegúrese de estar bien iluminado.', 'error'); return; }
            faceDescriptor = det.descriptor;
            document.getElementById('face_registered').value = '1';
            document.getElementById('face_descriptor').value = JSON.stringify(Array.from(faceDescriptor));
            document.getElementById('submit-btn').disabled = false;
            showFaceStatus('✅ ¡Rostro capturado exitosamente! Ya puede registrarse.', 'success');
            setTimeout(() => stopRegisterCamera(), 2000);
        } catch (e) { showFaceStatus('❌ Error: ' + e.message, 'error'); }
    }

    function stopRegisterCamera() {
        if (registerStream) { registerStream.getTracks().forEach(t => t.stop()); registerStream = null; }
        const v = document.getElementById('register-video');
        if (v) { v.srcObject = null; v.style.display = 'none'; }
        document.getElementById('face-instrucciones').style.display = 'block';
        document.getElementById('start-camera-btn').style.display = 'inline-flex';
        document.getElementById('capture-face-btn').style.display = 'none';
        document.getElementById('stop-camera-btn').style.display = 'none';
    }

    function showFaceStatus(msg, type) {
        const s = document.getElementById('face-status');
        s.style.display = 'block'; s.className = 'face-status ' + type;
        document.getElementById('face-status-text').innerHTML = msg;
        if (type === 'success') setTimeout(() => s.style.display = 'none', 4000);
    }

    // ── VALIDACIONES ──
    document.getElementById('carnet').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
    });

    const passInput = document.getElementById('passInput');
    const confirmInput = document.getElementById('confirmInput');
    const fill = document.getElementById('strengthFill');
    const txt = document.getElementById('strengthText');
    const matchTxt = document.getElementById('matchText');

    passInput.addEventListener('input', () => {
        const v = passInput.value;
        let s = 0;
        if (v.length >= 6) s++;
        if (v.length >= 10) s++;
        if (/[A-Z]/.test(v)) s++;
        if (/[0-9]/.test(v)) s++;
        if (/[^A-Za-z0-9]/.test(v)) s++;
        const levels = [
            {w:'20%',c:'#E74C3C',l:'Muy débil'},
            {w:'40%',c:'#E67E22',l:'Débil'},
            {w:'60%',c:'#F1C40F',l:'Regular'},
            {w:'80%',c:'#27AE60',l:'Buena'},
            {w:'100%',c:'#1ABC9C',l:'Muy segura'},
        ];
        const lv = v.length > 0 ? (levels[Math.min(s-1,4)] || levels[0]) : {w:'0%',c:'transparent',l:''};
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

    document.getElementById('registroForm').addEventListener('submit', function(e) {
        if (document.getElementById('face_registered').value !== '1') {
            e.preventDefault(); showFaceStatus('⚠️ Debe capturar su rostro antes de registrarse', 'error'); return false;
        }
        const btn = document.getElementById('submit-btn');
        btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadFaceModels();
        document.getElementById('submit-btn').disabled = true;
    });
</script>
</body>
</html>