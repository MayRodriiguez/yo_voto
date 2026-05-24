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
    <title>Seleccionar Tipo de Elección - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; display: flex; flex-direction: column; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100%; background: #003399; color: white; z-index: 1000; }
        .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(245,197,24,0.3); }
        .sidebar-header h3 { color: #f5c518; }
        .sidebar-menu-item { padding: 12px 25px; display: flex; align-items: center; gap: 15px; color: white; text-decoration: none; transition: 0.3s; }
        .sidebar-menu-item:hover, .sidebar-menu-item.active { background: rgba(245,197,24,0.2); border-left: 4px solid #f5c518; }
        .main-content { margin-left: 280px; padding: 20px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .top-bar { background: white; border-radius: 15px; padding: 15px 25px; margin-bottom: 40px; width: 100%; display: flex; justify-content: space-between; align-items: center; }
        .page-title { color: #003399; font-size: 24px; font-weight: bold; }

        .tipo-activo-badge { padding: 8px 20px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .badge-ninguno { background: #6c757d; color: white; }
        .badge-nacional { background: #003399; color: white; }
        .badge-subnacional { background: #f5c518; color: #003399; }

        .seleccion-container { display: flex; gap: 40px; justify-content: center; flex-wrap: wrap; }
        .tipo-card { background: white; border-radius: 25px; padding: 50px 40px; text-align: center; width: 280px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); transition: 0.3s; position: relative; }
        .tipo-card:not(.bloqueado):hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        .tipo-card.bloqueado { opacity: 0.5; cursor: not-allowed; }
        .tipo-card.activo { border: 4px solid #f5c518; }
        .tipo-icono { font-size: 70px; margin-bottom: 20px; }
        .tipo-titulo { font-size: 22px; font-weight: bold; color: #003399; margin-bottom: 10px; }
        .tipo-desc { color: #666; font-size: 14px; margin-bottom: 25px; }
        .btn-tipo { width: 100%; padding: 12px; border-radius: 10px; border: none; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-nacional { background: #003399; color: white; }
        .btn-nacional:hover:not(:disabled) { background: #1a5bc4; }
        .btn-subnacional { background: #f5c518; color: #003399; }
        .btn-subnacional:hover:not(:disabled) { background: #ffdd44; }
        .btn-tipo:disabled { cursor: not-allowed; opacity: 0.6; }
        .badge-activo-card { position: absolute; top: 15px; right: 15px; background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }

        .btn-cambiar { background: rgba(255,255,255,0.2); color: white; border: 2px solid white; padding: 10px 25px; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 40px; transition: 0.3s; font-size: 14px; }
        .btn-cambiar:hover { background: rgba(255,255,255,0.3); }

        footer { text-align: center; padding: 20px; color: white; margin-left: 280px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item"><i class="fas fa-user-plus"></i> Registro Ciudadano</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item active"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item"><i class="fas fa-gavel"></i> Jurados</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-flag"></i> Seleccionar Tipo de Elección</div>
            <span class="tipo-activo-badge 
                <?= $tipoActivo === 'ninguna' ? 'badge-ninguno' : ($tipoActivo === 'nacional' ? 'badge-nacional' : 'badge-subnacional') ?>">
                <?php if ($tipoActivo === 'ninguna'): ?>
                    <i class="fas fa-circle"></i> Sin selección activa
                <?php elseif ($tipoActivo === 'nacional'): ?>
                    <i class="fas fa-flag"></i> Nacionales activo
                <?php else: ?>
                    <i class="fas fa-map-marker-alt"></i> Subnacionales activo
                <?php endif; ?>
            </span>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:10px;margin-bottom:20px;width:100%;border-left:4px solid #28a745;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje'] ?>
                <?php unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>

        <div class="seleccion-container">
            <!-- Card Nacional -->
            <div class="tipo-card <?= $tipoActivo === 'subnacional' ? 'bloqueado' : '' ?> <?= $tipoActivo === 'nacional' ? 'activo' : '' ?>">
                <?php if ($tipoActivo === 'nacional'): ?>
                    <span class="badge-activo-card"><i class="fas fa-check"></i> Activo</span>
                <?php endif; ?>
                <div class="tipo-icono"></div>
                <div class="tipo-titulo">Nacionales</div>
                <div class="tipo-desc">Candidatos a Presidente y Vicepresidente de Bolivia</div>
                <button class="btn-tipo btn-nacional" 
                    <?= $tipoActivo === 'subnacional' ? 'disabled' : '' ?>
                    onclick="seleccionarTipo('nacional')">
                    <i class="fas fa-flag"></i> 
                    <?= $tipoActivo === 'nacional' ? 'Ver Candidatos' : 'Seleccionar' ?>
                </button>
            </div>

            <!-- Card Subnacional -->
            <div class="tipo-card <?= $tipoActivo === 'nacional' ? 'bloqueado' : '' ?> <?= $tipoActivo === 'subnacional' ? 'activo' : '' ?>">
                <?php if ($tipoActivo === 'subnacional'): ?>
                    <span class="badge-activo-card"><i class="fas fa-check"></i> Activo</span>
                <?php endif; ?>
                <div class="tipo-icono"></div>
                <div class="tipo-titulo">Subnacionales</div>
                <div class="tipo-desc">Candidatos a Gobernador, Alcalde y cargos regionales</div>
                <button class="btn-tipo btn-subnacional"
                    <?= $tipoActivo === 'nacional' ? 'disabled' : '' ?>
                    onclick="seleccionarTipo('subnacional')">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= $tipoActivo === 'subnacional' ? 'Ver Candidatos' : 'Seleccionar' ?>
                </button>
            </div>
        </div>

        <!-- Botón cambiar solo aparece si hay uno activo -->
        <?php if ($tipoActivo !== 'ninguna'): ?>
            <button class="btn-cambiar" onclick="cambiarTipo()">
                <i class="fas fa-exchange-alt"></i> Cambiar tipo de elección
            </button>
        <?php endif; ?>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>

    <script>
        function seleccionarTipo(tipo) {
            const nombres = { nacional: 'Nacionales', subnacional: 'Subnacionales' };
            const tipoActivo = '<?= $tipoActivo ?>';

            if (tipoActivo === tipo) {
                // Ya está activo, solo redirigir
                window.location.href = `/yo_voto/candidatos/${tipo}`;
                return;
            }

            if (confirm(`¿Estás seguro que deseas activar las elecciones ${nombres[tipo]}?\n\nEl otro tipo quedará bloqueado hasta que cambies la selección.`)) {
                window.location.href = `/yo_voto/candidatos/activar/${tipo}`;
            }
        }

        function cambiarTipo() {
            if (confirm('¿Estás seguro que deseas cambiar el tipo de elección?\n\nEsto desbloqueará ambas opciones y tendrás que seleccionar nuevamente.')) {
                window.location.href = '/yo_voto/candidatos/resetear';
            }
        }
    </script>
</body>
</html>