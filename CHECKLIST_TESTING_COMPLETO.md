# ✅ Checklist de Testing Completo - Sistema de Auditorías

## 📋 Información General

**Versión**: 1.0
**Fecha**: 2025-10-16
**Ambiente**: Desarrollo/Producción
**Framework**: CodeIgniter 4

---

## 👥 Usuarios Demo

### Credenciales de Acceso

| Rol | Email | Password | ID Usuario |
|-----|-------|----------|------------|
| **Super Admin** | admin@sistema.com | password123 | 1 |
| **Consultor** | consultor@sistema.com | password123 | 2 |
| **Proveedor** | proveedor@sistema.com | password123 | 3 |

**Nota**: Estos usuarios deben ser creados mediante el seeder `UsersSeeder.php`

---

## 🔧 Preparación del Ambiente

### Pre-requisitos

```bash
# 1. Clonar repositorio
git clone <repo-url>
cd auditorias

# 2. Instalar dependencias
composer install

# 3. Configurar base de datos
cp env .env
# Editar .env con credenciales de BD

# 4. Ejecutar migraciones
php spark migrate

# 5. Ejecutar seeders
php spark db:seed MasterSeeder

# 6. Iniciar servidor
php spark serve
```

---

## 1️⃣ Configuración Base (baseURL por .env)

### ✅ Checklist

- [ ] **1.1 Archivo `.env` existe en raíz del proyecto**
  - Ubicación: `c:\xampp\htdocs\auditorias\.env`
  - Si no existe, copiar desde `env`

- [ ] **1.2 Variable `app.baseURL` está configurada**
  ```env
  app.baseURL = 'http://localhost:8080/'
  ```
  - ⚠️ Debe terminar en `/`
  - Para producción: `https://tudominio.com/`

- [ ] **1.3 Archivo `app/Config/App.php` usa variable de entorno**
  ```php
  public string $baseURL = 'http://localhost:8080/';
  ```
  - Verificar que lee de `.env`

- [ ] **1.4 Probar acceso a URL base**
  - URL: `http://localhost:8080/`
  - **Resultado esperado**: Página de login o home
  - **Error común**: 404 → revisar baseURL

- [ ] **1.5 Assets (CSS/JS) cargan correctamente**
  - Inspeccionar en navegador (F12)
  - Verificar que no hay errores 404 en assets
  - Rutas deben ser: `http://localhost:8080/assets/...`

### 📊 Resultado Esperado

```
✅ baseURL configurado correctamente
✅ Aplicación accesible en http://localhost:8080/
✅ Assets cargan sin errores
✅ No hay warnings de configuración
```

---

## 2️⃣ Acceso como ADMIN - Gestión de Datos Base

### 👤 Login como Admin

- [ ] **2.1 Acceder a página de login**
  - URL: `http://localhost:8080/login`
  - Email: `admin@sistema.com`
  - Password: `password123`

- [ ] **2.2 Verificar redirección**
  - **Resultado esperado**: Dashboard de Admin
  - URL esperada: `http://localhost:8080/admin/dashboard`

### 📦 Creación de Proveedor

- [ ] **2.3 Acceder a módulo de Proveedores**
  - URL: `http://localhost:8080/admin/proveedores`
  - **Resultado esperado**: Lista de proveedores (puede estar vacía)

- [ ] **2.4 Crear nuevo proveedor**
  - Click en "Nuevo Proveedor"
  - **Datos de prueba**:
    ```
    Razón Social: Proveedor Test S.A.S.
    NIT: 900123456-7
    Dirección: Calle 123 #45-67
    Teléfono: 3001234567
    Email: contacto@proveedortest.com
    Usuario (para acceso):
      - Nombre: Usuario Proveedor
      - Email: usuario.proveedor@test.com
      - Password: password123
    ```

- [ ] **2.5 Verificar creación exitosa**
  - **Toast esperado**: "✅ Proveedor creado exitosamente"
  - Scroll automático al tope
  - Redirección a lista de proveedores
  - Proveedor aparece en tabla con estado "Activo"

- [ ] **2.6 Subir logo del proveedor (opcional)**
  - Editar proveedor
  - Subir imagen JPG/PNG (máx 2MB)
  - **Resultado esperado**: Logo visible en miniatura

### 👥 Creación de Clientes

- [ ] **2.7 Acceder a módulo de Clientes**
  - URL: `http://localhost:8080/admin/clientes`

- [ ] **2.8 Crear Cliente #1**
  - **Datos de prueba**:
    ```
    Razón Social: Cliente ABC Ltda.
    NIT: 800111222-3
    Dirección: Av. Principal #10-20
    Teléfono: 3109876543
    Email: contacto@clienteabc.com
    ```
  - **Resultado esperado**: Toast success + cliente en lista

- [ ] **2.9 Crear Cliente #2**
  - **Datos de prueba**:
    ```
    Razón Social: Cliente XYZ S.A.
    NIT: 800333444-5
    Dirección: Carrera 50 #30-40
    Teléfono: 3207654321
    Email: info@clientexyz.com
    ```

- [ ] **2.10 Crear Cliente #3**
  - **Datos de prueba**:
    ```
    Razón Social: Cliente 123 E.U.
    NIT: 800555666-7
    Dirección: Calle 80 #15-25
    Teléfono: 3156543210
    Email: admin@cliente123.com
    ```

### 📄 Creación de Contratos (Activos)

- [ ] **2.11 Acceder a módulo de Contratos**
  - URL: `http://localhost:8080/admin/contratos`

- [ ] **2.12 Crear Contrato #1 (Proveedor → Cliente ABC)**
  - **Datos de prueba**:
    ```
    Proveedor: Proveedor Test S.A.S.
    Cliente: Cliente ABC Ltda.
    Servicio: Auditoría de Seguridad
    Número Contrato: CONT-2025-001
    Fecha Inicio: 2025-01-01
    Fecha Fin: 2025-12-31
    Valor: $5,000,000
    Estado: Activo ✅
    ```
  - **Resultado esperado**: Toast success + contrato visible

- [ ] **2.13 Crear Contrato #2 (Proveedor → Cliente XYZ)**
  - **Datos de prueba**:
    ```
    Proveedor: Proveedor Test S.A.S.
    Cliente: Cliente XYZ S.A.
    Servicio: Auditoría de Seguridad
    Número Contrato: CONT-2025-002
    Fecha Inicio: 2025-01-01
    Fecha Fin: 2025-12-31
    Valor: $7,500,000
    Estado: Activo ✅
    ```

- [ ] **2.14 Crear Contrato #3 (Proveedor → Cliente 123)**
  - **Datos de prueba**:
    ```
    Proveedor: Proveedor Test S.A.S.
    Cliente: Cliente 123 E.U.
    Servicio: Auditoría de Seguridad
    Número Contrato: CONT-2025-003
    Fecha Inicio: 2025-01-01
    Fecha Fin: 2025-12-31
    Valor: $3,000,000
    Estado: Activo ✅
    ```

- [ ] **2.15 Verificar contratos activos**
  - Filtrar por estado "Activo"
  - **Resultado esperado**: 3 contratos activos visibles
  - Verificar que cada contrato muestra:
    - Proveedor, Cliente, Servicio
    - Fechas de vigencia
    - Valor del contrato
    - Badge verde "Activo"

### 📊 Resultado Esperado Sección 2

```
✅ 1 Proveedor creado: Proveedor Test S.A.S. (NIT 900123456-7)
✅ 3 Clientes creados: ABC, XYZ, 123
✅ 3 Contratos activos vinculando proveedor con cada cliente
✅ Todos con estado "Activo"
✅ Toasts de éxito mostrados en cada operación
```

---

## 3️⃣ Acceso como CONSULTOR - Creación de Auditoría

### 👤 Cerrar sesión y Login como Consultor

- [ ] **3.1 Logout de Admin**
  - Click en "Cerrar Sesión"

- [ ] **3.2 Login como Consultor**
  - Email: `consultor@sistema.com`
  - Password: `password123`
  - **Resultado esperado**: Dashboard de Consultor

### 📋 Creación de Auditoría

- [ ] **3.3 Acceder a módulo de Auditorías**
  - URL: `http://localhost:8080/consultor/auditorias`
  - **Resultado esperado**: Lista de auditorías (vacía inicialmente)

- [ ] **3.4 Click en "Nueva Auditoría"**
  - Botón: `<i class="bi bi-plus-circle"></i> Nueva Auditoría`

- [ ] **3.5 Formulario de creación - Seleccionar Proveedor**
  - **Proveedor**: Proveedor Test S.A.S.
  - Al seleccionar proveedor, debe cargar clientes asociados

- [ ] **3.6 Verificar carga dinámica de clientes**
  - **Resultado esperado**: Aparecen 3 clientes en lista de selección:
    - ☐ Cliente ABC Ltda. (NIT: 800111222-3)
    - ☐ Cliente XYZ S.A. (NIT: 800333444-5)
    - ☐ Cliente 123 E.U. (NIT: 800555666-7)
  - Todos provienen de contratos activos

- [ ] **3.7 Selección múltiple de clientes**
  - Marcar los 3 checkboxes
  - **Componente esperado**: Checkboxes o Select múltiple
  - Visual: Checkboxes marcados ✅

- [ ] **3.8 Datos de la auditoría**
  - **Datos de prueba**:
    ```
    Código Formato: AUD-SEC-001
    Versión Formato: 1.0
    Fecha Inicio: 2025-01-15
    Fecha Fin: 2025-02-15
    Descripción: Auditoría de seguridad trimestral Q1 2025
    Estado: En Proceso
    ```

- [ ] **3.9 Seleccionar ítems del banco**
  - Ir a sección "Ítems a Evaluar"
  - **Seleccionar ítems**:
    - ☑️ Ítem Global #1: "Verificación de documentación"
    - ☑️ Ítem Global #2: "Revisión de políticas de seguridad"
    - ☑️ Ítem por Cliente #1: "Evaluación de infraestructura específica"
    - ☑️ Ítem por Cliente #2: "Análisis de riesgos particulares"
  - **Total**: 2 globales + 2 por cliente = 4 ítems

- [ ] **3.10 Guardar auditoría**
  - Click en "Crear Auditoría"
  - **Resultado esperado**:
    - Toast: "✅ Auditoría creada exitosamente"
    - Scroll al tope
    - Redirección a detalle de auditoría

- [ ] **3.11 Verificar auditoría creada**
  - **Vista de detalle debe mostrar**:
    ```
    Código: AUD-SEC-001 v1.0
    Estado: En Proceso (badge amarillo)
    Proveedor: Proveedor Test S.A.S.
    Clientes asignados: 3
      - Cliente ABC Ltda.
      - Cliente XYZ S.A.
      - Cliente 123 E.U.
    Ítems a evaluar: 4
      - 2 Globales
      - 2 Por Cliente (x3 clientes = 6 evaluaciones)
    Progreso: 0%
    ```

- [ ] **3.12 Verificar estructura de ítems**
  - **Ítems Globales (2)**:
    - Aplican al proveedor en general
    - 1 comentario por ítem
    - 1 calificación por ítem
  - **Ítems por Cliente (2 x 3 = 6)**:
    - Cliente ABC: 2 ítems específicos
    - Cliente XYZ: 2 ítems específicos
    - Cliente 123: 2 ítems específicos
    - Cada uno con comentario y calificación independiente

### 📊 Resultado Esperado Sección 3

```
✅ Auditoría creada: AUD-SEC-001 v1.0
✅ Proveedor asignado: Proveedor Test S.A.S.
✅ 3 Clientes asignados (desde contratos activos)
✅ 4 Ítems configurados (2 globales + 2 por cliente)
✅ Estado: En Proceso
✅ Progreso inicial: 0%
✅ Consultor puede ver detalle completo
```

---

## 4️⃣ Acceso como PROVEEDOR - Diligenciamiento de Auditoría

### 👤 Cerrar sesión y Login como Proveedor

- [ ] **4.1 Logout de Consultor**

- [ ] **4.2 Login como Proveedor**
  - Email: `usuario.proveedor@test.com`
  - Password: `password123`
  - **Resultado esperado**: Dashboard de Proveedor

### 🎯 Acceder al Wizard de Auditoría

- [ ] **4.3 Ver auditorías asignadas**
  - URL: `http://localhost:8080/proveedor/auditorias`
  - **Resultado esperado**:
    - Lista con 1 auditoría: AUD-SEC-001
    - Estado: En Proceso
    - Progreso: 0%
    - Botón: "Diligenciar"

- [ ] **4.4 Click en "Diligenciar"**
  - URL esperada: `http://localhost:8080/proveedor/auditoria/wizard/{id}`
  - **Resultado esperado**: Wizard con tabs

### 📑 Verificar Estructura del Wizard

- [ ] **4.5 Tabs visibles**
  - **Estructura esperada**:
    ```
    [ Globales ] [ Cliente ABC ] [ Cliente XYZ ] [ Cliente 123 ]
    ```
  - 4 tabs en total
  - Tab "Globales" activo por defecto

- [ ] **4.6 Header del wizard muestra progreso**
  - **Elementos visibles**:
    ```
    Auditoría: AUD-SEC-001 v1.0
    Proveedor: Proveedor Test S.A.S.
    Progreso General: [========>        ] 0%
    ```
  - Barra de progreso con color dinámico

### 📝 Diligenciar Tab "Globales"

- [ ] **4.7 Tab Globales - Ítem #1**
  - **Campos visibles**:
    ```
    Título: Verificación de documentación
    Descripción: [descripción del ítem]
    Comentario Proveedor: [textarea vacío]
    Evidencias: [input file multiple]
    ```

- [ ] **4.8 Llenar Ítem Global #1**
  - **Comentario**:
    ```
    Se realizó la verificación completa de toda la documentación
    requerida según normativa vigente. Todos los documentos están
    actualizados y firmados correctamente.
    ```
  - **Evidencias**: Subir 2 archivos
    - `verificacion_docs.pdf` (PDF de prueba)
    - `evidencia_firma.jpg` (Imagen de prueba)
  - Click en "Guardar"

- [ ] **4.9 Verificar guardado Ítem Global #1**
  - **Toast esperado**: "✅ Ítem global guardado exitosamente"
  - Scroll al tope
  - Spinner en botón durante carga
  - Botón re-habilitado al terminar
  - Progreso actualizado: 25% (1 de 4 ítems globales/totales)

- [ ] **4.10 Llenar Ítem Global #2**
  - **Comentario**:
    ```
    Revisión exhaustiva de políticas de seguridad implementadas.
    Se identifican buenas prácticas y cumplimiento de estándares
    internacionales ISO 27001.
    ```
  - **Evidencias**:
    - `politicas_seguridad.pdf`
    - `checklist_iso27001.xlsx`
  - Click en "Guardar"
  - **Toast esperado**: "✅ Ítem global guardado exitosamente"
  - Progreso: 50%

### 📋 Diligenciar Tab "Cliente ABC"

- [ ] **4.11 Cambiar a tab "Cliente ABC"**
  - Click en tab
  - **Resultado esperado**:
    - Tab activo visualmente
    - Muestra 2 ítems específicos para Cliente ABC
    - Header muestra: "Cliente: Cliente ABC Ltda. (NIT: 800111222-3)"

- [ ] **4.12 Cliente ABC - Ítem por Cliente #1**
  - **Comentario**:
    ```
    Evaluación de infraestructura de Cliente ABC completada.
    Servidores actualizados, red segmentada correctamente,
    firewall configurado según mejores prácticas.
    ```
  - **Evidencias**:
    - `infraestructura_abc.pdf`
    - `diagrama_red_abc.png`
  - Guardar
  - **Toast**: "✅ Cliente guardado exitosamente"
  - Scroll al tope automático
  - Progreso: 62.5% (2.5 de 4)

- [ ] **4.13 Cliente ABC - Ítem por Cliente #2**
  - **Comentario**:
    ```
    Análisis de riesgos específicos de Cliente ABC realizado.
    Riesgos críticos mitigados, plan de contingencia actualizado.
    ```
  - **Evidencias**:
    - `analisis_riesgos_abc.docx`
  - Guardar
  - Progreso: 75%

### 📋 Diligenciar Tab "Cliente XYZ"

- [ ] **4.14 Cambiar a tab "Cliente XYZ"**
  - Click en tab
  - Header: "Cliente: Cliente XYZ S.A."

- [ ] **4.15 Cliente XYZ - Ítem #1**
  - **Comentario**:
    ```
    Infraestructura de Cliente XYZ evaluada satisfactoriamente.
    Cumple con todos los requisitos establecidos.
    ```
  - **Evidencias**: `infra_xyz.pdf`, `fotos_datacenter_xyz.jpg`
  - Guardar → Toast success → Progreso: 83%

- [ ] **4.16 Cliente XYZ - Ítem #2**
  - **Comentario**: "Análisis de riesgos XYZ completado. Todo conforme."
  - **Evidencias**: `riesgos_xyz.pdf`
  - Guardar → Progreso: 91%

### 📋 Diligenciar Tab "Cliente 123"

- [ ] **4.17 Cambiar a tab "Cliente 123"**

- [ ] **4.18 Cliente 123 - Ítem #1**
  - **Comentario**: "Evaluación infraestructura Cliente 123 OK."
  - **Evidencias**: `infra_123.pdf`
  - Guardar → Progreso: 96%

- [ ] **4.19 Cliente 123 - Ítem #2**
  - **Comentario**: "Riesgos Cliente 123 identificados y controlados."
  - **Evidencias**: `riesgos_123.pdf`
  - Guardar → Progreso: **100%** ✅

### 🚫 Validación: No permite finalizar sin 100%

- [ ] **4.20 Intentar finalizar con progreso < 100%**
  - Eliminar comentario de último ítem (progreso vuelve a 96%)
  - Buscar botón "Finalizar Auditoría"
  - **Resultado esperado**:
    - Botón "Finalizar" deshabilitado (disabled)
    - Color gris: `btn-secondary disabled`
    - Tooltip: "Complete todos los ítems para finalizar"
    - Click no hace nada

- [ ] **4.21 Completar ítem faltante**
  - Volver a llenar último ítem
  - Guardar
  - Progreso: 100%

- [ ] **4.22 Botón Finalizar se habilita**
  - **Resultado esperado**:
    - Botón cambia a: `btn-success` (verde)
    - Texto: "✅ Finalizar Auditoría"
    - Botón habilitado (clickable)

### ✅ Finalizar Auditoría

- [ ] **4.23 Click en "Finalizar Auditoría"**
  - Modal de confirmación aparece:
    ```
    ¿Está seguro de finalizar la auditoría?
    Esta acción notificará al consultor que la auditoría
    está lista para revisión.

    [Cancelar] [Sí, Finalizar]
    ```

- [ ] **4.24 Confirmar finalización**
  - Click en "Sí, Finalizar"
  - **Resultado esperado**:
    - Spinner en botón: "Finalizando..."
    - Toast: "✅ Auditoría finalizada exitosamente"
    - Redirección a lista de auditorías
    - Estado cambia a: "Diligenciada" (badge azul)
    - Progreso: 100% ✅

### 📊 Resultado Esperado Sección 4

```
✅ Wizard con 4 tabs (Globales + 3 Clientes)
✅ 2 Ítems globales diligenciados con comentarios y evidencias
✅ 6 Ítems por cliente diligenciados (2 x 3 clientes)
✅ Total: 8 ítems completados
✅ Progreso: 100%
✅ Botón finalizar deshabilitado hasta 100%
✅ Auditoría finalizada exitosamente
✅ Toast success + scroll automático en cada guardado
✅ Estado: Diligenciada
```

---

## 5️⃣ Finalización y Descarga de PDFs

### 👤 Login como Consultor

- [ ] **5.1 Logout de Proveedor y login como Consultor**
  - Email: `consultor@sistema.com`
  - Password: `password123`

### 📧 Verificar Envío de Correo

- [ ] **5.2 Revisar bandeja de entrada (o logs)**
  - **Destinatario**: Email del consultor
  - **Asunto**: "Auditoría AUD-SEC-001 Finalizada"
  - **Contenido esperado**:
    ```
    Estimado Consultor,

    La auditoría AUD-SEC-001 v1.0 del proveedor "Proveedor Test S.A.S."
    ha sido finalizada y está lista para revisión.

    Detalles:
    - Código: AUD-SEC-001
    - Proveedor: Proveedor Test S.A.S.
    - Clientes evaluados: 3
    - Progreso: 100%
    - Estado: Diligenciada

    Acceda al sistema para revisar y cerrar la auditoría.
    ```

- [ ] **5.3 Verificar logs de email**
  - Si email no está configurado, verificar en:
    - `writable/logs/log-{fecha}.php`
    - Buscar: `Email sent to consultor@sistema.com`

### 🔍 Revisar Auditoría como Consultor

- [ ] **5.4 Acceder a auditorías**
  - URL: `http://localhost:8080/consultor/auditorias`
  - **Resultado esperado**:
    - Lista muestra auditoría AUD-SEC-001
    - Estado: "Diligenciada" (badge azul)
    - Progreso: 100%
    - Botón: "Revisar"

- [ ] **5.5 Click en "Revisar"**
  - URL: `http://localhost:8080/consultor/auditoria/{id}`
  - **Vista de revisión muestra**:
    - Todos los ítems diligenciados
    - Comentarios del proveedor
    - Evidencias subidas (con iconos)
    - Campos para calificación del consultor

### ✍️ Calificar Auditoría

- [ ] **5.6 Calificar Ítems Globales**
  - Para cada ítem global:
    - Leer comentario del proveedor
    - Seleccionar calificación:
      - ☑️ Cumple / ⚠️ Parcial / ❌ No Cumple / ➖ No Aplica
    - Agregar comentario consultor (opcional)
    - Guardar

- [ ] **5.7 Calificar Ítems por Cliente**
  - Para cada cliente (ABC, XYZ, 123):
    - Revisar ítems específicos
    - Calificar cada uno
    - Agregar observaciones

### 🔒 Cerrar Auditoría

- [ ] **5.8 Click en "Cerrar Auditoría"**
  - Botón: `<i class="bi bi-check-circle"></i> Cerrar Auditoría`
  - **Validación**:
    - Verifica que todos los ítems estén calificados
    - Si faltan calificaciones → Toast error

- [ ] **5.9 Confirmar cierre**
  - Modal de confirmación:
    ```
    ¿Está seguro de cerrar la auditoría?

    Esta acción:
    - Calculará el porcentaje global de cumplimiento
    - Generará PDFs (global + por cada cliente)
    - Cambiará el estado a "Cerrada"
    - Enviará notificación al proveedor

    [Cancelar] [Sí, Cerrar]
    ```

- [ ] **5.10 Procesamiento de cierre**
  - Click en "Sí, Cerrar"
  - **Spinner**: "Cerrando auditoría..."
  - **Proceso backend**:
    1. Calcular porcentaje global (solo ítems globales)
    2. Calcular porcentaje por cada cliente (ítems cliente + globales)
    3. Generar PDF global
    4. Generar 3 PDFs (uno por cliente)
    5. Cambiar estado a "Cerrada"
    6. Enviar email

- [ ] **5.11 Verificar cierre exitoso**
  - **Toast**: "✅ Auditoría cerrada. Porcentaje global: 87.5%. Se generaron 3 PDF(s)."
  - Redirección a lista de auditorías
  - Estado: "Cerrada" (badge verde)
  - Porcentaje global visible

### 📄 Descarga de PDFs

- [ ] **5.12 Acceder a detalle de auditoría cerrada**
  - Click en auditoría cerrada
  - **Sección de PDFs visible**:
    ```
    📄 PDFs Generados

    [📊 Descargar PDF Global]

    PDFs por Cliente:
    [📄 Cliente ABC Ltda. - Descargar]
    [📄 Cliente XYZ S.A. - Descargar]
    [📄 Cliente 123 E.U. - Descargar]
    ```

- [ ] **5.13 Descargar PDF Global**
  - Click en "Descargar PDF Global"
  - URL: `http://localhost:8080/consultor/auditoria/{id}/pdf-global`
  - **Archivo descargado**: `auditoria_AUD-SEC-001_global.pdf`
  - **Contenido esperado**:
    ```
    Header:
    - Logo proveedor (si existe)
    - Datos proveedor (razón social, NIT)
    - Código auditoría: AUD-SEC-001 v1.0
    - Fecha generación: 16/10/2025

    Información General:
    - Proveedor: Proveedor Test S.A.S.
    - NIT: 900123456-7
    - Periodo: 15/01/2025 - 15/02/2025
    - Consultor: [nombre]

    Ítems Evaluados (Solo Globales):

    1. Verificación de documentación
       Estado: ✅ Cumple
       Comentario Proveedor: [texto]
       Comentario Consultor: [texto]
       Evidencias:
         - verificacion_docs.pdf
         - evidencia_firma.jpg

    2. Revisión de políticas de seguridad
       Estado: ✅ Cumple
       [...]

    Resultado:
    Porcentaje Global de Cumplimiento: 87.5%

    Footer:
    Página 1 de 2
    ```

- [ ] **5.14 Descargar PDF Cliente ABC**
  - Click en "Cliente ABC Ltda. - Descargar"
  - **Archivo**: `auditoria_AUD-SEC-001_cliente_ABC.pdf`
  - **Contenido esperado**:
    ```
    Header:
    - Logo proveedor + Logo cliente (si existen)
    - Cliente: Cliente ABC Ltda. (NIT: 800111222-3)

    Ítems Globales (aplican al cliente):
    1. Verificación de documentación - ✅ Cumple
    2. Revisión de políticas - ✅ Cumple

    Ítems Específicos del Cliente:
    3. Evaluación de infraestructura - ✅ Cumple
       Comentario: [texto específico ABC]
       Evidencias: infraestructura_abc.pdf, diagrama_red_abc.png

    4. Análisis de riesgos - ✅ Cumple
       [...]

    Resultado Cliente ABC:
    Porcentaje de Cumplimiento: 90.5%
    ```

- [ ] **5.15 Descargar PDF Cliente XYZ**
  - Verificar estructura similar a ABC
  - Evidencias específicas de XYZ
  - Porcentaje específico de XYZ

- [ ] **5.16 Descargar PDF Cliente 123**
  - Verificar estructura
  - Evidencias específicas
  - Porcentaje específico

### 📧 Verificar Email de Cierre

- [ ] **5.17 Revisar email enviado al proveedor**
  - **Destinatario**: Email del proveedor
  - **Asunto**: "Auditoría AUD-SEC-001 Cerrada - Resultados"
  - **Contenido**:
    ```
    Estimado Proveedor Test S.A.S.,

    La auditoría AUD-SEC-001 ha sido cerrada por el consultor.

    Resultados:
    - Porcentaje Global: 87.5%
    - Clientes evaluados: 3
      · Cliente ABC Ltda.: 90.5%
      · Cliente XYZ S.A.: 88.0%
      · Cliente 123 E.U.: 85.0%

    PDFs generados y disponibles en el sistema.

    Gracias por su colaboración.
    ```

### 📊 Resultado Esperado Sección 5

```
✅ Email enviado al consultor al finalizar (proveedor)
✅ Consultor puede revisar y calificar todos los ítems
✅ Cierre de auditoría exitoso
✅ Porcentaje global calculado: 87.5%
✅ Porcentajes por cliente calculados
✅ 4 PDFs generados:
   - 1 Global
   - 3 Por cliente (ABC, XYZ, 123)
✅ Todos los PDFs descargables
✅ Contenido de PDFs correcto (headers, logos, evidencias)
✅ Email de cierre enviado al proveedor
✅ Estado: Cerrada
```

---

## 6️⃣ Revisión de Bitácora

### 📜 Acceder a Bitácora

- [ ] **6.1 Login como Consultor (si no está logueado)**

- [ ] **6.2 Acceder a detalle de auditoría**
  - URL: `http://localhost:8080/consultor/auditoria/{id}`

- [ ] **6.3 Click en "Ver Bitácora"**
  - Botón: `<i class="bi bi-journal-text"></i> Ver Bitácora`
  - URL: `http://localhost:8080/consultor/auditoria/bitacora/{id}`

### 📊 Verificar Estadísticas de Bitácora

- [ ] **6.4 Header de estadísticas visible**
  - **Elementos esperados**:
    ```
    Resumen de Actividad

    Acciones Totales: 20

    Acciones por Tipo:
    - Comentario global guardado: 2
    - Comentario cliente guardado: 6
    - Evidencia global subida: 4
    - Evidencia cliente subida: 12
    - Auditoría cerrada: 1
    ```

- [ ] **6.5 Verificar desglose visual**
  - Iconos por tipo de acción:
    - 💬 Comentarios (info)
    - 📤 Evidencias (success)
    - ✅ Cierre (primary)

### 📋 Tabla de Bitácora

- [ ] **6.6 Columnas de la tabla**
  - **Estructura esperada**:
    ```
    | Fecha/Hora | Usuario | Acción | Detalles |
    ```

- [ ] **6.7 Verificar registro de comentarios globales**
  - **Ejemplo de fila**:
    ```
    15/01/2025 10:30 | Usuario Proveedor | 💬 Comentario Global |
    Verificación de documentación
    ```
  - Badge: `bg-info` con icono `bi-chat-left-text`

- [ ] **6.8 Verificar registro de evidencias**
  - **Ejemplo de fila**:
    ```
    15/01/2025 10:32 | Usuario Proveedor | 📤 Evidencia Global |
    📄 verificacion_docs.pdf (1.23 MB)
    ```
  - Badge: `bg-success` con icono `bi-upload`

- [ ] **6.9 Verificar registro de comentarios por cliente**
  - **Ejemplo**:
    ```
    15/01/2025 11:15 | Usuario Proveedor | 💬 Comentario Cliente |
    Evaluación de infraestructura - Cliente: Cliente ABC Ltda.
    ```

- [ ] **6.10 Verificar registro de evidencias por cliente**
  - **Ejemplo**:
    ```
    15/01/2025 11:17 | Usuario Proveedor | 📤 Evidencia Cliente |
    📄 infraestructura_abc.pdf (2.45 MB) - Cliente: Cliente ABC Ltda.
    ```

- [ ] **6.11 Verificar registro de cierre**
  - **Ejemplo**:
    ```
    15/01/2025 15:45 | Consultor | ✅ Auditoría Cerrada |
    Porcentaje global: 87.5% - 3 cliente(s) evaluados
    ```
  - Badge: `bg-primary` con icono `bi-check-circle`

### 🔍 Detalles JSON

- [ ] **6.12 Expandir detalles (si aplica)**
  - Click en fila para ver detalles completos
  - **JSON esperado para evidencia**:
    ```json
    {
      "id_auditoria_item": 123,
      "nombre_archivo": "verificacion_docs.pdf",
      "tamano_bytes": 1234567,
      "tamano_mb": 1.23
    }
    ```

### 📄 Paginación

- [ ] **6.13 Verificar paginación (si hay > 20 registros)**
  - **Elementos esperados**:
    ```
    Mostrando 1-20 de 25 registros

    [ « ] [ 1 ] [ 2 ] [ » ]
    ```
  - Click en página 2 → Registros 21-25 visibles

- [ ] **6.14 Orden cronológico**
  - **Orden esperado**: Más reciente primero (DESC)
  - Primer registro: Auditoría cerrada (más reciente)
  - Último registro: Primer comentario guardado (más antiguo)

### 🎨 Leyenda de Iconos

- [ ] **6.15 Footer con leyenda visible**
  - **Elementos**:
    ```
    Leyenda de Acciones:
    💬 Comentario global    💬 Comentario por cliente
    📤 Evidencia subida     🗑️ Evidencia eliminada
    ✅ Auditoría cerrada    👥 Clientes asignados
    ```

### 📊 Resultado Esperado Sección 6

```
✅ Bitácora accesible desde detalle de auditoría
✅ Estadísticas visibles: 20+ acciones totales
✅ Desglose por tipo de acción con contadores
✅ Tabla con todas las acciones registradas:
   - 2 comentarios globales
   - 6 comentarios por cliente
   - 4 evidencias globales subidas
   - 12 evidencias por cliente subidas
   - 1 registro de cierre de auditoría
✅ Información completa por cada registro:
   - Fecha/hora exacta
   - Usuario que realizó la acción
   - Tipo de acción con badge colorido
   - Detalles contextuales (nombres archivos, tamaños, clientes)
✅ Paginación funcional (20 registros por página)
✅ Orden cronológico inverso (más reciente primero)
✅ Leyenda de iconos visible
```

---

## 📊 Resumen Global de Testing

### Checklist General

- [ ] **Configuración Base**
  - [x] .env configurado
  - [x] baseURL correcto
  - [x] Base de datos migrada
  - [x] Seeders ejecutados

- [ ] **Módulo Admin**
  - [x] Login exitoso
  - [x] 1 Proveedor creado
  - [x] 3 Clientes creados
  - [x] 3 Contratos activos

- [ ] **Módulo Consultor**
  - [x] Login exitoso
  - [x] Auditoría creada
  - [x] 3 Clientes asignados (selección múltiple)
  - [x] 4 Ítems configurados
  - [x] Revisión y calificación
  - [x] Cierre de auditoría
  - [x] PDFs generados (4)
  - [x] Bitácora completa

- [ ] **Módulo Proveedor**
  - [x] Login exitoso
  - [x] Wizard con 4 tabs
  - [x] 2 Ítems globales diligenciados
  - [x] 6 Ítems por cliente diligenciados
  - [x] Progreso 100%
  - [x] Finalización bloqueada hasta 100%
  - [x] Finalización exitosa

- [ ] **Validaciones UI**
  - [x] Toasts funcionando (success/error)
  - [x] Scroll automático al tope
  - [x] Spinner en botones durante carga
  - [x] Campos marcados con error
  - [x] Auto-limpieza de errores

- [ ] **Emails**
  - [x] Email al finalizar (proveedor → consultor)
  - [x] Email al cerrar (consultor → proveedor)

- [ ] **PDFs**
  - [x] PDF Global descargable
  - [x] PDF Cliente ABC descargable
  - [x] PDF Cliente XYZ descargable
  - [x] PDF Cliente 123 descargable
  - [x] Contenido correcto (logos, evidencias, datos)

- [ ] **Bitácora**
  - [x] Accesible desde detalle
  - [x] Estadísticas correctas
  - [x] Todos los eventos registrados
  - [x] Paginación funcional

---

## 🐛 Errores Comunes y Soluciones

### Error 1: 404 al acceder a la aplicación

**Síntoma**: `http://localhost:8080/` retorna 404

**Solución**:
```bash
# Verificar que el servidor está corriendo
php spark serve

# Verificar baseURL en .env
app.baseURL = 'http://localhost:8080/'
```

### Error 2: No aparecen clientes al crear auditoría

**Síntoma**: Dropdown de clientes vacío

**Solución**:
```sql
-- Verificar que existen contratos activos
SELECT * FROM contratos WHERE estado = 'activo';

-- Verificar relaciones
SELECT c.*, cli.razon_social, p.razon_social as proveedor
FROM contratos c
JOIN clientes cli ON cli.id_cliente = c.id_cliente
JOIN proveedores p ON p.id_proveedor = c.id_proveedor;
```

### Error 3: Botón Finalizar siempre deshabilitado

**Síntoma**: Progreso 100% pero botón no se habilita

**Solución**:
```javascript
// Verificar en consola (F12):
console.log('Progreso actual:', progreso);

// Verificar que todos los ítems tienen:
// - Comentario guardado
// - Al menos 1 evidencia subida
```

### Error 4: PDFs no se generan

**Síntoma**: Error al cerrar auditoría

**Solución**:
```bash
# Verificar permisos de escritura
chmod -R 775 writable/

# Verificar que Dompdf está instalado
composer require dompdf/dompdf

# Verificar logs
tail -f writable/logs/log-*.php
```

### Error 5: Emails no se envían

**Síntoma**: No llegan emails

**Solución**:
```env
# Configurar SMTP en .env
email.protocol = smtp
email.SMTPHost = smtp.gmail.com
email.SMTPPort = 587
email.SMTPUser = tu-email@gmail.com
email.SMTPPass = tu-password
email.SMTPCrypto = tls

# O verificar en logs
tail -f writable/logs/log-*.php | grep Email
```

---

## 📝 Notas Finales

### Tiempo Estimado de Testing Completo

- **Sección 1 (Config)**: 5 minutos
- **Sección 2 (Admin)**: 15 minutos
- **Sección 3 (Consultor - Crear)**: 10 minutos
- **Sección 4 (Proveedor - Diligenciar)**: 30 minutos
- **Sección 5 (Consultor - Cerrar/PDFs)**: 20 minutos
- **Sección 6 (Bitácora)**: 10 minutos

**Total**: ~90 minutos (1.5 horas)

### Datos de Prueba Generados

Al completar este checklist, habrás generado:

- ✅ 1 Proveedor
- ✅ 3 Clientes
- ✅ 3 Contratos activos
- ✅ 1 Auditoría completa
- ✅ 8 Ítems diligenciados (2 globales + 6 por cliente)
- ✅ ~16 Evidencias subidas
- ✅ 4 PDFs generados
- ✅ ~20+ Registros en bitácora
- ✅ 2 Emails enviados

### Criterios de Éxito

El sistema pasa el testing si:

1. ✅ Todos los módulos son accesibles por el rol correcto
2. ✅ Creación de entidades funciona (toasts, validación)
3. ✅ Selección múltiple de clientes funciona
4. ✅ Wizard muestra tabs correctamente
5. ✅ Progreso se actualiza en tiempo real
6. ✅ Botón finalizar se deshabilita hasta 100%
7. ✅ Emails se envían (o se registran en logs)
8. ✅ PDFs se generan y descargan correctamente
9. ✅ Bitácora registra todas las acciones
10. ✅ No hay errores en consola del navegador
11. ✅ No hay errores en logs de CodeIgniter

---

## 📧 Reporte de Resultados

**Fecha de ejecución**: ___/___/_____
**Ejecutado por**: ________________________
**Ambiente**: [ ] Desarrollo [ ] Producción
**Resultado general**: [ ] ✅ PASS [ ] ❌ FAIL

**Errores encontrados**: _____________________
**Observaciones**: ___________________________

---

**Fin del Checklist de Testing Completo** ✅
