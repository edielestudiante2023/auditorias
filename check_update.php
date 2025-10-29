<?php
$mysqli = new mysqli("localhost", "root", "", "cycloid_auditorias");
if ($mysqli->connect_error) die("Error: " . $mysqli->connect_error);

echo "<h2>Verificar si el UPDATE funcionó</h2>";

echo "<h3>Item #27:</h3>";
$result = $mysqli->query("SELECT * FROM auditoria_items WHERE id_auditoria_item = 27");
$row = $result->fetch_assoc();

echo "<pre>";
print_r($row);
echo "</pre>";

echo "<hr>";
echo "<p>Si 'calificacion_consultor' dice 'cumple', el UPDATE funcionó.</p>";
echo "<p>Si dice 'sin_revision', el UPDATE NO funcionó.</p>";

$mysqli->close();
?>
