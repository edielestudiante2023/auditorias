# AdminQuickSeed - Guía de Uso

Esta guía explica cómo cargar datos de prueba rápidos para el módulo Admin del sistema de auditorías.

## 📦 Contenido del Seeder

El seeder `AdminQuickSeed` crea los siguientes datos de prueba:

- **3 Clientes** con información completa (razón social, NIT, contacto, etc.)
- **2 Proveedores** (uno con usuario asignado, otro sin usuario)
- **1 Servicio** llamado "Auditoría SST"
- **2 Consultores** con licencias SST
- **2 Contratos** activos (Proveedor A → Clientes 1 y 2, Servicio Auditoría SST)
- **1 Usuario Admin** (si no existe previamente)

## 🚀 Cómo Ejecutar el Seeder

### Opción 1: Script Automático (Recomendado) ⚡

#### En Windows (XAMPP):
Simplemente haz doble clic en el archivo o ejecuta:
```cmd
run_admin_setup.bat
```

#### En Linux/Mac:
```bash
chmod +x run_admin_setup.sh
./run_admin_setup.sh
```

El script automáticamente:
1. ✅ Ejecuta el seeder
2. ✅ Pregunta si quieres ejecutar las pruebas
3. ✅ Muestra las credenciales de acceso

---

### Opción 2: Manual 🔧

#### Paso 1: Preparar la base de datos

Asegúrate de que tu base de datos esté configurada correctamente en `app/Config/Database.php`.

```bash
# Opcional: Si necesitas crear/recrear las tablas
php spark migrate:refresh
```

#### Paso 2: Ejecutar el seeder

```bash
php spark db:seed AdminQuickSeed
```

#### Paso 3: Ejecutar pruebas (opcional)

```bash
php test_admin_workflow.php
```

**Salida esperada:**

```
=== AdminQuickSeed: Iniciando carga de datos de prueba ===

→ Verificando usuario admin...
  ✓ Usuario admin creado
→ Creando clientes...
  ✓ 3 clientes creados (IDs: 1, 2, 3)
→ Creando proveedores...
  ✓ 2 proveedores creados (IDs: 1, 2)
→ Creando servicio...
  ✓ Servicio 'Auditoría SST' creado (ID: 1)
→ Creando consultores...
  ✓ 2 consultores creados (IDs: 1, 2)
→ Creando contratos...
  ✓ 2 contratos creados (IDs: 1, 2)

=== AdminQuickSeed: Completado exitosamente ===

Credenciales de acceso:
  Admin:       superadmin@cycloidtalent.com / Admin123*
  Consultor 1: consultor1@cycloid.com / Consultor123*
  Consultor 2: consultor2@cycloid.com / Consultor123*
  Proveedor 1: proveedor1@empresa.com / Proveedor123*

Datos creados:
  • 3 Clientes
  • 2 Proveedores
  • 1 Servicio (Auditoría SST)
  • 2 Consultores
  • 2 Contratos activos
```

## 🔑 Credenciales de Acceso

### Usuario Admin (Super Admin)
- **Email:** `superadmin@cycloidtalent.com`
- **Contraseña:** `Admin123*`
- **Rol:** Super Admin (ID 1)

### Usuarios Consultores
- **Consultor 1:**
  - Email: `consultor1@cycloid.com`
  - Contraseña: `Consultor123*`
  - Rol: Consultor (ID 2)

- **Consultor 2:**
  - Email: `consultor2@cycloid.com`
  - Contraseña: `Consultor123*`
  - Rol: Consultor (ID 2)

### Usuario Proveedor
- **Proveedor 1:**
  - Email: `proveedor1@empresa.com`
  - Contraseña: `Proveedor123*`
  - Rol: Proveedor (ID 3)

## 🧪 Script de Prueba

Para verificar que todo funciona correctamente, ejecuta el script de prueba:

```bash
php test_admin_workflow.php
```

Este script automatiza las siguientes pruebas:

1. ✅ **Inicio de sesión** como admin
2. ✅ **Dashboard** con contadores > 0
3. ✅ **Acceso a módulos** (Clientes, Proveedores, Consultores, Contratos, Usuarios)
4. ✅ **Creación de registro** en módulo Clientes
5. ✅ **Edición de registro** en módulo Proveedores

**Nota:** Antes de ejecutar el script, asegúrate de:
- Tener `curl` habilitado en PHP
- Ajustar `$baseUrl` en el script si tu URL base no es `http://localhost/auditorias`

## 📋 Datos Creados en Detalle

### Clientes

| ID | Razón Social | NIT | Email | Teléfono | Ciudad |
|----|--------------|-----|-------|----------|--------|
| 1 | Empresa Demo ABC S.A.S | 900123456-1 | contacto@empresaabc.com | 3101234567 | Bogotá |
| 2 | Corporación XYZ LTDA | 800987654-3 | info@corpxyz.com | 3209876543 | Medellín |
| 3 | Industrias DEF S.A | 900555777-9 | admin@industriasdef.com | 3155554321 | Cali |

### Proveedores

| ID | Razón Social | NIT | Email | Usuario Asignado |
|----|--------------|-----|-------|------------------|
| 1 | Proveedor Alpha S.A.S | 901234567-8 | ventas@proveedoralpha.com | proveedor1@empresa.com |
| 2 | Servicios Beta LTDA | 800888999-4 | contacto@serviciosbeta.com | (sin usuario) |

### Consultores

| ID | Nombre Completo | Tipo Doc | Número Doc | Licencia SST | Usuario |
|----|----------------|----------|------------|--------------|---------|
| 1 | Ana García Pérez | CC | 52123456 | SST-2024-001 | consultor1@cycloid.com |
| 2 | Carlos López Martínez | CC | 79987654 | SST-2024-002 | consultor2@cycloid.com |

### Contratos

| ID | Proveedor | Cliente | Servicio | Fecha Inicio | Fecha Fin | N° Personas | Estado |
|----|-----------|---------|----------|--------------|-----------|-------------|--------|
| 1 | Proveedor Alpha | Empresa Demo ABC | Auditoría SST | 2025-01-01 | 2025-12-31 | 50 | Activo |
| 2 | Proveedor Alpha | Corporación XYZ | Auditoría SST | 2025-02-01 | 2025-12-31 | 75 | Activo |

## 🔄 Ejecutar Múltiples Veces

El seeder está diseñado para ser **idempotente** respecto al usuario admin:

- Si el usuario admin ya existe, **no lo duplica**.
- Los demás datos (clientes, proveedores, etc.) se **agregarán** a los existentes.

Si deseas limpiar y volver a empezar:

```bash
# Opción 1: Resetear todas las migraciones (⚠️ ELIMINA TODOS LOS DATOS)
php spark migrate:refresh

# Opción 2: Ejecutar de nuevo el seeder (agrega más datos)
php spark db:seed AdminQuickSeed
```

## 🐛 Solución de Problemas

### Error: "Class 'AdminQuickSeed' not found"

Asegúrate de que el archivo está en la ruta correcta:
```
app/Database/Seeds/AdminQuickSeed.php
```

Y que el namespace es correcto:
```php
namespace App\Database\Seeds;
```

### Error: "Foreign key constraint fails"

Esto puede ocurrir si las tablas relacionadas no existen. Ejecuta:

```bash
php spark migrate
```

### Los contadores del dashboard están en 0

Verifica que el seeder se ejecutó correctamente. Revisa la salida del comando y asegúrate de ver:

```
✓ 3 clientes creados
✓ 2 proveedores creados
✓ 2 consultores creados
✓ 2 contratos creados
```

## 📝 Personalización

Si deseas modificar los datos de prueba, edita el archivo:

```
app/Database/Seeds/AdminQuickSeed.php
```

Puedes cambiar:
- Cantidad de registros
- Nombres y datos de prueba
- Credenciales de usuarios
- Fechas de contratos

## ✅ Checklist Post-Seeder

Después de ejecutar el seeder, verifica:

1. ✅ Login con usuario admin funciona
2. ✅ Dashboard muestra contadores > 0
3. ✅ Módulo Clientes muestra 3 clientes
4. ✅ Módulo Proveedores muestra 2 proveedores
5. ✅ Módulo Consultores muestra 2 consultores
6. ✅ Módulo Contratos muestra 2 contratos
7. ✅ Puedes crear/editar registros en cada módulo
8. ✅ Breadcrumbs funcionan correctamente
9. ✅ Flash messages se muestran al crear/editar
10. ✅ DataTables funciona en las tablas

## 🆘 Soporte

Si encuentras problemas:

1. Revisa los logs de CodeIgniter en `writable/logs/`
2. Verifica la configuración de la base de datos
3. Asegúrate de tener las migraciones ejecutadas
4. Ejecuta el script de prueba para diagnóstico automático

---

**Última actualización:** 2025-01-XX
**Versión:** 1.0
