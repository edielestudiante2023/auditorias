<?php
// Script para verificar relación consultor-usuario

// Conectar a la base de datos
$conn = new mysqli('127.0.0.1', 'root', '', 'cycloid_auditorias');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

echo "<h2>Verificación Consultor-Usuario</h2>";

// Consultar el consultor con su usuario
$sql = "SELECT
    c.id_consultor,
    c.nombre_completo,
    c.id_users,
    u.nombre as nombre_usuario,
    u.email,
    u.id_roles,
    r.nombre as nombre_rol
FROM consultores c
LEFT JOIN users u ON u.id_users = c.id_users
LEFT JOIN roles r ON r.id_roles = u.id_roles
WHERE c.id_consultor = 3";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h3>Datos del Consultor:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    echo "<tr><td>ID Consultor</td><td>{$row['id_consultor']}</td></tr>";
    echo "<tr><td>Nombre Completo</td><td>{$row['nombre_completo']}</td></tr>";
    echo "<tr><td>ID Usuario</td><td>{$row['id_users']}</td></tr>";
    echo "<tr><td>Nombre Usuario</td><td>{$row['nombre_usuario']}</td></tr>";
    echo "<tr><td>Email (para login)</td><td><strong>{$row['email']}</strong></td></tr>";
    echo "<tr><td>ID Rol</td><td>{$row['id_roles']}</td></tr>";
    echo "<tr><td>Nombre Rol</td><td>{$row['nombre_rol']}</td></tr>";
    echo "</table>";

    if (empty($row['id_users'])) {
        echo "<div style='color: red; margin-top: 20px;'>";
        echo "<h3>⚠️ PROBLEMA: Este consultor NO tiene un usuario asignado</h3>";
        echo "<p>Necesitas editar el consultor y asignarle un usuario con rol 'Consultor'</p>";
        echo "</div>";
    } else {
        echo "<div style='color: green; margin-top: 20px;'>";
        echo "<h3>✅ El consultor tiene usuario asignado</h3>";
        echo "<p>Puede iniciar sesión con:</p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> {$row['email']}</li>";
        echo "<li><strong>Contraseña:</strong> La que se configuró al crear el usuario</li>";
        echo "</ul>";
        echo "</div>";
    }
} else {
    echo "<p style='color: red;'>No se encontró el consultor con ID 3</p>";
}

// Mostrar todos los usuarios con rol Consultor
echo "<hr>";
echo "<h3>Usuarios disponibles con rol Consultor (rol_id = 2):</h3>";

$sql2 = "SELECT
    u.id_users,
    u.nombre,
    u.email,
    u.estado,
    c.id_consultor,
    c.nombre_completo
FROM users u
LEFT JOIN consultores c ON c.id_users = u.id_users
WHERE u.id_roles = 2";

$result2 = $conn->query($sql2);

if ($result2->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID User</th><th>Nombre</th><th>Email</th><th>Estado</th><th>Tiene Consultor?</th></tr>";
    while ($row2 = $result2->fetch_assoc()) {
        $tieneConsultor = $row2['id_consultor'] ? "Sí (ID: {$row2['id_consultor']} - {$row2['nombre_completo']})" : "No";
        echo "<tr>";
        echo "<td>{$row2['id_users']}</td>";
        echo "<td>{$row2['nombre']}</td>";
        echo "<td>{$row2['email']}</td>";
        echo "<td>{$row2['estado']}</td>";
        echo "<td>{$tieneConsultor}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No hay usuarios con rol Consultor</p>";
}

$conn->close();
?>
