<?php

use App\Services\UploadService;

/**
 * Upload Helper
 * Funciones de ayuda para el manejo de uploads de archivos
 */

if (!function_exists('uploadService')) {
    /**
     * Obtiene una instancia del servicio de uploads
     *
     * @param int|null $maxFileSize Tamaño máximo en bytes (opcional)
     * @return UploadService
     */
    function uploadService(?int $maxFileSize = null): UploadService
    {
        return new UploadService($maxFileSize);
    }
}

if (!function_exists('saveFirmaConsultor')) {
    /**
     * Guarda la firma de un consultor
     *
     * @param array $file Array $_FILES del archivo
     * @param int $idConsultor ID del consultor
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    function saveFirmaConsultor(array $file, int $idConsultor): array
    {
        return uploadService()->saveFirmaConsultor($file, $idConsultor);
    }
}

if (!function_exists('saveLogoCliente')) {
    /**
     * Guarda el logo de un cliente
     *
     * @param array $file Array $_FILES del archivo
     * @param int $idCliente ID del cliente
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    function saveLogoCliente(array $file, int $idCliente): array
    {
        return uploadService()->saveLogoCliente($file, $idCliente);
    }
}

if (!function_exists('saveSoporteContrato')) {
    /**
     * Guarda el soporte/documento de un contrato
     *
     * @param array $file Array $_FILES del archivo
     * @param int $idContrato ID del contrato
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    function saveSoporteContrato(array $file, int $idContrato): array
    {
        return uploadService()->saveSoporteContrato($file, $idContrato);
    }
}

if (!function_exists('saveEvidencia')) {
    /**
     * Guarda una evidencia de auditoría
     *
     * @param array $file Array $_FILES del archivo
     * @param string $nit NIT del cliente/proveedor
     * @param int $idAuditoria ID de la auditoría
     * @param int $idAuditoriaItem ID del item de auditoría
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null]
     */
    function saveEvidencia(array $file, string $nit, int $idAuditoria, int $idAuditoriaItem): array
    {
        return uploadService()->saveEvidencia($file, $nit, $idAuditoria, $idAuditoriaItem);
    }
}

if (!function_exists('deleteUploadedFile')) {
    /**
     * Elimina un archivo previamente subido
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return bool True si se eliminó correctamente
     */
    function deleteUploadedFile(string $relativePath): bool
    {
        return uploadService()->deleteFile($relativePath);
    }
}

if (!function_exists('getUploadedFileInfo')) {
    /**
     * Obtiene información de un archivo subido
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return array|null ['size' => int, 'mime' => string, 'exists' => bool, 'path' => string] o null
     */
    function getUploadedFileInfo(string $relativePath): ?array
    {
        return uploadService()->getFileInfo($relativePath);
    }
}

if (!function_exists('isImageFile')) {
    /**
     * Verifica si un archivo es una imagen
     *
     * @param array $file Array $_FILES del archivo
     * @return bool True si es imagen
     */
    function isImageFile(array $file): bool
    {
        return uploadService()->isImage($file);
    }
}

if (!function_exists('formatFileSize')) {
    /**
     * Formatea un tamaño de archivo en bytes a formato legible
     *
     * @param int $bytes Tamaño en bytes
     * @param int $precision Precisión decimal
     * @return string Tamaño formateado (ej: "2.5 MB")
     */
    function formatFileSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('getUploadPath')) {
    /**
     * Obtiene la ruta completa de un archivo desde su ruta relativa
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return string Ruta completa
     */
    function getUploadPath(string $relativePath): string
    {
        return WRITEPATH . $relativePath;
    }
}

if (!function_exists('uploadExists')) {
    /**
     * Verifica si existe un archivo subido
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return bool True si existe
     */
    function uploadExists(string $relativePath): bool
    {
        return file_exists(WRITEPATH . $relativePath);
    }
}

if (!function_exists('getUploadUrl')) {
    /**
     * Genera una URL para acceder a un archivo subido
     * NOTA: Requiere configurar una ruta pública o controlador para servir archivos
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return string URL del archivo
     */
    function getUploadUrl(string $relativePath): string
    {
        // Por seguridad, los archivos deben servirse a través de un controlador
        // que verifique permisos antes de mostrar el archivo
        return site_url('uploads/file/' . base64_encode($relativePath));
    }
}

if (!function_exists('validateUploadFile')) {
    /**
     * Valida un archivo antes de subirlo (sin guardarlo)
     *
     * @param array $file Array $_FILES del archivo
     * @param int $maxSizeMB Tamaño máximo en MB
     * @return array ['valid' => bool, 'error' => string|null]
     */
    function validateUploadFile(array $file, int $maxSizeMB = 20): array
    {
        $result = ['valid' => true, 'error' => null];

        // Verificar que existe el archivo
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $result['valid'] = false;
            $result['error'] = 'No se recibió ningún archivo.';
            return $result;
        }

        // Verificar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $result['valid'] = false;
            $result['error'] = 'Error al subir el archivo.';
            return $result;
        }

        // Verificar tamaño
        $maxBytes = $maxSizeMB * 1048576;
        if ($file['size'] > $maxBytes) {
            $result['valid'] = false;
            $result['error'] = "El archivo excede el tamaño máximo de {$maxSizeMB} MB.";
            return $result;
        }

        return $result;
    }
}
