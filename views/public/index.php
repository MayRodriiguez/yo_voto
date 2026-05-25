<?php
require_once '../../config/auth.php';
soloUsuario();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Yo Voto - Sistema Electoral Bolivia</title>
    <link rel="stylesheet" href="/yo_voto/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #2e1a4a, #7c3aed); font-family: Arial, sans-serif; margin: 0; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: rgba(0,0,0,0.2); }
        .logo { color: white; font-size: 28px; font-weight: bold; }
        .logo span { color: #f5c518; }
        .menu a { color: white; text-decoration: none; margin-left: 25px; font-weight: bold; }
        .menu a:hover { color: #f5c518; }
        .hero { text-align: center; padding: 80px 20px; color: white; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; }
        .hero p { font-size: 20px; margin-bottom: 30px; }
        .btn-principal { background: #f5c518; color: #2e1a4a; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; font-size: 18px; display: inline-block; margin: 0 10px; }
        .btn-secundario { background: transparent; border: 2px solid white; color: white; padding: 12px 30px; border-radius: 30px; text-decoration: none; font-weight: bold; font-size: 18px; display: inline-block; margin: 0 10px; }
        .contenedor-principal { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; max-width: 1000px; margin: 60px auto; padding: 0 20px; }
        .boton-menu { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); color: white; height: 180px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-decoration: none; border-radius: 20px; font-size: 22px; font-weight: bold; transition: 0.3s; border: 1px solid rgba(255,255,255,0.2); }
        .boton-menu:hover { background: rgba(255,255,255,0.2); transform: scale(1.05); }
        .boton-menu .icono { font-size: 50px; margin-bottom: 15px; }
        footer { background: rgba(0,0,0,0.3); color: white; text-align: center; padding: 20px; margin-top: 40px; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo"> Yo <span>Voto</span></div>
        <nav class="menu">
            <a href="/yo_voto/public/resultados">Resultados</a>
            <a href="/yo_voto/public/votar">Votar</a>
            <a href="/yo_voto/login">Admin</a>
        </nav>
    </header>

    <div class="hero">
        <h1>¡Tu Voto es tu Voz!</h1>
        <p>Participa en las elecciones Bolivia 2026</p>
        <a href="/yo_voto/public/votar" class="btn-principal"> Votar Ahora</a>
        <a href="/yo_voto/login" class="btn-secundario"> Panel Admin</a>
    </div>

    <div class="contenedor-principal">
        <a href="/yo_voto/public/votar" class="boton-menu"><div class="icono"></div>Votar</a>
        <a href="/yo_voto/public/resultados" class="boton-menu"><div class="icono"></div>Resultados</a>
        <a href="/yo_voto/candidatos" class="boton-menu"><div class="icono"></div>Candidatos</a>
    </div>

    <footer>
        <p>Yo Voto - Sistema Electoral Bolivia 2026 | Democracia y Transparencia</p>
    </footer>
</body>
</html>