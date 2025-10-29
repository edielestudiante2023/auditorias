# Configuración PHP para Uploads

## Ajustes Requeridos en php.ini

Para permitir la subida de archivos de hasta **20 MB**, debes configurar los siguientes valores en tu archivo `php.ini`:

### Ubicación del php.ini en XAMPP:
```
C:\xampp\php\php.ini
```

### Valores Recomendados:

```ini
; Tamaño máximo de archivos que pueden subirse
upload_max_filesize = 20M

; Tamaño máximo de datos POST (debe ser mayor o igual a upload_max_filesize)
post_max_size = 25M

; Memoria máxima que puede usar un script (debe ser mayor que post_max_size)
memory_limit = 256M

; Tiempo máximo de ejecución de un script (para archivos grandes)
max_execution_time = 300

; Tiempo máximo de espera para datos de entrada
max_input_time = 300

; Habilitar la extensión fileinfo (necesaria para validación MIME)
extension=fileinfo
```

---

## Cómo Editar php.ini

### Paso 1: Detener Apache
1. Abre el **XAMPP Control Panel**
2. Haz clic en **Stop** en Apache

### Paso 2: Editar php.ini
1. Abre `C:\xampp\php\php.ini` con un editor de texto
2. Busca cada directiva (usa Ctrl+F):
   - `upload_max_filesize`
   - `post_max_size`
   - `memory_limit`
   - `max_execution_time`
   - `max_input_time`

3. Cambia los valores según lo recomendado arriba

### Ejemplo de búsqueda y edición:

**Antes:**
```ini
upload_max_filesize = 2M
post_max_size = 8M
```

**Después:**
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

### Paso 3: Verificar fileinfo
Busca la línea:
```ini
;extension=fileinfo
```

Si tiene `;` al inicio, quítalo para habilitarla:
```ini
extension=fileinfo
```

### Paso 4: Guardar y Reiniciar Apache
1. Guarda el archivo `php.ini`
2. En el **XAMPP Control Panel**, haz clic en **Start** en Apache
3. Verifica que Apache inicie correctamente

---

## Verificar la Configuración

### Método 1: Crear archivo phpinfo.php

1. Crea un archivo en `c:\xampp\htdocs\phpinfo.php`:

```php
<?php
phpinfo();
?>
```

2. Accede a: http://localhost/phpinfo.php

3. Busca las siguientes directivas y verifica sus valores:
   - `upload_max_filesize` → debe mostrar **20M**
   - `post_max_size` → debe mostrar **25M**
   - `memory_limit` → debe mostrar **256M**
   - `fileinfo` → debe aparecer en la sección "Loaded extensions"

4. **IMPORTANTE:** Elimina el archivo `phpinfo.php` después de verificar (seguridad)

### Método 2: Usar script de verificación

Crea `c:\xampp\htdocs\auditorias\public\verificar_config.php`:

```php
<?php
echo "<h2>Configuración de Uploads</h2>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "<li>max_execution_time: " . ini_get('max_execution_time') . " segundos</li>";
echo "<li>max_input_time: " . ini_get('max_input_time') . " segundos</li>";
echo "</ul>";

// Verificar si fileinfo está cargado
if (extension_loaded('fileinfo')) {
    echo "<p style='color: green;'>✓ Extensión fileinfo está HABILITADA</p>";
} else {
    echo "<p style='color: red;'>✗ Extensión fileinfo NO está habilitada</p>";
}

// Verificar permisos de escritura en writable/uploads
$uploadDir = '../writable/uploads/';
if (is_writable($uploadDir)) {
    echo "<p style='color: green;'>✓ Directorio uploads tiene permisos de ESCRITURA</p>";
} else {
    echo "<p style='color: red;'>✗ Directorio uploads NO tiene permisos de escritura</p>";
}
?>
```

Accede a: http://localhost/auditorias/public/verificar_config.php

---

## Verificar Permisos de Directorios

En Windows (XAMPP), generalmente no hay problemas de permisos, pero verifica:

### Permisos en writable/uploads:
```
c:\xampp\htdocs\auditorias\writable\uploads\
```

**Debe tener permisos de escritura para el usuario de Apache**

Si hay problemas:
1. Clic derecho en la carpeta `writable`
2. **Propiedades** → **Seguridad**
3. Asegúrate que "Users" o "Todos" tengan permisos de **Modificar** y **Escribir**

---

## Configuración para Producción (Linux/Ubuntu)

Si vas a desplegar en servidor Linux:

### php.ini en Linux (ubicaciones comunes):
- `/etc/php/8.1/apache2/php.ini` (Apache)
- `/etc/php/8.1/fpm/php.ini` (PHP-FPM)

### Permisos en Linux:
```bash
# Dar permisos al directorio uploads
sudo chown -R www-data:www-data /var/www/auditorias/writable/uploads
sudo chmod -R 755 /var/www/auditorias/writable/uploads
```

### Reiniciar servicios en Linux:
```bash
# Apache
sudo systemctl restart apache2

# PHP-FPM (si usas Nginx)
sudo systemctl restart php8.1-fpm
```

---

## Troubleshooting

### Error: "The uploaded file exceeds the upload_max_filesize directive"
**Solución:** Aumenta `upload_max_filesize` en php.ini

### Error: "POST Content-Length exceeds the limit"
**Solución:** Aumenta `post_max_size` en php.ini

### Error: "Call to undefined function finfo_open()"
**Solución:** Habilita la extensión `fileinfo` en php.ini

### Los archivos no se guardan
**Solución:** Verifica permisos de escritura en `writable/uploads/`

### Cambios en php.ini no se aplican
**Solución:**
1. Verifica que estás editando el php.ini correcto (usa `phpinfo()` para ver la ruta)
2. Reinicia Apache completamente (Stop → Start, no solo Restart)

---

## Valores Ajustables Según Necesidad

Si necesitas **archivos más grandes** (por ejemplo, 50 MB):

```ini
upload_max_filesize = 50M
post_max_size = 55M
memory_limit = 512M
max_execution_time = 600
max_input_time = 600
```

**NOTA:** Ajusta también el valor en `UploadService.php` constructor:
```php
protected int $maxFileSize = 52428800; // 50 * 1024 * 1024
```

O usa el método dinámico:
```php
$uploadService = new UploadService();
$uploadService->setMaxFileSize(50); // 50 MB
```

---

## Checklist de Configuración

- [ ] `upload_max_filesize = 20M` en php.ini
- [ ] `post_max_size = 25M` en php.ini
- [ ] `memory_limit = 256M` en php.ini
- [ ] `extension=fileinfo` habilitada
- [ ] Apache reiniciado
- [ ] Configuración verificada con `phpinfo()`
- [ ] Directorio `writable/uploads/` con permisos de escritura
- [ ] `.htaccess` en `writable/uploads/` para seguridad
- [ ] Prueba de subida funcional

---

**Fecha:** 2025-10-14
**Framework:** CodeIgniter 4
**Sistema:** Auditorías - Cycloid Talent SAS
