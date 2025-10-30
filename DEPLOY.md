# Guía de Despliegue a Producción

## 📋 Checklist Pre-Despliegue

### 1. Configuración del Servidor Web

#### Apache
Asegúrate de que el DocumentRoot apunte a la carpeta `public`:
```apache
<VirtualHost *:80>
    ServerName auditorias.cycloidtalent.com
    DocumentRoot /var/www/auditorias/public

    <Directory /var/www/auditorias/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Redirigir HTTP a HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</VirtualHost>

<VirtualHost *:443>
    ServerName auditorias.cycloidtalent.com
    DocumentRoot /var/www/auditorias/public

    <Directory /var/www/auditorias/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Configuración SSL
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    SSLCertificateChainFile /path/to/ca-bundle.crt
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name auditorias.cycloidtalent.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name auditorias.cycloidtalent.com;

    root /var/www/auditorias/public;
    index index.php index.html;

    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 2. Configuración del Archivo .env

1. Copia el archivo `.env.production.example` a `.env` en el servidor:
```bash
cp .env.production.example .env
```

2. Edita el archivo `.env` y configura:

```bash
# Ambiente
CI_ENVIRONMENT = production

# URL de la aplicación (MUY IMPORTANTE)
app.baseURL = 'https://auditorias.cycloidtalent.com/'

# Base de datos
database.default.hostname = localhost
database.default.database = cycloid_auditorias
database.default.username = usuario_produccion
database.default.password = password_seguro

# Email
sendgrid.apiKey = 'TU_API_KEY_REAL'
```

3. Genera una clave de encriptación:
```bash
php spark key:generate
```

### 3. Permisos de Archivos

```bash
# Asignar permisos correctos
sudo chown -R www-data:www-data /var/www/auditorias
sudo chmod -R 755 /var/www/auditorias
sudo chmod -R 775 /var/www/auditorias/writable
```

### 4. Base de Datos

1. Importa la base de datos:
```bash
mysql -u usuario -p cycloid_auditorias < database_backup.sql
```

2. Ejecuta las migraciones si las hay:
```bash
php spark migrate
```

### 5. Verificación de URLs en Emails

Las URLs en los emails se generan automáticamente usando `site_url()` y `base_url()`, que toman el valor de `app.baseURL` en el `.env`.

**En local:**
- `.env` tiene: `app.baseURL = 'http://localhost/auditorias/public/'`
- Los emails mostrarán: `http://localhost/auditorias/public/index.php/login`

**En producción:**
- `.env` debe tener: `app.baseURL = 'https://auditorias.cycloidtalent.com/'`
- Los emails mostrarán: `https://auditorias.cycloidtalent.com/login`

### 6. Archivos Estáticos (Favicon, CSS, JS, Imágenes)

Todos los archivos estáticos usan `base_url()`, por lo que se adaptarán automáticamente:

**En local:**
```html
<link rel="icon" href="http://localhost/auditorias/public/assets/images/brand/favicon.ico">
```

**En producción:**
```html
<link rel="icon" href="https://auditorias.cycloidtalent.com/assets/images/brand/favicon.ico">
```

### 7. Eliminar Rutas de Test

En producción, edita `app/Config/Routes.php` y comenta o elimina:

```php
// ELIMINAR O COMENTAR EN PRODUCCIÓN:
/*
$routes->group('test-upload', function ($routes) {
    // ...
});

$routes->group('test-email', function ($routes) {
    // ...
});
*/
```

### 8. Configuración de PHP

Asegúrate de que `php.ini` tenga:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M

; En producción
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; IMPORTANTE: Habilitar extensión fileinfo (requerida para subida de archivos)
extension=fileinfo
```

**Verificar que la extensión fileinfo esté habilitada:**

```bash
# En el servidor
php -m | grep fileinfo

# Debería mostrar: fileinfo
```

Si no está habilitada, edita el archivo `php.ini` y descomenta o agrega:
```ini
extension=fileinfo
```

Luego reinicia PHP-FPM o Apache:
```bash
# Para PHP-FPM
sudo systemctl restart php8.1-fpm

# Para Apache
sudo systemctl restart apache2
```

### 9. Verificación Final

1. Prueba el acceso a la aplicación:
```
https://auditorias.cycloidtalent.com/
```

2. Verifica que redirija a login:
```
https://auditorias.cycloidtalent.com/login
```

3. Prueba el favicon:
```
https://auditorias.cycloidtalent.com/assets/images/brand/favicon.ico
```

4. Envía un email de prueba y verifica que las URLs sean correctas

5. Verifica los logs:
```bash
tail -f /var/www/auditorias/writable/logs/log-*.log
```

## 🔒 Seguridad

- [ ] SSL/TLS configurado correctamente
- [ ] `.env` no accesible desde web
- [ ] Directorio `writable/` no accesible desde web
- [ ] `CI_ENVIRONMENT = production`
- [ ] Contraseñas de base de datos seguras
- [ ] API Keys de SendGrid válidas y seguras
- [ ] Rutas de test eliminadas o comentadas
- [ ] Permisos de archivos correctos

## 📧 Soporte

Si tienes problemas con las URLs en los emails, verifica:

1. ✅ El archivo `.env` tiene `app.baseURL` correctamente configurado
2. ✅ El valor de `app.baseURL` termina con `/`
3. ✅ El servidor web (Apache/Nginx) tiene el DocumentRoot apuntando a `/public`
4. ✅ El archivo `.htaccess` existe en la carpeta `public/`
5. ✅ El módulo `mod_rewrite` está habilitado (Apache)

---

**Última actualización:** 2025-10-30
