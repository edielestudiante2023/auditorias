# 🔧 Solución de Problemas Comunes

## Error: Call to undefined function finfo_open()

### Descripción del Error

```
Error
Call to undefined function App\Services\finfo_open()
APPPATH/Services/UploadService.php at line 505
```

### ¿Por qué ocurre?

Este error ocurre cuando la extensión `fileinfo` de PHP no está habilitada en el servidor. Esta extensión es necesaria para detectar el tipo MIME de archivos subidos.

**¿Por qué funciona en local pero no en producción?**
- En XAMPP (Windows), la extensión `fileinfo` viene habilitada por defecto
- En muchos servidores Linux, esta extensión puede estar deshabilitada por defecto

### ✅ Solución 1: El código ya está corregido

El código en `UploadService.php` ya incluye alternativas que funcionan sin `fileinfo`:

```php
public function isImage(array $file): bool
{
    // Intenta usar finfo si está disponible
    if (function_exists('finfo_open')) {
        // Usa finfo_open
    }

    // Si no está disponible, usa alternativas
    // Valida por MIME type del $_FILES
    // O valida por extensión del archivo
}
```

**Esto significa que la aplicación funcionará incluso sin fileinfo habilitado.**

### ✅ Solución 2: Habilitar la extensión fileinfo (Recomendado)

Para mejor seguridad y detección de archivos, es recomendable habilitar `fileinfo`:

#### En Linux (Ubuntu/Debian)

1. **Verificar si fileinfo está instalado:**
```bash
php -m | grep fileinfo
```

2. **Si no aparece, instalar la extensión:**
```bash
# Para PHP 8.1
sudo apt-get install php8.1-fileinfo

# Para PHP 8.2
sudo apt-get install php8.2-fileinfo

# Para PHP 8.3
sudo apt-get install php8.3-fileinfo
```

3. **Editar php.ini y habilitar la extensión:**
```bash
# Encontrar el archivo php.ini
php --ini

# Editar el archivo
sudo nano /etc/php/8.1/apache2/php.ini
# O para PHP-FPM:
sudo nano /etc/php/8.1/fpm/php.ini
```

4. **Agregar o descomentar esta línea:**
```ini
extension=fileinfo
```

5. **Reiniciar el servicio web:**
```bash
# Para Apache
sudo systemctl restart apache2

# Para PHP-FPM + Nginx
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

#### En CentOS/RHEL

```bash
# Instalar la extensión
sudo yum install php-fileinfo

# Reiniciar Apache
sudo systemctl restart httpd

# O reiniciar PHP-FPM
sudo systemctl restart php-fpm
```

#### En cPanel/WHM

1. Ir a: **WHM → Software → EasyApache 4**
2. Buscar: **PHP Extensions**
3. Habilitar: **php-fileinfo**
4. Hacer clic en: **Provision**

#### En Plesk

1. Ir a: **Tools & Settings → Updates**
2. Seleccionar: **Add/Remove Components**
3. Buscar y habilitar: **fileinfo** para tu versión de PHP
4. Hacer clic en: **Continue**

### ✅ Solución 3: Verificar después de habilitar

```bash
# Verificar que fileinfo esté cargado
php -m | grep fileinfo

# O crear un archivo phpinfo
echo "<?php phpinfo(); ?>" > /var/www/html/info.php
```

Visita: `https://tudominio.com/info.php` y busca "fileinfo"

**IMPORTANTE:** Elimina el archivo `info.php` después de verificar por seguridad.

### 📊 Comparación de Métodos

| Método | Seguridad | Precisión | Requiere Extensión |
|--------|-----------|-----------|-------------------|
| finfo_open() | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Sí (fileinfo) |
| $_FILES['type'] | ⭐⭐⭐ | ⭐⭐⭐ | No |
| Extensión | ⭐⭐ | ⭐⭐ | No |

**Recomendación:** Habilitar `fileinfo` para máxima seguridad.

## Otros Problemas Comunes

### Error: "413 Request Entity Too Large"

**Causa:** Límites de subida muy pequeños.

**Solución:**
```ini
# En php.ini
upload_max_filesize = 10M
post_max_size = 10M

# En nginx.conf (si usas Nginx)
client_max_body_size 10M;
```

### Error: "The uploaded file exceeds the upload_max_filesize"

**Causa:** Archivo muy grande.

**Solución:** Aumentar límites en `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
```

### Error: "Failed to open stream: Permission denied"

**Causa:** Permisos incorrectos en carpeta `writable/`.

**Solución:**
```bash
sudo chown -R www-data:www-data /var/www/auditorias/writable
sudo chmod -R 775 /var/www/auditorias/writable
```

### Base URL incorrecta en producción

**Síntoma:** Links y emails muestran `localhost` en lugar del dominio real.

**Solución:** Editar `.env` en producción:
```bash
app.baseURL = 'https://auditorias.cycloidtalent.com/'
```

### Emails no se envían

**Causa:** API Key de SendGrid no configurada o inválida.

**Solución:**
1. Verificar `.env`:
```bash
sendgrid.apiKey = 'SG.XXXXXXXXXXXXX'
```

2. Verificar logs:
```bash
tail -f writable/logs/log-*.log
```

3. Verificar modo log-only:
- Si no hay API key, los emails se guardan en logs pero no se envían
- Buscar en logs: `writable/uploads/emails/`

## 🆘 Soporte

Si el problema persiste:

1. Revisa los logs:
```bash
tail -f /var/www/auditorias/writable/logs/log-*.log
```

2. Verifica la configuración de PHP:
```bash
php -i | grep fileinfo
php -i | grep upload
```

3. Contacta al equipo de desarrollo con:
   - Mensaje de error completo
   - Logs relevantes
   - Versión de PHP: `php -v`
   - Sistema operativo: `uname -a`

---

**Última actualización:** 2025-10-30
