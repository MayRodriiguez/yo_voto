<?php
// views/admin/dashboard.php
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

// Obtener estadísticas
$totalUsuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'usuario'")->fetch_assoc()['total'];
$totalVotos = $conn->query("SELECT COUNT(*) as total FROM votos")->fetch_assoc()['total'];
$totalCandidatos = $conn->query("SELECT COUNT(*) as total FROM candidatos WHERE estado = 'activo'")->fetch_assoc()['total'];
$totalJurados = $conn->query("SELECT COUNT(*) as total FROM jurados")->fetch_assoc()['total'];
$participacion = $totalUsuarios > 0 ? round(($totalVotos / $totalUsuarios) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100%;
            background: #003399;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(245,197,24,0.3);
        }

        .sidebar-header h3 {
            color: #f5c518;
            font-size: 24px;
            margin: 0;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .sidebar-menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar-menu-item:hover {
            background: rgba(245,197,24,0.2);
            border-left-color: #f5c518;
        }

        .sidebar-menu-item.active {
            background: rgba(245,197,24,0.3);
            border-left-color: #f5c518;
        }

        .sidebar-menu-item i {
            width: 25px;
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 20px;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-title {
            color: #003399;
            font-size: 24px;
            font-weight: bold;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-name {
            color: #003399;
            font-weight: bold;
        }

        .btn-logout {
            background: #dc2626;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: #b91c1c;
        }

        /* Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #003399, #1a5bc4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .stat-icon i {
            font-size: 30px;
            color: #f5c518;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #003399;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Módulos Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .module-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-decoration: none;
            display: block;
        }

        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .module-header {
            background: linear-gradient(135deg, #003399, #1a5bc4);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .module-header i {
            font-size: 40px;
            color: #f5c518;
        }

        .module-header h3 {
            margin: 0;
            font-size: 20px;
        }

        .module-body {
            padding: 20px;
            color: #666;
        }

        .module-footer {
            padding: 15px 20px;
            background: #f8f5ff;
            border-top: 1px solid #ede9fe;
            color: #003399;
            font-weight: bold;
        }

        /* System Status */
        .system-status {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-top: 30px;
        }

        .system-status h3 {
            color: #003399;
            margin-bottom: 15px;
        }

        .status-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: #f8f5ff;
            border-radius: 10px;
        }

        .status-badge {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .status-badge.active {
            background: #4caf50;
            box-shadow: 0 0 5px #4caf50;
        }

        .status-badge.warning {
            background: #ff9800;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
            }
            .main-content {
                margin-left: 0;
            }
        }

        footer {
            text-align: center;
            padding: 20px;
            color: white;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia 2026</p>
        </div>
        <div class="sidebar-menu">
            <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/yo_voto/admin/registro" class="sidebar-menu-item">
                <i class="fas fa-user-plus"></i>
                <span>Registro Ciudadano</span>
            </a>
            <a href="/yo_voto/candidatos" class="sidebar-menu-item">
                <i class="fas fa-users"></i>
                <span>Candidatos</span>
            </a>
            <a href="/yo_voto/jurados" class="sidebar-menu-item">
                <i class="fas fa-gavel"></i>
                <span>Jurados Electorales</span>
            </a>
            <a href="/yo_voto/procesos" class="sidebar-menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Procesos</span>
            </a>
            <a href="/yo_voto/resultados-admin" class="sidebar-menu-item">
                <i class="fas fa-chart-bar"></i>
                <span>Resultados</span>
            </a>
            <a href="/yo_voto/admin/blockchain" class="module-card">
    <div class="module-header">
        <i class="fas fa-link"></i>
        <h3>Auditoría Blockchain</h3>
    </div>
    <div class="module-body">
        <p>Verificar integridad de la cadena de bloques y votantes registrados.</p>
    </div>
    <div class="module-footer">
        <i class="fas fa-arrow-right"></i> Verificar blockchain
    </div>
</a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-tachometer-alt"></i> Panel de Control Principal
            </div>
            <div class="user-info">
                <span class="user-name">
                    <i class="fas fa-user-shield"></i> Bienvenido, <?= htmlspecialchars($_SESSION['user']['nombres']) ?>
                </span>
                <a href="/yo_voto/logout" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $totalUsuarios ?></div>
                <div class="stat-label">Ciudadanos Registrados</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-vote-yea"></i>
                </div>
                <div class="stat-number"><?= $totalVotos ?></div>
                <div class="stat-label">Votos Emitidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-number"><?= $totalCandidatos ?></div>
                <div class="stat-label">Candidatos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <div class="stat-number"><?= $totalJurados ?></div>
                <div class="stat-label">Jurados Designados</div>
            </div>
        </div>

        <!-- Módulos Grid -->
        <div class="modules-grid">
            <a href="/yo_voto/admin/registro" class="module-card">
                <div class="module-header">
                    <i class="fas fa-user-plus"></i>
                    <h3>Gestión de Usuarios</h3>
                </div>
                <div class="module-body">
                    <p>Registro de ciudadanos, gestión biométrica y habilitaciones de voto.</p>
                </div>
                <div class="module-footer">
                    <i class="fas fa-arrow-right"></i> Acceder al módulo
                </div>
            </a>

            <a href="/yo_voto/candidatos" class="module-card">
                <div class="module-header">
                    <i class="fas fa-users"></i>
                    <h3>Información de Candidatos</h3>
                </div>
                <div class="module-body">
                    <p>Administración de candidatos, partidos políticos y listas de postulación.</p>
                </div>
                <div class="module-footer">
                    <i class="fas fa-arrow-right"></i> Acceder al módulo
                </div>
            </a>

            <a href="/yo_voto/jurados" class="module-card">
                <div class="module-header">
                    <i class="fas fa-gavel"></i>
                    <h3>Gestión de Jurados</h3>
                </div>
                <div class="module-body">
                    <p>Asignación de jurados electorales y gestión de mesas de votación.</p>
                </div>
                <div class="module-footer">
                    <i class="fas fa-arrow-right"></i> Acceder al módulo
                </div>
            </a>
        </div>

        <!-- System Status -->
        <div class="system-status">
            <h3><i class="fas fa-server"></i> Estado del Sistema</h3>
            <div class="status-items">
                <div class="status-item">
                    <div class="status-badge active"></div>
                    <span>Base de Datos: Conectada</span>
                </div>
                <div class="status-item">
                    <div class="status-badge active"></div>
                    <span>Módulo Biométrico: Activo</span>
                </div>
                <div class="status-item">
                    <div class="status-badge active"></div>
                    <span>Sistema de Votación: Operativo</span>
                </div>
                <div class="status-item">
                    <div class="status-badge warning"></div>
                    <span>Última sincronización: Hoy 20:30</span>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p><i class="fas fa-gavel"></i> Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>
</body>
</html>