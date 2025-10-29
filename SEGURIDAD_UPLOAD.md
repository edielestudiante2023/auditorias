# Sistema de Seguridad para Subida de Archivos

## 🔒 Implementación de Seguridad Completa

Este documento describe el sistema de seguridad implementado para proteger la subida de archivos en el sistema de auditorías.

---

## 📋 Características de Seguridad Implementadas

### 1. ✅ CSRF Protection (Activado Globalmente)

**Archivo:** `app/Config/Filters.php`

```php
public array $globals = [
    'before' => [
        'csrf',  // ← Protección CSRF activa globalmente
    ],
];
```

**Configuración:** `app/Config/Security.php`
- Método: Cookie
- Token Randomize: Desactivado para mantener consistencia
- Regenerate: Activado (token nuevo en cada request)
- Expires: 7200 segundos (2 horas)

### 2. 🛡️ Validaciones de Seguridad en `UploadService`

#### A. Validación de Tamaño
- **Límite:** 15 MB máximo
- **Verificación:** Antes de procesar el archivo

#### B. Validación MIME Real
- **Método:** `finfo_file()` - Verifica contenido real del archivo
- **No confía en:** Headers HTTP o extensión del archivo
- **MIME permitidos:**
  - `application/pdf` (PDF)
  - `application/vnd.openxmlformats-officedocument.wordprocessingml.document` (DOCX)
  - `application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` (XLSX)
  - `image/png` (PNG)
  - `image/jpeg` (JPG/JPEG)
  - `video/mp4` (MP4)

#### C. Bloqueo de Extensiones Peligrosas

**Lista negra de extensiones bloqueadas:**
```php
'php', 'phar', 'phtml', 'php3', 'php4', 'php5', 'php7',
'js', 'exe', 'sh', 'bat', 'cmd', 'dll', 'com', 'scr',
'vbs', 'jar', 'app', 'deb', 'rpm', 'bin', 'run',
'svg', 'html', 'htm', 'xml', 'swf'
```

#### D. Detección de Doble Extensión

**Ejemplos bloqueados:**
- `archivo.php.jpg` ❌
- `documento.phar.png` ❌
- `shell.php.pdf` ❌
- `exploit.js.docx` ❌

**Algoritmo:**
1. Divide el nombre por puntos: `['archivo', 'php', 'jpg']`
2. Verifica partes intermedias contra lista negra
3. Bloquea si encuentra extensión peligrosa en el medio

#### E. Bloqueo de MIME Types Peligrosos

```php
'application/x-httpd-php',
'application/x-php',
'application/x-sh',
'application/x-executable',
'application/x-msdos-program',
'text/x-php',
'text/x-shellscript',
'application/javascript',
'text/javascript',
'image/svg+xml',
'text/html',
'application/x-phar'
```

### 3. 📊 Sistema de Logging de Intentos Fallidos

#### Tabla de Base de Datos: `upload_security_logs`

```sql
CREATE TABLE upload_security_logs (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50),  -- 'upload_failed'
    reason VARCHAR(100),      -- 'dangerous_extension', 'double_extension', etc.
    filename VARCHAR(255),
    filesize INT UNSIGNED,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    user_id INT UNSIGNED,
    id_auditoria INT UNSIGNED,
    id_item INT UNSIGNED,
    metadata TEXT,            -- JSON con detalles
    created_at DATETIME
);
```

#### Tipos de Eventos Registrados

| Reason Code | Descripción |
|------------|-------------|
| `no_file` | No se recibió archivo |
| `upload_error` | Error de PHP en upload |
| `size_exceeded` | Archivo excede 15 MB |
| `double_extension` | Doble extensión detectada |
| `dangerous_extension` | Extensión bloqueada |
| `extension_not_allowed` | Extensión no permitida |
| `dangerous_mime` | MIME type peligroso |
| `mime_not_allowed` | MIME type no permitido |

#### Información Registrada

```json
{
    "event": "upload_failed",
    "reason": "dangerous_extension",
    "filename": "malicious.php.jpg",
    "size": 1024,
    "ip_address": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "user_id": 5,
    "id_auditoria": 123,
    "id_item": 45,
    "timestamp": "2025-10-16 14:30:00",
    "extra": {
        "extension": "jpg",
        "detected_mime": "application/x-php"
    }
}
```

---

## 🧪 Verificación Manual con Archivos Maliciosos

### Generar Archivos de Prueba

```bash
php tests/SecurityTests/malicious_files_generator.php
```

Esto genera 16 archivos de prueba en `tests/SecurityTests/test_files/`:

#### Archivos Maliciosos (DEBEN ser bloqueados) ❌

1. **malicious.php.jpg** - Doble extensión PHP
2. **document.phar.pdf** - Doble extensión PHAR
3. **shell.php** - PHP directo
4. **backdoor.phtml** - PHTML
5. **exploit.js** - JavaScript
6. **virus.exe.jpg** - EXE disfrazado
7. **file.php.pdf.jpg** - Triple extensión
8. **script.sh** - Shell script
9. **malware.bat** - Batch script
10. **image.jpg** - Magic bytes incorrectos (GIF como JPG)
11. **malicious.svg** - SVG con JavaScript
12. **phishing.html** - HTML malicioso
13. **large_file.pdf** - Excede 15 MB

#### Archivos Legítimos (DEBEN ser aceptados) ✅

14. **valid_image.jpg** - JPG válido
15. **valid_image.png** - PNG válido
16. **valid_document.pdf** - PDF válido

### Procedimiento de Prueba Manual

#### 1. Preparar Entorno

```bash
# Crear archivos de prueba
php tests/SecurityTests/malicious_files_generator.php

# Ejecutar migración de logs
php spark migrate
```

#### 2. Probar cada archivo

**Para cada archivo malicioso:**

1. Iniciar sesión en el sistema
2. Navegar a subida de evidencia
3. Intentar subir archivo malicioso
4. **Resultado esperado:** Error de validación
5. Verificar log en base de datos

```sql
SELECT * FROM upload_security_logs
ORDER BY created_at DESC LIMIT 10;
```

**Para archivos legítimos:**

1. Intentar subir archivo legítimo
2. **Resultado esperado:** Subida exitosa
3. Verificar archivo en filesystem

#### 3. Ejemplo de Prueba con cURL

```bash
# Obtener token CSRF
CSRF_TOKEN=$(curl -c cookies.txt http://localhost/auditorias/login | grep csrf_token_name | cut -d'"' -f6)

# Intentar subir archivo malicioso
curl -X POST \
  -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
  -F "evidencia=@tests/SecurityTests/test_files/malicious.php.jpg" \
  -F "id_auditoria=1" \
  -F "id_item=1" \
  -b cookies.txt \
  http://localhost/auditorias/api/upload/evidencia
```

**Respuesta esperada:**
```json
{
    "ok": false,
    "error": "Archivo con doble extensión detectado. Esto no está permitido por razones de seguridad."
}
```

---

## 📝 Uso en Formularios

### Agregar Token CSRF en Formularios

#### Método 1: Helper de CodeIgniter

```php
<?= csrf_field() ?>
```

#### Método 2: Manual

```html
<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" />
```

### Ejemplo Completo de Formulario

```html
<form action="/upload/evidencia" method="POST" enctype="multipart/form-data">
    <!-- Token CSRF (REQUERIDO) -->
    <?= csrf_field() ?>

    <!-- Campos del formulario -->
    <input type="hidden" name="id_auditoria" value="123">
    <input type="hidden" name="id_auditoria_item" value="45">

    <div class="mb-3">
        <label for="evidencia" class="form-label">Evidencia</label>
        <input type="file"
               class="form-control"
               id="evidencia"
               name="evidencia"
               accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx,.mp4"
               required>
        <div class="form-text">
            Máximo 15 MB. Formatos: PDF, JPG, PNG, DOCX, XLSX, MP4
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Subir Evidencia</button>
</form>
```

### Ejemplo con AJAX

```javascript
// Obtener token CSRF del DOM
const csrfName = document.querySelector('meta[name="csrf-token-name"]').content;
const csrfHash = document.querySelector('meta[name="csrf-token-hash"]').content;

// Crear FormData
const formData = new FormData();
formData.append(csrfName, csrfHash);
formData.append('evidencia', fileInput.files[0]);
formData.append('id_auditoria', 123);
formData.append('id_auditoria_item', 45);

// Enviar con Fetch
fetch('/upload/evidencia', {
    method: 'POST',
    body: formData,
    headers: {
        'X-CSRF-TOKEN': csrfHash
    }
})
.then(response => response.json())
.then(data => {
    if (data.ok) {
        alert('Archivo subido exitosamente');
    } else {
        alert('Error: ' + data.error);
    }
})
.catch(error => console.error('Error:', error));
```

#### Meta Tags en Layout

```html
<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<meta name="csrf-token-hash" content="<?= csrf_hash() ?>">
```

---

## 🔍 Monitoreo de Seguridad

### Consultas SQL Útiles

#### Ver intentos fallidos recientes

```sql
SELECT
    DATE(created_at) as fecha,
    COUNT(*) as intentos,
    reason,
    ip_address
FROM upload_security_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at), reason, ip_address
ORDER BY created_at DESC;
```

#### Detectar ataques masivos desde una IP

```sql
SELECT
    ip_address,
    COUNT(*) as intentos_fallidos,
    MAX(created_at) as ultimo_intento
FROM upload_security_logs
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address
HAVING COUNT(*) > 10
ORDER BY intentos_fallidos DESC;
```

#### Tipos de ataques más comunes

```sql
SELECT
    reason,
    COUNT(*) as total
FROM upload_security_logs
GROUP BY reason
ORDER BY total DESC;
```

#### Usuarios con más intentos fallidos

```sql
SELECT
    u.nombre,
    u.email,
    COUNT(*) as intentos_fallidos
FROM upload_security_logs sl
LEFT JOIN users u ON u.id_users = sl.user_id
WHERE sl.user_id IS NOT NULL
GROUP BY u.id_users
ORDER BY intentos_fallidos DESC
LIMIT 10;
```

---

## ⚠️ Respuesta ante Incidentes

### Procedimiento ante Detección de Ataque

1. **Verificar logs:**
   ```sql
   SELECT * FROM upload_security_logs
   WHERE ip_address = '192.168.1.100'
   ORDER BY created_at DESC;
   ```

2. **Bloquear IP temporalmente** (en firewall/WAF)

3. **Revisar archivos subidos recientemente:**
   ```sql
   SELECT * FROM evidencias
   WHERE created_at >= '2025-10-16 00:00:00'
   ORDER BY created_at DESC;
   ```

4. **Verificar integridad con hash SHA256**

5. **Notificar al equipo de seguridad**

---

## 🛠️ Configuración Adicional Recomendada

### 1. Configurar .htaccess en directorio de uploads

```apache
# writable/uploads/.htaccess
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phar|exe|sh)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Deshabilitar ejecución de scripts
Options -ExecCGI
RemoveHandler .php .phtml .php3 .php4 .php5 .phar
```

### 2. Configurar permisos de directorio

```bash
# Linux
chmod 755 writable/uploads/
find writable/uploads/ -type f -exec chmod 644 {} \;

# Propietario: www-data
chown -R www-data:www-data writable/uploads/
```

### 3. Configurar PHP ini

```ini
; Tamaño máximo de uploads
upload_max_filesize = 15M
post_max_size = 16M

; Deshabilitar funciones peligrosas
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

; Activar open_basedir
open_basedir = /var/www/html:/tmp
```

---

## 📊 Resultados Esperados de Pruebas

| Archivo | Resultado | Razón de Bloqueo |
|---------|-----------|------------------|
| malicious.php.jpg | ❌ BLOQUEADO | Doble extensión PHP |
| document.phar.pdf | ❌ BLOQUEADO | Doble extensión PHAR |
| shell.php | ❌ BLOQUEADO | Extensión peligrosa |
| backdoor.phtml | ❌ BLOQUEADO | Extensión peligrosa |
| exploit.js | ❌ BLOQUEADO | Extensión peligrosa |
| virus.exe.jpg | ❌ BLOQUEADO | MIME type peligroso |
| file.php.pdf.jpg | ❌ BLOQUEADO | Triple extensión |
| script.sh | ❌ BLOQUEADO | Extensión peligrosa |
| malware.bat | ❌ BLOQUEADO | Extensión peligrosa |
| image.jpg | ❌ BLOQUEADO | MIME no coincide |
| malicious.svg | ❌ BLOQUEADO | Extensión bloqueada |
| phishing.html | ❌ BLOQUEADO | Extensión bloqueada |
| large_file.pdf | ❌ BLOQUEADO | Excede 15 MB |
| valid_image.jpg | ✅ PERMITIDO | Válido |
| valid_image.png | ✅ PERMITIDO | Válido |
| valid_document.pdf | ✅ PERMITIDO | Válido |

---

## ✅ Checklist de Seguridad

- [x] CSRF activado globalmente
- [x] Validación MIME real con finfo
- [x] Límite de tamaño 15 MB
- [x] Bloqueo de extensiones peligrosas
- [x] Detección de doble extensión
- [x] Bloqueo de MIME types peligrosos
- [x] Logging de intentos fallidos
- [x] Registro de IP, user_id, timestamp
- [x] Tabla de logs en base de datos
- [x] Archivos de prueba maliciosos
- [x] Documentación completa

---

**Implementado:** 2025-10-16
**Versión:** 1.0
**Sistema:** Gestión de Auditorías - Cycloid Talent SAS
