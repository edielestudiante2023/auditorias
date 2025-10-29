# Guía de Pruebas Manuales - CRUD de Items Banco

## Archivos Implementados

### Modelo
- `app/Models/ItemsBancoModel.php` - Modelo con validaciones y métodos auxiliares

### Controlador
- `app/Controllers/Admin/ItemsBancoController.php` - CRUD completo con métodos:
  - `index()` - Listar items
  - `crear()` - Formulario de creación
  - `store()` - Guardar nuevo item
  - `editar($id)` - Formulario de edición
  - `update($id)` - Actualizar item
  - `toggle($id)` - Activar/desactivar item
  - `eliminar($id)` - Eliminar item
  - `reordenar()` - Interfaz de reordenamiento
  - `updateOrden()` - Guardar nuevo orden (AJAX)

### Vistas
- `app/Views/admin/items_banco/index.php` - Listado de items
- `app/Views/admin/items_banco/form.php` - Formulario crear/editar
- `app/Views/admin/items_banco/reordenar.php` - Interfaz drag & drop

### Rutas
- `/admin/items` - Listado
- `/admin/items/crear` - Crear
- `/admin/items/editar/{id}` - Editar
- `/admin/items/toggle/{id}` - Activar/desactivar
- `/admin/items/eliminar/{id}` - Eliminar
- `/admin/items/reordenar` - Reordenar

---

## Preparación de Datos de Prueba

### 1. Verificar que existe el seeder

El seeder ya debe estar creado: `app/Database/Seeds/ItemsBancoSeeder.php`

### 2. Ejecutar las migraciones y seeders

```bash
cd c:\xampp\htdocs\auditorias
php spark migrate
php spark db:seed ItemsBancoSeeder
```

Si el seeder no carga datos, puedes insertar manualmente:

```sql
INSERT INTO items_banco (codigo_item, titulo, descripcion, aplica_alto_riesgo, orden, activo) VALUES
('1.1', 'Verificación de EPP', 'Verificar que todo el personal cuenta con equipo de protección personal adecuado', 1, 1, 1),
('1.2', 'Capacitación SST', 'Verificar registros de capacitación en seguridad y salud en el trabajo', 1, 2, 1),
('2.1', 'Orden y limpieza', 'Verificar que las áreas de trabajo estén ordenadas y limpias', 0, 3, 1),
('2.2', 'Señalización', 'Verificar que existe señalización adecuada en todas las áreas', 0, 4, 1);
```

---

## Casos de Prueba

### CASO 1: Acceso al módulo (Protección por rol)

**Objetivo:** Verificar que solo el super_admin puede acceder

**Pasos:**
1. Iniciar sesión como **super_admin**:
   - Email: `superadmin@cycloidtalent.com`
   - Contraseña: `Admin123*`

2. Acceder a: http://localhost/auditorias/public/admin/items

3. **Resultado esperado:**
   - ✅ Se muestra la lista de ítems
   - ✅ Se ven botones "Nuevo Ítem" y "Reordenar"

4. Cerrar sesión y probar con usuario consultor o proveedor

5. **Resultado esperado:**
   - ❌ Redirige a `/login` con mensaje "Acceso denegado"

---

### CASO 2: Listar ítems

**Objetivo:** Verificar que se listan correctamente todos los ítems

**Pasos:**
1. Acceder a: http://localhost/auditorias/public/admin/items

2. **Verificar:**
   - ✅ Se muestra tabla con columnas: Orden, Código, Título, Alto Riesgo, Estado, Acciones
   - ✅ Los ítems están ordenados por el campo `orden`
   - ✅ Se muestran badges para "Alto Riesgo" (rojo) y "Estado" (verde/gris)
   - ✅ Botones de acción: Editar, Activar/Desactivar, Eliminar
   - ✅ Se muestra contador: "Total de ítems: X | Activos: Y"

3. **Si no hay ítems:**
   - ✅ Se muestra mensaje "No hay ítems registrados"
   - ✅ Botón "Crear Primer Ítem"

---

### CASO 3: Crear nuevo ítem

**Objetivo:** Verificar la creación de un ítem nuevo

**Pasos:**
1. Hacer clic en botón "Nuevo Ítem"
2. Llenar el formulario:
   - **Código:** `3.1`
   - **Título:** `Verificación de extintores`
   - **Descripción:** `Verificar que los extintores están vigentes y accesibles`
   - **Orden:** `10`
   - ☑ **Alto Riesgo:** Marcado
   - ☑ **Activo:** Marcado

3. Hacer clic en "Crear Ítem"

4. **Resultado esperado:**
   - ✅ Redirige a `/admin/items`
   - ✅ Mensaje verde: "Ítem creado exitosamente"
   - ✅ El nuevo ítem aparece en la lista

---

### CASO 4: Validaciones al crear

**Objetivo:** Verificar que las validaciones funcionan correctamente

**Prueba 4.1: Campo código vacío**
1. Ir a "Nuevo Ítem"
2. Dejar el campo "Código" vacío
3. Llenar solo "Título": `Test`
4. Enviar formulario

**Resultado esperado:**
- ❌ Error: "El código es obligatorio"

**Prueba 4.2: Código duplicado**
1. Intentar crear ítem con código `1.1` (que ya existe)
2. **Resultado esperado:**
   - ❌ Error: "Este código ya está en uso"

**Prueba 4.3: Título vacío**
1. Llenar código pero dejar título vacío
2. **Resultado esperado:**
   - ❌ Error: "El título es obligatorio"

**Prueba 4.4: Código muy largo**
1. Intentar código con más de 10 caracteres: `12345678901`
2. **Resultado esperado:**
   - ❌ Error: "El código no puede exceder 10 caracteres"

---

### CASO 5: Editar ítem existente

**Objetivo:** Verificar la edición de un ítem

**Pasos:**
1. En la lista, hacer clic en el botón de editar (lápiz) de cualquier ítem
2. Modificar el título a: `Título Modificado - Test`
3. Cambiar orden a: `99`
4. Hacer clic en "Actualizar Ítem"

**Resultado esperado:**
- ✅ Redirige a `/admin/items`
- ✅ Mensaje verde: "Ítem actualizado exitosamente"
- ✅ Los cambios se reflejan en la lista

**Verificar también:**
- ✅ Los campos se cargan con los valores actuales
- ✅ El código no puede duplicarse con otros ítems (excepto el mismo)

---

### CASO 6: Activar/Desactivar ítem

**Objetivo:** Verificar el toggle de estado

**Pasos:**
1. Identificar un ítem con estado "Activo"
2. Hacer clic en el botón con ícono de ojo (botón amarillo/verde)
3. Confirmar en el mensaje de alerta

**Resultado esperado:**
- ✅ Redirige a `/admin/items`
- ✅ Mensaje: "Estado del ítem actualizado"
- ✅ El ítem ahora muestra "Inactivo" con badge gris
- ✅ El botón cambia de ícono (ojo tachado → ojo normal)

**Repetir el proceso:**
4. Hacer clic nuevamente en el mismo ítem
5. **Resultado esperado:**
   - ✅ Vuelve a estado "Activo"

---

### CASO 7: Eliminar ítem

**Objetivo:** Verificar la eliminación de ítems

**Prueba 7.1: Eliminar ítem NO usado**
1. Crear un ítem de prueba temporal
2. Hacer clic en el botón de eliminar (tacho de basura rojo)
3. Confirmar en el mensaje de alerta

**Resultado esperado:**
- ✅ Redirige a `/admin/items`
- ✅ Mensaje: "Ítem eliminado exitosamente"
- ✅ El ítem ya no aparece en la lista

**Prueba 7.2: Eliminar ítem usado en auditorías**
1. Si hay ítems usados en `auditoria_items`, intentar eliminarlos
2. **Resultado esperado:**
   - ❌ Mensaje de error: "No se puede eliminar el ítem porque está siendo usado en auditorías"
   - ✅ El ítem permanece en la lista

---

### CASO 8: Reordenar ítems (Drag & Drop)

**Objetivo:** Verificar la funcionalidad de reordenamiento

**Pasos:**
1. Hacer clic en botón "Reordenar"
2. Acceder a: http://localhost/auditorias/public/admin/items/reordenar
3. **Verificar:**
   - ✅ Se muestra lista de ítems con ícono de grip (☰)
   - ✅ Instrucciones en el sidebar derecho

4. Hacer clic y arrastrar un ítem a otra posición
5. Soltar el ítem
6. **Verificar:**
   - ✅ El ítem se coloca en la nueva posición visualmente

7. Hacer clic en "Guardar Orden"

**Resultado esperado:**
- ✅ Mensaje de JavaScript: "Orden actualizado exitosamente"
- ✅ Redirige a `/admin/items`
- ✅ Los ítems aparecen en el nuevo orden

---

### CASO 9: Orden automático al crear

**Objetivo:** Verificar que el orden se asigna automáticamente si se deja vacío

**Pasos:**
1. Crear nuevo ítem
2. **NO** llenar el campo "Orden" (dejarlo vacío)
3. Guardar

**Resultado esperado:**
- ✅ El ítem se crea exitosamente
- ✅ Se le asigna automáticamente el siguiente número de orden disponible
- ✅ Aparece al final de la lista

---

### CASO 10: Interfaz responsiva

**Objetivo:** Verificar que las vistas se adaptan a diferentes tamaños

**Pasos:**
1. Reducir el ancho del navegador (simular móvil)
2. Acceder a `/admin/items`

**Verificar:**
- ✅ La tabla se hace scrollable horizontalmente
- ✅ La navbar se colapsa en un menú hamburguesa
- ✅ Los botones se adaptan al espacio disponible

---

## Checklist de Verificación General

**Funcionalidad:**
- [ ] Listar ítems ordenados
- [ ] Crear ítem nuevo
- [ ] Editar ítem existente
- [ ] Activar/desactivar ítem
- [ ] Eliminar ítem (con validación de uso)
- [ ] Reordenar ítems con drag & drop
- [ ] Orden automático al crear

**Validaciones:**
- [ ] Código obligatorio y único
- [ ] Título obligatorio
- [ ] Máximo 10 caracteres en código
- [ ] Máximo 255 caracteres en título
- [ ] Descripción opcional (máx 5000 caracteres)
- [ ] Orden numérico mayor o igual a 0

**Seguridad:**
- [ ] Solo super_admin puede acceder
- [ ] CSRF habilitado en todos los formularios
- [ ] Otros roles reciben "Acceso denegado"

**UX/UI:**
- [ ] Mensajes flash informativos (éxito/error)
- [ ] Confirmaciones antes de eliminar/desactivar
- [ ] Breadcrumbs en formularios
- [ ] Sidebar con ayuda en formulario
- [ ] Badges diferenciados por color
- [ ] Iconos Bootstrap Icons
- [ ] Diseño responsivo

**Integración:**
- [ ] Helper `auth` cargado correctamente
- [ ] Funciones `userName()` y `currentRoleName()` funcionan
- [ ] Rutas con `site_url()` funcionan correctamente
- [ ] AJAX para reordenamiento funciona

---

## Troubleshooting

### Error: "ItemsBancoModel not found"
**Solución:**
```bash
composer dump-autoload
```

### Error: "csrf_verify failed"
**Solución:** Verificar que todos los formularios incluyen `<?= csrf_field() ?>`

### No aparecen ítems en la lista
**Solución:** Ejecutar seeder:
```bash
php spark db:seed ItemsBancoSeeder
```

### Drag & Drop no funciona
**Solución:** Verificar que JavaScript está habilitado y consola del navegador no muestra errores

### Error al guardar orden (AJAX)
**Solución:**
- Verificar que la ruta `/admin/items/updateOrden` está configurada
- Verificar en DevTools > Network que la petición se envía correctamente
- Verificar que `Content-Type: application/json`

---

## Próximos Pasos

Con el CRUD de Items Banco completado, el sistema está listo para:
1. Implementar CRUD de Clientes (con upload de logo)
2. Implementar CRUD de Proveedores
3. Implementar CRUD de Consultores (con upload de firma)
4. Implementar CRUD de Contratos
5. Implementar módulo de Auditorías

---

**Fecha de implementación:** 2025-10-14
**Framework:** CodeIgniter 4.6.x
**Módulo:** Banco de Ítems de Auditoría
**Autor:** Sistema de Auditorías - Cycloid Talent SAS
