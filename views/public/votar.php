<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
    header("Location: /yo_voto/");
    exit();
}

require_once 'config/database.php';
require_once 'models/Candidato.php';
require_once 'models/Voto.php';
require_once 'models/User.php';

$db = new Database();
$conn = $db->getConnection();

$candidatoModel = new Candidato();
$votoModel = new Voto();
$userModel = new User();

$user = $_SESSION['user'];

$stmtUser = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmtUser->bind_param("i", $user['id']);
$stmtUser->execute();
$userData = $stmtUser->get_result()->fetch_assoc();
if ($userData) $user = $userData;

$yaVoto = $userModel->yaVoto($user['id']);
$candidatos = $candidatoModel->getAllActivos();

$config = [];
$resConfig = $conn->query("SELECT clave, valor FROM configuracion");
while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }

$fechaVotacion = $config['fecha_votacion'] ?? '';
$horaApertura  = $config['hora_apertura']  ?? '08:00';
$horaCierre    = $config['hora_cierre']    ?? '16:00';

$votacionAbierta = false;
$mensajeHorario  = '';

if ($fechaVotacion) {
    $ahora      = new DateTime();
    $fechaHoy   = $ahora->format('Y-m-d');
    $horaActual = $ahora->format('H:i');

    if ($fechaHoy < $fechaVotacion) {
        $mensajeHorario = '⏳ La votación aún no ha comenzado. Fecha programada: <strong>' . date('d/m/Y', strtotime($fechaVotacion)) . '</strong> desde las <strong>' . $horaApertura . '</strong>.';
    } elseif ($fechaHoy > $fechaVotacion) {
        $mensajeHorario = '🔒 El período de votación ha finalizado.';
    } elseif ($horaActual < $horaApertura) {
        $mensajeHorario = '⏳ La votación abre a las <strong>' . $horaApertura . '</strong>. Vuelve más tarde.';
    } elseif ($horaActual > $horaCierre) {
        $mensajeHorario = '🔒 La votación cerró a las <strong>' . $horaCierre . '</strong>.';
    } else {
        $votacionAbierta = true;
    }
} else {
    $mensajeHorario = '⏳ El administrador aún no ha programado la fecha de votación.';
}

$mensaje = $_SESSION['mensaje_voto'] ?? '';
$error   = $_SESSION['error_voto'] ?? '';
unset($_SESSION['mensaje_voto'], $_SESSION['error_voto']);

$candidatoVotado = null;
$fechaVoto = null;
if ($yaVoto || $mensaje) {
    $stmtVoto = $conn->prepare("SELECT c.nombre, c.partido, c.foto_url, v.fecha_voto FROM votos v JOIN candidatos c ON v.id_candidato = c.id_candidato WHERE v.id_usuario = ? ORDER BY v.id_voto DESC LIMIT 1");
    $stmtVoto->bind_param("i", $user['id']);
    $stmtVoto->execute();
    $res = $stmtVoto->get_result()->fetch_assoc();
    if ($res) {
        $candidatoVotado = $res;
        $fechaVoto = $res['fecha_voto'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votar - Yo Voto Bolivia 2026</title>
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
        .navbar-nav .user-name { color: #FF8C38; font-size: 14px; font-weight: 600; padding: 7px 14px; }
        .navbar-nav .btn-logout { background: rgba(231,76,60,0.15); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.3); }
        .navbar-nav .btn-logout:hover { background: #E74C3C; color: #fff; }
        .main { min-height: 100vh; background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%); padding: 90px 24px 60px; display: flex; flex-direction: column; align-items: center; position: relative; }
        .main::before { content: ''; position: fixed; inset: 0; pointer-events: none; background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 36px 36px; }
        .hero-text { text-align: center; margin-bottom: 32px; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35); color: #FF8C38; padding: 6px 18px; border-radius: 50px; font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
        .hero-text h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 36px; color: #fff; margin-bottom: 8px; }
        .hero-text h1 span { color: #FF6B00; }
        .hero-text p { color: rgba(255,255,255,0.45); font-size: 15px; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; width: 100%; max-width: 960px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4); position: relative; z-index: 1; margin-bottom: 24px; }
        .card-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 22px 28px; display: flex; align-items: center; gap: 16px; }
        .card-head-icon { width: 46px; height: 46px; border-radius: 12px; background: #FF6B00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-head-icon i { font-size: 20px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 18px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 3px 0 0; }
        .card-body { padding: 30px 28px; }
        .alerta-inmutable { background: rgba(255,152,0,0.08); border: 1px solid rgba(255,152,0,0.25); border-left: 4px solid #FF9800; border-radius: 12px; padding: 14px 18px; margin-bottom: 28px; display: flex; align-items: center; gap: 12px; font-size: 13px; color: #FFB74D; }
        .alerta-inmutable i { font-size: 18px; flex-shrink: 0; }
        .candidatos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .candidato-card { background: rgba(255,255,255,0.04); border: 2px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 24px 18px; text-align: center; cursor: pointer; transition: .25s; position: relative; overflow: hidden; }
        .candidato-card:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,107,0,0.4); transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.3); }
        .candidato-card.selected { border-color: #FF6B00; background: rgba(255,107,0,0.08); box-shadow: 0 0 0 4px rgba(255,107,0,0.15); }
        .candidato-card.selected::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; position: absolute; top: 12px; right: 14px; color: #FF6B00; font-size: 16px; }
        .candidato-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 14px; display: block; border: 3px solid rgba(255,107,0,0.5); }
        .candidato-card.selected .candidato-img { border-color: #FF6B00; }
        .candidato-nombre { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 15px; color: #fff; margin-bottom: 4px; }
        .candidato-partido { font-size: 12px; color: #FF8C38; margin-bottom: 6px; }
        .candidato-cargo { font-size: 11px; color: rgba(255,255,255,0.35); }
        .seleccion-info { text-align: center; padding: 14px; border-radius: 12px; background: rgba(255,107,0,0.08); border: 1px solid rgba(255,107,0,0.2); font-size: 14px; color: #FF8C38; font-weight: 700; margin-bottom: 20px; display: none; }
        .seleccion-info.visible { display: block; }
        .btn-votar-big { width: 100%; padding: 16px; border-radius: 12px; border: none; background: #FF6B00; color: #fff; font-family: 'Montserrat', sans-serif; font-size: 16px; font-weight: 800; cursor: pointer; transition: .25s; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 6px 24px rgba(255,107,0,0.35); }
        .btn-votar-big:hover:not(:disabled) { background: #FF8C38; transform: translateY(-2px); }
        .btn-votar-big:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }
        .btn-cancelar { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 14px; color: rgba(255,255,255,0.35); font-size: 13px; text-decoration: none; transition: .2s; }
        .btn-cancelar:hover { color: #ff6b6b; }
        .estado-box { border-radius: 16px; padding: 36px; text-align: center; }
        .estado-box.exito { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); }
        .estado-box.ya-voto { background: rgba(255,152,0,0.08); border: 1px solid rgba(255,152,0,0.2); }
        .estado-box.vacio { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); }
        .estado-box i.icono { font-size: 48px; margin-bottom: 16px; display: block; }
        .estado-box.exito i.icono { color: #5cdb95; }
        .estado-box.ya-voto i.icono { color: #FFB74D; }
        .estado-box h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 20px; margin-bottom: 10px; }
        .estado-box p { color: rgba(255,255,255,0.45); font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .btn-outline-secondary { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.7); padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; border: 1px solid rgba(255,255,255,0.12); transition: .2s; margin: 6px; }
        .btn-outline-secondary:hover { background: rgba(255,255,255,0.12); color: #fff; }
        .certificado-wrap { margin: 24px auto; max-width: 680px; }
        #certificado { background: #fff; border-radius: 12px; padding: 0; box-shadow: 0 8px 32px rgba(0,0,0,0.4); overflow: hidden; font-family: Arial, sans-serif; border: 3px solid #1a3a7a; }
        .cert-header { background: #1a3a7a; color: #fff; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; }
        .cert-header-left { display: flex; align-items: center; gap: 14px; }
        .cert-ted-logo { background: #fff; color: #1a3a7a; font-weight: 900; font-size: 14px; padding: 6px 10px; border-radius: 4px; letter-spacing: 1px; }
        .cert-header-title { text-align: right; }
        .cert-header-title h2 { font-size: 15px; font-weight: 900; letter-spacing: 1px; margin: 0; }
        .cert-header-title p { font-size: 11px; color: rgba(255,255,255,0.7); margin: 3px 0 0; }
        .cert-body { padding: 20px 24px; display: flex; gap: 20px; align-items: flex-start; }
        .cert-foto-col { flex-shrink: 0; text-align: center; }
        .cert-foto { width: 110px; height: 130px; object-fit: cover; border: 2px solid #1a3a7a; display: block; background: #eee; }
        .cert-ci-label { font-size: 10px; color: #555; margin-top: 4px; font-weight: 700; }
        .cert-ci-num { font-size: 13px; color: #1a3a7a; font-weight: 900; }
        .cert-datos { flex: 1; }
        .cert-row { display: flex; gap: 8px; padding: 7px 0; border-bottom: 1px solid #e8e8e8; font-size: 13px; }
        .cert-row:last-child { border-bottom: none; }
        .cert-label { color: #555; font-weight: 700; min-width: 120px; flex-shrink: 0; }
        .cert-valor { color: #111; font-weight: 600; }
        .cert-qr-col { flex-shrink: 0; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; }
        .cert-footer { background: #f5f5f5; border-top: 2px solid #1a3a7a; padding: 10px 24px; display: flex; justify-content: space-between; align-items: center; }
        .cert-footer-text { font-size: 10px; color: #777; }
        .cert-sello { font-size: 10px; color: #1a3a7a; font-weight: 700; text-align: right; }
        footer { background: #070e1f; color: rgba(255,255,255,0.3); text-align: center; padding: 28px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); width: 100%; position: relative; z-index: 1; margin-top: 10px; }
        footer span { color: #FF6B00; font-weight: 700; }
        @media print { body * { visibility: hidden; } #certificado, #certificado * { visibility: visible; } #certificado { position: fixed; top: 0; left: 0; width: 100%; box-shadow: none; border: 2px solid #1a3a7a; } }
        @media (max-width: 768px) { .navbar { padding: 0 16px; } .candidatos-grid { grid-template-columns: 1fr 1fr; } .card-body { padding: 20px 16px; } .cert-body { flex-direction: column; align-items: center; } }
        @media (max-width: 480px) { .candidatos-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></a>
    <nav class="navbar-nav">
        <span class="user-name"><i class="fas fa-user"></i> <?= htmlspecialchars($user['nombres']) ?></span>
        <span style="color:rgba(255,255,255,0.4);font-size:13px;">Votación en proceso...</span>
    </nav>
</header>

<main class="main">
    <div class="hero-text">
        <div class="hero-badge"><i class="fas fa-vote-yea"></i> Elecciones Generales Bolivia 2026</div>
        <h1>Emitir tu <span>Voto</span></h1>
        <p><?= htmlspecialchars($user['nombres']) ?> <?= htmlspecialchars($user['apellidos']) ?> · CI: <?= htmlspecialchars($user['carnet']) ?></p>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-vote-yea"></i></div>
            <div>
                <h2>Selecciona tu Candidato</h2>
                <p>Tu voto es secreto, único e irreversible</p>
            </div>
        </div>
        <div class="card-body">

            <?php if (!$votacionAbierta && !$yaVoto && !$mensaje): ?>
            <div class="estado-box vacio" style="text-align:center;padding:36px;">
                <i class="fas fa-clock icono" style="color:#FF8C38;font-size:48px;margin-bottom:16px;display:block;"></i>
                <h3 style="color:#fff;margin-bottom:10px;">Votación no disponible</h3>
                <p style="color:rgba(255,255,255,0.45);"><?= $mensajeHorario ?></p>
                <a href="/yo_voto/mi-perfil" class="btn-outline-secondary" style="margin-top:16px;"><i class="fas fa-arrow-left"></i> Volver a mi Perfil</a>
            </div>

            <?php elseif ($mensaje || ($yaVoto && $candidatoVotado)): ?>
            <div class="estado-box exito" style="margin-bottom:24px;">
                <i class="fas fa-check-circle icono"></i>
                <h3>¡Voto Registrado Exitosamente!</h3>
                <p>Gracias por participar<br>A continuación tu Certificado de Sufragio.</p>
                <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-top:16px;">
                    <a href="/yo_voto/certificado" style="background:#27AE60;color:#fff;padding:11px 22px;border-radius:10px;font-weight:800;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;"><i class="fas fa-download"></i> Descargar PDF</a>
                    <a href="/yo_voto/mi-perfil" class="btn-outline-secondary"><i class="fas fa-home"></i> Mi Perfil</a>
                </div>
            </div>
            <div class="certificado-wrap">
                <div id="certificado">
                    <div class="cert-header">
                        <div class="cert-header-left">
                            <div class="cert-ted-logo">Yo Voto</div>
                            <div>
                                <div style="font-size:11px;color:rgba(255,255,255,0.7);">Sistema Electoral</div>
                                <div style="font-size:11px;color:rgba(255,255,255,0.7);">Bolivia 2026</div>
                            </div>
                        </div>
                        <div class="cert-header-title">
                            <h2>CERTIFICADO DE SUFRAGIO</h2>
                            <p>Elecciones Generales Bolivia 2026</p>
                            <p><?= date('d') . ' de ' . ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n')-1] . ' de ' . date('Y') ?></p>
                        </div>
                    </div>
                    <div class="cert-body">
                        <div class="cert-foto-col">
                            <img src="/yo_voto/<?= htmlspecialchars($user['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>" class="cert-foto" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                            <div class="cert-ci-label">C.I.</div>
                            <div class="cert-ci-num"><?= htmlspecialchars($user['carnet']) ?></div>
                        </div>
                        <div class="cert-datos">
                            <div class="cert-row"><span class="cert-label">Nombres:</span><span class="cert-valor"><?= htmlspecialchars($user['nombres']) ?></span></div>
                            <div class="cert-row"><span class="cert-label">Apellidos:</span><span class="cert-valor"><?= htmlspecialchars($user['apellidos']) ?></span></div>
                            <div class="cert-row"><span class="cert-label">Fecha Nac.:</span><span class="cert-valor"><?= $user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : '—' ?></span></div>
                            <div class="cert-row"><span class="cert-label">Celular:</span><span class="cert-valor"><?= htmlspecialchars($user['telefono'] ?? '—') ?></span></div>
                            <div class="cert-row"><span class="cert-label">Fecha de Voto:</span><span class="cert-valor"><?= $fechaVoto ? date('d/m/Y', strtotime($fechaVoto)) : date('d/m/Y') ?></span></div>
                            <div class="cert-row"><span class="cert-label">Hora de Voto:</span><span class="cert-valor"><?= $fechaVoto ? date('H:i:s', strtotime($fechaVoto)) : date('H:i:s') ?></span></div>
                        </div>
                        <div class="cert-qr-col">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode('CERTIFICADO DE SUFRAGIO - Yo Voto Bolivia 2026 - CI: ' . $user['carnet'] . ' - Nombre: ' . $user['nombres'] . ' ' . $user['apellidos'] . ' - Fecha: ' . ($fechaVoto ? date('d/m/Y', strtotime($fechaVoto)) : date('d/m/Y')) . ' - Hora: ' . ($fechaVoto ? date('H:i:s', strtotime($fechaVoto)) : '---') . ' - SUFRAGIO VALIDO') ?>" width="100" height="100" alt="QR" style="border:1px solid #ddd;">
                            <div style="font-size:9px;color:#999;margin-top:4px;text-align:center;">Verificación<br>Digital</div>
                        </div>
                    </div>
                    <div class="cert-footer">
                        <div class="cert-footer-text">Generado: <?= date('d/m/Y H:i:s') ?></div>
                        <div class="cert-sello">✓ SUFRAGIO VÁLIDO<br>Sistema Electoral Bolivia 2026</div>
                    </div>
                </div>
            </div>

            <?php elseif ($yaVoto): ?>
            <div class="estado-box exito" style="margin-bottom:24px;">
                <i class="fas fa-check-circle icono"></i>
                <h3>Ya Emitiste tu Voto</h3>
                <p>A continuación tu Certificado de Sufragio.</p>
                <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-top:16px;">
                    <a href="/yo_voto/certificado" style="background:#27AE60;color:#fff;padding:11px 22px;border-radius:10px;font-weight:800;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;"><i class="fas fa-download"></i> Descargar PDF</a>
                    <a href="/yo_voto/mi-perfil" class="btn-outline-secondary"><i class="fas fa-home"></i> Mi Perfil</a>
                </div>
            </div>
            <div class="certificado-wrap">
                <div id="certificado">
                    <div class="cert-header">
                        <div class="cert-header-left">
                            <div class="cert-ted-logo">Yo Voto</div>
                            <div>
                                <div style="font-size:11px;color:rgba(255,255,255,0.7);">Sistema Electoral</div>
                                <div style="font-size:11px;color:rgba(255,255,255,0.7);">Bolivia 2026</div>
                            </div>
                        </div>
                        <div class="cert-header-title">
                            <h2>CERTIFICADO DE SUFRAGIO</h2>
                            <p>Elecciones Generales Bolivia 2026</p>
                            <p><?= date('d') . ' de ' . ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n')-1] . ' de ' . date('Y') ?></p>
                        </div>
                    </div>
                    <div class="cert-body">
                        <div class="cert-foto-col">
                            <img src="/yo_voto/<?= htmlspecialchars($user['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>" class="cert-foto" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                            <div class="cert-ci-label">C.I.</div>
                            <div class="cert-ci-num"><?= htmlspecialchars($user['carnet']) ?></div>
                        </div>
                        <div class="cert-datos">
                            <div class="cert-row"><span class="cert-label">Nombres:</span><span class="cert-valor"><?= htmlspecialchars($user['nombres']) ?></span></div>
                            <div class="cert-row"><span class="cert-label">Apellidos:</span><span class="cert-valor"><?= htmlspecialchars($user['apellidos']) ?></span></div>
                            <div class="cert-row"><span class="cert-label">Fecha Nac.:</span><span class="cert-valor"><?= $user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : '—' ?></span></div>
                            <div class="cert-row"><span class="cert-label">Celular:</span><span class="cert-valor"><?= htmlspecialchars($user['telefono'] ?? '—') ?></span></div>
                            <div class="cert-row"><span class="cert-label">Fecha de Voto:</span><span class="cert-valor"><?= $fechaVoto ? date('d/m/Y', strtotime($fechaVoto)) : date('d/m/Y') ?></span></div>
                            <div class="cert-row"><span class="cert-label">Hora de Voto:</span><span class="cert-valor"><?= $fechaVoto ? date('H:i:s', strtotime($fechaVoto)) : '—' ?></span></div>
                        </div>
                        <div class="cert-qr-col">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode('CERTIFICADO DE SUFRAGIO - Yo Voto Bolivia 2026 - CI: ' . $user['carnet'] . ' - Nombre: ' . $user['nombres'] . ' ' . $user['apellidos'] . ' - Fecha: ' . ($fechaVoto ? date('d/m/Y', strtotime($fechaVoto)) : date('d/m/Y')) . ' - Hora: ' . ($fechaVoto ? date('H:i:s', strtotime($fechaVoto)) : '---') . ' - SUFRAGIO VALIDO') ?>" width="100" height="100" alt="QR" style="border:1px solid #ddd;">
                            <div style="font-size:9px;color:#999;margin-top:4px;text-align:center;">Verificación<br>Digital</div>
                        </div>
                    </div>
                    <div class="cert-footer">
                        <div class="cert-footer-text"> Generado: <?= date('d/m/Y H:i:s') ?></div>
                        <div class="cert-sello">✓ SUFRAGIO VÁLIDO<br>Sistema Electoral Bolivia 2026</div>
                    </div>
                </div>
            </div>

            <?php elseif (empty($candidatos)): ?>
            <div class="estado-box vacio">
                <i class="fas fa-users-slash icono"></i>
                <h3>Sin Candidatos Disponibles</h3>
                <p>No hay candidatos activos para votar.</p>
                <a href="/yo_voto/mi-perfil" class="btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            <?php else: ?>
            <div class="alerta-inmutable">
                <i class="fas fa-shield-alt"></i>
                <div><strong>Importante:</strong> Una vez emitido, tu voto <strong>no podrá ser modificado ni eliminado</strong> Elige con cuidado</div>
            </div>

            <form method="POST" id="votoForm">
                <div class="candidatos-grid">
                    <?php foreach ($candidatos as $c): ?>
                    <div class="candidato-card" onclick="seleccionar(<?= $c['id_candidato'] ?>)" id="card_<?= $c['id_candidato'] ?>">
                        <img src="/yo_voto/<?= htmlspecialchars($c['foto_url']) ?>" class="candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                        <div class="candidato-nombre"><?= htmlspecialchars($c['nombre']) ?></div>
                        <div class="candidato-partido"><?= htmlspecialchars($c['partido']) ?></div>
                        <div class="candidato-cargo">Candidato a <?= htmlspecialchars($c['cargo'] ?? 'Presidente') ?></div>
                        <input type="radio" name="id_candidato" value="<?= $c['id_candidato'] ?>" id="cand_<?= $c['id_candidato'] ?>" style="display:none;">
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="seleccion-info" id="seleccionInfo"></div>

                <button type="button" class="btn-votar-big" id="btnVotar" disabled onclick="abrirModal()">
                    <i class="fas fa-vote-yea"></i> Confirmar y Emitir Voto
                </button>
            </form>

            <?php endif; ?>

        </div>
    </div>
</main>

<footer>
    <p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Tu voto es secreto, seguro e inmutable</p>
</footer>

<!-- MODAL CONFIRMACIÓN CON CONTRASEÑA -->
<div id="modalConfirmar" style="display:none;position:fixed;inset:0;z-index:999;background:rgba(0,10,30,0.9);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#111e3a;border:1px solid rgba(255,255,255,0.1);border-radius:20px;max-width:440px;width:100%;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,0.5);">
        <div style="background:linear-gradient(135deg,#0d2251,#1a3a7a);padding:20px 24px;border-bottom:2px solid #FF6B00;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-family:'Montserrat',sans-serif;font-size:18px;font-weight:800;color:#fff;margin:0;"><i class="fas fa-shield-alt" style="color:#FF6B00;margin-right:8px;"></i> Confirmar Voto</h2>
            <button onclick="cerrarModal()" style="background:rgba(255,255,255,0.1);border:none;color:#fff;width:32px;height:32px;border-radius:8px;font-size:18px;cursor:pointer;">×</button>
        </div>
        <div style="padding:28px;">
            <div style="background:rgba(255,152,0,0.1);border:1px solid rgba(255,152,0,0.3);border-left:4px solid #FF9800;border-radius:10px;padding:14px;margin-bottom:20px;font-size:13px;color:#FFB74D;">
                <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
                <strong>¡Atención!</strong> Este voto es <strong>irreversible</strong>. Ingresa tu contraseña para confirmar.
            </div>
            <div id="candidatoSeleccionadoInfo" style="background:rgba(255,107,0,0.08);border:1px solid rgba(255,107,0,0.2);border-radius:10px;padding:14px;margin-bottom:20px;text-align:center;font-size:14px;color:#FF8C38;font-weight:700;"></div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:700;color:rgba(255,255,255,0.7);margin-bottom:6px;"><i class="fas fa-lock"></i> Tu contraseña</label>
                <input type="password" id="passwordConfirm" placeholder="Ingresa tu contraseña" style="width:100%;padding:11px 14px;border:1.5px solid rgba(255,255,255,0.12);border-radius:10px;font-size:14px;background:rgba(255,255,255,0.07);color:#fff;font-family:inherit;" onkeypress="if(event.key==='Enter')confirmarVoto()">
            </div>
            <div id="errorPassword" style="display:none;background:rgba(231,76,60,0.15);color:#ff6b6b;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:14px;border-left:3px solid #E74C3C;"></div>
            <button onclick="confirmarVoto()" id="btnConfirmarFinal" style="width:100%;padding:14px;background:#FF6B00;color:#fff;border:none;border-radius:12px;font-family:'Montserrat',sans-serif;font-size:15px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;">
                <i class="fas fa-vote-yea"></i> Emitir mi Voto
            </button>
            <button onclick="cerrarModal()" style="width:100%;padding:11px;margin-top:10px;background:none;border:1px solid rgba(255,255,255,0.1);color:rgba(255,255,255,0.4);border-radius:10px;font-size:14px;cursor:pointer;">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    function seleccionar(id) {
        document.querySelectorAll('.candidato-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('card_' + id);
        if (card) {
            card.classList.add('selected');
            document.getElementById('cand_' + id).checked = true;
            const nombre = card.querySelector('.candidato-nombre').innerText;
            const info = document.getElementById('seleccionInfo');
            info.innerHTML = '<i class="fas fa-check-circle"></i> Has seleccionado a: <strong>' + nombre + '</strong>';
            info.classList.add('visible');
            document.getElementById('btnVotar').disabled = false;
        }
    }

    function abrirModal() {
        const sel = document.querySelector('input[name="id_candidato"]:checked');
        if (!sel) return;
        const nombre = document.getElementById('card_' + sel.value)?.querySelector('.candidato-nombre')?.innerText || '';
        document.getElementById('candidatoSeleccionadoInfo').innerHTML = '<i class="fas fa-user"></i> Vas a votar por: <strong>' + nombre + '</strong>';
        document.getElementById('passwordConfirm').value = '';
        document.getElementById('errorPassword').style.display = 'none';
        document.getElementById('modalConfirmar').style.display = 'flex';
        setTimeout(() => document.getElementById('passwordConfirm').focus(), 100);
    }

    function cerrarModal() {
        document.getElementById('modalConfirmar').style.display = 'none';
    }

    async function confirmarVoto() {
        const password = document.getElementById('passwordConfirm').value;
        const errDiv   = document.getElementById('errorPassword');
        const btn      = document.getElementById('btnConfirmarFinal');

        if (!password) {
            errDiv.innerHTML = '⚠️ Ingresa tu contraseña.';
            errDiv.style.display = 'block';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

        try {
            const res = await fetch('/yo_voto/api/verificar-password', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ password: password })
            });
            const r = await res.json();

            if (r.success) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando voto...';
                document.getElementById('votoForm').submit();
            } else {
                errDiv.innerHTML = '❌ Contraseña incorrecta. Intenta de nuevo.';
                errDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-vote-yea"></i> Emitir mi Voto';
                document.getElementById('passwordConfirm').value = '';
                document.getElementById('passwordConfirm').focus();
            }
        } catch(e) {
            errDiv.innerHTML = '❌ Error de conexión.';
            errDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-vote-yea"></i> Emitir mi Voto';
        }
    }

    document.getElementById('modalConfirmar')?.addEventListener('click', function(e) {
        if (e.target === this) cerrarModal();
    });
</script>
</body>
</html>