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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Open Sans', sans-serif; background: #0a1628; color: #e2e8f0; min-height: 100vh; }
        .sidebar { position: fixed; top: 0; left: 0; width: 255px; height: 100%; background: rgba(10,22,50,0.98); border-right: 1px solid rgba(255,255,255,0.08); overflow-y: auto; z-index: 100; }
        .sidebar-header { padding: 26px 22px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-header h3 { font-size: 20px; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 10px; }
        .sidebar-header h3 i { color: #FF6B00; font-size: 18px; }
        .sidebar-header p { color: rgba(255,255,255,0.3); font-size: 11px; margin-top: 6px; margin-left: 28px; letter-spacing: 1px; }
        .sidebar-menu-item { display: flex; align-items: center; gap: 11px; padding: 11px 22px; color: rgba(255,255,255,0.45); text-decoration: none; font-size: 14px; font-weight: 600; border-left: 3px solid transparent; transition: all 0.2s; }
        .sidebar-menu-item i { width: 17px; text-align: center; font-size: 14px; }
        .sidebar-menu-item:hover { color: #fff; background: rgba(255,255,255,0.06); border-left-color: rgba(255,107,0,0.4); }
        .sidebar-menu-item.active { color: #FF6B00; background: rgba(255,107,0,0.08); border-left-color: #FF6B00; font-weight: 700; }
        .main-content { margin-left: 255px; padding: 30px; min-height: 100vh; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 14px; }
        .page-title { font-size: 24px; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 11px; }
        .page-title i { color: #FF6B00; }
        .card-box { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 26px; margin-bottom: 22px; }
        .candidatos-grid { display: flex; flex-direction: column; gap: 0; }
        .candidato-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 14px; padding: 16px; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s; margin-bottom: 12px; }
        .candidato-card:hover { border-color: #FF6B00; background: rgba(255,107,0,0.05); }
        .candidato-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #FF6B00; flex-shrink: 0; }
        .candidato-info { flex: 1; }
        .candidato-nombre { font-size: 15px; font-weight: 800; color: #fff; margin-bottom: 3px; }
        .candidato-partido { font-size: 13px; color: #FF8C38; margin-bottom: 2px; }
        .candidato-cargo { font-size: 11px; color: rgba(255,255,255,0.35); margin-bottom: 10px; }
        .btn-actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px; }
        .btn-add { background: #FF6B00; color: #fff; border: none; padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 16px rgba(255,107,0,0.3); }
        .btn-add:hover { background: #FF8C38; color: #fff; transform: translateY(-1px); }
        .btn-propuestas, .btn-equipo { background: rgba(255,107,0,0.1); color: #FF8C38; border: 1px solid rgba(255,107,0,0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .btn-propuestas:hover, .btn-equipo:hover { background: rgba(255,107,0,0.2); color: #FF6B00; }
        .btn-editar { background: rgba(255,107,0,0.1); color: #FF8C38; border: 1px solid rgba(255,107,0,0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .btn-editar:hover { background: rgba(255,107,0,0.2); color: #FF6B00; }
        .btn-toggle { background: rgba(234,179,8,0.1); color: #facc15; border: 1px solid rgba(234,179,8,0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .btn-eliminar { background: rgba(231,76,60,0.1); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .btn-eliminar:hover { background: rgba(231,76,60,0.2); color: #E74C3C; }
        .badge-activo { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.25); padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .badge-inactivo { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.4); border: 1px solid rgba(255,255,255,0.1); padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
        .alert-success { background: rgba(39,174,96,0.12); border-left: 4px solid #27AE60; color: #5cdb95; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
        .alert-danger { background: rgba(231,76,60,0.12); border-left: 4px solid #E74C3C; color: #ff6b6b; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
        footer { text-align: center; padding: 28px; color: rgba(255,255,255,0.2); font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
        <p>Sistema Electoral Bolivia 2026</p>
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

    <div class="card-box">
        <div class="candidatos-grid">
            <?php if (empty($candidatos)): ?>
                <p style="color: rgba(255,255,255,0.4); text-align: center; padding: 40px;">
                    No hay candidatos registrados.
                    <a href="/yo_voto/candidatos/agregar" style="color: #FF6B00; font-weight: 700;">Agregar el primero</a>
                </p>
            <?php else: ?>
                <?php foreach ($candidatos as $candidato): ?>
                    <div class="candidato-card">
                        <img src="/yo_voto/<?= $candidato['foto_url'] ?>" class="candidato-img"
                             onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                        <div class="candidato-info">
                            <div class="candidato-nombre"><?= htmlspecialchars($candidato['nombre']) ?></div>
                            <div class="candidato-partido"><?= htmlspecialchars($candidato['partido']) ?></div>
                            <div class="candidato-cargo">Candidato a <?= htmlspecialchars($candidato['cargo']) ?></div>
                            <span class="<?= $candidato['estado'] === 'activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                                <?= $candidato['estado'] === 'activo' ? '✓ Habilitado' : '✗ Inhabilitado' ?>
                            </span>
                            <div class="btn-actions">
                                <a href="/yo_voto/propuestas/<?= $candidato['id_candidato'] ?>" class="btn-propuestas">
                                    <i class="fas fa-list-check"></i> Propuestas
                                </a>
                                <a href="/yo_voto/equipo/<?= $candidato['id_candidato'] ?>" class="btn-equipo">
                                    <i class="fas fa-users"></i> Equipo
                                </a>
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