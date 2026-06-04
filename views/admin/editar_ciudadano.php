<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/** @var array $usuario */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Ciudadano - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Open Sans',sans-serif; background:#0a1628; color:#e2e8f0; min-height:100vh; }
        .sidebar { position:fixed; left:0; top:0; width:255px; height:100%; background:rgba(10,22,50,0.98); border-right:1px solid rgba(255,255,255,0.08); overflow-y:auto; z-index:100; }
        .sidebar-header { padding:26px 22px 18px; border-bottom:1px solid rgba(255,255,255,0.08); }
        .sidebar-header h3 { font-size:20px; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px; }
        .sidebar-header h3 i { color:#FF6B00; }
        .sidebar-header p { color:rgba(255,255,255,0.3); font-size:11px; margin-top:6px; margin-left:28px; }
        .sidebar-menu-item { display:flex; align-items:center; gap:11px; padding:11px 22px; color:rgba(255,255,255,0.45); text-decoration:none; font-size:14px; font-weight:600; border-left:3px solid transparent; transition:.2s; }
        .sidebar-menu-item:hover { color:#fff; background:rgba(255,255,255,0.06); border-left-color:rgba(255,107,0,0.4); }
        .sidebar-menu-item.active { color:#FF6B00; background:rgba(255,107,0,0.08); border-left-color:#FF6B00; }
        .main-content { margin-left:255px; padding:30px; min-height:100vh; }
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; }
        .page-title { font-size:22px; font-weight:900; color:#fff; display:flex; align-items:center; gap:10px; }
        .page-title i { color:#FF6B00; }
        .btn-back { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:8px 16px; border-radius:8px; text-decoration:none; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:7px; transition:.2s; }
        .btn-back:hover { background:rgba(255,255,255,0.1); color:#fff; }
        .form-container { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:30px; max-width:700px; }
        label { font-weight:700; color:rgba(255,255,255,0.7); font-size:13px; margin-bottom:6px; display:block; }
        .form-control { background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12); border-radius:10px; padding:11px 14px; color:#fff; font-size:14px; width:100%; transition:.2s; margin-bottom:16px; }
        .form-control:focus { outline:none; border-color:#FF6B00; background:rgba(255,255,255,0.1); box-shadow:none; }
        .form-control::placeholder { color:rgba(255,255,255,0.25); }
        .btn-save { background:#FF6B00; color:#fff; border:none; padding:12px 28px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer; transition:.2s; }
        .btn-save:hover { background:#FF8C38; transform:translateY(-1px); }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); margin-top:30px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia 2026</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item active"><i class="fas fa-user-plus"></i> Registro Ciudadano</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-link"></i> Auditoría Blockchain</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-user-edit"></i> Editar Ciudadano</div>
            <a href="/yo_voto/admin/registro" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="form-container">
        <form method="POST">

<div class="mb-3">
<label>Nombres</label>
<input type="text" name="nombres" class="form-control"
value="<?= htmlspecialchars($usuario['nombres']) ?>" required>
</div>

<div class="mb-3">
<label>Apellidos</label>
<input type="text" name="apellidos" class="form-control"
value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
</div>

<div class="mb-3">
<label>Carnet</label>
<input type="text" name="carnet" class="form-control"
value="<?= htmlspecialchars($usuario['carnet']) ?>" required>
</div>

<div class="mb-3">
<label>Dirección</label>
<input type="text" name="direccion" class="form-control"
value="<?= htmlspecialchars($usuario['direccion']) ?>">
</div>

<div class="mb-3">
<label>Teléfono</label>
<input type="text" name="telefono" class="form-control"
value="<?= htmlspecialchars($usuario['telefono']) ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control"
value="<?= htmlspecialchars($usuario['email']) ?>">
</div>

        <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar Cambios</button>
        </form>
        </div>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>
</body>
</html>