<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mensaje = $_SESSION['mensaje'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['mensaje']);
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propuestas - <?= htmlspecialchars($candidato['nombre']) ?> | Yo Voto</title>
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
        .form-container, .propuestas-container { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-label { font-weight: bold; color: #003399; }
        .form-control, .form-select { border: 1px solid #c4b5fd; border-radius: 10px; padding: 10px 15px; }
        .btn-guardar { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 10px 25px; border: none; border-radius: 10px; font-weight: bold; }
        .propuesta-card { background: #f8f5ff; border-radius: 15px; padding: 20px; margin-bottom: 20px; border-left: 5px solid #f5c518; }
        .propuesta-categoria { display: inline-block; background: #003399; color: white; padding: 3px 12px; border-radius: 20px; font-size: 11px; margin-bottom: 10px; }
        .propuesta-titulo { font-size: 18px; font-weight: bold; color: #003399; }
        .btn-editar { background: #ff9800; color: white; padding: 5px 15px; border-radius: 8px; border: none; margin-right: 10px; }
        .btn-eliminar { background: #dc2626; color: white; padding: 5px 15px; border-radius: 8px; border: none; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        footer { text-align: center; padding: 20px; color: white; }
        @media (max-width: 768px) { .sidebar { left: -280px; } .main-content { margin-left: 0; } }
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
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item"><i class="fas fa-gavel"></i> Jurados</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-list-check"></i> Propuestas de <?= htmlspecialchars($candidato['nombre']) ?></div>
            <a href="/yo_voto/candidatos" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar -->
        <div class="form-container">
            <h4><i class="fas fa-plus-circle"></i> Agregar Nueva Propuesta</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria" class="form-select" required>
                            <option value="Educación"> Educación</option>
                            <option value="Salud"> Salud</option>
                            <option value="Economía"> Economía</option>
                            <option value="Seguridad"> Seguridad</option>
                            <option value="Medio Ambiente"> Medio Ambiente</option>
                            <option value="Infraestructura"> Infraestructura</option>
                            <option value="Vivienda"> Vivienda</option>
                            <option value="Gobierno"> Gobierno</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Propuesta</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Lista de propuestas -->
        <div class="propuestas-container">
            <h4><i class="fas fa-list"></i> Propuestas Registradas (<?= count($propuestas) ?>)</h4>
            <?php if (empty($propuestas)): ?>
                <p class="text-center text-muted">No hay propuestas registradas.</p>
            <?php else: ?>
                <?php foreach ($propuestas as $propuesta): ?>
                    <div class="propuesta-card">
                        <span class="propuesta-categoria"><i class="fas fa-tag"></i> <?= htmlspecialchars($propuesta['categoria']) ?></span>
                        <div class="propuesta-titulo"><?= htmlspecialchars($propuesta['titulo']) ?></div>
                        <p class="mt-2"><?= nl2br(htmlspecialchars($propuesta['descripcion'])) ?></p>
                        <div>
                            <a href="/yo_voto/propuestas/editar/<?= $propuesta['id_propuesta'] ?>/<?= $id_candidato ?>" class="btn-editar"><i class="fas fa-edit"></i> Editar</a>
                            <button class="btn-eliminar" onclick="eliminarPropuesta(<?= $propuesta['id_propuesta'] ?>, '<?= addslashes($propuesta['titulo']) ?>')"><i class="fas fa-trash"></i> Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>

    <script>
        function eliminarPropuesta(id, titulo) {
            if (confirm(`¿Eliminar la propuesta "${titulo}"?\n\nEsta acción no se puede deshacer.`)) {
                window.location.href = `/yo_voto/propuestas/eliminar/${id}/<?= $id_candidato ?>`;
            }
        }
    </script>
</body>
</html>