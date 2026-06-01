<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}

require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

// Acción habilitar/deshabilitar votación
$mensajeAccion = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion_votacion'])) {
        $nuevoEstado = $_POST['accion_votacion'] === 'habilitar' ? 1 : 0;
        $conn->query("UPDATE usuarios SET habilitado_voto = $nuevoEstado WHERE rol = 'usuario'");
        $mensajeAccion = $nuevoEstado ? '✅ Votación habilitada para todos los ciudadanos.' : '🔒 Votación deshabilitada para todos los ciudadanos.';
    }
    if (isset($_POST['fecha_votacion'])) {
        $fecha    = $conn->real_escape_string($_POST['fecha_votacion']);
        $apertura = $conn->real_escape_string($_POST['hora_apertura']);
        $cierre   = $conn->real_escape_string($_POST['hora_cierre']);
        $conn->query("UPDATE configuracion SET valor = '$fecha'    WHERE clave = 'fecha_votacion'");
        $conn->query("UPDATE configuracion SET valor = '$apertura' WHERE clave = 'hora_apertura'");
        $conn->query("UPDATE configuracion SET valor = '$cierre'   WHERE clave = 'hora_cierre'");
        $mensajeAccion = '✅ Configuración de votación guardada correctamente.';
    }
}

$totalUsuarios   = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'")->fetch_assoc()['total'];
$totalVotos      = $conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
$totalCandidatos = $conn->query("SELECT COUNT(*) as total FROM candidatos WHERE estado = 'activo'")->fetch_assoc()['total'];
$habilitados     = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario' AND habilitado_voto = 1")->fetch_assoc()['total'];

// Obtener configuración actual
$config = [];
$resConfig = $conn->query("SELECT clave, valor FROM configuracion");
while ($row = $resConfig->fetch_assoc()) { $config[$row['clave']] = $row['valor']; }
$fechaVotacion = $config['fecha_votacion'] ?? '';
$horaApertura  = $config['hora_apertura']  ?? '08:00';
$horaCierre    = $config['hora_cierre']    ?? '16:00';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Yo Voto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Open Sans', sans-serif; background: #0a1628; color: #e2e8f0; min-height: 100vh; }
        .sidebar { position: fixed; top:0; left:0; width:255px; height:100%; background: rgba(10,22,50,0.98); border-right:1px solid rgba(255,255,255,0.08); overflow-y:auto; z-index:100; }
        .sidebar-header { padding:26px 22px 18px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .sidebar-header h3 { font-size:20px; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px; }
        .sidebar-header h3 i { color:#FF6B00; }
        .sidebar-header p { color:rgba(255,255,255,0.3); font-size:11px; margin-top:6px; margin-left:28px; letter-spacing:1px; }
        .sidebar-menu-item { display:flex; align-items:center; gap:11px; padding:11px 22px; color:rgba(255,255,255,0.45); text-decoration:none; font-size:14px; font-weight:600; border-left:3px solid transparent; transition:all 0.2s; }
        .sidebar-menu-item:hover { color:#fff; background:rgba(255,255,255,0.06); border-left-color:rgba(255,107,0,0.4); }
        .sidebar-menu-item.active { color:#FF6B00; background:rgba(255,107,0,0.08); border-left-color:#FF6B00; font-weight:700; }
        .main-content { margin-left:255px; padding:30px; min-height:100vh; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:14px; }
        .page-title { font-size:24px; font-weight:900; color:#fff; display:flex; align-items:center; gap:11px; }
        .page-title i { color:#FF6B00; }
        .user-info { display:flex; align-items:center; gap:12px; }
        .user-name { font-size:14px; color:#FF8C38; font-weight:600; }
        .btn-logout { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:8px 15px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; transition:all 0.2s; display:inline-flex; align-items:center; gap:7px; }
        .btn-logout:hover { background:#FF6B00; color:#fff; border-color:#FF6B00; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:18px; margin-bottom:26px; }
        .stat-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:24px; transition:transform 0.2s,border-color 0.2s; }
        .stat-card:hover { transform:translateY(-3px); border-color:rgba(255,107,0,0.3); }
        .stat-icon { font-size:26px; color:#FF6B00; margin-bottom:12px; display:block; }
        .stat-number { font-size:32px; font-weight:900; color:#fff; margin-bottom:4px; }
        .stat-label { font-size:13px; color:rgba(255,255,255,0.35); }
        .modules-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px; margin-bottom:22px; }
        .module-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:24px; text-decoration:none; transition:all 0.25s; display:flex; flex-direction:column; gap:10px; }
        .module-card:hover { background:rgba(255,255,255,0.08); border-color:rgba(255,107,0,0.3); transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,0,0,0.3); }
        .module-header { display:flex; align-items:center; gap:10px; }
        .module-header i { font-size:20px; color:#FF6B00; }
        .module-header h3 { font-size:15px; font-weight:800; color:#fff; margin:0; }
        .module-body p { font-size:13px; color:rgba(255,255,255,0.4); line-height:1.5; }
        .module-footer { font-size:12px; color:#FF6B00; font-weight:700; }
        .system-status { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:22px; }
        .system-status h3 { color:#fff; font-size:15px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:9px; }
        .system-status h3 i { color:#FF6B00; }
        .status-items { display:flex; flex-direction:column; gap:10px; }
        .status-item { display:flex; align-items:center; gap:10px; font-size:13px; color:rgba(255,255,255,0.45); }
        .status-badge { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .status-badge.active { background:#5cdb95; box-shadow:0 0 6px #27AE60; }
        .status-badge.warning { background:#facc15; }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); }
        footer span { color:#FF6B00; font-weight:700; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia 2026</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item active"><i class="fas fa-tachometer-alt"></i> Panel Principal</a>
        <a href="/yo_voto/admin/ciudadanos" class="sidebar-menu-item"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-tachometer-alt"></i> Panel de Administración</div>
            <div class="user-info">
                <span class="user-name"><i class="fas fa-user-shield"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
                <a href="/yo_voto/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?= $totalUsuarios ?></div>
                <div class="stat-label">Ciudadanos Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-vote-yea"></i></div>
                <div class="stat-number"><?= $totalVotos ?></div>
                <div class="stat-label">Votos Emitidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                <div class="stat-number"><?= $totalCandidatos ?></div>
                <div class="stat-label">Candidatos Activos</div>
            </div>
        </div>

        <!-- Control de Votación -->
        <?php if ($mensajeAccion): ?>
        <div style="background:rgba(39,174,96,0.12);border-left:4px solid #27AE60;color:#5cdb95;border-radius:10px;padding:14px 18px;margin-bottom:22px;font-size:14px;">
            <?= $mensajeAccion ?>
        </div>
        <?php endif; ?>

        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:26px;margin-bottom:22px;">
            <h3 style="color:#fff;font-weight:800;font-size:16px;margin-bottom:6px;display:flex;align-items:center;gap:9px;">
                <i class="fas fa-toggle-on" style="color:#FF6B00;"></i> Control de Votación
            </h3>
            <p style="color:rgba(255,255,255,0.4);font-size:13px;margin-bottom:20px;">
                Ciudadanos habilitados actualmente: <strong style="color:#FF8C38;"><?= $habilitados ?> de <?= $totalUsuarios ?></strong>
            </p>
            <form method="POST" style="display:flex;gap:14px;flex-wrap:wrap;">
                <button type="submit" name="accion_votacion" value="habilitar"
                    style="background:#27AE60;color:#fff;border:none;padding:12px 24px;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;"
                    onmouseover="this.style.background='#2ecc71'" onmouseout="this.style.background='#27AE60'"
                    onclick="return confirm('¿Habilitar votación para TODOS los ciudadanos?')">
                    <i class="fas fa-unlock"></i> Habilitar Votación para Todos
                </button>
                <button type="submit" name="accion_votacion" value="deshabilitar"
                    style="background:rgba(231,76,60,0.15);color:#ff6b6b;border:1px solid rgba(231,76,60,0.3);padding:12px 24px;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:8px;transition:all 0.2s;"
                    onmouseover="this.style.background='rgba(231,76,60,0.3)'" onmouseout="this.style.background='rgba(231,76,60,0.15)'"
                    onclick="return confirm('¿Deshabilitar votación para TODOS los ciudadanos?')">
                    <i class="fas fa-lock"></i> Deshabilitar Votación
                </button>
            </form>
        </div>

        <!-- Configuración de Horario -->
        <div style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:26px;margin-bottom:22px;">
            <h3 style="color:#fff;font-weight:800;font-size:16px;margin-bottom:18px;display:flex;align-items:center;gap:9px;">
                <i class="fas fa-clock" style="color:#FF6B00;"></i> Configuración de Horario de Votación
            </h3>
            <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;align-items:end;">
                <div>
                    <label style="color:rgba(255,255,255,0.6);font-size:13px;font-weight:700;display:block;margin-bottom:6px;"><i class="fas fa-calendar"></i> Fecha de Votación</label>
                    <input type="date" name="fecha_votacion" value="<?= htmlspecialchars($fechaVotacion) ?>"
                        style="width:100%;padding:11px 14px;border:1.5px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;background:rgba(255,255,255,0.07);color:#fff;font-family:inherit;">
                </div>
                <div>
                    <label style="color:rgba(255,255,255,0.6);font-size:13px;font-weight:700;display:block;margin-bottom:6px;"><i class="fas fa-door-open"></i> Hora de Apertura</label>
                    <input type="time" name="hora_apertura" value="<?= htmlspecialchars($horaApertura) ?>"
                        style="width:100%;padding:11px 14px;border:1.5px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;background:rgba(255,255,255,0.07);color:#fff;font-family:inherit;">
                </div>
                <div>
                    <label style="color:rgba(255,255,255,0.6);font-size:13px;font-weight:700;display:block;margin-bottom:6px;"><i class="fas fa-door-closed"></i> Hora de Cierre</label>
                    <input type="time" name="hora_cierre" value="<?= htmlspecialchars($horaCierre) ?>"
                        style="width:100%;padding:11px 14px;border:1.5px solid rgba(255,255,255,0.1);border-radius:10px;font-size:14px;background:rgba(255,255,255,0.07);color:#fff;font-family:inherit;">
                </div>
                <div>
                    <button type="submit" style="width:100%;padding:12px;background:#FF6B00;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
            <?php if ($fechaVotacion): ?>
            <div style="margin-top:14px;font-size:13px;color:rgba(255,255,255,0.4);">
                <i class="fas fa-info-circle" style="color:#FF6B00;"></i>
                Votación programada: <strong style="color:#FF8C38;"><?= date('d/m/Y', strtotime($fechaVotacion)) ?></strong>
                de <strong style="color:#FF8C38;"><?= $horaApertura ?></strong> a <strong style="color:#FF8C38;"><?= $horaCierre ?></strong>
            </div>
            <?php endif; ?>
        </div>

    </div>

    <footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>
</body>
</html>