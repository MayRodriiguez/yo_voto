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
    <title>Agregar Candidato - Yo Voto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Open Sans',sans-serif; background:#0a1628; color:#e2e8f0; min-height:100vh; }
        .sidebar { position:fixed; top:0; left:0; width:255px; height:100%; background:rgba(10,22,50,0.98); border-right:1px solid rgba(255,255,255,0.08); overflow-y:auto; z-index:100; }
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
        .form-container { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:30px; max-width:800px; margin:0 auto; }
        .form-group { margin-bottom:20px; }
        .form-label { font-weight:700; color:rgba(255,255,255,0.7); font-size:13px; margin-bottom:7px; display:block; }
        .form-control, .form-select { background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12); border-radius:10px; padding:11px 14px; color:#fff; font-size:14px; width:100%; transition:border-color 0.2s; font-family:'Open Sans',sans-serif; }
        .form-control:focus, .form-select:focus { outline:none; border-color:#FF6B00; background:rgba(255,255,255,0.1); }
        .form-control::placeholder { color:rgba(255,255,255,0.25); }
        .form-select option { background:#0d2251; color:#fff; }
        textarea.form-control { resize:vertical; }
        .btn-guardar { background:#FF6B00; color:#fff; border:none; padding:13px 28px; border-radius:10px; font-size:15px; font-weight:800; cursor:pointer; width:100%; margin-top:10px; transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:9px; box-shadow:0 4px 16px rgba(255,107,0,0.3); }
        .btn-guardar:hover { background:#FF8C38; transform:translateY(-1px); box-shadow:0 6px 20px rgba(255,107,0,0.4); }
        .preview-img { width:130px; height:130px; object-fit:cover; border-radius:50%; margin-top:14px; border:3px solid #FF6B00; display:block; margin-left:auto; margin-right:auto; }
        .text-muted { color:rgba(255,255,255,0.3); font-size:12px; margin-top:6px; display:block; }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); margin-top:30px; }
        footer span { color:#FF6B00; font-weight:700; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Panel Principal</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item active"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-plus-circle"></i> Añadir Nuevo Candidato</div>
            <a href="/yo_voto/candidatos" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="form-container">
            <form action="/yo_voto/candidatos/agregar" method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-user"></i> Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Luis Arce Catacora" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-flag-checkered"></i> Partido político *</label>
                    <input type="text" name="partido" class="form-control" placeholder="Ej: Movimiento al Socialismo" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-briefcase"></i> Cargo *</label>
                    <input type="text" name="cargo" class="form-control" placeholder="Ej: Presidente, Alcalde, Gobernador" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-flag"></i> Tipo de elección *</label>
                    <select name="tipo" class="form-select" required>
                        <option value="">-- Seleccionar tipo --</option>
                        <option value="nacional">🏛️ Nacional (Presidente, Vicepresidente)</option>
                        <option value="subnacional">🗺️ Subnacional (Gobernador, Alcalde)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-align-left"></i> Biografía</label>
                    <textarea name="biografia" class="form-control" rows="5" placeholder="Escribe una breve biografía del candidato..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-calendar"></i> Fecha de postulación *</label>
                    <input type="date" name="fecha_postulacion" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><i class="fas fa-image"></i> Foto del candidato</label>
                    <input type="file" name="foto" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <span class="text-muted">Formatos: JPG, PNG, GIF. Tamaño máximo: 2MB</span>
                    <img id="preview" class="preview-img" src="/yo_voto/uploads/img/sin_foto.jpg" alt="Vista previa">
                </div>

                <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Candidato
                </button>
            </form>
        </div>
    </div>

    <footer><p> <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => preview.src = e.target.result;
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>