<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<!-- Incluir componente Toast -->
<?= $this->include('components/toast') ?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Ejemplo: Wizard con Toast y Validación</h1>

    <!-- Formulario de ejemplo para guardar por cliente -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-pencil-square"></i> Diligenciar Ítem por Cliente
        </div>
        <div class="card-body">
            <form id="formGuardarCliente" action="<?= site_url('proveedor/auditoria/guardar-item') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <input type="hidden" name="id_auditoria_item" value="123">
                <input type="hidden" name="id_cliente" value="45">

                <!-- Cliente Info -->
                <div class="alert alert-info">
                    <i class="bi bi-building"></i> <strong>Cliente:</strong> Empresa XYZ S.A. (NIT: 900123456)
                </div>

                <!-- Comentario del Proveedor -->
                <div class="mb-3">
                    <label for="comentario_proveedor_cliente" class="form-label">
                        Comentario / Observaciones <span class="text-danger">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="comentario_proveedor_cliente"
                        name="comentario_proveedor_cliente"
                        rows="4"
                        placeholder="Escriba aquí sus observaciones sobre el cumplimiento del ítem..."
                        required></textarea>
                    <div class="form-text">Mínimo 10 caracteres</div>
                </div>

                <!-- Evidencias -->
                <div class="mb-3">
                    <label for="evidencias_cliente" class="form-label">
                        Evidencias (Archivos) <span class="text-danger">*</span>
                    </label>
                    <input
                        type="file"
                        class="form-control"
                        id="evidencias_cliente"
                        name="evidencias_cliente[]"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png,.mp4,.xlsx,.docx">
                    <div class="form-text">
                        Formatos permitidos: PDF, JPG, PNG, MP4, XLSX, DOCX. Máximo 15 MB por archivo.
                    </div>
                </div>

                <!-- Estado/Calificación -->
                <div class="mb-3">
                    <label for="calificacion" class="form-label">
                        Estado de Cumplimiento <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="calificacion" name="calificacion" required>
                        <option value="">Seleccione...</option>
                        <option value="cumple">✅ Cumple</option>
                        <option value="parcial">⚠️ Cumple Parcialmente</option>
                        <option value="no_cumple">❌ No Cumple</option>
                        <option value="no_aplica">➖ No Aplica</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="bi bi-arrow-left"></i> Volver
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        <i class="bi bi-check-circle"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card de demostración de toasts -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <i class="bi bi-code-square"></i> Demostración de Toasts
        </div>
        <div class="card-body">
            <p class="mb-3">Prueba los diferentes tipos de toast:</p>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-success" onclick="showToast('Operación exitosa', 'success')">
                    Success Toast
                </button>
                <button type="button" class="btn btn-danger" onclick="showToast('Error al procesar', 'error')">
                    Error Toast
                </button>
                <button type="button" class="btn btn-warning" onclick="showToast('Advertencia importante', 'warning')">
                    Warning Toast
                </button>
                <button type="button" class="btn btn-info" onclick="showToast('Información relevante', 'info')">
                    Info Toast
                </button>
            </div>

            <hr>

            <p class="mb-3">Prueba el scroll suave:</p>
            <button type="button" class="btn btn-primary" onclick="smoothScrollTop()">
                <i class="bi bi-arrow-up"></i> Scroll to Top
            </button>

            <hr>

            <p class="mb-3">Prueba validación de campos:</p>
            <div class="mb-3">
                <label class="form-label">Campo de prueba</label>
                <input type="text" class="form-control" id="testField" placeholder="Escribe algo...">
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-danger btn-sm" onclick="markFieldInvalid(document.getElementById('testField'), 'Este campo es obligatorio')">
                    Marcar como Inválido
                </button>
                <button type="button" class="btn btn-success btn-sm" onclick="clearFieldValidation(document.getElementById('testField'))">
                    Limpiar Validación
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formGuardarCliente');
    const btnGuardar = document.getElementById('btnGuardar');

    if (!form || !btnGuardar) return;

    // Evento submit del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Limpiar validaciones anteriores
        clearFormValidation(form);

        // Validar campos
        const comentario = document.getElementById('comentario_proveedor_cliente');
        const evidencias = document.getElementById('evidencias_cliente');
        const calificacion = document.getElementById('calificacion');

        let isValid = true;

        // Validar comentario (mínimo 10 caracteres)
        if (!comentario.value || comentario.value.trim().length < 10) {
            markFieldInvalid(comentario, 'El comentario debe tener al menos 10 caracteres');
            isValid = false;
        }

        // Validar evidencias (al menos 1 archivo)
        if (evidencias.files.length === 0) {
            markFieldInvalid(evidencias, 'Debe subir al menos un archivo de evidencia');
            isValid = false;
        }

        // Validar archivos - tamaño máximo 15MB
        if (evidencias.files.length > 0) {
            const maxSize = 15 * 1024 * 1024; // 15 MB
            for (let i = 0; i < evidencias.files.length; i++) {
                if (evidencias.files[i].size > maxSize) {
                    markFieldInvalid(evidencias, `El archivo "${evidencias.files[i].name}" excede el tamaño máximo de 15 MB`);
                    isValid = false;
                    break;
                }
            }
        }

        // Validar calificación
        if (!calificacion.value) {
            markFieldInvalid(calificacion, 'Debe seleccionar un estado de cumplimiento');
            isValid = false;
        }

        // Si hay errores, mostrar toast y no continuar
        if (!isValid) {
            showToast('Por favor corrija los errores en el formulario', 'error');
            smoothScrollTop(300);
            return;
        }

        // Deshabilitar botón y mostrar spinner
        disableButtonWithSpinner(btnGuardar, 'Guardando...');

        // Simular envío de formulario (en producción, usar FormData y fetch)
        const formData = new FormData(form);

        // Simular petición AJAX
        // En producción, reemplazar con fetch real
        setTimeout(() => {
            // Simular respuesta exitosa
            const success = Math.random() > 0.3; // 70% éxito

            if (success) {
                // Éxito
                showToast('✅ Cliente guardado exitosamente', 'success', 5000);
                smoothScrollTop(500);

                // Re-habilitar botón
                enableButton(btnGuardar);

                // Opcional: Limpiar formulario
                form.reset();
            } else {
                // Error
                showToast('❌ Error al guardar. Intente nuevamente.', 'error', 5000);

                // Re-habilitar botón
                enableButton(btnGuardar);

                // Marcar campo de comentario como ejemplo
                markFieldInvalid(comentario, 'Error en el servidor al procesar el comentario');
            }
        }, 2000); // Simular 2 segundos de procesamiento

        /* EJEMPLO DE IMPLEMENTACIÓN REAL CON FETCH:

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('✅ Cliente guardado exitosamente', 'success', 5000);
                smoothScrollTop(500);
                form.reset();
            } else {
                showToast(data.message || '❌ Error al guardar', 'error');

                // Marcar campos específicos con error si vienen en la respuesta
                if (data.errors) {
                    Object.keys(data.errors).forEach(fieldName => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            markFieldInvalid(field, data.errors[fieldName]);
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('❌ Error de conexión. Intente nuevamente.', 'error');
        })
        .finally(() => {
            enableButton(btnGuardar);
        });

        */
    });

    // Limpiar validación cuando el usuario empieza a escribir
    const formFields = form.querySelectorAll('input, textarea, select');
    formFields.forEach(field => {
        field.addEventListener('input', function() {
            clearFieldValidation(this);
        });

        field.addEventListener('change', function() {
            clearFieldValidation(this);
        });
    });
});
</script>

<?= $this->endSection() ?>
