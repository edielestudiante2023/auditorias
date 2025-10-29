<?php

// Script temporal para verificar/actualizar contraseña
// ELIMINAR DESPUÉS DE USAR

require_once '../vendor/autoload.php';

// Inicializar CodeIgniter
$pathsConfig = new Config\Paths();
$bootstrap = \CodeIgniter\Boot::bootWeb($pathsConfig);

$app = new \CodeIgniter\CodeIgniter($bootstrap);
$app->initialize();

$db = \Config\Database::connect();

echo "<h2>Test de Contraseña</h2>";

$email = 'edisoncuervo1981@gmail.com';
$passwordPlainText = 'ytPGphHgr*o9'; // La contraseña del email

echo "<h3>1. Datos del usuario en DB:</h3>";
$user = $db->table('users')->where('email', $email)->get()->getRowArray();

if ($user) {
    echo "ID: " . $user['id_users'] . "<br>";
    echo "Nombre: " . $user['nombre'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Rol: " . $user['id_roles'] . "<br>";
    echo "Estado: " . $user['estado'] . "<br>";
    echo "Hash actual: " . substr($user['password_hash'], 0, 50) . "...<br>";

    echo "<h3>2. Verificar contraseña:</h3>";
    if (password_verify($passwordPlainText, $user['password_hash'])) {
        echo "✅ La contraseña '$passwordPlainText' COINCIDE con el hash guardado<br>";
    } else {
        echo "❌ La contraseña '$passwordPlainText' NO coincide con el hash guardado<br>";

        echo "<h3>3. Generando nuevo hash:</h3>";
        $newHash = password_hash($passwordPlainText, PASSWORD_DEFAULT);
        echo "Nuevo hash: " . substr($newHash, 0, 50) . "...<br>";

        // Actualizar en la base de datos
        $db->table('users')
            ->where('email', $email)
            ->update(['password_hash' => $newHash]);

        echo "<h3>4. Hash actualizado en la base de datos</h3>";
        echo "✅ Ahora intenta iniciar sesión con:<br>";
        echo "Email: <strong>$email</strong><br>";
        echo "Password: <strong>$passwordPlainText</strong><br>";

        // Verificar nuevamente
        $userUpdated = $db->table('users')->where('email', $email)->get()->getRowArray();
        if (password_verify($passwordPlainText, $userUpdated['password_hash'])) {
            echo "<br>✅ Verificación: El nuevo hash funciona correctamente<br>";
        } else {
            echo "<br>❌ Error: El nuevo hash tampoco funciona<br>";
        }
    }
} else {
    echo "❌ Usuario no encontrado con email: $email<br>";
}

echo "<hr>";
echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo después de usarlo por seguridad</p>";
