<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Yo Voto</title>
<<<<<<< HEAD
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Open Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px; position: relative;
        }
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 36px 36px;
        }

        /* LOGO ARRIBA */
        .top-logo {
            font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 22px;
            color: #fff; display: flex; align-items: center; gap: 8px;
            margin-bottom: 32px; position: relative; z-index: 1;
        }
        .top-logo span { color: #FF6B00; }
        .top-logo i { color: #FF6B00; }

        /* CARD */
        .card {
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px; width: 100%; max-width: 440px;
            overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4);
            position: relative; z-index: 1;
        }
        .card-head {
            background: linear-gradient(135deg, #0d2251, #1a3a7a);
            border-bottom: 2px solid #FF6B00;
            padding: 28px; text-align: center;
        }
        .card-head-icon {
            width: 60px; height: 60px; border-radius: 16px; background: #FF6B00;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
        }
        .card-head-icon i { font-size: 26px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 22px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 6px 0 0; }
        .card-body { padding: 30px 28px; }

        /* ALERT */
        .alert-danger {
            background: rgba(231,76,60,0.12); color: #ff6b6b;
            padding: 12px 14px; border-radius: 10px; margin-bottom: 20px;
            font-size: 13px; border-left: 4px solid #E74C3C;
            display: flex; align-items: center; gap: 8px;
        }

        /* FORM */
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.65); margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        .input-wrap { position: relative; }
        .input-wrap .ico { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.22); font-size: 13px; pointer-events: none; }
        .form-group input {
            width: 100%; padding: 12px 13px 12px 36px;
            border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px;
            font-size: 14px; background: rgba(255,255,255,0.06);
            color: #fff; transition: .2s; font-family: inherit;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.22); }
        .form-group input:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.09); box-shadow: 0 0 0 3px rgba(255,107,0,0.1); }

        /* CAPTCHA */
        .captcha-box {
            background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px; padding: 18px; text-align: center; margin-bottom: 20px;
        }
        .captcha-codigo {
            font-size: 30px; font-weight: 900; letter-spacing: 10px;
            background: #FF6B00; color: #fff;
            padding: 10px 24px; border-radius: 10px;
            display: inline-block; margin-bottom: 14px; font-family: monospace;
            user-select: none;
        }
        .captcha-input input {
            width: 180px; text-align: center; font-size: 16px;
            padding: 10px 14px; border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px; background: rgba(255,255,255,0.06);
            color: #fff; font-family: inherit; text-transform: uppercase;
            transition: .2s;
        }
        .captcha-input input::placeholder { color: rgba(255,255,255,0.25); }
        .captcha-input input:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.09); }
        .btn-recargar {
            background: none; border: none; color: rgba(255,255,255,0.4);
            margin-top: 10px; cursor: pointer; font-size: 12px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 5px; transition: .2s;
        }
        .btn-recargar:hover { color: #FF6B00; }

        /* SUBMIT */
        .btn-submit {
            width: 100%; padding: 14px; background: #FF6B00; color: #fff;
            border: none; border-radius: 11px;
            font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px;
            cursor: pointer; transition: .25s;
            box-shadow: 0 6px 24px rgba(255,107,0,0.35);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-submit:hover { background: #FF8C38; transform: translateY(-2px); box-shadow: 0 10px 32px rgba(255,107,0,0.5); }

        /* FOOTER LINK */
        .back-link { text-align: center; margin-top: 20px; font-size: 13px; color: rgba(255,255,255,0.3); position: relative; z-index: 1; }
        .back-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
        .back-link a:hover { text-decoration: underline; }

        footer { margin-top: 28px; font-size: 12px; color: rgba(255,255,255,0.2); position: relative; z-index: 1; }
        footer span { color: #FF6B00; }
    </style>
</head>
<body>

    <div class="top-logo"><i class="fas fa-envelope"></i> Yo <span>Voto</span></div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-user-shield"></i></div>
            <h2>Panel Admin</h2>
            <p>Acceso exclusivo para administradores</p>
        </div>
        <div class="card-body">

            <?php if (isset($error)): ?>
                <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
=======
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #003399, #1a5bc4, #f5c518); min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-container { width: 100%; max-width: 450px; padding: 20px; }
        .login-card { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 25px 50px rgba(0,0,0,0.3); text-align: center; border-top: 5px solid #f5c518; }
        .logo { font-size: 60px; margin-bottom: 10px; }
        h1 { color: #003399; font-size: 28px; margin-bottom: 5px; }
        .subtitle { color: #1a5bc4; margin-bottom: 30px; font-size: 14px; font-weight: bold; }
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: #003399; }
        .form-group input { width: 100%; padding: 14px 16px; border: 2px solid #c4b5fd; border-radius: 12px; font-size: 16px; transition: 0.3s; }
        .form-group input:focus { outline: none; border-color: #f5c518; box-shadow: 0 0 0 3px rgba(245,197,24,0.2); }
        .captcha-box { background: #e8e0ff; padding: 15px; border-radius: 12px; margin-bottom: 20px; border: 1px solid #f5c518; text-align: center; }
        .captcha-codigo { font-size: 28px; font-weight: bold; letter-spacing: 8px; background: #003399; color: #f5c518; padding: 10px 20px; border-radius: 10px; display: inline-block; margin-bottom: 15px; font-family: monospace; }
        .captcha-input { text-align: center; }
        .captcha-input input { width: 180px; text-align: center; font-size: 16px; padding: 10px; border: 2px solid #c4b5fd; border-radius: 10px; text-transform: uppercase; }
        .btn-recargar { background: none; border: none; color: #003399; margin-top: 10px; cursor: pointer; font-size: 12px; }
        .btn-recargar:hover { color: #f5c518; }
        .btn-login { width: 100%; padding: 14px; background: linear-gradient(135deg, #003399, #1a5bc4); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: bold; cursor: pointer; border-bottom: 3px solid #f5c518; transition: 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,51,153,0.4); }
        .alert-danger { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #dc2626; }
        .footer-links { margin-top: 20px; font-size: 12px; color: #888; }
        .footer-links a { color: #003399; text-decoration: none; font-weight: bold; transition: 0.3s; }
        .footer-links a:hover { color: #f5c518; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">🗳️</div>
            <h1>Yo Voto</h1>
            <p class="subtitle">Panel de Administración</p>
            
            <?php if (isset($error)): ?>
                <div class="alert-danger"><?= $error ?></div>
>>>>>>> 14bf65808c01528e1449c8356f81b4b5f8f1154f
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
<<<<<<< HEAD
                    <label><i class="fas fa-envelope"></i> Correo Electrónico</label>
                    <div class="input-wrap">
                        <i class="ico fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="admin@yovoto.com" required>
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-wrap">
                        <i class="ico fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Ingrese su contraseña" required>
                    </div>
                </div>

                <div style="display:flex;justify-content:center;margin-bottom:20px;">
                    <div class="h-captcha" data-sitekey="a22ee458-031e-489e-93ee-1aa2545e7aa2"></div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-sign-in-alt"></i> Ingresar al Panel
                </button>
            </form>
        </div>
    </div>

    <div class="back-link"><a href="/yo_voto/"><i class="fas fa-arrow-left"></i> Volver al inicio</a></div>
    <footer>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026</footer>

    <script>
        // Validar que hCaptcha esté completado antes de enviar
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const hcaptcha = document.querySelector('[name="h-captcha-response"]');
            if (!hcaptcha || !hcaptcha.value) {
                e.preventDefault();
                alert('Por favor complete el captcha de seguridad.');
=======
                    <label>📧 Email</label>
                    <input type="email" name="email" id="email" placeholder="admin@yovoto.com" required>
                </div>
                <div class="form-group">
                    <label>🔐 Contraseña</label>
                    <input type="password" name="password" id="password" placeholder="Ingrese su contraseña" required>
                </div>
                
                <!-- CAPTCHA DE TEXTO -->
                <div class="captcha-box">
                    <div class="captcha-codigo" id="captcha-codigo"><?= $_SESSION['captcha_codigo'] ?? 'XXXXXX' ?></div>
                    <div class="captcha-input">
                        <input type="text" name="captcha" id="captcha" placeholder="Ingrese el código" required>
                    </div>
                    <button type="button" class="btn-recargar" onclick="recargarCaptcha()">
                        <i class="fas fa-sync-alt"></i> Recargar código
                    </button>
                </div>
                
                <button type="submit" class="btn-login">Ingresar al Panel</button>
            </form>
            <div class="footer-links">
                <a href="/yo_voto/">← Volver al Inicio</a>
            </div>
        </div>
    </div>

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        function recargarCaptcha() {
            fetch('/yo_voto/api/captcha')
                .then(response => response.json())
                .then(data => {
                    if (data.captcha) {
                        document.getElementById('captcha-codigo').innerHTML = data.captcha;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Recargar CAPTCHA al cargar la página si está vacío
        document.addEventListener('DOMContentLoaded', function() {
            const captchaCodigo = document.getElementById('captcha-codigo');
            if (captchaCodigo && captchaCodigo.innerHTML === 'XXXXXX') {
                recargarCaptcha();
>>>>>>> 14bf65808c01528e1449c8356f81b4b5f8f1154f
            }
        });
    </script>
</body>
</html>