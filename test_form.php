<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
</head>
<body>
    <h2>Test - ¿El formulario funciona?</h2>
    
    <form method="post" action="http://localhost/auditorias/public/index.php/consultor/auditoria/item/25/calificar-global">
        <input type="hidden" name="csrf_test_name" value="test123">
        
        <label>
            <input type="radio" name="calificacion_consultor" value="cumple" required> Cumple
        </label><br>
        <label>
            <input type="radio" name="calificacion_consultor" value="parcial"> Parcial
        </label><br>
        <label>
            <input type="radio" name="calificacion_consultor" value="no_cumple"> No Cumple
        </label><br>
        
        <textarea name="comentario_consultor">Test comentario</textarea><br>
        
        <button type="submit">GUARDAR TEST</button>
    </form>
    
    <hr>
    <p><strong>Instrucciones:</strong></p>
    <ol>
        <li>Selecciona un radio button</li>
        <li>Dale clic en GUARDAR TEST</li>
        <li>Verifica el log: writable/logs/log-2025-10-28.log</li>
        <li>Busca "calificarItemGlobal"</li>
    </ol>
</body>
</html>
