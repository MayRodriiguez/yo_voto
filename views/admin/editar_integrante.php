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
    <title>Editar Integrante - Yo Voto</title>
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
        .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .form-label { font-weight: bold; color: #003399; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #c4b5fd; border-radius: 10px; padding: 10px 15px; }
        .form-control:focus, .form-select:focus { border-color: #f5c518; box-shadow: none; }
        .btn-guardar { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 12px 30px; border: none; border-radius: 10px; font-weight: bold; width: 100%; margin-top: 20px; transition: 0.3s; }
        .btn-guardar:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,51,153,0.3); }
        .candidato-info { background: #f0f4ff; border-radius: 15px; padding: 15px 20px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
        .candidato-info img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #f5c518; }
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
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item"><i class="fas fa-user-plus"></i> Registro Ciudadano</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/jurados" class="sidebar-menu-item"><i class="fas fa-gavel"></i> Jurados</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-edit"></i> Editar Integrante
            </div>
            <a href="/yo_voto/equipo/<?= $integrante['id_candidato'] ?>" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Info del candidato -->
        <div class="candidato-info">
            <img src="/yo_voto/<?= $candidato['foto_url'] ?>" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
            <div>
                <strong style="color:#003399"><?= htmlspecialchars($candidato['nombre']) ?></strong>
                <div style="color:#666;font-size:13px">Candidato a <?= htmlspecialchars($candidato['cargo']) ?></div>
            </div>
        </div>

        <div class="form-container">
            <form action="/yo_voto/equipo/editar/<?= $integrante['id_integrante'] ?>/<?= $integrante['id_candidato'] ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user"></i> Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?= htmlspecialchars($integrante['nombre']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-briefcase"></i> Cargo *</label>
                    <input type="text" name="cargo" class="form-control" 
                           value="<?= htmlspecialchars($integrante['cargo']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-layer-group"></i> Nivel en el árbol</label>
                    <select name="nivel" class="form-select">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= $integrante['nivel'] == $i ? 'selected' : '' ?>>
                                Nivel <?= $i ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>
</body>
</html>