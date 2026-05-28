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

$totalVotos = $conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
$totalUsuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'")->fetch_assoc()['total'];
$participacion = $totalUsuarios > 0 ? round(($totalVotos / $totalUsuarios) * 100, 1) : 0;

$result = $conn->query("SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC");
$candidatos = [];
while ($row = $result->fetch_assoc()) {
    $candidatos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Yo Voto Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Open Sans',sans-serif; background:#0a1628; color:#e2e8f0; min-height:100vh; }

        .sidebar { position:fixed; top:0; left:0; width:255px; height:100%; background:rgba(10,22,50,0.98); border-right:1px solid rgba(255,255,255,0.08); overflow-y:auto; z-index:100; }
        .sidebar-header { padding:26px 22px 18px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .sidebar-header h3 { font-size:20px; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px; }
        .sidebar-header h3 i { color:#FF6B00; font-size:18px; }
        .sidebar-header p { color:rgba(255,255,255,0.3); font-size:11px; margin-top:6px; margin-left:28px; letter-spacing:1px; }
        .sidebar-menu-item { display:flex; align-items:center; gap:11px; padding:11px 22px; color:rgba(255,255,255,0.45); text-decoration:none; font-size:14px; font-weight:600; border-left:3px solid transparent; transition:.2s; }
        .sidebar-menu-item i { width:17px; text-align:center; font-size:14px; }
        .sidebar-menu-item:hover { color:#fff; background:rgba(255,255,255,0.06); border-left-color:rgba(255,107,0,0.4); }
        .sidebar-menu-item.active { color:#FF6B00; background:rgba(255,107,0,0.08); border-left-color:#FF6B00; font-weight:700; }

        .main-content { margin-left:255px; padding:30px; min-height:100vh; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:14px; }
        .page-title { font-size:24px; font-weight:900; color:#fff; display:flex; align-items:center; gap:11px; }
        .page-title i { color:#FF6B00; }
        .user-info { display:flex; align-items:center; gap:12px; }
        .user-name { font-size:14px; color:#FF8C38; font-weight:600; }
        .btn-logout { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:8px 15px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; transition:.2s; display:inline-flex; align-items:center; gap:7px; }
        .btn-logout:hover { background:#FF6B00; color:#fff; border-color:#FF6B00; }

        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:26px; }
        .stat-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:24px; transition:.2s; }
        .stat-card:hover { transform:translateY(-3px); border-color:rgba(255,107,0,0.3); }
        .stat-icon { font-size:26px; color:#FF6B00; margin-bottom:12px; display:block; }
        .stat-number { font-size:32px; font-weight:900; color:#fff; margin-bottom:4px; }
        .stat-label { font-size:13px; color:rgba(255,255,255,0.35); }

        .card-box { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:26px; margin-bottom:22px; }
        .card-box h3 { color:#fff; font-weight:800; margin-bottom:18px; font-size:16px; display:flex; align-items:center; gap:9px; }
        .card-box h3 i { color:#FF6B00; }

        .resultado-item { margin-bottom:20px; }
        .resultado-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:6px; }
        .resultado-nombre { font-weight:800; color:#fff; font-size:15px; display:flex; align-items:center; gap:10px; }
        .resultado-nombre img { width:36px; height:36px; border-radius:50%; object-fit:cover; border:2px solid #FF6B00; }
        .resultado-datos { font-size:13px; color:rgba(255,255,255,0.45); }
        .resultado-datos strong { color:#FF8C38; }
        .barra { height:14px; background:rgba(255,255,255,0.07); border-radius:10px; overflow:hidden; }
        .barra-fill { height:100%; background:linear-gradient(90deg,#FF6B00,#FF8C38); border-radius:10px; transition:width 1.2s ease; display:flex; align-items:center; justify-content:flex-end; padding-right:8px; min-width:30px; }
        .barra-pct { font-size:10px; font-weight:800; color:#fff; }
        .winner-badge { background:rgba(255,107,0,0.2); border:1px solid rgba(255,107,0,0.4); color:#FF8C38; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

        .no-data { text-align:center; padding:40px; color:rgba(255,255,255,0.3); font-size:15px; }
        .no-data i { font-size:36px; margin-bottom:12px; display:block; color:rgba(255,107,0,0.3); }

        .refresh-btn { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.1); color:rgba(255,255,255,0.6); padding:8px 16px; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:7px; text-decoration:none; transition:.2s; }
        .refresh-btn:hover { background:rgba(255,255,255,0.1); color:#fff; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
        <p>Sistema Electoral Bolivia 2026</p>
    </div>
    <div class="sidebar-menu">
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item"><i class="fas fa-user-plus"></i><span>Registro Ciudadano</span></a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i><span>Candidatos</span></a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item"><i class="fas fa-gavel"></i><span>Jurados Electorales</span></a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item active"><i class="fas fa-chart-bar"></i><span>Resultados</span></a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-link"></i><span>Auditoría Blockchain</span></a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title"><i class="fas fa-chart-bar"></i> Resultados Electorales</div>
        <div class="user-info">
            <span class="user-name"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
            <a href="javascript:location.reload()" class="refresh-btn"><i class="fas fa-sync-alt"></i> Actualizar</a>
            <a href="/yo_voto/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-vote-yea"></i></div>
            <div class="stat-number"><?= $totalVotos ?></div>
            <div class="stat-label">Votos Emitidos</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?= $totalUsuarios ?></div>
            <div class="stat-label">Ciudadanos Registrados</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-percent"></i></div>
            <div class="stat-number"><?= $participacion ?>%</div>
            <div class="stat-label">Participación Electoral</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-flag-checkered"></i></div>
            <div class="stat-number"><?= count($candidatos) ?></div>
            <div class="stat-label">Candidatos Activos</div>
        </div>
    </div>

    <!-- Resultados por candidato -->
    <div class="card-box">
        <h3><i class="fas fa-chart-bar"></i> Conteo por Candidato</h3>
        <?php if (empty($candidatos)): ?>
            <div class="no-data">
                <i class="fas fa-inbox"></i>
                No hay candidatos activos registrados.
            </div>
        <?php else: ?>
            <?php foreach ($candidatos as $i => $c): ?>
                <?php $pct = $totalVotos > 0 ? round(($c['votos_recibidos'] / $totalVotos) * 100, 1) : 0; ?>
                <div class="resultado-item">
                    <div class="resultado-top">
                        <div class="resultado-nombre">
                            <img src="/yo_voto/<?= htmlspecialchars($c['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>"
                                 onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                            <?= htmlspecialchars($c['nombre']) ?>
                            <span style="color:rgba(255,255,255,0.35);font-size:12px;font-weight:400;"><?= htmlspecialchars($c['partido']) ?></span>
                            <?php if ($i === 0 && $totalVotos > 0): ?>
                                <span class="winner-badge"><i class="fas fa-trophy"></i> Líder</span>
                            <?php endif; ?>
                        </div>
                        <div class="resultado-datos">
                            <strong><?= $c['votos_recibidos'] ?></strong> votos · <strong><?= $pct ?>%</strong>
                        </div>
                    </div>
                    <div class="barra">
                        <div class="barra-fill" style="width:<?= $pct ?>%">
                            <?php if ($pct >= 10): ?>
                                <span class="barra-pct"><?= $pct ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tabla detallada -->
    <?php if (!empty($candidatos)): ?>
    <div class="card-box">
        <h3><i class="fas fa-table"></i> Tabla Detallada</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead style="background:rgba(255,107,0,0.08);">
                <tr>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:left;">#</th>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:left;">Candidato</th>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:left;">Partido</th>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:left;">Cargo</th>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:right;">Votos</th>
                    <th style="padding:13px 16px;font-size:12px;font-weight:700;color:#FF8C38;text-align:right;">%</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidatos as $i => $c): ?>
                    <?php $pct = $totalVotos > 0 ? round(($c['votos_recibidos'] / $totalVotos) * 100, 1) : 0; ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:14px 16px;color:rgba(255,255,255,0.4);font-size:13px;"><?= $i + 1 ?></td>
                        <td style="padding:14px 16px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="/yo_voto/<?= htmlspecialchars($c['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>"
                                     onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'"
                                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #FF6B00;">
                                <span style="font-weight:700;color:#fff;"><?= htmlspecialchars($c['nombre']) ?></span>
                            </div>
                        </td>
                        <td style="padding:14px 16px;color:#FF8C38;font-size:13px;"><?= htmlspecialchars($c['partido']) ?></td>
                        <td style="padding:14px 16px;color:rgba(255,255,255,0.5);font-size:13px;"><?= htmlspecialchars($c['cargo']) ?></td>
                        <td style="padding:14px 16px;text-align:right;font-weight:800;color:#fff;"><?= $c['votos_recibidos'] ?></td>
                        <td style="padding:14px 16px;text-align:right;color:#FF8C38;font-weight:700;"><?= $pct ?>%</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
