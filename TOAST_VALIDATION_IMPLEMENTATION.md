# Implementación de Toast Reutilizable y Validación de Formularios

## Resumen

Sistema completo de feedback visual para formularios con:
- ✅ Toast Bootstrap 5 reutilizable (success, error, warning, info)
- ✅ Scroll suave al tope después de guardar
- ✅ Botón con spinner durante la carga
- ✅ Validación de campos con marcado visual
- ✅ Auto-limpieza de errores al editar

---

## 1. Componente Toast Reutilizable

**Archivo**: `app/Views/components/toast.php`

### Uso desde cualquier vista:

```php
<?= $this->include('components/toast') ?>
```

### HTML del Toast:

```html
<!-- Toast Container (esquina superior derecha) -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="mainToast" class="toast align-items-center border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i id="toastIcon" class="bi me-2 fs-5"></i>
                <span id="toastMessage"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>
```

### CSS Estilos:

```css
/* Toast principal */
#mainToast {
    min-width: 300px;
    max-width: 400px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Variantes con gradientes */
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
```

---

## 2. Funciones JavaScript Disponibles

### A. `showToast(message, type, duration)`

Muestra un toast con mensaje y tipo específico.

**Parámetros**:
- `message` (string): Mensaje a mostrar
- `type` (string): Tipo de toast - `'success'`, `'error'`, `'warning'`, `'info'`
- `duration` (number, opcional): Duración en milisegundos (default: 4000)

**Ejemplos**:
```javascript
// Toast de éxito (verde)
showToast('Cliente guardado exitosamente', 'success');

// Toast de error (rojo)
showToast('Error al guardar el formulario', 'error');

// Toast de advertencia (amarillo)
showToast('Complete todos los campos obligatorios', 'warning');

// Toast de información (azul)
showToast('Recuerde revisar las evidencias', 'info');

// Con duración personalizada (10 segundos)
showToast('Proceso completado', 'success', 10000);
```

**Iconos por tipo**:
| Tipo | Icono | Color |
|------|-------|-------|
| success | `bi-check-circle-fill` | Verde |
| error | `bi-x-circle-fill` | Rojo |
| warning | `bi-exclamation-triangle-fill` | Amarillo |
| info | `bi-info-circle-fill` | Azul |

---

### B. `smoothScrollTop(duration)`

Scroll animado hacia el tope de la página.

**Parámetros**:
- `duration` (number, opcional): Duración de la animación en ms (default: 500)

**Ejemplos**:
```javascript
// Scroll rápido (300ms)
smoothScrollTop(300);

// Scroll normal (500ms)
smoothScrollTop();

// Scroll lento (1000ms)
smoothScrollTop(1000);
```

**Algoritmo**: Ease-out cubic para animación suave

---

### C. `disableButtonWithSpinner(button, loadingText)`

Deshabilita un botón y muestra spinner de carga.

**Parámetros**:
- `button` (HTMLButtonElement): Botón a deshabilitar
- `loadingText` (string, opcional): Texto durante la carga (default: 'Guardando...')

**Ejemplo**:
```javascript
const btnGuardar = document.getElementById('btnGuardar');

// Deshabilitar con texto por defecto
disableButtonWithSpinner(btnGuardar);

// Deshabilitar con texto personalizado
disableButtonWithSpinner(btnGuardar, 'Procesando...');
disableButtonWithSpinner(btnGuardar, 'Subiendo archivos...');
```

**Resultado visual**:
```
Antes:  [✓ Guardar Cliente]
Durante: [⚙️ Guardando...]  (botón deshabilitado con spinner)
```

---

### D. `enableButton(button)`

Re-habilita un botón y restaura su texto original.

**Parámetros**:
- `button` (HTMLButtonElement): Botón a re-habilitar

**Ejemplo**:
```javascript
const btnGuardar = document.getElementById('btnGuardar');

// Re-habilitar botón
enableButton(btnGuardar);
```

**Nota**: Automáticamente restaura el HTML original guardado en `button.dataset.originalText`

---

### E. `markFieldInvalid(field, errorMessage)`

Marca un campo como inválido con mensaje de error.

**Parámetros**:
- `field` (HTMLInputElement|HTMLTextAreaElement): Campo a marcar
- `errorMessage` (string): Mensaje de error a mostrar

**Ejemplo**:
```javascript
const comentario = document.getElementById('comentario_proveedor_cliente');

// Marcar campo con error
markFieldInvalid(comentario, 'El comentario debe tener al menos 10 caracteres');
```

**Resultado HTML**:
```html
<textarea class="form-control is-invalid" id="comentario_proveedor_cliente">...</textarea>
<div class="invalid-feedback">El comentario debe tener al menos 10 caracteres</div>
```

**CSS aplicado**:
```css
.is-invalid {
    border-color: #dc3545 !important;
    background-image: url("data:image/svg+xml..."); /* Icono de error */
    background-repeat: no-repeat;
    background-position: right;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 0.875rem;
}
```

---

### F. `clearFieldValidation(field)`

Limpia la validación de un campo específico.

**Parámetros**:
- `field` (HTMLInputElement|HTMLTextAreaElement): Campo a limpiar

**Ejemplo**:
```javascript
const comentario = document.getElementById('comentario_proveedor_cliente');

// Limpiar error
clearFieldValidation(comentario);
```

---

### G. `clearFormValidation(form)`

Limpia todos los errores de validación en un formulario.

**Parámetros**:
- `form` (HTMLFormElement): Formulario a limpiar

**Ejemplo**:
```javascript
const form = document.getElementById('formGuardarCliente');

// Limpiar todos los errores del formulario
clearFormValidation(form);
```

---

## 3. Ejemplo de Implementación Completa

### Vista con Formulario:

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Incluir Toast Component -->
<?= $this->include('components/toast') ?>

<form id="formGuardarCliente" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Comentario -->
    <div class="mb-3">
        <label for="comentario_proveedor_cliente" class="form-label">
            Comentario <span class="text-danger">*</span>
        </label>
        <textarea
            class="form-control"
            id="comentario_proveedor_cliente"
            name="comentario_proveedor_cliente"
            required></textarea>
    </div>

    <!-- Evidencias -->
    <div class="mb-3">
        <label for="evidencias_cliente" class="form-label">
            Evidencias <span class="text-danger">*</span>
        </label>
        <input
            type="file"
            class="form-control"
            id="evidencias_cliente"
            name="evidencias_cliente[]"
            multiple
            required>
    </div>

    <!-- Botón -->
    <button type="submit" class="btn btn-success" id="btnGuardar">
        <i class="bi bi-check-circle"></i> Guardar Cliente
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formGuardarCliente');
    const btnGuardar = document.getElementById('btnGuardar');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // 1. Limpiar errores anteriores
        clearFormValidation(form);

        // 2. Validar campos
        const comentario = document.getElementById('comentario_proveedor_cliente');
        const evidencias = document.getElementById('evidencias_cliente');
        let isValid = true;

        if (comentario.value.trim().length < 10) {
            markFieldInvalid(comentario, 'Mínimo 10 caracteres');
            isValid = false;
        }

        if (evidencias.files.length === 0) {
            markFieldInvalid(evidencias, 'Debe subir al menos un archivo');
            isValid = false;
        }

        // 3. Si hay errores, mostrar toast y salir
        if (!isValid) {
            showToast('Por favor corrija los errores', 'error');
            smoothScrollTop(300);
            return;
        }

        // 4. Deshabilitar botón
        disableButtonWithSpinner(btnGuardar, 'Guardando...');

        // 5. Enviar formulario (AJAX)
        const formData = new FormData(form);

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
                // Éxito
                showToast('✅ Cliente guardado exitosamente', 'success', 5000);
                smoothScrollTop(500);
                form.reset();
            } else {
                // Error
                showToast(data.message || '❌ Error al guardar', 'error');

                // Marcar campos con error
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
            showToast('❌ Error de conexión', 'error');
        })
        .finally(() => {
            // 6. Re-habilitar botón
            enableButton(btnGuardar);
        });
    });

    // Auto-limpiar errores al escribir
    const formFields = form.querySelectorAll('input, textarea, select');
    formFields.forEach(field => {
        field.addEventListener('input', function() {
            clearFieldValidation(this);
        });
    });
});
</script>

<?= $this->endSection() ?>
```

---

## 4. Respuesta JSON del Controlador

### Respuesta exitosa:

```json
{
  "success": true,
  "message": "Cliente guardado exitosamente",
  "data": {
    "id_auditoria_item_cliente": 123,
    "id_cliente": 45
  }
}
```

### Respuesta con error:

```json
{
  "success": false,
  "message": "Error al guardar el cliente",
  "errors": {
    "comentario_proveedor_cliente": "El comentario debe tener al menos 10 caracteres",
    "evidencias_cliente": "Debe subir al menos un archivo de evidencia"
  }
}
```

### Ejemplo en Controller:

```php
public function guardarItemPorCliente()
{
    $validation = $this->validate([
        'comentario_proveedor_cliente' => 'required|min_length[10]',
        'evidencias_cliente' => 'uploaded[evidencias_cliente]',
    ]);

    if (!$validation) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Error de validación',
            'errors' => $this->validator->getErrors()
        ]);
    }

    // Procesar y guardar...

    return $this->response->setJSON([
        'success' => true,
        'message' => 'Cliente guardado exitosamente',
        'data' => ['id' => $idGuardado]
    ]);
}
```

---

## 5. Flujo Completo de Guardado

### Paso a Paso:

```
1. Usuario completa formulario
   ↓
2. Click en "Guardar Cliente"
   ↓
3. JavaScript: clearFormValidation(form)
   ↓
4. JavaScript: Validar campos localmente
   ↓
5. ¿Hay errores?
   │
   ├─ SÍ → markFieldInvalid() + showToast('error') + smoothScrollTop()
   │        [FIN]
   │
   └─ NO → Continuar
       ↓
6. disableButtonWithSpinner(btnGuardar, 'Guardando...')
   [Botón muestra: ⚙️ Guardando...]
   ↓
7. Enviar FormData vía fetch()
   ↓
8. Servidor procesa
   ↓
9. Respuesta JSON
   │
   ├─ success: true
   │   ↓
   │   showToast('✅ Cliente guardado', 'success')
   │   smoothScrollTop(500)
   │   form.reset()
   │
   └─ success: false
       ↓
       showToast('❌ Error', 'error')
       markFieldInvalid() para cada campo con error
   ↓
10. enableButton(btnGuardar)
    [Botón restaurado: ✓ Guardar Cliente]
```

---

## 6. Validaciones Comunes

### A. Comentario mínimo 10 caracteres:

```javascript
const comentario = document.getElementById('comentario_proveedor_cliente');

if (comentario.value.trim().length < 10) {
    markFieldInvalid(comentario, 'El comentario debe tener al menos 10 caracteres');
    isValid = false;
}
```

### B. Archivos obligatorios:

```javascript
const evidencias = document.getElementById('evidencias_cliente');

if (evidencias.files.length === 0) {
    markFieldInvalid(evidencias, 'Debe subir al menos un archivo de evidencia');
    isValid = false;
}
```

### C. Tamaño máximo de archivo:

```javascript
const maxSize = 15 * 1024 * 1024; // 15 MB

for (let i = 0; i < evidencias.files.length; i++) {
    if (evidencias.files[i].size > maxSize) {
        markFieldInvalid(evidencias, `El archivo "${evidencias.files[i].name}" excede 15 MB`);
        isValid = false;
        break;
    }
}
```

### D. Extensiones permitidas:

```javascript
const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'mp4', 'xlsx', 'docx'];

for (let i = 0; i < evidencias.files.length; i++) {
    const fileName = evidencias.files[i].name;
    const extension = fileName.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(extension)) {
        markFieldInvalid(evidencias, `Extensión .${extension} no permitida`);
        isValid = false;
        break;
    }
}
```

### E. Select obligatorio:

```javascript
const calificacion = document.getElementById('calificacion');

if (!calificacion.value) {
    markFieldInvalid(calificacion, 'Debe seleccionar un estado de cumplimiento');
    isValid = false;
}
```

---

## 7. Personalización

### A. Cambiar colores del toast:

```css
/* Toast personalizado (violeta) */
#mainToast.toast-custom {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: white;
}
```

```javascript
// Usar toast personalizado
showToast('Mensaje especial', 'custom');
```

### B. Cambiar posición del toast:

```html
<!-- Inferior derecha -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    ...
</div>

<!-- Centrado arriba -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
    ...
</div>
```

### C. Cambiar duración global:

```javascript
// Toast que dura 10 segundos
showToast('Mensaje largo', 'info', 10000);
```

### D. Personalizar spinner:

```css
.btn-spinner {
    width: 1.2rem;
    height: 1.2rem;
    border-width: 3px;
}
```

---

## 8. Compatibilidad

- ✅ Bootstrap 5.x
- ✅ Bootstrap Icons (bi-*)
- ✅ Chrome, Firefox, Safari, Edge (últimas versiones)
- ✅ Responsive (mobile-friendly)

---

## 9. Archivos del Sistema

### Creados:
- ✅ `app/Views/components/toast.php` - Componente reutilizable
- ✅ `app/Views/ejemplos/wizard_form_example.php` - Ejemplo completo

### Documentación:
- ✅ `TOAST_VALIDATION_IMPLEMENTATION.md` - Este archivo

---

## 10. Checklist de Implementación

Para agregar en un formulario nuevo:

- [ ] Incluir componente: `<?= $this->include('components/toast') ?>`
- [ ] Agregar ID al botón submit: `id="btnGuardar"`
- [ ] Agregar listener al submit del form
- [ ] Llamar `clearFormValidation(form)` al inicio
- [ ] Validar campos y usar `markFieldInvalid()` si hay errores
- [ ] Llamar `disableButtonWithSpinner()` antes de fetch
- [ ] Hacer fetch con FormData
- [ ] Mostrar `showToast()` según resultado
- [ ] Llamar `smoothScrollTop()` después de éxito
- [ ] Llamar `enableButton()` en `.finally()`
- [ ] Agregar listeners para auto-limpiar errores al escribir

---

## Implementación Completada ✅

Sistema completo de Toast + Validación listo para usar en cualquier formulario del proyecto.
