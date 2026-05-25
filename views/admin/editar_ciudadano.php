<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Editar Ciudadano</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#0a1628;
    color:white;
    font-family:Arial;
}

.box{
    max-width:700px;
    margin:40px auto;
    background:rgba(255,255,255,0.05);
    padding:30px;
    border-radius:18px;
}

.form-control{
    background:rgba(255,255,255,0.08);
    border:1px solid rgba(255,255,255,0.1);
    color:white;
}

.form-control:focus{
    background:rgba(255,255,255,0.08);
    color:white;
    border-color:#ff6b00;
    box-shadow:none;
}

.btn-save{
    background:#ff6b00;
    color:white;
    border:none;
    padding:10px 18px;
    border-radius:10px;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="box">

<h2 class="mb-4">Editar Ciudadano</h2>

<form method="POST">

<div class="mb-3">
<label>Nombres</label>
<input type="text" name="nombres" class="form-control"
value="<?= htmlspecialchars($usuario['nombres']) ?>" required>
</div>

<div class="mb-3">
<label>Apellidos</label>
<input type="text" name="apellidos" class="form-control"
value="<?= htmlspecialchars($usuario['apellidos']) ?>" required>
</div>

<div class="mb-3">
<label>Carnet</label>
<input type="text" name="carnet" class="form-control"
value="<?= htmlspecialchars($usuario['carnet']) ?>" required>
</div>

<div class="mb-3">
<label>Dirección</label>
<input type="text" name="direccion" class="form-control"
value="<?= htmlspecialchars($usuario['direccion']) ?>">
</div>

<div class="mb-3">
<label>Teléfono</label>
<input type="text" name="telefono" class="form-control"
value="<?= htmlspecialchars($usuario['telefono']) ?>">
</div>

<div class="mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control"
value="<?= htmlspecialchars($usuario['email']) ?>">
</div>

<button type="submit" class="btn-save">
Guardar Cambios
</button>

</form>

</div>

</body>
</html>