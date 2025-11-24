<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'cycloid_auditorias';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "=== ESTADO ACTUAL DE PRIMARY KEYS ===\n\n";

$sql = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, COLUMN_KEY, EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = 'cycloid_auditorias'
          AND COLUMN_KEY = 'PRI'
        ORDER BY TABLE_NAME";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "Tabla: " . $row["TABLE_NAME"] . "\n";
        echo "  Columna: " . $row["COLUMN_NAME"] . "\n";
        echo "  Tipo: " . $row["COLUMN_TYPE"] . "\n";
        echo "  Extra: " . $row["EXTRA"] . "\n\n";
    }
} else {
    echo "No se encontraron primary keys\n";
}

echo "\n=== SENTENCIAS ALTER TABLE A EJECUTAR ===\n\n";

$sql = "SELECT
    CONCAT(
        'ALTER TABLE `', c.TABLE_NAME, '` MODIFY `', c.COLUMN_NAME, '` ',
        c.COLUMN_TYPE, ' NOT NULL AUTO_INCREMENT;'
    ) AS sql_statement
FROM information_schema.COLUMNS c
INNER JOIN (
    SELECT TABLE_NAME, COUNT(*) as pk_count
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = 'cycloid_auditorias'
      AND CONSTRAINT_NAME = 'PRIMARY'
    GROUP BY TABLE_NAME
    HAVING pk_count = 1
) pk ON c.TABLE_NAME = pk.TABLE_NAME
WHERE c.TABLE_SCHEMA = 'cycloid_auditorias'
  AND c.COLUMN_KEY = 'PRI'
  AND c.EXTRA NOT LIKE '%auto_increment%'
  AND c.DATA_TYPE IN ('tinyint', 'smallint', 'mediumint', 'int', 'bigint')
ORDER BY c.TABLE_NAME";

$result = $conn->query($sql);

$alterStatements = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo $row["sql_statement"] . "\n";
        $alterStatements[] = $row["sql_statement"];
    }
} else {
    echo "¡Todas las PKs ya tienen AUTO_INCREMENT configurado correctamente!\n";
}

$conn->close();

// Guardar las sentencias en un archivo
if (count($alterStatements) > 0) {
    file_put_contents('alter_statements.sql', implode("\n", $alterStatements));
    echo "\n\n=== Sentencias guardadas en: alter_statements.sql ===\n";
}
?>
