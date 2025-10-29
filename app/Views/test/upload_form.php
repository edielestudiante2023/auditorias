<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-cloud-upload"></i> Test UploadService
                        </h4>
                        <small>Prueba de servicio centralizado de subida de archivos</small>
                    </div>
                    <div class="card-body">
                        <!-- Alert para resultados -->
                        <div id="result-alert" class="alert alert-dismissible fade" role="alert" style="display:none;">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <div id="result-content"></div>
                        </div>

                        <!-- Información -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Información</h6>
                            <ul class="mb-0 small">
                                <li>Tipos permitidos: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG, JPEG</li>
                                <li>Tamaño máximo: 20 MB</li>
                                <li>Validación MIME con finfo (no solo extensión)</li>
                                <li>Los archivos se guardan en <code>writable/uploads/</code></li>
                            </ul>
                        </div>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="uploadTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#firma-tab">
                                    <i class="bi bi-pen"></i> Firma Consultor
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#logo-tab">
                                    <i class="bi bi-image"></i> Logo Cliente
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#contrato-tab">
                                    <i class="bi bi-file-earmark-pdf"></i> Soporte Contrato
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#evidencia-tab">
                                    <i class="bi bi-paperclip"></i> Evidencia
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#helper-tab">
                                    <i class="bi bi-code-square"></i> Con Helpers
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content p-4 border border-top-0">
                            <!-- Tab Firma -->
                            <div class="tab-pane fade show active" id="firma-tab">
                                <h5>Subir Firma de Consultor</h5>
                                <form id="form-firma" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">ID Consultor</label>
                                        <input type="number" name="id_consultor" class="form-control" value="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Archivo de Firma</label>
                                        <input type="file" name="firma" class="form-control" accept=".png,.jpg,.jpeg,.pdf" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Subir Firma
                                    </button>
                                </form>
                            </div>

                            <!-- Tab Logo -->
                            <div class="tab-pane fade" id="logo-tab">
                                <h5>Subir Logo de Cliente</h5>
                                <form id="form-logo" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">ID Cliente</label>
                                        <input type="number" name="id_cliente" class="form-control" value="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Archivo de Logo</label>
                                        <input type="file" name="logo" class="form-control" accept=".png,.jpg,.jpeg" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Subir Logo
                                    </button>
                                </form>
                            </div>

                            <!-- Tab Contrato -->
                            <div class="tab-pane fade" id="contrato-tab">
                                <h5>Subir Soporte de Contrato</h5>
                                <form id="form-contrato" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">ID Contrato</label>
                                        <input type="number" name="id_contrato" class="form-control" value="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Documento</label>
                                        <input type="file" name="documento" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Subir Documento
                                    </button>
                                </form>
                            </div>

                            <!-- Tab Evidencia -->
                            <div class="tab-pane fade" id="evidencia-tab">
                                <h5>Subir Evidencia de Auditoría</h5>
                                <form id="form-evidencia" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">NIT</label>
                                            <input type="text" name="nit" class="form-control" value="900123456">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ID Auditoría</label>
                                            <input type="number" name="id_auditoria" class="form-control" value="1">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ID Item</label>
                                            <input type="number" name="id_item" class="form-control" value="1">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Archivo de Evidencia</label>
                                        <input type="file" name="evidencia" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Subir Evidencia
                                    </button>
                                </form>
                            </div>

                            <!-- Tab Helpers -->
                            <div class="tab-pane fade" id="helper-tab">
                                <h5>Subir usando Upload Helpers</h5>
                                <form id="form-helper" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Tipo de Upload</label>
                                        <select name="tipo" class="form-select">
                                            <option value="firma">Firma Consultor</option>
                                            <option value="logo">Logo Cliente</option>
                                            <option value="contrato">Soporte Contrato</option>
                                            <option value="evidencia">Evidencia</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Archivo</label>
                                        <input type="file" name="archivo" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-upload"></i> Subir con Helper
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Botón para listar -->
                        <div class="mt-4">
                            <button id="btn-list" class="btn btn-secondary">
                                <i class="bi bi-list"></i> Ver Archivos Subidos
                            </button>
                        </div>

                        <!-- Lista de archivos -->
                        <div id="files-list" class="mt-4" style="display:none;">
                            <h5>Archivos Subidos</h5>
                            <div id="files-content"></div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 alert alert-warning">
                    <strong><i class="bi bi-exclamation-triangle"></i> Nota:</strong>
                    Este controlador es solo para pruebas. Eliminar en producción.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const baseUrl = '<?= base_url() ?>';

        function showResult(success, message, data = null) {
            const alert = document.getElementById('result-alert');
            const content = document.getElementById('result-content');

            alert.className = `alert alert-${success ? 'success' : 'danger'} alert-dismissible fade show`;
            alert.style.display = 'block';

            let html = `<strong>${success ? '✓' : '✗'}</strong> ${message}`;

            if (data) {
                html += '<hr class="my-2"><pre class="mb-0 small">' + JSON.stringify(data, null, 2) + '</pre>';
            }

            content.innerHTML = html;
        }

        async function uploadFile(formId, endpoint) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);

            try {
                const response = await fetch(baseUrl + 'test-upload/' + endpoint, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.ok) {
                    showResult(true, 'Archivo subido exitosamente', result);
                    form.reset();
                } else {
                    showResult(false, result.error || 'Error al subir archivo', result);
                }
            } catch (error) {
                showResult(false, 'Error de conexión: ' + error.message);
            }
        }

        // Event listeners
        document.getElementById('form-firma').addEventListener('submit', (e) => {
            e.preventDefault();
            uploadFile('form-firma', 'test-firma-consultor');
        });

        document.getElementById('form-logo').addEventListener('submit', (e) => {
            e.preventDefault();
            uploadFile('form-logo', 'test-logo-cliente');
        });

        document.getElementById('form-contrato').addEventListener('submit', (e) => {
            e.preventDefault();
            uploadFile('form-contrato', 'test-soporte-contrato');
        });

        document.getElementById('form-evidencia').addEventListener('submit', (e) => {
            e.preventDefault();
            uploadFile('form-evidencia', 'test-evidencia');
        });

        document.getElementById('form-helper').addEventListener('submit', (e) => {
            e.preventDefault();
            uploadFile('form-helper', 'test-helpers');
        });

        document.getElementById('btn-list').addEventListener('click', async () => {
            try {
                const response = await fetch(baseUrl + 'test-upload/list-uploads');
                const files = await response.json();

                let html = '';

                if (files.firmas && files.firmas.length > 0) {
                    html += '<h6>Firmas de Consultores</h6><ul class="list-group mb-3">';
                    files.firmas.forEach(f => {
                        html += `<li class="list-group-item"><strong>${f.name}</strong><br>
                                <small>Tamaño: ${f.size} | MIME: ${f.mime}</small></li>`;
                    });
                    html += '</ul>';
                }

                if (files.logos && files.logos.length > 0) {
                    html += '<h6>Logos de Clientes</h6><ul class="list-group mb-3">';
                    files.logos.forEach(f => {
                        html += `<li class="list-group-item"><strong>${f.name}</strong><br>
                                <small>Tamaño: ${f.size} | MIME: ${f.mime}</small></li>`;
                    });
                    html += '</ul>';
                }

                if (!html) {
                    html = '<p class="text-muted">No hay archivos subidos aún.</p>';
                }

                document.getElementById('files-content').innerHTML = html;
                document.getElementById('files-list').style.display = 'block';
            } catch (error) {
                showResult(false, 'Error al listar archivos: ' + error.message);
            }
        });
    </script>
</body>
</html>
