<?php
// views/admin/jurados.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Jurados - Yo Voto</title>
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
        .btn-back { background: #003399; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; }
        .content-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .table-jurados { width: 100%; border-collapse: collapse; }
        .table-jurados th { background: #003399; color: white; padding: 12px; text-align: left; }
        .table-jurados td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
        .btn-asignar { background: #003399; color: white; padding: 10px 20px; border: none; border-radius: 8px; }
        .btn-eliminar { background: #dc2626; color: white; padding: 5px 12px; border: none; border-radius: 5px; cursor: pointer; }
        .btn-editar { background: #f5c518; color: #003399; padding: 5px 12px; border: none; border-radius: 5px; cursor: pointer; }
        .badge-confirmado { background: #4caf50; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; }
        .badge-pendiente { background: #ff9800; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; }
        footer { text-align: center; padding: 20px; color: white; margin-top: 30px; }
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
        <a href="/yo_voto/jurados" class="sidebar-menu-item active">
            <i class="fas fa-gavel"></i> Jurados
        </a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <i class="fas fa-gavel"></i> Gestión de Jurados Electorales
            </div>
            <a href="/yo_voto/admin/dashboard" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Formulario de Asignación -->
        <div class="content-card">
            <h3 style="color: #003399; margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Asignar Nuevo Jurado</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Seleccionar Ciudadano</label>
                        <select name="id_usuario" class="form-control" required>
                            <option value="">Seleccionar</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['id'] ?>"><?= $u['nombres'] ?> <?= $u['apellidos'] ?> (<?= $u['carnet'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">N° Mesa</label>
                        <input type="number" name="id_mesa" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Cargo</label>
                        <select name="cargo" class="form-control" required>
                            <option value="Presidente de Mesa">Presidente de Mesa</option>
                            <option value="Vocal">Vocal</option>
                            <option value="Secretario">Secretario</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn-asignar form-control">
                            <i class="fas fa-user-check"></i> Asignar Jurado
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Jurados -->
        <div class="content-card">
            <h3 style="color: #003399; margin-bottom: 20px;"><i class="fas fa-list"></i> Jurados Seleccionados</h3>
            <table class="table-jurados">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Mesa</th>
                        <th>Nombre</th>
                        <th>Cargo Jurado</th>
                        <th>Fecha Asignación</th>
                        <th>Confirmado</th>
                        <th>Activo</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jurados as $j): ?>
                        <tr>
                            <td><strong><?= $j['codigo_jurado'] ?></strong></td>
                            <td>Mesa <?= $j['id_mesa'] ?></td>
                            <td><?= $j['nombres'] ?? 'Pendiente' ?> <?= $j['apellidos'] ?? '' ?></td>
                            <td><?= $j['cargo_jurado'] ?></td>
                            <td><?= $j['fecha_asignacion'] ?></td>
                            <td>
                                <?php if ($j['confirmado']): ?>
                                    <span class="badge-confirmado"><i class="fas fa-check"></i> Confirmado</span>
                                <?php else: ?>
                                    <span class="badge-pendiente"><i class="fas fa-clock"></i> Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($j['activo']): ?>
                                    <span class="badge-confirmado">Activo</span>
                                <?php else: ?>
                                    <span class="badge-pendiente">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-editar" onclick="editarJurado(<?= $j['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-eliminar" onclick="if(confirm('¿Eliminar este jurado?')) location.href='/yo_voto/jurados/eliminar/<?= $j['id'] ?>'">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script>
        function editarJurado(id) {
            alert('Editar jurado ID: ' + id + ' (Funcionalidad en desarrollo)');
        }
    </script>
</body>
</html>