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
    *{
        margin:0;
        padding:0;
        box-sizing:border-box;
    }

    body{
        font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
        background:#f4f7fb;
        color:#1e293b;
    }

    .sidebar{
        position:fixed;
        top:0;
        left:0;
        width:280px;
        height:100%;
        background:linear-gradient(180deg,#003399,#165dff);
        color:white;
        overflow:auto;
        box-shadow:0 0 25px rgba(0,0,0,0.15);
    }

    .sidebar-header{
        padding:28px 20px;
        text-align:center;
        border-bottom:1px solid rgba(255,255,255,0.1);
    }

    .sidebar-header h3{
        color:#ffd447;
        font-size:27px;
        font-weight:700;
    }

    .sidebar-menu-item{
        display:flex;
        align-items:center;
        gap:14px;
        padding:14px 24px;
        color:white;
        text-decoration:none;
        transition:0.3s;
        border-left:4px solid transparent;
    }

    .sidebar-menu-item:hover,
    .sidebar-menu-item.active{
        background:rgba(255,212,71,0.12);
        border-left:4px solid #ffd447;
        padding-left:30px;
    }

    .main-content{
        margin-left:280px;
        padding:30px;
    }

    .top-bar{
        background:white;
        border-radius:22px;
        padding:22px 28px;
        margin-bottom:30px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        box-shadow:0 10px 30px rgba(0,0,0,0.06);
    }

    .page-title{
        font-size:28px;
        font-weight:700;
        color:#003399;
    }

    .form-container,
    .table-container,
    .card-box{
        background:white;
        border-radius:24px;
        padding:28px;
        box-shadow:0 12px 30px rgba(0,0,0,0.06);
    }

    .form-label{
        font-weight:600;
        color:#003399;
        margin-bottom:8px;
    }

    .form-control,
    .form-select{
        border-radius:14px;
        border:1px solid #dbe2ea;
        padding:12px;
        box-shadow:none;
    }

    .form-control:focus,
    .form-select:focus{
        border-color:#165dff;
        box-shadow:0 0 0 4px rgba(22,93,255,0.1);
    }

    .btn-guardar,
    .btn-add,
    .btn-primary{
        background:linear-gradient(135deg,#003399,#165dff);
        color:white;
        border:none;
        padding:12px 24px;
        border-radius:14px;
        font-weight:600;
        transition:0.3s;
        text-decoration:none;
    }

    .btn-guardar:hover,
    .btn-add:hover,
    .btn-primary:hover{
        transform:translateY(-2px);
        color:white;
        box-shadow:0 10px 20px rgba(0,51,153,0.2);
    }

    .btn-warning{
        background:#ffd447;
        border:none;
        color:#003399;
        font-weight:700;
        border-radius:12px;
    }

    .btn-danger{
        border-radius:12px;
    }

    table{
        width:100%;
        border-collapse:collapse;
    }

    thead{
        background:#003399;
        color:white;
    }

    th{
        padding:16px;
        font-size:14px;
    }

    td{
        padding:16px;
        border-bottom:1px solid #edf2f7;
        vertical-align:middle;
    }

    tbody tr{
        transition:0.2s;
    }

    tbody tr:hover{
        background:#f8fbff;
    }

    .alert-success,
    .alert-danger{
        border:none;
        border-radius:16px;
        padding:16px;
    }

    .badge-si{
        background:#16a34a;
        color:white;
        padding:6px 12px;
        border-radius:30px;
    }

    .badge-no{
        background:#dc2626;
        color:white;
        padding:6px 12px;
        border-radius:30px;
    }

    img{
        border-radius:14px;
        object-fit:cover;
    }

    footer{
        text-align:center;
        margin-top:30px;
        color:#64748b;
    }
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