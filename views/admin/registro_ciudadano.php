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
        .form-container { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .tabla-registros { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla-registros th { background: #003399; color: white; padding: 12px; text-align: left; }
        .tabla-registros td { padding: 12px; border-bottom: 1px solid #e0e0e0; }
        .tabla-registros tr:hover { background: #f8f5ff; }
        .badge-si { background: #4caf50; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; display: inline-block; }
        .badge-no { background: #dc2626; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; display: inline-block; }
        .badge-face { background: #003399; color: #f5c518; padding: 3px 10px; border-radius: 15px; font-size: 11px; display: inline-block; }
        .badge-warning { background: #ff9800; color: white; padding: 3px 10px; border-radius: 15px; font-size: 11px; display: inline-block; }
        .btn-habilitar { background: #4caf50; color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: bold; transition: 0.3s; }
        .btn-habilitar:hover { background: #45a049; transform: scale(1.02); }
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        .alert-info { background: #e8e0ff; color: #003399; padding: 20px; border-radius: 15px; text-align: center; border-left: 4px solid #003399; }
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
        <a href="/yo_voto/admin/registro" class="sidebar-menu-item active">
            <i class="fas fa-user-check"></i> Gestionar Ciudadanos
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
                <i class="fas fa-user-check"></i> Gestión de Ciudadanos
            </div>
            <a href="/yo_voto/admin/dashboard" class="btn-back">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <!-- Mensaje informativo - El registro ahora es público -->
        <div class="form-container">
            <div class="alert-info">
                <i class="fas fa-info-circle" style="font-size: 40px; color: #003399;"></i>
                <h4 style="color: #003399; margin-top: 10px;">Registro de Ciudadanos</h4>
                <p>Los ciudadanos se registran desde la página pública:</p>
                <a href="/yo_voto/registro" class="btn" style="background: #003399; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; display: inline-block; margin-top: 10px;">
                    <i class="fas fa-external-link-alt"></i> Ir al registro público
                </a>
            </div>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert-success" style="margin-top: 20px;">
                <i class="fas fa-check-circle"></i> <?= $mensaje ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-danger" style="margin-top: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- TABLA DE CIUDADANOS REGISTRADOS CON BOTÓN DE HABILITAR -->
        <div class="form-container mt-4">
            <h3 style="color: #003399; margin-bottom: 20px;">
                <i class="fas fa-users"></i> Ciudadanos Registrados
                <small style="font-size: 12px; color: #666;">(Habilitar para votar)</small>
            </h3>
            <div class="table-responsive">
                <table class="tabla-registros">
                    <thead>
                        <tr>
                            <th><i class="fas fa-qrcode"></i> N° Registro</th>
                            <th><i class="fas fa-user"></i> Nombre Completo</th>
                            <th><i class="fas fa-id-card"></i> Carnet</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-check-circle"></i> Voto Habilitado</th>
                            <th><i class="fas fa-face-smile"></i> Facial</th>
                            <th><i class="fas fa-calendar"></i> Fecha Registro</th>
                            <th><i class="fas fa-cog"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $db = new Database();
                        $conn = $db->getConnection();
                        $result = $conn->query("SELECT id, numero_registro, nombres, apellidos, carnet, email, habilitado_voto, face_descriptor, fecha_registro FROM usuarios WHERE rol = 'usuario' ORDER BY id DESC LIMIT 50");
                        while ($row = $result->fetch_assoc()):
                            $hasFace = !empty($row['face_descriptor']);
                            $isHabilitado = $row['habilitado_voto'] == 1;
                        ?>
                        <tr id="fila-<?= $row['id'] ?>">
                            <td><strong style="color: #003399; font-family: monospace;"><?= htmlspecialchars($row['numero_registro'] ?? 'Pendiente') ?></strong></td>
                            <td><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></td>
                            <td><?= $row['carnet'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td>
                                <?php if ($isHabilitado): ?>
                                    <span class="badge-si"><i class="fas fa-check"></i> Habilitado</span>
                                <?php else: ?>
                                    <span class="badge-no"><i class="fas fa-times"></i> No habilitado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($hasFace): ?>
                                    <span class="badge-face"><i class="fas fa-check-circle"></i> Registrado</span>
                                <?php else: ?>
                                    <span class="badge-warning"><i class="fas fa-exclamation-triangle"></i> Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($row['fecha_registro'])) ?></td>
                            <td>
                                <?php if (!$isHabilitado && $hasFace): ?>
                                    <button class="btn-habilitar" onclick="habilitarCiudadano(<?= $row['id'] ?>, '<?= htmlspecialchars($row['nombres']) ?>')">
                                        <i class="fas fa-check-circle"></i> Habilitar Voto
                                    </button>
                                <?php elseif (!$isHabilitado && !$hasFace): ?>
                                    <span class="badge-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Falta rostro
                                    </span>
                                <?php elseif ($isHabilitado): ?>
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

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script>
    // Habilitar ciudadano para votar
async function habilitarCiudadano(id, nombre) {
    if (confirm(`¿Estás seguro de habilitar a ${nombre} para votar?`)) {
        try {
            console.log('Habilitando usuario ID:', id);
            
            const response = await fetch('/yo_voto/api/admin/habilitar', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',  // 🔑 IMPORTANTE: enviar cookies
                body: JSON.stringify({ id: id })
            });
            
            const result = await response.json();
            console.log('Respuesta:', result);
            
            if (result.success) {
                // Actualizar la fila de la tabla
                const fila = document.getElementById(`fila-${id}`);
                if (fila) {
                    const celdaHabilitado = fila.cells[4];
                    const celdaAcciones = fila.cells[7];
                    celdaHabilitado.innerHTML = '<span class="badge-si"><i class="fas fa-check"></i> Habilitado</span>';
                    celdaAcciones.innerHTML = '<span class="badge-si"><i class="fas fa-check"></i> Activo</span>';
                }
                alert(`✅ ${nombre} ha sido habilitado para votar`);
                location.reload(); // Recargar para actualizar la tabla
            } else {
                alert('❌ Error: ' + (result.error || 'No se pudo habilitar'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('❌ Error de conexión: ' + error.message);
        }
    }
}
    </script>
</body>
</html>