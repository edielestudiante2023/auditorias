# Item Completion Logic Implementation

Complete implementation of item completion tracking with separate logic for global and per-client items.

---

## Overview

This implementation adds sophisticated completion tracking that:
1. Marks global items complete when `comentario_proveedor` is filled
2. Marks per-client items complete when `comentario_proveedor_cliente` is filled for ALL assigned clients
3. Shows detailed progress breakdown (global vs per-client)
4. Displays completion badges per client
5. Adds Bootstrap toasts with smooth scroll on save
6. Disables "Finalizar" button until 100% complete

---

## Controller Changes

### File: `app/Controllers/Proveedor/AuditoriasProveedorController.php`

#### 1. Updated `calcularProgreso()` Method

**Before:**
```php
private function calcularProgreso(int $idAuditoria): float
{
    // Returned single percentage value
}
```

**After:**
```php
private function calcularProgreso(int $idAuditoria): array
{
    // Returns detailed array with breakdown
    return [
        'porcentaje_total' => 85.5,
        'globales_completos' => 5,
        'globales_total' => 6,
        'por_cliente_completos' => 8,
        'por_cliente_total' => 10,
        'total_completados' => 13,
        'total' => 16,
    ];
}
```

**Key Changes:**
- Now returns array instead of float
- Separates global and per-client item counts
- Global items: `alcance IN ('global', 'mixto')`
- Per-client items: `alcance = 'por_cliente'` × number of clients
- Completion requires non-empty comment (`!= ''`)

**Diff:**
```diff
-    private function calcularProgreso(int $idAuditoria): float
+    private function calcularProgreso(int $idAuditoria): array
     {
         $db = \Config\Database::connect();

-        // Total de ítems (considerando por_cliente multiplicado por clientes)
-        $query = $db->query("
-            SELECT
-                SUM(CASE
-                    WHEN ib.alcance = 'por_cliente' THEN (
-                        SELECT COUNT(*) FROM auditoria_clientes WHERE id_auditoria = ai.id_auditoria
-                    )
-                    ELSE 1
-                END) as total
-            FROM auditoria_items ai
-            JOIN items_banco ib ON ib.id_item = ai.id_item
-            WHERE ai.id_auditoria = ?
-        ", [$idAuditoria]);
+        // === ÍTEMS GLOBALES ===
+        $queryGlobalesTotal = $db->query("
+            SELECT COUNT(*) as total
+            FROM auditoria_items ai
+            JOIN items_banco ib ON ib.id_item = ai.id_item
+            WHERE ai.id_auditoria = ?
+              AND ib.alcance IN ('global', 'mixto')
+        ", [$idAuditoria]);
+
+        $globalesTotal = $queryGlobalesTotal->getRow()->total ?? 0;
+
+        $queryGlobalesCompletos = $db->query("
+            SELECT COUNT(*) as completados
+            FROM auditoria_items ai
+            JOIN items_banco ib ON ib.id_item = ai.id_item
+            WHERE ai.id_auditoria = ?
+              AND ib.alcance IN ('global', 'mixto')
+              AND ai.comentario_proveedor IS NOT NULL
+              AND ai.comentario_proveedor != ''
+        ", [$idAuditoria]);
+
+        // ... (similar for per-client items)

-        return round(($completados / $total) * 100, 2);
+        return [
+            'porcentaje_total' => $porcentaje,
+            'globales_completos' => $globalesCompletos,
+            'globales_total' => $globalesTotal,
+            'por_cliente_completos' => $porClienteCompletos,
+            'por_cliente_total' => $porClienteTotal,
+            'total_completados' => $completados,
+            'total' => $total,
+        ];
     }
```

---

#### 2. Updated `getItemsConAlcance()` Method

**Added completion status for each item and client**

**Diff:**
```diff
-    private function getItemsConAlcance(int $idAuditoria): array
+    /**
+     * Obtiene items con alcance, evidencias y estado de completitud
+     */
+    private function getItemsConAlcance(int $idAuditoria): array
     {
         // ... existing code ...

         foreach ($items as &$item) {
-            if ($item['alcance'] === 'global') {
+            if ($item['alcance'] === 'global' || $item['alcance'] === 'mixto') {
                 $item['evidencias'] = $this->evidenciaModel
                     ->where('id_auditoria_item', $item['id_auditoria_item'])
                     ->findAll();
+
+                // Marcar como completo si tiene comentario
+                $item['completo'] = !empty($item['comentario_proveedor']);

-            } else {
+            } else { // por_cliente
                 // ... existing code ...

+                // Marcar completitud por cada item-cliente
+                $todosClientesCompletos = true;
                 foreach ($item['items_cliente'] as &$itemCliente) {
                     $itemCliente['evidencias'] = $this->evidenciaClienteModel
                         ->where('id_auditoria_item_cliente', $itemCliente['id_auditoria_item_cliente'])
                         ->findAll();
+
+                    // Marcar cliente como completo si tiene comentario
+                    $itemCliente['completo'] = !empty($itemCliente['comentario_proveedor_cliente']);
+
+                    if (!$itemCliente['completo']) {
+                        $todosClientesCompletos = false;
+                    }
                 }
+
+                // El ítem está completo solo si TODOS sus clientes están completos
+                $item['completo'] = $todosClientesCompletos && !empty($item['items_cliente']);
             }
         }

         return $items;
     }
```

---

#### 3. Updated `index()` Method

**Adapted to use new array return format**

**Diff:**
```diff
     foreach ($auditorias as &$auditoria) {
-        $auditoria['progreso'] = $this->calcularProgreso($auditoria['id_auditoria']);
+        $progreso = $this->calcularProgreso($auditoria['id_auditoria']);
+        $auditoria['progreso'] = $progreso['porcentaje_total'];
     }
```

---

## View Changes

### File: `app/Views/proveedor/auditorias/wizard.php`

#### 1. Progress Bar Section (Lines 19-100)

**New Features:**
- Total progress bar (unchanged visually)
- Breakdown cards showing:
  - **Ítems Globales**: X / Y complete with mini progress bar
  - **Ítems Por Cliente**: X / Y complete with mini progress bar

**Diff:**
```diff
 <!-- Progreso General -->
-<div class="card mb-4 border-warning shadow-sm">
+<div class="card mb-4 border-primary shadow-sm">
     <div class="card-body">
-        <div class="d-flex justify-content-between align-items-center mb-2">
+        <div class="d-flex justify-content-between align-items-center mb-3">
             <h5 class="mb-0"><i class="bi bi-graph-up"></i> Progreso General</h5>
-            <span class="badge bg-<?= $progreso >= 100 ? 'success' : 'warning' ?> fs-6">
-                <?= number_format($progreso, 0) ?>%
+            <span class="badge bg-<?= $progreso['porcentaje_total'] >= 100 ? 'success' : 'warning' ?> fs-6">
+                <?= number_format($progreso['porcentaje_total'], 0) ?>%
             </span>
         </div>

+        <!-- Barra de progreso total -->
-        <div class="progress" style="height: 30px;">
-            <div class="progress-bar <?= $progreso >= 100 ? 'bg-success' : 'bg-primary' ?>"
+        <div class="progress mb-3" style="height: 30px;">
+            <div class="progress-bar <?= $progreso['porcentaje_total'] >= 100 ? 'bg-success' : 'bg-primary' ?>"
                  role="progressbar"
-                 style="width: <?= $progreso ?>%;">
-                <?= number_format($progreso, 0) ?>%
+                 style="width: <?= $progreso['porcentaje_total'] ?>%;">
+                <?= number_format($progreso['porcentaje_total'], 0) ?>%
             </div>
         </div>
+
+        <!-- Desglose de progreso -->
+        <div class="row g-2">
+            <!-- Ítems Globales -->
+            <div class="col-md-6">
+                <div class="border rounded p-2 bg-light">
+                    <div class="d-flex justify-content-between align-items-center mb-1">
+                        <small class="fw-bold text-primary">
+                            <i class="bi bi-globe"></i> Ítems Globales
+                        </small>
+                        <small class="badge bg-primary">
+                            <?= $progreso['globales_completos'] ?> / <?= $progreso['globales_total'] ?>
+                        </small>
+                    </div>
+                    <div class="progress" style="height: 8px;">
+                        <?php
+                        $pctGlobales = $progreso['globales_total'] > 0
+                            ? ($progreso['globales_completos'] / $progreso['globales_total']) * 100
+                            : 0;
+                        ?>
+                        <div class="progress-bar bg-primary" style="width: <?= $pctGlobales ?>%;"></div>
+                    </div>
+                </div>
+            </div>
+
+            <!-- Ítems Por Cliente -->
+            <div class="col-md-6">
+                <div class="border rounded p-2 bg-light">
+                    <div class="d-flex justify-content-between align-items-center mb-1">
+                        <small class="fw-bold text-warning">
+                            <i class="bi bi-building"></i> Ítems Por Cliente
+                        </small>
+                        <small class="badge bg-warning">
+                            <?= $progreso['por_cliente_completos'] ?> / <?= $progreso['por_cliente_total'] ?>
+                        </small>
+                    </div>
+                    <div class="progress" style="height: 8px;">
+                        <?php
+                        $pctPorCliente = $progreso['por_cliente_total'] > 0
+                            ? ($progreso['por_cliente_completos'] / $progreso['por_cliente_total']) * 100
+                            : 0;
+                        ?>
+                        <div class="progress-bar bg-warning" style="width: <?= $pctPorCliente %>%;"></div>
+                    </div>
+                </div>
+            </div>
+        </div>
     </div>
 </div>
```

---

#### 2. Client Badge Section (Line 277-288)

**Added "Cliente Completo" green badge**

**Diff:**
```diff
-<div class="alert alert-secondary mb-3">
+<div class="alert alert-secondary mb-3 d-flex justify-content-between align-items-center">
+    <div>
         <i class="bi bi-building"></i>
         <strong>Cliente:</strong> <?= esc($cliente['razon_social']) ?>
         (NIT: <?= esc($cliente['nit']) ?>)
+    </div>
+    <?php if ($itemCliente && !empty($itemCliente['comentario_proveedor_cliente'])): ?>
+        <span class="badge bg-success">
+            <i class="bi bi-check-circle-fill"></i> Cliente Completo
+        </span>
+    <?php endif; ?>
 </div>
```

---

#### 3. Save Button Update (Line 356-358)

**Added data attribute for JavaScript**

**Diff:**
```diff
-<button type="submit" class="btn btn-success">
+<button type="submit" class="btn btn-success save-cliente-btn" data-cliente-nombre="<?= esc($cliente['razon_social']) ?>">
     <i class="bi bi-save"></i> Guardar para <?= esc($cliente['razon_social']) ?>
 </button>
```

---

#### 4. Finalizar Button (Line 374-395)

**Disabled until 100% complete**

**Diff:**
```diff
 <p class="text-muted">
     Una vez finalizada, la auditoría será enviada al consultor para su revisión.
-    <?php if ($progreso < 100): ?>
+    <?php if ($progreso['porcentaje_total'] < 100): ?>
         <br><strong class="text-warning">Recuerde completar todos los ítems antes de finalizar.</strong>
+        <br><small class="text-danger">Faltan <?= $progreso['total'] - $progreso['total_completados'] ?> ítems por completar</small>
     <?php endif; ?>
 </p>
 <form method="post" ...>
     <button type="submit"
             class="btn btn-primary btn-lg"
-            <?= $progreso < 100 ? 'disabled' : '' ?>>
+            <?= $progreso['porcentaje_total'] < 100 ? 'disabled' : '' ?>>
         <i class="bi bi-send-check"></i> Finalizar y Enviar a Revisión
     </button>
+    <?php if ($progreso['porcentaje_total'] < 100): ?>
+        <div class="mt-2">
+            <small class="text-muted">El botón se habilitará cuando complete el 100% de los ítems</small>
+        </div>
+    <?php endif; ?>
 </form>
```

---

#### 5. Bootstrap Toast and JavaScript (Lines 405-476)

**New Code - Toast Notification System**

```html
<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert">
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="toastMessage">Guardado exitosamente</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show toast on successful save from flash messages
    <?php if (session()->getFlashdata('success')): ?>
        showSuccessToast('<?= addslashes(session()->getFlashdata('success')) ?>');
        scrollToTop();
    <?php endif; ?>

    // Add event listeners to save-cliente-btn buttons
    const saveButtons = document.querySelectorAll('.save-cliente-btn');
    saveButtons.forEach(button => {
        button.closest('form').addEventListener('submit', function(e) {
            const clienteNombre = button.getAttribute('data-cliente-nombre');
            sessionStorage.setItem('lastSavedCliente', clienteNombre);
        });
    });

    // Check if we just saved and show toast
    const lastSavedCliente = sessionStorage.getItem('lastSavedCliente');
    if (lastSavedCliente && <?= session()->getFlashdata('success') ? 'true' : 'false' ?>) {
        showSuccessToast('Guardado exitosamente para ' + lastSavedCliente);
        sessionStorage.removeItem('lastSavedCliente');
        scrollToTop();
    }
});

function showSuccessToast(message) {
    const toastEl = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    toastMessage.textContent = message;

    const toast = new bootstrap.Toast(toastEl, {
        animation: true,
        autohide: true,
        delay: 4000
    });

    toast.show();
}

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>
```

**Features:**
- ✅ Bootstrap 5 Toast component
- ✅ Auto-shows on flash message success
- ✅ Personalized message with client name
- ✅ Smooth scroll to top after save
- ✅ Auto-hides after 4 seconds
- ✅ Uses sessionStorage to persist client name across page reload

---

## Completion Logic Rules

### Global Items

**Complete when:**
- `comentario_proveedor IS NOT NULL`
- `comentario_proveedor != ''`

**Not required:**
- Evidences (optional)

### Per-Client Items

**Complete when:**
- **ALL** assigned clients have:
  - `comentario_proveedor_cliente IS NOT NULL`
  - `comentario_proveedor_cliente != ''`

**Individual client complete when:**
- That specific client has non-empty `comentario_proveedor_cliente`

**Not required:**
- Evidences (optional per client)

### Progress Calculation

**Global Items:**
```
Completados = COUNT(items WHERE alcance IN ('global','mixto') AND comentario != '')
Total = COUNT(items WHERE alcance IN ('global','mixto'))
```

**Per-Client Items:**
```
Completados = COUNT(auditoria_item_cliente WHERE comentario != '')
Total = COUNT(items WHERE alcance='por_cliente') × COUNT(clientes asignados)
```

**Overall:**
```
Porcentaje = ((Globales Completados + Por Cliente Completados) / (Globales Total + Por Cliente Total)) × 100
```

---

## Visual Indicators

### Progress Bar Card
- **Blue border** when in progress
- **Success badge** when 100% complete
- **Two mini progress bars** showing breakdown

### Item Cards
- **Green border** when item complete
- **Green "Completado" badge** when item complete
- **Yellow "Pendiente" badge** when incomplete

### Client Tabs
- **Green checkmark (✓)** on tab if client complete
- **Green "Cliente Completo" badge** in alert when complete

### Finalizar Button
- **Disabled** (gray) until 100%
- **Active** (blue) at 100%
- **Helper text** showing items remaining

### Toast Notifications
- **Green toast** top-right corner
- **Personalized message** with client name
- **Auto-hide** after 4 seconds
- **Smooth scroll** to top

---

## Testing Scenarios

### Scenario 1: Global Item
1. Navigate to audit wizard
2. Find a global item (badge: "Global")
3. Fill `comentario_proveedor` field
4. Click "Guardar Ítem"
5. **Expected**: Green toast appears, scrolls to top, item shows "Completado" badge

### Scenario 2: Per-Client Item - Single Client
1. Find a per-client item (badge: "Por Cliente")
2. Click on first client tab
3. Fill `comentario_proveedor_cliente`
4. Click "Guardar para [Cliente]"
5. **Expected**:
   - Green toast: "Guardado exitosamente para [Cliente]"
   - Tab shows green checkmark (✓)
   - Alert shows "Cliente Completo" badge
   - Item NOT marked complete (if more clients exist)

### Scenario 3: Per-Client Item - All Clients
1. Complete all clients for a per-client item
2. **Expected**:
   - All tabs show green checkmarks
   - Item card shows green border + "Completado" badge
   - Progress bar updates

### Scenario 4: Progress Tracking
1. View progress card at top
2. **Expected**:
   - Total percentage updates
   - "Ítems Globales" breakdown shows X / Y
   - "Ítems Por Cliente" breakdown shows X / Y
   - Mini progress bars fill accordingly

### Scenario 5: Finalizar Button
1. With incomplete items (< 100%)
   - **Expected**: Button disabled, shows "Faltan X ítems"
2. With all items complete (100%)
   - **Expected**: Button enabled, can click to finalize

---

## Browser Compatibility

- **Toast**: Bootstrap 5 (works in all modern browsers)
- **sessionStorage**: Supported in IE8+, all modern browsers
- **scrollTo with behavior**: Chrome 61+, Firefox 36+, Safari 14+
  - Falls back to instant scroll in older browsers

---

## File Summary

### Modified Files
1. `app/Controllers/Proveedor/AuditoriasProveedorController.php`
   - calcularProgreso() - returns array
   - getItemsConAlcance() - adds completion flags
   - index() - adapted to array format

2. `app/Views/proveedor/auditorias/wizard.php`
   - Progress card with breakdown
   - Client completion badges
   - Save button with data attributes
   - Disabled finalizar until 100%
   - Bootstrap toast HTML
   - JavaScript for toasts and scroll

### No New Files Required
All JavaScript is inline in the view (no separate .js file needed)

---

## Future Enhancements

Possible improvements:
1. **Evidence requirement**: Add `requiere_evidencia` field to `items_banco` table
2. **Real-time progress**: Use AJAX to update progress without page reload
3. **Visual feedback**: Add loading spinner on save button
4. **Validation**: Client-side validation before submit
5. **Undo**: Allow un-completing items before finalization

---

**Last Updated:** 2025-01-16
**Version:** 2.0.0
