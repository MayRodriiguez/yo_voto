<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}

require_once 'config/database.php';
require_once 'models/BlockchainVote.php';

$db = new Database();
$conn = $db->getConnection();
$blockchainVote = new BlockchainVote($conn);

$estadisticas = $blockchainVote->getEstadisticas();

$sql = "SELECT u.id, u.numero_registro, u.nombres, u.apellidos, u.carnet,
            v.fecha_voto, b.indice as bloque_indice, b.hash_bloque
        FROM usuarios u
        INNER JOIN votos v ON u.id = v.id_usuario
        LEFT JOIN blockchain_votos b ON b.indice = (
            SELECT MAX(indice) FROM blockchain_votos WHERE JSON_EXTRACT(datos_voto, '$.carnet_hash') = SHA2(CONCAT(u.carnet, 'SALT_SECRETO_VOTO'), 256)
        )
        WHERE u.rol = 'usuario' AND u.ya_voto = 1
        ORDER BY v.fecha_voto DESC";

$votantes = $conn->query($sql);
$totalVotantes = $votantes ? $votantes->num_rows : 0;
$cadenaValida = $blockchainVote->verificarIntegridad();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Blockchain - Yo Voto</title>
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
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-title { font-size:24px; font-weight:900; color:#fff; display:flex; align-items:center; gap:11px; }
        .page-title i { color:#FF6B00; }
        .btn-back { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:9px 16px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:7px; transition:all 0.2s; }
        .btn-back:hover { background:rgba(255,255,255,0.1); color:#fff; }
        .card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:26px; margin-bottom:22px; }
        .card h3 { color:#fff; font-weight:800; font-size:16px; margin-bottom:18px; display:flex; align-items:center; gap:9px; }
        .card h3 i { color:#FF6B00; }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:18px; margin-bottom:22px; }
        .stat-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:22px; text-align:center; }
        .stat-number { font-size:32px; font-weight:900; color:#fff; margin-bottom:6px; }
        .stat-label { font-size:13px; color:rgba(255,255,255,0.35); }
        .badge-valid { background:rgba(39,174,96,0.15); color:#5cdb95; border:1px solid rgba(39,174,96,0.25); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:6px; }
        .badge-invalid { background:rgba(231,76,60,0.15); color:#ff6b6b; border:1px solid rgba(231,76,60,0.2); padding:6px 16px; border-radius:20px; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:6px; }
        .alert-info { background:rgba(25,118,210,0.1); border-left:4px solid #1976D2; color:#7eb3ff; border-radius:10px; padding:14px 18px; font-size:13px; }
        .alert-warning { background:rgba(234,179,8,0.08); border-left:4px solid #facc15; color:#facc15; border-radius:10px; padding:14px 18px; font-size:13px; text-align:center; }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(255,107,0,0.08); }
        th { padding:13px 16px; font-size:11px; font-weight:700; color:#FF8C38; text-transform:uppercase; letter-spacing:0.5px; text-align:left; }
        td { padding:13px 16px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px; color:rgba(255,255,255,0.6); }
        tbody tr:hover { background:rgba(255,255,255,0.03); }
        .hash-text { font-family:monospace; font-size:12px; background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.5); padding:3px 8px; border-radius:5px; }
        .table-responsive { overflow-x:auto; }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); }
        footer span { color:#FF6B00; font-weight:700; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item"><i class="fas fa-gavel"></i> Jurados</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item active"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-vote-yea"></i> Registro de Votaciones</div>
            <a href="/yo_voto/admin/dashboard" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Estado de las Votaciones</h3>
            <div class="stat-grid">

                <div class="stat-card">
                    <div class="stat-number"><?= $estadisticas['total_votos'] ?? 0 ?></div>
                    <div class="stat-label">Votos Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $totalVotantes ?></div>
                    <div class="stat-label">Ciudadanos que Votaron</div>
                </div>

            </div>

        </div>

        <div class="card">
            <h3><i class="fas fa-users"></i> Ciudadanos que ya emitieron su voto</h3>
            <div class="alert-info" style="margin-bottom:18px;">
                <i class="fas fa-shield-alt"></i>
                <strong>Voto Secreto:</strong> Se muestra quién votó, pero NO por quién votó. El voto es anónimo y seguro.
            </div>
            <?php if ($totalVotantes > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-qrcode"></i> N° Registro</th>
                            <th><i class="fas fa-user"></i> Nombre Completo</th>
                            <th><i class="fas fa-id-card"></i> Carnet</th>
                            <th><i class="fas fa-calendar"></i> Fecha de Voto</th>
                            <th><i class="fas fa-link"></i> Hash Blockchain</th>
                            <th><i class="fas fa-cube"></i> Bloque</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($votante = $votantes->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($votante['numero_registro'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($votante['nombres'] . ' ' . $votante['apellidos']) ?></td>
                            <td><?= htmlspecialchars($votante['carnet']) ?></td>
                            <td><?= date('d/m/Y H:i:s', strtotime($votante['fecha_voto'])) ?></td>
                            <td><span class="hash-text" title="<?= htmlspecialchars($votante['hash_bloque'] ?? '') ?>"><?= substr($votante['hash_bloque'] ?? 'N/A', 0, 16) ?>...</span></td>
                            <td>#<?= $votante['bloque_indice'] ?? 'N/A' ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert-warning"><i class="fas fa-info-circle"></i> Aún no hay ciudadanos que hayan votado.</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3><i class="fas fa-cubes"></i> Últimos Bloques Registrados</h3>
            <div id="ultimos-bloques" style="text-align:center;color:rgba(255,255,255,0.3);padding:20px;">
                <i class="fas fa-spinner fa-spin"></i> Cargando...
            </div>
        </div>
    </div>

    <footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Blockchain verificable · Voto secreto garantizado</p></footer>

    <script>
    async function cargarUltimosBloques() {
        try {
            const response = await fetch('/yo_voto/api/blockchain_api.php?action=cadena&limit=10');
            const bloques = await response.json();
            const container = document.getElementById('ultimos-bloques');
            if (!bloques || bloques.length === 0) {
                container.innerHTML = '<div style="color:rgba(255,255,255,0.3);text-align:center;padding:20px;">No hay bloques registrados</div>';
                return;
            }
            let html = '<div style="overflow-x:auto;"><table><thead><tr><th># Bloque</th><th>Timestamp</th><th>Hash</th><th>Hash Anterior</th><th>Tipo</th></tr></thead><tbody>';
            for (let i = bloques.length - 1; i >= 0; i--) {
                const b = bloques[i];
                const esGenesis = b.indice === 0;
                const fecha = new Date(b.timestamp * 1000).toLocaleString();
                html += `<tr>
                    <td><strong>#${b.indice}</strong></td>
                    <td>${fecha}</td>
                    <td><span class="hash-text">${b.hash_bloque.substring(0,20)}...</span></td>
                    <td><span class="hash-text">${b.hash_anterior === '0' ? 'Genesis' : b.hash_anterior.substring(0,20) + '...'}</span></td>
                    <td>${esGenesis ? '<span style="background:rgba(255,107,0,0.15);color:#FF8C38;border:1px solid rgba(255,107,0,0.2);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">Génesis</span>' : '<span style="background:rgba(39,174,96,0.15);color:#5cdb95;border:1px solid rgba(39,174,96,0.2);padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;">Voto</span>'}</td>
                </tr>`;
            }
            html += '</tbody></table></div>';
            container.innerHTML = html;
        } catch (error) {
            document.getElementById('ultimos-bloques').innerHTML = '<div style="color:#ff6b6b;text-align:center;padding:20px;">Error al cargar los bloques</div>';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        cargarUltimosBloques();
        setInterval(cargarUltimosBloques, 30000);
    });
    </script>
</body>
</html>