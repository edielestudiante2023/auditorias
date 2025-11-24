<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'cycloid_auditorias';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener todas las tablas
$tables = $conn->query("SHOW TABLES");

echo "=== ANÁLISIS DE TODAS LAS TABLAS ===\n\n";

$tablasSinPK = [];
$tablasConPKSinAutoincrement = [];
$tablasOK = [];

while ($table = $tables->fetch_array()) {
    $tableName = $table[0];

    // Obtener CREATE TABLE
    $result = $conn->query("SHOW CREATE TABLE `$tableName`");
    $row = $result->fetch_assoc();
    $createTable = $row['Create Table'];

    echo "--- $tableName ---\n";

    // Verificar si tiene PRIMARY KEY
    if (strpos($createTable, 'PRIMARY KEY') === false) {
        echo "  ❌ NO TIENE PRIMARY KEY DEFINIDA\n";
        $tablasSinPK[] = $tableName;

        // Buscar campo que probablemente debería ser PK
        $desc = $conn->query("DESCRIBE `$tableName`");
        while ($field = $desc->fetch_assoc()) {
            if (preg_match('/^id_/', $field['Field']) || $field['Field'] === 'id') {
                echo "  → Campo candidato a PK: {$field['Field']} ({$field['Type']})\n";
            }
        }
    } else {
        // Tiene PK, verificar AUTO_INCREMENT
        if (strpos($createTable, 'AUTO_INCREMENT') === false) {
            echo "  ⚠️  Tiene PK pero SIN AUTO_INCREMENT\n";
            $tablasConPKSinAutoincrement[] = $tableName;
        } else {
            echo "  ✓ Tiene PK con AUTO_INCREMENT\n";
            $tablasOK[] = $tableName;
        }
    }
    echo "\n";
}

echo "\n=== RESUMEN ===\n";
echo "Tablas SIN PRIMARY KEY: " . count($tablasSinPK) . "\n";
if (count($tablasSinPK) > 0) {
    echo "  - " . implode("\n  - ", $tablasSinPK) . "\n";
}

echo "\nTablas con PK pero SIN AUTO_INCREMENT: " . count($tablasConPKSinAutoincrement) . "\n";
if (count($tablasConPKSinAutoincrement) > 0) {
    echo "  - " . implode("\n  - ", $tablasConPKSinAutoincrement) . "\n";
}

echo "\nTablas OK: " . count($tablasOK) . "\n";

$conn->close();
?>
