# HARDENING DE REPOSITORIO — auditorias

**Fecha:** 2026-04-05
**Aplicativo:** auditorias — Sistema de Gestion de Auditorias SG-SST
**Empresa:** Cycloid Talent SAS
**Repositorio:** github.com/edielestudiante2023/auditorias
**Preparado para:** Edwin Lopez (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD (Gitea)
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Stack tecnologico

| Componente | Tecnologia |
| --- | --- |
| Backend | PHP 8.1+ con CodeIgniter 4.6.3 |
| Base de datos | MySQL 8.0 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04 en Hetzner) |
| Email | SendGrid API v3 |
| PDF | DomPDF 3.1 |
| Frontend | Bootstrap 5.3.2 + Bootstrap Icons |
| Testing | PHPUnit 10.5 + FakerPHP |

### Modulos principales (3)

| Modulo | Descripcion |
| --- | --- |
| Administrador | Gestion de usuarios, clientes, proveedores, contratos, servicios, banco de items, dashboard con reportes |
| Consultor | Creacion y configuracion de auditorias, asignacion de items, revision y calificacion, generacion de PDFs, envio de informes por email |
| Proveedor | Visualizacion de auditorias asignadas, diligenciamiento de items, carga de evidencias, seguimiento de progreso, gestion de personal |

### Roles de usuario

| Rol | Acceso |
| --- | --- |
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultor | Gestion de auditorias asignadas + revision + PDFs + email |
| proveedor | Portal de auditorias + items + evidencias + personal |

### Estructura del proyecto

```text
auditorias/
├── app/
│   ├── Commands/          # 1 comando (ResetPassword)
│   ├── Config/            # Routes, Database, Filters, Security, etc.
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
│   └── Views/             # Vistas por modulo
├── public/                # index.php, assets, css
├── tests/                 # PHPUnit (unit, database, security, session)
├── docs/                  # Documentacion tecnica
├── writable/              # Logs, cache, sesiones, uploads, reportes
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

### Cron jobs

| Comando | Frecuencia | Descripcion |
| --- | --- | --- |
| `php spark reset:password` | Manual | Resetear password de usuario |

> Nota: El sistema actualmente tiene un solo comando CLI. No hay cron jobs automatizados configurados.

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8.0.45 (DigitalOcean Managed)
**Base de datos:** cycloid_auditorias
**Tamano total:** 2.02 MB
**SSL:** Required
**Host:** db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com:25060

### Usuarios de base de datos

| Usuario | Permisos | Uso |
| --- | --- | --- |
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |

### Resumen

- **19 tablas** (BASE TABLE)
- **0 vistas** (VIEW)
- **29 foreign keys** definidas
- **1 tabla vacia** (migrations — tabla de control de CI4)

### Tablas por modulo funcional

**Nucleo (5 tablas):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| users | 49 | 48 KB |
| roles | 3 | 32 KB |
| clientes | 43 | 16 KB |
| consultores | 2 | 32 KB |
| servicios | 2 | 32 KB |

**Proveedores (2 tablas):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| proveedores | 37 | 16 KB |
| usuarios_proveedores | 37 | 48 KB |

**Auditorias (5 tablas):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| auditorias | 38 | 48 KB |
| auditoria_items | 740 | 224 KB |
| auditoria_clientes | 68 | 80 KB |
| auditoria_item_cliente | 551 | 256 KB |
| auditoria_log | 1,544 | 528 KB |

**Evidencias (2 tablas):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| evidencias | 444 | 176 KB |
| evidencias_cliente | 596 | 208 KB |

**Contratos (1 tabla):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| contratos_proveedor_cliente | 68 | 128 KB |

**Otros (3 tablas):**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| notificaciones | 147 | 80 KB |
| personal_asignado | 222 | 80 KB |
| items_banco | 18 | 16 KB |

**Control:**

| Tabla | Registros | Tamano |
| --- | --- | --- |
| migrations | 0 | 16 KB |

### Tabla central: auditorias

La tabla `auditorias` es la entidad central. Dependen de ella directamente:
- `auditoria_items` (items de cada auditoria)
- `auditoria_clientes` (clientes asignados a cada auditoria)
- `auditoria_log` (log de actividad)
- `notificaciones` (notificaciones del sistema)

### Arbol de foreign keys

```text
roles
  └── users
        ├── auditoria_log
        ├── consultores
        ├── contratos_proveedor_cliente
        └── usuarios_proveedores

clientes
  ├── auditoria_clientes
  ├── auditoria_item_cliente
  ├── contratos_proveedor_cliente
  └── personal_asignado

proveedores
  ├── auditorias
  ├── contratos_proveedor_cliente
  ├── personal_asignado
  └── usuarios_proveedores

consultores
  ├── auditorias
  └── contratos_proveedor_cliente

servicios
  ├── auditoria_clientes
  └── contratos_proveedor_cliente

auditorias
  ├── auditoria_items
  ├── auditoria_clientes
  ├── auditoria_log
  └── notificaciones

auditoria_items
  ├── auditoria_item_cliente
  └── evidencias

auditoria_item_cliente
  └── evidencias_cliente

items_banco
  └── auditoria_items

contratos_proveedor_cliente
  └── auditoria_clientes
```

### Tablas mas grandes por peso

| Tabla | Registros | Tamano |
| --- | --- | --- |
| auditoria_log | 1,544 | 528 KB |
| auditoria_item_cliente | 551 | 256 KB |
| auditoria_items | 740 | 224 KB |
| evidencias_cliente | 596 | 208 KB |
| evidencias | 444 | 176 KB |

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
| --- | --- | --- | --- |
| SendGrid | `sendgrid.apiKey` | 1 (EmailService.php) | Activa |
| Email config | `email.fromEmail`, `email.fromName` | 1 | Activa |
| Tutorial video | `tutorial.videoUrl` | 1 | Activa |

### SendGrid (servicio de email)

Usado en 1 archivo centralizado: `app/Services/EmailService.php`

**Patron:** `config('Email')->sendgridApiKey` y `getenv('sendgrid.apiKey')`

**Funcionalidades de email:**
- Notificaciones de auditoria
- Envio de informes PDF a clientes
- Notificaciones de firma
- Recordatorios
- Recuperacion de password

### HALLAZGOS CRITICOS DE SEGURIDAD

**CRITICO — SendGrid API Key en .env commiteado al historial:**

| Archivo | Problema |
| --- | --- |
| `.env` (historial git) | SendGrid API key visible en commits anteriores |

**CRITICO — Credenciales de BD de produccion expuestas:**

| Ubicacion | Problema |
| --- | --- |
| `.claude/settings.local.json` | Password de BD de DigitalOcean en archivo local |

**ALTO — Scripts con credenciales hardcodeadas en raiz:**

| Archivo | Problema |
| --- | --- |
| `debug_query.php` | `new mysqli("localhost", "root", "", "cycloid_auditorias")` |
| `check_items.php` | `new mysqli("localhost", "root", "", "cycloid_auditorias")` |
| `check_update.php` | `new mysqli("localhost", "root", "", "cycloid_auditorias")` |
| `final_debug.php` | `new mysqli("localhost", "root", "", "cycloid_auditorias")` |
| `check_autoincrement.php` | Conexion directa a BD |
| `check_all_tables.php` | Conexion directa a BD |
| `check_assigned_items.php` | Conexion directa a BD |
| `fix_auditoria_9.php` | Conexion directa a BD |
| `test_porcentaje_cumplimiento.php` | Conexion directa a BD |
| `test_sendgrid.php` | Posible API key hardcodeada |
| `test_admin_workflow.php` | Credenciales de admin hardcodeadas |

**MEDIO — Passwords en seeders:**

| Archivo | Problema |
| --- | --- |
| `app/Database/Seeds/UsersSeeder.php` | Password `password123` hardcodeado |
| `app/Database/Seeds/UsersAdminSeeder.php` | Password `Admin123*` hardcodeado |
| `app/Database/Seeds/AdminQuickSeed.php` | Password `Consultor123*` hardcodeado |

**MEDIO — Repositorio PUBLICO en GitHub:**

El repositorio `github.com/edielestudiante2023/auditorias` es **PUBLICO**. Las credenciales que alguna vez fueron commiteadas son visibles en el historial.

**Credenciales que deben rotarse:**

| Variable | Accion |
| --- | --- |
| `sendgrid.apiKey` | ROTAR INMEDIATAMENTE |
| `database.default.password` (produccion) | Rotar en DigitalOcean |
| Passwords de seeders | Cambiar en produccion |

---

## 4. DOCUMENTACION DEL PROYECTO

### Archivos creados en el repositorio

| Archivo | Descripcion |
| --- | --- |
| `README.md` | Documentacion principal: stack, modulos, roles, estructura, instalacion, deploy |
| `CONTRIBUTING.md` | Guia de contribucion: flujo de ramas, convencion de commits, reglas, proceso de revision |
| `.env.example` | Template con todas las variables de entorno necesarias (sin valores reales) |

### README.md incluye

- Stack tecnologico completo
- 3 modulos con descripcion detallada
- 3 roles de usuario con accesos
- Estructura de carpetas del proyecto
- Requisitos previos e instrucciones de instalacion
- 8 variables de entorno documentadas
- Instrucciones de deploy
- Links a documentacion adicional

### CONTRIBUTING.md incluye

- Flujo de ramas (main -> develop -> feature/ -> hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas
- 5 reglas (no push directo, no credenciales, no temporales, no destructivos)
- Proceso de revision con pipeline CI/CD
- Checklist antes de hacer PR

### .env.example incluye

- Variables de ambiente (CI_ENVIRONMENT)
- Configuracion de app (baseURL, timezone)
- Credenciales de BD (sin valores, con placeholders)
- Configuracion de email/SendGrid
- URL de tutorial video

---

## 5. RAMAS DE TRABAJO

### Estructura creada

```text
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Estado actual

| Rama | Estado | Commit actual |
| --- | --- | --- |
| main | Existente, en remoto | e40e322 (recuperacion de contrasena) |
| develop | Creada, pendiente push a remoto | Mismo commit que main |
| cycloid | Legacy — sera reemplazada por develop | Mismo commit que main |

### Proteccion de ramas (pendiente en Gitea)

- **main:** protegida, requiere PR, no push directo
- **develop:** protegida, requiere PR desde feature/

### Flujo de trabajo

- Nueva funcionalidad: `develop` -> `feature/nombre` -> PR a `develop` -> PR a `main`
- Hotfix urgente: `main` -> `hotfix/nombre` -> PR a `main` + PR a `develop`

---

## 6. PIPELINES CI/CD

### Plataforma: Gitea con Gitea Runner (act_runner)

### Pipeline 1: Validar y Deploy a Dev/QA

**Archivo:** `.gitea/workflows/validate-and-deploy-qa.yml`
**Trigger:** Push/PR a develop o feature/*

```text
git push -> Gitea -> Runner -> Tests + Trivy + Semgrep + Secrets -> Deploy SSH -> QA
```

| Job | Que hace | Bloquea si falla |
| --- | --- | --- |
| test | `php -l` en todos los .php de app/ | Si |
| trivy | Escaneo de vulnerabilidades en dependencias (HIGH/CRITICAL) | Si |
| semgrep | Analisis estatico de seguridad (reglas PHP + secrets + security-audit) | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, OpenAI, DB passwords) | Si |
| deploy-qa | SSH al servidor QA y ejecuta deploy | Solo en push a develop |

### Pipeline 2: Cutover a Produccion

**Archivo:** `.gitea/workflows/cutover-production.yml`
**Trigger:** Push a main (despues de merge de PR desde develop)

```text
PR develop -> main -> Validate -> Trivy + Semgrep (paralelo) -> Deploy SSH -> Produccion
                                                                           -> Verificacion HTTP
```

| Job | Que hace |
| --- | --- |
| validate | Sintaxis PHP + busqueda de credenciales |
| trivy | Escaneo vulnerabilidades (paralelo con semgrep) |
| semgrep | Analisis estatico seguridad (paralelo con trivy) |
| deploy-production | SSH a Hetzner + deploy + verificacion HTTP post-deploy |

**Todo por pipeline, nada manual.**

### Secrets necesarios en Gitea

**Para Dev/QA:** QA_HOST, QA_USER, QA_SSH_KEY, QA_PATH
**Para Produccion:** PROD_HOST, PROD_USER, PROD_SSH_KEY, PROD_PATH

### Flujo completo

```text
feature/xxx -> push -> Validacion -> PR a develop -> Validacion -> merge
                                                                    |
                                         Deploy automatico a QA server
                                                                    |
                                             Pruebas en QA (manuales)
                                                                    |
                                         PR develop -> main -> Validacion -> merge
                                                                              |
                                                    Cutover automatico a Hetzner
                                                                              |
                                                         Verificacion HTTP 200
                                                                              |
                                                             EN PRODUCCION
```

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
| --- | --- | --- |
| Visibilidad | PUBLICO en GitHub | Migrar a Gitea privado |
| .gitignore | Actualizado (excluye tmp, debug, test, .claude) | OK |
| .env.example | Creado con todas las variables | OK |
| .env | Excluido por .gitignore (pero existe en historial) | Limpiar historial |
| Archivos basura | 20+ scripts de debug/test en raiz | Pendiente limpieza |

### Archivos basura trackeados en git (pendiente limpieza)

**Scripts de debug con credenciales de BD (11 archivos):**
- debug_query.php, check_items.php, check_update.php, final_debug.php
- check_autoincrement.php, check_all_tables.php, check_assigned_items.php
- fix_auditoria_9.php, test_porcentaje_cumplimiento.php
- test_sendgrid.php, test_admin_workflow.php, test_form.php

**Scripts SQL temporales (5 archivos):**
- add_porcentaje_cumplimiento.sql, check_pk.sql, create_personal_table.sql
- fix_notificaciones_nullable.sql, fix_primary_keys.sql, temp_check_autoincrement.sql

**Archivos de texto temporales:**
- texto.txt, tmp_lines.txt

**Scripts de setup (deberian estar en docs/ o tools/):**
- check-server.php, pre_flight_check.php, pre_flight_check.sh, pre_flight_check.bat
- run_admin_setup.sh, run_admin_setup.bat

**Archivo de backup (no deberia estar en git):**
- app/Controllers/Proveedor/AuditoriasProveedorController.php.bak
- app/Services/UploadService.php.backup, app/Services/UploadService_old.php

**Archivos de configuracion de servidor (mejor en docs/):**
- apache-vhost.conf, nginx-server.conf, .htaccess.root

**28 archivos .md sueltos en raiz** que deberian moverse a `docs/`:
- ACTUALIZACION_AUDITORIA_MODEL.md
- ADMIN_DASHBOARD_IMPLEMENTATION.md
- AUDITORIA_LOG_IMPLEMENTATION.md
- AUDITORIA-CLIENTES-SNAPSHOT-IMPLEMENTATION.md
- CHECKLIST_TESTING_COMPLETO.md
- COMPLETION-LOGIC-IMPLEMENTATION.md
- CONFIGURACION_EMAIL_SENDGRID.md
- CONFIGURACION_PHP_UPLOADS.md
- DEPLOY.md (este puede quedarse en raiz)
- FLUJO_CREACION_USUARIOS.md
- GUIA_PRUEBAS_CERRAR_AUDITORIA.md
- GUIA_PRUEBAS_CONSULTORES.md
- GUIA_PRUEBAS_FILTROS.md
- GUIA_PRUEBAS_ITEMS_BANCO.md
- GUIA_PRUEBAS_UPLOAD_SERVICE.md
- IMPLEMENTACION_PDF.md
- INICIO_RAPIDO.md
- MIGRACIONES_SEEDERS.md
- PDF-MEJORAS-DOCUMENTATION.md
- PRE_FLIGHT_CHECKLIST.md
- README_ADMIN_QUICKSEED.md
- README_COMPLETO.md
- REAPERTURA_AUDITORIAS.md
- SECURITY_CONTEXT_LOGGING_IMPLEMENTATION.md
- SEGURIDAD_UPLOAD.md
- SERVER-SETUP.md
- SMOKE_TESTS_QA.md
- TOAST_QUICK_REFERENCE.md
- TOAST_VALIDATION_IMPLEMENTATION.md
- TROUBLESHOOTING.md
- TUTORIAL_VIDEO.md
- UPLOAD-SERVICE-REFACTOR-DOCUMENTATION.md

### Archivos que SI deben quedarse

- composer.json, composer.lock, spark, preload.php
- phpunit.xml.dist
- LICENSE
- README.md, CONTRIBUTING.md, .env.example, DEPLOY.md, TROUBLESHOOTING.md

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
| --- | --- | --- |
| 1 | Hacer repo privado en GitHub o migrar a Gitea privado | Consultor/Cliente |
| 2 | Rotar SendGrid API key (expuesta en historial git) | Cliente |
| 3 | Rotar password de BD de DigitalOcean (expuesta en .claude/) | Cliente |
| 4 | Eliminar scripts con credenciales hardcodeadas (11 archivos .php en raiz) | Cliente |

### Prioridad ALTA

| # | Accion | Responsable |
| --- | --- | --- |
| 5 | Push de rama develop al remoto | Cliente |
| 6 | Configurar proteccion de ramas en Gitea | Consultor |
| 7 | Configurar secrets en Gitea para pipelines CI/CD | Consultor |
| 8 | Limpiar historial de git (eliminar .env commiteado) | Consultor |
| 9 | Cambiar passwords de seeders por unos seguros | Cliente |

### Prioridad MEDIA

| # | Accion | Responsable |
| --- | --- | --- |
| 10 | Mover 28+ .md sueltos de raiz a docs/ | Cliente |
| 11 | Eliminar archivos .bak y _old del repo | Cliente |
| 12 | Eliminar 5 scripts SQL temporales del repo | Cliente |
| 13 | Mover scripts de setup (check-server, pre_flight) a tools/ | Cliente |
| 14 | Eliminar archivos de config de servidor de raiz (mover a docs/) | Cliente |

### Prioridad BAJA

| # | Accion | Responsable |
| --- | --- | --- |
| 15 | Configurar HTTPS forzado en produccion (app.forceGlobalSecureRequests) | Cliente |
| 16 | Generar encryption.key para produccion | Cliente |
| 17 | Agregar mas tests PHPUnit | Cliente |
| 18 | Considerar crear vistas de BD para reportes | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio auditorias.*
