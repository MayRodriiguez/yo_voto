<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_SESSION['error_registro'] ?? null;
$success = $_SESSION['success_registro'] ?? null;
unset($_SESSION['error_registro']);
unset($_SESSION['success_registro']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Ciudadano - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- FaceAPI - Versión UMD que funciona directamente en navegador -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        
        .navbar { background: rgba(0,51,153,0.95); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logo { font-size: 28px; font-weight: bold; color: #f5c518; }
        .logo span { color: white; }
        .nav-links a { color: white; text-decoration: none; margin-left: 25px; transition: 0.3s; }
        .nav-links a:hover { color: #f5c518; }
        
        .container-custom { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card-registro { background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 25px; text-align: center; }
        .card-header h1 { font-size: 28px; margin-bottom: 5px; }
        .card-header p { opacity: 0.9; }
        .card-body { padding: 30px; }
        
        .form-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #f5c518; }
        .form-section h3 { color: #003399; margin-bottom: 20px; font-size: 18px; }
        .form-label { font-weight: bold; color: #003399; }
        .form-control { border: 1px solid #c4b5fd; border-radius: 10px; padding: 10px 15px; }
        .form-control:focus { border-color: #f5c518; box-shadow: none; }
        
        .biometric-area { background: linear-gradient(135deg, #e8e0ff, #f8f5ff); border-radius: 15px; padding: 25px; text-align: center; border: 2px dashed #003399; }
        .btn-facial { background: #003399; color: white; padding: 12px 25px; border-radius: 10px; border: none; margin: 10px 5px; transition: 0.3s; font-weight: bold; }
        .btn-facial:hover { background: #1a5bc4; transform: scale(1.02); }
        .btn-facial-danger { background: #dc2626; }
        .btn-facial-danger:hover { background: #b91c1c; }
        .btn-registrar { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 14px; border: none; border-radius: 10px; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-registrar:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,51,153,0.3); }
        .btn-registrar:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .face-preview { width: 100%; max-width: 300px; border-radius: 15px; margin: 0 auto; display: none; border: 3px solid #f5c518; }
        .face-status { margin-top: 15px; padding: 10px; border-radius: 8px; font-weight: bold; display: none; }
        .face-status.success { background: #d4edda; color: #155724; }
        .face-status.error { background: #f8d7da; color: #721c24; }
        .face-status.info { background: #e8e0ff; color: #003399; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        footer { text-align: center; padding: 30px; color: white; background: rgba(0,51,153,0.9); margin-top: 40px; }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 15px; }
            .card-body { padding: 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">🗳️ Yo <span>Voto</span></div>
        <div class="nav-links">
            <a href="/yo_voto/">Inicio</a>
            <a href="/yo_voto/resultados">Resultados</a>
            <a href="/yo_voto/login">Admin</a>
        </div>
    </nav>

    <div class="container-custom">
        <div class="card-registro">
            <div class="card-header">
                <h1><i class="fas fa-user-plus"></i> Registro de Ciudadano</h1>
                <p>Regístrese para poder votar en las elecciones Bolivia 2026</p>
            </div>
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                        <br><br>
                        <a href="/yo_voto/" class="btn btn-primary">Ir a Iniciar Sesión</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/yo_voto/registro-ciudadano" id="registroForm">
                    <!-- DATOS PERSONALES -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> DATOS PERSONALES</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombres *</label>
                                <input type="text" name="nombres" id="nombres" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Apellidos *</label>
                                <input type="text" name="apellidos" id="apellidos" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Carnet de Identidad *</label>
                                <input type="text" name="carnet" id="carnet" class="form-control" maxlength="8" placeholder="8 dígitos" required>
                                <small class="text-muted">Ej: 12345678 (8 dígitos)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Fecha de Nacimiento *</label>
                                <input type="date" name="fecha_nac" id="fecha_nac" class="form-control" required>
                                <small class="text-muted">Debe ser mayor de 18 años</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" placeholder="Ej: 77712345">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control" placeholder="Calle, número, zona">
                            </div>
                        </div>
                    </div>

                    <!-- REGISTRO FACIAL -->
                    <div class="form-section">
                        <h3><i class="fas fa-face-smile"></i> REGISTRO FACIAL</h3>
                        <div class="biometric-area">
                            <i class="fas fa-camera" style="font-size: 50px; color: #003399;"></i>
                            <p class="mt-2">Registre su rostro para poder iniciar sesión fácilmente</p>
                            
                            <div id="face-register-container">
                                <video id="register-video" class="face-preview" autoplay muted playsinline></video>
                                <div>
                                    <button type="button" id="start-camera-btn" class="btn-facial" onclick="startRegisterCamera()">
                                        <i class="fas fa-camera"></i> Activar Cámara
                                    </button>
                                    <button type="button" id="capture-face-btn" class="btn-facial" onclick="captureAndRegisterFace()" style="display:none;">
                                        <i class="fas fa-camera-retro"></i> Capturar Rostro
                                    </button>
                                    <button type="button" id="stop-camera-btn" class="btn-facial btn-facial-danger" onclick="stopRegisterCamera()" style="display:none;">
                                        <i class="fas fa-stop"></i> Detener Cámara
                                    </button>
                                </div>
                            </div>
                            
                            <div id="face-status" class="face-status info" style="display: none;">
                                <i class="fas fa-info-circle"></i> <span id="face-status-text">Esperando acción...</span>
                            </div>
                            <input type="hidden" id="face_registered" name="face_registered" value="0">
                            <input type="hidden" id="face_descriptor" name="face_descriptor" value="">
                        </div>
                    </div>

                    <button type="submit" class="btn-registrar" id="submit-btn" disabled>
                        <i class="fas fa-save"></i> Registrarme
                    </button>
                    <p class="text-muted text-center mt-2"><small>⚠️ Debe capturar su rostro antes de registrarse</small></p>
                </form>
                
                <div class="text-center mt-4">
                    <small><i class="fas fa-info-circle"></i> ¿Ya tienes cuenta? <a href="/yo_voto/">Inicia sesión aquí</a></small>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script>
        let registerVideo = null;
        let registerStream = null;
        let faceDescriptor = null;
        let modelsLoaded = false;

        // Función para esperar a que faceAPI esté disponible
        async function waitForFaceAPI() {
            return new Promise((resolve) => {
                let checks = 0;
                const interval = setInterval(() => {
                    checks++;
                    if (typeof faceapi !== 'undefined' && faceapi.nets) {
                        clearInterval(interval);
                        console.log('✅ FaceAPI detectado');
                        resolve(true);
                    } else if (checks > 30) {
                        clearInterval(interval);
                        console.error('❌ FaceAPI no detectado después de 15 segundos');
                        resolve(false);
                    } else {
                        console.log(`⏳ Esperando FaceAPI... intento ${checks}`);
                    }
                }, 500);
            });
        }

        // ============================================
        // CARGAR MODELOS DESDE CDN (NO requiere archivos locales)
        // ============================================
        async function loadFaceModels() {
            const statusDiv = document.getElementById('face-status');
            statusDiv.style.display = 'block';
            document.getElementById('face-status-text').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando modelos de reconocimiento facial...';
            statusDiv.className = 'face-status info';
            
            const available = await waitForFaceAPI();
            if (!available) {
                document.getElementById('face-status-text').innerHTML = '❌ No se pudo cargar FaceAPI. Verifica tu conexión a internet.';
                statusDiv.className = 'face-status error';
                return;
            }
            
            try {
                // Cargar modelos desde CDN de GitHub (requiere internet)
                console.log('Cargando modelos desde CDN...');
                const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
                
                await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                
                modelsLoaded = true;
                document.getElementById('face-status-text').innerHTML = '✅ Modelos cargados correctamente';
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 2000);
            } catch (error) {
                console.error('Error detallado:', error);
                document.getElementById('face-status-text').innerHTML = '❌ Error al cargar modelos: ' + error.message;
                statusDiv.className = 'face-status error';
            }
        }

        // Iniciar cámara para registro
        async function startRegisterCamera() {
            const carnet = document.getElementById('carnet').value;
            
            if (!carnet || carnet.length !== 8) {
                showFaceStatus('⚠️ Primero ingrese su número de carnet (8 dígitos)', 'error');
                return;
            }
            
            const video = document.getElementById('register-video');
            
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showFaceStatus('❌ Tu navegador no soporta la cámara', 'error');
                return;
            }
            
            if (!modelsLoaded) {
                await loadFaceModels();
            }
            
            if (!modelsLoaded) {
                showFaceStatus('❌ Los modelos no se cargaron correctamente. Recarga la página.', 'error');
                return;
            }
            
            try {
                registerStream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = registerStream;
                video.style.display = 'block';
                
                document.getElementById('start-camera-btn').style.display = 'none';
                document.getElementById('capture-face-btn').style.display = 'inline-block';
                document.getElementById('stop-camera-btn').style.display = 'inline-block';
                
                document.getElementById('submit-btn').disabled = true;
                
                showFaceStatus('🔍 Cámara activada. Mire directamente a la cámara.', 'info');
                
            } catch (error) {
                showFaceStatus('❌ Error al acceder a la cámara: ' + error.message, 'error');
            }
        }

        // Capturar y registrar rostro
        async function captureAndRegisterFace() {
            const video = document.getElementById('register-video');
            const carnet = document.getElementById('carnet').value;
            
            if (!carnet || carnet.length !== 8) {
                showFaceStatus('⚠️ Ingrese un carnet válido de 8 dígitos', 'error');
                return;
            }
            
            if (!modelsLoaded) {
                showFaceStatus('⚠️ Espere a que los modelos terminen de cargar', 'error');
                return;
            }
            
            showFaceStatus('🔍 Detectando rostro...', 'info');
            
            try {
                const detections = await faceapi.detectSingleFace(video)
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                if (!detections) {
                    showFaceStatus('❌ No se detectó ningún rostro. Asegúrese de estar bien iluminado.', 'error');
                    return;
                }
                
                faceDescriptor = detections.descriptor;
                
                // Guardar descriptor en el campo oculto
                document.getElementById('face_registered').value = '1';
                document.getElementById('face_descriptor').value = JSON.stringify(Array.from(faceDescriptor));
                
                // Habilitar el botón de registro
                document.getElementById('submit-btn').disabled = false;
                
                showFaceStatus('✅ ¡Rostro capturado exitosamente! Ya puede registrarse.', 'success');
                
                // Detener cámara después de capturar
                setTimeout(() => {
                    stopRegisterCamera();
                }, 2000);
                
            } catch (error) {
                console.error('Error en captura:', error);
                showFaceStatus('❌ Error: ' + error.message, 'error');
            }
        }

        // Detener cámara
        function stopRegisterCamera() {
            if (registerStream) {
                registerStream.getTracks().forEach(track => track.stop());
                registerStream = null;
            }
            const video = document.getElementById('register-video');
            if (video) {
                video.srcObject = null;
                video.style.display = 'none';
            }
            
            document.getElementById('start-camera-btn').style.display = 'inline-block';
            document.getElementById('capture-face-btn').style.display = 'none';
            document.getElementById('stop-camera-btn').style.display = 'none';
        }

        // Mostrar estado
        function showFaceStatus(message, type) {
            const statusDiv = document.getElementById('face-status');
            const statusText = document.getElementById('face-status-text');
            
            statusDiv.style.display = 'block';
            statusText.innerHTML = message;
            statusDiv.className = 'face-status ' + type;
            
            if (type === 'success') {
                setTimeout(() => {
                    statusDiv.style.display = 'none';
                }, 4000);
            }
        }

        // Validar carnet (solo números, 8 dígitos)
        document.getElementById('carnet').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
        });

        // Validar formulario antes de enviar
        document.getElementById('registroForm').addEventListener('submit', function(e) {
            const faceRegistered = document.getElementById('face_registered').value;
            
            if (faceRegistered !== '1') {
                e.preventDefault();
                showFaceStatus('⚠️ Debe capturar su rostro antes de registrarse', 'error');
                return false;
            }
            
            const faceDescriptorValue = document.getElementById('face_descriptor').value;
            if (!faceDescriptorValue || faceDescriptorValue === '') {
                e.preventDefault();
                showFaceStatus('⚠️ Error: No se capturó el descriptor facial', 'error');
                return false;
            }
            
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
            return true;
        });

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página cargada, iniciando FaceAPI...');
            loadFaceModels();
            document.getElementById('submit-btn').disabled = true;
        });
    </script>
</body>
</html>