<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

$totalVotos = $conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
$result = $conn->query("SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY votos_recibidos DESC");
$candidatos = [];
while ($row = $result->fetch_assoc()) { $candidatos[] = $row; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Open Sans',sans-serif; min-height:100vh; background:#0a1628; color:#e2e8f0; }
        .navbar { position:fixed; top:0; left:0; right:0; z-index:100; background:rgba(10,22,50,0.97); backdrop-filter:blur(10px); height:60px; display:flex; align-items:center; padding:0 40px; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family:'Montserrat',sans-serif; font-weight:900; font-size:20px; color:#fff; text-decoration:none; display:flex; align-items:center; gap:8px; }
        .navbar-logo span { color:#FF6B00; }
        .btn-back { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:7px; transition:.2s; }
        .btn-back:hover { background:#FF6B00; color:#fff; border-color:#FF6B00; }
        .main { min-height:100vh; background:linear-gradient(160deg,#0a1628 0%,#0d2251 40%,#1a3a7a 70%,#0d2251 100%); padding:90px 24px 60px; }
        .main::before { content:''; position:fixed; inset:0; pointer-events:none; background-image:radial-gradient(rgba(255,255,255,0.03) 1px,transparent 1px); background-size:36px 36px; }
        .container { max-width:900px; margin:0 auto; position:relative; z-index:1; }
        .hero { text-align:center; margin-bottom:36px; }
        .hero-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(255,107,0,0.12); border:1px solid rgba(255,107,0,0.35); color:#FF8C38; padding:6px 18px; border-radius:50px; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:16px; }
        .hero h1 { font-family:'Montserrat',sans-serif; font-weight:900; font-size:38px; color:#fff; margin-bottom:8px; }
        .hero h1 span { color:#FF6B00; }
        .hero p { color:rgba(255,255,255,0.45); font-size:15px; }
        .card { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:24px; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.4); margin-bottom:24px; }
        .card-head { background:linear-gradient(135deg,#0d2251,#1a3a7a); border-bottom:2px solid #FF6B00; padding:20px 28px; display:flex; align-items:center; gap:14px; }
        .card-head-icon { width:42px; height:42px; border-radius:10px; background:#FF6B00; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .card-head-icon i { font-size:18px; color:#fff; }
        .card-head h2 { font-family:'Montserrat',sans-serif; font-weight:800; font-size:17px; color:#fff; margin:0; }
        .card-head p { font-size:12px; color:rgba(255,255,255,0.4); margin:3px 0 0; }
        .card-body { padding:28px; }
        .total-badge { background:rgba(255,107,0,0.08); border:1px solid rgba(255,107,0,0.2); border-radius:12px; padding:14px 20px; text-align:center; margin-bottom:24px; font-family:'Montserrat',sans-serif; font-weight:800; color:#fff; font-size:18px; }
        .total-badge span { color:#FF6B00; }
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
        .no-data { text-align:center; padding:40px; color:rgba(255,255,255,0.3); }
        .no-data i { font-size:36px; margin-bottom:12px; display:block; color:rgba(255,107,0,0.3); }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); }
        footer span { color:#FF6B00; font-weight:700; }
        @media(max-width:640px){ .navbar{padding:0 16px;} .hero h1{font-size:28px;} .card-body{padding:18px;} }
    </style>
</head>
<body>

<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope" style="color:#FF6B00;"></i> Yo <span>Voto</span></a>
    <a href="/yo_voto/" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
</header>

<main class="main">
    <div class="container">
        <div class="hero">
            <div class="hero-badge"><i class="fas fa-chart-bar"></i> En Vivo</div>
            <h1>Resultados <span>Electorales</span></h1>
            <p>Elecciones Generales Bolivia 2026 · Conteo en tiempo real</p>
        </div>

        <!-- Resultados por candidato -->
        <div class="card">
            <div class="card-head">
                <div class="card-head-icon"><i class="fas fa-chart-bar"></i></div>
                <div><h2>Conteo por Candidato</h2><p>Actualizado en tiempo real</p></div>
            </div>
            <div class="card-body">
                <?php if (empty($candidatos)): ?>
                    <div class="no-data"><i class="fas fa-inbox"></i>No hay candidatos registrados.</div>
                <?php else: ?>
                    <div class="total-badge">Total de votos emitidos: <span><?= $totalVotos ?></span></div>
                    <?php foreach ($candidatos as $i => $c): ?>
                        <?php $pct = $totalVotos > 0 ? round(($c['votos_recibidos'] / $totalVotos) * 100, 1) : 0; ?>
                        <div class="resultado-item">
                            <div class="resultado-top">
                                <div class="resultado-nombre">
                                    <img src="/yo_voto/<?= htmlspecialchars($c['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                                    <?= htmlspecialchars($c['nombre']) ?>
                                    <span style="color:rgba(255,255,255,0.35);font-size:12px;font-weight:400;"><?= htmlspecialchars($c['partido']) ?></span>
                                    <?php if ($i === 0 && $totalVotos > 0): ?>
                                        <span class="winner-badge"><i class="fas fa-trophy"></i> Líder</span>
                                    <?php endif; ?>
                                </div>
                                <div class="resultado-datos"><strong><?= $c['votos_recibidos'] ?></strong> votos · <strong><?= $pct ?>%</strong></div>
                            </div>
                            <div class="barra">
                                <div class="barra-fill" style="width:<?= $pct ?>%">
                                    <?php if ($pct >= 10): ?><span class="barra-pct"><?= $pct ?>%</span><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón volver -->
        <div style="text-align:center;margin-top:10px;">
            <a href="/yo_voto/" style="background:#FF6B00;color:#fff;padding:14px 32px;border-radius:10px;font-family:'Montserrat',sans-serif;font-weight:800;font-size:15px;text-decoration:none;display:inline-flex;align-items:center;gap:9px;box-shadow:0 6px 24px rgba(255,107,0,0.35);">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>
</main>

<footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>
</body>
</html>