<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejemplo: Upload con CSRF Protection</title>

    <!-- Meta tags para CSRF (necesarios para AJAX) -->
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token-hash" content="<?= csrf_hash() ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Ejemplo: Subida de Evidencia con CSRF Protection</h1>

        <!-- ============================================ -->
        <!-- EJEMPLO 1: Formulario HTML Tradicional -->
        <!-- ============================================ -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Método 1: Formulario HTML Tradicional</h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('upload/evidencia/global') ?>" method="POST" enctype="multipart/form-data">

                    <!-- ✅ TOKEN CSRF (OBLIGATORIO) -->
                    <?= csrf_field() ?>

                    <!-- Campos ocultos del contexto -->
                    <input type="hidden" name="id_auditoria" value="123">
                    <input type="hidden" name="id_auditoria_item" value="45">

                    <!-- Campo de archivo -->
                    <div class="mb-3">
                        <label for="evidencia" class="form-label">Evidencia Global</label>
                        <input type="file"
                               class="form-control"
                               id="evidencia"
                               name="evidencia"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.mp4"
                               required>
                        <div class="form-text">
                            <strong>Formatos permitidos:</strong> PDF, JPG, PNG, DOCX, XLSX, MP4<br>
                            <strong>Tamaño máximo:</strong> 15 MB
                        </div>
                    </div>

                    <!-- Descripción opcional -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Subir Evidencia
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- EJEMPLO 2: Upload con AJAX/Fetch -->
        <!-- ============================================ -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Método 2: Upload con AJAX (Fetch API)</h5>
            </div>
            <div class="card-body">
                <form id="formAjax">
                    <!-- Campos ocultos -->
                    <input type="hidden" id="ajax_id_auditoria" value="123">
                    <input type="hidden" id="ajax_id_cliente" value="5">
                    <input type="hidden" id="ajax_id_auditoria_item_cliente" value="78">

                    <!-- Campo de archivo -->
                    <div class="mb-3">
                        <label for="evidencia_ajax" class="form-label">Evidencia por Cliente (AJAX)</label>
                        <input type="file"
                               class="form-control"
                               id="evidencia_ajax"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.mp4">
                    </div>

                    <button type="button" class="btn btn-success" onclick="uploadWithAjax()">
                        <i class="bi bi-cloud-upload"></i> Subir con AJAX
                    </button>

                    <!-- Barra de progreso -->
                    <div class="progress mt-3" style="display: none;" id="progressBar">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             style="width: 0%"
                             id="progressBarInner"></div>
                    </div>

                    <!-- Mensaje de resultado -->
                    <div id="ajaxResult" class="mt-3"></div>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- EJEMPLO 3: Upload Múltiple -->
        <!-- ============================================ -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Método 3: Upload Múltiple</h5>
            </div>
            <div class="card-body">
                <form action="<?= site_url('upload/evidencias/multiple') ?>" method="POST" enctype="multipart/form-data">

                    <!-- ✅ TOKEN CSRF -->
                    <?= csrf_field() ?>

                    <input type="hidden" name="id_auditoria" value="123">
                    <input type="hidden" name="id_auditoria_item" value="45">

                    <!-- Campo de archivos múltiples -->
                    <div class="mb-3">
                        <label for="evidencias_multiple" class="form-label">Evidencias Múltiples</label>
                        <input type="file"
                               class="form-control"
                               id="evidencias_multiple"
                               name="evidencias[]"
                               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.mp4"
                               multiple>
                        <div class="form-text">
                            Puedes seleccionar múltiples archivos (máximo 5 archivos, 15 MB cada uno)
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-files"></i> Subir Múltiples Archivos
                    </button>
                </form>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- INFORMACIÓN DE SEGURIDAD -->
        <!-- ============================================ -->
        <div class="alert alert-info">
            <h5><i class="bi bi-shield-check"></i> Medidas de Seguridad Implementadas:</h5>
            <ul class="mb-0">
                <li>✅ Protección CSRF en todos los formularios</li>
                <li>✅ Validación MIME real con <code>finfo_file()</code></li>
                <li>✅ Bloqueo de extensiones peligrosas: php, phar, phtml, js, exe, sh, bat, cmd, dll</li>
                <li>✅ Detección de doble extensión (archivo.php.jpg)</li>
                <li>✅ Límite de 15 MB por archivo</li>
                <li>✅ Logging de intentos fallidos con IP, user_id y contexto</li>
            </ul>
        </div>
    </div>

    <script>
        /**
         * Upload con AJAX usando Fetch API
         */
        function uploadWithAjax() {
            const fileInput = document.getElementById('evidencia_ajax');
            const file = fileInput.files[0];

            if (!file) {
                alert('Por favor selecciona un archivo');
                return;
            }

            // Obtener token CSRF del meta tag
            const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
            const csrfHash = document.querySelector('meta[name="csrf-token-hash"]').content;

            // Crear FormData
            const formData = new FormData();
            formData.append(csrfName, csrfHash);
            formData.append('evidencia', file);
            formData.append('id_auditoria', document.getElementById('ajax_id_auditoria').value);
            formData.append('id_cliente', document.getElementById('ajax_id_cliente').value);
            formData.append('id_auditoria_item_cliente', document.getElementById('ajax_id_auditoria_item_cliente').value);

            // Mostrar barra de progreso
            const progressBar = document.getElementById('progressBar');
            const progressBarInner = document.getElementById('progressBarInner');
            progressBar.style.display = 'block';

            // Crear XMLHttpRequest para tracking de progreso
            const xhr = new XMLHttpRequest();

            // Evento de progreso
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBarInner.style.width = percentComplete + '%';
                }
            });

            // Evento de carga completa
            xhr.addEventListener('load', function() {
                progressBar.style.display = 'none';
                const resultDiv = document.getElementById('ajaxResult');

                try {
                    const response = JSON.parse(xhr.responseText);

                    if (response.ok) {
                        resultDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h5>✅ Archivo subido exitosamente</h5>
                                <p><strong>Ruta:</strong> ${response.path}</p>
                                <p><strong>Hash SHA256:</strong> <code>${response.hash}</code></p>
                            </div>
                        `;
                        fileInput.value = '';
                    } else {
                        resultDiv.innerHTML = `
                            <div class="alert alert-danger">
                                <h5>❌ Error al subir archivo</h5>
                                <p>${response.error}</p>
                            </div>
                        `;
                    }
                } catch (e) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>❌ Error inesperado</h5>
                            <p>Respuesta del servidor no válida</p>
                        </div>
                    `;
                }
            });

            // Evento de error
            xhr.addEventListener('error', function() {
                progressBar.style.display = 'none';
                document.getElementById('ajaxResult').innerHTML = `
                    <div class="alert alert-danger">
                        <h5>❌ Error de red</h5>
                        <p>No se pudo conectar con el servidor</p>
                    </div>
                `;
            });

            // Enviar request
            xhr.open('POST', '/upload/evidencia/cliente');
            xhr.send(formData);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
