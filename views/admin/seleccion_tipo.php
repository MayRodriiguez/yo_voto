<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/** @var string $tipoActivo */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Tipo de Elección - Yo Voto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         * { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Open Sans', sans-serif;
    background: #0a1628;
    color: #e2e8f0;
    min-height: 100vh;
}

.sidebar {
    position: fixed;
    top: 0; left: 0;
    width: 255px;
    height: 100%;
    background: rgba(10,22,50,0.98);
    border-right: 1px solid rgba(255,255,255,0.08);
    overflow-y: auto;
    z-index: 100;
}

.sidebar-header {
    padding: 26px 22px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.sidebar-header h3 {
    font-size: 20px;
    font-weight: 900;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 10px;
}

.sidebar-header h3 i {
    color: #FF6B00;
    font-size: 18px;
}

.sidebar-header p {
    color: rgba(255,255,255,0.3);
    font-size: 11px;
    margin-top: 6px;
    margin-left: 28px;
    letter-spacing: 1px;
}

.sidebar-menu-item {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 11px 22px;
    color: rgba(255,255,255,0.45);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.sidebar-menu-item i {
    width: 17px;
    text-align: center;
    font-size: 14px;
}

.sidebar-menu-item:hover {
    color: #fff;
    background: rgba(255,255,255,0.06);
    border-left-color: rgba(255,107,0,0.4);
}

.sidebar-menu-item.active {
    color: #FF6B00;
    background: rgba(255,107,0,0.08);
    border-left-color: #FF6B00;
    font-weight: 700;
}

.main-content {
    margin-left: 255px;
    padding: 30px;
    min-height: 100vh;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 14px;
}

.page-title {
    font-size: 24px;
    font-weight: 900;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 11px;
}

.page-title i { color: #FF6B00; }

.user-info { display: flex; align-items: center; gap: 12px; }

.user-name {
    font-size: 14px;
    color: #FF8C38;
    font-weight: 600;
}

.btn-logout {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}

.btn-logout:hover {
    background: #FF6B00;
    color: #fff;
    border-color: #FF6B00;
}

.btn-back {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 9px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.2s;
}

.btn-back:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
}

.card-box, .form-container, .table-container,
.content-card, .recent-section, .quick-actions, .card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 26px;
    margin-bottom: 22px;
}

.card-box h3, .form-container h3, .form-container h4,
.content-card h3, .recent-section h3, .quick-actions h3, .card h3 {
    color: #fff;
    font-weight: 800;
    margin-bottom: 18px;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 9px;
}

.card-box h3 i, .form-container h4 i, .content-card h3 i,
.recent-section h3 i, .quick-actions h3 i, .card h3 i {
    color: #FF6B00;
}

.stats-grid, .cards-container, .stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 18px;
    margin-bottom: 26px;
}

.stats-card, .stat-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 24px;
    transition: transform 0.2s, border-color 0.2s;
}

.stats-card:hover, .stat-card:hover {
    transform: translateY(-3px);
    border-color: rgba(255,107,0,0.3);
}

.stat-icon, .stats-card i {
    font-size: 26px;
    color: #FF6B00;
    margin-bottom: 12px;
    display: block;
}

.stat-number, .stats-card h2 {
    font-size: 32px;
    font-weight: 900;
    color: #fff;
    margin-bottom: 4px;
}

.stat-label, .stats-card p {
    font-size: 13px;
    color: rgba(255,255,255,0.35);
}

table { width: 100%; border-collapse: collapse; }

thead { background: rgba(255,107,0,0.08); }

th {
    padding: 13px 16px;
    font-size: 12px;
    font-weight: 700;
    color: #FF8C38;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
}

td {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-size: 14px;
    color: rgba(255,255,255,0.75);
}

tbody tr:hover { background: rgba(255,255,255,0.03); }

.form-label {
    font-weight: 700;
    color: rgba(255,255,255,0.7);
    font-size: 13px;
    margin-bottom: 7px;
    display: block;
}

.form-control, .form-select {
    background: rgba(255,255,255,0.07);
    border: 1.5px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    padding: 11px 14px;
    color: #fff;
    font-size: 14px;
    width: 100%;
    transition: border-color 0.2s;
}

.form-control:focus, .form-select:focus {
    outline: none;
    border-color: #FF6B00;
    background: rgba(255,255,255,0.1);
}

.form-control::placeholder { color: rgba(255,255,255,0.25); }

.form-select option { background: #0d2251; color: #fff; }

.btn-guardar, .btn-add, .btn-primary, .btn-asignar, .btn-actualizar {
    background: #FF6B00;
    color: #fff;
    border: none;
    padding: 11px 22px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(255,107,0,0.3);
}

.btn-guardar:hover, .btn-add:hover, .btn-primary:hover,
.btn-asignar:hover, .btn-actualizar:hover {
    background: #FF8C38;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(255,107,0,0.4);
}

.btn-editar, .btn-propuestas, .btn-equipo {
    background: rgba(255,107,0,0.1);
    color: #FF8C38;
    border: 1px solid rgba(255,107,0,0.2);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-editar:hover, .btn-propuestas:hover, .btn-equipo:hover {
    background: rgba(255,107,0,0.2);
    color: #FF6B00;
}

.btn-eliminar {
    background: rgba(231,76,60,0.1);
    color: #ff6b6b;
    border: 1px solid rgba(231,76,60,0.2);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-eliminar:hover {
    background: rgba(231,76,60,0.2);
    color: #E74C3C;
}

.btn-toggle, .btn-warning {
    background: rgba(234,179,8,0.1);
    color: #facc15;
    border: 1px solid rgba(234,179,8,0.2);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-habilitar {
    background: rgba(39,174,96,0.1);
    color: #5cdb95;
    border: 1px solid rgba(39,174,96,0.2);
    padding: 7px 13px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-habilitar:hover {
    background: rgba(39,174,96,0.2);
    color: #27AE60;
}

.badge-si, .badge-confirmado, .badge-valid, .badge-activo {
    background: rgba(39,174,96,0.15);
    color: #5cdb95;
    border: 1px solid rgba(39,174,96,0.25);
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.badge-no, .badge-invalid {
    background: rgba(231,76,60,0.15);
    color: #ff6b6b;
    border: 1px solid rgba(231,76,60,0.2);
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.badge-pendiente, .badge-warning {
    background: rgba(234,179,8,0.1);
    color: #facc15;
    border: 1px solid rgba(234,179,8,0.2);
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.badge-face {
    background: rgba(25,118,210,0.15);
    color: #7eb3ff;
    border: 1px solid rgba(25,118,210,0.25);
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.badge-inactivo {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.4);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    display: inline-block;
}

.alert-success {
    background: rgba(39,174,96,0.12);
    border-left: 4px solid #27AE60;
    color: #5cdb95;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-danger {
    background: rgba(231,76,60,0.12);
    border-left: 4px solid #E74C3C;
    color: #ff6b6b;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-info {
    background: rgba(25,118,210,0.1);
    border-left: 4px solid #1976D2;
    color: #7eb3ff;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-warning {
    background: rgba(234,179,8,0.08);
    border-left: 4px solid #facc15;
    color: #facc15;
    border-radius: 10px;
    padding: 14px 18px;
    font-size: 14px;
}

.hash-text {
    font-family: monospace;
    font-size: 12px;
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.5);
    padding: 3px 8px;
    border-radius: 5px;
}

img { border-radius: 10px; object-fit: cover; }

.preview-img {
    width: 120px; height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #FF6B00;
    margin-top: 12px;
    display: block;
}

.candidato-img {
    width: 60px; height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #FF6B00;
    flex-shrink: 0;
}

.candidato-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 16px;
    display: flex;
    gap: 14px;
    align-items: center;
    transition: all 0.2s;
    margin-bottom: 12px;
}

.candidato-card:hover {
    border-color: #FF6B00;
    background: rgba(255,107,0,0.06);
}

.candidato-info { flex: 1; }

.candidato-nombre {
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    margin-bottom: 3px;
}

.candidato-partido {
    font-size: 13px;
    color: #FF8C38;
    margin-bottom: 2px;
}

.candidato-cargo {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 10px;
}

.btn-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.candidatos-grid { display: flex; flex-direction: column; }

.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.module-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 24px;
    text-decoration: none;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
    gap: 10px;
    cursor: pointer;
}

.module-card:hover {
    background: rgba(255,255,255,0.08);
    border-color: rgba(255,107,0,0.3);
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.3);
}

.module-header { display: flex; align-items: center; gap: 10px; }

.module-header i { font-size: 20px; color: #FF6B00; }

.module-header h3 {
    font-size: 15px;
    font-weight: 800;
    color: #fff;
    margin: 0;
}

.module-body p {
    font-size: 13px;
    color: rgba(255,255,255,0.4);
    line-height: 1.5;
}

.module-footer {
    font-size: 12px;
    color: #FF6B00;
    font-weight: 700;
}

.system-status {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 22px;
}

.system-status h3 {
    color: #fff;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 9px;
}

.system-status h3 i { color: #FF6B00; }

.status-items { display: flex; flex-direction: column; gap: 10px; }

.status-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: rgba(255,255,255,0.45);
}

.status-badge {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.status-badge.active { background: #5cdb95; box-shadow: 0 0 6px #27AE60; }

.status-badge.warning { background: #facc15; }

.equipo-container {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 26px;
}

.candidato-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 20px;
}

.candidato-header img {
    width: 80px; height: 80px;
    border-radius: 50%;
    border: 3px solid #FF6B00;
}

.candidato-header h3 { color: #fff; margin-top: 12px; font-size: 17px; font-weight: 800; }

.candidato-header p { color: #FF8C38; font-size: 13px; }

.nivel-equipo { margin-bottom: 18px; }

.nivel-titulo {
    font-size: 11px;
    font-weight: 800;
    color: #FF8C38;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.integrantes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 10px;
}

.integrante-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 10px;
    padding: 14px;
    text-align: center;
}

.integrante-nombre {
    font-size: 13px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 4px;
}

.integrante-cargo {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 10px;
}

.propuesta-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-left: 4px solid #FF6B00;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 12px;
}

.propuesta-titulo {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 5px;
}

.propuesta-categoria {
    font-size: 11px;
    font-weight: 800;
    color: #FF8C38;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: block;
}

.propuesta-card p {
    font-size: 13px;
    color: rgba(255,255,255,0.45);
    line-height: 1.6;
}

.candidato-info-box {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    padding: 14px;
    margin-bottom: 20px;
}

.candidato-info-box strong { color: #fff; font-size: 15px; }

.candidato-info-box div { font-size: 12px; color: rgba(255,255,255,0.35); }

.seleccion-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 22px;
}

.tipo-card {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 20px;
    padding: 32px 24px;
    text-align: center;
    position: relative;
    transition: all 0.2s;
}

.tipo-card.activo {
    border-color: rgba(255,107,0,0.4);
    background: rgba(255,107,0,0.06);
    box-shadow: 0 0 30px rgba(255,107,0,0.1);
}

.tipo-card.bloqueado { opacity: 0.35; pointer-events: none; }

.badge-activo-card {
    position: absolute;
    top: 14px; right: 14px;
    background: rgba(255,107,0,0.15);
    color: #FF8C38;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid rgba(255,107,0,0.25);
}

.tipo-icono { font-size: 40px; margin-bottom: 12px; }

.tipo-titulo {
    font-size: 18px;
    font-weight: 900;
    color: #fff;
    margin-bottom: 8px;
}

.tipo-desc {
    font-size: 13px;
    color: rgba(255,255,255,0.4);
    margin-bottom: 22px;
    line-height: 1.5;
}

.btn-tipo {
    padding: 11px 24px;
    border-radius: 10px;
    border: none;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-nacional {
    background: #FF6B00;
    color: #fff;
    box-shadow: 0 4px 16px rgba(255,107,0,0.35);
}

.btn-nacional:hover { background: #FF8C38; }

.btn-subnacional {
    background: rgba(255,107,0,0.1);
    color: #FF8C38;
    border: 1.5px solid rgba(255,107,0,0.25);
}

.btn-subnacional:hover { background: rgba(255,107,0,0.18); }

.tipo-activo-badge {
    font-size: 13px;
    font-weight: 700;
    padding: 8px 14px;
    border-radius: 8px;
}

.badge-ninguno {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.4);
}

.badge-nacional {
    background: rgba(255,107,0,0.1);
    color: #FF8C38;
}

.badge-subnacional {
    background: rgba(25,118,210,0.1);
    color: #7eb3ff;
}

.btn-cambiar {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.6);
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-cambiar:hover {
    color: #fff;
    background: rgba(255,255,255,0.08);
}

.tabla-registros { width: 100%; border-collapse: collapse; }

.tabla-registros th {
    background: rgba(255,107,0,0.08);
    color: #FF8C38;
    padding: 13px 14px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
}

.tabla-registros td {
    padding: 13px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
    font-size: 13px;
    color: rgba(255,255,255,0.6);
}

.tabla-registros tr:hover td { background: rgba(255,255,255,0.02); }

.table-responsive { overflow-x: auto; }

.action-btn {
    background: rgba(255,107,0,0.08);
    border: 1px solid rgba(255,107,0,0.15);
    color: #FF8C38;
    padding: 14px;
    border-radius: 14px;
    text-decoration: none;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    display: block;
    transition: all 0.2s;
}

.action-btn i { display: block; font-size: 26px; margin-bottom: 8px; color: #FF6B00; }

.action-btn:hover {
    background: rgba(255,107,0,0.15);
    color: #FF6B00;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(255,107,0,0.2);
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 14px;
}

footer {
    text-align: center;
    padding: 28px;
    color: rgba(255,255,255,0.2);
    font-size: 13px;
    border-top: 1px solid rgba(255,255,255,0.06);
}

footer span { color: #FF6B00; font-weight: 700; }

.mb-3 { margin-bottom: 16px; }
.mt-4 { margin-top: 22px; }
.text-muted { color: rgba(255,255,255,0.3); font-size: 12px; }
.row { display: flex; flex-wrap: wrap; gap: 16px; }
.col-md-4 { flex: 1; min-width: 180px; }
.col-md-2 { flex: 0 0 120px; }
.col-md-3 { flex: 1; min-width: 140px; }
.col-md-6 { flex: 1; min-width: 200px; }
.col-12 { width: 100%; }
.col-md-1 { flex: 0 0 60px; }
.w-100 { width: 100%; }
.text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-vote-yea"></i> Yo Voto</h3>
            <p>Sistema Electoral Bolivia</p>
        </div>
        <a href="/yo_voto/admin/dashboard" class="sidebar-menu-item"><i class="fas fa-tachometer-alt"></i> Panel Principal</a>
        <a href="/yo_voto/admin/ciudadanos" class="sidebar-menu-item"><i class="fas fa-user-check"></i> Gestionar Ciudadanos</a>
        <a href="/yo_voto/candidatos" class="sidebar-menu-item active"><i class="fas fa-users"></i> Candidatos</a>
        <a href="/yo_voto/admin/resultados" class="sidebar-menu-item"><i class="fas fa-chart-bar"></i> Resultados</a>
        <a href="/yo_voto/admin/blockchain" class="sidebar-menu-item"><i class="fas fa-vote-yea"></i> Registro de Votaciones</a>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="page-title"><i class="fas fa-flag"></i> Seleccionar Tipo de Elección</div>
            <span class="tipo-activo-badge 
                <?= $tipoActivo === 'ninguna' ? 'badge-ninguno' : ($tipoActivo === 'nacional' ? 'badge-nacional' : 'badge-subnacional') ?>">
                <?php if ($tipoActivo === 'ninguna'): ?>
                    <i class="fas fa-circle"></i> Sin selección activa
                <?php elseif ($tipoActivo === 'nacional'): ?>
                    <i class="fas fa-flag"></i> Nacionales activo
                <?php else: ?>
                    <i class="fas fa-map-marker-alt"></i> Subnacionales activo
                <?php endif; ?>
            </span>
        </div>

        <?php if (isset($_SESSION['mensaje'])): ?>
            <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:10px;margin-bottom:20px;width:100%;border-left:4px solid #28a745;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje'] ?>
                <?php unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>

        <div class="seleccion-container">
            <!-- Card Nacional -->
            <div class="tipo-card <?= $tipoActivo === 'subnacional' ? 'bloqueado' : '' ?> <?= $tipoActivo === 'nacional' ? 'activo' : '' ?>">
                <?php if ($tipoActivo === 'nacional'): ?>
                    <span class="badge-activo-card"><i class="fas fa-check"></i> Activo</span>
                <?php endif; ?>
                <div class="tipo-icono"></div>
                <div class="tipo-titulo">Nacionales</div>
                <div class="tipo-desc">Candidatos a Presidente y Vicepresidente de Bolivia</div>
                <button class="btn-tipo btn-nacional" 
                    <?= $tipoActivo === 'subnacional' ? 'disabled' : '' ?>
                    onclick="seleccionarTipo('nacional')">
                    <i class="fas fa-flag"></i> 
                    <?= $tipoActivo === 'nacional' ? 'Ver Candidatos' : 'Seleccionar' ?>
                </button>
            </div>

            <!-- Card Subnacional -->
            <div class="tipo-card <?= $tipoActivo === 'nacional' ? 'bloqueado' : '' ?> <?= $tipoActivo === 'subnacional' ? 'activo' : '' ?>">
                <?php if ($tipoActivo === 'subnacional'): ?>
                    <span class="badge-activo-card"><i class="fas fa-check"></i> Activo</span>
                <?php endif; ?>
                <div class="tipo-icono"></div>
                <div class="tipo-titulo">Subnacionales</div>
                <div class="tipo-desc">Candidatos a Gobernador, Alcalde y cargos regionales</div>
                <button class="btn-tipo btn-subnacional"
                    <?= $tipoActivo === 'nacional' ? 'disabled' : '' ?>
                    onclick="seleccionarTipo('subnacional')">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= $tipoActivo === 'subnacional' ? 'Ver Candidatos' : 'Seleccionar' ?>
                </button>
            </div>
        </div>

        <!-- Botón cambiar solo aparece si hay uno activo -->
        <?php if ($tipoActivo !== 'ninguna'): ?>
            <button class="btn-cambiar" onclick="cambiarTipo()">
                <i class="fas fa-exchange-alt"></i> Cambiar tipo de elección
            </button>
        <?php endif; ?>
    </div>

    <footer>Yo Voto - Sistema Electoral Bolivia 2026</footer>

    <script>
        function seleccionarTipo(tipo) {
            const nombres = { nacional: 'Nacionales', subnacional: 'Subnacionales' };
            const tipoActivo = '<?= $tipoActivo ?>';

            if (tipoActivo === tipo) {
                // Ya está activo, solo redirigir
                window.location.href = `/yo_voto/candidatos/${tipo}`;
                return;
            }

            if (confirm(`¿Estás seguro que deseas activar las elecciones ${nombres[tipo]}?\n\nEl otro tipo quedará bloqueado hasta que cambies la selección.`)) {
                window.location.href = `/yo_voto/candidatos/activar/${tipo}`;
            }
        }

        function cambiarTipo() {
            if (confirm('¿Estás seguro que deseas cambiar el tipo de elección?\n\nEsto desbloqueará ambas opciones y tendrás que seleccionar nuevamente.')) {
                window.location.href = '/yo_voto/candidatos/resetear';
            }
        }
    </script>
</body>
</html>