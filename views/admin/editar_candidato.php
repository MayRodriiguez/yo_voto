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
    <title>Editar Candidato - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100%; background: #003399; color: white; z-index: 1000; }
        .sidebar-header { padding: 25px 20px; text-align: center; border-bottom: 1px solid rgba(245,197,24,0.3); }
        .sidebar-header h3 { color: #f5c518; }
        .sidebar-menu-item { padding: 12px 25px; display: flex; align-items: center; gap: 15px; color: white; text-decoration: none; transition: 0.3s; }
        .sidebar-menu-item:hover { background: rgba(245,197,24,0.2); border-left: 4px solid #f5c518; }
        .main-content { margin-left: 280px; padding: 20px; }
        .top-bar { background: white; border-radius: 15px; padding: 15px 25px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { color: #003399; font-size: 24px; font-weight: bold; }
        .btn-back { background: #6c757d; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .form-label { font-weight: bold; color: #003399; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #c4b5fd; border-radius: 10px; padding: 10px 15px; }
        .form-control:focus, .form-select:focus { border-color: #f5c518; box-shadow: none; }
        textarea.form-control { resize: vertical; }
        .btn-actualizar { background: linear-gradient(135deg, #ff9800, #e68900); color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: bold; width: 100%; margin-top: 20px; transition: 0.3s; }
        .btn-actualizar:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,152,0,0.3); }
        .preview-img { width: 150px; height: 150px; object-fit: cover; border-radius: 50%; margin-top: 10px; border: 3px solid #f5c518; }
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
            <i class="fas fa-user-plus"></i> Registro Ciudadano
        </a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item">
            <i class="fas fa-users"></i> Candidatos
        </a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item">
            <i class="fas fa-gavel"></i> Jurados
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-edit"></i> Editar Candidato
            </div>
            <a href="/yo_voto/candidatos" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="form-container">
            <form action="/yo_voto/candidatos/editar/<?= $candidato['id_candidato'] ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user"></i> Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($candidato['nombre']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-flag-checkered"></i> Partido político *</label>
                    <input type="text" name="partido" class="form-control" value="<?= htmlspecialchars($candidato['partido']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-briefcase"></i> Cargo *</label>
                    <input type="text" name="cargo" class="form-control" value="<?= htmlspecialchars($candidato['cargo']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-align-left"></i> Biografía</label>
                    <textarea name="biografia" class="form-control" rows="5"><?= htmlspecialchars($candidato['biografia']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-calendar"></i> Fecha de postulación *</label>
                    <input type="date" name="fecha_postulacion" class="form-control" value="<?= $candidato['fecha_postulacion'] ?>" required>
                </div>

                <div class="mb-3 text-center">
                    <label class="form-label"><i class="fas fa-image"></i> Foto actual</label><br>
                    <img src="/yo_voto/<?= $candidato['foto_url'] ?>" class="preview-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                    <p class="text-muted mt-2">Para cambiar la foto, usa la opción "Editar" desde el listado principal (subir nueva imagen)</p>
                </div>

                <button type="submit" class="btn-actualizar">
                    <i class="fas fa-save"></i> Actualizar Candidato
                </button>
            </form>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>
</body>
</html>