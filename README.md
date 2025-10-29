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
- PHP >= 8.0
- MySQL >= 8.0
- Composer

### Configuración

1. Clonar repositorio
2. Instalar dependencias: `composer install`
3. Configurar `.env` con credenciales de base de datos y SendGrid
4. Importar base de datos
5. Configurar permisos en `writable/`

## 📝 Licencia

Propietario: Cycloid Talent SAS. Todos los derechos reservados © 2024-2025
