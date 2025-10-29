# Guía de Pruebas Manuales - CRUD de Consultores

## Archivos Implementados

### Modelo
- `app/Models/ConsultorModel.php` - Modelo con validaciones y métodos:
  - Validación de unicidad de `numero_documento`
  - `getConsultoresWithUsers()` - Lista con info de usuarios
  - `userHasConsultor()` - Verifica si usuario ya tiene consultor
  - `hasAuditorias()` - Verifica si tiene auditorías asignadas

### Controlador
- `app/Controllers/Admin/ConsultoresController.php` - CRUD completo con:
  - Upload de firma usando `UploadService`
  - Validación de imágenes (PNG/JPG)
  - Eliminación de firma antigua al actualizar
  - Prevención de eliminación si tiene auditorías

### Vistas
- `app/Views/admin/consultores/index.php` - Listado con thumbnails
- `app/Views/admin/consultores/form.php` - Formulario con preview de firma

### Rutas
- `/admin/consultores` - Listado
- `/admin/consultores/crear` - Crear
- `/admin/consultores/editar/{id}` - Editar
- `/admin/consultores/eliminar/{id}` - Eliminar
- `/admin/consultores/eliminarFirma/{id}` - Eliminar solo firma

---

## Preparación de Datos de Prueba

### 1. Crear usuarios con rol consultor

Ejecutar en MySQL:

```sql
-- Usuario Consultor 1
INSERT INTO users (email, password_hash, nombre, id_roles, estado, created_at)
VALUES ('juan.consultor@cycloid.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 2, 'activo', NOW());

-- Usuario Consultor 2
INSERT INTO users (email, password_hash, nombre, id_roles, estado, created_at)
VALUES ('maria.auditora@cycloid.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 2, 'activo', NOW());

-- Usuario Consultor 3
INSERT INTO users (email, password_hash, nombre, id_roles, estado, created_at)
VALUES ('carlos.auditor@cycloid.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Rodríguez', 2, 'activo', NOW());
```

**Contraseña para todos:** `password`

### 2. Preparar imágenes de firma

Crea o descarga imágenes PNG/JPG de firmas para las pruebas. Ejemplos:
- `firma_prueba_1.png` (200x100px aprox.)
- `firma_prueba_2.jpg`

---

## Casos de Prueba

### CASO 1: Acceso al módulo (Protección por rol)

**Objetivo:** Verificar que solo super_admin puede acceder

**Pasos:**
1. Iniciar sesión como **super_admin**
2. Acceder a: http://localhost/auditorias/public/admin/consultores

**Resultado esperado:**
- ✅ Se muestra la lista de consultores
- ✅ Botón "Nuevo Consultor" visible
- ✅ Navbar con enlace activo

3. Cerrar sesión e intentar con usuario con otro rol

**Resultado esperado:**
- ❌ Redirige a `/login` con mensaje "Acceso denegado"

---

### CASO 2: Listar consultores

**Objetivo:** Verificar listado correcto

**Pasos:**
1. Acceder a `/admin/consultores`

**Verificar:**
- ✅ Tabla con columnas: Consultor, Documento, Licencia SST, Usuario, Firma, Estado, Acciones
- ✅ Badge con tipo de documento (CC, CE, PAS, OTRO)
- ✅ Indicador de firma (Sí/No con badges)
- ✅ Estado del usuario (Activo/Inactivo)
- ✅ Botones Editar y Eliminar
- ✅ Contador total y activos

**Si no hay consultores:**
- ✅ Mensaje "No hay consultores registrados"
- ✅ Botón "Crear Primer Consultor"

---

### CASO 3: Crear consultor SIN firma

**Objetivo:** Verificar creación básica

**Pasos:**
1. Clic en "Nuevo Consultor"
2. Llenar formulario:
   - **Usuario:** Seleccionar "Juan Pérez"
   - **Nombre completo:** `Juan Carlos Pérez Gómez`
   - **Tipo documento:** CC
   - **Número documento:** `1234567890`
   - **Licencia SST:** `SST-2024-001`
   - **Firma:** NO subir archivo

3. Clic en "Crear Consultor"

**Resultado esperado:**
- ✅ Redirige a `/admin/consultores`
- ✅ Mensaje verde: "Consultor creado exitosamente"
- ✅ Consultor aparece en la lista
- ✅ Columna "Firma" muestra badge "No"

---

### CASO 4: Crear consultor CON firma

**Objetivo:** Verificar upload de firma

**Pasos:**
1. Crear nuevo consultor
2. Llenar datos:
   - **Usuario:** María González
   - **Nombre completo:** `María Teresa González López`
   - **Tipo documento:** CE
   - **Número documento:** `CE-9876543`
   - **Licencia SST:** Dejar vacío
   - **Firma:** Subir `firma_prueba_1.png`

3. Guardar

**Resultado esperado:**
- ✅ Consultor creado exitosamente
- ✅ Columna "Firma" muestra badge verde "Sí"
- ✅ Archivo guardado en `writable/uploads/firmas_consultor/`
- ✅ Nombre del archivo: `consultor_{id}_{timestamp}_firma_prueba_1.png`

**Verificar en base de datos:**
```sql
SELECT id_consultor, nombre_completo, firma_path FROM consultores WHERE numero_documento = 'CE-9876543';
```
- ✅ Campo `firma_path` contiene ruta relativa

---

### CASO 5: Validaciones al crear

**Prueba 5.1: Usuario ya tiene consultor**
1. Intentar crear otro consultor con el mismo usuario de uno existente

**Resultado esperado:**
- ❌ Error: "Este usuario ya tiene un registro de consultor"

**Prueba 5.2: Número de documento duplicado**
1. Intentar crear consultor con número de documento existente

**Resultado esperado:**
- ❌ Error: "Este número de documento ya está registrado"

**Prueba 5.3: Campos obligatorios vacíos**
1. Dejar campos obligatorios vacíos

**Resultado esperado:**
- ❌ Errores de validación por cada campo vacío

**Prueba 5.4: Archivo no es imagen**
1. Intentar subir PDF como firma

**Resultado esperado:**
- ❌ Error: "La firma debe ser una imagen PNG o JPG"

**Prueba 5.5: Imagen muy grande**
1. Intentar subir imagen mayor a 20 MB

**Resultado esperado:**
- ❌ Error: "El archivo excede el tamaño máximo permitido"

---

### CASO 6: Editar consultor SIN cambiar firma

**Objetivo:** Verificar edición de datos

**Pasos:**
1. Hacer clic en "Editar" de un consultor existente
2. Modificar:
   - **Nombre completo:** Agregar segundo apellido
   - **Licencia SST:** Actualizar número

3. NO tocar el campo "Firma"
4. Guardar

**Resultado esperado:**
- ✅ Mensaje: "Consultor actualizado exitosamente"
- ✅ Cambios reflejados en la lista
- ✅ Firma sigue siendo la misma (si existía)

**Verificar:**
- ✅ Campo `id_users` NO se puede cambiar (está disabled)
- ✅ El select muestra el usuario actual

---

### CASO 7: Editar consultor y REEMPLAZAR firma

**Objetivo:** Verificar reemplazo de firma

**Pasos:**
1. Editar consultor que YA tiene firma
2. En el formulario debe aparecer:
   - ✅ Thumbnail de la firma actual
   - ✅ Botón "Eliminar Firma Actual"

3. Subir nueva firma: `firma_prueba_2.jpg`
4. Guardar

**Resultado esperado:**
- ✅ Consultor actualizado exitosamente
- ✅ Firma anterior ELIMINADA del servidor
- ✅ Nueva firma guardada
- ✅ `firma_path` actualizado en BD

**Verificar en filesystem:**
```bash
ls writable/uploads/firmas_consultor/
```
- ✅ Solo existe la nueva firma (la anterior fue eliminada)

---

### CASO 8: Eliminar solo la firma

**Objetivo:** Verificar eliminación de firma sin eliminar consultor

**Pasos:**
1. Editar consultor con firma
2. Hacer clic en botón "Eliminar Firma Actual"
3. Confirmar en el alert

**Resultado esperado:**
- ✅ Mensaje: "Firma eliminada exitosamente"
- ✅ Ya no se muestra thumbnail
- ✅ Campo `firma_path` en BD es `NULL`
- ✅ Archivo eliminado del servidor
- ✅ Consultor sigue existiendo

---

### CASO 9: Eliminar consultor SIN auditorías

**Objetivo:** Verificar eliminación permitida

**Pasos:**
1. Crear consultor de prueba temporal
2. Hacer clic en botón "Eliminar"
3. Confirmar en el alert

**Resultado esperado:**
- ✅ Mensaje: "Consultor eliminado exitosamente"
- ✅ Consultor ya no aparece en la lista
- ✅ Firma eliminada del servidor (si existía)
- ✅ Registro eliminado de BD

---

### CASO 10: Eliminar consultor CON auditorías

**Objetivo:** Verificar prevención de eliminación

**Pasos:**
1. Si tienes un consultor con auditorías asignadas, intentar eliminarlo
2. Si NO tienes auditorías, simula insertando una:

```sql
-- Primero necesitas un proveedor (crear temporalmente)
INSERT INTO proveedores (razon_social, nit, created_at)
VALUES ('Proveedor Test', '900123456', NOW());

-- Crear auditoría
INSERT INTO auditorias (id_proveedor, id_consultor, estado, created_at)
VALUES (1, 1, 'borrador', NOW());
-- Donde id_consultor = 1 es el ID del consultor que quieres proteger
```

3. Intentar eliminar el consultor

**Resultado esperado:**
- ❌ Error: "No se puede eliminar el consultor porque tiene auditorías asignadas"
- ✅ Consultor permanece en la lista

---

### CASO 11: Vista de firma (thumbnail/preview)

**Objetivo:** Verificar visualización de firma

**En el listado:**
- ✅ Badge verde "Sí" si tiene firma
- ✅ Badge gris "No" si no tiene

**En el formulario de edición:**
- ✅ Se muestra thumbnail de la firma
- ✅ Imagen se ve correctamente (base64 embebido)
- ✅ Tamaño máximo de preview: 300x150px
- ✅ Borde punteado alrededor

---

### CASO 12: Select de usuarios

**Objetivo:** Verificar filtrado correcto de usuarios

**Al crear:**
1. Abrir formulario de creación
2. Verificar el select "Usuario"

**Debe mostrar:**
- ✅ Solo usuarios con `id_roles = 2` (consultores)
- ✅ Solo usuarios con `estado = 'activo'`
- ✅ NO debe mostrar usuarios que ya tienen registro de consultor
- ✅ Formato: "Nombre (email)"

**Al editar:**
- ✅ Select está DISABLED (no se puede cambiar)
- ✅ Hidden input mantiene el `id_users` original
- ✅ Mensaje: "El usuario no puede cambiarse una vez creado el consultor"

---

## Checklist de Verificación General

**Funcionalidad:**
- [ ] Listar consultores con datos de usuario
- [ ] Crear consultor sin firma
- [ ] Crear consultor con firma (upload)
- [ ] Editar consultor sin cambiar firma
- [ ] Editar consultor y reemplazar firma
- [ ] Eliminar solo la firma
- [ ] Eliminar consultor (sin auditorías)
- [ ] Prevenir eliminación (con auditorías)

**Validaciones:**
- [ ] Usuario obligatorio y debe existir
- [ ] Nombre completo obligatorio
- [ ] Tipo de documento obligatorio (CC/CE/PAS/OTRO)
- [ ] Número de documento obligatorio y único
- [ ] Licencia SST opcional
- [ ] Firma debe ser PNG o JPG
- [ ] Usuario no puede tener más de un consultor
- [ ] Usuario no puede cambiarse después de crear

**Upload de firma:**
- [ ] Solo acepta imágenes PNG/JPG
- [ ] Guarda en `writable/uploads/firmas_consultor/`
- [ ] Nombre con formato: `consultor_{id}_{timestamp}_{nombre}.ext`
- [ ] Elimina firma anterior al reemplazar
- [ ] Valida MIME real con `finfo`

**UX/UI:**
- [ ] Mensajes flash informativos
- [ ] Confirmación antes de eliminar
- [ ] Thumbnail de firma en formulario de edición
- [ ] Botón para eliminar solo firma
- [ ] Select filtrado de usuarios
- [ ] Select disabled al editar
- [ ] Breadcrumbs
- [ ] Sidebar con ayuda
- [ ] Badges diferenciados
- [ ] Iconos Bootstrap
- [ ] Diseño responsivo

---

## Troubleshooting

### Error: "No se recibió ningún archivo válido"
**Solución:** Verificar que el formulario tiene `enctype="multipart/form-data"`

### Firma no se muestra (imagen rota)
**Solución:**
- Verificar que el archivo existe: `uploadExists($consultor['firma_path'])`
- Verificar permisos de lectura en `writable/uploads/`

### Error: "Call to undefined function uploadExists"
**Solución:** Cargar helper en constructor: `helper('upload')`

### Select de usuarios vacío
**Solución:** Verificar que existen usuarios con:
- `id_roles = 2`
- `estado = 'activo'`
- Que NO tengan registro en `consultores`

### Error al eliminar firma antigua
**Solución:** Verificar que `UploadService->deleteFile()` recibe ruta correcta

---

## Próximos Pasos

Con el CRUD de Consultores completado, el sistema está listo para:
1. Implementar CRUD de **Clientes** (con upload de logo)
2. Implementar CRUD de **Proveedores**
3. Implementar CRUD de **Contratos**
4. Implementar módulo de **Auditorías** usando consultores

---

**Fecha de implementación:** 2025-10-14
**Framework:** CodeIgniter 4.6.x
**Módulo:** Gestión de Consultores
**Autor:** Sistema de Auditorías - Cycloid Talent SAS
