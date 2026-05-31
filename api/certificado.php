<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/yo_voto/');
    session_name('YOVOTO_SESSION');
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['rol'] != 'usuario') {
    header("Location: /yo_voto/");
    exit();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = new Database();
$conn = $db->getConnection();
$user = $_SESSION['user'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user['ya_voto']) {
    header("Location: /yo_voto/mi-perfil");
    exit();
}

$stmtVoto = $conn->prepare("SELECT fecha_voto FROM votos WHERE id_usuario = ? ORDER BY id_voto DESC LIMIT 1");
$stmtVoto->bind_param("i", $user['id']);
$stmtVoto->execute();
$votoData = $stmtVoto->get_result()->fetch_assoc();
$fechaVoto = $votoData['fecha_voto'] ?? date('Y-m-d H:i:s');

// A5 landscape = 210 x 148 mm
$pdf = new TCPDF('L', 'mm', 'A5', true, 'UTF-8', false);
$pdf->SetCreator('Yo Voto Bolivia 2026');
$pdf->SetTitle('Certificado de Sufragio');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$W = 210;
$H = 148;

// ── HEADER ──
$pdf->SetFillColor(26, 58, 122);
$pdf->Rect(0, 0, $W, 26, 'F');

// Yo Voto logo
$pdf->SetFillColor(255, 107, 0);
$pdf->RoundedRect(6, 6, 20, 14, 3, '1111', 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetXY(6, 10);
$pdf->Cell(20, 6, 'YoVoto', 0, 0, 'C');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 7);
$pdf->SetXY(28, 8);
$pdf->Cell(50, 4, 'Sistema Electoral Bolivia', 0, 1);
$pdf->SetXY(28, 13);
$pdf->Cell(50, 4, 'Elecciones 2026', 0, 1);

// Título derecho
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetXY(70, 5);
$pdf->Cell($W - 76, 7, 'CERTIFICADO DE SUFRAGIO', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 8);
$pdf->SetXY(70, 13);
$pdf->Cell($W - 76, 5, 'Elecciones Generales Bolivia 2026  |  ' . date('d/m/Y', strtotime($fechaVoto)), 0, 1, 'R');

// Línea naranja
$pdf->SetFillColor(255, 107, 0);
$pdf->Rect(0, 26, $W, 2, 'F');

// ── CUERPO ──
// Columna izquierda: FOTO (x=6, y=30, ancho=30)
$fotoPath = __DIR__ . '/../' . ($user['foto_url'] ?? 'uploads/img/sin_foto.jpg');
if (!file_exists($fotoPath)) $fotoPath = __DIR__ . '/../uploads/img/sin_foto.jpg';
if (file_exists($fotoPath)) {
    $pdf->Image($fotoPath, 6, 30, 30, 36);
}
$pdf->SetFont('helvetica', 'B', 7);
$pdf->SetTextColor(26, 58, 122);
$pdf->SetXY(6, 67);
$pdf->Cell(30, 4, 'C.I.: ' . $user['carnet'], 0, 1, 'C');

// Columna central: DATOS (x=40, ancho=130)
$datos = [
    ['Nombres y Apellidos:', $user['nombres'] . ' ' . $user['apellidos']],
    ['Fecha de Nacimiento:', $user['fecha_nacimiento'] ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : '—'],
    ['Celular:', $user['telefono'] ?? '—'],
    ['Departamento:', $user['departamento'] ?? '—'],
    ['Fecha de Voto:', date('d/m/Y', strtotime($fechaVoto))],
    ['Hora de Voto:', date('H:i:s', strtotime($fechaVoto))],
];

$y = 31;
foreach ($datos as $dato) {
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetXY(40, $y);
    $pdf->Cell(45, 6, $dato[0], 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(80, 6, $dato[1], 0, 1);
    $pdf->SetDrawColor(220, 220, 220);
    $pdf->Line(40, $y + 6, 168, $y + 6);
    $y += 9;
}

// Columna derecha: QR (x=170, ancho=34)
$qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=YOVOTO-' . $user['carnet'] . '-' . date('Y');
$pdf->Image($qrUrl, 172, 30, 32, 32, 'PNG');
$pdf->SetFont('helvetica', '', 6);
$pdf->SetTextColor(130, 130, 130);
$pdf->SetXY(170, 63);
$pdf->Cell(36, 4, 'Verificacion Digital', 0, 1, 'C');

// ── FOOTER ──
$footerY = $H - 20;
$pdf->SetFillColor(240, 240, 240);
$pdf->Rect(0, $footerY, $W, 20, 'F');
$pdf->SetDrawColor(26, 58, 122);
$pdf->Line(0, $footerY, $W, $footerY);

$pdf->SetFont('helvetica', '', 7);
$pdf->SetTextColor(100, 100, 100);
$pdf->SetXY(6, $footerY + 4);
$pdf->Cell(80, 4, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1);

$pdf->SetFont('helvetica', 'B', 8);
$pdf->SetTextColor(26, 58, 122);
$pdf->SetXY(6, $footerY + 10);
$pdf->Cell(80, 4, 'Sistema Electoral Bolivia 2026', 0, 1);

// Sello verde
$pdf->SetFillColor(39, 174, 96);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('helvetica', 'B', 8);
$pdf->RoundedRect($W - 52, $footerY + 3, 46, 12, 3, '1111', 'F');
$pdf->SetXY($W - 52, $footerY + 6);
$pdf->Cell(46, 6, 'SUFRAGIO VALIDO', 0, 1, 'C');

$pdf->Output('certificado_sufragio_' . $user['carnet'] . '.pdf', 'D');