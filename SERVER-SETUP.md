# Server Configuration Guide - Cycloid Auditorías

Complete guide for deploying the application on different server environments.

---

## Table of Contents

1. [Apache VirtualHost (Recommended)](#1-apache-virtualhost-recommended)
2. [Nginx Server Block](#2-nginx-server-block)
3. [Shared Hosting Fallback](#3-shared-hosting-fallback-htaccess)
4. [Health Check Verification](#4-health-check-verification)
5. [Troubleshooting](#5-troubleshooting)

---

## 1. Apache VirtualHost (Recommended)

### Prerequisites

- Apache 2.4+
- PHP 8.1+
- mod_rewrite enabled
- mod_headers enabled (optional, for security headers)

### Installation Steps

#### Windows (XAMPP)

1. **Open XAMPP Apache config:**
   ```
   C:\xampp\apache\conf\extra\httpd-vhosts.conf
   ```

2. **Add to the end of the file:**
   ```apache
   # Copy the contents of apache-vhost.conf
   ```

3. **Edit your hosts file:**
   ```
   C:\Windows\System32\drivers\etc\hosts
   ```
   Add:
   ```
   127.0.0.1  auditorias.local
   ```

4. **Update paths in the VirtualHost:**
   Replace `C:/xampp/htdocs/auditorias` with your actual path

5. **Restart Apache:**
   - Open XAMPP Control Panel
   - Stop and Start Apache

#### Linux (Ubuntu/Debian)

1. **Copy configuration file:**
   ```bash
   sudo cp apache-vhost.conf /etc/apache2/sites-available/auditorias.conf
   ```

2. **Update paths in the file:**
   ```bash
   sudo nano /etc/apache2/sites-available/auditorias.conf
   ```
   Replace paths with:
   ```apache
   DocumentRoot "/var/www/auditorias/public"
   <Directory "/var/www/auditorias/public">
   ```

3. **Enable required modules:**
   ```bash
   sudo a2enmod rewrite
   sudo a2enmod headers
   sudo a2enmod expires
   ```

4. **Enable the site:**
   ```bash
   sudo a2ensite auditorias.conf
   ```

5. **Test configuration:**
   ```bash
   sudo apachectl configtest
   ```

6. **Reload Apache:**
   ```bash
   sudo systemctl reload apache2
   ```

7. **Edit hosts file (for local development):**
   ```bash
   sudo nano /etc/hosts
   ```
   Add:
   ```
   127.0.0.1  auditorias.local
   ```

#### macOS

1. **Copy configuration file:**
   ```bash
   sudo cp apache-vhost.conf /etc/apache2/extra/httpd-vhosts.conf
   # OR append to existing file
   ```

2. **Enable vhosts in Apache config:**
   ```bash
   sudo nano /etc/apache2/httpd.conf
   ```
   Uncomment:
   ```apache
   Include /private/etc/apache2/extra/httpd-vhosts.conf
   ```

3. **Enable mod_rewrite:**
   Uncomment in httpd.conf:
   ```apache
   LoadModule rewrite_module libexec/apache2/mod_rewrite.so
   ```

4. **Test and restart:**
   ```bash
   sudo apachectl configtest
   sudo apachectl restart
   ```

### Verification

```bash
curl http://auditorias.local/health
```

Expected response:
```json
{
  "status": "ok",
  "timestamp": "2025-01-16 10:30:00",
  "app": "Cycloid Auditorías",
  "version": "1.0.0",
  "environment": "development",
  "base_url": "http://auditorias.local/"
}
```

---

## 2. Nginx Server Block

### Prerequisites

- Nginx 1.18+
- PHP-FPM 8.1+
- php-fpm service running

### Installation Steps

#### Linux (Ubuntu/Debian)

1. **Install Nginx and PHP-FPM:**
   ```bash
   sudo apt update
   sudo apt install nginx php8.2-fpm
   ```

2. **Copy configuration file:**
   ```bash
   sudo cp nginx-server.conf /etc/nginx/sites-available/auditorias
   ```

3. **Update paths and PHP version:**
   ```bash
   sudo nano /etc/nginx/sites-available/auditorias
   ```

   Update:
   - `root /var/www/auditorias/public;`
   - `fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;` (match your PHP version)

4. **Find your PHP-FPM socket:**
   ```bash
   sudo find /var/run -name "php*fpm.sock"
   ```

5. **Create symbolic link:**
   ```bash
   sudo ln -s /etc/nginx/sites-available/auditorias /etc/nginx/sites-enabled/
   ```

6. **Test configuration:**
   ```bash
   sudo nginx -t
   ```

7. **Reload Nginx:**
   ```bash
   sudo systemctl reload nginx
   ```

8. **Ensure PHP-FPM is running:**
   ```bash
   sudo systemctl status php8.2-fpm
   sudo systemctl start php8.2-fpm  # if not running
   ```

#### macOS (Homebrew)

1. **Install Nginx and PHP:**
   ```bash
   brew install nginx php@8.2
   ```

2. **Copy configuration:**
   ```bash
   sudo cp nginx-server.conf /usr/local/etc/nginx/servers/auditorias.conf
   ```

3. **Update PHP-FPM socket location:**
   ```nginx
   fastcgi_pass unix:/usr/local/var/run/php-fpm.sock;
   ```

4. **Test and reload:**
   ```bash
   nginx -t
   nginx -s reload
   ```

### Verification

```bash
curl http://auditorias.local/health
```

---

## 3. Shared Hosting Fallback (.htaccess)

### When to Use

Use this method ONLY when:
- You cannot modify Apache VirtualHost configuration
- You're on shared hosting without root access
- You cannot set DocumentRoot to public/ folder

### Installation Steps

1. **Rename the fallback file:**
   ```bash
   mv .htaccess.root .htaccess
   ```

2. **Verify mod_rewrite is enabled:**
   Contact your hosting provider if uncertain

3. **Update RewriteBase if in subdirectory:**

   If your app is at `http://domain.com/myapp/`:
   ```apache
   RewriteBase /myapp/
   ```

4. **Set correct permissions:**
   ```bash
   chmod 644 .htaccess
   ```

### ⚠️ Security Warning

This method exposes sensitive directories to the web. Ensure:
- Directory listing is disabled (Options -Indexes)
- All deny rules in .htaccess are working
- File permissions prevent direct access

### Verification

```bash
curl http://yourdomain.com/health
# OR
curl http://yourdomain.com/myapp/health
```

---

## 4. Health Check Verification

The `/health` endpoint provides server configuration verification.

### Test Commands

**Basic test:**
```bash
curl http://auditorias.local/health
```

**With headers:**
```bash
curl -I http://auditorias.local/health
```

**Pretty print JSON:**
```bash
curl http://auditorias.local/health | python -m json.tool
# OR
curl http://auditorias.local/health | jq
```

### Expected Response

```json
{
  "status": "ok",
  "timestamp": "2025-01-16 10:30:00",
  "app": "Cycloid Auditorías",
  "version": "1.0.0",
  "environment": "development",
  "base_url": "http://auditorias.local/"
}
```

### Response Fields

- `status`: "ok" if server is responding
- `timestamp`: Current server time
- `app`: Application name
- `version`: Current version
- `environment`: development/production/testing
- `base_url`: Detected base URL (verifies auto-detection)

---

## 5. Troubleshooting

### Apache Issues

**Problem:** 500 Internal Server Error

**Solutions:**
```bash
# Check error logs
tail -f /var/log/apache2/error.log  # Linux
tail -f C:/xampp/apache/logs/error.log  # Windows

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check AllowOverride
# In Apache config, ensure: AllowOverride All

# Test configuration
apachectl configtest
```

**Problem:** VirtualHost not working

**Solutions:**
```bash
# Check if VirtualHost is loaded
apachectl -S

# Verify hosts file
cat /etc/hosts  # Linux/Mac
type C:\Windows\System32\drivers\etc\hosts  # Windows

# Ensure site is enabled
sudo a2ensite auditorias.conf
```

### Nginx Issues

**Problem:** 502 Bad Gateway

**Solutions:**
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm
sudo systemctl start php8.2-fpm

# Check PHP-FPM socket location
sudo find /var/run -name "php*fpm.sock"

# Update fastcgi_pass in nginx config
sudo nano /etc/nginx/sites-available/auditorias

# Check Nginx error log
tail -f /var/log/nginx/auditorias-error.log
```

**Problem:** 404 Not Found for all routes

**Solutions:**
```bash
# Verify root path is correct
# Should point to: /path/to/project/public

# Check try_files directive
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Test Nginx config
sudo nginx -t
```

### .htaccess Issues

**Problem:** Rewrites not working

**Solutions:**
```apache
# Add at top of .htaccess
Options +FollowSymLinks

# Set RewriteBase manually
RewriteBase /

# For subdirectory
RewriteBase /myapp/

# Check mod_rewrite
apache2 -M | grep rewrite  # Linux
httpd -M | grep rewrite  # Windows
```

**Problem:** Access to app/ or writable/ directories

**Solutions:**
```apache
# Verify deny rules exist in .htaccess
RewriteRule ^app/(.*)$ - [F,L]
RewriteRule ^writable/(.*)$ - [F,L]

# Test directly
curl http://yourdomain.com/app/Config/App.php
# Should return: 403 Forbidden
```

### Base URL Issues

**Problem:** Wrong base URL detected

**Solutions:**

1. **Set in .env file:**
   ```ini
   app.baseURL = 'http://yourdomain.com/'
   ```

2. **Check detection:**
   ```bash
   curl http://yourdomain.com/health | grep base_url
   ```

3. **For subfolder deployments:**
   ```ini
   app.baseURL = 'http://yourdomain.com/myapp/'
   ```

### Permission Issues

**Problem:** Can't write to writable/ folder

**Solutions:**
```bash
# Linux/Mac - set correct ownership
sudo chown -R www-data:www-data /var/www/auditorias/writable
sudo chmod -R 755 /var/www/auditorias/writable

# Or with current user
sudo chown -R $USER:www-data /var/www/auditorias/writable
sudo chmod -R 775 /var/www/auditorias/writable

# Windows (XAMPP) - usually no issues
# Just ensure folder exists and is writable
```

### Static Assets Not Loading

**Problem:** CSS/JS/Images return 404

**Solutions:**

1. **Verify files exist in public/ folder:**
   ```bash
   ls -la public/css
   ls -la public/js
   ```

2. **Check browser console for 404s**

3. **Verify file permissions:**
   ```bash
   chmod 644 public/css/*
   chmod 644 public/js/*
   ```

4. **Clear browser cache:**
   - Ctrl+Shift+R (hard reload)
   - Or clear cache in browser settings

---

## Production Deployment Checklist

- [ ] Set `CI_ENVIRONMENT = production` in .env
- [ ] Set proper `app.baseURL` in .env
- [ ] Disable test routes (remove test-upload group)
- [ ] Enable HTTPS in VirtualHost/Server Block
- [ ] Configure SSL certificates
- [ ] Set up automatic SSL renewal (Let's Encrypt)
- [ ] Configure database backups
- [ ] Set proper file permissions
- [ ] Enable error logging
- [ ] Disable PHP errors to screen
- [ ] Configure security headers
- [ ] Set up monitoring
- [ ] Test all redirects work with HTTPS
- [ ] Test health endpoint: `curl https://yourdomain.com/health`

---

## Quick Reference

### Important Paths

```
Project Root:       C:\xampp\htdocs\auditorias
Public Folder:      C:\xampp\htdocs\auditorias\public
App Folder:         C:\xampp\htdocs\auditorias\app
Writable Folder:    C:\xampp\htdocs\auditorias\writable
```

### Important URLs

```
Homepage:           http://auditorias.local/
Health Check:       http://auditorias.local/health
Login:              http://auditorias.local/login
Admin Dashboard:    http://auditorias.local/admin/dashboard
```

### Configuration Files

```
Apache VirtualHost: apache-vhost.conf
Nginx Server:       nginx-server.conf
Root .htaccess:     .htaccess.root (rename to .htaccess)
Environment:        .env
App Config:         app/Config/App.php
Routes:             app/Config/Routes.php
```

---

## Support

For issues not covered here:

1. Check CodeIgniter 4 documentation: https://codeigniter.com/user_guide/
2. Check application logs: `writable/logs/`
3. Check server error logs
4. Enable debugging in .env: `CI_ENVIRONMENT = development`

---

**Last Updated:** 2025-01-16
**Version:** 1.0.0
