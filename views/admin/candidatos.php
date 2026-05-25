<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Candidatos - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        
        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100%; background: #003399; color: white; z-index: 1000; }
        .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(245,197,24,0.3); }
        .sidebar-header h3 { color: #f5c518; }
        .sidebar-menu-item { padding: 12px 25px; display: flex; align-items: center; gap: 15px; color: white; text-decoration: none; transition: 0.3s; }
        .sidebar-menu-item:hover, .sidebar-menu-item.active { background: rgba(245,197,24,0.2); border-left: 4px solid #f5c518; }
        
        /* Main Content */
        .main-content { margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 15px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { color: #003399; font-size: 24px; font-weight: bold; }
        .btn-add { background: #f5c518; color: #003399; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .btn-add:hover { background: #ffdd44; transform: scale(1.02); }
        
        /* Alertas */
        .alert-success { background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        /* Grid de candidatos */
        .candidatos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .candidato-card { background: white; border-radius: 20px; overflow: hidden; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .candidato-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .candidato-img { width: 100%; height: 220px; object-fit: cover; }
        .candidato-info { padding: 20px; text-align: center; }
        .candidato-nombre { font-size: 20px; font-weight: bold; color: #003399; margin-bottom: 5px; }
        .candidato-partido { color: #1a5bc4; font-size: 14px; margin-bottom: 5px; }
        .candidato-cargo { color: #666; font-size: 12px; margin-bottom: 10px; }
        .badge-activo { background: #4caf50; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .badge-inactivo { background: #dc2626; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; display: inline-block; }
        
        /* Botones de acción */
        .btn-actions { display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap; }
        .btn-propuestas { background: #003399; color: white; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; text-align: center; flex: 1; transition: 0.3s; }
        .btn-propuestas:hover { background: #1a5bc4; color: white; }
        .btn-equipo { background: #f5c518; color: #003399; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; text-align: center; flex: 1; transition: 0.3s; }
        .btn-equipo:hover { background: #ffdd44; }
        .btn-editar { background: #ff9800; color: white; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 12px; text-align: center; flex: 1; transition: 0.3s; }
        .btn-editar:hover { background: #e68900; }
        .btn-toggle { background: #6c757d; color: white; padding: 8px 12px; border-radius: 8px; border: none; font-size: 12px; cursor: pointer; transition: 0.3s; flex: 1; }
        .btn-toggle:hover { background: #5a6268; }
        .btn-eliminar { background: #dc2626; color: white; padding: 8px 12px; border-radius: 8px; border: none; font-size: 12px; cursor: pointer; transition: 0.3s; flex: 1; }
        .btn-eliminar:hover { background: #b91c1c; }
        
        footer { text-align: center; padding: 20px; color: white; margin-top: 30px; }
        
        @media (max-width: 768px) {
            .sidebar { left: -280px; }
            .main-content { margin-left: 0; }
        }
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
            <i class="fas fa-user-plus"></i> Registro Ciudadano
        </a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item active">
            <i class="fas fa-users"></i> Candidatos
        </a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item">
            <i class="fas fa-gavel"></i> Jurados
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-users"></i> Gestión de Candidatos
            </div>
            <a href="/yo_voto/candidatos/agregar" class="btn-add">
                <i class="fas fa-plus"></i> Añadir Candidato
            </a>
        </div>

        <!-- Mostrar mensajes -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje'] ?>
                <?php unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="candidatos-grid">
            <?php if (empty($candidatos)): ?>
                <p style="color: white; text-align: center; grid-column: 1/-1;">No hay candidatos registrados. <a href="/yo_voto/candidatos/agregar" style="color: #f5c518;">Agregar el primero</a></p>
            <?php else: ?>
                <?php foreach ($candidatos as $candidato): ?>
                    <div class="candidato-card">
                        <img src="/yo_voto/<?= $candidato['foto_url'] ?>" class="candidato-img" 
                             onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                        <div class="candidato-info">
                            <div class="candidato-nombre"><?= htmlspecialchars($candidato['nombre']) ?></div>
                            <div class="candidato-partido"><?= htmlspecialchars($candidato['partido']) ?></div>
                            <div class="candidato-cargo">Candidato a <?= htmlspecialchars($candidato['cargo']) ?></div>
                            <div class="mt-2">
                                <span class="<?= $candidato['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                    <?= $candidato['estado'] === 'activo' ? '✓ Habilitado' : '✗ Inhabilitado' ?>
                                </span>
                            </div>
                            
                            <!-- Botones de acción  -->
                            <div class="btn-actions">
                                <a href="/yo_voto/propuestas/<?= $candidato['id_candidato'] ?>" class="btn-propuestas">
                                    <i class="fas fa-list-check"></i> Propuestas
                                </a>
                                <a href="/yo_voto/equipo/<?= $candidato['id_candidato'] ?>" class="btn-equipo">
                                    <i class="fas fa-users"></i> Equipo
                                </a>
                            </div>
                            <div class="btn-actions">
                                <a href="/yo_voto/candidatos/editar/<?= $candidato['id_candidato'] ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <button class="btn-toggle" onclick="toggleEstado(<?= $candidato['id_candidato'] ?>, '<?= $candidato['estado'] ?>')">
                                    <i class="fas fa-toggle-on"></i> Estado
                                </button>
                                <button class="btn-eliminar" onclick="eliminarCandidato(<?= $candidato['id_candidato'] ?>, '<?= htmlspecialchars($candidato['nombre']) ?>')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script>
        function toggleEstado(id, estadoActual) {
            const nuevoEstado = estadoActual === 'activo' ? 'inactivar' : 'activar';
            if (confirm(`¿${nuevoEstado === 'activar' ? 'Activar' : 'Inactivar'} este candidato?`)) {
                window.location.href = `/yo_voto/candidatos/toggle/${id}`;
            }
        }
        
        function eliminarCandidato(id, nombre) {
            if (confirm(`¿Eliminar permanentemente a "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `/yo_voto/candidatos/eliminar/${id}`;
            }
        }
    </script>
</body>
</html>