<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
$db = new Database();
$conn = $db->getConnection();

$result = $conn->query("SELECT * FROM candidatos WHERE estado = 'activo' ORDER BY id_candidato ASC");
$candidatos = [];
while ($row = $result->fetch_assoc()) { $candidatos[] = $row; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidatos - Yo Voto Bolivia 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Open Sans',sans-serif; min-height:100vh; background:#0a1628; color:#e2e8f0; }
        .navbar { position:fixed; top:0; left:0; right:0; z-index:100; background:rgba(10,22,50,0.97); backdrop-filter:blur(10px); height:60px; display:flex; align-items:center; padding:0 40px; justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.08); }
        .navbar-logo { font-family:'Montserrat',sans-serif; font-weight:900; font-size:20px; color:#fff; text-decoration:none; display:flex; align-items:center; gap:8px; }
        .navbar-logo span { color:#FF6B00; }
        .btn-back { background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); padding:8px 18px; border-radius:8px; font-size:13px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:7px; transition:.2s; }
        .btn-back:hover { background:#FF6B00; color:#fff; border-color:#FF6B00; }
        .main { min-height:100vh; background:linear-gradient(160deg,#0a1628 0%,#0d2251 40%,#1a3a7a 70%,#0d2251 100%); padding:90px 24px 60px; }
        .main::before { content:''; position:fixed; inset:0; pointer-events:none; background-image:radial-gradient(rgba(255,255,255,0.03) 1px,transparent 1px); background-size:36px 36px; }
        .container { max-width:1100px; margin:0 auto; position:relative; z-index:1; }
        .hero { text-align:center; margin-bottom:40px; }
        .hero-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(255,107,0,0.12); border:1px solid rgba(255,107,0,0.35); color:#FF8C38; padding:6px 18px; border-radius:50px; font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; margin-bottom:16px; }
        .hero h1 { font-family:'Montserrat',sans-serif; font-weight:900; font-size:38px; color:#fff; margin-bottom:8px; }
        .hero h1 span { color:#FF6B00; }
        .hero p { color:rgba(255,255,255,0.45); font-size:15px; }

        /* Grid candidatos */
        .candidatos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:22px; margin-bottom:40px; }
        .candidato-card { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:22px; overflow:hidden; cursor:pointer; transition:.25s; }
        .candidato-card:hover { border-color:rgba(255,107,0,0.5); transform:translateY(-5px); box-shadow:0 16px 40px rgba(0,0,0,0.4); background:rgba(255,255,255,0.07); }
        .candidato-img-wrap { position:relative; background:linear-gradient(135deg,#0d2251,#1a3a7a); padding:24px; text-align:center; }
        .candidato-img { width:130px; height:130px; border-radius:50%; object-fit:cover; border:4px solid #FF6B00; box-shadow:0 8px 24px rgba(255,107,0,0.3); }
        .candidato-body { padding:20px; }
        .candidato-nombre { font-family:'Montserrat',sans-serif; font-weight:800; font-size:16px; color:#fff; margin-bottom:4px; }
        .candidato-partido { color:#FF8C38; font-size:13px; margin-bottom:4px; font-weight:600; }
        .candidato-cargo { color:rgba(255,255,255,0.35); font-size:12px; margin-bottom:16px; }
        .btn-ver { width:100%; background:#FF6B00; color:#fff; border:none; padding:11px; border-radius:10px; font-family:'Montserrat',sans-serif; font-weight:800; font-size:14px; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
        .btn-ver:hover { background:#FF8C38; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.88); z-index:500; overflow-y:auto; padding:30px 20px; }
        .modal-box { background:#0d1e42; border:1px solid rgba(255,255,255,0.1); border-radius:24px; max-width:780px; margin:0 auto; overflow:hidden; animation:fadeUp .3s ease; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(30px);} to{opacity:1;transform:translateY(0);} }
        .modal-head { background:linear-gradient(135deg,#0d2251,#1a3a7a); border-bottom:2px solid #FF6B00; padding:20px 28px; display:flex; align-items:center; justify-content:space-between; }
        .modal-head h2 { font-family:'Montserrat',sans-serif; font-weight:800; color:#fff; font-size:18px; margin:0; }
        .btn-close { background:rgba(255,255,255,0.1); border:none; color:#fff; width:34px; height:34px; border-radius:8px; font-size:18px; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; }
        .btn-close:hover { background:#FF6B00; }
        .modal-body { padding:28px; }

        /* Modal candidato info */
        .modal-cand-header { display:flex; gap:22px; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; }
        .modal-cand-img { width:110px; height:110px; border-radius:50%; object-fit:cover; border:4px solid #FF6B00; flex-shrink:0; }
        .modal-cand-info { flex:1; }
        .modal-cand-nombre { font-family:'Montserrat',sans-serif; font-weight:900; font-size:22px; color:#fff; margin-bottom:4px; }
        .modal-cand-partido { color:#FF8C38; font-size:14px; font-weight:600; margin-bottom:4px; }
        .modal-cand-cargo { color:rgba(255,255,255,0.4); font-size:13px; margin-bottom:12px; }
        .modal-cand-bio { color:rgba(255,255,255,0.55); font-size:14px; line-height:1.7; }

        /* Secciones modal */
        .modal-sec { font-size:11px; font-weight:700; color:#FF6B00; text-transform:uppercase; letter-spacing:2px; margin:24px 0 14px; padding-bottom:8px; border-bottom:1px solid rgba(255,107,0,0.25); display:flex; align-items:center; gap:8px; }

        /* Propuestas */
        .propuesta-card { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07); border-left:4px solid #FF6B00; border-radius:12px; padding:16px; margin-bottom:12px; }
        .propuesta-cat { font-size:10px; font-weight:800; color:#FF8C38; text-transform:uppercase; letter-spacing:1px; margin-bottom:5px; }
        .propuesta-titulo { font-weight:700; color:#fff; font-size:14px; margin-bottom:5px; }
        .propuesta-desc { color:rgba(255,255,255,0.45); font-size:13px; line-height:1.6; }

        /* Equipo */
        .equipo-nivel { margin-bottom:16px; }
        .nivel-badge { display:inline-flex; align-items:center; gap:6px; background:rgba(255,107,0,0.1); border:1px solid rgba(255,107,0,0.25); color:#FF8C38; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; margin-bottom:10px; }
        .integrantes-wrap { display:flex; flex-wrap:wrap; gap:10px; }
        .integrante { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:10px 14px; }
        .integrante-nombre { font-weight:700; color:#fff; font-size:13px; }
        .integrante-cargo { color:rgba(255,255,255,0.35); font-size:11px; }

        .loading { text-align:center; padding:40px; color:rgba(255,255,255,0.3); }
        .loading i { font-size:28px; color:#FF6B00; display:block; margin-bottom:10px; }

        footer { text-align:center; padding:28px; color:rgba(255,255,255,0.2); font-size:13px; border-top:1px solid rgba(255,255,255,0.06); }
        footer span { color:#FF6B00; font-weight:700; }

        @media(max-width:640px) { .navbar{padding:0 16px;} .hero h1{font-size:28px;} .candidatos-grid{grid-template-columns:1fr 1fr;} .modal-body{padding:18px;} }
        @media(max-width:400px) { .candidatos-grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>

<header class="navbar">
    <a href="/yo_voto/" class="navbar-logo"><i class="fas fa-envelope" style="color:#FF6B00;"></i> Yo <span>Voto</span></a>
    <a href="/yo_voto/" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
</header>

<main class="main">
    <div class="container">
        <div class="hero">
            <div class="hero-badge"><i class="fas fa-users"></i> Bolivia 2026</div>
            <h1>Nuestros <span>Candidatos</span></h1>
            <p>Conoce a los postulantes para las Elecciones Generales Bolivia 2026</p>
        </div>

        <div class="candidatos-grid">
            <?php foreach ($candidatos as $c): ?>
            <div class="candidato-card" onclick="verCandidato(<?= $c['id_candidato'] ?>)">
                <div class="candidato-img-wrap">
                    <img src="/yo_voto/<?= htmlspecialchars($c['foto_url'] ?? 'uploads/img/sin_foto.jpg') ?>"
                         class="candidato-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                </div>
                <div class="candidato-body">
                    <div class="candidato-nombre"><?= htmlspecialchars($c['nombre']) ?></div>
                    <div class="candidato-partido"><i class="fas fa-flag" style="font-size:10px;margin-right:4px;"></i><?= htmlspecialchars($c['partido']) ?></div>
                    <div class="candidato-cargo">Candidato a <?= htmlspecialchars($c['cargo']) ?></div>
                    <button class="btn-ver"><i class="fas fa-eye"></i> Ver Propuestas y Equipo</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;">
            <a href="/yo_voto/" style="background:#FF6B00;color:#fff;padding:14px 32px;border-radius:10px;font-family:'Montserrat',sans-serif;font-weight:800;font-size:15px;text-decoration:none;display:inline-flex;align-items:center;gap:9px;box-shadow:0 6px 24px rgba(255,107,0,0.35);">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>
        </div>
    </div>
</main>

<!-- MODAL -->
<div class="modal-overlay" id="modalCandidato">
    <div class="modal-box">
        <div class="modal-head">
            <h2 id="modal-titulo">Candidato</h2>
            <button class="btn-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        </div>
    </div>
</div>

<footer><p>🗳️ <span>Yo Voto</span> — Sistema Electoral Bolivia 2026 · Democracia y Transparencia</p></footer>

<script>
function esc(t) { if(!t) return ''; const d=document.createElement('div'); d.textContent=t; return d.innerHTML; }

async function verCandidato(id) {
    document.getElementById('modalCandidato').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('modal-body').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';

    try {
        const [cRes, eRes, pRes] = await Promise.all([
            fetch(`/yo_voto/api/candidato/${id}`),
            fetch(`/yo_voto/api/equipo/${id}`),
            fetch(`/yo_voto/api/propuestas/${id}`)
        ]);
        const c = await cRes.json();
        const equipo = await eRes.json();
        const propuestas = await pRes.json();

        document.getElementById('modal-titulo').textContent = c.nombre;

        // Equipo HTML
        let equipoHtml = '';
        if (!equipo || Object.keys(equipo).length === 0) {
            equipoHtml = '<p style="color:rgba(255,255,255,0.3);">Sin equipo registrado.</p>';
        } else {
            Object.entries(equipo).forEach(([nivel, ints]) => {
                equipoHtml += `<div class="equipo-nivel">
                    <div class="nivel-badge"><i class="fas fa-layer-group"></i> Nivel ${esc(nivel)}</div>
                    <div class="integrantes-wrap">
                        ${ints.map(i => `<div class="integrante"><div class="integrante-nombre">${esc(i.nombre)}</div><div class="integrante-cargo">${esc(i.cargo)}</div></div>`).join('')}
                    </div>
                </div>`;
            });
        }

        // Propuestas HTML
        let propHtml = '';
        if (!propuestas || !propuestas.length) {
            propHtml = '<p style="color:rgba(255,255,255,0.3);">Sin propuestas registradas.</p>';
        } else {
            propHtml = propuestas.map(p => `
                <div class="propuesta-card">
                    <div class="propuesta-cat"><i class="fas fa-tag"></i> ${esc(p.categoria)}</div>
                    <div class="propuesta-titulo">${esc(p.titulo)}</div>
                    <div class="propuesta-desc">${esc(p.descripcion)}</div>
                </div>`).join('');
        }

        document.getElementById('modal-body').innerHTML = `
            <div class="modal-cand-header">
                <img src="/yo_voto/${esc(c.foto_url)}" class="modal-cand-img" onerror="this.src='/yo_voto/uploads/img/sin_foto.jpg'">
                <div class="modal-cand-info">
                    <div class="modal-cand-nombre">${esc(c.nombre)}</div>
                    <div class="modal-cand-partido"><i class="fas fa-flag" style="font-size:11px;margin-right:4px;"></i>${esc(c.partido)}</div>
                    <div class="modal-cand-cargo">Candidato a ${esc(c.cargo)}</div>
                    <div class="modal-cand-bio">${esc(c.biografia || 'Sin biografía disponible.')}</div>
                </div>
            </div>
            <div class="modal-sec"><i class="fas fa-list-check"></i> Propuestas de Gobierno</div>
            ${propHtml}
            <div class="modal-sec"><i class="fas fa-users"></i> Equipo de Campaña</div>
            ${equipoHtml}
        `;
    } catch(e) {
        document.getElementById('modal-body').innerHTML = '<p style="color:#ff6b6b;text-align:center;">Error al cargar los datos.</p>';
    }
}

function cerrarModal() {
    document.getElementById('modalCandidato').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if(e.key==='Escape') cerrarModal(); });
window.onclick = e => { if(e.target===document.getElementById('modalCandidato')) cerrarModal(); };
</script>
</body>
</html>
