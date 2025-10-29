<?php
// Script para resetear contraseña de usuario

// Conectar a la base de datos
$conn = new mysqli('127.0.0.1', 'root', '', 'cycloid_auditorias');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>Resetear Contraseña de Usuario</h2>";

// Datos del usuario
$id_user = 6; // ID del usuario "Natalia Jimenez"
$nueva_password = 'consultor123'; // Nueva contraseña

// Hashear la contraseña
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

// Actualizar en la base de datos
$sql = "UPDATE users SET password_hash = ? WHERE id_users = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $password_hash, $id_user);

if ($stmt->execute()) {
    echo "<div style='background: #d4edda; padding: 20px; border: 1px solid #c3e6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #155724;'>✅ Contraseña actualizada exitosamente</h3>";
    echo "<p><strong>Email:</strong> sistemasdegestionpropiedadhori@gmail.com</p>";
    echo "<p><strong>Nueva contraseña:</strong> <code style='background: #fff; padding: 5px; font-size: 18px;'>$nueva_password</code></p>";
    echo "<hr>";
    echo "<p>Ahora puedes iniciar sesión en:</p>";
    echo "<a href='http://localhost/auditorias/public/index.php/login' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a Login</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 1px solid #f5c6cb; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Error al actualizar la contraseña</h3>";
    echo "<p>" . $stmt->error . "</p>";
    echo "</div>";
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<p><small>Nota: Este script es temporal y debe eliminarse en producción por seguridad.</small></p>";
?>
