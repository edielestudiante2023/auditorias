# Implementación Sistema PDF Mejorado para Auditorías

## 📋 Resumen de Cambios

Se ha implementado un sistema completo de generación de PDFs profesionales para auditorías con las siguientes características:

- ✅ Encabezado con logos (proveedor/cliente)
- ✅ Información completa: proveedor, NIT, contrato, código formato, fecha
- ✅ Tabla de ítems con estados y comentarios (globales y por cliente)
- ✅ Sección de evidencias con miniaturas para imágenes e íconos para archivos
- ✅ Formato A4 con márgenes estándar
- ✅ Numeración de páginas automática
- ✅ Estilos optimizados para impresión

---

## 📁 Archivos Modificados/Creados

### 1. **Helper de Formateo**
**Archivo:** `app/Helpers/format_helper.php`

**Funciones nuevas agregadas:**

```php
// Formatea código de formato con versión
formatoCodigoFormato(?string $codigo, ?string $version = null): string
// Ejemplo: formatoCodigoFormato('FRM-AUD-001', '2.0') → "FRM-AUD-001 v2.0"

// Formatea NIT con separadores de miles
formatoNIT(?string $nit): string
// Ejemplo: formatoNIT('9001234567') → "900.123.456-7"

// Verifica si un archivo es imagen
esImagen(?string $nombreArchivo): bool
// Ejemplo: esImagen('foto.jpg') → true

// Retorna ícono de Bootstrap según extensión
iconoArchivo(?string $nombreArchivo): string
// Ejemplo: iconoArchivo('documento.pdf') → "bi-file-earmark-pdf"
```

---

### 2. **Vista PDF Completa**
**Archivo:** `app/Views/pdf/auditoria_completa.php`

**Características:**

- **Encabezado con logos:** Muestra logo del proveedor (izq) y cliente (der) si existen
- **Información general:** Tabla con todos los datos de la auditoría
- **Porcentaje de cumplimiento:** Box colorizado según nivel (verde/amarillo/rojo)
- **Tabla de ítems globales:** Código, título, estado, comentarios proveedor/consultor
- **Tabla de ítems por cliente:** Similar a globales pero específicos del cliente
- **Sección de evidencias:**
  - Miniaturas (70x70px) para imágenes
  - Íconos para otros archivos
  - Información del ítem asociado
- **Firma del consultor:** Con imagen de firma y licencia SST
- **Footer:** Fecha de generación, paginación automática

**Estilos CSS incluidos:**
- Diseño para tamaño A4 (`@page { size: A4; margin: 2cm 1.5cm; }`)
- Prevención de saltos de página en elementos críticos (`page-break-inside: avoid`)
- Colores corporativos azul (#0056b3)
- Badges de estado con colores semafóricos

---

### 3. **Servicio PDF Extendido**
**Archivo:** `app/Services/PdfService.php`

**Método nuevo:**

```php
public function generarPdfCompleto(int $idAuditoria, ?int $idCliente = null): string
```

**Parámetros:**
- `$idAuditoria`: ID de la auditoría
- `$idCliente`: (Opcional) ID del cliente. Si es `null`, genera reporte global

**Retorna:** Ruta relativa del PDF generado

**Métodos privados agregados:**
- `obtenerDatosAuditoriaClienteCompleto()`: Obtiene todos los datos incluyendo logos y evidencias para un cliente
- `obtenerDatosAuditoriaGlobalCompleto()`: Obtiene datos para reporte global con todos los clientes

---

## 🚀 Ejemplos de Uso

### Ejemplo 1: Generar PDF para un Cliente Específico

```php
// En un controlador
use App\Services\PdfService;

$pdfService = new PdfService();
$idAuditoria = 123;
$idCliente = 45;

try {
    $rutaPdf = $pdfService->generarPdfCompleto($idAuditoria, $idCliente);

    // La ruta retornada es relativa: "reports/123/clientes/45/auditoria-completa-123-cliente-45.pdf"
    $rutaCompleta = WRITEPATH . $rutaPdf;

    // Ejemplo: Descargar el PDF
    return $this->response->download($rutaCompleta, null);

} catch (\Exception $e) {
    log_message('error', 'Error generando PDF: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Error al generar PDF');
}
```

### Ejemplo 2: Generar PDF Global (Todos los Clientes)

```php
use App\Services\PdfService;

$pdfService = new PdfService();
$idAuditoria = 123;

try {
    // Pasar null como segundo parámetro para reporte global
    $rutaPdf = $pdfService->generarPdfCompleto($idAuditoria, null);

    // Ruta retornada: "reports/123/auditoria-completa-global-123.pdf"
    $rutaCompleta = WRITEPATH . $rutaPdf;

    return $this->response->download($rutaCompleta, null);

} catch (\Exception $e) {
    log_message('error', 'Error generando PDF global: ' . $e->getMessage());
    return redirect()->back()->with('error', 'Error al generar PDF');
}
```

### Ejemplo 3: Generar PDFs al Cerrar Auditoría

```php
// En AuditoriasConsultorController::cerrar()

public function cerrar(int $idAuditoria)
{
    // ... validaciones ...

    $clientes = $this->auditoriaClienteModel->getClientesByAuditoria($idAuditoria);
    $pdfClientes = [];

    foreach ($clientes as $cliente) {
        // Generar PDF completo para cada cliente
        $rutaPdf = $this->pdfService->generarPdfCompleto($idAuditoria, $cliente['id_cliente']);

        $pdfClientes[] = [
            'razon_social' => $cliente['razon_social'],
            'ruta_pdf' => $rutaPdf
        ];
    }

    // Opcionalmente generar PDF global
    $rutaPdfGlobal = $this->pdfService->generarPdfCompleto($idAuditoria, null);

    // ... continuar con el cierre ...
}
```

### Ejemplo 4: Usar Funciones Helper en Vistas

```php
// En cualquier vista PHP
<?php helper('format'); ?>

<!-- Formatear NIT -->
<p>NIT: <?= formatoNIT($proveedor['nit']) ?></p>
<!-- Salida: NIT: 900.123.456-7 -->

<!-- Formatear código de formato -->
<p>Código: <?= formatoCodigoFormato($auditoria['codigo_formato'], $auditoria['version_formato']) ?></p>
<!-- Salida: Código: FRM-AUD-001 v2.0 -->

<!-- Verificar si es imagen -->
<?php if (esImagen($evidencia['nombre_archivo'])): ?>
    <img src="..." alt="Evidencia">
<?php else: ?>
    <i class="<?= iconoArchivo($evidencia['nombre_archivo']) ?>"></i>
<?php endif; ?>
```

---

## 📊 Estructura de Datos del PDF

### Datos de Entrada (array `$data`)

```php
[
    'auditoria' => [
        'id_auditoria' => 123,
        'proveedor_nombre' => 'Empresa ABC',
        'proveedor_nit' => '9001234567',
        'codigo_formato' => 'FRM-AUD-001',
        'version_formato' => '2.0',
        'fecha_programada' => '2025-10-15 10:00:00',
        'estado' => 'cerrada',
        'consultor_nombre' => 'Juan Pérez',
        'licencia_sst' => 'LIC-12345',
        'firma_path' => 'firmas/firma_juan.png',
        // ... más campos
    ],
    'cliente' => [
        'razon_social' => 'Cliente XYZ',
        'nit' => '8009876543',
        // ... más campos
    ],
    'porcentaje_cumplimiento' => 85.50,
    'items_globales' => [
        [
            'codigo_item' => 'ITEM-001',
            'titulo' => 'Política de Seguridad',
            'descripcion' => 'Verificar política documentada',
            'calificacion' => 'cumple',
            'comentario_proveedor' => 'Política actualizada 2025',
            'comentario_consultor' => 'Cumple satisfactoriamente',
            'evidencias' => [
                [
                    'nombre_original' => 'politica.pdf',
                    'ruta_archivo' => 'evidencias/politica.pdf',
                    'created_at' => '2025-10-15 14:30:00'
                ]
            ]
        ],
        // ... más ítems
    ],
    'items_por_cliente' => [
        // Estructura similar a items_globales
    ],
    'logo_proveedor' => '/path/to/logo_proveedor.png',
    'logo_cliente' => '/path/to/logo_cliente.png',
    'fecha_generacion' => '2025-10-16 08:00:00'
]
```

---

## 🎨 Personalización de Estilos

Para modificar los estilos del PDF, edita la sección `<style>` en `app/Views/pdf/auditoria_completa.php`:

```css
/* Cambiar color corporativo */
.header-title {
    color: #0056b3; /* Cambiar a tu color */
}

/* Ajustar tamaño de logos */
.logo-img {
    max-width: 120px; /* Ajustar según necesidad */
    max-height: 60px;
}

/* Cambiar colores de porcentaje */
.porcentaje-alto {
    background-color: #d4edda;
    color: #155724;
    border: 2px solid #28a745;
}
```

---

## 🔧 Requisitos de Base de Datos

Asegúrate de que las siguientes tablas/campos existen:

**Tabla `proveedores`:**
- `logo_path` (VARCHAR) - Ruta del logo del proveedor

**Tabla `clientes`:**
- `logo_path` (VARCHAR) - Ruta del logo del cliente

**Tabla `consultores`:**
- `firma_path` (VARCHAR) - Ruta de la firma del consultor
- `licencia_sst` (VARCHAR) - Número de licencia SST

**Tabla `auditorias`:**
- `codigo_formato` (VARCHAR)
- `version_formato` (VARCHAR)

Si faltan campos, agregar con migraciones:

```php
// Ejemplo de migración
$this->forge->addColumn('proveedores', [
    'logo_path' => [
        'type' => 'VARCHAR',
        'constraint' => '255',
        'null' => true,
        'after' => 'observaciones'
    ]
]);
```

---

## 📝 Notas Importantes

1. **Logos:** Los logos deben estar en `writable/uploads/` y los paths en BD deben ser relativos
2. **Evidencias:** Las rutas de evidencias también son relativas a `writable/uploads/`
3. **Permisos:** Asegúrate que `writable/reports/` tenga permisos de escritura (755)
4. **Dompdf:** Ya está instalado como dependencia (`dompdf/dompdf: ^3.1`)
5. **Helper:** El helper `format` debe cargarse con `helper('format')` antes de usar las funciones

---

## 🐛 Troubleshooting

### Problema: Los logos no aparecen
**Solución:** Verifica que:
- Los archivos existen en `writable/uploads/`
- Los paths en BD son relativos (sin WRITEPATH)
- Los archivos tienen permisos de lectura

### Problema: Las evidencias no se muestran
**Solución:**
- Verifica que la columna `nombre_original` existe en tablas `evidencias` y `evidencias_cliente`
- Confirma que las rutas son relativas

### Problema: Error de memoria al generar PDF
**Solución:** Aumenta `memory_limit` en php.ini o en tiempo de ejecución:
```php
ini_set('memory_limit', '256M');
```

---

## 📞 Soporte

Para más información sobre Dompdf:
- Documentación: https://github.com/dompdf/dompdf
- Opciones avanzadas: https://github.com/dompdf/dompdf/wiki

---

**Implementado:** 2025-10-16
**Versión:** 1.0
**Autor:** Sistema de Gestión Cycloid Talent SAS
