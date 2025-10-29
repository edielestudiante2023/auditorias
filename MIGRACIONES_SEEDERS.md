# Migraciones y Seeders - Sistema de Auditorías

## 📋 Estructura de Base de Datos

### Tablas Creadas

1. **roles** - Roles del sistema (super_admin, consultor, proveedor)
2. **users** - Usuarios del sistema
3. **proveedores** - Proveedores de servicios
4. **clientes** - Clientes que reciben servicios
5. **servicios** - Catálogo de servicios
6. **contratos** - Contratos entre proveedores y clientes
7. **items_banco** - Banco de ítems para auditorías
8. **auditorias** - Auditorías realizadas
9. **auditoria_items** - Ítems evaluados en cada auditoría
10. **auditoria_clientes** - Clientes asignados a cada auditoría
11. **auditoria_item_cliente** - Evaluaciones específicas por cliente
12. **evidencias** - Evidencias globales de auditorías
13. **evidencias_cliente** - Evidencias específicas por cliente

---

## 🚀 Comandos de Ejecución

### Opción 1: Ejecutar Todo de una Vez (RECOMENDADO)

```bash
# Windows (CMD/PowerShell)
php spark migrate --all
php spark db:seed MasterSeeder

# Linux/Mac
php spark migrate --all
php spark db:seed MasterSeeder
```

### Opción 2: Ejecución Manual Paso a Paso

#### 1. Ejecutar Migraciones (en orden automático)

```bash
php spark migrate
```

El sistema ejecutará las migraciones en este orden automáticamente:

1. `2025-10-16-100000_CreateRolesTable`
2. `2025-10-16-100001_CreateUsersTable`
3. `2025-10-16-100002_CreateProveedoresTable`
4. `2025-10-16-100003_CreateClientesTable`
5. `2025-10-16-100004_CreateServiciosTable`
6. `2025-10-16-100005_CreateContratosTable`
7. `2025-10-16-100006_CreateItemsBancoTable`
8. `2025-10-16-100007_CreateAuditoriasTable`
9. `2025-10-16-100008_CreateAuditoriaItemsTable`
10. `2025-10-16-100009_CreateAuditoriaClientesTable`
11. `2025-10-16-100010_CreateAuditoriaItemClienteTable`
12. `2025-10-16-100011_CreateEvidenciasTable`
13. `2025-10-16-100012_CreateEvidenciasClienteTable`

#### 2. Ejecutar Seeders Individuales (en orden)

Si prefieres ejecutar seeders uno por uno:

```bash
# 1. Roles
php spark db:seed RolesSeeder

# 2. Usuarios
php spark db:seed UsersSeeder

# 3. Proveedores
php spark db:seed ProveedoresSeeder

# 4. Clientes
php spark db:seed ClientesSeeder

# 5. Servicios
php spark db:seed ServiciosSeeder

# 6. Contratos
php spark db:seed ContratosSeeder

# 7. Items Banco
php spark db:seed ItemsBancoSeeder
```

---

## 🔄 Comandos de Rollback

### Revertir todas las migraciones

```bash
php spark migrate:rollback
```

### Revertir un número específico de lotes

```bash
php spark migrate:rollback -b 1
```

### Refrescar (rollback + migrate)

```bash
php spark migrate:refresh
```

### Refrescar con seeders

```bash
php spark migrate:refresh --all
php spark db:seed MasterSeeder
```

---

## 👥 Usuarios Demo Creados

Los seeders crean los siguientes usuarios de prueba:

| Email | Contraseña | Rol | Descripción |
|-------|-----------|-----|-------------|
| admin@cycloidtalent.com | password123 | super_admin | Administrador del sistema |
| consultor@cycloidtalent.com | password123 | consultor | Consultor/auditor |
| proveedor@empresa.com | password123 | proveedor | Proveedor de servicios |

---

## 📊 Datos Demo Creados

### Proveedores
- **Seguridad Total SAS** (NIT: 9001234567)
- **Vigilancia Profesional LTDA** (NIT: 8009876543)

### Clientes
- **Industrias ABC SA** (NIT: 9005551234)
- **Corporación XYZ LTDA** (NIT: 8005559876)
- **Manufacturas DEF SAS** (NIT: 9005554321)

### Servicios
- **Vigilancia y Seguridad Física** (SRV-VSF-001)
- **Seguridad Industrial** (SRV-SI-002)
- **Gestión de SST** (SRV-SST-003)

### Contratos
- 3 contratos activos entre proveedores y clientes

### Items Banco
- **2 ítems globales:**
  - ITEM-GLOB-001: Política de Seguridad y Salud en el Trabajo
  - ITEM-GLOB-002: Certificaciones y Licencias Vigentes

- **3 ítems por cliente:**
  - ITEM-CLI-001: Cumplimiento de Protocolo Específico del Cliente
  - ITEM-CLI-002: Personal Asignado y Capacitación Cliente Específico
  - ITEM-CLI-003: Registro de Incidentes en Sitio

---

## 🗂️ Estructura de Archivos

```
app/Database/
├── Migrations/
│   ├── 2025-10-16-100000_CreateRolesTable.php
│   ├── 2025-10-16-100001_CreateUsersTable.php
│   ├── 2025-10-16-100002_CreateProveedoresTable.php
│   ├── 2025-10-16-100003_CreateClientesTable.php
│   ├── 2025-10-16-100004_CreateServiciosTable.php
│   ├── 2025-10-16-100005_CreateContratosTable.php
│   ├── 2025-10-16-100006_CreateItemsBancoTable.php
│   ├── 2025-10-16-100007_CreateAuditoriasTable.php
│   ├── 2025-10-16-100008_CreateAuditoriaItemsTable.php
│   ├── 2025-10-16-100009_CreateAuditoriaClientesTable.php
│   ├── 2025-10-16-100010_CreateAuditoriaItemClienteTable.php
│   ├── 2025-10-16-100011_CreateEvidenciasTable.php
│   └── 2025-10-16-100012_CreateEvidenciasClienteTable.php
│
└── Seeds/
    ├── MasterSeeder.php
    ├── RolesSeeder.php
    ├── UsersSeeder.php
    ├── ProveedoresSeeder.php
    ├── ClientesSeeder.php
    ├── ServiciosSeeder.php
    ├── ContratosSeeder.php
    └── ItemsBancoSeeder.php
```

---

## 📐 Diagrama de Relaciones

```
roles
  └─→ users
       ├─→ proveedores
       │    ├─→ contratos ←─┐
       │    └─→ auditorias   │
       │                     │
       └─→ auditorias        │
                             │
clientes ←───────────────────┘
  └─→ contratos
  └─→ auditoria_clientes
       └─→ auditoria_item_cliente
            └─→ evidencias_cliente

servicios
  └─→ contratos

items_banco
  └─→ auditoria_items
       ├─→ evidencias
       └─→ auditoria_item_cliente

auditorias
  ├─→ auditoria_items
  └─→ auditoria_clientes
```

---

## 🔑 Campos Importantes

### Tabla `items_banco`
- `es_por_cliente`: TINYINT(1)
  - `0` = ítem global (aplica a toda la auditoría)
  - `1` = ítem por cliente (se evalúa por cada cliente)

### Tabla `evidencias` vs `evidencias_cliente`
- `evidencias`: Para ítems globales (relacionada con `auditoria_items`)
- `evidencias_cliente`: Para ítems por cliente (relacionada con `auditoria_item_cliente`)

### Hash de Archivos
- Campo `hash` en tablas de evidencias: SHA256 del archivo para verificación de integridad

---

## ⚠️ Consideraciones Importantes

### 1. Orden de Dependencias
Las migraciones deben ejecutarse en el orden especificado por el timestamp. CodeIgniter lo hace automáticamente.

### 2. Seeders
- Los seeders usan `truncate()` para limpiar las tablas antes de insertar
- Ejecutar solo en entornos de desarrollo/pruebas
- En producción, insertar datos manualmente o modificar seeders

### 3. Foreign Keys
Todas las relaciones tienen llaves foráneas con:
- `CASCADE` en DELETE para datos relacionados
- `SET NULL` en campos opcionales
- `CASCADE` en UPDATE para mantener integridad

### 4. Timestamps
Todas las tablas principales tienen campos:
- `created_at`: Fecha de creación
- `updated_at`: Fecha de última actualización

---

## 🧪 Verificación Post-Instalación

### Verificar tablas creadas

```bash
# Windows (usando mysql CLI)
mysql -u root -p -e "USE auditorias; SHOW TABLES;"

# Verificar datos insertados
php spark db:table roles
php spark db:table users
php spark db:table items_banco
```

### Verificar desde PHP

```php
<?php
// En un controlador o script de prueba
$db = \Config\Database::connect();

// Verificar roles
$roles = $db->table('roles')->get()->getResult();
print_r($roles);

// Verificar usuarios
$users = $db->table('users')->get()->getResult();
print_r($users);

// Verificar items banco
$items = $db->table('items_banco')->orderBy('orden', 'ASC')->get()->getResult();
print_r($items);
```

---

## 📝 Notas de Desarrollo

### Agregar más ítems al banco

Editar `app/Database/Seeds/ItemsBancoSeeder.php` y agregar elementos al array `$data`:

```php
[
    'codigo' => 'ITEM-GLOB-003',
    'nombre' => 'Nombre del ítem',
    'descripcion' => 'Descripción detallada',
    'es_por_cliente' => 0, // 0=global, 1=por_cliente
    'orden' => 6,
    'evidencia_requerida' => 1, // 0=opcional, 1=requerida
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
],
```

### Crear nueva migración

```bash
php spark make:migration NombreDeLaMigracion
```

### Crear nuevo seeder

```bash
php spark make:seeder NombreDelSeeder
```

---

## 🐛 Troubleshooting

### Error: "Base table or view already exists"
**Solución:** Ejecutar rollback antes de migrar nuevamente
```bash
php spark migrate:rollback
php spark migrate
```

### Error: "Cannot add foreign key constraint"
**Solución:** Verificar que las tablas referenciadas existen. Ejecutar migraciones en orden.

### Error: "Duplicate entry for key 'PRIMARY'"
**Solución:** Limpiar la base de datos antes de ejecutar seeders
```bash
php spark migrate:refresh --all
php spark db:seed MasterSeeder
```

### Error de permisos en Windows
**Solución:** Ejecutar CMD/PowerShell como Administrador

---

## ✅ Checklist de Instalación

- [ ] Base de datos creada (`auditorias`)
- [ ] Configuración de BD en `app/Config/Database.php`
- [ ] Ejecutar: `php spark migrate --all`
- [ ] Ejecutar: `php spark db:seed MasterSeeder`
- [ ] Verificar tabla `roles` tiene 3 registros
- [ ] Verificar tabla `users` tiene 3 usuarios
- [ ] Verificar tabla `items_banco` tiene 5 items
- [ ] Login con usuario admin: admin@cycloidtalent.com / password123
- [ ] Configurar permisos de carpeta `writable/` (777 en desarrollo)

---

**Fecha de Creación:** 2025-10-16
**Versión:** 1.0
**Sistema:** Auditorías - Cycloid Talent SAS
