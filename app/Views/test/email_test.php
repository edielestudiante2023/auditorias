<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">🧪 Test Email Service</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Nota:</strong> Este controlador es solo para pruebas. Eliminar en producción.
                    </div>

                    <!-- Test Invite Proveedor -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>1. Test: Invitación a Proveedor</h5>
                        </div>
                        <div class="card-body">
                            <form id="formInvite">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="test@example.com">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Usuario</label>
                                        <input type="text" class="form-control" name="usuario" value="proveedor_test">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contraseña</label>
                                        <input type="text" class="form-control" name="clave" value="Temporal123!">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Invitación</button>
                            </form>
                            <div id="resultInvite" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test Proveedor Finalizó -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>2. Test: Proveedor Finalizó Auditoría</h5>
                        </div>
                        <div class="card-body">
                            <form id="formFinalizo">
                                <div class="mb-3">
                                    <label class="form-label">ID Auditoría</label>
                                    <input type="number" class="form-control" name="id_auditoria" value="1">
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Notificación</button>
                            </form>
                            <div id="resultFinalizo" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Test Auditoría Cerrada -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5>3. Test: Auditoría Cerrada</h5>
                        </div>
                        <div class="card-body">
                            <form id="formCierre">
                                <div class="mb-3">
                                    <label class="form-label">ID Auditoría</label>
                                    <input type="number" class="form-control" name="id_auditoria" value="1">
                                </div>
                                <button type="submit" class="btn btn-primary">Enviar Notificación</button>
                            </form>
                            <div id="resultCierre" class="mt-3"></div>
                        </div>
                    </div>

                    <!-- Ver Último Log -->
                    <div class="card">
                        <div class="card-header">
                            <h5>4. Ver Último Email en Logs</h5>
                        </div>
                        <div class="card-body">
                            <a href="<?= site_url('test-email/ver-ultimo-log') ?>" target="_blank" class="btn btn-info">
                                Ver Último Email en Logs
                            </a>
                            <p class="mt-2 text-muted small">
                                <strong>Nota:</strong> Si no hay API key configurada, los emails se guardan en
                                <code>writable/email_logs/</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Helper para enviar formularios
    function sendForm(formId, url, resultId) {
        document.getElementById(formId).addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const resultDiv = document.getElementById(resultId);

            resultDiv.innerHTML = '<div class="alert alert-info">Enviando...</div>';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.ok) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✓ Éxito:</strong> ${result.message}
                            <pre class="mt-2 mb-0">${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>✗ Error:</strong> ${result.message}
                            <br><small>${result.error || ''}</small>
                            <pre class="mt-2 mb-0">${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>✗ Error de conexión:</strong> ${error.message}
                    </div>
                `;
            }
        });
    }

    // Configurar formularios
    sendForm('formInvite', '<?= site_url('test-email/invite-proveedor') ?>', 'resultInvite');
    sendForm('formFinalizo', '<?= site_url('test-email/proveedor-finalizo') ?>', 'resultFinalizo');
    sendForm('formCierre', '<?= site_url('test-email/cierre-auditoria') ?>', 'resultCierre');
</script>

</body>
</html>
