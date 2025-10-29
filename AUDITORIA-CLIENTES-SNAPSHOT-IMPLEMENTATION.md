# Auditoría Clientes - Snapshot de Contratos

## Resumen

Implementación del sistema de snapshot de clientes y contratos al crear auditorías. Cuando el consultor crea una auditoría y asigna clientes, el sistema ahora guarda en `auditoria_clientes` un snapshot que incluye:

- `id_auditoria`: Auditoría a la que pertenece
- `id_cliente`: Cliente asignado
- `id_contrato`: Contrato activo al momento de la creación (snapshot)
- `id_servicio`: Servicio del contrato (snapshot)

Este snapshot garantiza que la auditoría mantenga la información del contrato vigente al momento de su creación, independientemente de cambios posteriores en los contratos.

---

## Flujo Implementado

### 1. Paso 2: Asignación de Clientes (Setup)

**Consultor selecciona clientes desde contratos activos:**

- Se cargan clientes que tienen contratos activos con el proveedor seleccionado
- Se muestra información del servicio asociado al contrato
- El formulario incluye data-attributes con `id_contrato` y `id_servicio`
- JavaScript recopila estos datos al hacer submit
- Se envía al backend como arrays asociativos indexados por `id_cliente`

### 2. Paso 2: Guardado de Snapshot

**Controller recibe y guarda snapshot en `auditoria_clientes`:**

- Recibe arrays: `clientes[]`, `contrato[id_cliente]`, `servicio[id_cliente]`
- Elimina asignaciones anteriores (permite re-editar en borrador)
- Inserta registros en `auditoria_clientes` con el snapshot completo

### 3. Paso 3: Creación de Items por Cliente

**Durante el envío de invitación, se crean items basados en el snapshot:**

- Se leen clientes desde `auditoria_clientes`
- Se crean items globales en `auditoria_items`
- Se crean items por cliente en `auditoria_item_cliente` para cada cliente del snapshot
- La auditoría cambia a estado `en_proveedor`

### 4. Provider Wizard: Diligenciamiento

**El proveedor diligencie usando el snapshot:**

- Los tabs de clientes se generan desde `auditoria_item_cliente`
- Los datos de cliente se obtienen mediante JOIN con `clientes`
- El snapshot garantiza que solo se muestran los clientes asignados originalmente

---

## Archivos Modificados

### 1. app/Models/ContratoModel.php

**Método modificado: `getClientesByProveedor()`**

```diff
/**
- * Obtiene clientes distintos por proveedor
+ * Obtiene clientes distintos por proveedor con información del contrato
+ * Incluye id_contrato y id_servicio para snapshot en auditorias
 */
public function getClientesByProveedor(int $idProveedor): array
{
-    return $this->select('DISTINCT clientes.*')
+    return $this->select('clientes.*,
+                          contratos_proveedor_cliente.id_contrato,
+                          contratos_proveedor_cliente.id_servicio,
+                          servicios.nombre as servicio_nombre')
                ->join('clientes', 'clientes.id_cliente = contratos_proveedor_cliente.id_cliente')
+                ->join('servicios', 'servicios.id_servicio = contratos_proveedor_cliente.id_servicio')
                ->where('contratos_proveedor_cliente.id_proveedor', $idProveedor)
                ->where('contratos_proveedor_cliente.estado', 'activo')
                ->where('clientes.estado', 'activo')
+                ->groupBy('clientes.id_cliente')
                ->findAll();
}
```

**Cambios:**
- Ahora retorna `id_contrato`, `id_servicio` y `servicio_nombre`
- Incluye JOIN con `servicios` para obtener el nombre del servicio
- Usa `groupBy` para evitar duplicados si hay múltiples contratos

---

### 2. app/Controllers/Consultor/AuditoriasSetupController.php

**Método modificado: `guardarClientesSetup()`**

```diff
/**
 * PASO 2: Guarda los clientes asignados a la auditoría
+ * Ahora incluye snapshot de id_contrato y id_servicio
 */
public function guardarClientesSetup(int $idAuditoria)
{
    // ... validaciones ...

    $clientesSeleccionados = $this->request->getPost('clientes') ?? [];
+    $contratos = $this->request->getPost('contrato') ?? [];
+    $servicios = $this->request->getPost('servicio') ?? [];

    // Eliminar asignaciones anteriores
    $this->auditoriaClienteModel->where('id_auditoria', $idAuditoria)->delete();

-    // Crear nuevas asignaciones
+    // Crear nuevas asignaciones con snapshot de contrato y servicio
    foreach ($clientesSeleccionados as $idCliente) {
        $this->auditoriaClienteModel->insert([
            'id_auditoria' => $idAuditoria,
            'id_cliente' => $idCliente,
+            'id_contrato' => $contratos[$idCliente] ?? null,
+            'id_servicio' => $servicios[$idCliente] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Redirigir al PASO 3...
}
```

**Cambios:**
- Recibe arrays `contrato[]` y `servicio[]` del formulario
- Los arrays están indexados por `id_cliente`
- Guarda `id_contrato` y `id_servicio` en el snapshot

---

### 3. app/Views/consultor/auditorias/asignar_clientes_setup.php

**A) Modificación del formulario:**

```diff
- <form method="post" action="/consultor/auditorias/<?= $auditoria['id_auditoria'] ?>/asignar-clientes-setup">
+ <form method="post" action="/consultor/auditorias/<?= $auditoria['id_auditoria'] ?>/asignar-clientes-setup" id="formAsignarClientes">
     <?= csrf_field() ?>
```

**B) Modificación de checkboxes con data-attributes:**

```diff
<input class="form-check-input"
       type="checkbox"
       name="clientes[]"
       value="<?= $cliente['id_cliente'] ?>"
       id="cliente<?= $cliente['id_cliente'] ?>"
+       data-contrato="<?= $cliente['id_contrato'] ?>"
+       data-servicio="<?= $cliente['id_servicio'] ?>"
       <?= in_array($cliente['id_cliente'], $asignados) ? 'checked' : '' ?>>
<label class="form-check-label w-100" for="cliente<?= $cliente['id_cliente'] ?>">
    <div>
        <strong><?= esc($cliente['razon_social']) ?></strong>
        <br>
        <small class="text-muted">
            <i class="bi bi-card-text"></i> NIT: <?= esc($cliente['nit']) ?>
        </small>
+        <?php if (!empty($cliente['servicio_nombre'])): ?>
+            <br>
+            <small class="text-primary">
+                <i class="bi bi-briefcase"></i> <?= esc($cliente['servicio_nombre']) ?>
+            </small>
+        <?php endif; ?>
        <!-- ... email ... -->
    </div>
</label>
```

**C) Nuevo script JavaScript al final del archivo:**

```javascript
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formAsignarClientes');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        // Recopilar datos de contratos y servicios de los clientes seleccionados
        const checkboxes = form.querySelectorAll('input[name="clientes[]"]:checked');

        checkboxes.forEach(checkbox => {
            const idCliente = checkbox.value;
            const idContrato = checkbox.getAttribute('data-contrato');
            const idServicio = checkbox.getAttribute('data-servicio');

            // Crear campos ocultos para enviar el snapshot de contrato y servicio
            if (idContrato) {
                const inputContrato = document.createElement('input');
                inputContrato.type = 'hidden';
                inputContrato.name = `contrato[${idCliente}]`;
                inputContrato.value = idContrato;
                form.appendChild(inputContrato);
            }

            if (idServicio) {
                const inputServicio = document.createElement('input');
                inputServicio.type = 'hidden';
                inputServicio.name = `servicio[${idCliente}]`;
                inputServicio.value = idServicio;
                form.appendChild(inputServicio);
            }
        });
    });
});
</script>
```

**Cambios:**
- Formulario tiene ID para referencia JavaScript
- Checkboxes incluyen `data-contrato` y `data-servicio`
- Se muestra el nombre del servicio en la tarjeta de cada cliente
- JavaScript captura los data-attributes al hacer submit
- Crea campos hidden dinámicamente con formato `contrato[id_cliente]` y `servicio[id_cliente]`

---

### 4. app/Models/AuditoriaClienteModel.php

**Ya existía correctamente implementado:**

```php
protected $allowedFields = [
    'id_auditoria',
    'id_cliente',
    'id_contrato',
    'id_servicio',
    'created_at',
];

public function getClientesByAuditoria(int $idAuditoria): array
{
    return $this->select('auditoria_clientes.*, clientes.razon_social, clientes.nit')
                ->join('clientes', 'clientes.id_cliente = auditoria_clientes.id_cliente')
                ->where('auditoria_clientes.id_auditoria', $idAuditoria)
                ->findAll();
}
```

- Incluye `id_contrato` y `id_servicio` en `allowedFields`
- El método `getClientesByAuditoria()` hace JOIN con `clientes` para obtener datos actuales
- El snapshot se mantiene en `auditoria_clientes.id_contrato` e `id_servicio`

---

### 5. app/Controllers/Proveedor/AuditoriasProveedorController.php

**Método: `getItemsConAlcance()` - Sin cambios requeridos**

El método ya funciona correctamente:

```php
// Para ítems por cliente
$item['items_cliente'] = $this->auditoriaItemClienteModel
    ->select('auditoria_item_cliente.*, clientes.razon_social, clientes.nit')
    ->join('clientes', 'clientes.id_cliente = auditoria_item_cliente.id_cliente')
    ->where('id_auditoria_item', $item['id_auditoria_item'])
    ->findAll();
```

**Explicación:**
- Los items en `auditoria_item_cliente` fueron creados en el Paso 3 basándose en `auditoria_clientes`
- Solo existen registros para los clientes del snapshot
- El JOIN con `clientes` obtiene datos actualizados de razón social y NIT
- El snapshot original está preservado en `auditoria_clientes`

---

## Nuevo Seeder de Prueba

### app/Database/Seeds/AuditoriaConClientesTestSeeder.php

**Propósito:** Crear datos de prueba completos para validar el flujo.

**Requisitos previos:**
- Proveedores (id_proveedor = 1, 2)
- Clientes (id_cliente = 1, 2, 3)
- Servicios (id_servicio = 1, 2)
- Consultores (id_consultor = 1)
- Items en `items_banco` con diferentes alcances

**Qué crea:**
1. **3 contratos activos** entre proveedor 1 y clientes 1, 2, 3
2. **1 auditoría** en estado `en_proveedor`
3. **Snapshot en `auditoria_clientes`** con los 3 clientes (incluye id_contrato e id_servicio)
4. **Ítems globales** (3 primeros de items_banco)
5. **Ítems por cliente** (2 primeros de items_banco con alcance `por_cliente`, uno por cada cliente)

**Ejecución:**
```bash
php spark db:seed AuditoriaConClientesTestSeeder
```

**Output esperado:**
```
Contratos creados: IDs 1, 2, 3
Auditoría creada: ID 1
Snapshot de 3 clientes insertado
3 ítems globales creados
2 ítems por cliente creados (x3 clientes cada uno)

========================================
SEEDER COMPLETADO EXITOSAMENTE
========================================
Auditoría ID: 1
Estado: en_proveedor
Contratos: 3 (IDs 1, 2, 3)
Clientes en snapshot: 3
Ítems globales: 3
Ítems por cliente: 2 (cada uno con 3 clientes)

Puedes acceder al wizard del proveedor en:
/proveedor/auditoria/1
========================================
```

---

## Esquema de Base de Datos

### Tabla: `auditoria_clientes`

**Estructura existente (sin cambios necesarios):**

```sql
CREATE TABLE auditoria_clientes (
    id_auditoria_cliente INT AUTO_INCREMENT PRIMARY KEY,
    id_auditoria INT NOT NULL,
    id_cliente INT NOT NULL,
    id_contrato INT NULL,           -- Snapshot del contrato activo
    id_servicio INT NULL,           -- Snapshot del servicio
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_auditoria) REFERENCES auditorias(id_auditoria) ON DELETE CASCADE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_contrato) REFERENCES contratos_proveedor_cliente(id_contrato),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
);
```

**Campos clave:**
- `id_contrato`: Snapshot del contrato vigente al crear la auditoría
- `id_servicio`: Snapshot del servicio del contrato

**Nota:** Si la migración no tiene estas columnas, agregarlas:

```php
$this->forge->addColumn('auditoria_clientes', [
    'id_contrato' => [
        'type' => 'INT',
        'null' => true,
        'after' => 'id_cliente'
    ],
    'id_servicio' => [
        'type' => 'INT',
        'null' => true,
        'after' => 'id_contrato'
    ]
]);
```

---

## Validación y Testing

### Escenario 1: Creación de Auditoría con Clientes

1. **Setup inicial:**
   - Crear proveedor con al menos 2 clientes con contratos activos
   - Ejecutar seeder de items_banco si no existen

2. **Paso 1:** Crear auditoría básica
   - Seleccionar proveedor
   - Completar código y versión
   - Guardar → Estado: `borrador`

3. **Paso 2:** Asignar clientes
   - Verificar que se cargan solo clientes con contratos activos
   - Verificar que se muestra el servicio de cada contrato
   - Seleccionar al menos 2 clientes
   - Guardar

4. **Verificación en DB:**
   ```sql
   SELECT * FROM auditoria_clientes WHERE id_auditoria = ?;
   ```
   - Debe mostrar `id_contrato` e `id_servicio` para cada cliente

5. **Paso 3:** Enviar invitación
   - Verificar que se crean items globales
   - Verificar que se crean items por cliente para CADA cliente del snapshot

6. **Verificación en DB:**
   ```sql
   SELECT aic.*, c.razon_social
   FROM auditoria_item_cliente aic
   JOIN auditoria_items ai ON ai.id_auditoria_item = aic.id_auditoria_item
   JOIN clientes c ON c.id_cliente = aic.id_cliente
   WHERE ai.id_auditoria = ?;
   ```
   - Debe listar todos los items por cliente con los clientes correctos

### Escenario 2: Provider Wizard

1. **Login como proveedor**
2. **Acceder al wizard:** `/proveedor/auditoria/{id}`
3. **Verificar:**
   - Los tabs de clientes coinciden con el snapshot
   - Se pueden diligenciar items por cada cliente
   - El progreso se calcula correctamente
   - Al finalizar, pasa a estado `en_consultor`

### Escenario 3: Cambios en Contratos POST-Auditoría

1. **Crear auditoría** con cliente 1
2. **Cambiar el contrato** del cliente 1 a `inactivo` o modificar su servicio
3. **Verificar wizard del proveedor:**
   - El cliente 1 debe seguir apareciendo en la auditoría
   - Los datos del snapshot (`id_contrato`, `id_servicio`) no cambian
   - La auditoría mantiene la información original

**Este es el comportamiento correcto del snapshot.**

---

## Diagrama de Flujo

```
[Consultor crea auditoría]
         ↓
[Paso 1: Info básica] → Estado: borrador
         ↓
[Paso 2: Asignar clientes]
    → ContratoModel.getClientesByProveedor()
      ↓ (retorna id_contrato, id_servicio)
    → Vista muestra multiselect con data-attributes
      ↓ (usuario selecciona clientes)
    → JavaScript captura data-attributes al submit
      ↓ (envía arrays contrato[] y servicio[])
    → AuditoriasSetupController.guardarClientesSetup()
      ↓ (inserta en auditoria_clientes con snapshot)
    → SNAPSHOT CREADO ✓
         ↓
[Paso 3: Enviar invitación]
    → Lee clientes desde auditoria_clientes
    → Crea items globales
    → Crea items por cliente (auditoria_item_cliente)
    → Estado: en_proveedor
         ↓
[Provider wizard]
    → Lee items con getItemsConAlcance()
    → JOIN con clientes para datos actuales
    → Tabs basados en auditoria_item_cliente
    → (que fueron creados desde snapshot)
         ↓
[Diligenciamiento completo]
```

---

## Beneficios del Snapshot

1. **Integridad histórica:** La auditoría mantiene la información del contrato vigente al momento de creación

2. **Independencia de cambios:** Si el contrato se desactiva o cambia después, la auditoría no se ve afectada

3. **Trazabilidad:** Se puede rastrear qué contrato y servicio estaban activos cuando se creó la auditoría

4. **Reportes precisos:** Los PDFs y reportes reflejan la situación al momento de la auditoría, no la actual

5. **Auditorías múltiples:** Se pueden tener varias auditorías del mismo proveedor en diferentes momentos, cada una con su snapshot

---

## Resumen de Cambios

| Archivo | Tipo | Cambio Principal |
|---------|------|------------------|
| ContratoModel.php | Model | Retorna id_contrato, id_servicio y servicio_nombre |
| AuditoriasSetupController.php | Controller | Guarda snapshot en auditoria_clientes |
| asignar_clientes_setup.php | View | Data-attributes + JavaScript para capturar snapshot |
| AuditoriaConClientesTestSeeder.php | Seeder | Datos de prueba completos |

**Total de archivos modificados:** 3
**Total de archivos nuevos:** 1 (seeder)
**Líneas de código agregadas:** ~150
**Funcionalidad nueva:** Sistema de snapshot de contratos en auditorías

---

## Notas Finales

- El sistema ya funcionaba correctamente en términos de mostrar clientes en el wizard
- La mejora principal es guardar el **snapshot explícito** de `id_contrato` e `id_servicio`
- Esto permite rastrear qué contrato estaba vigente al momento de la auditoría
- Los datos de `clientes` (razón social, NIT) se obtienen siempre actualizados mediante JOIN
- El snapshot garantiza que solo se muestren los clientes asignados originalmente

**Próximos pasos sugeridos:**
1. Ejecutar el seeder de prueba
2. Validar el flujo completo en desarrollo
3. Agregar reportes que muestren el servicio desde el snapshot
4. Considerar agregar campos adicionales al snapshot si se requiere (ej: número de personas del contrato)
