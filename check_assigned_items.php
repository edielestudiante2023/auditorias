<?php
// DEBUG: Verificar qué ítems están asignados a la auditoría #9

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "cycloid_auditorias";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Ítems Asignados a Auditoría #9</h2>";
echo "<p>Estos son los ítems que YA ESTÁN en la base de datos para esta auditoría</p>";

$sql = "SELECT
    ai.id_auditoria_item,
    ai.id_auditoria,
    ai.id_item,
    ib.codigo_item,
    ib.titulo,
    ib.orden,
    ai.created_at
FROM auditoria_items ai
LEFT JOIN items_banco ib ON ib.id_item = ai.id_item
WHERE ai.id_auditoria = 9
ORDER BY ai.id_item";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr>";
    echo "<th>ID Auditoria Item</th>";
    echo "<th>ID Item</th>";
    echo "<th>Código</th>";
    echo "<th>Título</th>";
    echo "<th>Orden</th>";
    echo "<th>Fecha Creación</th>";
    echo "</tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id_auditoria_item'] . "</td>";
        echo "<td style='background-color: yellow; font-weight: bold;'>" . $row['id_item'] . "</td>";
        echo "<td>" . htmlspecialchars($row['codigo_item']) . "</td>";
        echo "<td>" . htmlspecialchars($row['titulo']) . "</td>";
        echo "<td>" . $row['orden'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<br><p style='background-color: #ffffcc; padding: 10px; border: 2px solid orange;'>";
    echo "<strong>CONCLUSIÓN:</strong> Los ítems con id_item mostrados arriba (columna amarilla) ";
    echo "aparecerán PRE-SELECCIONADOS cuando cargues el formulario de selección de ítems.<br>";
    echo "Si ves que items 1 y 2 están aquí, es porque YA fueron guardados anteriormente.";
    echo "</p>";
} else {
    echo "<p style='color: green;'>No hay ítems asignados a esta auditoría.</p>";
}

$conn->close();
?>
