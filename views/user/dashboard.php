<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
    header("Location: /yo_voto/");
    exit();
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; z-index: 100; background: rgba(10,22,50,0.97); backdrop-filter: blur(10px); height: 60px; display: flex; align-items: center; padding: 0 40px; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-logo i { color: #FF6B00; font-size: 18px; }
        .navbar-nav { display: flex; align-items: center; gap: 6px; }
        .navbar-nav a { color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; display: flex; align-items: center; gap: 6px; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .navbar-nav .btn-votar-nav { background: #FF6B00; color: #fff; }
        .navbar-nav .btn-votar-nav:hover { background: #FF8C38; }
        .navbar-nav .btn-logout { background: rgba(231,76,60,0.15); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.3); }
        .navbar-nav .btn-logout:hover { background: #E74C3C; color: #fff; }
        .navbar-nav .user-name { color: #FF8C38; font-size: 14px; font-weight: 600; padding: 7px 14px; }
        .main { min-height: 100vh; background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%); padding: 90px 24px 60px; display: flex; flex-direction: column; align-items: center; position: relative; }
        .main::before { content: ''; position: fixed; inset: 0; pointer-events: none; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 36px 36px; }
        .hero-text { text-align: center; margin-bottom: 32px; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35); color: #FF8C38; padding: 6px 18px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 36px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; width: 100%; max-width: 760px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4); position: relative; z-index: 1; margin-bottom: 24px; }
        .card-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 22px 28px; display: flex; align-items: center; gap: 16px; }
        .card-head-icon { width: 46px; height: 46px; border-radius: 12px; background: #FF6B00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-head-icon i { font-size: 20px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 18px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 3px 0 0; }
        .card-body { padding: 30px 28px; }
        .sec-title { font-size: 11px; font-weight: 700; color: #FF6B00; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; margin-top: 26px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25); display: flex; align-items: center; gap: 8px; }
        .sec-title:first-child { margin-top: 0; }
        .info-row { display: flex; align-items: center; gap: 14px; padding: 13px 0; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .info-row:last-child { border-bottom: none; }
        .info-icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,107,0,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .info-icon i { color: #FF6B00; font-size: 14px; }
        .info-content { flex: 1; }
        .info-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .info-value { font-size: 14px; color: #fff; font-weight: 600; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-success { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.3); }
        .badge-warning { background: rgba(255,152,0,0.15); color: #FFB74D; border: 1px solid rgba(255,152,0,0.3); }
        .badge-voted { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.3); }
        .badge-pending { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.45); border: 1px solid rgba(255,255,255,0.12); }
        .btn-votar-big { width: 100%; padding: 16px; border-radius: 12px; border: none; background: #FF6B00; color: #fff; font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 800; cursor: pointer; transition: .25s; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; box-shadow: 0 6px 24px rgba(255,107,0,0.35); }
        .btn-votar-big:hover { background: #FF8C38; transform: translateY(-2px); color: #fff; }
        .btn-outline-secondary { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.7); padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; border: 1px solid rgba(255,255,255,0.12); transition: .2s; }
        .btn-outline-secondary:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .estado-box { border-radius: 16px; padding: 28px; text-align: center; }
        .estado-box.ya-voto { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); }
        .estado-box.pendiente { background: rgba(255,152,0,0.08); border: 1px solid rgba(255,152,0,0.2); }
        .estado-box i { font-size: 44px; margin-bottom: 14px; display: block; }
        .estado-box.ya-voto i { color: #5cdb95; }
        .estado-box.pendiente i { color: #FFB74D; }
        .estado-box h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 18px; margin-bottom: 8px; }
        .estado-box p { color: rgba(255,255,255,0.45); font-size: 14px; margin-bottom: 20px; }
        footer { background: #070e1f; color: rgba(255,255,255,0.3); text-align: center; padding: 28px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); width: 100%; position: relative; z-index: 1; margin-top: 20px; }
        footer span { color: #FF6B00; font-weight: 700; }
        @media (max-width: 768px) { .navbar { padding: 0 16px; } .hero-text h1 { font-size: 26px; } .card-body { padding: 20px 16px; } }
    </style>
</head>
<body>

<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></a>
    <nav class="navbar-nav">
        <span class="user-name"><i class="fas fa-user"></i> <?= htmlspecialchars($user['nombres']) ?></span>
        <a href="/yo_voto/"><i class="fas fa-home"></i> Inicio</a>
        <?php if (!$user['ya_voto'] && $user['habilitado_voto']): ?>
            <a href="/yo_voto/votar" class="btn-votar-nav"><i class="fas fa-vote-yea"></i> Votar</a>
        <?php endif; ?>
        <a href="/yo_voto/logout-votante" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </nav>
</header>

<main class="main">
    <div class="hero-text">
        <div class="hero-badge"><i class="fas fa-shield-alt"></i> Panel del Ciudadano</div>
        <h1>Bienvenido, <span><?= htmlspecialchars($user['nombres']) ?></span></h1>
        <p>Tu voto es importante para nuestra Bolivia </p>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-user-circle"></i></div>
            <div>
                <h2>Mi Perfil de Ciudadano</h2>
                <p>Información de tu registro electoral</p>
            </div>
        </div>
        <div class="card-body">

            <div class="sec-title"><i class="fas fa-id-badge"></i> Identificación</div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-user"></i></div>
                <div class="info-content">
                    <div class="info-label">Nombre Completo</div>
                    <div class="info-value"><?= htmlspecialchars($user['nombres']) ?> <?= htmlspecialchars($user['apellidos']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-id-card"></i></div>
                <div class="info-content">
                    <div class="info-label">Carnet de Identidad</div>
                    <div class="info-value"><?= htmlspecialchars($user['carnet']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-calendar"></i></div>
                <div class="info-content">
                    <div class="info-label">Fecha de Nacimiento</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($user['fecha_nacimiento'])) ?></div>
                </div>
            </div>

            <div class="sec-title"><i class="fas fa-address-book"></i> Contacto</div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-content">
                    <div class="info-label">Correo Electrónico</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div class="info-content">
                    <div class="info-label">Teléfono</div>
                    <div class="info-value"><?= htmlspecialchars($user['telefono'] ?? 'No registrado') ?></div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="info-content">
                    <div class="info-label">Dirección</div>
                    <div class="info-value"><?= htmlspecialchars($user['direccion'] ?? 'No registrada') ?></div>
                </div>
            </div>

            <div class="sec-title"><i class="fas fa-vote-yea"></i> Estado Electoral</div>

            <?php if ($user['ya_voto']): ?>
            <div style="margin-top:8px;background:rgba(39,174,96,0.08);border:1px solid rgba(39,174,96,0.2);border-radius:16px;padding:28px;text-align:center;">
                <i class="fas fa-check-circle" style="font-size:44px;color:#5cdb95;display:block;margin-bottom:14px;"></i>
                <h3 style="font-family:'Montserrat',sans-serif;font-weight:800;color:#fff;font-size:18px;margin-bottom:8px;">Gracias por participar</h3>
                <p style="color:rgba(255,255,255,0.45);font-size:14px;margin-bottom:20px;">Ya has emitido tu voto</p>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <a href="/yo_voto/certificado" style="background:#FF6B00;color:#fff;padding:11px 22px;border-radius:10px;font-weight:800;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                        <i class="fas fa-download"></i> Descargar Certificado
                    </a>
                    <a href="/yo_voto/" class="btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
                </div>
            </div>
            <?php elseif ($user['habilitado_voto']): ?>
            <div style="margin-top:8px;">
                <a href="/yo_voto/votar" class="btn-votar-big">
                    <i class="fas fa-vote-yea"></i> Ir a Votar Ahora
                </a>
            </div>
            <?php else: ?>
            <div style="margin-top:8px;background:rgba(255,152,0,0.08);border:1px solid rgba(255,152,0,0.2);border-radius:16px;padding:28px;text-align:center;">
                <i class="fas fa-clock" style="font-size:44px;color:#FFB74D;display:block;margin-bottom:14px;"></i>
                <h3 style="font-family:'Montserrat',sans-serif;font-weight:800;color:#fff;font-size:18px;margin-bottom:8px;">Habilitación Pendiente</h3>
                <p style="color:rgba(255,255,255,0.45);font-size:14px;margin-bottom:20px;">Tu cuenta aún no ha sido habilitada. Vuelve a intentarlo más tarde.</p>
                <a href="/yo_voto/" class="btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<footer>
    <p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p>
</footer>

</body>
</html>