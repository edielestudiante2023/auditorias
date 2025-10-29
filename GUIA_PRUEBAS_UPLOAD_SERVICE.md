# Guía de Pruebas y Ejemplos - Upload Service

## Archivos Implementados

### Servicio
- `app/Services/UploadService.php` - Servicio centralizado de uploads con:
  - Validación MIME real usando `finfo`
  - Validación de tamaño (20 MB por defecto)
  - Sanitización de nombres de archivo
  - Métodos específicos por tipo de upload
  - Gestión de directorios automática

### Helper
- `app/Helpers/upload_helper.php` - Funciones de ayuda:
  - `uploadService()` - Obtener instancia del servicio
  - `saveFirmaConsultor()` - Guardar firma
  - `saveLogoCliente()` - Guardar logo
  - `saveEvidencia()` - Guardar evidencia
  - `deleteUploadedFile()` - Eliminar archivo
  - `getUploadedFileInfo()` - Información del archivo
  - `isImageFile()` - Validar si es imagen
  - `formatFileSize()` - Formatear tamaño
  - `uploadExists()` - Verificar existencia
  - Y más...

### Seguridad
- `writable/uploads/.htaccess` - Protección contra acceso directo

### Documentación
- `CONFIGURACION_PHP_UPLOADS.md` - Guía de configuración PHP

---

## Estructura de Directorios

```
writable/
└── uploads/
    ├── .htaccess                          (protección)
    ├── firmas_consultor/                  (firmas digitales)
    │   └── consultor_1_1730000000_firma.png
    ├── logos_clientes/                    (logos de empresas)
    │   └── cliente_5_1730000000_logo.png
    └── evidencias/                        (evidencias de auditorías)
        └── {nit}/
            └── {id_auditoria}/
                └── {id_auditoria_item}/
                    └── evidencia_1730000000_archivo.pdf
```

---

## Ejemplos de Uso

### Ejemplo 1: Guardar Firma de Consultor

**En un controlador:**

```php
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ConsultoresController extends BaseController
{
    public function guardarFirma(int $idConsultor)
    {
        helper('upload');

        // Obtener archivo del formulario
        $file = $this->request->getFile('firma');

        // Validar que se subió un archivo
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'No se recibió ningún archivo válido.');
        }

        // Convertir a formato array para UploadService
        $fileArray = [
            'name'     => $file->getName(),
            'type'     => $file->getClientMimeType(),
            'tmp_name' => $file->getTempName(),
            'error'    => $file->getError(),
            'size'     => $file->getSize(),
        ];

        // Guardar usando el helper
        $result = saveFirmaConsultor($fileArray, $idConsultor);

        if ($result['ok']) {
            // Guardar la ruta en la base de datos
            $consultorModel = new \App\Models\ConsultorModel();
            $consultorModel->update($idConsultor, [
                'firma_path' => $result['path']
            ]);

            return redirect()->back()->with('success', 'Firma guardada exitosamente.');
        }

        return redirect()->back()->with('error', $result['error']);
    }
}
```

**Vista del formulario:**

```php
<form method="post" action="<?= site_url('admin/consultores/guardarFirma/' . $idConsultor) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="firma" class="form-label">Firma Digital (PNG, JPG)</label>
        <input type="file"
               class="form-control"
               id="firma"
               name="firma"
               accept="image/png,image/jpeg"
               required>
        <small class="text-muted">Tamaño máximo: 20 MB</small>
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="bi bi-upload"></i> Subir Firma
    </button>
</form>
```

---

### Ejemplo 2: Guardar Logo de Cliente

```php
public function guardarLogo(int $idCliente)
{
    helper('upload');

    $file = $this->request->getFile('logo');

    if (!$file || !$file->isValid()) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'No se recibió ningún archivo válido.'
        ]);
    }

    $fileArray = [
        'name'     => $file->getName(),
        'type'     => $file->getClientMimeType(),
        'tmp_name' => $file->getTempName(),
        'error'    => $file->getError(),
        'size'     => $file->getSize(),
    ];

    // Validar que es imagen antes de guardar
    if (!isImageFile($fileArray)) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'El archivo debe ser una imagen (PNG o JPG).'
        ]);
    }

    $result = saveLogoCliente($fileArray, $idCliente);

    if ($result['ok']) {
        // Actualizar base de datos
        $clienteModel = new \App\Models\ClienteModel();
        $clienteModel->update($idCliente, [
            'logo_cliente_path' => $result['path']
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Logo guardado exitosamente.',
            'path'    => $result['path'],
            'size'    => formatFileSize($result['size'])
        ]);
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => $result['error']
    ]);
}
```

---

### Ejemplo 3: Guardar Evidencia de Auditoría

```php
public function subirEvidencia(int $idAuditoriaItem)
{
    helper('upload');

    // Obtener datos del ítem de auditoría
    $itemModel = new \App\Models\AuditoriaItemModel();
    $item = $itemModel->find($idAuditoriaItem);

    if (!$item) {
        return redirect()->back()->with('error', 'Ítem de auditoría no encontrado.');
    }

    // Obtener auditoría para sacar el NIT
    $auditoriaModel = new \App\Models\AuditoriaModel();
    $auditoria = $auditoriaModel->find($item['id_auditoria']);

    // Obtener NIT del proveedor
    $proveedorModel = new \App\Models\ProveedorModel();
    $proveedor = $proveedorModel->find($auditoria['id_proveedor']);

    $file = $this->request->getFile('evidencia');

    if (!$file || !$file->isValid()) {
        return redirect()->back()->with('error', 'No se recibió ningún archivo válido.');
    }

    $fileArray = [
        'name'     => $file->getName(),
        'type'     => $file->getClientMimeType(),
        'tmp_name' => $file->getTempName(),
        'error'    => $file->getError(),
        'size'     => $file->getSize(),
    ];

    // Guardar evidencia con estructura de carpetas
    $result = saveEvidencia(
        $fileArray,
        $proveedor['nit'],
        $item['id_auditoria'],
        $idAuditoriaItem
    );

    if ($result['ok']) {
        // Registrar en tabla evidencias
        $evidenciaModel = new \App\Models\EvidenciaModel();
        $evidenciaModel->insert([
            'id_auditoria_item'      => $idAuditoriaItem,
            'nombre_archivo_original'=> $file->getName(),
            'ruta_archivo'           => $result['path'],
            'tipo_mime'              => $result['mime'],
            'tamanio_bytes'          => $result['size'],
            'hash_archivo'           => hash_file('sha256', WRITEPATH . $result['path']),
            'created_at'             => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success', 'Evidencia subida exitosamente.');
    }

    return redirect()->back()->with('error', $result['error']);
}
```

---

### Ejemplo 4: Usar el Servicio Directamente (sin helper)

```php
use App\Services\UploadService;

public function uploadCustom()
{
    $uploadService = new UploadService();

    // Configurar tamaño máximo personalizado (50 MB)
    $uploadService->setMaxFileSize(50);

    $file = $this->request->getFile('documento');

    $fileArray = [
        'name'     => $file->getName(),
        'type'     => $file->getClientMimeType(),
        'tmp_name' => $file->getTempName(),
        'error'    => $file->getError(),
        'size'     => $file->getSize(),
    ];

    // Usar método genérico
    $result = $uploadService->saveFirmaConsultor($fileArray, 123);

    if ($result['ok']) {
        log_message('info', 'Archivo guardado: ' . $result['path']);
        log_message('info', 'Tamaño: ' . formatFileSize($result['size']));
        log_message('info', 'MIME: ' . $result['mime']);
    }
}
```

---

### Ejemplo 5: Eliminar Archivo Antiguo

```php
public function actualizarFirma(int $idConsultor)
{
    helper('upload');

    $consultorModel = new \App\Models\ConsultorModel();
    $consultor = $consultorModel->find($idConsultor);

    // Eliminar firma antigua si existe
    if (!empty($consultor['firma_path'])) {
        deleteUploadedFile($consultor['firma_path']);
    }

    // Subir nueva firma
    $file = $this->request->getFile('firma');
    // ... código de subida ...
}
```

---

### Ejemplo 6: Mostrar Información de Archivo

```php
public function verEvidencia(string $relativePath)
{
    helper('upload');

    if (!uploadExists($relativePath)) {
        return redirect()->back()->with('error', 'Archivo no encontrado.');
    }

    $info = getUploadedFileInfo($relativePath);

    return view('evidencias/detalle', [
        'archivo' => [
            'ruta'   => $relativePath,
            'tamano' => formatFileSize($info['size']),
            'mime'   => $info['mime'],
            'existe' => $info['exists'],
        ]
    ]);
}
```

---

### Ejemplo 7: Validar Antes de Subir

```php
public function validarArchivo()
{
    helper('upload');

    $file = $this->request->getFile('documento');

    $fileArray = [
        'name'     => $file->getName(),
        'type'     => $file->getClientMimeType(),
        'tmp_name' => $file->getTempName(),
        'error'    => $file->getError(),
        'size'     => $file->getSize(),
    ];

    // Validar sin guardar
    $validation = validateUploadFile($fileArray, 20);

    if (!$validation['valid']) {
        return $this->response->setJSON([
            'error' => $validation['error']
        ]);
    }

    // Validar si es imagen
    if (isImageFile($fileArray)) {
        // Procesar como imagen
    }

    // Continuar con el guardado...
}
```

---

## Pruebas Manuales

### Caso de Prueba 1: Subir archivo válido

**Crear archivo de prueba:** `test_upload.php` en `public/`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

helper('upload');

// Simular $_FILES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $file = $_FILES['archivo'];

    // Probar guardar firma de consultor
    $result = saveFirmaConsultor($file, 1);

    if ($result['ok']) {
        echo "<div style='color: green;'>";
        echo "<h3>✓ Archivo subido exitosamente</h3>";
        echo "<ul>";
        echo "<li><strong>Ruta:</strong> " . $result['path'] . "</li>";
        echo "<li><strong>MIME:</strong> " . $result['mime'] . "</li>";
        echo "<li><strong>Tamaño:</strong> " . formatFileSize($result['size']) . "</li>";
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<div style='color: red;'>";
        echo "<h3>✗ Error al subir archivo</h3>";
        echo "<p>" . $result['error'] . "</p>";
        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prueba de Upload</title>
</head>
<body>
    <h2>Prueba de Upload Service</h2>
    <form method="post" enctype="multipart/form-data">
        <p>
            <label>Seleccionar archivo:</label><br>
            <input type="file" name="archivo" required>
        </p>
        <p>
            <small>Tipos permitidos: PDF, Word, Excel, PNG, JPG</small><br>
            <small>Tamaño máximo: 20 MB</small>
        </p>
        <button type="submit">Subir Archivo</button>
    </form>
</body>
</html>
```

**Acceder a:** http://localhost/auditorias/public/test_upload.php

**Probar:**
1. Subir imagen PNG válida → Debe funcionar
2. Subir PDF válido → Debe funcionar
3. Subir archivo de 25 MB → Debe rechazar (excede tamaño)
4. Subir archivo .exe → Debe rechazar (tipo no permitido)

---

### Caso de Prueba 2: Validación de MIME

**Crear archivo:** `test_mime.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
helper('upload');

if (isset($_FILES['archivo'])) {
    $uploadService = uploadService();

    $isImage = $uploadService->isImage($_FILES['archivo']);

    echo "<h3>Resultado de validación:</h3>";
    echo "<p>¿Es imagen?: " . ($isImage ? 'SÍ' : 'NO') . "</p>";

    // Detectar MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['archivo']['tmp_name']);
    finfo_close($finfo);

    echo "<p>MIME detectado: <strong>{$mime}</strong></p>";
}
?>

<form method="post" enctype="multipart/form-data">
    <input type="file" name="archivo" required>
    <button type="submit">Detectar MIME</button>
</form>
```

**Probar:**
1. Subir imagen real → Debe detectar `image/png` o `image/jpeg`
2. Renombrar `.exe` a `.png` y subir → Debe detectar MIME real, rechazando el archivo

---

## Checklist de Verificación

**Configuración:**
- [ ] Directorios creados en `writable/uploads/`
- [ ] `.htaccess` en `writable/uploads/` para seguridad
- [ ] `upload_max_filesize = 20M` en php.ini
- [ ] `post_max_size = 25M` en php.ini
- [ ] Extensión `fileinfo` habilitada
- [ ] Apache reiniciado

**Funcionalidad:**
- [ ] `saveFirmaConsultor()` funciona correctamente
- [ ] `saveLogoCliente()` funciona correctamente
- [ ] `saveEvidencia()` funciona correctamente
- [ ] Validación MIME con `finfo` funciona
- [ ] Rechaza archivos mayores a 20 MB
- [ ] Rechaza tipos de archivo no permitidos
- [ ] Sanitiza nombres de archivo correctamente
- [ ] Crea directorios automáticamente
- [ ] Genera nombres únicos con timestamp

**Seguridad:**
- [ ] `.htaccess` bloquea acceso directo
- [ ] Validación MIME real (no solo extensión)
- [ ] Nombres de archivo sanitizados
- [ ] Archivos almacenados fuera de `public/`

---

## Próximos Pasos

Con el servicio de uploads implementado, ahora puedes:

1. Implementar CRUD de **Clientes** con upload de logo
2. Implementar CRUD de **Consultores** con upload de firma
3. Implementar upload de **Evidencias** en módulo de auditorías
4. Crear controlador para **servir archivos** con validación de permisos
5. Implementar **galería de evidencias** en auditorías

---

**Fecha:** 2025-10-14
**Framework:** CodeIgniter 4
**Módulo:** Upload Service
**Autor:** Sistema de Auditorías - Cycloid Talent SAS
