<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
    header("Location: /yo_voto/");
    exit();
}

require_once 'config/database.php';
require_once 'vendor/autoload.php';

$db   = new Database();
$conn = $db->getConnection();

$user = $_SESSION['user'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user['ya_voto']) {
    header("Location: /yo_voto/votar");
    exit();
}

$stmtVoto = $conn->prepare("SELECT c.nombre as candidato, c.partido, v.fecha_voto, v.id_voto FROM votos v JOIN candidatos c ON v.id_candidato = c.id_candidato WHERE v.id_usuario = ? ORDER BY v.id_voto DESC LIMIT 1");
$stmtVoto->bind_param("i", $user['id']);
$stmtVoto->execute();
$voto = $stmtVoto->get_result()->fetch_assoc();

$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetCreator('Yo Voto Bolivia 2026');
$pdf->SetAuthor('Yo Voto');
$pdf->SetTitle('Certificado de Sufragio');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$fotoPath = getcwd() . '/' . ($user['foto_url'] ?? 'uploads/img/sin_foto.jpg');
if (!file_exists($fotoPath) || empty($user['foto_url'])) {
    $fotoPath = null;
}

$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode('localhost/yo_voto/mi-perfil?ci=' . $user['carnet']);

$html = '
<table style="width:100%;background-color:#1a3a7a;padding:14px 18px;border-radius:6px 6px 0 0;">
    <tr>
        <td style="color:white;width:40%;">
            <div style="background-color:white;color:#1a3a7a;font-weight:bold;padding:4px 10px;font-size:14px;border-radius:4px;display:inline-block;">Yo Voto</div>
            <div style="font-size:9px;color:rgba(255,255,255,0.75);margin-top:5px;">Sistema Electoral Bolivia</div>
        </td>
        <td style="text-align:right;color:white;">
            <div style="font-size:18px;font-weight:bold;letter-spacing:2px;">CERTIFICADO DE SUFRAGIO</div>
            <div style="font-size:10px;color:rgba(255,255,255,0.8);margin-top:3px;">Elecciones Generales Bolivia 2026</div>
            <div style="font-size:10px;color:rgba(255,255,255,0.8);">' . date('d \d\e F \d\e Y') . '</div>
        </td>
    </tr>
</table>

<br>

<table style="width:100%;border:1px solid #ddd;border-radius:0 0 6px 6px;">
    <tr>
        <td style="width:22%;vertical-align:top;text-align:center;padding:16px 10px;border-right:1px solid #eee;">';

if ($fotoPath) {
    $html .= '<img src="' . $fotoPath . '" width="90" height="110" style="border:2px solid #1a3a7a;border-radius:4px;"><br>';
} else {
    $html .= '<div style="width:90px;height:110px;background:#f0f0f0;border:2px solid #1a3a7a;border-radius:4px;display:inline-block;line-height:110px;text-align:center;font-size:11px;color:#aaa;">Sin foto</div><br>';
}

$html .= '
            <div style="font-size:9px;color:#555;font-weight:bold;margin-top:6px;">CARNET DE IDENTIDAD</div>
            <div style="font-size:14px;color:#1a3a7a;font-weight:bold;margin-top:2px;">' . htmlspecialchars($user['carnet']) . '</div>
        </td>
        <td style="vertical-align:top;padding:16px 18px;">
            <table style="width:100%;border-collapse:collapse;">
                <tr style="background:#f8f9fa;">
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;width:38%;border-bottom:1px solid #eee;">NOMBRE COMPLETO</td>
                    <td style="font-size:11px;padding:7px 10px;border-bottom:1px solid #eee;font-weight:600;">' . htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) . '</td>
                </tr>
                <tr>
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;border-bottom:1px solid #eee;">FECHA NACIMIENTO</td>
                    <td style="font-size:11px;padding:7px 10px;border-bottom:1px solid #eee;">' . ($user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : '---') . '</td>
                </tr>
                <tr style="background:#f8f9fa;">
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;border-bottom:1px solid #eee;">CELULAR</td>
                    <td style="font-size:11px;padding:7px 10px;border-bottom:1px solid #eee;">' . htmlspecialchars($user['telefono'] ?? '---') . '</td>
                </tr>
                <tr>
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;border-bottom:1px solid #eee;">CORREO</td>
                    <td style="font-size:11px;padding:7px 10px;border-bottom:1px solid #eee;">' . htmlspecialchars($user['email'] ?? '---') . '</td>
                </tr>
                <tr style="background:#f8f9fa;">
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;border-bottom:1px solid #eee;">FECHA DE VOTO</td>
                    <td style="font-size:11px;padding:7px 10px;border-bottom:1px solid #eee;font-weight:600;color:#1a3a7a;">' . ($voto ? date('d/m/Y', strtotime($voto['fecha_voto'])) : date('d/m/Y')) . '</td>
                </tr>
                <tr>
                    <td style="color:#555;font-weight:bold;font-size:10px;padding:7px 10px;">HORA DE VOTO</td>
                    <td style="font-size:11px;padding:7px 10px;font-weight:600;color:#1a3a7a;">' . ($voto ? date('H:i:s', strtotime($voto['fecha_voto'])) : '---') . '</td>
                </tr>
            </table>
        </td>
        <td style="width:18%;vertical-align:bottom;text-align:center;padding:16px 10px;border-left:1px solid #eee;">
            <img src="' . $qrUrl . '" width="90" height="90" style="border:1px solid #ddd;"><br>
            <div style="font-size:8px;color:#999;margin-top:4px;">Escanea para verificar</div>
        </td>
    </tr>
</table>

<br>

<table style="width:100%;background-color:#f5f7fa;border:1px solid #ddd;border-radius:4px;padding:10px 15px;">
    <tr>
        <td style="font-size:9px;color:#777;">
            Documento generado el ' . date('d/m/Y') . ' a las ' . date('H:i:s') . '
        </td>
        <td style="text-align:center;">
            <div style="font-size:12px;color:#27AE60;font-weight:bold;">SUFRAGIO VALIDO</div>
        </td>
        <td style="text-align:right;font-size:9px;color:#1a3a7a;font-weight:bold;">
            Sistema Electoral Bolivia 2026
        </td>
    </tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('certificado_sufragio_' . $user['carnet'] . '.pdf', 'D');
exit();
?>