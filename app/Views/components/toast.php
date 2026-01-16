<!--
    Toast Component - Reutilizable Bootstrap 5

    Uso desde JavaScript:
    - showToast('Guardado exitosamente', 'success')
    - showToast('Error al guardar', 'error')
    - showToast('Advertencia', 'warning')
    - showToast('Información', 'info')
-->

<!-- Toast Container (fijo en esquina superior derecha) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <!-- Toast Principal -->
    <div id="mainToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <!-- Icono dinámico -->
                <i id="toastIcon" class="bi me-2 fs-5"></i>
                <!-- Mensaje -->
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<style>
/* Toast Styles */
.toast-container {
    z-index: 9999;
}

#mainToast {
    min-width: 300px;
    max-width: 400px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Variantes de color */
#mainToast.toast-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
}

#mainToast.toast-error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

#mainToast.toast-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #212529;
}

#mainToast.toast-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

/* Animación de entrada */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

#mainToast.show {
    animation: slideInRight 0.3s ease-out;
}

/* Spinner en botones */
.btn-spinner {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 2px solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to {
        transform: rotate(360deg);
    }
}

/* Campos con error */
.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(0.375em + 0.1875rem) center;
    background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    padding-right: calc(1.5em + 0.75rem);
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Scroll suave */
html {
    scroll-behavior: smooth;
}
</style>

<script>
/**
 * Muestra un toast con mensaje y tipo
 *
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms (default: 4000)
 */
function showToast(message, type = 'info', duration = 6000) {
    const toastEl = document.getElementById('mainToast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');

    if (!toastEl || !toastIcon || !toastMessage) {
        console.error('Toast elements not found');
        return;
    }

    // Configurar icono según tipo
    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };

    // Limpiar clases anteriores
    toastEl.className = 'toast align-items-center border-0';
    toastIcon.className = 'bi me-2 fs-5';

    // Aplicar nuevas clases
    toastEl.classList.add(`toast-${type}`);
    toastIcon.classList.add(icons[type] || icons.info);
    toastMessage.textContent = message;

    // Mostrar toast
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: duration
    });
    toast.show();
}

/**
 * Scroll suave hacia arriba
 *
 * @param {number} duration - Duración de la animación en ms (default: 500)
 */
function smoothScrollTop(duration = 500) {
    const startPosition = window.pageYOffset;
    const startTime = performance.now();

    function scrollAnimation(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Ease-out cubic
        const easeOut = 1 - Math.pow(1 - progress, 3);

        window.scrollTo(0, startPosition * (1 - easeOut));

        if (progress < 1) {
            requestAnimationFrame(scrollAnimation);
        }
    }

    requestAnimationFrame(scrollAnimation);
}

/**
 * Deshabilita un botón y muestra spinner
 *
 * @param {HTMLButtonElement} button - Botón a deshabilitar
 * @param {string} loadingText - Texto mientras carga (default: 'Guardando...')
 */
function disableButtonWithSpinner(button, loadingText = 'Guardando...') {
    if (!button) return;

    // Guardar texto original
    button.dataset.originalText = button.innerHTML;

    // Deshabilitar y mostrar spinner
    button.disabled = true;
    button.innerHTML = `
        <span class="btn-spinner me-2"></span>
        ${loadingText}
    `;
}

/**
 * Re-habilita un botón y restaura texto original
 *
 * @param {HTMLButtonElement} button - Botón a re-habilitar
 */
function enableButton(button) {
    if (!button) return;

    button.disabled = false;
    button.innerHTML = button.dataset.originalText || 'Guardar';
}

/**
 * Marca un campo como inválido
 *
 * @param {HTMLInputElement|HTMLTextAreaElement} field - Campo a marcar
 * @param {string} errorMessage - Mensaje de error
 */
function markFieldInvalid(field, errorMessage) {
    if (!field) return;

    field.classList.add('is-invalid');

    // Buscar o crear feedback
    let feedback = field.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.insertBefore(feedback, field.nextSibling);
    }

    feedback.textContent = errorMessage;
}

/**
 * Limpia validación de un campo
 *
 * @param {HTMLInputElement|HTMLTextAreaElement} field - Campo a limpiar
 */
function clearFieldValidation(field) {
    if (!field) return;

    field.classList.remove('is-invalid');

    const feedback = field.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.remove();
    }
}

/**
 * Limpia validación de todos los campos en un formulario
 *
 * @param {HTMLFormElement} form - Formulario a limpiar
 */
function clearFormValidation(form) {
    if (!form) return;

    const invalidFields = form.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => clearFieldValidation(field));
}
</script>
