<?php

namespace App\Services;

/**
 * Servicio centralizado para manejo de uploads de archivos
 *
 * Gestiona la subida, validación y almacenamiento de archivos en diferentes contextos:
 * - Firmas de consultores
 * - Logos de clientes
 * - Evidencias de auditorías
 */
class UploadService
{
    /**
     * Tamaño máximo permitido por defecto (20 MB en bytes)
     */
    protected int $maxFileSize = 20971520; // 20 * 1024 * 1024

    /**
     * Tipos MIME permitidos
     */
    protected array $allowedMimeTypes = [
        // PDFs
        'application/pdf',
        // Word
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        // Excel
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        // Imágenes
        'image/png',
        'image/jpeg',
        'image/jpg',
    ];

    /**
     * Extensiones permitidas (respaldo)
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'
    ];

    /**
     * Constructor
     *
     * @param int|null $maxFileSize Tamaño máximo en bytes (opcional)
     */
    public function __construct(?int $maxFileSize = null)
    {
        if ($maxFileSize !== null) {
            $this->maxFileSize = $maxFileSize;
        }
    }

    /**
     * Guarda la firma de un consultor
     *
     * @param array $file Array $_FILES del archivo subido
     * @param int $idConsultor ID del consultor
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    public function saveFirmaConsultor(array $file, int $idConsultor): array
    {
        $uploadPath = 'uploads/firmas_consultor/';
        $prefix = "consultor_{$idConsultor}_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Guarda el logo de un cliente
     *
     * @param array $file Array $_FILES del archivo subido
     * @param int $idCliente ID del cliente
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    public function saveLogoCliente(array $file, int $idCliente): array
    {
        $uploadPath = 'uploads/logos_clientes/';
        $prefix = "cliente_{$idCliente}_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Guarda el soporte/documento de un contrato
     *
     * @param array $file Array $_FILES del archivo subido
     * @param int $idContrato ID del contrato
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    public function saveSoporteContrato(array $file, int $idContrato): array
    {
        $uploadPath = "uploads/contratos/{$idContrato}/";
        $prefix = "contrato_{$idContrato}_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Guarda una evidencia de auditoría (global)
     *
     * @param array $file Array $_FILES del archivo subido
     * @param string $nit NIT del proveedor
     * @param int $idAuditoria ID de la auditoría
     * @param int $idAuditoriaItem ID del item de auditoría
     * @param int|null $idCliente ID del cliente (opcional, para compatibilidad)
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    public function saveEvidencia(array $file, string $nit, int $idAuditoria, int $idAuditoriaItem, ?int $idCliente = null): array
    {
        // Si viene idCliente, delegar al método específico
        if ($idCliente !== null) {
            return $this->saveEvidenciaCliente($file, $nit, $idAuditoria, $idAuditoriaItem, $idCliente);
        }

        // Sanitizar NIT para usar en path
        $nitSanitized = $this->sanitizeForPath($nit);

        $uploadPath = "uploads/evidencias/{$nitSanitized}/{$idAuditoria}/{$idAuditoriaItem}/";
        $prefix = "evidencia_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Guarda una evidencia específica por cliente
     *
     * @param array $file Array $_FILES del archivo subido
     * @param string $nit NIT del proveedor
     * @param int $idAuditoria ID de la auditoría
     * @param int $idAuditoriaItem ID del item de auditoría
     * @param int $idCliente ID del cliente
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    public function saveEvidenciaCliente(array $file, string $nit, int $idAuditoria, int $idAuditoriaItem, int $idCliente): array
    {
        // Sanitizar NIT para usar en path
        $nitSanitized = $this->sanitizeForPath($nit);

        // Ruta: uploads/evidencias/{nit}/{id_auditoria}/cliente_{id_cliente}/{id_auditoria_item}/
        $uploadPath = "uploads/evidencias/{$nitSanitized}/{$idAuditoria}/cliente_{$idCliente}/{$idAuditoriaItem}/";
        $prefix = "evidencia_cliente_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Método genérico para guardar archivos
     *
     * @param array $file Array $_FILES del archivo
     * @param string $uploadPath Ruta relativa desde WRITEPATH
     * @param string $prefix Prefijo para el nombre del archivo
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    protected function saveFile(array $file, string $uploadPath, string $prefix = ''): array
    {
        // Estructura de respuesta
        $response = [
            'ok'    => false,
            'path'  => null,
            'error' => null,
            'mime'  => null,
            'size'  => null,
        ];

        // Validar que el archivo existe y no tiene errores
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $response['error'] = 'No se recibió ningún archivo.';
            return $response;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['error'] = $this->getUploadErrorMessage($file['error']);
            return $response;
        }

        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            $response['error'] = "El archivo excede el tamaño máximo permitido de {$maxSizeMB} MB.";
            return $response;
        }

        // Validar MIME type usando finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($detectedMime, $this->allowedMimeTypes)) {
            $response['error'] = 'Tipo de archivo no permitido. Tipos aceptados: PDF, Word, Excel, PNG, JPG.';
            return $response;
        }

        // Validar extensión
        $originalName = $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $this->allowedExtensions)) {
            $response['error'] = 'Extensión de archivo no permitida.';
            return $response;
        }

        // Crear directorio si no existe
        $fullUploadPath = WRITEPATH . $uploadPath;
        if (!is_dir($fullUploadPath)) {
            if (!mkdir($fullUploadPath, 0755, true)) {
                $response['error'] = 'No se pudo crear el directorio de destino.';
                return $response;
            }
        }

        // Generar nombre único y sanitizado
        $sanitizedBasename = $this->sanitizeFilename(pathinfo($originalName, PATHINFO_FILENAME));
        $timestamp = time();
        $newFilename = $prefix . $timestamp . '_' . $sanitizedBasename . '.' . $extension;

        // Ruta completa del archivo
        $fullFilePath = $fullUploadPath . $newFilename;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $fullFilePath)) {
            $response['error'] = 'Error al guardar el archivo en el servidor.';
            return $response;
        }

        // Éxito
        $response['ok']   = true;
        $response['path'] = $uploadPath . $newFilename; // Ruta relativa desde WRITEPATH
        $response['mime'] = $detectedMime;
        $response['size'] = $file['size'];

        return $response;
    }

    /**
     * Elimina un archivo del sistema
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return bool True si se eliminó correctamente
     */
    public function deleteFile(string $relativePath): bool
    {
        $fullPath = WRITEPATH . $relativePath;

        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }

    /**
     * Sanitiza un nombre de archivo
     *
     * @param string $filename Nombre del archivo
     * @return string Nombre sanitizado
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remover caracteres especiales, dejar solo alfanuméricos, guiones y guiones bajos
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

        // Limitar longitud
        $filename = substr($filename, 0, 100);

        // Remover guiones/underscores múltiples
        $filename = preg_replace('/[_-]+/', '_', $filename);

        return trim($filename, '_-');
    }

    /**
     * Sanitiza texto para usar en paths
     *
     * @param string $text Texto a sanitizar
     * @return string Texto sanitizado
     */
    protected function sanitizeForPath(string $text): string
    {
        // Remover caracteres no alfanuméricos excepto guiones
        $text = preg_replace('/[^a-zA-Z0-9-]/', '_', $text);

        return strtolower(trim($text, '_-'));
    }

    /**
     * Obtiene el mensaje de error según código de error de upload
     *
     * @param int $errorCode Código de error
     * @return string Mensaje de error
     */
    protected function getUploadErrorMessage(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo permitido por el formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la carga del archivo.',
        ];

        return $errors[$errorCode] ?? 'Error desconocido al subir el archivo.';
    }

    /**
     * Valida si un archivo es una imagen
     *
     * @param array $file Array $_FILES del archivo
     * @return bool True si es imagen
     */
    public function isImage(array $file): bool
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        return in_array($mime, ['image/png', 'image/jpeg', 'image/jpg']);
    }

    /**
     * Obtiene información de un archivo subido
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return array|null ['size' => int, 'mime' => string, 'exists' => bool] o null
     */
    public function getFileInfo(string $relativePath): ?array
    {
        $fullPath = WRITEPATH . $relativePath;

        if (!file_exists($fullPath)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        return [
            'size'   => filesize($fullPath),
            'mime'   => $mime,
            'exists' => true,
            'path'   => $fullPath,
        ];
    }

    /**
     * Configura un tamaño máximo personalizado
     *
     * @param int $sizeInMB Tamaño en megabytes
     * @return self
     */
    public function setMaxFileSize(int $sizeInMB): self
    {
        $this->maxFileSize = $sizeInMB * 1048576;
        return $this;
    }

    /**
     * Obtiene el tamaño máximo configurado en MB
     *
     * @return float Tamaño en MB
     */
    public function getMaxFileSizeMB(): float
    {
        return round($this->maxFileSize / 1048576, 2);
    }
}
