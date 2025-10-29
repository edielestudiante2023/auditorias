# 🧪 Smoke Tests QA - Sistema de Auditorías

## Pruebas Funcionales Mínimas

Este documento define los tests funcionales mínimos que deben pasar antes de considerar el sistema listo para producción.

---

## Pre-requisitos

✅ Completar [PRE_FLIGHT_CHECKLIST.md](PRE_FLIGHT_CHECKLIST.md)
✅ Ejecutar `php pre_flight_check.php` sin errores
✅ Base de datos poblada con `AdminQuickSeed`

---

## 🔐 1. Autenticación y Roles

### Test 1.1: Login Super Admin

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/login` | Formulario de login visible |
| 2 | Ingresar: `superadmin@cycloidtalent.com` / `Admin123*` | Login exitoso |
| 3 | Verificar redirección | Redirige a `/admin/dashboard` |
| 4 | Verificar navbar | Muestra "Super Admin" en dropdown |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 1.2: Login Consultor

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Logout y volver a `/login` | Formulario visible |
| 2 | Ingresar: `consultor1@cycloid.com` / `Consultor123*` | Login exitoso |
| 3 | Verificar redirección | Redirige a `/consultor/dashboard` |
| 4 | Verificar acceso | No puede acceder a `/admin/dashboard` (redirección con "No autorizado") |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 1.3: Login Proveedor

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Logout y volver a `/login` | Formulario visible |
| 2 | Ingresar: `proveedor1@empresa.com` / `Proveedor123*` | Login exitoso |
| 3 | Verificar redirección | Redirige a `/proveedor/dashboard` |
| 4 | Verificar acceso | No puede acceder a `/admin/*` ni `/consultor/*` |

**Estado:** [ ] Pasa [ ] Falla

---

## 👨‍💼 2. Módulo Admin (Rol: Super Admin)

Login como: `superadmin@cycloidtalent.com`

### Test 2.1: Dashboard Admin

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Acceder a `/admin/dashboard` | Dashboard carga sin errores |
| 2 | Verificar contadores | Todos los contadores > 0 (Clientes: 3, Proveedores: 2, etc.) |
| 3 | Verificar cards | 6 cards visibles (Ítems, Clientes, Proveedores, Consultores, Contratos, Usuarios) |
| 4 | Click en card "Clientes" | Redirige a `/admin/clientes` |
| 5 | Verificar card activo | Card "Clientes" tiene borde destacado al volver al dashboard |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 2.2: CRUD Clientes

#### 2.2.1: Listar Clientes

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/admin/clientes` | Tabla con 3 clientes |
| 2 | Verificar breadcrumbs | "Admin > Clientes" visible |
| 3 | Verificar DataTables | Búsqueda y ordenamiento funcionan |
| 4 | Buscar "ABC" | Filtra y muestra "Empresa Demo ABC" |

**Estado:** [ ] Pasa [ ] Falla

#### 2.2.2: Crear Cliente

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Click en "Nuevo" | Redirige a `/admin/clientes/create` |
| 2 | Verificar breadcrumbs | "Admin > Clientes > Crear" |
| 3 | Llenar formulario:<br>- Razón Social: "Cliente QA Test"<br>- NIT: "999888777-6"<br>- Estado: Activo | Formulario válido |
| 4 | Click en "Guardar" | Redirección a `/admin/clientes` |
| 5 | Verificar flash message | Mensaje verde: "Cliente creado exitosamente" |
| 6 | Verificar en tabla | "Cliente QA Test" aparece en la lista |

**Estado:** [ ] Pasa [ ] Falla

#### 2.2.3: Editar Cliente

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Click en botón "Editar" del cliente recién creado | Redirige a `/admin/clientes/{id}/edit` |
| 2 | Verificar breadcrumbs | "Admin > Clientes > Editar" |
| 3 | Verificar formulario | Datos prellenados correctamente |
| 4 | Modificar Razón Social a "Cliente QA Editado" | Campo actualizado |
| 5 | Click en "Guardar" | Redirección a `/admin/clientes` |
| 6 | Verificar flash message | "Cliente actualizado exitosamente" |
| 7 | Verificar cambio | "Cliente QA Editado" en la tabla |

**Estado:** [ ] Pasa [ ] Falla

#### 2.2.4: Eliminar Cliente

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Click en botón "Eliminar" del cliente de prueba | Modal de confirmación aparece |
| 2 | Verificar modal | Muestra nombre del cliente |
| 3 | Confirmar eliminación | Cliente eliminado |
| 4 | Verificar flash message | "Cliente eliminado exitosamente" |
| 5 | Verificar tabla | Cliente ya no aparece |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 2.3: CRUD Proveedores

#### 2.3.1: Listar Proveedores

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/admin/proveedores` | Tabla con 2 proveedores |
| 2 | Verificar breadcrumbs | "Admin > Proveedores" |
| 3 | Verificar estadísticas | Card de resumen con contadores |

**Estado:** [ ] Pasa [ ] Falla

#### 2.3.2: Crear y Editar Proveedor

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Click "Nuevo Proveedor" | Formulario de creación |
| 2 | Llenar:<br>- Razón Social: "Proveedor Test QA"<br>- NIT: "888999000-1" | Datos ingresados |
| 3 | Guardar | Proveedor creado, flash message exitoso |
| 4 | Editar proveedor | Modificar razón social |
| 5 | Guardar cambios | Actualización exitosa |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 2.4: CRUD Consultores

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/admin/consultores` | Lista con 2 consultores |
| 2 | Verificar datos | Nombres, documentos, licencias SST visibles |
| 3 | Crear nuevo consultor (requiere crear usuario primero) | Proceso completo exitoso |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 2.5: CRUD Contratos

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/admin/contratos` | Lista con 2 contratos activos |
| 2 | Verificar relaciones | Muestra nombres de proveedor, cliente y servicio |
| 3 | Ver detalle de contrato | Información completa visible |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 2.6: Gestión Usuarios

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/admin/usuarios` | Lista de usuarios |
| 2 | Verificar roles | Admin, Consultores, Proveedor visibles |
| 3 | Crear usuario nuevo | Formulario funcional |

**Estado:** [ ] Pasa [ ] Falla

---

## 🔍 3. Módulo Consultor (Rol: Consultor)

Login como: `consultor1@cycloid.com`

### Test 3.1: Dashboard Consultor

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Acceder a `/consultor/dashboard` | Dashboard consultor carga |
| 2 | Verificar menú | Opciones de auditorías visibles |
| 3 | Verificar permisos | No puede acceder a `/admin/*` |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 3.2: Flujo Completo - Crear Auditoría (3 Pasos)

#### Paso 1: Crear Auditoría

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a `/consultor/auditorias` | Lista de auditorías |
| 2 | Click "Nueva Auditoría" | Formulario de creación |
| 3 | Seleccionar:<br>- Proveedor: "Proveedor Alpha"<br>- Fecha | Datos válidos |
| 4 | Guardar | Auditoría creada, redirige a paso 2 |

**Estado:** [ ] Pasa [ ] Falla

---

#### Paso 2: Asignar Clientes

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | En pantalla de asignación | Lista de clientes del proveedor visible |
| 2 | Seleccionar clientes A, B y C | Checkboxes marcados |
| 3 | Guardar selección | Clientes asignados, redirige a paso 3 |

**Estado:** [ ] Pasa [ ] Falla

---

#### Paso 3: Enviar Invitación

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Formulario de invitación | Email prellenado del proveedor |
| 2 | Agregar mensaje personalizado | Texto ingresado |
| 3 | Enviar invitación | Email enviado o log registrado |
| 4 | Verificar notificación | Entrada en tabla `notificaciones` |

**Estado:** [ ] Pasa [ ] Falla

**Nota:** Si SendGrid no está configurado, verificar que el log se guarda en BD.

---

### Test 3.3: Revisión de Auditoría

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a detalle de auditoría | `/consultor/auditoria/{id}` |
| 2 | Ver lista de ítems del banco | Ítems según alcance visibles |
| 3 | Calificar ítem global | Seleccionar "Cumple/No cumple", agregar comentario |
| 4 | Guardar calificación | Calificación guardada exitosamente |
| 5 | Calificar ítem por cliente A | Calificación específica guardada |
| 6 | Ver diferenciación | Cliente A tiene calificación distinta a global |

**Estado:** [ ] Pasa [ ] Falla

**✨ Key Feature:** Verifica que el mismo proveedor puede tener calificaciones diferentes para clientes A, B, C (dotación/planilla por proyecto).

---

### Test 3.4: Cerrar Auditoría y Generar PDFs

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | En detalle de auditoría, click "Cerrar Auditoría" | Modal de confirmación |
| 2 | Confirmar cierre | Auditoría marcada como cerrada |
| 3 | Generar PDF por cliente A | PDF descarga correctamente |
| 4 | Verificar contenido PDF | Datos del cliente A, calificaciones, evidencias |
| 5 | Generar PDF global | PDF con resumen de todos los clientes |
| 6 | Generar PDF completo | PDF con toda la información |

**Estado:** [ ] Pasa [ ] Falla

---

## 📦 4. Módulo Proveedor (Rol: Proveedor)

Login como: `proveedor1@empresa.com`

### Test 4.1: Dashboard Proveedor

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Acceder a `/proveedor/dashboard` | Dashboard proveedor carga |
| 2 | Ver auditorías asignadas | Lista de auditorías pendientes |
| 3 | Verificar permisos | No puede acceder a `/admin/*` ni `/consultor/*` |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 4.2: Wizard de Diligenciamiento

#### 4.2.1: Acceso al Wizard

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Click en auditoría asignada | Redirige a wizard `/proveedor/auditoria/{id}` |
| 2 | Ver estructura del wizard | Ítems organizados por alcance |
| 3 | Verificar clientes asignados | Tabs o secciones por cliente A, B, C |

**Estado:** [ ] Pasa [ ] Falla

---

#### 4.2.2: Subir Evidencias Globales

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Seleccionar ítem global | Formulario de evidencia visible |
| 2 | Agregar comentario | Texto ingresado |
| 3 | Subir archivo (imagen/PDF) | Upload exitoso |
| 4 | Verificar ruta | Archivo guardado en `writable/uploads/{nit}/{id_auditoria}/global/{id_item}/` |
| 5 | Ver preview | Archivo visible en interfaz |

**Estado:** [ ] Pasa [ ] Falla

---

#### 4.2.3: Subir Evidencias por Cliente

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Ir a ítem para cliente A | Formulario específico del cliente |
| 2 | Agregar comentario específico | "Dotación para proyecto A" |
| 3 | Subir evidencia específica | Upload exitoso |
| 4 | Verificar ruta | Archivo en `writable/uploads/{nit}/{id_auditoria}/cliente-{id_A}/{id_item}/` |
| 5 | Repetir para clientes B y C | Evidencias separadas por cliente |

**Estado:** [ ] Pasa [ ] Falla

**✨ Key Feature:** Verifica que las evidencias se almacenan separadamente para cada cliente (A, B, C).

---

#### 4.2.4: Finalizar Auditoría

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Completar todos los ítems | Progreso al 100% |
| 2 | Click "Finalizar Auditoría" | Modal de confirmación |
| 3 | Confirmar | Auditoría marcada como completada |
| 4 | Verificar notificación | Email/log enviado al consultor |
| 5 | Estado cambia | Proveedor ya no puede editar |

**Estado:** [ ] Pasa [ ] Falla

---

## 📄 5. Sistema de Archivos y Uploads

### Test 5.1: Estructura de Carpetas

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Navegar a `writable/uploads/` | Carpeta existe |
| 2 | Verificar subcarpeta por proveedor | `writable/uploads/{nit_proveedor}/` |
| 3 | Verificar subcarpeta por auditoría | `writable/uploads/{nit}/{id_auditoria}/` |
| 4 | Verificar carpeta global | `writable/uploads/{nit}/{id}/global/` |
| 5 | Verificar carpeta por cliente | `writable/uploads/{nit}/{id}/cliente-{id_cliente}/` |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 5.2: Validación de Uploads

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Intentar subir archivo > 20MB | Error: "Archivo muy grande" |
| 2 | Intentar subir .exe | Error: "Tipo de archivo no permitido" |
| 3 | Subir .jpg válido | Upload exitoso |
| 4 | Subir .pdf válido | Upload exitoso |
| 5 | Verificar MIME type | UploadService valida correctamente |

**Estado:** [ ] Pasa [ ] Falla

---

## 📧 6. Sistema de Notificaciones (Opcional si SendGrid configurado)

### Test 6.1: Email de Invitación

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Consultor envía invitación | Email enviado a proveedor |
| 2 | Verificar template | Email usa plantilla correcta |
| 3 | Verificar contenido | Link de acceso, datos de auditoría |
| 4 | Verificar log en BD | Entrada en tabla `notificaciones` |

**Estado:** [ ] Pasa [ ] Falla [ ] N/A (SendGrid no configurado)

---

### Test 6.2: Email de Finalización

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Proveedor finaliza auditoría | Email enviado a consultor |
| 2 | Verificar contenido | Notifica que auditoría está lista |
| 3 | Verificar log | Registrado en BD |

**Estado:** [ ] Pasa [ ] Falla [ ] N/A

---

## 📊 7. Generación de PDFs

### Test 7.1: PDF por Cliente

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Consultor selecciona "PDF Cliente A" | PDF descarga |
| 2 | Abrir PDF | Se visualiza correctamente |
| 3 | Verificar datos | Solo información del cliente A |
| 4 | Verificar evidencias | Imágenes de cliente A incluidas |
| 5 | Verificar logo | Logo del cliente A si existe |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 7.2: PDF Global

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Generar PDF global | PDF con resumen |
| 2 | Verificar contenido | Datos de todos los clientes A, B, C |
| 3 | Verificar gráficos | Estadísticas globales |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 7.3: PDF Completo

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Generar PDF completo | PDF extenso descarga |
| 2 | Verificar secciones | Global + por cada cliente |
| 3 | Verificar anexos | Todas las evidencias |

**Estado:** [ ] Pasa [ ] Falla

---

## 🔒 8. Seguridad y Permisos

### Test 8.1: Control de Acceso por Rol

| Usuario | URL Prohibida | Resultado Esperado |
|---------|---------------|-------------------|
| Consultor | `/admin/dashboard` | Redirección con flash "No autorizado" |
| Proveedor | `/admin/clientes` | Redirección con flash "No autorizado" |
| Proveedor | `/consultor/auditorias` | Redirección con flash "No autorizado" |
| Sin login | `/admin/dashboard` | Redirección a `/login` |

**Estado:** [ ] Pasa [ ] Falla

---

### Test 8.2: Aislamiento de Datos

| Paso | Acción | Resultado Esperado |
|------|--------|-------------------|
| 1 | Login como Proveedor 1 | Dashboard proveedor |
| 2 | Ver auditorías | Solo auditorías asignadas a Proveedor 1 |
| 3 | Intentar acceder a auditoría de otro proveedor | Error 403 o redirección |

**Estado:** [ ] Pasa [ ] Falla

---

## 📝 Resumen de Pruebas

| Categoría | Tests | Pasados | Fallados |
|-----------|-------|---------|----------|
| Autenticación | 3 | | |
| Admin - Clientes | 4 | | |
| Admin - Otros Módulos | 4 | | |
| Consultor - Flujo Completo | 4 | | |
| Proveedor - Wizard | 4 | | |
| Uploads y Archivos | 2 | | |
| Notificaciones | 2 | | |
| PDFs | 3 | | |
| Seguridad | 2 | | |
| **TOTAL** | **28** | | |

---

## ✅ Criterios de Aprobación

Para considerar el sistema listo para producción:

- ✅ **Mínimo 90% de tests pasados** (25/28)
- ✅ **0 fallos críticos** (autenticación, permisos)
- ✅ **Flujo completo funcional** (Admin → Consultor → Proveedor)
- ✅ **Evidencias se guardan correctamente** por cliente
- ✅ **PDFs generan sin errores**

---

## 🐛 Reporte de Bugs

Si encuentras fallos, documenta:

1. **Test que falló:** (ej: Test 3.2 - Crear Auditoría)
2. **Pasos para reproducir:**
3. **Resultado esperado:**
4. **Resultado actual:**
5. **Screenshots/Logs:**
6. **Navegador/Entorno:**

---

**Fecha de última actualización:** 2025-01-XX
**Versión del sistema:** 1.0
