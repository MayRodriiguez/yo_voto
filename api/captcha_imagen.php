<?php
// api/captcha_imagen.php - Versión ultra simple
// Generar código numérico de 5 dígitos
$codigo = rand(10000, 99999);
$_SESSION['captcha_codigo'] = $codigo;

// Crear imagen
$imagen = imagecreate(130, 50);
$fondo = imagecolorallocate($imagen, 232, 224, 255);
$texto = imagecolorallocate($imagen, 0, 51, 153);
$linea = imagecolorallocate($imagen, 245, 197, 24);

// Fondo
imagefilledrectangle($imagen, 0, 0, 130, 50, $fondo);

// Línea decorativa
imageline($imagen, 0, 25, 130, 25, $linea);

// Texto
imagestring($imagen, 5, 35, 17, $codigo, $texto);

header('Content-Type: image/png');
imagepng($imagen);
imagedestroy($imagen);
?>