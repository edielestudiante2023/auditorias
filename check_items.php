<?php
// Script temporal para verificar calificaciones
$mysqli = new mysqli("localhost", "root", "", "cycloid_auditorias");

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$idAuditoria = 8;

echo "<h2>Auditoría #$idAuditoria - Estado de calificaciones</h2>";

echo "<h3>Ítems globales (auditoria_items):</h3>";
$result = $mysqli->query("
    SELECT ai.id_auditoria_item, ai.id_item, ib.titulo, ai.calificacion_consultor, ai.comentario_consultor
    FROM auditoria_items ai
    JOIN items_banco ib ON ib.id_item = ai.id_item
    WHERE ai.id_auditoria = $idAuditoria
    AND ib.alcance = 'global'
    ORDER BY ib.orden
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Título</th><th>Calificación</th><th>Comentario</th></tr>";
while ($row = $result->fetch_assoc()) {
    $calificacion = $row['calificacion_consultor'] ?: 'sin_revision';
    $comentario = $row['comentario_consultor'] ?: '(vacío)';
    echo "<tr>";
    echo "<td>{$row['id_auditoria_item']}</td>";
    echo "<td>{$row['titulo']}</td>";
    echo "<td style='background:" . ($calificacion === 'sin_revision' ? '#ffcccc' : '#ccffcc') . "'>$calificacion</td>";
    echo "<td>$comentario</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Ítems por cliente (auditoria_item_cliente):</h3>";
$result = $mysqli->query("
    SELECT aic.id_auditoria_item_cliente, ai.id_auditoria_item, ib.titulo, c.razon_social, aic.calificacion_ajustada
    FROM auditoria_item_cliente aic
    JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
    JOIN items_banco ib ON ib.id_item = ai.id_item
    JOIN clientes c ON c.id_cliente = aic.id_cliente
    WHERE ai.id_auditoria = $idAuditoria
    ORDER BY ib.orden, c.razon_social
");

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Título</th><th>Cliente</th><th>Calificación</th></tr>";
while ($row = $result->fetch_assoc()) {
    $calificacion = $row['calificacion_ajustada'] ?: 'sin_revision';
    echo "<tr>";
    echo "<td>{$row['id_auditoria_item_cliente']}</td>";
    echo "<td>{$row['titulo']}</td>";
    echo "<td>{$row['razon_social']}</td>";
    echo "<td style='background:" . ($calificacion === 'sin_revision' ? '#ffcccc' : '#ccffcc') . "'>$calificacion</td>";
    echo "</tr>";
}
echo "</table>";

$mysqli->close();
?>
