<?php 
// views/admin/equipo.php - VERSIÓN CORREGIDA
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'admin') {
    header("Location: /yo_voto/login");
    exit();
}

require_once 'config/database.php';
require_once 'models/Candidato.php';
require_once 'models/Equipo.php';

$database = new Database();
$conn = $database->getConnection();

$candidatoModel = new Candidato();
$equipoModel = new Equipo();

$id_candidato = isset($param) ? $param : (isset($_GET['id']) ? $_GET['id'] : null);
if (!$id_candidato) {
    header("Location: /yo_voto/candidatos");
    exit();
}

$candidato = $candidatoModel->getById($id_candidato);
if (!$candidato) {
    header("Location: /yo_voto/candidatos");
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $cargo = $_POST['cargo'];
    $nivel = $_POST['nivel'];
    
    $sql = "INSERT INTO equipo (id_candidato, nombre, cargo, nivel) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $id_candidato, $nombre, $cargo, $nivel);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = " Integrante agregado exitosamente";
    } else {
        $_SESSION['error'] = " Error al agregar integrante";
    }
    header("Location: /yo_voto/equipo/$id_candidato");
    exit();
}

// equipo organizado por niveles
$sql = "SELECT * FROM equipo WHERE id_candidato = ? ORDER BY nivel ASC, id_integrante ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_candidato);
$stmt->execute();
$result = $stmt->get_result();

$niveles = [];
while ($row = $result->fetch_assoc()) {
    $niveles[$row['nivel']][] = $row;
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
    <title>Equipo de <?= htmlspecialchars($candidato['nombre']) ?> - Yo Voto</title>
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
        .form-container { background: white; border-radius: 20px; padding: 25px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-label { font-weight: bold; color: #003399; }
        .form-control, .form-select { border: 1px solid #c4b5fd; border-radius: 10px; padding: 10px 15px; }
        .btn-guardar { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 10px 25px; border: none; border-radius: 10px; font-weight: bold; }
        .equipo-container { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .candidato-header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #f5c518; }
        .candidato-header img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #f5c518; }
        .nivel-equipo { margin-bottom: 30px; }
        .nivel-titulo { background: #003399; color: white; display: inline-block; padding: 5px 20px; border-radius: 20px; font-size: 14px; margin-bottom: 15px; }
        .integrantes-grid { display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; }
        .integrante-card { background: #f8f5ff; border-radius: 15px; padding: 15px; text-align: center; min-width: 180px; border-left: 4px solid #f5c518; position: relative; }
        .integrante-nombre { font-weight: bold; color: #003399; font-size: 16px; }
        .integrante-cargo { color: #1a5bc4; font-size: 12px; margin-top: 5px; }
        .btn-eliminar { background: #dc2626; color: white; border: none; padding: 5px 12px; border-radius: 5px; margin-top: 10px; font-size: 11px; cursor: pointer; }
        .btn-eliminar:hover { background: #b91c1c; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
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
            <div class="page-title"><i class="fas fa-users"></i> Equipo de <?= htmlspecialchars($candidato['nombre']) ?></div>
            <a href="/yo_voto/candidatos" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert-success"><i class="fas fa-check-circle"></i> <?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <!-- Formulario para agregar integrante -->
        <div class="form-container">
            <h4><i class="fas fa-plus-circle"></i> Agregar Nuevo Integrante</h4>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre completo *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Cargo *</label>
                        <input type="text" name="cargo" class="form-control" placeholder="Ej: Vicepresidente, Asesor" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nivel en el árbol</label>
                        <select name="nivel" class="form-select">
                            <option value="1">Nivel 1 (Primera fila)</option>
                            <option value="2">Nivel 2 (Segunda fila)</option>
                            <option value="3">Nivel 3 (Tercera fila)</option>
                            <option value="4">Nivel 4 (Cuarta fila)</option>
                            <option value="5">Nivel 5 (Quinta fila)</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn-guardar w-100"><i class="fas fa-save"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Visualización del equipo -->
        <div class="equipo-container">
            <div class="candidato-header">
                <img src="/yo_voto/<?= $candidato['foto_url'] ?>" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                <h3 style="color: #003399; margin-top: 15px;"><?= htmlspecialchars($candidato['nombre']) ?></h3>
                <p style="color: #1a5bc4;">Candidato a <?= htmlspecialchars($candidato['cargo']) ?></p>
            </div>

            <?php if (empty($niveles)): ?>
                <p class="text-center text-muted">No hay integrantes registrados en el equipo.</p>
            <?php else: ?>
                <?php for ($nivel = 1; $nivel <= 5; $nivel++): ?>
                    <?php if (isset($niveles[$nivel]) && !empty($niveles[$nivel])): ?>
                        <div class="nivel-equipo">
                            <div class="nivel-titulo"><i class="fas fa-layer-group"></i> Nivel <?= $nivel ?></div>
                            <div class="integrantes-grid">
                                <?php foreach ($niveles[$nivel] as $integrante): ?>
                                    <div class="integrante-card">
                                        <div class="integrante-nombre"><?= htmlspecialchars($integrante['nombre']) ?></div>
                                        <div class="integrante-cargo"><?= htmlspecialchars($integrante['cargo']) ?></div>
                                      <a href="/yo_voto/equipo/editar/<?= $integrante['id_integrante'] ?>/<?= $id_candidato ?>" 
   style="background:#ff9800;color:white;border:none;padding:5px 12px;border-radius:5px;margin-top:10px;font-size:11px;text-decoration:none;display:inline-block;">
    <i class="fas fa-edit"></i> Editar
</a>
<button class="btn-eliminar" onclick="if(confirm('¿Eliminar a <?= addslashes($integrante['nombre']) ?>?')) location.href='/yo_voto/equipo/eliminar/<?= $integrante['id_integrante'] ?>/<?= $id_candidato ?>'">
    <i class="fas fa-trash"></i> Eliminar
</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>
</body>
</html>