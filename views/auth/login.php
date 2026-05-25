<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Yo Voto</title>
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
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
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
            }
        });
    </script>
</body>
</html>