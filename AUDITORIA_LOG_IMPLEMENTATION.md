# Implementación de Bitácora de Auditoría (auditoria_log)

## Resumen

Sistema completo de trazabilidad para auditorías que registra todas las acciones realizadas:
- ✅ Guardado de comentarios (global y por-cliente)
- ✅ Subida de evidencias (global y por-cliente)
- ✅ Eliminación de evidencias (global y por-cliente) - preparado en modelo
- ✅ Cierre de auditoría
- ✅ Asignación de clientes (preparado en modelo)

Incluye método en controlador de Consultor para ver bitácora con paginación y vista completa con estadísticas.

---

## 1. Migración de Base de Datos

**Archivo**: `app/Database/Migrations/2025-10-16-210000_CreateAuditoriaLogTable.php`

**Estructura de tabla**:

```sql
CREATE TABLE auditoria_log (
    id_log INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_auditoria INT(11) UNSIGNED NOT NULL,
    id_users INT(11) UNSIGNED NULL,
    accion VARCHAR(60) NOT NULL,
    detalle TEXT NULL,
    created_at DATETIME NULL,

    INDEX idx_auditoria (id_auditoria),
    INDEX idx_users (id_users),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (id_auditoria) REFERENCES auditorias(id_auditoria) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_users) REFERENCES users(id_users) ON DELETE SET NULL ON UPDATE CASCADE
);
```

**Campos**:
- `id_log`: Primary key auto-incremental
- `id_auditoria`: FK a auditorías (CASCADE on delete)
- `id_users`: Usuario que realizó la acción (NULL = sistema)
- `accion`: Tipo de acción (max 60 caracteres)
- `detalle`: Información adicional en formato JSON o texto
- `created_at`: Timestamp de la acción

**Ejecutar migración**:
```bash
php spark migrate
```

---

## 2. Modelo AuditoriaLogModel

**Archivo**: `app/Models/AuditoriaLogModel.php`

### Métodos principales:

#### a) `registrar()` - Método genérico
```php
public function registrar(
    int $idAuditoria,
    string $accion,
    $detalle = null,
    ?int $idUsers = null
): int|false
```
- Auto-detecta usuario actual si no se proporciona
- Convierte arrays a JSON automáticamente
- Retorna ID del log creado o false

#### b) Métodos específicos:

**Comentarios**:
```php
// Comentario global
registrarComentarioGlobal(
    int $idAuditoria,
    int $idAuditoriaItem,
    string $itemTitulo,
    ?int $idUsers = null
)

// Comentario por cliente
registrarComentarioCliente(
    int $idAuditoria,
    int $idAuditoriaItem,
    int $idCliente,
    string $itemTitulo,
    string $clienteNombre,
    ?int $idUsers = null
)
```

**Evidencias - Subida**:
```php
// Global
registrarEvidenciaGlobalSubida(
    int $idAuditoria,
    int $idAuditoriaItem,
    string $nombreArchivo,
    int $tamanoBytes,
    ?int $idUsers = null
)

// Por cliente
registrarEvidenciaClienteSubida(
    int $idAuditoria,
    int $idAuditoriaItem,
    int $idCliente,
    string $nombreArchivo,
    int $tamanoBytes,
    string $clienteNombre,
    ?int $idUsers = null
)
```

**Evidencias - Eliminación**:
```php
// Global
registrarEvidenciaGlobalEliminada(
    int $idAuditoria,
    int $idAuditoriaItem,
    string $nombreArchivo,
    ?int $idUsers = null
)

// Por cliente
registrarEvidenciaClienteEliminada(
    int $idAuditoria,
    int $idAuditoriaItem,
    int $idCliente,
    string $nombreArchivo,
    string $clienteNombre,
    ?int $idUsers = null
)
```

**Cierre de auditoría**:
```php
registrarAuditoriaCerrada(
    int $idAuditoria,
    float $porcentajeGlobal,
    int $cantidadClientes,
    ?int $idUsers = null
)
```

**Asignación de clientes**:
```php
registrarClientesAsignados(
    int $idAuditoria,
    array $idsClientes,
    ?int $idUsers = null
)
```

#### c) Métodos de consulta:

```php
// Obtener bitácora paginada (para vistas)
getBitacoraPaginada(int $idAuditoria, int $perPage = 20): array
// Retorna: ['logs' => [...], 'pager' => PagerObject]

// Obtener bitácora completa (sin paginación)
getBitacora(int $idAuditoria): array

// Estadísticas de acciones
getEstadisticas(int $idAuditoria): array
// Retorna: [['accion' => 'comentario_global_guardado', 'total' => 5], ...]

// Contar total de acciones
contarAcciones(int $idAuditoria): int
```

---

## 3. Cambios en Controladores

### A. AuditoriasProveedorController

**Archivo**: `app/Controllers/Proveedor/AuditoriasProveedorController.php`

#### Imports agregados:
```diff
+ use App\Models\AuditoriaLogModel;
```

#### Propiedades agregadas:
```diff
+ protected $auditoriaLogModel;
```

#### Constructor actualizado:
```diff
+ $this->auditoriaLogModel = model('App\Models\AuditoriaLogModel');
```

#### Método `guardarItemGlobal()` - Diff líneas 132-150:
```diff
  private function guardarItemGlobal(int $idAuditoriaItem)
  {
      $item = $this->auditoriaItemModel->find($idAuditoriaItem);

      // Actualizar comentario en auditoria_items
+     $comentario = $this->request->getPost('comentario_proveedor');
      $this->auditoriaItemModel->update($idAuditoriaItem, [
-         'comentario_proveedor' => $this->request->getPost('comentario_proveedor'),
+         'comentario_proveedor' => $comentario,
      ]);

+     // Registrar en bitácora
+     if (!empty($comentario)) {
+         $itemBanco = $this->itemsBancoModel->find($item['id_item']);
+         $this->auditoriaLogModel->registrarComentarioGlobal(
+             $item['id_auditoria'],
+             $idAuditoriaItem,
+             $itemBanco['titulo'] ?? 'Ítem sin título'
+         );
+     }

      // Procesar evidencias globales
```

#### Método `guardarItemGlobal()` - Evidencias (líneas 178-196):
```diff
                  if ($result['ok']) {
                      $this->evidenciaModel->insert([
                          'id_auditoria_item' => $idAuditoriaItem,
                          'nombre_archivo_original' => $file->getName(),
                          'ruta_archivo' => $result['path'],
                          'tipo_mime' => $result['mime'],
                          'tamanio_bytes' => $result['size'],
                          'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                          'created_at' => date('Y-m-d H:i:s'),
                      ]);
+
+                     // Registrar en bitácora
+                     $this->auditoriaLogModel->registrarEvidenciaGlobalSubida(
+                         $item['id_auditoria'],
+                         $idAuditoriaItem,
+                         $file->getName(),
+                         $result['size']
+                     );
                  } else {
```

#### Método `guardarItemPorCliente()` - Comentario (líneas 209-254):
```diff
  private function guardarItemPorCliente(int $idAuditoriaItem, int $idCliente)
  {
      $item = $this->auditoriaItemModel->find($idAuditoriaItem);
+     $comentario = $this->request->getPost('comentario_proveedor_cliente');

      // Buscar o crear registro en auditoria_item_cliente
      $itemCliente = $this->auditoriaItemClienteModel
          ->where('id_auditoria_item', $idAuditoriaItem)
          ->where('id_cliente', $idCliente)
          ->first();

      if (!$itemCliente) {
          // Crear registro
          $this->auditoriaItemClienteModel->insert([
              'id_auditoria_item' => $idAuditoriaItem,
              'id_cliente' => $idCliente,
-             'comentario_proveedor_cliente' => $this->request->getPost('comentario_proveedor_cliente'),
+             'comentario_proveedor_cliente' => $comentario,
              'created_at' => date('Y-m-d H:i:s'),
          ]);
          $idAuditoriaItemCliente = $this->auditoriaItemClienteModel->getInsertID();
      } else {
          // Actualizar comentario
          $this->auditoriaItemClienteModel->update($itemCliente['id_auditoria_item_cliente'], [
-             'comentario_proveedor_cliente' => $this->request->getPost('comentario_proveedor_cliente'),
+             'comentario_proveedor_cliente' => $comentario,
              'updated_at' => date('Y-m-d H:i:s'),
          ]);
          $idAuditoriaItemCliente = $itemCliente['id_auditoria_item_cliente'];
      }
+
+     // Registrar en bitácora
+     if (!empty($comentario)) {
+         $itemBanco = $this->itemsBancoModel->find($item['id_item']);
+         $cliente = $this->auditoriaClienteModel
+             ->select('clientes.razon_social')
+             ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
+             ->where('auditoria_clientes.id_cliente', $idCliente)
+             ->first();
+
+         $this->auditoriaLogModel->registrarComentarioCliente(
+             $item['id_auditoria'],
+             $idAuditoriaItem,
+             $idCliente,
+             $itemBanco['titulo'] ?? 'Ítem sin título',
+             $cliente['razon_social'] ?? 'Cliente'
+         );
+     }
```

#### Método `guardarItemPorCliente()` - Evidencias (líneas 283-309):
```diff
                  if ($result['ok']) {
                      $this->evidenciaClienteModel->insert([
                          'id_auditoria_item_cliente' => $idAuditoriaItemCliente,
                          'nombre_archivo_original' => $file->getName(),
                          'ruta_archivo' => $result['path'],
                          'tipo_mime' => $result['mime'],
                          'tamanio_bytes' => $result['size'],
                          'hash_archivo' => hash_file('sha256', WRITEPATH . $result['path']),
                          'created_at' => date('Y-m-d H:i:s'),
                      ]);
+
+                     // Registrar en bitácora
+                     $cliente = $this->auditoriaClienteModel
+                         ->select('clientes.razon_social')
+                         ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
+                         ->where('auditoria_clientes.id_cliente', $idCliente)
+                         ->first();
+
+                     $this->auditoriaLogModel->registrarEvidenciaClienteSubida(
+                         $item['id_auditoria'],
+                         $idAuditoriaItem,
+                         $idCliente,
+                         $file->getName(),
+                         $result['size'],
+                         $cliente['razon_social'] ?? 'Cliente'
+                     );
                  } else {
```

---

### B. AuditoriasConsultorController

**Archivo**: `app/Controllers/Consultor/AuditoriasConsultorController.php`

#### Imports agregados:
```diff
+ use App\Models\AuditoriaLogModel;
```

#### Propiedades agregadas:
```diff
+ protected $auditoriaLogModel;
```

#### Constructor actualizado:
```diff
+ $this->auditoriaLogModel = model('App\Models\AuditoriaLogModel');
```

#### Método `cerrar()` - Cierre de auditoría (líneas 473-486):
```diff
          // PASO 3: Cambiar estado a cerrada
          $this->auditoriaModel->update($idAuditoria, [
              'estado' => 'cerrada',
              'porcentaje_cumplimiento' => $porcentajeGlobal,
              'updated_at' => date('Y-m-d H:i:s')
          ]);
+
+         // Registrar en bitácora
+         $this->auditoriaLogModel->registrarAuditoriaCerrada(
+             $idAuditoria,
+             $porcentajeGlobal,
+             count($clientes)
+         );

          $db->transComplete();
```

#### Nuevo método: `bitacora()` (líneas 509-538):
```php
/**
 * Muestra la bitácora (log) de una auditoría con paginación
 *
 * @param int $idAuditoria
 * @return string Vista de bitácora
 */
public function bitacora(int $idAuditoria)
{
    $auditoria = $this->auditoriaModel->find($idAuditoria);

    if (!$auditoria || $auditoria['id_consultor'] != $this->idConsultor) {
        return redirect()->to('/consultor/auditorias')->with('error', 'Auditoría no encontrada');
    }

    // Obtener bitácora paginada (20 registros por página)
    $resultado = $this->auditoriaLogModel->getBitacoraPaginada($idAuditoria, 20);

    // Obtener estadísticas de acciones
    $estadisticas = $this->auditoriaLogModel->getEstadisticas($idAuditoria);
    $totalAcciones = $this->auditoriaLogModel->contarAcciones($idAuditoria);

    return view('consultor/auditorias/bitacora', [
        'title' => 'Bitácora de Auditoría',
        'auditoria' => $auditoria,
        'logs' => $resultado['logs'],
        'pager' => $resultado['pager'],
        'estadisticas' => $estadisticas,
        'total_acciones' => $totalAcciones,
    ]);
}
```

---

## 4. Vista de Bitácora

**Archivo**: `app/Views/consultor/auditorias/bitacora.php`

### Características:

#### A. Breadcrumb de navegación
```php
Auditorías > Auditoría #123 > Bitácora
```

#### B. Sección de estadísticas
- Total de acciones
- Desglose por tipo de acción con iconos
- Visualización clara de actividad

#### C. Tabla de bitácora con:
- **Fecha/Hora**: Formato dd/mm/yyyy HH:mm
- **Usuario**: Nombre + icono (o "Sistema" si es NULL)
- **Acción**: Badge colorido con icono según tipo
- **Detalles**: Información contextual según acción

#### D. Detalles por tipo de acción:

| Acción | Detalles Mostrados |
|--------|-------------------|
| `comentario_global_guardado` | Título del ítem |
| `comentario_cliente_guardado` | Título del ítem + nombre del cliente |
| `evidencia_global_subida` | Nombre archivo + tamaño en MB |
| `evidencia_cliente_subida` | Nombre archivo + tamaño + cliente |
| `evidencia_global_eliminada` | Nombre del archivo |
| `evidencia_cliente_eliminada` | Nombre del archivo + cliente |
| `auditoria_cerrada` | Porcentaje global + cantidad de clientes |
| `clientes_asignados` | Cantidad de clientes |

#### E. Paginación
- 20 registros por página
- Bootstrap pagination
- Contador de registros visibles

#### F. Leyenda de iconos
- Guía visual de colores y símbolos

---

## 5. Tipos de Acciones Registradas

### Acciones Implementadas:

| Código | Descripción | Color Badge | Icono |
|--------|-------------|-------------|-------|
| `comentario_global_guardado` | Comentario guardado (global) | `bg-info` | `bi-chat-left-text` |
| `comentario_cliente_guardado` | Comentario guardado (por cliente) | `bg-info` | `bi-chat-left-dots` |
| `evidencia_global_subida` | Evidencia subida (global) | `bg-success` | `bi-upload` |
| `evidencia_cliente_subida` | Evidencia subida (por cliente) | `bg-success` | `bi-upload` |
| `evidencia_global_eliminada` | Evidencia eliminada (global) | `bg-danger` | `bi-trash` |
| `evidencia_cliente_eliminada` | Evidencia eliminada (por cliente) | `bg-danger` | `bi-trash` |
| `auditoria_cerrada` | Auditoría cerrada por consultor | `bg-primary` | `bi-check-circle` |
| `clientes_asignados` | Clientes asignados a auditoría | `bg-warning` | `bi-people` |

---

## 6. Formato de Detalles (JSON)

### Ejemplos de JSON almacenado en campo `detalle`:

#### Comentario Global:
```json
{
  "id_auditoria_item": 123,
  "item_titulo": "Verificación de documentación"
}
```

#### Comentario por Cliente:
```json
{
  "id_auditoria_item": 123,
  "id_cliente": 45,
  "item_titulo": "Verificación de documentación",
  "cliente_nombre": "Empresa XYZ S.A."
}
```

#### Evidencia Global Subida:
```json
{
  "id_auditoria_item": 123,
  "nombre_archivo": "documento_verificado.pdf",
  "tamano_bytes": 2456789,
  "tamano_mb": 2.34
}
```

#### Evidencia por Cliente Subida:
```json
{
  "id_auditoria_item": 123,
  "id_cliente": 45,
  "nombre_archivo": "contrato_firmado.pdf",
  "tamano_bytes": 1234567,
  "tamano_mb": 1.18,
  "cliente_nombre": "Empresa XYZ S.A."
}
```

#### Auditoría Cerrada:
```json
{
  "porcentaje_global": 87.5,
  "cantidad_clientes": 3
}
```

#### Clientes Asignados:
```json
{
  "cantidad_clientes": 3,
  "ids_clientes": [45, 67, 89]
}
```

---

## 7. Rutas Necesarias

Agregar en `app/Config/Routes.php`:

```php
// Consultor - Bitácora de auditoría
$routes->get('consultor/auditoria/bitacora/(:num)', 'Consultor\AuditoriasConsultorController::bitacora/$1', ['filter' => 'auth']);
```

---

## 8. Uso desde Vistas

### Link a bitácora desde detalle de auditoría:

```php
<!-- En app/Views/consultor/auditorias/detalle.php -->
<a href="<?= base_url('consultor/auditoria/bitacora/' . $auditoria['id_auditoria']) ?>"
   class="btn btn-info">
    <i class="bi bi-journal-text"></i> Ver Bitácora
</a>
```

### Badge con contador de acciones:

```php
<?php
$totalAcciones = model('App\Models\AuditoriaLogModel')->contarAcciones($idAuditoria);
?>
<span class="badge bg-secondary"><?= $totalAcciones ?> acciones</span>
```

---

## 9. Consultas SQL Útiles

### Ver todas las acciones de una auditoría:
```sql
SELECT
    al.*,
    u.nombre as usuario_nombre,
    u.email as usuario_email
FROM auditoria_log al
LEFT JOIN users u ON u.id_users = al.id_users
WHERE al.id_auditoria = 123
ORDER BY al.created_at DESC;
```

### Estadísticas por tipo de acción:
```sql
SELECT
    accion,
    COUNT(*) as total
FROM auditoria_log
WHERE id_auditoria = 123
GROUP BY accion
ORDER BY total DESC;
```

### Actividad reciente (últimas 24 horas):
```sql
SELECT
    al.*,
    u.nombre as usuario_nombre
FROM auditoria_log al
LEFT JOIN users u ON u.id_users = al.id_users
WHERE al.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY al.created_at DESC;
```

### Usuarios más activos por auditoría:
```sql
SELECT
    u.nombre,
    u.email,
    COUNT(*) as total_acciones
FROM auditoria_log al
JOIN users u ON u.id_users = al.id_users
WHERE al.id_auditoria = 123
GROUP BY al.id_users
ORDER BY total_acciones DESC;
```

### Timeline de auditoría (todas las acciones importantes):
```sql
SELECT
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as momento,
    accion,
    detalle
FROM auditoria_log
WHERE id_auditoria = 123
  AND accion IN ('auditoria_cerrada', 'clientes_asignados', 'comentario_global_guardado')
ORDER BY created_at ASC;
```

---

## 10. Pendientes (Futuras Implementaciones)

### A. Logging de eliminación de evidencias

Cuando se implemente la funcionalidad de eliminar evidencias, agregar:

```php
// Después de eliminar evidencia global
$this->auditoriaLogModel->registrarEvidenciaGlobalEliminada(
    $idAuditoria,
    $idAuditoriaItem,
    $nombreArchivo
);

// Después de eliminar evidencia por cliente
$this->auditoriaLogModel->registrarEvidenciaClienteEliminada(
    $idAuditoria,
    $idAuditoriaItem,
    $idCliente,
    $nombreArchivo,
    $clienteNombre
);
```

### B. Logging de asignación de clientes

En `AuditoriasConsultorController::guardarClientes()`:

```php
// Después de asignar clientes
$this->auditoriaLogModel->registrarClientesAsignados(
    $idAuditoria,
    $clientesSeleccionados
);
```

---

## 11. Testing

### Verificar que se registran acciones:

1. **Comentario Global**:
   - Diligenciar ítem global → guardar comentario
   - Verificar registro en `auditoria_log` con acción `comentario_global_guardado`

2. **Comentario por Cliente**:
   - Diligenciar ítem por cliente → guardar comentario
   - Verificar registro con acción `comentario_cliente_guardado`

3. **Evidencia Global**:
   - Subir archivo en ítem global
   - Verificar registro con acción `evidencia_global_subida` y detalle con nombre/tamaño

4. **Evidencia por Cliente**:
   - Subir archivo en ítem por cliente
   - Verificar registro con acción `evidencia_cliente_subida`

5. **Cierre de Auditoría**:
   - Cerrar auditoría como consultor
   - Verificar registro con acción `auditoria_cerrada` y porcentaje

6. **Vista de Bitácora**:
   - Acceder a `/consultor/auditoria/bitacora/{id}`
   - Verificar que muestra todos los registros
   - Verificar paginación si hay más de 20 registros
   - Verificar estadísticas en header

---

## 12. Resumen de Archivos Creados/Modificados

### Archivos Nuevos:
- ✅ `app/Database/Migrations/2025-10-16-210000_CreateAuditoriaLogTable.php`
- ✅ `app/Models/AuditoriaLogModel.php`
- ✅ `app/Views/consultor/auditorias/bitacora.php`

### Archivos Modificados:
- ✅ `app/Controllers/Proveedor/AuditoriasProveedorController.php`
  - Agregado import `AuditoriaLogModel`
  - Agregada propiedad `$auditoriaLogModel`
  - Inicialización en constructor
  - Logging en `guardarItemGlobal()` (comentario + evidencias)
  - Logging en `guardarItemPorCliente()` (comentario + evidencias)

- ✅ `app/Controllers/Consultor/AuditoriasConsultorController.php`
  - Agregado import `AuditoriaLogModel`
  - Agregada propiedad `$auditoriaLogModel`
  - Inicialización en constructor
  - Logging en método `cerrar()`
  - Nuevo método `bitacora()` con paginación

---

## 13. Beneficios del Sistema

✅ **Trazabilidad completa**: Registro de todas las acciones importantes
✅ **Auditoría interna**: Saber quién hizo qué y cuándo
✅ **Debugging**: Facilita identificar problemas en el flujo de trabajo
✅ **Compliance**: Cumplimiento de normativas que requieren audit trail
✅ **Estadísticas**: Métricas de actividad por auditoría
✅ **Escalable**: Fácil agregar nuevos tipos de acciones
✅ **Performante**: Índices en campos clave para consultas rápidas

---

## Implementación completada ✅

Sistema de bitácora funcional con:
- ✅ Migración de tabla
- ✅ Modelo con métodos específicos
- ✅ Logging automático en controladores
- ✅ Vista con paginación y estadísticas
- ✅ Detalles JSON estructurados
- ✅ Iconos y badges por tipo de acción
