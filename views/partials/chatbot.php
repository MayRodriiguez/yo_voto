<?php
$chatUserId = isset($_SESSION['user']['id']) ? 'user_' . $_SESSION['user']['id'] : 'guest_' . uniqid();
?>

<style>
    #chatbot-widget {
        position: fixed; bottom: 20px; right: 20px; z-index: 9999;
        font-family: 'Open Sans', sans-serif;
    }
    #chatbot-toggle {
        background: #f5c518; color: #003399; border: none; border-radius: 50%;
        width: 60px; height: 60px; font-size: 24px; cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: transform 0.3s;
    }
    #chatbot-toggle:hover { transform: scale(1.1); }
    
    #chatbot-window {
        display: none; width: 360px; height: 500px; background: white;
        border-radius: 15px; box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        flex-direction: column; overflow: hidden;
        position: absolute; bottom: 80px; right: 0;
    }
    #chatbot-header {
        background: #003399; color: white; padding: 15px; font-weight: bold;
        display: flex; justify-content: space-between; align-items: center;
    }
    #chatbot-close { background: none; border: none; color: white; cursor: pointer; font-size: 18px; }
    
    #chatbot-messages {
        flex: 1; padding: 15px; overflow-y: auto; background: #f4f0fb;
        display: flex; flex-direction: column; gap: 10px; scroll-behavior: smooth;
    }
    .msg-bubble { padding: 10px 14px; border-radius: 15px; max-width: 85%; font-size: 13px; line-height: 1.5; word-wrap: break-word; }
    .msg-user { background: #003399; color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
    .msg-bot { background: #ffffff; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; border: 1px solid #ede9fe; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    
    /* Panel de opciones predeterminadas */
    #chatbot-options-area {
        padding: 12px 10px; background: white; border-top: 1px solid #ddd;
        display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
    }
    .chat-btn-option {
        background: #f8f5ff; border: 1px solid #7c3aed; color: #7c3aed;
        padding: 8px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all 0.2s ease;
    }
    .chat-btn-option:hover {
        background: #7c3aed; color: white; transform: translateY(-2px);
    }
    .chat-btn-option:disabled {
        opacity: 0.5; cursor: not-allowed; transform: none;
    }
</style>

<div id="chatbot-widget">
    <button id="chatbot-toggle"><i class="fas fa-headset"></i></button>
    <div id="chatbot-window">
        <div id="chatbot-header">
            <span>🤖 Soporte Yo Voto</span>
            <button id="chatbot-close"><i class="fas fa-times"></i></button>
        </div>
        <div id="chatbot-messages"></div>
        
        <div id="chatbot-options-area">
            <button class="chat-btn-option" data-opcion="como_votar">🗳️ ¿Cómo votar?</button>
            <button class="chat-btn-option" data-opcion="candidatos">👥 Ver Candidatos</button>
            <button class="chat-btn-option" data-opcion="blockchain">🔒 Seguridad Blockchain</button>
            <button class="chat-btn-option" data-opcion="habilitacion">⏳ Mi cuenta no está habilitada</button>
        </div>
    </div>
</div>

<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-firestore.js"></script>

<script>
    // 1. INICIALIZAR FIREBASE (⚠️ Pon tus credenciales reales aquí)
    const firebaseConfig = {
        apiKey: "AIzaSyC_0G2wLZF_m0bYRpuBXVsMNbbwr_F1rPw",
        authDomain: "yo-voto-chat.firebaseapp.com",
        projectId: "yo-voto-chat",
        storageBucket: "yo-voto-chat.firebasestorage.app",
        messagingSenderId: "840603390342",
        appId: "1:840603390342:web:b2ca302b5e78f4995edc07"
    };
    if (!firebase.apps.length) { firebase.initializeApp(firebaseConfig); }
    const db = firebase.firestore();

    // 2. CONFIGURACIÓN DEL DOM
    const chatUserId = "<?php echo $chatUserId; ?>";
    const windowEl = document.getElementById('chatbot-window');
    const toggleBtn = document.getElementById('chatbot-toggle');
    const closeBtn = document.getElementById('chatbot-close');
    const messagesEl = document.getElementById('chatbot-messages');
    const optionButtons = document.querySelectorAll('.chat-btn-option');

    const chatRef = db.collection('chats').doc(chatUserId).collection('mensajes');
    let isFirstTime = true;

    // 3. UI LÓGICA (Abrir/Cerrar)
    toggleBtn.addEventListener('click', () => {
        windowEl.style.display = windowEl.style.display === 'flex' ? 'none' : 'flex';
        // Enviar saludo si es la primera vez que abre
        if (windowEl.style.display === 'flex' && messagesEl.children.length === 0) {
            triggerBotResponse('saludo', '👋 Solicitando conexión...');
        }
    });
    closeBtn.addEventListener('click', () => windowEl.style.display = 'none');

    // 4. CARGAR HISTORIAL EN TIEMPO REAL
    chatRef.orderBy('timestamp', 'asc').onSnapshot(snapshot => {
        messagesEl.innerHTML = ''; 
        snapshot.forEach(doc => {
            const msg = doc.data();
            appendMessageUI(msg.texto, msg.emisor);
        });
        scrollToBottom();
    });

    // 5. MANEJAR CLIC EN LAS OPCIONES
    optionButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            const opcionId = this.getAttribute('data-opcion');
            const textoBoton = this.innerText;

            // Deshabilitar botones temporalmente
            optionButtons.forEach(b => b.disabled = true);

            // Guardar pregunta del usuario en Firebase
            await chatRef.add({
                texto: textoBoton,
                emisor: 'user',
                timestamp: firebase.firestore.FieldValue.serverTimestamp()
            });

            // Llamar al backend
            await triggerBotResponse(opcionId);

            // Rehabilitar botones
            optionButtons.forEach(b => b.disabled = false);
        });
    });

    // 6. COMUNICACIÓN CON PHP (El Cerebro)
    async function triggerBotResponse(opcionId, textoOpcional = null) {
        try {
            const response = await fetch('/yo_voto/api/chatbot', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ opcion: opcionId })
            });
            const data = await response.json();

            // Guardar respuesta del bot en Firebase
            await chatRef.add({
                texto: data.respuesta,
                emisor: 'bot',
                timestamp: firebase.firestore.FieldValue.serverTimestamp()
            });
        } catch (error) {
            console.error('Error de red:', error);
        }
    }

    // Funciones Auxiliares
    function appendMessageUI(text, emisor) {
        const div = document.createElement('div');
        div.className = 'msg-bubble ' + (emisor === 'user' ? 'msg-user' : 'msg-bot');
        div.innerHTML = text; // Usamos innerHTML para soportar <br> y <strong>
        messagesEl.appendChild(div);
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
</script>