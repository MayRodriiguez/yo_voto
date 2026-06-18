<?php
// diagnostic_admin.php 
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h1>🔍 Diagnóstico de Login de Administrador</h1>";

// 1. Verifica si existe el admin
$email = 'admin@yovoto.com';
$sql = "SELECT id, nombres, email, password, rol, habilitado_voto, activo FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2> 1. Buscando administrador con email: {$email}</h2>";

if ($admin = $result->fetch_assoc()) {
    echo "<p style='color: green;'> Administrador ENCONTRADO</p>";
    echo "<pre>";
    print_r($admin);
    echo "</pre>";
    
    // 2. Probamos contraseña admin123
    $testPassword = 'admin123';
    echo "<h2> 2. Probando contraseña: '{$testPassword}'</h2>";
    
    if (password_verify($testPassword, $admin['password'])) {
        echo "<p style='color: green;'> La contraseña ES CORRECTA</p>";
    } else {
        echo "<p style='color: red;'> La contraseña NO es correcta</p>";
        
        // Generar nuevo hash
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "<p>Nuevo hash generado: <code>{$newHash}</code></p>";
        
        // Actualizar
        $updateSql = "UPDATE usuarios SET password = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newHash, $admin['id']);
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green;'> Contraseña ACTUALIZADA correctamente</p>";
            echo "<p>Ahora prueba con: <strong>{$testPassword}</strong></p>";
        } else {
            echo "<p style='color: red;'> Error al actualizar: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'> No existe administrador con email: {$email}</p>";
    
    // Crear administrador
    $password = 'admin123';
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $insertSql = "INSERT INTO usuarios (nombres, apellidos, carnet, fecha_nacimiento, email, password, rol, habilitado_voto, activo) 
                  VALUES ('Admin', 'Sistema', 'ADMIN001', '1990-01-01', ?, ?, 'admin', 1, 1)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ss", $email, $hashedPassword);
    
    if ($insertStmt->execute()) {
        echo "<p style='color: green;'> Administrador CREADO correctamente</p>";
        echo "<p>Email: <strong>{$email}</strong></p>";
        echo "<p>Contraseña: <strong>{$password}</strong></p>";
    } else {
        echo "<p style='color: red;'> Error al crear: " . $conn->error . "</p>";
    }
}

// 3. Mostrar todos los usuarios
echo "<h2> 3. Todos los usuarios en el sistema:</h2>";
$allUsers = $conn->query("SELECT id, nombres, apellidos, email, carnet, rol FROM usuarios");
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background: #003399; color: white;'><th>ID</th><th>Nombre</th><th>Email</th><th>Carnet</th><th>Rol</th></tr>";
while ($row = $allUsers->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nombres']} {$row['apellidos']}</td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>{$row['carnet']}</td>";
    echo "<td><strong>{$row['rol']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<a href='/yo_voto/login' style='background: #003399; color: white; padding: 10px 20px; text-decoration: none; border-radius: 10px;'>Ir al Login de Admin</a>";
echo "<a href='/yo_voto/' style='background: #f5c518; color: #003399; padding: 10px 20px; text-decoration: none; border-radius: 10px; margin-left: 10px;'>Volver al Inicio</a>";
?>