// face-recognition.js - Sistema de reconocimiento facial para Yo Voto
let labeledFaceDescriptors = [];
let modelsLoaded = false;
let currentUserFaceDescriptor = null;

// Cargar modelos de FaceAPI
async function loadFaceAPIModels() {
    const MODEL_URL = '/yo_voto/public/face-api/models';
    
    await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
    
    modelsLoaded = true;
    console.log('✅ Modelos de reconocimiento facial cargados');
}

// Capturar descriptor facial desde la cámara
async function captureFaceDescriptor(videoElement) {
    if (!modelsLoaded) {
        await loadFaceAPIModels();
    }
    
    const detections = await faceapi.detectSingleFace(videoElement)
        .withFaceLandmarks()
        .withFaceDescriptor();
    
    if (!detections) {
        throw new Error('No se detectó ningún rostro');
    }
    
    return detections.descriptor;
}

// Registrar usuario con reconocimiento facial
async function registerFace(carnet, videoElement) {
    try {
        const descriptor = await captureFaceDescriptor(videoElement);
        
        // Guardar descriptor en el servidor
        const response = await fetch('/yo_voto/api/face/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                carnet: carnet,
                descriptor: Array.from(descriptor)
            })
        });
        
        const result = await response.json();
        if (result.success) {
            showMessage('✅ Rostro registrado correctamente', 'success');
            return true;
        } else {
            showMessage(result.error || 'Error al registrar rostro', 'error');
            return false;
        }
    } catch (error) {
        showMessage(error.message, 'error');
        return false;
    }
}

// Verificar rostro para login
async function verifyFace(carnet, videoElement) {
    try {
        const currentDescriptor = await captureFaceDescriptor(videoElement);
        
        const response = await fetch('/yo_voto/api/face/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                carnet: carnet,
                descriptor: Array.from(currentDescriptor)
            })
        });
        
        const result = await response.json();
        return result;
    } catch (error) {
        return { success: false, error: error.message };
    }
}

// Inicializar cámara
async function initCamera(videoElementId) {
    const video = document.getElementById(videoElementId);
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        throw new Error('Tu navegador no soporta la cámara');
    }
    
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    video.srcObject = stream;
    
    return new Promise((resolve) => {
        video.onloadedmetadata = () => {
            video.play();
            resolve(video);
        };
    });
}

// Detener cámara
function stopCamera(videoElementId) {
    const video = document.getElementById(videoElementId);
    if (video && video.srcObject) {
        const tracks = video.srcObject.getTracks();
        tracks.forEach(track => track.stop());
        video.srcObject = null;
    }
}

function showMessage(msg, type) {
    const msgDiv = document.getElementById('face-message');
    if (msgDiv) {
        msgDiv.textContent = msg;
        msgDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
        msgDiv.style.display = 'block';
        setTimeout(() => {
            msgDiv.style.display = 'none';
        }, 3000);
    }
}