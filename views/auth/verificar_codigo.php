<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Yo Voto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Open Sans', sans-serif; min-height: 100vh;
            background: linear-gradient(160deg, #0a1628 0%, #0d2251 40%, #1a3a7a 70%, #0d2251 100%);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px; position: relative;
        }
        body::before {
            content: ''; position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 36px 36px;
        }
        .top-logo { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 22px; color: #fff; display: flex; align-items: center; gap: 8px; margin-bottom: 32px; position: relative; z-index: 1; }
        .top-logo span { color: #FF6B00; }
        .card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; width: 100%; max-width: 420px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.4); position: relative; z-index: 1; }
        .card-head { background: linear-gradient(135deg, #0d2251, #1a3a7a); border-bottom: 2px solid #FF6B00; padding: 28px; text-align: center; }
        .card-head-icon { width: 60px; height: 60px; border-radius: 16px; background: #FF6B00; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; }
        .card-head-icon i { font-size: 26px; color: #fff; }
        .card-head h2 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 20px; color: #fff; margin: 0; }
        .card-head p { font-size: 13px; color: rgba(255,255,255,0.45); margin: 6px 0 0; }
        .card-body { padding: 30px 28px; }
        .info-box { background: rgba(255,107,0,0.08); border: 1px solid rgba(255,107,0,0.2); border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: center; }
        .info-box i { color: #FF6B00; font-size: 28px; display: block; margin-bottom: 8px; }
        .info-box p { color: rgba(255,255,255,0.6); font-size: 13px; line-height: 1.6; }
        .info-box strong { color: #FF8C38; }
        .alert-danger { background: rgba(231,76,60,0.12); color: #ff6b6b; padding: 12px 14px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; border-left: 4px solid #E74C3C; display: flex; align-items: center; gap: 8px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.65); margin-bottom: 8px; }
        .codigo-inputs { display: flex; gap: 8px; justify-content: center; }
        .codigo-inputs input {
            width: 48px; height: 56px; text-align: center; font-size: 22px; font-weight: 800;
            border: 1.5px solid rgba(255,255,255,0.1); border-radius: 10px;
            background: rgba(255,255,255,0.06); color: #fff; font-family: monospace;
            transition: .2s; caret-color: #FF6B00;
        }
        .codigo-inputs input:focus { outline: none; border-color: #FF6B00; background: rgba(255,255,255,0.1); box-shadow: 0 0 0 3px rgba(255,107,0,0.15); }
        .btn-submit { width: 100%; padding: 14px; background: #FF6B00; color: #fff; border: none; border-radius: 11px; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 16px; cursor: pointer; transition: .25s; box-shadow: 0 6px 24px rgba(255,107,0,0.35); display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-submit:hover { background: #FF8C38; transform: translateY(-2px); }
        .timer { text-align: center; margin-top: 16px; font-size: 13px; color: rgba(255,255,255,0.35); }
        .timer span { color: #FF8C38; font-weight: 700; }
        .back-link { text-align: center; margin-top: 20px; font-size: 13px; color: rgba(255,255,255,0.3); position: relative; z-index: 1; }
        .back-link a { color: #FF6B00; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>

    <div class="top-logo"><i class="fas fa-envelope" style="color:#FF6B00;"></i> Yo <span>Voto</span></div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-envelope-open-text"></i></div>
            <h2>Verificación de Correo</h2>
            <p>Ingresa el código que te enviamos</p>
        </div>
        <div class="card-body">

            <div class="info-box">
                <i class="fas fa-paper-plane"></i>
                <p>Enviamos un código de 6 dígitos a<br><strong><?= htmlspecialchars($_SESSION['admin_email_destino'] ?? '') ?></strong><br>Revisa tu bandeja de entrada.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="codigoForm">
                <input type="hidden" name="codigo_verificacion" id="codigo-hidden">
                <div class="form-group">
                    <label style="text-align:center;display:block;">Código de verificación</label>
                    <div class="codigo-inputs">
                        <input type="text" maxlength="1" class="digit" id="d1" inputmode="numeric">
                        <input type="text" maxlength="1" class="digit" id="d2" inputmode="numeric">
                        <input type="text" maxlength="1" class="digit" id="d3" inputmode="numeric">
                        <input type="text" maxlength="1" class="digit" id="d4" inputmode="numeric">
                        <input type="text" maxlength="1" class="digit" id="d5" inputmode="numeric">
                        <input type="text" maxlength="1" class="digit" id="d6" inputmode="numeric">
                    </div>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-check-circle"></i> Verificar Código</button>
            </form>

            <div class="timer">El código expira en <span id="countdown">5:00</span></div>
        </div>
    </div>

    <div class="back-link"><a href="/yo_voto/login"><i class="fas fa-arrow-left"></i> Volver al login</a></div>

    <script>
        // Auto-avanzar entre inputs
        const digits = document.querySelectorAll('.digit');
        digits.forEach((input, i) => {
            input.addEventListener('input', () => {
                input.value = input.value.replace(/[^0-9]/g, '');
                if (input.value && i < digits.length - 1) digits[i + 1].focus();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && i > 0) digits[i - 1].focus();
            });
        });

        // Juntar los 6 dígitos antes de enviar
        document.getElementById('codigoForm').addEventListener('submit', function(e) {
            const codigo = Array.from(digits).map(d => d.value).join('');
            if (codigo.length !== 6) { e.preventDefault(); alert('Ingresa los 6 dígitos del código.'); return; }
            document.getElementById('codigo-hidden').value = codigo;
        });

        // Countdown 5 minutos
        let segundos = 300;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            segundos--;
            const m = Math.floor(segundos / 60);
            const s = segundos % 60;
            countdownEl.textContent = m + ':' + String(s).padStart(2, '0');
            if (segundos <= 0) {
                clearInterval(timer);
                countdownEl.textContent = '0:00';
                countdownEl.style.color = '#E74C3C';
            }
        }, 1000);

        // Focus en el primer input
        document.getElementById('d1').focus();
    </script>
</body>
</html>