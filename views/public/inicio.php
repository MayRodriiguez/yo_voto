<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error_login = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yo Voto - Sistema Electoral Bolivia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; }
        
        .header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: rgba(0,0,0,0.2); }
        .logo { color: white; font-size: 28px; font-weight: bold; }
        .logo span { color: #f5c518; }
        .menu a { color: white; text-decoration: none; margin-left: 25px; font-weight: bold; transition: 0.3s; }
        .menu a:hover { color: #f5c518; }
        
        .hero { text-align: center; padding: 60px 20px; color: white; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero p { font-size: 20px; margin-bottom: 30px; }
        .btn-principal { background: #f5c518; color: #003399; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; font-size: 18px; display: inline-block; margin: 0 10px; }
        .btn-secundario { background: transparent; border: 2px solid white; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; font-size: 18px; display: inline-block; margin: 0 10px; }
        
        .contenedor-principal { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .boton-menu { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); color: white; height: 160px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-decoration: none; border-radius: 20px; font-size: 22px; font-weight: bold; transition: 0.3s; border: 1px solid rgba(255,255,255,0.2); }
        .boton-menu:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
        .boton-menu .icono { font-size: 50px; margin-bottom: 15px; }
        
        .section-candidatos { background: white; border-radius: 30px; padding: 40px; margin: 40px auto; max-width: 1200px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .section-title { color: #003399; font-size: 28px; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #f5c518; text-align: center; }
        .candidatos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }
        .candidato-card { background: linear-gradient(135deg, #f8f5ff, #e8e0ff); border-radius: 20px; overflow: hidden; transition: 0.3s; cursor: pointer; border: 1px solid #003399; text-align: center; }
        .candidato-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,51,153,0.3); border-color: #f5c518; }
        .candidato-img { width: 100%; height: 200px; object-fit: cover; }
        .candidato-info { padding: 20px; }
        .candidato-nombre { font-size: 18px; font-weight: bold; color: #003399; margin-bottom: 5px; }
        .candidato-partido { color: #1a5bc4; font-size: 14px; margin-bottom: 5px; }
        .candidato-cargo { color: #666; font-size: 12px; margin-bottom: 15px; }
        .btn-ver { background: #003399; color: white; border: none; padding: 8px 20px; border-radius: 25px; cursor: pointer; transition: 0.3s; }
        .btn-ver:hover { background: #f5c518; color: #003399; }
        
        .modal-custom { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; overflow-y: auto; }
        .modal-content-custom { background: white; max-width: 500px; margin: 60px auto; border-radius: 30px; overflow: hidden; animation: modalFade 0.3s; }
        .modal-content-large { max-width: 800px; }
        @keyframes modalFade { from { opacity: 0; transform: translateY(-50px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header-custom { background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header-custom h2 { margin: 0; font-size: 24px; }
        .close-modal { background: none; border: none; color: white; font-size: 28px; cursor: pointer; }
        .modal-body-custom { padding: 30px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #003399; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 16px; }
        .form-group input:focus { outline: none; border-color: #f5c518; }
        
        .captcha-box { background: #e8e0ff; padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .captcha-codigo { font-size: 28px; font-weight: bold; letter-spacing: 8px; background: #003399; color: #f5c518; padding: 10px 20px; border-radius: 10px; display: inline-block; margin-bottom: 15px; font-family: monospace; }
        .captcha-input input { width: 180px; text-align: center; font-size: 16px; padding: 10px; border: 2px solid #c4b5fd; border-radius: 10px; margin: 0 auto; text-transform: uppercase; }
        .btn-recargar { background: none; border: none; color: #003399; cursor: pointer; font-size: 12px; margin-top: 10px; }
        .btn-recargar:hover { color: #f5c518; }
        
        .btn-login-submit { width: 100%; padding: 14px; background: linear-gradient(135deg, #003399, #1a5bc4); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 15px; }
        .btn-login-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,51,153,0.4); }
        
        .btn-facial { background: #003399; color: white; padding: 12px 25px; border-radius: 10px; border: none; margin: 10px 5px; transition: 0.3s; font-weight: bold; }
        .btn-facial:hover { background: #1a5bc4; transform: scale(1.02); }
        .btn-facial-danger { background: #dc2626; }
        .btn-facial-danger:hover { background: #b91c1c; }
        
        .alert-error { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        
        .face-preview { width: 100%; max-width: 300px; border-radius: 15px; margin: 0 auto; display: none; border: 3px solid #f5c518; }
        .face-status { margin-top: 15px; padding: 10px; border-radius: 8px; font-weight: bold; display: none; }
        .face-status.success { background: #d4edda; color: #155724; }
        .face-status.error { background: #f8d7da; color: #721c24; }
        .face-status.info { background: #e8e0ff; color: #003399; }
        
        .modal-candidato-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; display: block; border: 4px solid #f5c518; }
        .equipo-arbol { background: #e8e0ff; border-radius: 15px; padding: 20px; margin-top: 20px; }
        .nivel-equipo { margin-bottom: 20px; text-align: center; }
        .nivel-titulo { background: #003399; color: white; display: inline-block; padding: 5px 15px; border-radius: 20px; font-size: 12px; }
        .integrantes-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; }
        .integrante-card { background: white; padding: 10px 15px; border-radius: 10px; text-align: center; min-width: 120px; border-left: 3px solid #f5c518; }
        .integrante-nombre { font-weight: bold; color: #003399; }
        .propuesta-item { background: #e8e0ff; padding: 15px; border-radius: 10px; margin-bottom: 10px; border-left: 4px solid #f5c518; }
        .propuesta-titulo { font-weight: bold; color: #003399; }
        .loading { text-align: center; padding: 50px; color: #003399; }
        
        footer { text-align: center; padding: 30px; color: white; background: rgba(0,51,153,0.9); margin-top: 40px; }
        
        .admin-link-container { margin-top: 20px; text-align: center; }
        .separator { border: none; height: 1px; background: linear-gradient(90deg, transparent, #003399, #f5c518, #003399, transparent); margin: 20px 0; }
        .btn-admin-login { display: inline-flex; align-items: center; justify-content: center; gap: 10px; background: linear-gradient(135deg, #003399, #1a5bc4); color: white; padding: 10px 20px; border-radius: 50px; text-decoration: none; font-weight: bold; font-size: 14px; border: 2px solid #f5c518; width: 100%; }
        
        @media (max-width: 768px) {
            .header { flex-direction: column; gap: 15px; }
            .contenedor-principal { grid-template-columns: 1fr; }
            .hero h1 { font-size: 32px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">🗳️ Yo <span>Voto</span></div>
        <nav class="menu">
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] == 'usuario'): ?>
                <span style="color: #f5c518;">👤 <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
                <a href="/yo_voto/mi-perfil">Mi Perfil</a>
                <?php if ($_SESSION['user']['ya_voto'] == 0): ?>
                    <a href="/yo_voto/votar">Votar</a>
                <?php endif; ?>
                <a href="/yo_voto/logout-votante">Cerrar Sesión</a>
            <?php else: ?>
                <a href="#" onclick="mostrarModalLogin()">Iniciar Sesión</a>
                <a href="/yo_voto/login">Admin</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="hero">
        <h1>¡Tu Voto es tu Voz!</h1>
        <p>Participa en las elecciones Bolivia 2026</p>
        <?php if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario'): ?>
            <a href="#" onclick="mostrarModalLogin()" class="btn-principal">🗳️ Votar Ahora</a>
        <?php elseif ($_SESSION['user']['ya_voto'] == 0): ?>
            <a href="/yo_voto/votar" class="btn-principal">🗳️ Votar Ahora</a>
        <?php endif; ?>
        <a href="/yo_voto/login" class="btn-secundario">🔐 Panel Admin</a>
    </div>

    <div class="contenedor-principal">
        <a href="#" onclick="mostrarModalLogin()" class="boton-menu"><div class="icono">🗳️</div>Votar</a>
        <a href="/yo_voto/resultados" class="boton-menu"><div class="icono">📊</div>Resultados</a>
        <a href="#" class="boton-menu" onclick="document.getElementById('seccion-candidatos').scrollIntoView({behavior: 'smooth'})"><div class="icono">👥</div>Candidatos</a>
        <a href="/yo_voto/blockchain-verificar" class="boton-menu" style="margin-top: 10px;"><div class="icono">🔗</div>Blockchain</a>
    </div>

    <!-- Sección de Candidatos -->
    <div id="seccion-candidatos" class="section-candidatos">
        <h2 class="section-title"><i class="fas fa-users"></i> Candidatos a la Presidencia</h2>
        <div class="candidatos-grid" id="candidatos-grid">
            <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando candidatos...</div>
        </div>
    </div>

    <!-- Modal de Login - SOLO RECONOCIMIENTO FACIAL -->
    <div id="modalLogin" class="modal-custom">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
                <button class="close-modal" onclick="cerrarModalLogin()">&times;</button>
            </div>
            <div class="modal-body-custom">
                <div id="login-error-message" class="alert-error" style="display: none;"></div>
                
                <!-- Login con Reconocimiento Facial -->
                <div class="text-center mb-3">
                    <i class="fas fa-face-smile" style="font-size: 50px; color: #003399;"></i>
                    <p class="mt-2">Verifique su identidad con reconocimiento facial</p>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Número de Carnet</label>
                    <input type="text" id="face-carnet" class="form-control" placeholder="Ej: 12345678" maxlength="8">
                    <small class="text-muted">Ingrese su carnet para verificar su rostro</small>
                </div>
                
                <!-- CAPTCHA -->
                <div class="captcha-box">
                    <div class="captcha-codigo" id="face-captcha-codigo">Cargando...</div>
                    <div class="captcha-input">
                        <input type="text" id="face-captcha" class="form-control" placeholder="Ingrese el código" required>
                    </div>
                    <button type="button" class="btn-recargar" onclick="cargarCaptchaFacial()">
                        <i class="fas fa-sync-alt"></i> Recargar código
                    </button>
                </div>
                
                <div id="face-login-container" class="text-center">
                    <video id="login-video" class="face-preview" autoplay muted playsinline></video>
                    <div>
                        <button type="button" id="start-face-login-btn" class="btn-facial" onclick="startFaceLogin()">
                            <i class="fas fa-camera"></i> Activar Cámara y Verificar
                        </button>
                        <button type="button" id="stop-face-login-btn" class="btn-facial btn-facial-danger" onclick="stopFaceLogin()" style="display:none;">
                            <i class="fas fa-stop"></i> Detener
                        </button>
                    </div>
                </div>
                
                <div id="face-login-status" class="face-status"></div>
                
                <div class="admin-link-container mt-3">
                    <hr class="separator">
                    <a href="/yo_voto/login" class="btn-admin-login">
                        <i class="fas fa-user-shield"></i> ¿Eres Administrador? Haz clic aquí
                    </a>
                </div>
                <div class="text-center mt-3">
                    <small><i class="fas fa-info-circle"></i> ¿No tienes cuenta? <a href="/yo_voto/registro">Regístrate aquí</a></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Candidato -->
    <div id="modalCandidato" class="modal-custom">
        <div class="modal-content-custom modal-content-large">
            <div class="modal-header-custom">
                <h2 id="modal-titulo">Candidato</h2>
                <button class="close-modal" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body-custom" id="modal-body"><div class="loading">Cargando...</div></div>
        </div>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let loginVideo = null;
        let loginStream = null;
        let faceModelsLoaded = false;
        let recognitionInterval = null;

        // ============================================
        // FUNCIONES DEL CAPTCHA
        // ============================================
        function cargarCaptchaFacial() {
            fetch('/yo_voto/api/captcha')
                .then(response => response.json())
                .then(data => {
                    if (data.captcha) {
                        document.getElementById('face-captcha-codigo').innerHTML = data.captcha;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // ============================================
        // FUNCIONES DE RECONOCIMIENTO FACIAL
        // ============================================
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
                        console.error('❌ FaceAPI no detectado');
                        resolve(false);
                    }
                }, 500);
            });
        }

        async function loadFaceModels() {
            const statusDiv = document.getElementById('face-login-status');
            if (!statusDiv) return;
            
            statusDiv.style.display = 'block';
            statusDiv.className = 'face-status info';
            statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando modelos...';
            
            const available = await waitForFaceAPI();
            if (!available) {
                statusDiv.className = 'face-status error';
                statusDiv.innerHTML = '❌ No se pudo cargar FaceAPI. Verifica internet.';
                return;
            }
            
            try {
                const MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
                await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                faceModelsLoaded = true;
                statusDiv.className = 'face-status success';
                statusDiv.innerHTML = '✅ Modelos listos';
                setTimeout(() => statusDiv.style.display = 'none', 2000);
            } catch (error) {
                console.error('Error:', error);
                statusDiv.className = 'face-status error';
                statusDiv.innerHTML = '❌ Error: ' + error.message;
            }
        }

        async function startFaceLogin() {
            const carnet = document.getElementById('face-carnet').value;
            const captcha = document.getElementById('face-captcha').value;
            const statusDiv = document.getElementById('face-login-status');
            const errorDiv = document.getElementById('login-error-message');
            
            errorDiv.style.display = 'none';
            
            if (!carnet || carnet.length !== 8) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '⚠️ Ingrese un carnet válido de 8 dígitos';
                return;
            }
            
            if (!captcha) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '⚠️ Complete el código de seguridad';
                return;
            }
            
            if (!faceModelsLoaded) await loadFaceModels();
            if (!faceModelsLoaded) return;
            
            try {
                loginStream = await navigator.mediaDevices.getUserMedia({ video: true });
                const video = document.getElementById('login-video');
                video.srcObject = loginStream;
                video.style.display = 'block';
                
                document.getElementById('start-face-login-btn').style.display = 'none';
                document.getElementById('stop-face-login-btn').style.display = 'inline-block';
                
                statusDiv.style.display = 'block';
                statusDiv.className = 'face-status info';
                statusDiv.innerHTML = '🔍 Mire a la cámara...';
                
                startFaceRecognition(carnet, captcha);
            } catch (error) {
                errorDiv.style.display = 'block';
                errorDiv.innerHTML = '❌ Error de cámara: ' + error.message;
            }
        }

        async function startFaceRecognition(carnet, captcha) {
            const video = document.getElementById('login-video');
            const statusDiv = document.getElementById('face-login-status');
            
            if (recognitionInterval) clearInterval(recognitionInterval);
            
            recognitionInterval = setInterval(async () => {
                try {
                    const detections = await faceapi.detectSingleFace(video).withFaceLandmarks().withFaceDescriptor();
                    
                    if (detections) {
                        statusDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
                        
                        const response = await fetch('/yo_voto/api/face/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                carnet: carnet,
                                captcha: captcha,
                                descriptor: Array.from(detections.descriptor)
                            })
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            statusDiv.className = 'face-status success';
                            statusDiv.innerHTML = '✅ ¡Identificado! Redirigiendo...';
                            clearInterval(recognitionInterval);
                            setTimeout(() => window.location.href = '/yo_voto/mi-perfil', 1500);
                        } else {
                            statusDiv.className = 'face-status error';
                            statusDiv.innerHTML = '❌ ' + (result.error || 'Rostro no coincide');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    statusDiv.className = 'face-status error';
                    statusDiv.innerHTML = '❌ Error: ' + error.message;
                }
            }, 2000);
        }

        function stopFaceLogin() {
            if (recognitionInterval) clearInterval(recognitionInterval);
            if (loginStream) loginStream.getTracks().forEach(track => track.stop());
            const video = document.getElementById('login-video');
            if (video) video.srcObject = null;
            document.getElementById('start-face-login-btn').style.display = 'inline-block';
            document.getElementById('stop-face-login-btn').style.display = 'none';
        }

        // ============================================
        // FUNCIONES DEL MODAL
        // ============================================
        function mostrarModalLogin() {
            document.getElementById('modalLogin').style.display = 'block';
            cargarCaptchaFacial();
            document.getElementById('face-carnet').value = '';
            document.getElementById('face-captcha').value = '';
            stopFaceLogin();
        }
        
        function cerrarModalLogin() {
            document.getElementById('modalLogin').style.display = 'none';
            stopFaceLogin();
        }
        
        function cerrarModal() { 
            document.getElementById('modalCandidato').style.display = 'none'; 
        }
        
        function escapeHtml(text) { 
            if (!text) return ''; 
            const div = document.createElement('div'); 
            div.textContent = text; 
            return div.innerHTML; 
        }

        // ============================================
        // FUNCIONES DE CANDIDATOS
        // ============================================
        async function cargarCandidatos() {
            const grid = document.getElementById('candidatos-grid');
            grid.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
            try {
                const res = await fetch('/yo_voto/api/candidatos');
                const data = await res.json();
                if (!data.length) { grid.innerHTML = '<p>No hay candidatos</p>'; return; }
                grid.innerHTML = data.map(c => `
                    <div class="candidato-card" onclick="verCandidato(${c.id_candidato})">
                        <img src="/yo_voto/${c.foto_url}" class="candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                        <div class="candidato-info">
                            <div class="candidato-nombre">${escapeHtml(c.nombre)}</div>
                            <div class="candidato-partido">${escapeHtml(c.partido)}</div>
                            <div class="candidato-cargo">Candidato a ${escapeHtml(c.cargo)}</div>
                            <button class="btn-ver"><i class="fas fa-eye"></i> Ver más</button>
                        </div>
                    </div>
                `).join('');
            } catch(e) { 
                grid.innerHTML = '<p>Error al cargar candidatos</p>'; 
            }
        }

        async function verCandidato(id) {
            try {
                const [c, eq, prop] = await Promise.all([
                    fetch(`/yo_voto/api/candidato/${id}`).then(r=>r.json()),
                    fetch(`/yo_voto/api/equipo/${id}`).then(r=>r.json()),
                    fetch(`/yo_voto/api/propuestas/${id}`).then(r=>r.json())
                ]);
                document.getElementById('modal-titulo').innerHTML = c.nombre;
                document.getElementById('modal-body').innerHTML = `
                    <img src="/yo_voto/${c.foto_url}" class="modal-candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                    <h3>${escapeHtml(c.nombre)}</h3>
                    <p><strong>${escapeHtml(c.partido)}</strong> | ${escapeHtml(c.cargo)}</p>
                    <p>${escapeHtml(c.biografia || '')}</p>
                    <h4>Equipo</h4><div id="eq-container"></div>
                    <h4>Propuestas</h4><div id="prop-container"></div>
                `;
                const eqContainer = document.getElementById('eq-container');
                if (Object.keys(eq).length) {
                    eqContainer.innerHTML = Object.entries(eq).map(([nivel, ints]) => `
                        <div><strong>Nivel ${nivel}</strong><br>${ints.map(i=>`${escapeHtml(i.nombre)} (${escapeHtml(i.cargo)})`).join('<br>')}</div>
                    `).join('');
                } else eqContainer.innerHTML = '<p>Sin equipo</p>';
                const propContainer = document.getElementById('prop-container');
                if (prop.length) propContainer.innerHTML = prop.map(p=>`<div><strong>${escapeHtml(p.titulo)}</strong><br>${escapeHtml(p.descripcion)}</div>`).join('');
                else propContainer.innerHTML = '<p>Sin propuestas</p>';
                document.getElementById('modalCandidato').style.display = 'block';
            } catch(e) { 
                alert('Error al cargar los datos del candidato'); 
            }
        }

        // ============================================
        // INICIALIZACIÓN
        // ============================================
        document.addEventListener('DOMContentLoaded', () => { 
            cargarCandidatos(); 
            loadFaceModels(); 
            cargarCaptchaFacial();
        });
        
        window.onclick = e => { 
            if(e.target === document.getElementById('modalCandidato')) cerrarModal(); 
            if(e.target === document.getElementById('modalLogin')) cerrarModalLogin(); 
        }
        
        <?php if ($error_login): ?>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarModalLogin();
            document.getElementById('login-error-message').innerHTML = '<?= $error_login ?>';
            document.getElementById('login-error-message').style.display = 'block';
        });
        <?php endif; ?>
    </script>
</body>
</html>