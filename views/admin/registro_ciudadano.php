<?php
// views/admin/registro_ciudadano.php - Panel de Administración (SOLO HABILITAR)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
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
    <title>Gestión de Ciudadanos - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
.btn-back { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.1); padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; transition: all 0.2s; }
.btn-back:hover { background: rgba(255,255,255,0.1); color: #fff; }
.form-container { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 26px; margin-bottom: 22px; }
.alert-success { background: rgba(39,174,96,0.12); border-left: 4px solid #27AE60; color: #5cdb95; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
.alert-danger { background: rgba(231,76,60,0.12); border-left: 4px solid #E74C3C; color: #ff6b6b; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
.alert-info { background: rgba(25,118,210,0.1); border-left: 4px solid #1976D2; color: #7eb3ff; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; }
.tabla-registros { width: 100%; border-collapse: collapse; }
.tabla-registros th { background: rgba(255,107,0,0.08); color: #FF8C38; padding: 13px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
.tabla-registros td { padding: 13px 14px; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 13px; color: rgba(255,255,255,0.6); }
.tabla-registros tr:hover td { background: rgba(255,255,255,0.02); }
.table-responsive { overflow-x: auto; }
.badge-si { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.25); padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
.badge-no { background: rgba(231,76,60,0.15); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.2); padding: 4px 11px; border-radius: 20px; font-size: 11px; font-weight: 700; display: inline-block; }
.btn-editar { background: rgba(255,107,0,0.1); color: #FF8C38; border: 1px solid rgba(255,107,0,0.2); padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
.btn-editar:hover { background: rgba(255,107,0,0.2); color: #FF6B00; }
.btn-habilitar { background: rgba(39,174,96,0.1); color: #5cdb95; border: 1px solid rgba(39,174,96,0.2); padding: 7px 13px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
.btn-habilitar:hover { background: rgba(39,174,96,0.2); color: #27AE60; }
footer { text-align: center; padding: 28px; color: rgba(255,255,255,0.2); font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); }
footer span { color: #FF6B00; font-weight: 700; }
.mt-4 { margin-top: 22px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Panel Principal</a>
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item active"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-user-check"></i> Gestión de Ciudadanos</div>
            <a href="/yo_voto/admin/dashboard" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="form-container mt-4">
            <h3 style="color:#fff;font-weight:800;margin-bottom:20px;font-size:16px;display:flex;align-items:center;gap:9px;">
                <i class="fas fa-users" style="color:#FF6B00;"></i> Ciudadanos Registrados
                <small style="font-size:12px;color:rgba(255,255,255,0.35);font-weight:400;">(Habilitar para votar)</small>
            </h3>
            <div class="table-responsive">
                <table class="tabla-registros">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Nombre Completo</th>
                            <th><i class="fas fa-id-card"></i> Carnet</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-check-circle"></i> Voto Habilitado</th>
                            <th><i class="fas fa-calendar"></i> Fecha Registro</th>
                            <th><i class="fas fa-cog"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $db = new Database();
                        $conn = $db->getConnection();
                        $result = $conn->query("SELECT id, numero_registro, nombres, apellidos, carnet, email, habilitado_voto, fecha_registro FROM usuarios WHERE rol = 'usuario' ORDER BY id DESC LIMIT 50");
                        while ($row = $result->fetch_assoc()):
                            $isHabilitado = $row['habilitado_voto'] == 1;
                        ?>
                        <tr id="fila-<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></td>
                            <td><?= htmlspecialchars($row['carnet']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                                <?php if ($isHabilitado): ?>
                                    <span class="badge-si"><i class="fas fa-check"></i> Habilitado</span>
                                <?php else: ?>
                                    <span class="badge-no"><i class="fas fa-times"></i> No habilitado</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['fecha_registro'])) ?></td>
                            <td>
                                <a href="/yo_voto/admin/editar-ciudadano/<?= $row['id'] ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <?php if (!$isHabilitado): ?>
                                    <button class="btn-habilitar" onclick="habilitarCiudadano(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombres']) ?>')">
                                        <i class="fas fa-check-circle"></i> Habilitar Voto
                                    </button>
                                <?php else: ?>
                                    <span class="badge-si"><i class="fas fa-check"></i> Activo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer><p> <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

    <script>
    async function habilitarCiudadano(id, nombre) {
        if (confirm(`¿Estás seguro de habilitar a ${nombre} para votar?`)) {
            try {
                const response = await fetch('/yo_voto/api/admin/habilitar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();
                if (result.success) {
                    alert(` ${nombre} ha sido habilitado para votar`);
                    location.reload();
                } else {
                    alert(' Error: ' + (result.error || 'No se pudo habilitar'));
                }
            } catch (error) {
                alert(' Error de conexión: ' + error.message);
            }
        }
    }
    </script>
</body>
</html>