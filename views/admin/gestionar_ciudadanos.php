<?php
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

$mensaje = $_SESSION['mensaje'] ?? null;
$error   = $_SESSION['error'] ?? null;
unset($_SESSION['mensaje'], $_SESSION['error']);

// Editar ciudadano
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_id'])) {
    $id           = intval($_POST['editar_id']);
    $nombres      = $conn->real_escape_string($_POST['nombres']);
    $apellidos    = $conn->real_escape_string($_POST['apellidos']);
    $carnet       = $conn->real_escape_string($_POST['carnet']);
    $email        = $conn->real_escape_string($_POST['email']);
    $telefono     = $conn->real_escape_string($_POST['telefono']);
    $direccion    = $conn->real_escape_string($_POST['direccion']);
    $departamento = $conn->real_escape_string($_POST['departamento']);
    $habilitado   = intval($_POST['habilitado_voto'] ?? 0);

    $sql = "UPDATE usuarios SET nombres='$nombres', apellidos='$apellidos', carnet='$carnet', email='$email', telefono='$telefono', direccion='$direccion', departamento='$departamento', habilitado_voto=$habilitado WHERE id=$id AND rol='usuario'";
    if ($conn->query($sql)) {
        $_SESSION['mensaje'] = "Ciudadano actualizado correctamente.";
    } else {
        $_SESSION['error'] = "Error al actualizar: " . $conn->error;
    }
    header("Location: /yo_voto/admin/ciudadanos");
    exit();
}

// Buscar ciudadanos
$buscar = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';
$where = "WHERE rol = 'usuario'";
if ($buscar) {
    $where .= " AND (nombres LIKE '%$buscar%' OR apellidos LIKE '%$buscar%' OR carnet LIKE '%$buscar%' OR email LIKE '%$buscar%')";
}
$ciudadanos = $conn->query("SELECT * FROM usuarios $where ORDER BY fecha_registro DESC LIMIT 100");

// Ver ciudadano para editar
$editando = null;
if (isset($_GET['editar'])) {
    $editId = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM usuarios WHERE id=$editId AND rol='usuario'");
    $editando = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Ciudadanos - Yo Voto</title>
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
        .top-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:14px; }
        .page-title { font-size:24px; font-weight:900; color:#fff; display:flex; align-items:center; gap:11px; }
        .page-title i { color:#FF6B00; }
        .card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:26px; margin-bottom:22px; }
        .alert-success { background:rgba(39,174,96,0.12); border-left:4px solid #27AE60; color:#5cdb95; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:14px; }
        .alert-danger { background:rgba(231,76,60,0.12); border-left:4px solid #E74C3C; color:#ff6b6b; border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:14px; }
        .search-box { display:flex; gap:10px; margin-bottom:20px; }
        .search-input { flex:1; padding:11px 14px; background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12); border-radius:10px; color:#fff; font-size:14px; }
        .search-input:focus { outline:none; border-color:#FF6B00; }
        .search-input::placeholder { color:rgba(255,255,255,0.25); }
        .btn-buscar { background:#FF6B00; color:#fff; border:none; padding:11px 20px; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; display:inline-flex; align-items:center; gap:7px; }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(255,107,0,0.08); }
        th { padding:13px 16px; font-size:11px; font-weight:700; color:#FF8C38; text-transform:uppercase; letter-spacing:0.5px; text-align:left; }
        td { padding:12px 16px; border-bottom:1px solid rgba(255,255,255,0.05); font-size:13px; color:rgba(255,255,255,0.6); }
        tbody tr:hover { background:rgba(255,255,255,0.03); }
        .btn-editar { background:rgba(255,107,0,0.1); color:#FF8C38; border:1px solid rgba(255,107,0,0.2); padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s; }
        .btn-editar:hover { background:rgba(255,107,0,0.2); color:#FF6B00; }
        .badge-si { background:rgba(39,174,96,0.15); color:#5cdb95; border:1px solid rgba(39,174,96,0.25); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; cursor:pointer; }
        .badge-no { background:rgba(231,76,60,0.15); color:#ff6b6b; border:1px solid rgba(231,76,60,0.2); padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; cursor:pointer; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:500; overflow-y:auto; padding:30px 20px; }
        .modal-overlay.open { display:block; }
        .modal-box { background:#0d1e42; border:1px solid rgba(255,255,255,0.1); border-radius:20px; max-width:600px; margin:0 auto; overflow:hidden; }
        .modal-head { background:linear-gradient(135deg,#0d2251,#1a3a7a); border-bottom:2px solid #FF6B00; padding:20px 24px; display:flex; align-items:center; justify-content:space-between; }
        .modal-head h2 { font-family:'Montserrat',sans-serif; font-weight:800; color:#fff; font-size:17px; margin:0; }
        .btn-close { background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:8px; font-size:18px; cursor:pointer; display:flex; align-items:center; justify-content:center; text-decoration:none; }
        .btn-close:hover { background:#FF6B00; }
        .modal-body { padding:24px; }
        .form-group { margin-bottom:16px; }
        .form-label { font-weight:700; color:rgba(255,255,255,0.7); font-size:13px; margin-bottom:6px; display:block; }
        .form-control, .form-select { background:rgba(255,255,255,0.07); border:1.5px solid rgba(255,255,255,0.12); border-radius:10px; padding:10px 14px; color:#fff; font-size:14px; width:100%; transition:border-color 0.2s; font-family:'Open Sans',sans-serif; }
        .form-control:focus, .form-select:focus { outline:none; border-color:#FF6B00; }
        .form-select option { background:#0d2251; }
        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        .btn-guardar { background:#FF6B00; color:#fff; border:none; padding:12px 24px; border-radius:10px; font-size:14px; font-weight:800; cursor:pointer; width:100%; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 4px 16px rgba(255,107,0,0.3); }
        .btn-guardar:hover { background:#FF8C38; }
        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); }
        footer span { color:#FF6B00; font-weight:700; }
        .table-responsive { overflow-x:auto; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Panel Principal</a>
        <a href="/yo_voto/admin/ciudadanos" class="sidebar-menu-item active"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="GET" class="search-box">
                <input type="text" name="q" class="search-input" placeholder="Buscar por nombre, carnet o email..." value="<?= htmlspecialchars($buscar) ?>">
                <button type="submit" class="btn-buscar"><i class="fas fa-search"></i> Buscar</button>
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Carnet</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Departamento</th>
                            <th>Habilitado</th>
                            <th>Ya Votó</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($c = $ciudadanos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nombres'] . ' ' . $c['apellidos']) ?></td>
                            <td><?= htmlspecialchars($c['carnet']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['telefono'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($c['departamento'] ?? '—') ?></td>
                            <td>
                                <a href="/yo_voto/admin/toggle-habilitar/<?= $c['id'] ?>"
                                   onclick="return confirm('¿Cambiar estado de habilitación de <?= htmlspecialchars($c['nombres']) ?>?')"
                                   style="text-decoration:none;">
                                    <?= $c['habilitado_voto'] ? '<span class="badge-si">Sí</span>' : '<span class="badge-no">No</span>' ?>
                                </a>
                            </td>
                            <td><?= $c['ya_voto'] ? '<span class="badge-si">Sí</span>' : '<span class="badge-no">No</span>' ?></td>
                            <td>
                                <a href="?editar=<?= $c['id'] ?><?= $buscar ? '&q=' . urlencode($buscar) : '' ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer><p> <span>Yo Voto</span> — Sistema Electoral Bolivia 2026</p></footer>

    <!-- MODAL EDITAR -->
    <div class="modal-overlay <?= $editando ? 'open' : '' ?>" id="modalEditar">
        <div class="modal-box">
            <div class="modal-head">
                <h2><i class="fas fa-edit" style="color:#FF6B00;margin-right:8px;"></i> Editar Ciudadano</h2>
                <a href="/yo_voto/admin/ciudadanos<?= $buscar ? '?q=' . urlencode($buscar) : '' ?>" class="btn-close"><i class="fas fa-times"></i></a>
            </div>
            <?php if ($editando): ?>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="editar_id" value="<?= $editando['id'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" class="form-control" value="<?= htmlspecialchars($editando['nombres']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" value="<?= htmlspecialchars($editando['apellidos']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Carnet</label>
                            <input type="text" name="carnet" class="form-control" value="<?= htmlspecialchars($editando['carnet']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editando['email']) ?>" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($editando['telefono'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Departamento</label>
                            <select name="departamento" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php
                                $deptos = ['La Paz','Cochabamba','Santa Cruz','Oruro','Potosí','Chuquisaca','Tarija','Beni','Pando'];
                                foreach ($deptos as $d) {
                                    $sel = ($editando['departamento'] === $d) ? 'selected' : '';
                                    echo "<option value='$d' $sel>$d</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($editando['direccion'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">¿Habilitado para votar?</label>
                        <select name="habilitado_voto" class="form-select">
                            <option value="1" <?= $editando['habilitado_voto'] == 1 ? 'selected' : '' ?>>Si - Puede votar</option>
                            <option value="0" <?= $editando['habilitado_voto'] == 0 ? 'selected' : '' ?>>No - No puede votar</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>