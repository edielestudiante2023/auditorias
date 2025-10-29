# Actualización de AuditoriaModel

## 🔄 Cambios Realizados

El modelo `AuditoriaModel.php` ha sido actualizado para coincidir con la nueva estructura de base de datos creada por las migraciones.

---

## 📊 Cambios en Campos

### ❌ Campos Removidos
- `id_consultor` - Ya no existe tabla `consultores`

### ✅ Campos Agregados
- `creado_por` - Referencia a `users.id_users` (reemplaza `id_consultor`)

### 📝 Campos con Estructura Actualizada

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_proveedor` | INT | FK a `proveedores.id_proveedor` |
| `codigo_formato` | VARCHAR(100) | Código del formato de auditoría |
| `version_formato` | VARCHAR(20) | Versión del formato |
| `estado` | ENUM | Estados: borrador, en_proveedor, en_revision, cerrada, anulada |
| `porcentaje_cumplimiento` | DECIMAL(5,2) | Porcentaje global de cumplimiento |
| `creado_por` | INT | FK a `users.id_users` - Usuario que creó la auditoría |
| `fecha_programada` | DATETIME | Fecha programada de la auditoría |
| `fecha_envio_proveedor` | DATETIME | Fecha de envío al proveedor |

---

## 🔧 Métodos Actualizados

### 1. `getAuditoriasWithRelations()`
**Antes:** Joins con `consultores`
```php
->join('consultores', 'consultores.id_consultor = auditorias.id_consultor')
```

**Ahora:** Joins con `users`
```php
->join('users', 'users.id_users = auditorias.creado_por')
```

**Retorna:**
- `proveedor_nombre`, `proveedor_nit`
- `creador_nombre`, `creador_email`

---

### 2. `getAuditoriasByUsuario()` (NUEVO)
**Antes:** `getAuditoriasByConsultor()`

**Ahora:**
```php
public function getAuditoriasByUsuario(int $idUsuario): array
```

Obtiene todas las auditorías creadas por un usuario específico (consultor).

---

### 3. `getAuditoriasByProveedor()`
**Actualizado** para usar `users` en lugar de `consultores`.

**Retorna:**
- `creador_nombre`, `creador_email`

---

### 4. `getAuditoriaCompleta()` (NUEVO)
Obtiene una auditoría con **TODA** su información relacionada:

```php
public function getAuditoriaCompleta(int $idAuditoria): ?array
```

**Retorna un array con:**
- Datos de la auditoría
- Datos del proveedor (incluyendo `logo_path`)
- Datos del usuario creador
- Array de clientes asignados (`auditoria_clientes`)
- Array de items de la auditoría (`auditoria_items`)

**Ejemplo de retorno:**
```php
[
    'id_auditoria' => 1,
    'proveedor_nombre' => 'Seguridad Total SAS',
    'proveedor_nit' => '9001234567',
    'proveedor_logo' => 'logos/proveedor_123.png',
    'creador_nombre' => 'Consultor Demo',
    'creador_email' => 'consultor@cycloidtalent.com',
    'clientes' => [
        [
            'id_cliente' => 1,
            'razon_social' => 'Cliente ABC',
            'porcentaje_cumplimiento' => 85.50,
            // ... más datos
        ]
    ],
    'items' => [
        [
            'id_auditoria_item' => 1,
            'codigo' => 'ITEM-GLOB-001',
            'nombre' => 'Política de SST',
            'es_por_cliente' => 0,
            // ... más datos
        ]
    ]
]
```

---

### 5. `calcularPorcentajeGlobal()` (NUEVO)
Calcula el porcentaje de cumplimiento solo de **ítems globales**.

```php
public function calcularPorcentajeGlobal(int $idAuditoria): float
```

**Lógica:**
- Solo considera ítems donde `es_por_cliente = 0`
- Excluye calificación `no_aplica`
- Mapeo: `cumple=1`, `parcial=0.5`, `no_cumple=0`
- Retorna porcentaje redondeado a 2 decimales

**Ejemplo:**
```php
$porcentaje = $auditoriaModel->calcularPorcentajeGlobal(1);
// Retorna: 75.50
```

---

### 6. `cambiarEstado()` (NUEVO)
Actualiza el estado de una auditoría de forma segura.

```php
public function cambiarEstado(int $idAuditoria, string $nuevoEstado): bool
```

**Estados válidos:**
- `borrador`
- `en_proveedor`
- `en_revision`
- `cerrada`
- `anulada`

**Ejemplo:**
```php
$success = $auditoriaModel->cambiarEstado(1, 'cerrada');
```

---

### 7. `puedeEditar()` (NUEVO)
Verifica si un usuario tiene permisos para editar una auditoría.

```php
public function puedeEditar(int $idAuditoria, int $idUsuario): bool
```

**Regla:**
- Solo el creador puede editar si la auditoría está en estado `borrador`

**Ejemplo:**
```php
if ($auditoriaModel->puedeEditar(1, $userId)) {
    // Permitir edición
}
```

---

### 8. `getEstadisticasPorEstado()` (NUEVO)
Obtiene conteo de auditorías agrupadas por estado.

```php
public function getEstadisticasPorEstado(int $idUsuario = null): array
```

**Parámetro opcional:**
- `$idUsuario`: Filtra solo auditorías de ese usuario

**Retorna:**
```php
[
    ['estado' => 'borrador', 'total' => 5],
    ['estado' => 'cerrada', 'total' => 12],
    ['estado' => 'en_revision', 'total' => 3]
]
```

---

## 🗂️ Estructura de Relaciones

```
users (creado_por)
  └─→ auditorias
       ├─→ proveedores (id_proveedor)
       ├─→ auditoria_items
       │    ├─→ items_banco
       │    └─→ evidencias
       └─→ auditoria_clientes
            ├─→ clientes
            └─→ auditoria_item_cliente
                 └─→ evidencias_cliente
```

---

## 📝 Ejemplos de Uso

### Crear Nueva Auditoría
```php
$auditoriaModel = new AuditoriaModel();

$data = [
    'id_proveedor' => 1,
    'creado_por' => $userId, // ID del usuario consultor
    'codigo_formato' => 'FRM-AUD-001',
    'version_formato' => '2.0',
    'estado' => 'borrador',
    'fecha_programada' => date('Y-m-d H:i:s'),
];

$idAuditoria = $auditoriaModel->insert($data);
```

### Obtener Auditorías de un Usuario
```php
$auditorias = $auditoriaModel->getAuditoriasByUsuario($userId);

foreach ($auditorias as $auditoria) {
    echo $auditoria['proveedor_nombre'];
    echo $auditoria['estado'];
}
```

### Obtener Auditoría Completa
```php
$auditoria = $auditoriaModel->getAuditoriaCompleta(1);

if ($auditoria) {
    echo "Proveedor: " . $auditoria['proveedor_nombre'];
    echo "Clientes asignados: " . count($auditoria['clientes']);
    echo "Items: " . count($auditoria['items']);
}
```

### Calcular y Actualizar Porcentaje
```php
$porcentaje = $auditoriaModel->calcularPorcentajeGlobal($idAuditoria);

$auditoriaModel->update($idAuditoria, [
    'porcentaje_cumplimiento' => $porcentaje
]);
```

### Cerrar Auditoría
```php
if ($auditoriaModel->cambiarEstado($idAuditoria, 'cerrada')) {
    // Auditoría cerrada exitosamente
    echo "Auditoría cerrada";
}
```

### Verificar Permisos
```php
if ($auditoriaModel->puedeEditar($idAuditoria, $userId)) {
    // Mostrar formulario de edición
} else {
    // Mostrar mensaje de error
    echo "No tienes permisos para editar";
}
```

### Obtener Estadísticas
```php
// Estadísticas globales
$stats = $auditoriaModel->getEstadisticasPorEstado();

// Estadísticas de un usuario
$statsUsuario = $auditoriaModel->getEstadisticasPorEstado($userId);

foreach ($stats as $stat) {
    echo "{$stat['estado']}: {$stat['total']} auditorías\n";
}
```

---

## ⚠️ Consideraciones de Migración

Si tienes código existente que usa el modelo anterior:

### Cambios Necesarios en Controladores

**Antes:**
```php
$auditorias = $auditoriaModel->getAuditoriasByConsultor($idConsultor);
```

**Ahora:**
```php
$auditorias = $auditoriaModel->getAuditoriasByUsuario($idUsuario);
```

**Antes:**
```php
'id_consultor' => $idConsultor
```

**Ahora:**
```php
'creado_por' => $idUsuario
```

---

## 🔑 Campos de Base de Datos

### Tabla `auditorias`

```sql
CREATE TABLE auditorias (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    codigo_formato VARCHAR(100),
    version_formato VARCHAR(20),
    estado ENUM('borrador','en_proveedor','en_revision','cerrada','anulada'),
    porcentaje_cumplimiento DECIMAL(5,2),
    creado_por INT NOT NULL,
    fecha_programada DATETIME,
    fecha_envio_proveedor DATETIME,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id_proveedor),
    FOREIGN KEY (creado_por) REFERENCES users(id_users)
);
```

---

## ✅ Checklist de Actualización

- [x] Modelo actualizado con nueva estructura
- [x] Joins corregidos (users en lugar de consultores)
- [x] Métodos agregados para funcionalidades nuevas
- [x] Validación de campos actualizada
- [x] Documentación completa

---

**Fecha de Actualización:** 2025-10-16
**Versión:** 2.0
**Archivo:** `app/Models/AuditoriaModel.php`
