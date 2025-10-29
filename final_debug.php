<?php
$mysqli = new mysqli("localhost", "root", "", "cycloid_auditorias");
if ($mysqli->connect_error) die("Error: " . $mysqli->connect_error);

echo "<h2>Auditoría #8 - Análisis Completo</h2>";

echo "<h3>1. Ítems de la auditoría:</h3>";
$result = $mysqli->query("
    SELECT 
        ai.id_auditoria_item,
        ib.codigo_item,
        ib.titulo,
        ib.alcance,
        ai.calificacion_consultor as calif_global
    FROM auditoria_items ai
    JOIN items_banco ib ON ib.id_item = ai.id_item
    WHERE ai.id_auditoria = 8
    ORDER BY ib.orden
");

$items = [];
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Código</th><th>Título</th><th>Alcance</th><th>Calif Global</th></tr>";
while ($row = $result->fetch_assoc()) {
    $items[$row['id_auditoria_item']] = $row;
    $bg = ($row['alcance'] === 'global') ? '#e3f2fd' : '#fff3e0';
    echo "<tr style='background: $bg'>";
    echo "<td>{$row['id_auditoria_item']}</td>";
    echo "<td>{$row['codigo_item']}</td>";
    echo "<td>" . substr($row['titulo'], 0, 30) . "...</td>";
    echo "<td><strong>{$row['alcance']}</strong></td>";
    echo "<td>{$row['calif_global']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>2. Calificaciones por cliente (auditoria_item_cliente):</h3>";
$result = $mysqli->query("
    SELECT 
        aic.id_auditoria_item,
        aic.id_cliente,
        c.razon_social,
        aic.calificacion_ajustada,
        aic.created_at,
        aic.updated_at
    FROM auditoria_item_cliente aic
    JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
    JOIN clientes c ON c.id_cliente = aic.id_cliente
    WHERE ai.id_auditoria = 8
    ORDER BY aic.id_auditoria_item, c.razon_social
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID Item</th><th>Código</th><th>Cliente</th><th>Calificación</th><th>Created</th><th>Updated</th></tr>";
while ($row = $result->fetch_assoc()) {
    $item = $items[$row['id_auditoria_item']];
    $bg = ($row['calificacion_ajustada'] === 'sin_revision') ? '#ffcccc' : '#ccffcc';
    echo "<tr style='background: $bg'>";
    echo "<td>{$row['id_auditoria_item']}</td>";
    echo "<td>{$item['codigo_item']}</td>";
    echo "<td>{$row['razon_social']}</td>";
    echo "<td><strong>{$row['calificacion_ajustada']}</strong></td>";
    echo "<td>" . ($row['created_at'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['updated_at'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>3. Clientes asignados:</h3>";
$result = $mysqli->query("
    SELECT c.id_cliente, c.razon_social
    FROM auditoria_clientes ac
    JOIN clientes c ON c.id_cliente = ac.id_cliente
    WHERE ac.id_auditoria = 8
");
echo "<ul>";
while ($row = $result->fetch_assoc()) {
    echo "<li>ID {$row['id_cliente']}: {$row['razon_social']}</li>";
}
echo "</ul>";

$mysqli->close();
?>
