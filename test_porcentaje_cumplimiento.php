<?php
// SCRIPT DE PRUEBA: Verificar cálculo de porcentaje de cumplimiento

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "cycloid_auditorias";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Prueba de Cálculo de Porcentaje de Cumplimiento</h2>";
echo "<p>Lógica esperada:</p>";
echo "<ul>";
echo "<li><strong>Cumple</strong> = 1.0 punto (100%)</li>";
echo "<li><strong>Parcial</strong> = 0.5 puntos (50%)</li>";
echo "<li><strong>No Cumple</strong> = 0.0 puntos (0%)</li>";
echo "<li><strong>No Aplica</strong> = 1.0 punto (100%)</li>";
echo "</ul>";

// Seleccionar una auditoría para probar (puedes cambiar el ID)
$idAuditoria = 9;

echo "<hr>";
echo "<h3>Auditoría #$idAuditoria</h3>";

// Obtener ítems globales calificados
$queryGlobales = "
    SELECT
        ai.id_auditoria_item,
        ib.codigo_item,
        ib.titulo,
        ai.calificacion_consultor as calificacion,
        'global' as tipo
    FROM auditoria_items ai
    JOIN items_banco ib ON ib.id_item = ai.id_item
    WHERE ai.id_auditoria = $idAuditoria
      AND ib.alcance IN ('global', 'mixto')
      AND ai.calificacion_consultor IS NOT NULL
      AND ai.calificacion_consultor != 'sin_revision'
";

$resultGlobales = $conn->query($queryGlobales);
$itemsGlobales = [];
if ($resultGlobales) {
    while ($row = $resultGlobales->fetch_assoc()) {
        $itemsGlobales[] = $row;
    }
}

// Obtener ítems por cliente calificados
$queryPorCliente = "
    SELECT
        aic.id_auditoria_item_cliente,
        ib.codigo_item,
        ib.titulo,
        cl.razon_social as cliente,
        aic.calificacion_ajustada as calificacion,
        'por_cliente' as tipo
    FROM auditoria_item_cliente aic
    JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
    JOIN items_banco ib ON ib.id_item = ai.id_item
    JOIN clientes cl ON cl.id_cliente = aic.id_cliente
    WHERE ai.id_auditoria = $idAuditoria
      AND ib.alcance = 'por_cliente'
      AND aic.calificacion_ajustada IS NOT NULL
      AND aic.calificacion_ajustada != 'sin_revision'
";

$resultPorCliente = $conn->query($queryPorCliente);
$itemsPorCliente = [];
if ($resultPorCliente) {
    while ($row = $resultPorCliente->fetch_assoc()) {
        $itemsPorCliente[] = $row;
    }
}

// Combinar todos los ítems
$todosLosItems = array_merge($itemsGlobales, $itemsPorCliente);

if (empty($todosLosItems)) {
    echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "<strong>⚠️ No hay ítems calificados en esta auditoría.</strong>";
    echo "</div>";
} else {
    echo "<h4>Ítems Calificados:</h4>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #e9ecef;'>";
    echo "<th>Tipo</th>";
    echo "<th>Código</th>";
    echo "<th>Título</th>";
    echo "<th>Cliente</th>";
    echo "<th>Calificación</th>";
    echo "<th>Puntos</th>";
    echo "</tr>";

    $totalPuntos = 0;
    $totalItems = count($todosLosItems);

    foreach ($todosLosItems as $item) {
        $calificacion = $item['calificacion'];

        // Calcular puntos según la calificación
        $puntos = 0;
        switch ($calificacion) {
            case 'cumple':
                $puntos = 1.0;
                $bgColor = '#d4edda'; // Verde
                break;
            case 'parcial':
                $puntos = 0.5;
                $bgColor = '#fff3cd'; // Amarillo
                break;
            case 'no_cumple':
                $puntos = 0.0;
                $bgColor = '#f8d7da'; // Rojo
                break;
            case 'no_aplica':
                $puntos = 1.0;
                $bgColor = '#d1ecf1'; // Azul claro
                break;
        }

        $totalPuntos += $puntos;

        echo "<tr style='background-color: $bgColor;'>";
        echo "<td>" . strtoupper($item['tipo']) . "</td>";
        echo "<td>" . htmlspecialchars($item['codigo_item']) . "</td>";
        echo "<td>" . htmlspecialchars($item['titulo']) . "</td>";
        echo "<td>" . (isset($item['cliente']) ? htmlspecialchars($item['cliente']) : 'N/A') . "</td>";
        echo "<td><strong>" . strtoupper(str_replace('_', ' ', $calificacion)) . "</strong></td>";
        echo "<td><strong>" . number_format($puntos, 1) . "</strong></td>";
        echo "</tr>";
    }

    echo "</table>";

    // Calcular porcentaje final
    $porcentajeFinal = round(($totalPuntos / $totalItems) * 100, 2);

    echo "<div style='background-color: #e7f3ff; padding: 20px; border-radius: 5px; margin-top: 20px; border: 2px solid #0d6efd;'>";
    echo "<h3>📊 Resultado Final:</h3>";
    echo "<p style='font-size: 18px;'>";
    echo "<strong>Total Ítems:</strong> $totalItems<br>";
    echo "<strong>Total Puntos:</strong> " . number_format($totalPuntos, 1) . "<br>";
    echo "<strong>Porcentaje de Cumplimiento:</strong> <span style='font-size: 24px; color: #0d6efd;'>$porcentajeFinal%</span>";
    echo "</p>";
    echo "</div>";

    // Mostrar ejemplos de cálculo
    echo "<hr>";
    echo "<h4>Ejemplos de Cálculo:</h4>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<p><strong>Ejemplo 1:</strong> 2 ítems (1 Cumple + 1 No Aplica) = (1.0 + 1.0) / 2 = 100%</p>";
    echo "<p><strong>Ejemplo 2:</strong> 2 ítems (1 Cumple + 1 Parcial) = (1.0 + 0.5) / 2 = 75%</p>";
    echo "<p><strong>Ejemplo 3:</strong> 2 ítems (1 Cumple + 1 No Cumple) = (1.0 + 0.0) / 2 = 50%</p>";
    echo "<p><strong>Ejemplo 4:</strong> 3 ítems (1 Cumple + 1 Parcial + 1 No Aplica) = (1.0 + 0.5 + 1.0) / 3 = 83.33%</p>";
    echo "</div>";
}

$conn->close();
?>
