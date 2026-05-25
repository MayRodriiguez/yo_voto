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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518);
            min-height: 100vh;
        }
        
        /* 
           MENÚ DE NAVEGACIÓN
        */
        .navbar {
            background: rgba(0,51,153,0.95);
            backdrop-filter: blur(10px);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #f5c518;
        }
        
        .logo span {
            color: white;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
            padding: 8px 16px;
            border-radius: 25px;
        }
        
        .nav-links a:hover, .nav-links a.active {
            background: #f5c518;
            color: #003399;
        }
        
        .btn-login-nav {
            background: #f5c518;
            color: #003399 !important;
            font-weight: bold;
        }
        
        .btn-login-nav:hover {
            background: #ffdd44;
            transform: scale(1.05);
        }
        
        .user-info {
            background: rgba(245,197,24,0.2);
            padding: 8px 16px;
            border-radius: 25px;
            color: white;
        }
        
        .user-info i {
            margin-right: 8px;
            color: #f5c518;
        }
        
        .btn-logout {
            background: #dc2626;
            color: white !important;
        }
        
        /* 
           HERO PRINCIPAL
         */
        .hero {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .hero p {
            font-size: 20px;
            opacity: 0.95;
        }
        
        /* 
           CONTENEDOR PRINCIPAL
         */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .section {
            background: white;
            border-radius: 30px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .section-title {
            color: #003399;
            font-size: 28px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #f5c518;
            display: inline-block;
        }
        
        /* 
           TARJETAS DE CANDIDATOS
         */
        .candidatos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .candidato-card {
            background: linear-gradient(135deg, #f8f5ff, #e8e0ff);
            border-radius: 20px;
            overflow: hidden;
            transition: 0.3s;
            cursor: pointer;
            border: 1px solid #003399;
        }
        
        .candidato-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,51,153,0.3);
            border-color: #f5c518;
        }
        
        .candidato-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .candidato-info {
            padding: 20px;
            text-align: center;
        }
        
        .candidato-nombre {
            font-size: 20px;
            font-weight: bold;
            color: #003399;
            margin-bottom: 5px;
        }
        
        .candidato-partido {
            color: #1a5bc4;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .candidato-cargo {
            color: #666;
            font-size: 12px;
        }
        
        .btn-ver {
            background: #003399;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            margin-top: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-ver:hover {
            background: #f5c518;
            color: #003399;
        }
        
        /* 
           MODALES
      */
        .modal-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            overflow-y: auto;
        }
        
        .modal-content-custom {
            background: white;
            max-width: 450px;
            margin: 80px auto;
            border-radius: 30px;
            overflow: hidden;
            animation: modalFade 0.3s;
        }
        
        .modal-content-large {
            max-width: 800px;
        }
        
        @keyframes modalFade {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header-custom {
            background: linear-gradient(135deg, #003399, #1a5bc4);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header-custom h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
        }
        
        .modal-body-custom {
            padding: 30px;
        }
        
        /*
           FORMULARIO DE LOGIN*/
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #003399;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #f5c518;
        }
        
        .captcha-box {
            background: #e8e0ff;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .captcha-pregunta {
            font-size: 20px;
            font-weight: bold;
            color: #003399;
            margin-bottom: 10px;
        }
        
        .captcha-input {
            text-align: center;
        }
        
        .captcha-input input {
            width: 120px;
            text-align: center;
            font-size: 18px;
            padding: 10px;
        }
        
        .btn-login-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #003399, #1a5bc4);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .btn-login-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,51,153,0.4);
            background: linear-gradient(135deg, #1a5bc4, #003399);
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        /* 
           MODAL CANDIDATO
         */
        .modal-candidato-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 4px solid #f5c518;
        }
        
        .equipo-arbol {
            background: #e8e0ff;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .nivel-equipo {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .nivel-titulo {
            background: #003399;
            color: white;
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .integrantes-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        
        .integrante-card {
            background: white;
            padding: 10px 15px;
            border-radius: 10px;
            text-align: center;
            min-width: 120px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 3px solid #f5c518;
        }
        
        .integrante-nombre {
            font-weight: bold;
            color: #003399;
        }
        
        .integrante-cargo {
            font-size: 11px;
            color: #1a5bc4;
        }
        
        .propuesta-item {
            background: #e8e0ff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #f5c518;
        }
        
        .propuesta-titulo {
            font-weight: bold;
            color: #003399;
        }
        
        .propuesta-categoria {
            font-size: 11px;
            color: #1a5bc4;
            margin-bottom: 5px;
        }
        
        /* 
           RESULTADOS
         */
        .resultado-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #e8e0ff;
            border-radius: 10px;
            flex-wrap: wrap;
        }
        
        .resultado-nombre {
            flex: 2;
            font-weight: bold;
            min-width: 150px;
            color: #003399;
        }
        
        .resultado-votos {
            flex: 1;
            min-width: 80px;
        }
        
        .barra {
            flex: 3;
            height: 25px;
            background: #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            min-width: 150px;
        }
        
        .barra-fill {
            height: 100%;
            background: linear-gradient(90deg, #003399, #f5c518);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 10px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #003399;
        }
        
        footer {
            text-align: center;
            padding: 30px;
            color: white;
            background: rgba(0,51,153,0.9);
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 15px;
            }
            .nav-links {
                justify-content: center;
            }
            .hero h1 {
                font-size: 32px;
            }
            .resultado-item {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            .barra {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- MENÚ DE NAVEGACIÓN -->
    <nav class="navbar">
        <div class="logo">Yo <span>Voto</span></div>
        <div class="nav-links">
            <a href="#" class="active" onclick="mostrarSeccion('candidatos')"><i class="fas fa-users"></i> Inicio</a>
            <a href="#" onclick="mostrarSeccion('candidatos')"><i class="fas fa-user-check"></i> Candidatos</a>
            <a href="#" onclick="mostrarSeccion('propuestas')"><i class="fas fa-list-check"></i> Propuestas</a>
            <a href="#" onclick="mostrarSeccion('resultados')"><i class="fas fa-chart-bar"></i> Resultados</a>
            <a href="#" onclick="mostrarSeccion('verificar')"><i class="fas fa-id-card"></i> Verificar Jurado</a>
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] == 'usuario'): ?>
                <span class="user-info"><i class="fas fa-user-check"></i> <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
                <a href="/yo_voto/votar" class="btn-login-nav"><i class="fas fa-vote-yea"></i> Votar</a>
                <a href="/yo_voto/logout-votante" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
            <?php else: ?>
                <a href="#" onclick="mostrarModalLogin()" class="btn-login-nav"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- HERO PRINCIPAL -->
    <div class="hero">
        <h1>Yo Voto</h1>
        <p>Sistema Electoral Bolivia 2026</p>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="main-container">
        <!-- Sección Candidatos -->
        <div id="seccion-candidatos" class="section">
            <h2 class="section-title"><i class="fas fa-users"></i> Candidatos a la Presidencia</h2>
            <div class="candidatos-grid" id="candidatos-grid">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando candidatos...</div>
            </div>
        </div>

        <!-- Sección Propuestas -->
        <div id="seccion-propuestas" class="section" style="display: none;">
            <h2 class="section-title"><i class="fas fa-list-check"></i> Propuestas de Gobierno</h2>
            <div id="propuestas-container">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando propuestas...</div>
            </div>
        </div>

        <!-- Sección Resultados -->
        <div id="seccion-resultados" class="section" style="display: none;">
            <h2 class="section-title"><i class="fas fa-chart-bar"></i> Resultados Electorales</h2>
            <div id="resultados-container">
                <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando resultados...</div>
            </div>
        </div>

        <!-- Sección Verificar Jurado -->
        <div id="seccion-verificar" class="section" style="display: none;">
            <h2 class="section-title"><i class="fas fa-id-card"></i> Verificar Estado de Jurado</h2>
            
            <div style="max-width: 500px; margin: 0 auto;">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] == 'usuario'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> ¡Bienvenido! Has iniciado sesión correctamente.
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Ingresa tu número de CI (8 dígitos):</label>
                    <input type="text" id="carnet-verificar" class="form-control" placeholder="Ej: 12345678" maxlength="8" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,8)">
                    <small id="carnet-error" style="color: #dc2626; display: none;"> La CI debe tener exactamente 8 dígitos numéricos.</small>
                </div>
                
                <button class="btn-ver" onclick="verificarJurado()" style="width: 100%;">
                    <i class="fas fa-search"></i> Verificar
                </button>
                
                <div id="resultado-verificar" style="margin-top: 30px;"></div>
            </div>
        </div>
    </div>

    <!-- MODAL DE INICIO DE SESIÓN -->
    <div id="modalLogin" class="modal-custom">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
                <button class="close-modal" onclick="cerrarModalLogin()">&times;</button>
            </div>
            <div class="modal-body-custom">
                <div id="login-error-message" class="alert-error" style="display: none;"></div>
                
                <form id="loginFormulario" method="POST" action="/yo_voto/login-votante">
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Número de Carnet</label>
                        <input type="text" name="carnet" id="login-carnet" placeholder="Ej: 12345678" required autocomplete="off" maxlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="password" id="login-password" placeholder="Ingrese su contraseña" required>
                    </div>
                    
                    <div class="captcha-box">
                        <div class="captcha-pregunta" id="captcha-pregunta">Cargando...</div>
                        <div class="captcha-input">
                            <input type="text" name="captcha" id="login-captcha" placeholder="Ingrese el resultado" required autocomplete="off">
                        </div>
                    </div>
                    
                    <input type="hidden" name="login_votante" value="1">
                    
                    <button type="submit" class="btn-login-submit" id="btnSubmitLogin">
                        <i class="fas fa-vote-yea"></i> Ingresar a Votar
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small style="color: #666;">
                        <i class="fas fa-info-circle"></i> ¿No tienes cuenta? Contacta con el administrador electoral.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CANDIDATO -->
    <div id="modalCandidato" class="modal-custom">
        <div class="modal-content-custom modal-content-large">
            <div class="modal-header-custom">
                <h2 id="modal-titulo">Candidato</h2>
                <button class="close-modal" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="modal-body-custom" id="modal-body">
                <div class="loading">Cargando...</div>
            </div>
        </div>
    </div>

    <footer>
        <p><i class="fas fa-gavel"></i> Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>

    <script>
        let candidatosData = [];

        // Mostrar sección
        function mostrarSeccion(seccion) {
            document.getElementById('seccion-candidatos').style.display = seccion === 'candidatos' ? 'block' : 'none';
            document.getElementById('seccion-propuestas').style.display = seccion === 'propuestas' ? 'block' : 'none';
            document.getElementById('seccion-resultados').style.display = seccion === 'resultados' ? 'block' : 'none';
            document.getElementById('seccion-verificar').style.display = seccion === 'verificar' ? 'block' : 'none';
            
            document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
            event.target.classList.add('active');
            
            if (seccion === 'propuestas' && document.getElementById('propuestas-container').innerHTML.includes('Cargando propuestas')) {
                cargarPropuestas();
            }
            if (seccion === 'candidatos' && document.getElementById('candidatos-grid').innerHTML.includes('Cargando candidatos')) {
                cargarCandidatos();
            }
            if (seccion === 'resultados') {
                cargarResultados();
            }
        }

        // Cargar resultados
        async function cargarResultados() {
            try {
                const response = await fetch('/yo_voto/api/resultados');
                const data = await response.json();
                const container = document.getElementById('resultados-container');
                
                if (data.candidatos.length === 0) {
                    container.innerHTML = '<p class="text-center">No hay resultados disponibles.</p>';
                    return;
                }
                
                let html = `<div class="total-votos" style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8e0ff; border-radius: 15px;">
                    <h3> Total de votos emitidos: <strong>${data.total_votos}</strong></h3>
                </div>`;
                
                data.candidatos.forEach(c => {
                    const porcentaje = data.total_votos > 0 ? ((c.votos_recibidos / data.total_votos) * 100).toFixed(1) : 0;
                    html += `
                        <div class="resultado-item">
                            <div class="resultado-nombre">${escapeHtml(c.nombre)}</div>
                            <div class="resultado-votos">${c.votos_recibidos} votos</div>
                            <div class="barra">
                                <div class="barra-fill" style="width: ${porcentaje}%;">${porcentaje}%</div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('resultados-container').innerHTML = '<p class="text-center text-danger">Error al cargar resultados</p>';
            }
        }

        // Verificar Jurado
        async function verificarJurado() {
            const carnet = document.getElementById('carnet-verificar').value;
            const resultadoDiv = document.getElementById('resultado-verificar');
            const carnetError = document.getElementById('carnet-error');
            
            // Validar carnet
            if (!carnet) {
                carnetError.style.display = 'block';
                carnetError.innerHTML = ' Ingrese un número de carnet';
                resultadoDiv.innerHTML = '';
                return;
            }
            
            if (carnet.length !== 8 || !/^\d+$/.test(carnet)) {
                carnetError.style.display = 'block';
                carnetError.innerHTML = ' La CI debe tener exactamente 8 dígitos numéricos.';
                resultadoDiv.innerHTML = '';
                return;
            }
            
            carnetError.style.display = 'none';
            resultadoDiv.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Verificando...</div>';
            
            try {
                const response = await fetch(`/yo_voto/api/verificar-jurado?carnet=${carnet}`);
                const data = await response.json();
                
                if (data.es_jurado) {
                    resultadoDiv.innerHTML = `
                        <div style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); border-radius: 20px; padding: 25px; text-align: center; border: 2px solid #4caf50;">
                            <i class="fas fa-trophy" style="font-size: 50px; color: #ff9800; margin-bottom: 15px;"></i>
                            <h3 style="color: #2e7d32; margin-bottom: 15px;"> Usted es jurado</h3>
                            <p style="font-size: 18px; color: #2e7d32;">Has sido designado como <strong>Jurado Electoral</strong>.</p>
                            <div style="background: white; border-radius: 15px; padding: 20px; margin-top: 20px; text-align: left;">
                                <p><strong><i class="fas fa-user"></i> Tu usuario:</strong> <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 5px;">${data.carnet}</code></p>
                                <p><strong><i class="fas fa-lock"></i> Tu contraseña:</strong> <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 5px;">${data.password || 'ange2006'}</code></p>
                                <p><strong><i class="fas fa-qrcode"></i> Código de Jurado:</strong> <code style="background: #f0f0f0; padding: 5px 10px; border-radius: 5px;">${data.codigo_jurado}</code></p>
                                <p><strong><i class="fas fa-table"></i> Mesa asignada:</strong> Mesa ${data.id_mesa}</p>
                                <p><strong><i class="fas fa-gavel"></i> Cargo:</strong> ${data.cargo_jurado}</p>
                            </div>
                        </div>
                    `;
                } else {
                    resultadoDiv.innerHTML = `
                        <div style="background: #fff3e0; border-radius: 20px; padding: 25px; text-align: center; border: 2px solid #ff9800;">
                            <i class="fas fa-user-times" style="font-size: 50px; color: #ff9800; margin-bottom: 15px;"></i>
                            <h3 style="color: #e65100;">No eres Jurado Electoral</h3>
                            <p style="color: #666;">Luego de la revisión, <strong>${data.nombres || 'el ciudadano'}</strong> no ha sido designado como jurado en este proceso electoral.</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultadoDiv.innerHTML = '<div class="alert-error">Error al verificar</div>';
            }
        }

        // Cargar candidatos
        async function cargarCandidatos() {
            try {
                const response = await fetch('/yo_voto/api/candidatos');
                candidatosData = await response.json();
                
                const grid = document.getElementById('candidatos-grid');
                if (candidatosData.length === 0) {
                    grid.innerHTML = '<p style="text-align: center; color: #666;">No hay candidatos registrados.</p>';
                    return;
                }
                
                grid.innerHTML = candidatosData.map(c => `
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
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('candidatos-grid').innerHTML = '<p style="text-align: center; color: red;">Error al cargar candidatos</p>';
            }
        }

        // Ver candidato completo
        async function verCandidato(id) {
            try {
                const [candidatoRes, equipoRes, propuestasRes] = await Promise.all([
                    fetch(`/yo_voto/api/candidato/${id}`),
                    fetch(`/yo_voto/api/equipo/${id}`),
                    fetch(`/yo_voto/api/propuestas/${id}`)
                ]);
                
                const c = await candidatoRes.json();
                const equipo = await equipoRes.json();
                const propuestas = await propuestasRes.json();
                
                document.getElementById('modal-titulo').innerHTML = c.nombre;
                
                const modalBody = document.getElementById('modal-body');
                modalBody.innerHTML = `
                    <img src="/yo_voto/${c.foto_url}" class="modal-candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                    <div style="text-align: center;">
                        <h3>${escapeHtml(c.nombre)}</h3>
                        <p style="color: #1a5bc4;"><strong>${escapeHtml(c.partido)}</strong> | Candidato a ${escapeHtml(c.cargo)}</p>
                        <p style="color: #666; margin-top: 10px;">${escapeHtml(c.biografia || 'Sin biografía disponible')}</p>
                        <p style="margin-top: 10px;"><strong> Fecha de postulación:</strong> ${c.fecha_postulacion}</p>
                    </div>
                    
                    <h4 style="margin-top: 25px;"><i class="fas fa-users"></i> Equipo de Campaña</h4>
                    <div class="equipo-arbol" id="equipo-container"></div>
                    
                    <h4 style="margin-top: 25px;"><i class="fas fa-list-check"></i> Propuestas</h4>
                    <div id="propuestas-candidato"></div>
                `;
                
                // Equipo
                const equipoContainer = document.getElementById('equipo-container');
                if (Object.keys(equipo).length === 0) {
                    equipoContainer.innerHTML = '<p style="text-align: center;">No hay integrantes registrados.</p>';
                } else {
                    equipoContainer.innerHTML = Object.entries(equipo).map(([nivel, integrantes]) => `
                        <div class="nivel-equipo">
                            <div class="nivel-titulo"><i class="fas fa-layer-group"></i> Nivel ${nivel}</div>
                            <div class="integrantes-grid">
                                ${integrantes.map(i => `
                                    <div class="integrante-card">
                                        <div class="integrante-nombre">${escapeHtml(i.nombre)}</div>
                                        <div class="integrante-cargo">${escapeHtml(i.cargo)}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('');
                }
                
                // Propuestas
                const propContainer = document.getElementById('propuestas-candidato');
                if (propuestas.length === 0) {
                    propContainer.innerHTML = '<p>Este candidato no tiene propuestas registradas.</p>';
                } else {
                    propContainer.innerHTML = propuestas.map(p => `
                        <div class="propuesta-item">
                            <div class="propuesta-categoria"><i class="fas fa-tag"></i> ${escapeHtml(p.categoria)}</div>
                            <div class="propuesta-titulo">${escapeHtml(p.titulo)}</div>
                            <p style="margin-top: 5px; color: #666;">${escapeHtml(p.descripcion)}</p>
                        </div>
                    `).join('');
                }
                
                document.getElementById('modalCandidato').style.display = 'block';
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Cargar propuestas
        async function cargarPropuestas() {
            try {
                const response = await fetch('/yo_voto/api/propuestas');
                const propuestas = await response.json();
                
                const container = document.getElementById('propuestas-container');
                if (propuestas.length === 0) {
                    container.innerHTML = '<p style="text-align: center;">No hay propuestas registradas.</p>';
                    return;
                }
                
                container.innerHTML = `
                    <div class="candidatos-grid">
                        ${propuestas.map(p => `
                            <div class="candidato-card" style="cursor: default;">
                                <div style="padding: 20px;">
                                    <div class="propuesta-categoria"><i class="fas fa-tag"></i> ${escapeHtml(p.categoria)}</div>
                                    <div class="candidato-nombre" style="font-size: 18px;">${escapeHtml(p.titulo)}</div>
                                    <p style="color: #666; margin: 10px 0;">${escapeHtml(p.descripcion.substring(0, 100))}${p.descripcion.length > 100 ? '...' : ''}</p>
                                    <div style="color: #1a5bc4; font-size: 12px; margin-top: 10px;">
                                        <i class="fas fa-user"></i> ${escapeHtml(p.candidato_nombre)} - ${escapeHtml(p.candidato_partido)}
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Función para cargar captcha
        function cargarCaptcha() {
            fetch('/yo_voto/api/captcha')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-pregunta').innerHTML = '<i class="fas fa-calculator"></i> ' + data.pregunta;
                })
                .catch(error => console.error('Error cargando captcha:', error));
        }

        // Mostrar modal login
        function mostrarModalLogin() {
            document.getElementById('modalLogin').style.display = 'block';
            document.getElementById('login-error-message').style.display = 'none';
            document.getElementById('loginFormulario').reset();
            cargarCaptcha();
        }
        
        function cerrarModalLogin() {
            document.getElementById('modalLogin').style.display = 'none';
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
        
        // Manejar el envío del formulario de login
        document.getElementById('loginFormulario').addEventListener('submit', function(e) {
            // Aqui no prevenimos el envío, dejamos que el formulario se envíe normalmente
            // El captcha se validará en el servidor
        });
        
        // Cerrar modal con ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                cerrarModal();
                cerrarModalLogin();
            }
        });
        
        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalCandidato');
            const modalLogin = document.getElementById('modalLogin');
            if (event.target === modal) cerrarModal();
            if (event.target === modalLogin) cerrarModalLogin();
        }
        
        // Inicializarlo
        cargarCandidatos();
        
        // Mostrar error de login si existe
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