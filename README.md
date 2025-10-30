# Sistema de Gestión de Auditorías SG-SST

Sistema web para la gestión integral de auditorías de Seguridad y Salud en el Trabajo (SG-SST), desarrollado para **Cycloid Talent SAS**.

## 📋 Descripción

Plataforma completa que permite a consultores SST gestionar auditorías para múltiples proveedores y clientes, con generación automática de informes PDF y notificaciones por correo electrónico.

## 🚀 Características Principales

### Módulo Administrador
- ✅ Gestión de usuarios (Admin, Consultor, Proveedor)
- ✅ Gestión de clientes y proveedores
- ✅ Configuración de contratos
- ✅ Gestión del banco de ítems de auditoría

### Módulo Consultor
- ✅ Creación y configuración de auditorías
- ✅ Asignación de clientes y proveedores
- ✅ Selección de ítems del banco (globales y por cliente)
- ✅ Revisión y calificación de auditorías
- ✅ Generación de PDFs por cliente bajo demanda
- ✅ Envío de informes PDF a clientes con CC automático
- ✅ Gestión de perfil y firma digital

### Módulo Proveedor
- ✅ Visualización de auditorías asignadas
- ✅ Diligenciamiento de ítems con comentarios
- ✅ Carga de evidencias (global y por cliente)
- ✅ Seguimiento de progreso en tiempo real
- ✅ Finalización y envío a revisión
- ✅ Gestión de información empresarial

## 🛠️ Tecnologías

- **Framework:** CodeIgniter 4.6.3
- **PHP:** 8.x
- **Base de Datos:** MySQL 8.x
- **Frontend:** Bootstrap 5.3.2, Bootstrap Icons
- **PDF:** DomPDF
- **Email:** SendGrid API

## 📦 Instalación

### Requisitos Previos
- **PHP:** >= 8.1
- **MySQL:** >= 8.0
- **Composer:** 2.0+
- **Extensiones PHP requeridas:**
  - mysqli, mbstring, json, curl, intl, gd
  - fileinfo (recomendada)

### Configuración Local

1. **Clonar repositorio**
```bash
git clone <url-repositorio>
cd auditorias
```

2. **Instalar dependencias**
```bash
composer install
```

3. **Configurar archivo .env**
```bash
cp .env.example .env
# Editar .env con tu configuración
```

4. **Importar base de datos**
```bash
mysql -u root -p cycloid_auditorias < database.sql
```

5. **Configurar permisos**
```bash
chmod -R 775 writable/
```

6. **Acceder al sistema**
```
http://localhost/auditorias/public/
```

## 🚀 Despliegue a Producción

Ver documentación completa en [DEPLOY.md](DEPLOY.md)

### Verificación del Servidor

Antes de desplegar, ejecuta:
```bash
php check-server.php
```

## 🐛 Solución de Problemas

### Error: "Call to undefined function finfo_open()"

Este error ocurre cuando la extensión `fileinfo` no está habilitada.

**Solución:**
```bash
# En Linux
sudo apt-get install php-fileinfo
sudo systemctl restart apache2
```

El sistema tiene fallbacks que funcionan sin esta extensión, pero se recomienda habilitarla para mejor seguridad.

Ver [TROUBLESHOOTING.md](TROUBLESHOOTING.md) para más problemas comunes.

## 📚 Documentación

- [DEPLOY.md](DEPLOY.md) - Guía de despliegue a producción
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Solución de problemas
- [TUTORIAL_VIDEO.md](TUTORIAL_VIDEO.md) - Configuración de tutorial en video

## 🆘 Soporte

1. Revisar [TROUBLESHOOTING.md](TROUBLESHOOTING.md)
2. Ejecutar `php check-server.php`
3. Revisar logs: `writable/logs/log-*.log`
4. Contactar al equipo de desarrollo

## 📝 Licencia

Propietario: Cycloid Talent SAS. Todos los derechos reservados © 2024-2025
