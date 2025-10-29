# Toast Component - Guía Rápida

## 🚀 Uso Básico

### 1. Incluir en Vista
```php
<?= $this->include('components/toast') ?>
```

### 2. Mostrar Toast
```javascript
// Success (verde)
showToast('Guardado exitosamente', 'success');

// Error (rojo)
showToast('Error al guardar', 'error');

// Warning (amarillo)
showToast('Advertencia importante', 'warning');

// Info (azul)
showToast('Información relevante', 'info');
```

---

## 📜 Funciones Disponibles

| Función | Uso | Ejemplo |
|---------|-----|---------|
| `showToast(msg, type, duration)` | Mostrar toast | `showToast('Éxito', 'success')` |
| `smoothScrollTop(duration)` | Scroll suave arriba | `smoothScrollTop(500)` |
| `disableButtonWithSpinner(btn, text)` | Deshabilitar botón + spinner | `disableButtonWithSpinner(btnGuardar)` |
| `enableButton(btn)` | Re-habilitar botón | `enableButton(btnGuardar)` |
| `markFieldInvalid(field, msg)` | Marcar campo con error | `markFieldInvalid(input, 'Error')` |
| `clearFieldValidation(field)` | Limpiar error de campo | `clearFieldValidation(input)` |
| `clearFormValidation(form)` | Limpiar errores de form | `clearFormValidation(form)` |

---

## 💾 Ejemplo Completo

```javascript
// 1. Obtener elementos
const form = document.getElementById('miForm');
const btnGuardar = document.getElementById('btnGuardar');

// 2. Submit handler
form.addEventListener('submit', function(e) {
    e.preventDefault();

    // 3. Limpiar errores
    clearFormValidation(form);

    // 4. Validar
    const campo = document.getElementById('miCampo');
    let isValid = true;

    if (campo.value.length < 10) {
        markFieldInvalid(campo, 'Mínimo 10 caracteres');
        isValid = false;
    }

    if (!isValid) {
        showToast('Corrija los errores', 'error');
        smoothScrollTop();
        return;
    }

    // 5. Deshabilitar botón
    disableButtonWithSpinner(btnGuardar, 'Guardando...');

    // 6. Enviar
    fetch('/guardar', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Guardado', 'success');
            smoothScrollTop();
            form.reset();
        } else {
            showToast('❌ Error', 'error');
        }
    })
    .finally(() => {
        // 7. Re-habilitar
        enableButton(btnGuardar);
    });
});
```

---

## 🎨 Tipos de Toast

| Tipo | Color | Icono | Uso |
|------|-------|-------|-----|
| `success` | Verde | ✅ | Operación exitosa |
| `error` | Rojo | ❌ | Error/Fallo |
| `warning` | Amarillo | ⚠️ | Advertencia |
| `info` | Azul | ℹ️ | Información |

---

## 📋 Validaciones Comunes

```javascript
// Mínimo caracteres
if (campo.value.trim().length < 10) {
    markFieldInvalid(campo, 'Mínimo 10 caracteres');
}

// Campo vacío
if (!campo.value) {
    markFieldInvalid(campo, 'Este campo es obligatorio');
}

// Archivo requerido
if (fileInput.files.length === 0) {
    markFieldInvalid(fileInput, 'Debe subir un archivo');
}

// Tamaño archivo (15MB)
const maxSize = 15 * 1024 * 1024;
if (file.size > maxSize) {
    markFieldInvalid(fileInput, 'Archivo excede 15 MB');
}

// Select sin selección
if (!select.value) {
    markFieldInvalid(select, 'Seleccione una opción');
}
```

---

## 🔄 Flujo Típico

```
1. clearFormValidation(form)
   ↓
2. Validar campos
   ↓
3. ¿Errores?
   SÍ → markFieldInvalid() + showToast('error') + smoothScrollTop()
   NO → Continuar
   ↓
4. disableButtonWithSpinner(btn)
   ↓
5. fetch() / AJAX
   ↓
6. Respuesta
   success → showToast('success') + smoothScrollTop() + reset()
   error → showToast('error') + markFieldInvalid()
   ↓
7. enableButton(btn)
```

---

## 🎯 HTML Mínimo

```html
<form id="miForm" method="POST">
    <input type="text" id="miCampo" class="form-control" required>
    <button type="submit" id="btnGuardar" class="btn btn-success">
        Guardar
    </button>
</form>
```

---

## 📦 Archivos

- `app/Views/components/toast.php` - Componente
- `app/Views/ejemplos/wizard_form_example.php` - Ejemplo completo
- `TOAST_VALIDATION_IMPLEMENTATION.md` - Documentación completa

---

## ✅ Checklist

- [ ] Incluir `<?= $this->include('components/toast') ?>`
- [ ] ID en botón submit: `id="btnGuardar"`
- [ ] Listener `form.addEventListener('submit', ...)`
- [ ] `clearFormValidation()` al inicio
- [ ] Validar y `markFieldInvalid()` si error
- [ ] `disableButtonWithSpinner()` antes de fetch
- [ ] `showToast()` según resultado
- [ ] `smoothScrollTop()` después de éxito
- [ ] `enableButton()` en `.finally()`
- [ ] Auto-limpiar: `field.addEventListener('input', ...)`
