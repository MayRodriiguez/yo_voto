<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/yo_voto/config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = null;
$success = null;

$maxDate = date('Y-m-d', strtotime('-18 years'));
$minDate = date('Y-m-d', strtotime('-99 years'));

$extensiones = ['LP','OR','CB','PT','CH','TJ','SC','BE','PD'];
$departamentos = ['Beni','Chuquisaca','Cochabamba','La Paz','Oruro','Pando','Potosí','Santa Cruz','Tarija'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombres     = trim($_POST['nombres'] ?? '');
    $apellidos   = trim($_POST['apellidos'] ?? '');
    $carnet      = trim($_POST['carnet'] ?? '');
    $extension   = trim($_POST['extension'] ?? '');
    $nacimiento  = trim($_POST['fecha_nacimiento'] ?? '');
    $genero      = trim($_POST['genero'] ?? '');
    $direccion   = trim($_POST['direccion'] ?? '');
    $zona        = trim($_POST['zona'] ?? '');
    $ciudad      = trim($_POST['ciudad'] ?? '');
    $departamento= trim($_POST['departamento'] ?? '');
    $telefono    = trim($_POST['telefono'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    if (empty($nombres) || empty($apellidos) || empty($carnet) || empty($extension) || empty($nacimiento) || empty($genero) || empty($departamento) || empty($password)) {
        $error = "Los campos marcados con * son obligatorios.";
    } elseif (!ctype_digit($carnet) || strlen($carnet) < 7 || strlen($carnet) > 9) {
        $error = "El número de CI no es válido.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $fechaNac = new DateTime($nacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac)->y;
        if ($edad < 18) {
            $error = "Debes ser mayor de 18 años para registrarte.";
        } elseif ($edad > 99) {
            $error = "Edad máxima permitida: 99 años.";
        } else {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE carnet = ?");
            $stmt->bind_param("s", $carnet);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "Ya existe una cuenta con ese número de CI.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                // Check if new columns exist
                $cols = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'zona'");
                if ($cols->num_rows > 0) {
                    $insert = $conn->prepare("INSERT INTO usuarios (nombres, apellidos, carnet, extension, fecha_nacimiento, genero, direccion, zona, ciudad, departamento, telefono, email, password, rol, activo, habilitado_voto, ya_voto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 1, 0, 0)");
                    $insert->bind_param("sssssssssssss", $nombres, $apellidos, $carnet, $extension, $nacimiento, $genero, $direccion, $zona, $ciudad, $departamento, $telefono, $email, $hash);
                } else {
                    // Fallback sin columnas nuevas
                    $insert = $conn->prepare("INSERT INTO usuarios (nombres, apellidos, carnet, extension, fecha_nacimiento, direccion, telefono, email, password, rol, activo, habilitado_voto, ya_voto) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', 1, 0, 0)");
                    $insert->bind_param("sssssssss", $nombres, $apellidos, $carnet, $extension, $nacimiento, $direccion, $telefono, $email, $hash);
                }
                if ($insert->execute()) {
                    $success = "¡Registro exitoso! Ya puedes iniciar sesión con tu número de CI.";
                } else {
                    $error = "Error al registrar: " . $conn->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Yo Voto Bolivia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul: #003087; --azul-claro: #1976D2; --naranja: #FF6B00;
            --naranja-claro: #FF8C38; --gris: #F4F6FA; --borde: #E0E6F0;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Open Sans',sans-serif;
            min-height:100vh;
            background: linear-gradient(160deg, #001F5B 0%, #003087 40%, #0050B3 100%);
            display:flex; flex-direction:column;
        }

        /* dots bg */
        body::before {
            content:''; position:fixed; inset:0;
            background-image: radial-gradient(rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size:32px 32px; pointer-events:none; z-index:0;
        }

        /* TOPBAR */
        .topbar {
            position:relative; z-index:10;
            background:rgba(0,0,0,0.25); backdrop-filter:blur(16px);
            border-bottom:1px solid rgba(255,255,255,0.08);
            padding:16px 48px; display:flex; justify-content:space-between; align-items:center;
        }
        .topbar-logo { font-family:'Montserrat',sans-serif; font-weight:900; font-size:22px; color:white; display:flex; align-items:center; gap:10px; }
        .topbar-logo i { color:var(--naranja); }
        .topbar-logo span { color:var(--naranja); }
        .topbar-back { color:rgba(255,255,255,0.7); text-decoration:none; font-size:14px; font-weight:600; display:flex; align-items:center; gap:7px; transition:.2s; }
        .topbar-back:hover { color:var(--naranja); }

        /* MAIN */
        .main { position:relative; z-index:1; max-width:720px; width:100%; margin:36px auto 40px; padding:0 20px; }

        /* HERO TEXT */
        .hero-text { text-align:center; margin-bottom:28px; }
        .hero-text h1 { font-family:'Montserrat',sans-serif; font-weight:900; font-size:32px; color:white; margin-bottom:8px; }
        .hero-text h1 span { color:var(--naranja); }
        .hero-text p { color:rgba(255,255,255,0.6); font-size:15px; }

        /* CARD */
        .card { background:white; border-radius:20px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
        .card-header { background:linear-gradient(135deg, var(--naranja), var(--naranja-claro)); padding:20px 28px; display:flex; align-items:center; gap:14px; }
        .card-header-icon { width:44px; height:44px; background:rgba(255,255,255,0.2); border-radius:11px; display:flex; align-items:center; justify-content:center; }
        .card-header-icon i { font-size:20px; color:white; }
        .card-header h2 { font-family:'Montserrat',sans-serif; font-weight:700; font-size:17px; color:white; margin:0; }
        .card-header p { font-size:12px; color:rgba(255,255,255,0.8); margin:2px 0 0; }
        .card-body { padding:32px 28px; }

        /* ALERTS */
        .alert { padding:13px 16px; border-radius:10px; font-size:14px; margin-bottom:24px; display:flex; align-items:flex-start; gap:10px; }
        .alert-error { background:#FFF0F0; color:#C0392B; border-left:4px solid #E74C3C; }
        .alert-success { background:#F0FFF4; color:#1A7A3A; border-left:4px solid #27AE60; }
        .alert i { margin-top:1px; flex-shrink:0; }

        /* SECTION TITLE */
        .section-title { font-size:11px; font-weight:700; color:var(--naranja); text-transform:uppercase; letter-spacing:1.5px; margin-bottom:16px; margin-top:28px; padding-bottom:8px; border-bottom:2px solid #FFF0E6; display:flex; align-items:center; gap:8px; }
        .section-title:first-child { margin-top:0; }
        .section-title i { font-size:13px; }

        /* FORM GRID */
        .form-grid { display:grid; gap:16px; }
        .grid-2 { grid-template-columns:1fr 1fr; }
        .grid-3 { grid-template-columns:1fr 1fr 1fr; }
        .grid-ci { grid-template-columns:1fr 100px; }

        .form-group { display:flex; flex-direction:column; gap:6px; }
        .form-group label { font-size:13px; font-weight:700; color:var(--azul); display:flex; align-items:center; gap:6px; }
        .form-group label .req { color:var(--naranja); font-size:14px; }
        .input-wrap { position:relative; }
        .input-wrap .ico { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:#BBB; font-size:14px; pointer-events:none; }
        .form-group input,
        .form-group select {
            width:100%; padding:11px 13px 11px 38px;
            border:1.5px solid var(--borde); border-radius:10px;
            font-size:14px; font-family:inherit; color:#333;
            background:#FAFBFD; transition:.2s;
            appearance:none;
        }
        .form-group select { cursor:pointer; }
        .form-group input:focus,
        .form-group select:focus {
            outline:none; border-color:var(--azul-claro);
            box-shadow:0 0 0 3px rgba(25,118,210,0.1); background:white;
        }
        .form-group input.no-icon,
        .form-group select.no-icon { padding-left:13px; }
        .form-group small { font-size:11px; color:#AAA; }

        /* GENERO PILLS */
        .genero-pills { display:flex; gap:10px; }
        .genero-pill { flex:1; }
        .genero-pill input[type=radio] { display:none; }
        .genero-pill label {
            display:flex; align-items:center; justify-content:center; gap:8px;
            padding:11px 10px; border-radius:10px; border:1.5px solid var(--borde);
            background:#FAFBFD; cursor:pointer; font-size:13px; font-weight:600;
            color:#666; transition:.2s; text-align:center;
        }
        .genero-pill input:checked + label {
            border-color:var(--naranja); background:#FFF5EC; color:var(--naranja);
        }
        .genero-pill label:hover { border-color:#CCC; background:#F0F2F6; }

        /* STRENGTH */
        .strength-bar { height:4px; border-radius:2px; background:#EEE; overflow:hidden; margin-top:7px; }
        .strength-fill { height:100%; border-radius:2px; width:0%; transition:.3s; }
        .strength-text { font-size:11px; color:#AAA; margin-top:4px; }

        /* SUBMIT */
        .btn-submit {
            width:100%; padding:14px; margin-top:24px;
            background:linear-gradient(135deg, var(--naranja), var(--naranja-claro));
            color:white; border:none; border-radius:11px;
            font-family:'Montserrat',sans-serif; font-weight:800; font-size:16px;
            cursor:pointer; transition:.25s;
            box-shadow:0 6px 20px rgba(255,107,0,0.35);
            display:flex; align-items:center; justify-content:center; gap:10px;
        }
        .btn-submit:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(255,107,0,0.45); }

        /* LOGIN LINK */
        .login-link { text-align:center; margin-top:20px; font-size:14px; color:rgba(255,255,255,0.6); }
        .login-link a { color:var(--naranja-claro); font-weight:700; text-decoration:none; }
        .login-link a:hover { text-decoration:underline; }

        /* STEPS indicator */
        .steps { display:flex; justify-content:center; gap:0; margin-bottom:28px; }
        .step { display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:rgba(255,255,255,0.4); }
        .step.active { color:white; }
        .step-dot { width:28px; height:28px; border-radius:50%; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; border:1.5px solid rgba(255,255,255,0.2); }
        .step.active .step-dot { background:var(--naranja); border-color:var(--naranja); color:white; }
        .step-line { width:40px; height:1px; background:rgba(255,255,255,0.15); margin:0 4px; }

        footer { text-align:center; padding:20px; font-size:12px; color:rgba(255,255,255,0.3); position:relative; z-index:1; }

        @media(max-width:600px) {
            .grid-2, .grid-3 { grid-template-columns:1fr; }
            .grid-ci { grid-template-columns:1fr 90px; }
            .topbar { padding:14px 20px; }
            .card-body { padding:24px 18px; }
            .genero-pills { flex-direction:column; }
        }
    </style>
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo"><i class="fas fa-vote-yea"></i> Yo <span>Voto</span></div>
    <a href="/yo_voto/" class="topbar-back"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
</div>

<div class="main">
    <div class="hero-text">
        <h1>Crea tu <span>cuenta</span></h1>
        <p>Regístrate para participar en las elecciones Bolivia 2026</p>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-user-plus"></i></div>
            <div>
                <h2>Formulario de Registro</h2>
                <p>Completa todos los campos obligatorios (*)</p>
            </div>
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <div><?= htmlspecialchars($success) ?><br><a href="/yo_voto/" style="color:var(--azul-claro);font-weight:700;margin-top:6px;display:inline-block;">← Ir al inicio a iniciar sesión</a></div>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="/yo_voto/registro" id="regForm">

                <!-- DATOS PERSONALES -->
                <div class="section-title"><i class="fas fa-user"></i> Datos Personales</div>
                <div class="form-grid grid-2" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombres <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-user"></i>
                            <input type="text" name="nombres" placeholder="Ej: Juan Carlos" value="<?= htmlspecialchars($_POST['nombres'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellidos <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-user"></i>
                            <input type="text" name="apellidos" placeholder="Ej: Mamani Quispe" value="<?= htmlspecialchars($_POST['apellidos'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-grid" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Número de CI + Extensión <span class="req">*</span></label>
                        <div class="form-grid grid-ci">
                            <div class="input-wrap">
                                <i class="ico fas fa-id-card"></i>
                                <input type="text" name="carnet" placeholder="Número de CI" maxlength="9" inputmode="numeric" value="<?= htmlspecialchars($_POST['carnet'] ?? '') ?>" required>
                            </div>
                            <div class="input-wrap">
                                <select name="extension" class="no-icon" required>
                                    <option value="">Ext.</option>
                                    <?php foreach($extensiones as $ext): ?>
                                        <option value="<?= $ext ?>" <?= (($_POST['extension'] ?? '') == $ext) ? 'selected' : '' ?>><?= $ext ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-grid grid-2" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha de Nacimiento <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-calendar"></i>
                            <input type="date" name="fecha_nacimiento" min="<?= $minDate ?>" max="<?= $maxDate ?>" value="<?= htmlspecialchars($_POST['fecha_nacimiento'] ?? '') ?>" required>
                        </div>
                        <small>Debes ser mayor de 18 años</small>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono / Celular</label>
                        <div class="input-wrap">
                            <i class="ico fas fa-phone"></i>
                            <input type="text" name="telefono" placeholder="Ej: 77712345" maxlength="15" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label><i class="fas fa-venus-mars"></i> Género <span class="req">*</span></label>
                    <div class="genero-pills">
                        <div class="genero-pill">
                            <input type="radio" name="genero" id="g-m" value="Masculino" <?= (($_POST['genero'] ?? '') == 'Masculino') ? 'checked' : '' ?> required>
                            <label for="g-m"><i class="fas fa-mars"></i> Masculino</label>
                        </div>
                        <div class="genero-pill">
                            <input type="radio" name="genero" id="g-f" value="Femenino" <?= (($_POST['genero'] ?? '') == 'Femenino') ? 'checked' : '' ?>>
                            <label for="g-f"><i class="fas fa-venus"></i> Femenino</label>
                        </div>
                        <div class="genero-pill">
                            <input type="radio" name="genero" id="g-o" value="Prefiero no decir" <?= (($_POST['genero'] ?? '') == 'Prefiero no decir') ? 'checked' : '' ?>>
                            <label for="g-o"><i class="fas fa-genderless"></i> Prefiero no decir</label>
                        </div>
                    </div>
                </div>

                <!-- UBICACION -->
                <div class="section-title"><i class="fas fa-map-marker-alt"></i> Información de Ubicación</div>

                <div class="form-grid grid-3" style="margin-bottom:16px;">
                    <div class="form-group">
                        <label><i class="fas fa-map"></i> Departamento <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-map"></i>
                            <select name="departamento" required>
                                <option value="">Seleccionar</option>
                                <?php foreach($departamentos as $dep): ?>
                                    <option value="<?= $dep ?>" <?= (($_POST['departamento'] ?? '') == $dep) ? 'selected' : '' ?>><?= $dep ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> Ciudad </label>
                        <div class="input-wrap">
                            <i class="ico fas fa-city"></i>
                            <input type="text" name="ciudad" placeholder="Ej: La Paz" value="<?= htmlspecialchars($_POST['ciudad'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-map-pin"></i> Zona / Av </label>
                        <div class="input-wrap">
                            <i class="ico fas fa-map-pin"></i>
                            <input type="text" name="zona" placeholder="Ej: Miraflores" value="<?= htmlspecialchars($_POST['zona'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label><i class="fas fa-home"></i> Dirección</label>
                    <div class="input-wrap">
                        <i class="ico fas fa-home"></i>
                        <input type="text" name="direccion" placeholder="Ej: Calle Murillo #123" value="<?= htmlspecialchars($_POST['direccion'] ?? '') ?>">
                    </div>
                </div>

                <!-- CONTACTO -->
                <div class="section-title"><i class="fas fa-envelope"></i> Contacto</div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label><i class="fas fa-envelope"></i> Correo Electrónico <span class="req">*</span></label>
                    <div class="input-wrap">
                        <i class="ico fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="tu@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- SEGURIDAD -->
                <div class="section-title"><i class="fas fa-shield-alt"></i> Seguridad</div>
                <div class="form-grid grid-2">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-lock"></i>
                            <input type="password" name="password" id="passInput" placeholder="Mín. 6 caracteres" required>
                        </div>
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-check-double"></i> Confirmar Contraseña <span class="req">*</span></label>
                        <div class="input-wrap">
                            <i class="ico fas fa-check-double"></i>
                            <input type="password" name="confirm_password" id="confirmInput" placeholder="Repite tu contraseña" required>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:16px;display:flex;justify-content:center;">
                    <div class="h-captcha" data-sitekey="a22ee458-031e-489e-93ee-1aa2545e7aa2"></div>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-user-plus"></i> Crear mi Cuenta</button>
            </form>
            <?php endif; ?>

        </div>
    </div>

    <div class="login-link">¿Ya tienes cuenta? <a href="/yo_voto/">Iniciar sesión aquí</a></div>
</div>

<footer>Yo Voto — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</footer>

<script>
    const passInput = document.getElementById('passInput');
    const fill = document.getElementById('strengthFill');
    const txt = document.getElementById('strengthText');
    passInput && passInput.addEventListener('input', () => {
        const v = passInput.value;
        let s = 0;
        if(v.length>=6) s++;
        if(v.length>=10) s++;
        if(/[A-Z]/.test(v)) s++;
        if(/[0-9]/.test(v)) s++;
        if(/[^A-Za-z0-9]/.test(v)) s++;
        const levels = [
            {w:'20%',c:'#E74C3C',l:'Muy débil'},
            {w:'40%',c:'#E67E22',l:'Débil'},
            {w:'60%',c:'#F1C40F',l:'Regular'},
            {w:'80%',c:'#27AE60',l:'Buena'},
            {w:'100%',c:'#1ABC9C',l:'Muy segura'},
        ];
        const lv = levels[Math.min(s-1,4)] || {w:'0%',c:'#EEE',l:''};
        fill.style.width=lv.w; fill.style.background=lv.c;
        txt.textContent=lv.l; txt.style.color=lv.c;
    });
</script>
</body>
</html>