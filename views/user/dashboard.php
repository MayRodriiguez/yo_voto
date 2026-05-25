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