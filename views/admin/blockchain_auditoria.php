<?php
// views/admin/blockchain_auditoria.php - Panel de auditoría blockchain
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

// Obtener estadísticas
$estadisticas = $blockchainVote->getEstadisticas();

// Obtener votantes (con información de quién votó, SIN mostrar el voto)
$sql = "SELECT 
            u.id, 
            u.numero_registro, 
            u.nombres, 
            u.apellidos, 
            u.carnet,
            u.fecha_registro,
            v.fecha_voto,
            b.indice as bloque_indice,
            b.hash_bloque,
            b.timestamp as bloque_timestamp
        FROM usuarios u
        INNER JOIN votos v ON u.id = v.id_usuario
        INNER JOIN blockchain_votos b ON b.indice = (
            SELECT MAX(indice) FROM blockchain_votos WHERE JSON_EXTRACT(datos_voto, '$.carnet_hash') = SHA2(CONCAT(u.carnet, 'SALT_SECRETO_VOTO'), 256)
        )
        WHERE u.rol = 'usuario' AND u.ya_voto = 1
        ORDER BY v.fecha_voto DESC";

$votantes = $conn->query($sql);

// Contar total de votantes
$totalVotantes = $votantes ? $votantes->num_rows : 0;

// Verificar integridad de la blockchain
$cadenaValida = $blockchainVote->verificarIntegridad();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría Blockchain - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100%; background: #003399; color: white; z-index: 1000; }
        .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(245,197,24,0.3); }
        .sidebar-header h3 { color: #f5c518; }
        .sidebar-menu-item { padding: 12px 25px; display: flex; align-items: center; gap: 15px; color: white; text-decoration: none; transition: 0.3s; }
        .sidebar-menu-item:hover, .sidebar-menu-item.active { background: rgba(245,197,24,0.2); border-left: 4px solid #f5c518; }
        
        .main-content { margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 15px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { color: #003399; font-size: 24px; font-weight: bold; }
        .btn-back { background: #6c757d; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        
        .card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card h3 { color: #003399; margin-bottom: 20px; font-size: 18px; border-bottom: 2px solid #f5c518; padding-bottom: 10px; }
        
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #e8e0ff, #f8f5ff); border-radius: 15px; padding: 20px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; color: #003399; }
        .stat-label { color: #666; font-size: 14px; }
        
        .badge-valid { background: #4caf50; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; }
        .badge-invalid { background: #dc2626; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; }
        
        .table-votantes { width: 100%; border-collapse: collapse; }
        .table-votantes th { background: #003399; color: white; padding: 12px; text-align: left; }
        .table-votantes td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
        .table-votantes tr:hover { background: #f8f5ff; }
        
        .hash-text { font-family: monospace; font-size: 12px; background: #f0f0f0; padding: 3px 6px; border-radius: 5px; }
        
        footer { text-align: center; padding: 20px; color: white; margin-top: 30px; }
        
        @media (max-width: 768px) { .sidebar { left: -280px; } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item">
            <i class="fas fa-user-check"></i> Gestionar Ciudadanos
        </a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item">
            <i class="fas fa-users"></i> Candidatos
        </a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item">
            <i class="fas fa-gavel"></i> Jurados
        </a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item active">
            <i class="fas fa-link"></i> Auditoría Blockchain
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-link"></i> Auditoría Blockchain
            </div>
            <a href="/yo_voto/admin/dashboard" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Estadísticas de la Blockchain -->
        <div class="card">
            <h3><i class="fas fa-chart-line"></i> Estado de la Blockchain</h3>
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $estadisticas['total_bloques'] ?? 0 ?></div>
                    <div class="stat-label">Total Bloques</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $estadisticas['total_votos'] ?? 0 ?></div>
                    <div class="stat-label">Votos Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $totalVotantes ?></div>
                    <div class="stat-label">Ciudadanos que Votaron</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php if ($cadenaValida): ?>
                            <span class="badge-valid"><i class="fas fa-check-circle"></i> Válida</span>
                        <?php else: ?>
                            <span class="badge-invalid"><i class="fas fa-exclamation-triangle"></i> Corrupta</span>
                        <?php endif; ?>
                    </div>
                    <div class="stat-label">Integridad de la Cadena</div>
                </div>
            </div>
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i> 
                <strong>Información:</strong> La blockchain garantiza la inmutabilidad de los votos. 
                Cada bloque contiene un hash único que lo encadena con el anterior.
            </div>
        </div>

        <!-- Lista de Ciudadanos que Votaron (SIN mostrar el voto) -->
        <div class="card">
            <h3><i class="fas fa-users"></i> Ciudadanos que ya emitieron su voto</h3>
            <p class="text-muted mb-3">
                <i class="fas fa-shield-alt"></i> 
                <strong>Voto Secreto:</strong> Se muestra quién votó, pero NO por quién votó.
                El voto es anónimo y seguro.
            </p>
            
            <?php if ($totalVotantes > 0): ?>
                <div class="table-responsive">
                    <table class="table-votantes">
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
                                    <td>
                                        <span class="hash-text" title="<?= htmlspecialchars($votante['hash_bloque']) ?>">
                                            <?= substr($votante['hash_bloque'], 0, 16) ?>...
                                        </span>
                                    </td>
                                    <td>#<?= $votante['bloque_indice'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center">
                    <i class="fas fa-info-circle"></i> 
                    Aún no hay ciudadanos que hayan votado.
                </div>
            <?php endif; ?>
        </div>

        <!-- Últimos Bloques de la Blockchain -->
        <div class="card">
            <h3><i class="fas fa-cubes"></i> Últimos Bloques Registrados</h3>
            <div id="ultimos-bloques">
                <div class="text-center">Cargando...</div>
            </div>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Blockchain verificable | Voto secreto garantizado</p>
    </footer>

    <script>
        // Cargar últimos bloques de la blockchain
        async function cargarUltimosBloques() {
            try {
                const response = await fetch('/yo_voto/api/blockchain_api.php?action=cadena&limit=10');
                const bloques = await response.json();
                const container = document.getElementById('ultimos-bloques');
                
                if (!bloques || bloques.length === 0) {
                    container.innerHTML = '<div class="alert alert-info">No hay bloques registrados</div>';
                    return;
                }
                
                let html = '<div class="table-responsive">';
                html += '<table class="table-votantes">';
                html += '<thead><tr><th># Bloque</th><th>Timestamp</th><th>Hash</th><th>Hash Anterior</th><th>Tipo</th></tr></thead>';
                html += '<tbody>';
                
                for (let i = bloques.length - 1; i >= 0; i--) {
                    const b = bloques[i];
                    const esGenesis = b.indice === 0;
                    const fecha = new Date(b.timestamp * 1000).toLocaleString();
                    
                    html += `
                        <tr>
                            <td><strong>#${b.indice}</strong></td>
                            <td>${fecha}</td>
                            <td><span class="hash-text">${b.hash_bloque.substring(0, 20)}...</span></td>
                            <td><span class="hash-text">${b.hash_anterior === '0' ? 'Genesis' : b.hash_anterior.substring(0, 20) + '...'}</span></td>
                            <td>${esGenesis ? '<span class="badge-valid">Génesis</span>' : '<span class="badge-valid">Voto</span>'}</td>
                        </tr>
                    `;
                }
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('ultimos-bloques').innerHTML = '<div class="alert alert-danger">Error al cargar los bloques</div>';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            cargarUltimosBloques();
            // Actualizar cada 30 segundos
            setInterval(cargarUltimosBloques, 30000);
        });
    </script>
</body>
</html>