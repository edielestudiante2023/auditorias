# ✈️ Pre-Flight Checklist - Sistema de Auditorías

## Antes de Iniciar QA

Este checklist asegura que el sistema está correctamente configurado antes de pruebas funcionales.

---

## 📋 Checklist Completo

### 1. ⚙️ Configuración .env

```env
# CI4 Environment
CI_ENVIRONMENT = development  # Cambiar a 'production' en producción

# App
app.baseURL = 'http://localhost/auditorias/'
# ⚠️ IMPORTANTE: En VPS ajusta dominio + /public/ si es necesario
# Ejemplo VPS: app.baseURL = 'https://tudominio.com/public/'

# Database
database.default.hostname = localhost
database.default.database = auditorias_db
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.DBPrefix =

# SendGrid (Email)
sendgrid.apiKey = 'SG.xxxxxxxxxxxxxxxxxxxxx'
email.fromEmail = 'noreply@tuempresa.com'
email.fromName = 'Sistema de Auditorías'

# Security
encryption.key = 'your-32-character-encryption-key-here'
```

**✅ Verificar:**
- [ ] `.env` existe y no es `.env.example`
- [ ] `app.baseURL` apunta correctamente (con slash final)
- [ ] Credenciales de BD correctas
- [ ] API Key de SendGrid configurada (opcional para QA básica)
- [ ] `encryption.key` generada (32 caracteres)

---

### 2. 🗄️ Base de Datos

```bash
# Crear base de datos
mysql -u root -p
CREATE DATABASE auditorias_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Ejecutar migraciones
php spark migrate

# Cargar datos de prueba
php spark db:seed AdminQuickSeed
```

**✅ Verificar:**
- [ ] Base de datos creada
- [ ] Migraciones ejecutadas sin errores
- [ ] Tablas creadas (users, roles, clientes, proveedores, etc.)
- [ ] Seeder ejecutado con datos de prueba

---

### 3. 📁 Permisos de Archivos

```bash
# En Linux/Mac
chmod -R 775 writable/
chmod -R 775 writable/uploads/

# En Windows (XAMPP)
# Verificar que IUSR/IIS_IUSRS tengan permisos de escritura
# o que el usuario de Apache tenga acceso completo
```

**✅ Verificar:**
- [ ] `writable/` tiene permisos de escritura
- [ ] `writable/uploads/` existe y es escribible
- [ ] `writable/logs/` es escribible
- [ ] `writable/cache/` es escribible
- [ ] `writable/session/` es escribible

---

### 4. 📦 Dependencias Composer

```bash
composer install
```

**✅ Verificar:**
- [ ] `vendor/` existe
- [ ] `vendor/autoload.php` existe
- [ ] Dompdf instalado (`vendor/dompdf/`)
- [ ] SendGrid instalado (`vendor/sendgrid/`)
- [ ] No hay errores de dependencias

---

### 5. 🌐 Servidor Web

#### Apache (.htaccess)

Verificar que `public/.htaccess` existe:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

#### VirtualHost Recomendado

```apache
<VirtualHost *:80>
    ServerName auditorias.local
    DocumentRoot "C:/xampp/htdocs/auditorias/public"

    <Directory "C:/xampp/htdocs/auditorias/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**✅ Verificar:**
- [ ] Servidor web corriendo (Apache/Nginx)
- [ ] PHP >= 7.4 (recomendado 8.0+)
- [ ] `mod_rewrite` habilitado (Apache)
- [ ] Document root apunta a `/public` (opcional pero recomendado)
- [ ] URL base accesible sin 404

---

### 6. 🔒 Configuración PHP

Verificar en `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
memory_limit = 256M

# Extensiones requeridas
extension=mysqli
extension=mbstring
extension=intl
extension=json
extension=curl
extension=gd
extension=zip
```

**✅ Verificar:**
- [ ] Límites de upload adecuados (>= 20M)
- [ ] Extensiones PHP habilitadas
- [ ] `allow_url_fopen = On` (para SendGrid)

---

### 7. 🧪 Endpoints de Prueba (Solo QA)

**⚠️ IMPORTANTE:** Estos endpoints deben estar **DESHABILITADOS en producción**

Ubicados en `app/Config/Routes.php`:

```php
// ============================================================
// Test Upload Service (ELIMINAR EN PRODUCCIÓN)
// ============================================================
$routes->group('test-upload', function ($routes) {
    // ...
});

// ============================================================
// Test Email Service (ELIMINAR EN PRODUCCIÓN)
// ============================================================
$routes->group('test-email', function ($routes) {
    // ...
});
```

**✅ Verificar:**
- [ ] Endpoints de test accesibles en QA
- [ ] Comentar/eliminar antes de desplegar a producción
- [ ] Considerar agregar filtro de IP o ambiente

---

### 8. 🔐 Seguridad Pre-Producción

**Para QA:**
- [ ] CSRF deshabilitado temporalmente (OK en desarrollo)
- [ ] Errores visibles (`CI_ENVIRONMENT = development`)
- [ ] Logs habilitados

**Antes de Producción:**
- [ ] CSRF habilitado en formularios
- [ ] `CI_ENVIRONMENT = production`
- [ ] Errores ocultos al usuario
- [ ] HTTPS configurado
- [ ] Eliminar endpoints de test
- [ ] Cambiar credenciales por defecto

---

## 🚀 Scripts de Validación

### Script Rápido (Windows)

```cmd
pre_flight_check.bat
```

### Script Rápido (Linux/Mac)

```bash
./pre_flight_check.sh
```

---

## ✅ Tests Mínimos Antes de QA

### 1. Login Test
```
URL: http://localhost/auditorias/login
Credenciales: superadmin@cycloidtalent.com / Admin123*
Resultado esperado: Redirección a /admin/dashboard
```

### 2. Dashboard Test
```
URL: http://localhost/auditorias/admin/dashboard
Resultado esperado:
  - Contadores > 0 (clientes, proveedores, etc.)
  - No errores 500/404
  - Cards de módulos visibles
```

### 3. Upload Test (Opcional)
```
URL: http://localhost/auditorias/test-upload/
Acción: Subir imagen de prueba
Resultado esperado: Archivo guardado en writable/uploads/
```

### 4. Email Test (Opcional)
```
URL: http://localhost/auditorias/test-email/
Acción: Enviar email de prueba
Resultado esperado: Log en notificaciones (o email recibido si SendGrid configurado)
```

---

## 🐛 Troubleshooting Común

### Error: "404 Page Not Found" después de login

**Causa:** `app.baseURL` incorrecta o document root mal configurado

**Solución:**
```env
# En .env, asegúrate de incluir el slash final
app.baseURL = 'http://localhost/auditorias/'
```

### Error: "Class 'Dompdf' not found"

**Causa:** Dependencias no instaladas

**Solución:**
```bash
composer install
```

### Error: "Failed to write file to disk"

**Causa:** Permisos insuficientes en `writable/uploads/`

**Solución:**
```bash
chmod -R 775 writable/
# o en Windows, dar permisos de escritura completos
```

### Error: SendGrid "Unauthorized"

**Causa:** API Key inválida o no configurada

**Solución:**
- Verificar `.env` tiene `sendgrid.apiKey` correcto
- Temporalmente, probar sin enviar emails (usar logs)

---

## 📊 Estado del Sistema

Una vez completado el checklist, el sistema debe tener:

✅ **Usuarios:**
- 1 Super Admin
- 2 Consultores
- 1 Proveedor

✅ **Datos:**
- 3 Clientes
- 2 Proveedores
- 2 Consultores
- 2 Contratos activos
- 1 Servicio (Auditoría SST)
- Ítems del banco (seeds)

✅ **Funcionalidad:**
- Login por roles
- Dashboards personalizados
- CRUD completo en módulos admin
- Upload de archivos funcional
- Email (opcional en QA)
- Generación de PDFs

---

## 🎯 Siguiente Paso

Una vez completado este checklist, puedes proceder con:

**[SMOKE_TESTS_QA.md](SMOKE_TESTS_QA.md)** - Tests funcionales completos

---

**Última actualización:** 2025-01-XX
