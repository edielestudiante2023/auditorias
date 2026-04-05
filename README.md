# Sistema de Gestion de Auditorias SG-SST

Sistema web para la gestion integral de auditorias de Seguridad y Salud en el Trabajo (SG-SST), desarrollado para **Cycloid Talent SAS**.

## Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.1+ con CodeIgniter 4.6.3 |
| Base de datos | MySQL 8.0 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04) / Apache (XAMPP local) |
| Email | SendGrid API v3 |
| PDF | DomPDF 3.1 |
| Frontend | Bootstrap 5.3.2 + Bootstrap Icons |
| Testing | PHPUnit 10.5 |

## Modulos principales

### Modulo Administrador (`app/Controllers/Admin/`)
- Gestion de usuarios (Admin, Consultor, Proveedor)
- Gestion de clientes y proveedores
- Configuracion de contratos y servicios
- Banco de items de auditoria
- Dashboard administrativo con reportes

### Modulo Consultor (`app/Controllers/Consultor/`)
- Creacion y configuracion de auditorias
- Asignacion de clientes, proveedores e items
- Revision y calificacion de auditorias por cliente
- Generacion de PDFs de informe por cliente
- Envio de informes por email con CC automatico

### Modulo Proveedor (`app/Controllers/Proveedor/`)
- Visualizacion de auditorias asignadas
- Diligenciamiento de items con comentarios
- Carga de evidencias (global y por cliente)
- Seguimiento de progreso en tiempo real
- Gestion de personal asignado

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultor | Gestion de auditorias asignadas + revision + PDFs + email |
| proveedor | Portal de auditorias + items + evidencias + personal |

## Estructura del proyecto

```
auditorias/
├── app/
│   ├── Commands/          # Comandos CLI (ResetPassword)
│   ├── Config/            # Routes.php, Database.php, Filters.php, Security.php
│   ├── Controllers/
│   │   ├── Admin/         # 10 controladores administrativos
│   │   ├── Consultor/     # 3 controladores de consultor
│   │   └── Proveedor/     # 4 controladores de proveedor
│   ├── Database/
│   │   ├── Migrations/    # 37 migraciones
│   │   └── Seeds/         # 13 seeders
│   ├── Filters/           # AuthFilter, RoleFilter, CsrfExceptionFilter
│   ├── Models/            # 20 modelos
│   ├── Services/          # EmailService, PdfService, UploadService
│   └── Views/             # Vistas por modulo (admin, consultor, proveedor, auth, pdf, emails)
├── public/                # Punto de entrada web (index.php, assets, css)
├── tests/                 # Tests PHPUnit (unit, database, security, session)
├── writable/              # Logs, cache, sesiones, uploads, reportes PDF
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

## Requisitos previos

- **PHP:** >= 8.1
- **MySQL:** >= 8.0
- **Composer:** 2.0+
- **Extensiones PHP:** mysqli, mbstring, json, curl, intl, gd, fileinfo (recomendada)

## Instalacion local

```bash
# 1. Clonar repositorio
git clone https://github.com/edielestudiante2023/auditorias.git
cd auditorias

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tu configuracion local

# 4. Ejecutar migraciones
php spark migrate

# 5. Ejecutar seeders (datos iniciales)
php spark db:seed MasterSeeder

# 6. Configurar permisos
chmod -R 775 writable/

# 7. Acceder al sistema
# http://localhost/auditorias/public/
```

## Variables de entorno

| Variable | Descripcion |
|----------|-------------|
| `CI_ENVIRONMENT` | Ambiente: development / production |
| `app.baseURL` | URL base de la aplicacion |
| `app.appTimezone` | Zona horaria (America/Bogota) |
| `database.default.*` | Credenciales de base de datos |
| `email.fromEmail` | Email remitente para notificaciones |
| `email.fromName` | Nombre remitente |
| `sendgrid.apiKey` | API Key de SendGrid |
| `tutorial.videoUrl` | URL del video tutorial para proveedores |

## Deploy a produccion

**Servidor:** server1.cycloidtalent.com (66.29.154.174)
**Ruta:** `/www/wwwroot/auditorias`
**URL:** https://auditorias.cycloidtalent.com/

Ver documentacion completa en [DEPLOY.md](DEPLOY.md)

### Verificacion pre-deploy
```bash
php check-server.php
php pre_flight_check.php
```

## Documentacion adicional

| Archivo | Descripcion |
|---------|-------------|
| [DEPLOY.md](DEPLOY.md) | Guia completa de deploy a produccion |
| [TROUBLESHOOTING.md](TROUBLESHOOTING.md) | Solucion de problemas comunes |
| [SERVER-SETUP.md](SERVER-SETUP.md) | Configuracion del servidor |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Guia de contribucion |
| [docs/HARDENING-auditorias.md](docs/HARDENING-auditorias.md) | Documento de hardening del repositorio |

## Licencia

Propietario: Cycloid Talent SAS. Todos los derechos reservados 2024-2026.
