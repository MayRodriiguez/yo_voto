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
<<<<<<< HEAD
    <title>Mi Perfil - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
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
        .navbar-logo i { color: #FF6B00; font-size: 18px; }
        .navbar-nav { display: flex; align-items: center; gap: 6px; }
        .navbar-nav a { color: rgba(255,255,255,0.75); text-decoration: none; font-size: 14px; font-weight: 600; padding: 7px 14px; border-radius: 8px; transition: .2s; display: flex; align-items: center; gap: 6px; }
        .navbar-nav a:hover { color: #fff; background: rgba(255,255,255,0.08); }
        .navbar-nav .btn-votar-nav { background: #FF6B00; color: #fff; }
        .navbar-nav .btn-votar-nav:hover { background: #FF8C38; }
        .navbar-nav .btn-logout { background: rgba(231,76,60,0.15); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.3); }
        .navbar-nav .btn-logout:hover { background: #E74C3C; color: #fff; }
        .navbar-nav .user-name { color: #FF8C38; font-size: 14px; font-weight: 600; padding: 7px 14px; }

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
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 36px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }

        /* CARD */
        .card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; width: 100%; max-width: 760px;
            overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4);
            position: relative; z-index: 1; margin-bottom: 24px;
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

        /* INFO ROWS */
        .sec-title {
            font-size: 11px; font-weight: 700; color: #FF6B00;
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 16px; margin-top: 26px;
            padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25);
            display: flex; align-items: center; gap: 8px;
        }
        .sec-title:first-child { margin-top: 0; }

        .info-row {
            display: flex; align-items: center; gap: 14px;
            padding: 13px 0; border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .info-row:last-child { border-bottom: none; }
        .info-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,107,0,0.12); display: flex; align-items: center;
            justify-content: center; flex-shrink: 0;
        }
        .info-icon i { color: #FF6B00; font-size: 14px; }
        .info-content { flex: 1; }
        .info-label { font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .info-value { font-size: 14px; color: #fff; font-weight: 600; }

        /* NUMERO REGISTRO */
        .numero-registro {
            font-family: 'Courier New', monospace; font-size: 16px; font-weight: 700;
            color: #FF8C38; background: rgba(255,107,0,0.1);
            border: 1px solid rgba(255,107,0,0.25);
            padding: 4px 12px; border-radius: 8px; display: inline-block;
        }

        /* BADGES */
        .badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
        }
        .badge-success { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.3); }
        .badge-warning { background: rgba(255,152,0,0.15); color: #FFB74D; border: 1px solid rgba(255,152,0,0.3); }
        .badge-voted { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.3); }
        .badge-pending { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.45); border: 1px solid rgba(255,255,255,0.12); }

        /* BOTONES */
        .btn-votar-big {
            width: 100%; padding: 16px; border-radius: 12px; border: none;
            background: #FF6B00; color: #fff; font-family: 'Montserrat', sans-serif;
            font-size: 16px; font-weight: 800; cursor: pointer; transition: .25s;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none; box-shadow: 0 6px 24px rgba(255,107,0,0.35);
        }
        .btn-votar-big:hover { background: #FF8C38; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(255,107,0,0.45); color: #fff; }

        .btn-outline-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.7);
            padding: 11px 22px; border-radius: 10px; text-decoration: none;
            font-size: 14px; font-weight: 600; border: 1px solid rgba(255,255,255,0.12);
            transition: .2s;
        }
        .btn-outline-secondary:hover { background: rgba(255,255,255,0.12); color: #fff; }

        /* ESTADO ESPECIAL */
        .estado-box {
            border-radius: 16px; padding: 28px; text-align: center;
        }
        .estado-box.ya-voto { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); }
        .estado-box.pendiente { background: rgba(255,152,0,0.08); border: 1px solid rgba(255,152,0,0.2); }
        .estado-box i { font-size: 44px; margin-bottom: 14px; display: block; }
        .estado-box.ya-voto i { color: #5cdb95; }
        .estado-box.pendiente i { color: #FFB74D; }
        .estado-box h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 18px; margin-bottom: 8px; }
        .estado-box p { color: rgba(255,255,255,0.45); font-size: 14px; margin-bottom: 20px; }

        /* FOOTER */
        footer { background: #070e1f; color: rgba(255,255,255,0.3); text-align: center; padding: 28px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); width: 100%; position: relative; z-index: 1; margin-top: 20px; }
        footer span { color: #FF6B00; font-weight: 700; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .hero-text h1 { font-size: 26px; }
            .card-body { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
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

<!-- MAIN -->
<main class="main">

    <!-- HERO -->
    <div class="hero-text">
        <div class="hero-badge"><i class="fas fa-shield-alt"></i> Panel del Ciudadano</div>
        <h1>Bienvenido, <span><?= htmlspecialchars($user['nombres']) ?></span></h1>
        <p>Tu voz es importante para la democracia de Bolivia</p>
    </div>

    <!-- CARD: DATOS PERSONALES -->
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
                <div class="info-icon"><i class="fas fa-qrcode"></i></div>
                <div class="info-content">
                    <div class="info-label">N° Registro Electoral</div>
                    <div class="info-value">
                        <span class="numero-registro"><?= htmlspecialchars($user['numero_registro'] ?? 'Pendiente') ?></span>
                    </div>
                </div>
            </div>

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

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-check-circle"></i></div>
                <div class="info-content">
                    <div class="info-label">Habilitado para Votar</div>
                    <div class="info-value">
                        <?php if ($user['habilitado_voto']): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí, habilitado</span>
                        <?php else: ?>
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente de habilitación</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="info-row">
                <div class="info-icon"><i class="fas fa-ballot-check"></i></div>
                <div class="info-content">
                    <div class="info-label">Estado de Voto</div>
                    <div class="info-value">
                        <?php if ($user['ya_voto']): ?>
                            <span class="badge badge-voted"><i class="fas fa-check-circle"></i> ✅ Ya votaste — ¡Gracias!</span>
                        <?php else: ?>
                            <span class="badge badge-pending"><i class="fas fa-hourglass-half"></i> Aún no has votado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- CARD: ACCIONES -->
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-bolt"></i></div>
            <div>
                <h2>Acciones</h2>
                <p>¿Qué deseas hacer?</p>
            </div>
        </div>
        <div class="card-body">

            <?php if ($user['ya_voto']): ?>
                <div class="estado-box ya-voto">
                    <i class="fas fa-check-circle"></i>
                    <h3>¡Gracias por participar!</h3>
                    <p>Ya has emitido tu voto en este proceso electoral. Tu participación fortalece la democracia boliviana.</p>
                    <a href="/yo_voto/" class="btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
                </div>

            <?php elseif ($user['habilitado_voto']): ?>
                <a href="/yo_voto/votar" class="btn-votar-big">
                    <i class="fas fa-vote-yea"></i> Ir a Votar Ahora
                </a>

            <?php else: ?>
                <div class="estado-box pendiente">
                    <i class="fas fa-clock"></i>
                    <h3>Habilitación Pendiente</h3>
                    <p>Tu cuenta aún no ha sido habilitada por el administrador electoral. Vuelve a intentarlo más tarde.</p>
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
=======
    <title>Mi Perfil - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        .navbar { background: rgba(0,51,153,0.95); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logo { font-size: 28px; font-weight: bold; color: #f5c518; }
        .logo span { color: white; }
        .nav-links { display: flex; gap: 20px; align-items: center; }
        .nav-links a { color: white; text-decoration: none; padding: 8px 16px; border-radius: 25px; transition: 0.3s; }
        .nav-links a:hover { background: rgba(255,255,255,0.2); }
        .btn-logout { background: #dc2626; color: white !important; }
        .dashboard-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .welcome-card { background: linear-gradient(135deg, #003399, #1a5bc4); border-radius: 30px; padding: 40px; color: white; margin-bottom: 30px; text-align: center; border-bottom: 4px solid #f5c518; }
        .welcome-card h1 { font-size: 36px; }
        .profile-card { background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 30px; }
        .profile-header { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 20px 30px; }
        .profile-header h2 { margin: 0; font-size: 24px; }
        .profile-body { padding: 30px; }
        .info-row { display: flex; padding: 12px 0; border-bottom: 1px solid #e0e0e0; }
        .info-label { width: 200px; font-weight: bold; color: #003399; }
        .info-value { flex: 1; color: #333; }
        .badge-success { background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .badge-voted { background: #c8e6c9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 12px; display: inline-block; }
        .numero-registro { font-family: 'Courier New', monospace; font-size: 24px; font-weight: bold; color: #003399; background: #e8e0ff; padding: 5px 15px; border-radius: 10px; }
        .btn-votar { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 15px 30px; border: none; border-radius: 15px; font-size: 18px; font-weight: bold; cursor: pointer; width: 100%; text-align: center; display: block; text-decoration: none; }
        .btn-volver { background: #6c757d; color: white; padding: 12px 25px; border-radius: 10px; text-decoration: none; display: inline-block; }
        .ya-voto-mensaje { text-align: center; padding: 30px; background: #e8f5e9; border-radius: 15px; border-left: 5px solid #4caf50; }
        footer { text-align: center; padding: 30px; color: white; background: rgba(0,51,153,0.9); margin-top: 40px; }
        @media (max-width: 768px) { .navbar { flex-direction: column; gap: 15px; } .info-row { flex-direction: column; } .info-label { width: 100%; margin-bottom: 5px; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Yo <span>Voto</span></div>
        <div class="nav-links">
            <a href="/yo_voto/"><i class="fas fa-home"></i> Inicio</a>
            <?php if (!$user['ya_voto'] && $user['habilitado_voto']): ?>
                <a href="/yo_voto/votar" style="background: #4caf50;"><i class="fas fa-vote-yea"></i> Votar</a>
            <?php endif; ?>
            <a href="/yo_voto/logout-votante" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="welcome-card">
            <h1><i class="fas fa-hand-peace"></i> ¡Bienvenido, <?= htmlspecialchars($user['nombres']) ?>!</h1>
            <p>Tu voz es importante para la democracia de Bolivia</p>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <h2><i class="fas fa-user-circle"></i> Mi Perfil de Ciudadano</h2>
            </div>
            <div class="profile-body">
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-qrcode"></i> N° Registro Electoral</div>
                    <div class="info-value"><span class="numero-registro"><?= htmlspecialchars($user['numero_registro'] ?? 'Pendiente') ?></span></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user"></i> Nombre completo</div>
                    <div class="info-value"><?= htmlspecialchars($user['nombres']) ?> <?= htmlspecialchars($user['apellidos']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-id-card"></i> Carnet de Identidad</div>
                    <div class="info-value"><?= htmlspecialchars($user['carnet']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-calendar"></i> Fecha de nacimiento</div>
                    <div class="info-value"><?= date('d/m/Y', strtotime($user['fecha_nacimiento'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-envelope"></i> Correo electrónico</div>
                    <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-phone"></i> Teléfono</div>
                    <div class="info-value"><?= htmlspecialchars($user['telefono'] ?? 'No registrado') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-home"></i> Dirección</div>
                    <div class="info-value"><?= htmlspecialchars($user['direccion'] ?? 'No registrada') ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-check-circle"></i> Habilitado para votar</div>
                    <div class="info-value"><?= $user['habilitado_voto'] ? '<span class="badge-success"><i class="fas fa-check"></i> Sí, habilitado</span>' : '<span class="badge-success">Pendiente</span>' ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-vote-yea"></i> Estado de voto</div>
                    <div class="info-value"><?= $user['ya_voto'] ? '<span class="badge-voted"><i class="fas fa-check-circle"></i> ✅ Ya votaste - ¡Gracias!</span>' : '<span class="badge-success"><i class="fas fa-hourglass-half"></i> Aún no has votado</span>' ?></div>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <h2><i class="fas fa-actions"></i> Acciones</h2>
            </div>
            <div class="profile-body">
                <?php if ($user['ya_voto']): ?>
                    <div class="ya-voto-mensaje">
                        <i class="fas fa-check-circle" style="font-size: 48px; color: #4caf50; margin-bottom: 15px;"></i>
                        <h3>¡Gracias por participar!</h3>
                        <p>Ya has emitido tu voto en este proceso electoral.</p>
                        <a href="/yo_voto/" class="btn-volver" style="margin-top: 20px;"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
                    </div>
                <?php elseif ($user['habilitado_voto']): ?>
                    <a href="/yo_voto/votar" class="btn-votar"><i class="fas fa-vote-yea"></i> Ir a Votar Ahora</a>
                <?php else: ?>
                    <div class="ya-voto-mensaje" style="background: #fff3e0; border-left-color: #ff9800;">
                        <i class="fas fa-clock" style="font-size: 48px; color: #ff9800;"></i>
                        <h3>Habilitación Pendiente</h3>
                        <p>Tu cuenta aún no ha sido habilitada para votar.</p>
                        <a href="/yo_voto/" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer><p><i class="fas fa-gavel"></i> Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p></footer>
</body>
</html>
>>>>>>> 14bf65808c01528e1449c8356f81b4b5f8f1154f
