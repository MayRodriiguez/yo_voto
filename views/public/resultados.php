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
    <title>Yo Voto - Sistema Electoral Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Open Sans', sans-serif; min-height: 100vh; background: #0a1628; }

        /* NAVBAR */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(10,22,50,0.97); backdrop-filter: blur(10px);
            height: 60px; display: flex; align-items: center;
            padding: 0 40px; justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .navbar-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 20px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .navbar-logo span { color: #FF6B00; }
        .navbar-logo i { color: #FF6B00; font-size: 18px; }
        .navbar-nav { display: flex; align-items: center; gap: 4px; }
        .navbar-nav a {
            color: rgba(255,255,255,0.65); text-decoration: none; font-size: 13px; font-weight: 600;
            padding: 7px 13px; border-radius: 8px; transition: .2s;
            display: flex; align-items: center; gap: 6px; cursor: pointer;
        }
        .navbar-nav a:hover, .navbar-nav a.active { color: #fff; background: rgba(255,255,255,0.08); }
        .navbar-nav .btn-login { background: #FF6B00; color: #fff; }
        .navbar-nav .btn-login:hover { background: #FF8C38; }
        .navbar-nav .btn-logout { background: rgba(231,76,60,0.15); color: #ff6b6b; border: 1px solid rgba(231,76,60,0.3); }
        .navbar-nav .btn-logout:hover { background: #E74C3C; color: #fff; }
        .navbar-nav .user-name { color: #FF8C38; font-size: 13px; font-weight: 600; padding: 7px 13px; }
        .navbar-nav .btn-votar { background: rgba(39,174,96,0.15); color: #5cdb95; border: 1px solid rgba(39,174,96,0.3); }
        .navbar-nav .btn-votar:hover { background: #27AE60; color: #fff; }

        /* MAIN */
        .main {
            min-height: 100vh;
            background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%);
            padding: 80px 24px 60px;
            position: relative;
        }
        .main::before {
            content: ''; position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 36px 36px;
        }

        /* HERO */
        .hero { text-align: center; padding: 40px 20px 32px; position: relative; z-index: 1; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.35);
            color: #FF8C38; padding: 6px 18px; border-radius: 50px;
            font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase;
            margin-bottom: 16px;
        }
        .hero h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 42px; color: #fff; margin-bottom: 8px; }
        .hero h1 span { color: #FF6B00; }
        .hero p { color: rgba(255,255,255,0.45); font-size: 15px; }

        /* NAV SECCIONES */
        .sec-nav { display: flex; justify-content: center; gap: 10px; margin-bottom: 36px; flex-wrap: wrap; position: relative; z-index: 1; }
        .sec-nav button {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6); padding: 10px 22px; border-radius: 50px;
            font-size: 13px; font-weight: 700; cursor: pointer; transition: .2s;
            display: flex; align-items: center; gap: 8px; font-family: 'Open Sans', sans-serif;
        }
        .sec-nav button:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .sec-nav button.active { background: #FF6B00; border-color: #FF6B00; color: #fff; box-shadow: 0 4px 16px rgba(255,107,0,0.3); }

        /* CONTENEDOR */
        .container { max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }

        /* CARD */
        .card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 24px 60px rgba(0,0,0,0.4); margin-bottom: 24px;
        }
        .card-head {
            background: linear-gradient(135deg, #0d2251, #1a3a7a);
            border-bottom: 2px solid #FF6B00;
            padding: 20px 28px; display: flex; align-items: center; gap: 14px;
        }
        .card-head-icon { width: 42px; height: 42px; border-radius: 10px; background: #FF6B00; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .card-head-icon i { font-size: 18px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 17px; color: #fff; margin: 0; }
        .card-head p { font-size: 12px; color: rgba(255,255,255,0.4); margin: 3px 0 0; }
        .card-body { padding: 28px; }

        /* GRID CANDIDATOS */
        .candidatos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
        .candidato-card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 18px; overflow: hidden; cursor: pointer; transition: .25s;
        }
        .candidato-card:hover { border-color: rgba(255,107,0,0.4); transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.3); background: rgba(255,255,255,0.07); }
        .candidato-img { width: 100%; height: 180px; object-fit: cover; display: block; }
        .candidato-info { padding: 16px; text-align: center; }
        .candidato-nombre { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 15px; color: #fff; margin-bottom: 4px; }
        .candidato-partido { color: #FF8C38; font-size: 12px; margin-bottom: 4px; }
        .candidato-cargo { color: rgba(255,255,255,0.35); font-size: 11px; margin-bottom: 12px; }
        .btn-ver {
            background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.3);
            color: #FF8C38; padding: 7px 18px; border-radius: 20px; font-size: 12px;
            font-weight: 700; cursor: pointer; transition: .2s;
        }
        .btn-ver:hover { background: #FF6B00; color: #fff; border-color: #FF6B00; }

        /* PROPUESTAS */
        .propuesta-item {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-left: 3px solid #FF6B00; border-radius: 12px; padding: 16px; margin-bottom: 12px;
        }
        .propuesta-cat { font-size: 10px; font-weight: 700; color: #FF8C38; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .propuesta-titulo { font-weight: 700; color: #fff; font-size: 14px; margin-bottom: 4px; }
        .propuesta-desc { color: rgba(255,255,255,0.45); font-size: 13px; margin-bottom: 6px; }
        .propuesta-autor { font-size: 11px; color: rgba(255,255,255,0.3); }

        /* RESULTADOS */
        .total-votos-badge {
            background: rgba(255,107,0,0.08); border: 1px solid rgba(255,107,0,0.2);
            border-radius: 12px; padding: 16px 20px; text-align: center; margin-bottom: 24px;
            font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 18px;
        }
        .total-votos-badge span { color: #FF6B00; }
        .resultado-item { margin-bottom: 16px; }
        .resultado-top { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .resultado-nombre { font-weight: 700; color: #fff; font-size: 14px; }
        .resultado-votos { font-size: 13px; color: rgba(255,255,255,0.45); }
        .barra { height: 10px; background: rgba(255,255,255,0.07); border-radius: 10px; overflow: hidden; }
        .barra-fill { height: 100%; background: linear-gradient(90deg, #FF6B00, #FF8C38); border-radius: 10px; transition: width 1s ease; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px; }
        .barra-pct { font-size: 10px; font-weight: 700; color: #fff; }

        /* VERIFICAR JURADO */
        .form-field { margin-bottom: 20px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: rgba(255,255,255,0.5); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .form-input {
            width: 100%; padding: 13px 16px; background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12); border-radius: 10px;
            color: #fff; font-size: 15px; outline: none; transition: .2s;
            font-family: 'Open Sans', sans-serif;
        }
        .form-input:focus { border-color: #FF6B00; background: rgba(255,107,0,0.06); }
        .form-input::placeholder { color: rgba(255,255,255,0.2); }
        .form-error { font-size: 12px; color: #ff6b6b; margin-top: 6px; display: none; }
        .btn-verificar {
            width: 100%; padding: 14px; border-radius: 10px; border: none;
            background: #FF6B00; color: #fff; font-family: 'Montserrat', sans-serif;
            font-size: 15px; font-weight: 800; cursor: pointer; transition: .25s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-verificar:hover { background: #FF8C38; transform: translateY(-1px); }

        /* RESULTADOS JURADO */
        .jurado-result { border-radius: 16px; padding: 28px; text-align: center; margin-top: 24px; }
        .jurado-result.es-jurado { background: rgba(39,174,96,0.08); border: 1px solid rgba(39,174,96,0.2); }
        .jurado-result.no-jurado { background: rgba(255,152,0,0.08); border: 1px solid rgba(255,152,0,0.2); }
        .jurado-result i.icono { font-size: 44px; margin-bottom: 14px; display: block; }
        .jurado-result.es-jurado i.icono { color: #5cdb95; }
        .jurado-result.no-jurado i.icono { color: #FFB74D; }
        .jurado-result h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 18px; margin-bottom: 8px; }
        .jurado-result p { color: rgba(255,255,255,0.45); font-size: 14px; }
        .jurado-data { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px; margin-top: 16px; text-align: left; }
        .jurado-row { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 13px; }
        .jurado-row:last-child { border-bottom: none; }
        .jurado-row strong { color: rgba(255,255,255,0.5); min-width: 140px; }
        .jurado-row code { background: rgba(255,107,0,0.1); color: #FF8C38; padding: 2px 10px; border-radius: 6px; font-family: 'Courier New', monospace; }
        .jurado-row span { color: #fff; }

        /* MODAL CANDIDATO */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 200; overflow-y: auto; padding: 20px; }
        .modal-box { background: #0d2251; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; max-width: 700px; margin: 40px auto; overflow: hidden; animation: fadeUp .3s; }
        @keyframes fadeUp { from { opacity:0; transform: translateY(-30px); } to { opacity:1; transform: translateY(0); } }
        .modal-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .modal-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 18px; margin: 0; }
        .modal-close { background: none; border: none; color: rgba(255,255,255,0.5); font-size: 24px; cursor: pointer; transition: .2s; }
        .modal-close:hover { color: #ff6b6b; }
        .modal-body { padding: 28px; }
        .modal-cand-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 16px; display: block; border: 3px solid #FF6B00; }
        .modal-cand-name { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 20px; text-align: center; margin-bottom: 4px; }
        .modal-cand-partido { color: #FF8C38; font-size: 13px; text-align: center; margin-bottom: 6px; }
        .modal-cand-bio { color: rgba(255,255,255,0.45); font-size: 13px; text-align: center; margin-bottom: 20px; line-height: 1.6; }
        .modal-sec { font-size: 11px; font-weight: 700; color: #FF6B00; text-transform: uppercase; letter-spacing: 2px; margin: 20px 0 12px; padding-bottom: 8px; border-bottom: 1px solid rgba(255,107,0,0.25); }
        .nivel-titulo { display: inline-block; background: rgba(255,107,0,0.12); border: 1px solid rgba(255,107,0,0.3); color: #FF8C38; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 10px; }
        .integrantes-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
        .integrante-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-left: 3px solid #FF6B00; border-radius: 8px; padding: 8px 14px; min-width: 130px; }
        .integrante-nombre { font-weight: 700; color: #fff; font-size: 13px; }
        .integrante-cargo { color: rgba(255,255,255,0.35); font-size: 11px; }

        /* LOGIN MODAL */
        .login-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 300; align-items: center; justify-content: center; }
        .login-overlay.open { display: flex; }
        .login-box { background: #0d2251; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; width: 100%; max-width: 420px; overflow: hidden; animation: fadeUp .3s; }
        .login-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .login-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; color: #fff; font-size: 17px; margin: 0; }
        .login-body { padding: 28px; }
        .login-error { background: rgba(231,76,60,0.1); border: 1px solid rgba(231,76,60,0.3); color: #ff6b6b; border-radius: 10px; padding: 12px; font-size: 13px; text-align: center; margin-bottom: 16px; display: none; }
        .btn-login-submit {
            width: 100%; padding: 14px; border-radius: 10px; border: none;
            background: #FF6B00; color: #fff; font-family: 'Montserrat', sans-serif;
            font-size: 15px; font-weight: 800; cursor: pointer; transition: .25s;
        }
        .btn-login-submit:hover { background: #FF8C38; transform: translateY(-1px); }

        /* CAPTCHA */
        .captcha-box { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 14px; text-align: center; margin-bottom: 16px; }
        .captcha-pregunta { font-size: 18px; font-weight: 700; color: #FF8C38; margin-bottom: 10px; font-family: 'Montserrat', sans-serif; }

        /* LOADING */
        .loading { text-align: center; padding: 50px; color: rgba(255,255,255,0.3); font-size: 14px; }
        .loading i { font-size: 28px; margin-bottom: 10px; display: block; color: #FF6B00; }

        /* FOOTER */
        footer { background: #070e1f; color: rgba(255,255,255,0.3); text-align: center; padding: 28px; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.06); }
        footer span { color: #FF6B00; font-weight: 700; }

        @media (max-width: 768px) {
            .navbar { padding: 0 16px; }
            .hero h1 { font-size: 28px; }
            .candidatos-grid { grid-template-columns: 1fr 1fr; }
            .card-body { padding: 18px 14px; }
            .resultado-top { flex-direction: column; gap: 2px; }
        }
        @media (max-width: 480px) {
            .candidatos-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></a>
    <nav class="navbar-nav">
        <a href="#" onclick="mostrarSeccion('candidatos')" id="nav-candidatos" class="active"><i class="fas fa-users"></i> Candidatos</a>
        <a href="#" onclick="mostrarSeccion('propuestas')" id="nav-propuestas"><i class="fas fa-list-check"></i> Propuestas</a>
        <a href="#" onclick="mostrarSeccion('resultados')" id="nav-resultados"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="#" onclick="mostrarSeccion('verificar')" id="nav-verificar"><i class="fas fa-id-card"></i> Verificar Jurado</a>
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['rol'] == 'usuario'): ?>
            <span class="user-name"><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['user']['nombres']) ?></span>
            <?php if (!$_SESSION['user']['ya_voto'] && $_SESSION['user']['habilitado_voto']): ?>
                <a href="/yo_voto/votar" class="btn-votar"><i class="fas fa-vote-yea"></i> Votar</a>
            <?php endif; ?>
            <a href="/yo_voto/logout-votante" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Salir</a>
        <?php else: ?>
            <a href="#" onclick="abrirLogin()" class="btn-login"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
        <?php endif; ?>
    </nav>
</header>

<!-- MAIN -->
<main class="main">
    <div class="hero">
        <div class="hero-badge"><i class="fas fa-flag"></i> Elecciones Generales Bolivia 2026</div>
        <h1>Yo <span>Voto</span></h1>
        <p>Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p>
    </div>

    <!-- NAV SECCIONES -->
    <div class="sec-nav">
        <button onclick="mostrarSeccion('candidatos')" id="btn-candidatos" class="active"><i class="fas fa-users"></i> Candidatos</button>
        <button onclick="mostrarSeccion('propuestas')" id="btn-propuestas"><i class="fas fa-list-check"></i> Propuestas</button>
        <button onclick="mostrarSeccion('resultados')" id="btn-resultados"><i class="fas fa-chart-bar"></i> Resultados</button>
        <button onclick="mostrarSeccion('verificar')" id="btn-verificar"><i class="fas fa-id-card"></i> Verificar Jurado</button>
    </div>

    <div class="container">

        <!-- CANDIDATOS -->
        <div id="seccion-candidatos">
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-users"></i></div>
                    <div><h2>Candidatos a la Presidencia</h2><p>Conoce a los postulantes para Bolivia 2026</p></div>
                </div>
                <div class="card-body">
                    <div class="candidatos-grid" id="candidatos-grid">
                        <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando candidatos...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PROPUESTAS -->
        <div id="seccion-propuestas" style="display:none;">
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-list-check"></i></div>
                    <div><h2>Propuestas de Gobierno</h2><p>Las propuestas de cada candidato</p></div>
                </div>
                <div class="card-body" id="propuestas-container">
                    <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando propuestas...</div>
                </div>
            </div>
        </div>

        <!-- RESULTADOS -->
        <div id="seccion-resultados" style="display:none;">
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-chart-bar"></i></div>
                    <div><h2>Resultados Electorales</h2><p>Conteo en tiempo real</p></div>
                </div>
                <div class="card-body" id="resultados-container">
                    <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando resultados...</div>
                </div>
            </div>
        </div>

        <!-- VERIFICAR JURADO -->
        <div id="seccion-verificar" style="display:none;">
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-id-card"></i></div>
                    <div><h2>Verificar Estado de Jurado</h2><p>Ingresa tu CI para consultar</p></div>
                </div>
                <div class="card-body">
                    <div style="max-width:460px; margin: 0 auto;">
                        <div class="form-field">
                            <label class="form-label"><i class="fas fa-id-card"></i> Número de CI (8 dígitos)</label>
                            <input type="text" id="carnet-verificar" class="form-input" placeholder="Ej: 12345678" maxlength="8" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)">
                            <div class="form-error" id="carnet-error">La CI debe tener exactamente 8 dígitos numéricos.</div>
                        </div>
                        <button class="btn-verificar" onclick="verificarJurado()">
                            <i class="fas fa-search"></i> Verificar
                        </button>
                        <div id="resultado-verificar"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- MODAL CANDIDATO -->
<div class="modal-overlay" id="modalCandidato">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="modal-titulo">Candidato</h2>
            <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        </div>
    </div>
</div>

<!-- MODAL LOGIN -->
<div class="login-overlay" id="loginOverlay">
    <div class="login-box">
        <div class="login-head">
            <h2><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</h2>
            <button class="modal-close" onclick="cerrarLogin()"><i class="fas fa-times"></i></button>
        </div>
        <div class="login-body">
            <div class="login-error" id="login-error-message"></div>
            <form id="loginFormulario" method="POST" action="/yo_voto/login-votante">
                <div class="form-field">
                    <label class="form-label"><i class="fas fa-id-card"></i> Número de Carnet</label>
                    <input type="text" name="carnet" class="form-input" placeholder="Ej: 12345678" required maxlength="8">
                </div>
                <div class="form-field">
                    <label class="form-label"><i class="fas fa-lock"></i> Contraseña</label>
                    <input type="password" name="password" class="form-input" placeholder="Ingrese su contraseña" required>
                </div>
                <div class="captcha-box">
                    <div class="captcha-pregunta" id="captcha-pregunta"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
                    <input type="text" name="captcha" class="form-input" placeholder="Ingrese el resultado" required autocomplete="off" style="text-align:center; margin-top:8px;">
                </div>
                <input type="hidden" name="login_votante" value="1">
                <button type="submit" class="btn-login-submit"><i class="fas fa-vote-yea"></i> Ingresar a Votar</button>
            </form>
            <p style="text-align:center; margin-top:16px; font-size:12px; color:rgba(255,255,255,0.3);">
                <i class="fas fa-info-circle"></i> ¿No tienes cuenta? Contacta con el administrador electoral.
            </p>
        </div>
    </div>
</div>

<footer>
    <p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p>
</footer>

<script>
    let candidatosData = [];

    function mostrarSeccion(sec) {
        ['candidatos','propuestas','resultados','verificar'].forEach(s => {
            document.getElementById('seccion-' + s).style.display = s === sec ? 'block' : 'none';
            document.getElementById('btn-' + s).classList.toggle('active', s === sec);
            const nav = document.getElementById('nav-' + s);
            if (nav) nav.classList.toggle('active', s === sec);
        });
        if (sec === 'propuestas' && document.getElementById('propuestas-container').innerHTML.includes('Cargando')) cargarPropuestas();
        if (sec === 'candidatos' && document.getElementById('candidatos-grid').innerHTML.includes('Cargando')) cargarCandidatos();
        if (sec === 'resultados') cargarResultados();
    }

    async function cargarCandidatos() {
        try {
            const res = await fetch('/yo_voto/api/candidatos');
            candidatosData = await res.json();
            const grid = document.getElementById('candidatos-grid');
            if (!candidatosData.length) { grid.innerHTML = '<p style="color:rgba(255,255,255,0.3);text-align:center;">No hay candidatos registrados.</p>'; return; }
            grid.innerHTML = candidatosData.map(c => `
                <div class="candidato-card" onclick="verCandidato(${c.id_candidato})">
                    <img src="/yo_voto/${c.foto_url}" class="candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                    <div class="candidato-info">
                        <div class="candidato-nombre">${esc(c.nombre)}</div>
                        <div class="candidato-partido">${esc(c.partido)}</div>
                        <div class="candidato-cargo">Candidato a ${esc(c.cargo)}</div>
                        <button class="btn-ver"><i class="fas fa-eye"></i> Ver más</button>
                    </div>
                </div>
            `).join('');
        } catch(e) { document.getElementById('candidatos-grid').innerHTML = '<p style="color:#ff6b6b;text-align:center;">Error al cargar candidatos</p>'; }
    }

    async function verCandidato(id) {
        document.getElementById('modalCandidato').style.display = 'block';
        document.getElementById('modal-body').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
        try {
            const [cRes, eRes, pRes] = await Promise.all([
                fetch(`/yo_voto/api/candidato/${id}`),
                fetch(`/yo_voto/api/equipo/${id}`),
                fetch(`/yo_voto/api/propuestas/${id}`)
            ]);
            const c = await cRes.json(), equipo = await eRes.json(), propuestas = await pRes.json();
            document.getElementById('modal-titulo').textContent = c.nombre;
            let equipoHtml = Object.keys(equipo).length === 0
                ? '<p style="color:rgba(255,255,255,0.3);text-align:center;">No hay integrantes registrados.</p>'
                : Object.entries(equipo).map(([nivel, ints]) => `
                    <div style="margin-bottom:14px;">
                        <div class="nivel-titulo"><i class="fas fa-layer-group"></i> Nivel ${nivel}</div>
                        <div class="integrantes-grid">
                            ${ints.map(i => `<div class="integrante-card"><div class="integrante-nombre">${esc(i.nombre)}</div><div class="integrante-cargo">${esc(i.cargo)}</div></div>`).join('')}
                        </div>
                    </div>`).join('');
            let propHtml = !propuestas.length
                ? '<p style="color:rgba(255,255,255,0.3);">Sin propuestas registradas.</p>'
                : propuestas.map(p => `<div class="propuesta-item"><div class="propuesta-cat"><i class="fas fa-tag"></i> ${esc(p.categoria)}</div><div class="propuesta-titulo">${esc(p.titulo)}</div><div class="propuesta-desc">${esc(p.descripcion)}</div></div>`).join('');
            document.getElementById('modal-body').innerHTML = `
                <img src="/yo_voto/${c.foto_url}" class="modal-cand-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                <div class="modal-cand-name">${esc(c.nombre)}</div>
                <div class="modal-cand-partido">${esc(c.partido)} · Candidato a ${esc(c.cargo)}</div>
                <div class="modal-cand-bio">${esc(c.biografia || 'Sin biografía disponible.')}</div>
                <div class="modal-sec"><i class="fas fa-users"></i> Equipo de Campaña</div>
                ${equipoHtml}
                <div class="modal-sec"><i class="fas fa-list-check"></i> Propuestas</div>
                ${propHtml}`;
        } catch(e) { document.getElementById('modal-body').innerHTML = '<p style="color:#ff6b6b;text-align:center;">Error al cargar datos.</p>'; }
    }

    async function cargarPropuestas() {
        try {
            const res = await fetch('/yo_voto/api/propuestas');
            const propuestas = await res.json();
            const c = document.getElementById('propuestas-container');
            if (!propuestas.length) { c.innerHTML = '<p style="color:rgba(255,255,255,0.3);text-align:center;">No hay propuestas registradas.</p>'; return; }
            c.innerHTML = '<div class="candidatos-grid">' + propuestas.map(p => `
                <div class="propuesta-item" style="border-radius:14px;">
                    <div class="propuesta-cat"><i class="fas fa-tag"></i> ${esc(p.categoria)}</div>
                    <div class="propuesta-titulo">${esc(p.titulo)}</div>
                    <div class="propuesta-desc">${esc(p.descripcion.substring(0,100))}${p.descripcion.length > 100 ? '...' : ''}</div>
                    <div class="propuesta-autor"><i class="fas fa-user"></i> ${esc(p.candidato_nombre)} — ${esc(p.candidato_partido)}</div>
                </div>`).join('') + '</div>';
        } catch(e) {}
    }

    async function cargarResultados() {
        try {
            const res = await fetch('/yo_voto/api/resultados');
            const data = await res.json();
            const c = document.getElementById('resultados-container');
            if (!data.candidatos.length) { c.innerHTML = '<p style="color:rgba(255,255,255,0.3);text-align:center;">No hay resultados disponibles.</p>'; return; }
            let html = `<div class="total-votos-badge">Total de votos emitidos: <span>${data.total_votos}</span></div>`;
            data.candidatos.forEach(cand => {
                const pct = data.total_votos > 0 ? ((cand.votos_recibidos / data.total_votos) * 100).toFixed(1) : 0;
                html += `<div class="resultado-item">
                    <div class="resultado-top">
                        <div class="resultado-nombre">${esc(cand.nombre)}</div>
                        <div class="resultado-votos">${cand.votos_recibidos} votos · ${pct}%</div>
                    </div>
                    <div class="barra"><div class="barra-fill" style="width:${pct}%"></div></div>
                </div>`;
            });
            c.innerHTML = html;
        } catch(e) { document.getElementById('resultados-container').innerHTML = '<p style="color:#ff6b6b;text-align:center;">Error al cargar resultados</p>'; }
    }

    async function verificarJurado() {
        const carnet = document.getElementById('carnet-verificar').value;
        const errEl = document.getElementById('carnet-error');
        const resEl = document.getElementById('resultado-verificar');
        if (!carnet || carnet.length !== 8 || !/^\d+$/.test(carnet)) {
            errEl.style.display = 'block'; resEl.innerHTML = ''; return;
        }
        errEl.style.display = 'none';
        resEl.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Verificando...</div>';
        try {
            const res = await fetch(`/yo_voto/api/verificar-jurado?carnet=${carnet}`);
            const data = await res.json();
            if (data.es_jurado) {
                resEl.innerHTML = `<div class="jurado-result es-jurado">
                    <i class="fas fa-trophy icono"></i>
                    <h3>¡Eres Jurado Electoral!</h3>
                    <p>Has sido designado como Jurado en este proceso electoral.</p>
                    <div class="jurado-data">
                        <div class="jurado-row"><strong><i class="fas fa-user"></i> Usuario:</strong> <code>${esc(data.carnet)}</code></div>
                        <div class="jurado-row"><strong><i class="fas fa-lock"></i> Contraseña:</strong> <code>${esc(data.password || 'usuario123')}</code></div>
                        <div class="jurado-row"><strong><i class="fas fa-qrcode"></i> Código Jurado:</strong> <code>${esc(data.codigo_jurado)}</code></div>
                        <div class="jurado-row"><strong><i class="fas fa-table"></i> Mesa asignada:</strong> <span>Mesa ${esc(String(data.id_mesa))}</span></div>
                        <div class="jurado-row"><strong><i class="fas fa-gavel"></i> Cargo:</strong> <span>${esc(data.cargo_jurado)}</span></div>
                    </div>
                </div>`;
            } else {
                resEl.innerHTML = `<div class="jurado-result no-jurado">
                    <i class="fas fa-user-times icono"></i>
                    <h3>No eres Jurado Electoral</h3>
                    <p>${esc(data.nombres || 'El ciudadano')} no ha sido designado como jurado en este proceso electoral.</p>
                </div>`;
            }
        } catch(e) { resEl.innerHTML = '<p style="color:#ff6b6b;text-align:center;">Error al verificar</p>'; }
    }

    function abrirLogin() { document.getElementById('loginOverlay').classList.add('open'); cargarCaptcha(); }
    function cerrarLogin() { document.getElementById('loginOverlay').classList.remove('open'); }
    function cerrarModal() { document.getElementById('modalCandidato').style.display = 'none'; }

    function cargarCaptcha() {
        fetch('/yo_voto/api/captcha').then(r => r.json()).then(d => {
            document.getElementById('captcha-pregunta').innerHTML = '<i class="fas fa-calculator"></i> ' + (d.pregunta || d.captcha);
        }).catch(() => {});
    }

    function esc(t) {
        if (!t) return '';
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') { cerrarModal(); cerrarLogin(); } });
    window.onclick = e => {
        if (e.target === document.getElementById('modalCandidato')) cerrarModal();
        if (e.target === document.getElementById('loginOverlay')) cerrarLogin();
    };

    cargarCandidatos();

    <?php if ($error_login): ?>
    document.addEventListener('DOMContentLoaded', () => {
        abrirLogin();
        const err = document.getElementById('login-error-message');
        err.innerHTML = '<?= addslashes($error_login) ?>';
        err.style.display = 'block';
    });
    <?php endif; ?>
</script>
</body>
</html>
