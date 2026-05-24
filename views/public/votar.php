<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
    header("Location: /yo_voto/");
    exit();
}

require_once 'config/database.php';
require_once 'models/Candidato.php';
require_once 'models/Voto.php';
require_once 'models/User.php';

$db = new Database();
$conn = $db->getConnection();

$candidatoModel = new Candidato();
$votoModel = new Voto();
$userModel = new User();

$user = $_SESSION['user'];
$yaVoto = $userModel->yaVoto($user['id']);

// ============================================
// 👇 ESTO ES LO QUE FALTABA - OBTENER CANDIDATOS
// ============================================
$candidatos = $candidatoModel->getAllActivos();

$mensaje = $_SESSION['mensaje_voto'] ?? '';
$error = $_SESSION['error_voto'] ?? '';
unset($_SESSION['mensaje_voto']);
unset($_SESSION['error_voto']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votar - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a0a2e, #6a1b9a, #d32f2f);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        .votar-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .card-votar {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }
        .card-header {
            background: linear-gradient(135deg, #2e1a4a, #7c3aed);
            color: white;
            padding: 25px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 14px;
        }
        .card-body {
            padding: 30px;
        }
        .candidato-card {
            border: 2px solid #ede9fe;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
        }
        .candidato-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #7c3aed;
        }
        .candidato-card.selected {
            border-color: #7c3aed;
            background: #f8f5ff;
            box-shadow: 0 5px 15px rgba(124,58,237,0.2);
        }
        .candidato-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #7c3aed;
        }
        .candidato-nombre {
            font-weight: bold;
            font-size: 18px;
            color: #2e1a4a;
            margin-bottom: 5px;
        }
        .candidato-partido {
            color: #7c3aed;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .btn-votar {
            background: linear-gradient(135deg, #2e1a4a, #7c3aed);
            color: white;
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            transition: 0.3s;
        }
        .btn-votar:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(124,58,237,0.4);
        }
        .btn-votar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .alert {
            border-radius: 15px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 5px solid #dc2626;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .alert-warning {
            background: #fff3e0;
            color: #e65100;
            border-left: 5px solid #ff9800;
        }
        .ya-voto-mensaje {
            text-align: center;
            padding: 40px;
        }
        .ya-voto-mensaje .icono {
            font-size: 80px;
            margin-bottom: 20px;
        }
        .btn-cerrar {
            background: #dc2626;
            color: white;
            padding: 10px 25px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .inmutabilidad-badge {
            background: #e8e0ff;
            padding: 10px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #003399;
        }
        footer {
            text-align: center;
            padding: 20px;
            color: white;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="votar-container">
        <div class="card-votar">
            <div class="card-header">
                <h1>🗳️ Emitir tu Voto</h1>
                <p>Elecciones Generales Bolivia 2026</p>
                <div class="user-info">
                    <strong><?= htmlspecialchars($user['nombres']) ?> <?= htmlspecialchars($user['apellidos']) ?></strong> | 
                    Carnet: <?= htmlspecialchars($user['carnet']) ?>
                </div>
            </div>
            <div class="card-body">
                
                <!-- ============================================ -->
                <!-- MENSAJE DE ÉXITO AL VOTAR -->
                <!-- ============================================ -->
                <?php if ($mensaje): ?>
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle" style="font-size: 40px;"></i>
                        <h4>✅ ¡Voto Registrado Exitosamente!</h4>
                        <?= $mensaje ?>
                        <div class="inmutabilidad-badge mt-3">
                            <i class="fas fa-shield-alt"></i> <strong>VOTO INMUTABLE</strong><br>
                            <small>Su voto ha sido registrado en la blockchain y no puede ser modificado ni eliminado.</small>
                        </div>
                        <a href="/yo_voto/mi-perfil" class="btn btn-primary mt-3">Ir a mi Perfil</a>
                    </div>
                
                <!-- ============================================ -->
                <!-- MENSAJE DE ERROR (YA VOTÓ) -->
                <!-- ============================================ -->
                <?php elseif ($yaVoto): ?>
                    <div class="ya-voto-mensaje">
                        <div class="icono">⚠️</div>
                        <h3 style="color: #e65100;">¡USTED YA HA EMITIDO SU VOTO!</h3>
                        <p style="font-size: 16px; margin-top: 15px;">
                            <strong>Los votos son inmutables y no pueden ser modificados ni eliminados.</strong>
                        </p>
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <i class="fas fa-gavel"></i> <strong>¿Por qué no puedo votar nuevamente?</strong><br>
                            <small>El sistema electoral garantiza que cada ciudadano puede votar una sola vez. 
                            Su voto ya ha sido registrado en la blockchain y es irreversible.</small>
                        </div>
                        <div class="inmutabilidad-badge mt-3">
                            <i class="fas fa-link"></i> Hash de su transacción: 
                            <code><?= $_SESSION['bloque_voto']['hash'] ?? 'Registrado en blockchain' ?></code>
                        </div>
                        <a href="/yo_voto/mi-perfil" class="btn-cerrar">
                            <i class="fas fa-arrow-left"></i> Volver a mi Perfil
                        </a>
                    </div>
                
                <!-- ============================================ -->
                <!-- NO HAY CANDIDATOS -->
                <!-- ============================================ -->
                <?php elseif (empty($candidatos)): ?>
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle" style="font-size: 40px;"></i>
                        <h4>No hay candidatos disponibles</h4>
                        <p>En este momento no hay candidatos activos para votar.</p>
                        <a href="/yo_voto/mi-perfil" class="btn btn-secondary">Volver a mi Perfil</a>
                    </div>
                
                <!-- ============================================ -->
                <!-- FORMULARIO DE VOTACIÓN -->
                <!-- ============================================ -->
                <?php else: ?>
                
                <!-- Advertencia de inmutabilidad antes de votar -->
                <div class="alert alert-warning text-center">
                    <i class="fas fa-shield-alt"></i> 
                    <strong>IMPORTANTE:</strong> Una vez emitido, su voto <strong>NO PODRÁ SER MODIFICADO NI ELIMINADO</strong>.
                </div>
                
                <form method="POST" id="votoForm">
                    <div class="row">
                        <?php foreach ($candidatos as $c): ?>
                            <div class="col-md-4">
                                <div class="candidato-card" onclick="seleccionar(<?= $c['id_candidato'] ?>)">
                                    <img src="/yo_voto/<?= $c['foto_url'] ?>" class="candidato-img" 
                                         onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                                    <div class="candidato-nombre"><?= htmlspecialchars($c['nombre']) ?></div>
                                    <div class="candidato-partido"><?= htmlspecialchars($c['partido']) ?></div>
                                    <input type="radio" name="id_candidato" value="<?= $c['id_candidato'] ?>" 
                                           id="cand_<?= $c['id_candidato'] ?>" style="display: none;">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center mt-4">
                        <p id="seleccion-texto" style="color: #7c3aed; font-weight: bold;"></p>
                    </div>

                    <button type="submit" class="btn-votar" id="btnVotar" disabled>
                        <i class="fas fa-vote-yea"></i> Confirmar y Emitir Voto (Irreversible)
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="/yo_voto/mi-perfil" style="color: #dc2626; text-decoration: none;">
                        <i class="fas fa-times"></i> Cancelar y volver a mi perfil
                    </a>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
        
        <div class="inmutabilidad-badge mt-3" style="background: rgba(0,0,0,0.3); color: white;">
            <i class="fas fa-link"></i> Todos los votos son registrados en blockchain | Inmutabilidad garantizada
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Tu voto es secreto, seguro e inmutable</p>
    </footer>

    <script>
        let candidatoSeleccionado = null;
        
        function seleccionar(id) {
            document.querySelectorAll('.candidato-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            const cardSeleccionado = document.querySelector('.candidato-card input[value="' + id + '"]')?.closest('.candidato-card');
            if (cardSeleccionado) {
                cardSeleccionado.classList.add('selected');
                document.getElementById('cand_' + id).checked = true;
                document.getElementById('btnVotar').disabled = false;
                const nombre = cardSeleccionado.querySelector('.candidato-nombre').innerText;
                document.getElementById('seleccion-texto').innerHTML = '✅ Has seleccionado a: <strong>' + nombre + '</strong>';
            }
        }
        
        // Confirmación adicional al enviar el formulario
        const form = document.getElementById('votoForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const seleccionado = document.querySelector('input[name="id_candidato"]:checked');
                if (seleccionado) {
                    const card = seleccionado.closest('.candidato-card');
                    const nombre = card ? card.querySelector('.candidato-nombre').innerText : 'este candidato';
                    if (!confirm(`⚠️ ADVERTENCIA\n\nEstás a punto de votar por: ${nombre}\n\nEste voto es IRREVERSIBLE y no podrá ser modificado.\n\n¿Estás seguro de continuar?`)) {
                        e.preventDefault();
                    }
                } else {
                    e.preventDefault();
                    alert('Por favor selecciona un candidato primero');
                }
            });
        }
    </script>
</body>
</html>