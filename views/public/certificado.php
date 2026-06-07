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

$stmtVoto = $conn->prepare("SELECT c.nombre as candidato, c.partido, v.fecha_voto FROM votos v JOIN candidatos c ON v.id_candidato = c.id_candidato WHERE v.id_usuario = ? ORDER BY v.id_voto DESC LIMIT 1");
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

// ── HEADER AZUL ──
$pdf->SetFillColor(26, 58, 122);
$pdf->Rect(15, 15, 180, 45, 'F');

// Logo Yo Voto
$pdf->SetFillColor(255, 255, 255);
$pdf->RoundedRect(18, 19, 35, 10, 2, '1111', 'F');
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetTextColor(26, 58, 122);
$pdf->SetXY(18, 20);
$pdf->Cell(35, 8, 'Yo Voto', 0, 0, 'C');

// Subtitulo logo
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(200, 210, 230);
$pdf->SetXY(18, 31);
$pdf->Cell(60, 5, 'Sistema Electoral Bolivia', 0, 0, 'L');

// Titulo derecha
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetXY(80, 20);
$pdf->Cell(113, 9, 'CERTIFICADO DE SUFRAGIO', 0, 0, 'R');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(200, 210, 230);
$pdf->SetXY(80, 31);
$pdf->Cell(113, 6, 'Elecciones Generales Bolivia 2026', 0, 0, 'R');
$pdf->SetXY(80, 38);
$pdf->Cell(113, 6, date('d') . ' de ' . ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n')-1] . ' de ' . date('Y'), 0, 0, 'R');

// ── CUERPO ──
$pdf->SetFillColor(255, 255, 255);
$pdf->SetDrawColor(220, 220, 220);
$pdf->SetLineWidth(0.3);
$pdf->Rect(15, 60, 180, 90, 'D');
$pdf->Line(58, 60, 58, 150);
$pdf->Line(152, 60, 152, 150);

// ── FOTO ──
$fotoPath = null;
$fotoPath = 'C:/xampp/htdocs/yo_voto/' . $user['foto_url'];
if (!file_exists($fotoPath)) {
    $fotoPath = null;
}

if ($fotoPath) {
    $pdf->Image($fotoPath, 20, 64, 32, 40, '', '', '', true, 150, '', false, false, 1);
} else {
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Rect(20, 64, 32, 40, 'DF');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(170, 170, 170);
    $pdf->SetXY(20, 81);
    $pdf->Cell(32, 5, 'Sin foto', 0, 0, 'C');
}

// CI label
$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(20, 107);
$pdf->Cell(32, 5, 'C.I.', 0, 0, 'C');

// CI numero
$pdf->SetFont('helvetica', 'B', 13);
$pdf->SetTextColor(26, 58, 122);
$pdf->SetXY(20, 113);
$pdf->Cell(32, 7, $user['carnet'], 0, 0, 'C');

// ── DATOS ──
$datos = [
    ['Nombre:', $user['nombres'] . ' ' . $user['apellidos']],
    ['Fec. Nac.:', $user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : '---'],
    ['Celular:', $user['telefono'] ?? '---'],
    ['Correo:', $user['email'] ?? '---'],
    ['Fecha Voto:', $voto ? date('d/m/Y', strtotime($voto['fecha_voto'])) : date('d/m/Y')],
    ['Hora Voto:', $voto ? date('H:i:s', strtotime($voto['fecha_voto'])) : '---'],
];

$y = 60;
foreach ($datos as $i => $dato) {
    if ($i % 2 == 0) {
        $pdf->SetFillColor(248, 249, 250);
        $pdf->Rect(58, $y, 94, 15, 'F');
    }
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(61, $y + 4);
    $pdf->Cell(30, 6, $dato[0], 0, 0, 'L');

    if (in_array($dato[0], ['Fecha Voto:', 'Hora Voto:'])) {
        $pdf->SetTextColor(26, 58, 122);
        $pdf->SetFont('helvetica', 'B', 11);
    } else {
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetFont('helvetica', '', 11);
    }
    $pdf->SetXY(93, $y + 4);
    $pdf->Cell(57, 6, $dato[1], 0, 0, 'L');

    $pdf->SetDrawColor(230, 230, 230);
    $pdf->Line(58, $y + 15, 152, $y + 15);
    $y += 15;
}

// ── QR ──
$qrData = "CERTIFICADO DE SUFRAGIO\n" .
          "Yo Voto Bolivia 2026\n" .
          "-------------------\n" .
          "CI: " . $user['carnet'] . "\n" .
          "Nombre: " . $user['nombres'] . " " . $user['apellidos'] . "\n" .
          "Fecha Voto: " . ($voto ? date('d/m/Y', strtotime($voto['fecha_voto'])) : date('d/m/Y')) . "\n" .
          "Hora Voto: " . ($voto ? date('H:i:s', strtotime($voto['fecha_voto'])) : '---') . "\n" .
          "-------------------\n" .
          "SUFRAGIO VALIDO";
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($qrData);
$pdf->Image($qrUrl, 155, 64, 35, 35, 'PNG', '', '', true);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetXY(152, 101);
$pdf->Cell(43, 5, 'Escanea para verificar', 0, 0, 'C');

// ── FOOTER ──
$pdf->SetFillColor(245, 247, 250);
$pdf->SetDrawColor(26, 58, 122);
$pdf->SetLineWidth(0.5);
$pdf->Rect(15, 150, 180, 14, 'DF');

$pdf->SetFont('helvetica', '', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(17, 154);
$pdf->Cell(55, 6, 'Generado: ' . date('d/m/Y H:i:s'), 0, 0, 'L');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(39, 174, 96);
$pdf->SetXY(75, 154);
$pdf->Cell(50, 6, 'SUFRAGIO VALIDO', 0, 0, 'C');

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(26, 58, 122);
$pdf->SetXY(130, 154);
$pdf->Cell(63, 6, 'Sistema Electoral Bolivia 2026', 0, 0, 'R');

$pdf->Output('certificado_sufragio_' . $user['carnet'] . '.pdf', 'D');
exit();
?>