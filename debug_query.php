<?php
$mysqli = new mysqli("localhost", "root", "", "cycloid_auditorias");
if ($mysqli->connect_error) die("Error: " . $mysqli->connect_error);

echo "<h2>Debug - Auditoría #8</h2>";

// Ver todos los ítems de la auditoría 8
echo "<h3>Tabla auditoria_items:</h3>";
$result = $mysqli->query("
    SELECT 
        ai.id_auditoria_item,
        ai.id_item,
        ib.codigo_item,
        ib.titulo,
        ib.alcance,
        ai.calificacion_consultor,
        LENGTH(ai.comentario_consultor) as comentario_len,
        ai.created_at,
        ai.updated_at
    FROM auditoria_items ai
    JOIN items_banco ib ON ib.id_item = ai.id_item
    WHERE ai.id_auditoria = 8
    ORDER BY ib.orden
");

echo "<table border='1' cellpadding='5' style='font-size: 11px;'>";
echo "<tr><th>ID Item</th><th>Código</th><th>Título</th><th>Alcance</th><th>Calificación</th><th>Comentario (len)</th><th>Created</th><th>Updated</th></tr>";
while ($row = $result->fetch_assoc()) {
    $bg = ($row['calificacion_consultor'] === 'sin_revision') ? '#ffcccc' : '#ccffcc';
    echo "<tr style='background: $bg'>";
    echo "<td>{$row['id_auditoria_item']}</td>";
    echo "<td>{$row['codigo_item']}</td>";
    echo "<td>" . substr($row['titulo'], 0, 40) . "...</td>";
    echo "<td>{$row['alcance']}</td>";
    echo "<td><strong>{$row['calificacion_consultor']}</strong></td>";
    echo "<td>{$row['comentario_len']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "<td>" . ($row['updated_at'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Instrucciones:</h3>";
echo "<ol>";
echo "<li>Califica UN SOLO ítem en la interfaz</li>";
echo "<li>Refresca esta página</li>";
echo "<li>Verifica que solo ese ítem cambió su 'updated_at' y 'calificacion_consultor'</li>";
echo "</ol>";

$mysqli->close();
?>
