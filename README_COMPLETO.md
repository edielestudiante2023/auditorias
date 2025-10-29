# 📚 Sistema de Auditorías - Documentación Completa

## 🎯 Visión General del Proyecto

Sistema de auditorías a proveedores de servicios que trabajan para múltiples clientes/contratos. Permite auditorías diferenciadas por proyecto/cliente con gestión de evidencias separadas.

### Roles del Sistema

- **Super Admin (1):** Gestión completa del sistema
- **Consultor (2):** Crea auditorías, asigna clientes, revisa, califica y genera PDFs
- **Proveedor (3):** Completa auditorías mediante wizard, sube evidencias globales y por cliente

---

## 📂 Índice de Documentación

### 1. 🚀 Inicio Rápido

**[INICIO_RAPIDO.md](INICIO_RAPIDO.md)**
- Guía de 1 página para setup inmediato
- Opción de 1 clic (Windows) o 1 comando (Linux/Mac)
- Credenciales de acceso

**Para usuarios impacientes:**
```cmd
# Windows
run_admin_setup.bat

# Linux/Mac
./run_admin_setup.sh
```

---

### 2. ✈️ Pre-Flight (Antes de QA)

**[PRE_FLIGHT_CHECKLIST.md](PRE_FLIGHT_CHECKLIST.md)**
- Configuración `.env` completa
- Permisos de archivos
- Dependencias Composer
- Configuración PHP
- Validación de base de datos
- Troubleshooting común

**Script de validación automática:**
```bash
php pre_flight_check.php
# o ejecutar: pre_flight_check.bat (Windows) / pre_flight_check.sh (Linux)
```

---

### 3. 🗄️ Setup de Datos

**[README_ADMIN_QUICKSEED.md](README_ADMIN_QUICKSEED.md)**
- Seeder `AdminQuickSeed` completo
- 3 Clientes, 2 Proveedores, 2 Consultores
- 2 Contratos activos
- Credenciales de 4 usuarios

**Ejecución:**
```bash
php spark db:seed AdminQuickSeed
```

---

### 4. 🧪 Tests QA

**[SMOKE_TESTS_QA.md](SMOKE_TESTS_QA.md)**
- 28 tests funcionales mínimos
- Cobertura completa de flujos
- Checklist de aprobación
- Formato de reporte de bugs

**Categorías de tests:**
1. ✅ Autenticación y roles (3 tests)
2. ✅ Módulo Admin - CRUD completo (8 tests)
3. ✅ Módulo Consultor - Flujo auditoría (4 tests)
4. ✅ Módulo Proveedor - Wizard evidencias (4 tests)
5. ✅ Sistema de archivos (2 tests)
6. ✅ Notificaciones email (2 tests)
7. ✅ Generación PDFs (3 tests)
8. ✅ Seguridad y permisos (2 tests)

---

## 🔧 Scripts Disponibles

### Setup y Validación

| Script | Descripción | Plataforma |
|--------|-------------|------------|
| `run_admin_setup.bat` | Setup completo automático | Windows |
| `run_admin_setup.sh` | Setup completo automático | Linux/Mac |
| `pre_flight_check.bat` | Validación pre-QA | Windows |
| `pre_flight_check.sh` | Validación pre-QA | Linux/Mac |
| `pre_flight_check.php` | Validación detallada | Todos |
| `test_admin_workflow.php` | Test funcional automático | Todos |

### Uso Típico

```bash
# 1. Validar configuración
php pre_flight_check.php

# 2. Cargar datos de prueba
php spark db:seed AdminQuickSeed

# 3. Ejecutar tests automatizados
php test_admin_workflow.php

# 4. Realizar tests manuales según SMOKE_TESTS_QA.md
```

---

## 🏗️ Arquitectura del Sistema

### Stack Tecnológico

- **Framework:** CodeIgniter 4
- **Base de datos:** MySQL/MariaDB
- **PDFs:** Dompdf
- **Email:** SendGrid
- **Frontend:** Bootstrap 5 + DataTables

### Estructura de Carpetas

```
auditorias/
├── app/
│   ├── Controllers/
│   │   ├── Admin/          → CRUD módulos admin
│   │   ├── Consultor/      → Flujo auditorías consultor
│   │   └── Proveedor/      → Wizard evidencias proveedor
│   ├── Models/             → Modelos de datos
│   ├── Views/
│   │   ├── admin/          → Vistas admin
│   │   ├── consultor/      → Vistas consultor
│   │   ├── proveedor/      → Vistas proveedor
│   │   ├── pdf/            → Templates PDFs
│   │   └── partials/       → Componentes reutilizables
│   ├── Database/
│   │   ├── Migrations/     → Estructura BD
│   │   └── Seeds/          → Datos de prueba
│   ├── Filters/            → Auth y Role filters
│   └── Services/           → Upload, Email, PDF
├── writable/
│   └── uploads/
│       └── {nit_proveedor}/
│           └── {id_auditoria}/
│               ├── global/                  → Evidencias globales
│               └── cliente-{id_cliente}/    → Evidencias por cliente
└── public/                 → Document root
```

---

## 📊 Flujos Principales

### 1. Flujo Admin

```
Login Admin
    ↓
Dashboard (contadores)
    ↓
CRUD: Clientes / Proveedores / Consultores / Contratos / Usuarios
    ↓
Configurar Banco de Ítems (alcance)
```

### 2. Flujo Consultor (3 Pasos + Revisión)

```
Login Consultor
    ↓
Crear Auditoría (selecciona proveedor)
    ↓
Asignar Clientes A, B, C (del proveedor)
    ↓
Enviar Invitación (email/notificación)
    ↓
[Proveedor completa wizard]
    ↓
Revisar Auditoría
    ↓
Calificar ítems (global o por cliente)
    ↓
Cerrar Auditoría
    ↓
Generar PDFs (por cliente / global / completo)
```

### 3. Flujo Proveedor

```
Login Proveedor
    ↓
Ver Auditorías Asignadas
    ↓
Acceder a Wizard
    ↓
Por cada ítem del banco:
    - Agregar comentarios
    - Subir evidencias GLOBALES
    - Subir evidencias POR CLIENTE A, B, C
    ↓
Finalizar Auditoría
    ↓
Notificar a Consultor
```

---

## ✨ Características Clave

### 🎯 Feature 1: Auditorías Diferenciadas por Cliente

**Problema:** Un mismo proveedor trabaja para clientes A, B, C con dotaciones/planillas distintas.

**Solución:**
- Tabla `auditoria_clientes`: vincula auditoría a múltiples clientes
- Tabla `auditoria_item_cliente`: calificación específica por cliente
- Estructura de carpetas: `uploads/{nit}/{id_auditoria}/cliente-{id}/`

**Ejemplo:**
```
Proveedor Alpha audita clientes:
  - Cliente A: Dotación completa → Cumple
  - Cliente B: Dotación incompleta → No cumple
  - Cliente C: Sin dotación → No aplica
```

### 📁 Feature 2: Evidencias Separadas

Estructura de archivos:
```
writable/uploads/
└── 901234567-8/              ← NIT Proveedor Alpha
    └── 1/                     ← ID Auditoría
        ├── global/            ← Evidencias que aplican a todos
        │   └── item-5/
        │       └── foto.jpg
        ├── cliente-1/         ← Evidencias específicas Cliente A
        │   └── item-7/
        │       └── planilla_A.pdf
        ├── cliente-2/         ← Evidencias específicas Cliente B
        │   └── item-7/
        │       └── planilla_B.pdf
        └── cliente-3/         ← Evidencias específicas Cliente C
            └── item-7/
                └── planilla_C.pdf
```

### 📋 Feature 3: Banco de Ítems con Alcance

Cada ítem puede configurarse:
- **Alcance:** ¿Para qué clientes aplica?
- **Requerido:** ¿Es obligatorio?
- **Tipo:** Global o por cliente

### 📄 Feature 4: PDFs Personalizados

3 tipos de reportes:
1. **PDF por Cliente:** Solo información del cliente seleccionado
2. **PDF Global:** Resumen de todos los clientes
3. **PDF Completo:** Global + anexos por cada cliente

---

## 🔐 Seguridad

### Autenticación
- Filtro `AuthFilter`: Verifica sesión activa
- Redirección a `/login` si no autenticado

### Autorización
- Filtro `RoleFilter`: Valida roles permitidos
- Redirección con flash "No autorizado" si acceso denegado
- Helper `can($permiso)`: Verifica permisos en vistas

### Rutas Protegidas
```php
// Admin: solo rol 1
$routes->group('admin', ['filter' => ['auth', 'role:1']], ...);

// Consultor: solo rol 2
$routes->group('consultor', ['filter' => ['auth', 'role:2']], ...);

// Proveedor: solo rol 3
$routes->group('proveedor', ['filter' => ['auth', 'role:3']], ...);
```

### Uploads
- Validación de extensiones (whitelist)
- Validación de MIME types
- Límite de tamaño (configurable)
- Carpetas por NIT (aislamiento)

---

## 🧩 Componentes Reutilizables

### Vistas Parciales

#### 1. Flash Messages
**Archivo:** `app/Views/partials/flash.php`

Soporta: success, error, warning, info

**Uso:**
```php
<?= view('partials/flash') ?>
```

#### 2. Breadcrumbs
**Archivo:** `app/Views/partials/breadcrumbs.php`

**Uso:**
```php
$data['breadcrumbs'] = [
    ['title' => 'Admin', 'url' => 'admin/dashboard'],
    ['title' => 'Clientes', 'url' => 'admin/clientes'],
    ['title' => 'Crear', 'url' => null]
];

<?= view('partials/breadcrumbs', ['breadcrumbs' => $breadcrumbs]) ?>
```

#### 3. Empty States
**Archivo:** `app/Views/partials/_empty.php`

**Uso:**
```php
<?= view('partials/_empty', [
    'icon' => 'bi-inbox',
    'title' => 'No hay datos',
    'message' => 'Comienza agregando registros',
    'button_text' => 'Crear Nuevo',
    'button_url' => 'admin/clientes/create'
]) ?>
```

### Helpers

#### Auth Helper
```php
isLogged()              // ¿Está autenticado?
userId()                // ID del usuario actual
userRole()              // ID del rol (1, 2, 3)
userName()              // Nombre del usuario
isSuperAdmin()          // ¿Es super admin?
isConsultor()           // ¿Es consultor?
isProveedor()           // ¿Es proveedor?
can('admin.create')     // ¿Tiene permiso?
```

---

## 📊 Base de Datos

### Tablas Principales

| Tabla | Descripción |
|-------|-------------|
| `roles` | 3 roles: Super Admin, Consultor, Proveedor |
| `users` | Usuarios del sistema |
| `clientes` | Empresas cliente |
| `proveedores` | Proveedores de servicios |
| `consultores` | Consultores con licencias SST |
| `servicios` | Tipos de servicios (ej: Auditoría SST) |
| `contratos_proveedor_cliente` | Relación proveedor-cliente-servicio |
| `items_banco` | Banco de ítems con alcance |
| `auditorias` | Auditorías creadas |
| `auditoria_clientes` | Clientes asignados a auditoría |
| `auditoria_items` | Ítems de una auditoría |
| `auditoria_item_cliente` | Calificación por cliente |
| `evidencias` | Evidencias globales |
| `evidencias_cliente` | Evidencias por cliente |
| `notificaciones` | Log de emails enviados |

### Relaciones Clave

```
proveedor (1) ──→ (N) contratos ←── (N) clientes
                         ↓
                    auditorias
                         ↓
              ┌──────────┴──────────┐
         auditoria_items    auditoria_clientes
              ↓                      ↓
    auditoria_item_cliente (permite calificación específica)
```

---

## 🚀 Despliegue a Producción

### Checklist Pre-Producción

- [ ] Cambiar `CI_ENVIRONMENT = production` en `.env`
- [ ] Configurar `app.baseURL` con dominio real
- [ ] Habilitar HTTPS
- [ ] Configurar CSRF en formularios
- [ ] **ELIMINAR endpoints de test** (`test-upload/*`, `test-email/*`)
- [ ] Cambiar credenciales por defecto
- [ ] Configurar SendGrid con API Key real
- [ ] Ajustar límites de upload según necesidad
- [ ] Configurar backups de BD
- [ ] Verificar logs en `writable/logs/`
- [ ] Configurar cron para limpieza de archivos antiguos

### Servidor Recomendado

- PHP >= 7.4 (recomendado 8.0+)
- MySQL >= 5.7 o MariaDB >= 10.3
- Apache con mod_rewrite o Nginx
- SSL/TLS configurado
- Permisos: `writable/` escribible por www-data

### VirtualHost Producción (Apache)

```apache
<VirtualHost *:443>
    ServerName auditorias.tuempresa.com
    DocumentRoot /var/www/auditorias/public

    SSLEngine on
    SSLCertificateFile /path/to/cert.crt
    SSLCertificateKeyFile /path/to/private.key

    <Directory /var/www/auditorias/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 🐛 Troubleshooting

### Problema: 404 después de login

**Causa:** `app.baseURL` incorrecta o document root mal configurado

**Solución:**
1. Verificar `.env`: `app.baseURL = 'https://tudominio.com/'` (con slash final)
2. Document root debe apuntar a `/public`

---

### Problema: Uploads fallan

**Causa:** Permisos insuficientes

**Solución:**
```bash
sudo chown -R www-data:www-data writable/
sudo chmod -R 775 writable/
```

---

### Problema: PDFs en blanco

**Causa:** Dompdf no puede acceder a imágenes

**Solución:**
- Verificar que `FCPATH` está configurado
- Usar rutas absolutas en templates
- Verificar permisos de lectura en `writable/uploads/`

---

### Problema: Emails no se envían

**Causa:** SendGrid no configurado o API Key inválida

**Solución:**
1. Verificar `sendgrid.apiKey` en `.env`
2. Revisar logs en tabla `notificaciones`
3. En QA, desactivar envío real y solo registrar logs

---

## 📞 Soporte

### Logs del Sistema

- **Aplicación:** `writable/logs/log-YYYY-MM-DD.log`
- **Emails:** Tabla `notificaciones`
- **Errores PHP:** Según configuración de `php.ini`

### Información para Reportar Bugs

1. Mensaje de error completo
2. Pasos para reproducir
3. Usuario y rol que experimenta el problema
4. Screenshot (si aplica)
5. Logs relevantes

---

## 📅 Mantenimiento

### Tareas Periódicas

- **Diario:** Revisar logs de errores
- **Semanal:** Verificar espacio en disco (`writable/uploads/`)
- **Mensual:** Backup de base de datos
- **Trimestral:** Actualizar dependencias (`composer update`)
- **Anual:** Revisar y actualizar credenciales

### Limpieza de Archivos

Script recomendado para limpiar uploads antiguos (>6 meses):

```php
// Ejecutar como cron job
php spark app:cleanup-old-uploads
```

---

## 🎓 Referencias

- [CodeIgniter 4 Documentation](https://codeigniter.com/user_guide/)
- [Dompdf Documentation](https://github.com/dompdf/dompdf)
- [SendGrid PHP Library](https://github.com/sendgrid/sendgrid-php)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.0/)
- [DataTables Documentation](https://datatables.net/)

---

## 📝 Changelog

### Versión 1.0 (2025-01-XX)

**Features:**
- ✅ Sistema de autenticación por roles
- ✅ CRUD completo módulos admin
- ✅ Flujo consultor: crear auditoría en 3 pasos
- ✅ Wizard proveedor con evidencias diferenciadas
- ✅ Generación de PDFs (cliente/global/completo)
- ✅ Sistema de notificaciones por email
- ✅ Banco de ítems con alcance
- ✅ Upload de archivos con estructura por cliente
- ✅ Dashboard con contadores y cards activos
- ✅ DataTables integrado
- ✅ Componentes reutilizables (breadcrumbs, flash, empty)

**Scripts:**
- ✅ AdminQuickSeed: datos de prueba completos
- ✅ pre_flight_check.php: validación automática
- ✅ test_admin_workflow.php: tests automatizados
- ✅ Scripts .bat y .sh para setup rápido

**Documentación:**
- ✅ PRE_FLIGHT_CHECKLIST.md
- ✅ SMOKE_TESTS_QA.md (28 tests)
- ✅ README_ADMIN_QUICKSEED.md
- ✅ INICIO_RAPIDO.md
- ✅ README_COMPLETO.md (este archivo)

---

**Última actualización:** 2025-01-XX
**Versión del sistema:** 1.0.0
**Autor:** Equipo de Desarrollo Cycloid
