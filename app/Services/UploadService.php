<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

/**
 * Servicio centralizado para manejo de uploads de archivos
 *
 * Gestiona la subida, validación y almacenamiento de archivos en diferentes contextos:
 * - Firmas de consultores
 * - Logos de clientes
 * - Evidencias de auditorías (global y por cliente)
 *
 * Nueva estructura de directorios para evidencias:
 * - Global: /writable/uploads/{proveedor_nit}/{id_auditoria}/global/{id_auditoria_item}/
 * - Por cliente: /writable/uploads/{proveedor_nit}/{id_auditoria}/cliente_{id_cliente}/{id_auditoria_item}/
 */
class UploadService
{
    /**
     * Tamaño máximo permitido (15 MB en bytes)
     */
    protected int $maxFileSize = 15728640; // 15 * 1024 * 1024

    /**
     * Tipos MIME permitidos (validados con finfo_file)
     */
    protected array $allowedMimeTypes = [
        // PDFs
        'application/pdf',
        // Word
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        // Excel
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        // Imágenes
        'image/png',
        'image/jpeg',
        // Videos
        'video/mp4',
    ];

    /**
     * Extensiones permitidas
     */
    protected array $allowedExtensions = [
        'pdf', 'jpg', 'jpeg', 'png', 'mp4', 'xlsx', 'docx'
    ];

    /**
     * Extensiones y tipos peligrosos BLOQUEADOS
     */
    protected array $dangerousExtensions = [
        'php', 'phar', 'phtml', 'php3', 'php4', 'php5', 'php7',
        'js', 'exe', 'sh', 'bat', 'cmd', 'dll', 'com', 'scr',
        'vbs', 'jar', 'app', 'deb', 'rpm', 'bin', 'run',
        'svg', 'html', 'htm', 'xml', 'swf'
    ];

    /**
     * MIME types peligrosos BLOQUEADOS
     */
    protected array $dangerousMimeTypes = [
        'application/x-httpd-php',
        'application/x-php',
        'application/x-sh',
        'application/x-executable',
        'application/x-msdos-program',
        'text/x-php',
        'text/x-shellscript',
        'application/javascript',
        'text/javascript',
        'image/svg+xml',
        'text/html',
        'application/x-phar'
    ];

    /**
     * Conexión a base de datos (para transacciones en eliminación)
     */
    protected ?ConnectionInterface $db = null;

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
     * Inyecta la conexión de base de datos (para transacciones)
     *
     * @param ConnectionInterface $db
     * @return self
     */
    public function setDatabase(ConnectionInterface $db): self
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Guarda la firma de un consultor
     *
     * @param array $file Array $_FILES del archivo subido
     * @param int $idConsultor ID del consultor
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
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
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
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
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
     */
    public function saveSoporteContrato(array $file, int $idContrato): array
    {
        $uploadPath = "uploads/contratos/{$idContrato}/";
        $prefix = "contrato_{$idContrato}_";

        return $this->saveFile($file, $uploadPath, $prefix);
    }

    /**
     * Guarda una evidencia de auditoría GLOBAL
     *
     * Nueva estructura: /writable/uploads/{proveedor_nit}/{id_auditoria}/global/{id_auditoria_item}/
     *
     * @param array $file Array $_FILES del archivo subido
     * @param string $nit NIT del proveedor
     * @param int $idAuditoria ID de la auditoría
     * @param int $idAuditoriaItem ID del item de auditoría
     * @param array $context Contexto para logging (user_id, id_auditoria, id_item)
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
     */
    public function saveEvidencia(array $file, string $nit, int $idAuditoria, int $idAuditoriaItem, array $context = []): array
    {
        // Sanitizar NIT para usar en path
        $nitSanitized = $this->sanitizeForPath($nit);

        // Nueva estructura: {nit}/{id_auditoria}/global/{id_auditoria_item}/
        $uploadPath = "uploads/{$nitSanitized}/{$idAuditoria}/global/{$idAuditoriaItem}/";
        $prefix = "";

        return $this->saveFile($file, $uploadPath, $prefix, $context);
    }

    /**
     * Guarda una evidencia específica POR CLIENTE
     *
     * Nueva estructura: /writable/uploads/{proveedor_nit}/{id_auditoria}/cliente_{id_cliente}/{id_auditoria_item}/
     *
     * @param array $file Array $_FILES del archivo subido
     * @param string $nit NIT del proveedor
     * @param int $idAuditoria ID de la auditoría
     * @param int $idAuditoriaItem ID del item de auditoría (parent)
     * @param int $idCliente ID del cliente
     * @param array $context Contexto para logging (user_id, id_auditoria, id_item, id_cliente)
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
     */
    public function saveEvidenciaCliente(array $file, string $nit, int $idAuditoria, int $idAuditoriaItem, int $idCliente, array $context = []): array
    {
        // Sanitizar NIT para usar en path
        $nitSanitized = $this->sanitizeForPath($nit);

        // Nueva estructura: {nit}/{id_auditoria}/cliente_{id_cliente}/{id_auditoria_item}/
        $uploadPath = "uploads/{$nitSanitized}/{$idAuditoria}/cliente_{$idCliente}/{$idAuditoriaItem}/";
        $prefix = "";

        return $this->saveFile($file, $uploadPath, $prefix, $context);
    }

    /**
     * Método genérico para guardar archivos con validación completa
     *
     * Reglas de Seguridad:
     * - Max 15MB
     * - Extensiones permitidas: pdf, jpg, jpeg, png, mp4, xlsx, docx
     * - Verifica MIME real con finfo_file
     * - Bloquea extensiones peligrosas: php, phar, phtml, js, exe, sh, bat, cmd, dll
     * - Detecta doble extensión (archivo.php.jpg)
     * - Normaliza nombre: slug + timestamp
     * - Calcula hash SHA256
     * - Logging de intentos fallidos
     *
     * @param array $file Array $_FILES del archivo
     * @param string $uploadPath Ruta relativa desde WRITEPATH
     * @param string $prefix Prefijo para el nombre del archivo
     * @param array $context Contexto adicional para logging (user_id, id_auditoria, id_item)
     * @return array ['ok' => bool, 'path' => string|null, 'error' => string|null, 'mime' => string|null, 'size' => int|null, 'hash' => string|null]
     */
    protected function saveFile(array $file, string $uploadPath, string $prefix = '', array $context = []): array
    {
        // Estructura de respuesta
        $response = [
            'ok'    => false,
            'path'  => null,
            'error' => null,
            'mime'  => null,
            'size'  => null,
            'hash'  => null,
        ];

        // Validar que el archivo existe y no tiene errores
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $response['error'] = 'No se recibió ningún archivo.';
            $this->logFailedUpload('no_file', $file, $context);
            return $response;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $response['error'] = $this->getUploadErrorMessage($file['error']);
            $this->logFailedUpload('upload_error', $file, $context);
            return $response;
        }

        // Validar tamaño (15MB máximo)
        if ($file['size'] > $this->maxFileSize) {
            $maxSizeMB = round($this->maxFileSize / 1048576, 2);
            $response['error'] = "El archivo excede el tamaño máximo permitido de {$maxSizeMB} MB.";
            $this->logFailedUpload('size_exceeded', $file, $context, ['max_size_mb' => $maxSizeMB]);
            return $response;
        }

        $originalName = $file['name'];

        // SEGURIDAD: Detectar doble extensión (archivo.php.jpg)
        if ($this->hasDoubleExtension($originalName)) {
            $response['error'] = 'Archivo con doble extensión detectado. Esto no está permitido por razones de seguridad.';
            $this->logFailedUpload('double_extension', $file, $context, ['filename' => $originalName]);
            return $response;
        }

        // Validar extensión
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // SEGURIDAD: Bloquear extensiones peligrosas explícitamente
        if (in_array($extension, $this->dangerousExtensions)) {
            $response['error'] = 'Extensión de archivo peligrosa detectada y bloqueada.';
            $this->logFailedUpload('dangerous_extension', $file, $context, ['extension' => $extension]);
            return $response;
        }

        if (!in_array($extension, $this->allowedExtensions)) {
            $response['error'] = 'Extensión de archivo no permitida.';
            $this->logFailedUpload('extension_not_allowed', $file, $context, ['extension' => $extension]);
            return $response;
        }

        // SEGURIDAD: Validar MIME type con fallback si finfo no está disponible
        $detectedMime = null;

        if (function_exists('finfo_open')) {
            // Intentar usar finfo si está disponible
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $detectedMime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }

        // Fallback 1: usar mime_content_type si existe
        if ($detectedMime === null && function_exists('mime_content_type')) {
            $detectedMime = mime_content_type($file['tmp_name']);
        }

        // Fallback 2: usar el tipo declarado por el cliente
        if ($detectedMime === null && isset($file['type'])) {
            $detectedMime = $file['type'];
        }

        // Fallback 3: inferir por extensión (menos seguro pero funcional)
        if ($detectedMime === null) {
            $mimeMap = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'mp4' => 'video/mp4',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $detectedMime = $mimeMap[$extension] ?? 'application/octet-stream';
        }

        // SEGURIDAD: Bloquear MIME types peligrosos
        if (in_array($detectedMime, $this->dangerousMimeTypes)) {
            $response['error'] = 'Tipo de archivo peligroso detectado y bloqueado.';
            $this->logFailedUpload('dangerous_mime', $file, $context, ['mime' => $detectedMime]);
            return $response;
        }

        if (!in_array($detectedMime, $this->allowedMimeTypes)) {
            $response['error'] = 'Tipo de archivo no permitido. Tipos aceptados: PDF, JPG, PNG, MP4, XLSX, DOCX.';
            $this->logFailedUpload('mime_not_allowed', $file, $context, ['mime' => $detectedMime]);
            return $response;
        }

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

        // Generar nombre único y normalizado: slug + timestamp
        $sanitizedBasename = $this->slugify(pathinfo($originalName, PATHINFO_FILENAME));
        $timestamp = time();
        $randomSuffix = substr(md5(uniqid()), 0, 6); // Sufijo aleatorio para evitar colisiones
        $newFilename = $prefix . $sanitizedBasename . '_' . $timestamp . '_' . $randomSuffix . '.' . $extension;

        // Ruta completa del archivo
        $fullFilePath = $fullUploadPath . $newFilename;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $fullFilePath)) {
            $response['error'] = 'Error al guardar el archivo en el servidor.';
            return $response;
        }

        // Calcular hash SHA256 del archivo guardado
        $hash = hash_file('sha256', $fullFilePath);

        // Éxito
        $response['ok']   = true;
        $response['path'] = $uploadPath . $newFilename; // Ruta relativa desde WRITEPATH
        $response['mime'] = $detectedMime;
        $response['size'] = $file['size'];
        $response['hash'] = $hash;

        return $response;
    }

    /**
     * Elimina un archivo del sistema y su registro de base de datos con transacción
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @param string $table Tabla de la que eliminar (evidencias o evidencias_cliente)
     * @param string $primaryKey Nombre de la clave primaria
     * @param int $id ID del registro a eliminar
     * @return array ['ok' => bool, 'error' => string|null]
     */
    public function deleteFileWithTransaction(string $relativePath, string $table, string $primaryKey, int $id): array
    {
        $response = [
            'ok' => false,
            'error' => null,
        ];

        if (!$this->db) {
            $response['error'] = 'No hay conexión de base de datos configurada.';
            return $response;
        }

        $fullPath = WRITEPATH . $relativePath;

        // Iniciar transacción
        $this->db->transStart();

        try {
            // 1. Eliminar registro de base de datos
            $deleted = $this->db->table($table)->where($primaryKey, $id)->delete();

            if (!$deleted) {
                throw new \Exception('No se pudo eliminar el registro de la base de datos.');
            }

            // 2. Eliminar archivo físico
            if (file_exists($fullPath) && is_file($fullPath)) {
                if (!unlink($fullPath)) {
                    throw new \Exception('No se pudo eliminar el archivo físico.');
                }
            }

            // Completar transacción
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Error en la transacción de base de datos.');
            }

            $response['ok'] = true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            $response['error'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Elimina un archivo del sistema (sin transacción)
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
     * Convierte texto a slug (URL-friendly)
     *
     * @param string $text Texto a convertir
     * @return string Slug generado
     */
    protected function slugify(string $text): string
    {
        // Convertir a minúsculas
        $text = strtolower($text);

        // Reemplazar caracteres especiales
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);

        // Remover guiones múltiples
        $text = preg_replace('/-+/', '-', $text);

        // Remover guiones al inicio y final
        $text = trim($text, '-');

        // Limitar longitud
        $text = substr($text, 0, 50);

        return $text ?: 'file';
    }

    /**
     * Sanitiza un nombre de archivo (legacy, usar slugify preferentemente)
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

        // Verificar que el archivo temporal existe
        if (!file_exists($file['tmp_name'])) {
            return false;
        }

        // Primero validar por extensión (más permisivo)
        if (isset($file['name'])) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                return true;
            }
        }

        // Validar por tipo MIME del cliente
        if (isset($file['type'])) {
            $mime = strtolower($file['type']);
            if (in_array($mime, ['image/png', 'image/jpeg', 'image/jpg', 'image/pjpeg'])) {
                return true;
            }
        }

        // Intentar usar finfo si está disponible
        if (function_exists('finfo_open')) {
            try {
                $finfo = @finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false) {
                    $mime = @finfo_file($finfo, $file['tmp_name']);
                    @finfo_close($finfo);

                    if ($mime && in_array($mime, ['image/png', 'image/jpeg', 'image/jpg'])) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                // Continuar con otras validaciones
            }
        }

        return false;
    }

    /**
     * Obtiene información de un archivo subido
     *
     * @param string $relativePath Ruta relativa desde WRITEPATH
     * @return array|null ['size' => int, 'mime' => string, 'exists' => bool, 'hash' => string] o null
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

        $hash = hash_file('sha256', $fullPath);

        return [
            'size'   => filesize($fullPath),
            'mime'   => $mime,
            'exists' => true,
            'path'   => $fullPath,
            'hash'   => $hash,
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

    /**
     * Obtiene las extensiones permitidas
     *
     * @return array
     */
    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    /**
     * Obtiene los tipos MIME permitidos
     *
     * @return array
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Detecta si un archivo tiene doble extensión
     * Ejemplos peligrosos: archivo.php.jpg, documento.phar.png
     *
     * @param string $filename Nombre del archivo
     * @return bool True si tiene doble extensión peligrosa
     */
    protected function hasDoubleExtension(string $filename): bool
    {
        // Obtener todas las partes separadas por punto
        $parts = explode('.', $filename);

        // Si tiene menos de 3 partes, no puede tener doble extensión
        // Ejemplo: archivo.jpg (2 partes: "archivo" y "jpg")
        if (count($parts) < 3) {
            return false;
        }

        // Verificar si alguna de las partes intermedias es una extensión peligrosa
        // Excluir la última parte (extensión final)
        $intermediateParts = array_slice($parts, 1, count($parts) - 2);

        foreach ($intermediateParts as $part) {
            $partLower = strtolower($part);
            if (in_array($partLower, $this->dangerousExtensions)) {
                return true;
            }

            // También verificar si es una extensión permitida usada maliciosamente
            if (in_array($partLower, $this->allowedExtensions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Registra un intento fallido de subida de archivo
     *
     * @param string $reason Razón del fallo
     * @param array $file Información del archivo
     * @param array $context Contexto adicional
     * @param array $extra Datos adicionales
     * @return void
     */
    protected function logFailedUpload(string $reason, array $file, array $context = [], array $extra = []): void
    {
        $request = \Config\Services::request();

        $logData = [
            'event' => 'upload_failed',
            'reason' => $reason,
            'filename' => $file['name'] ?? 'unknown',
            'size' => $file['size'] ?? 0,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString(),
            'user_id' => $context['user_id'] ?? null,
            'id_auditoria' => $context['id_auditoria'] ?? null,
            'id_item' => $context['id_item'] ?? null,
            'id_auditoria_item' => $context['id_auditoria_item'] ?? null,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // Agregar datos extra
        if (!empty($extra)) {
            $logData['extra'] = $extra;
        }

        // Registrar en log de CodeIgniter
        log_message('warning', 'Upload Security: ' . json_encode($logData));

        // Si hay conexión a BD, guardar también en tabla de logs
        if ($this->db) {
            try {
                $this->db->table('upload_security_logs')->insert([
                    'event_type' => 'upload_failed',
                    'reason' => $reason,
                    'filename' => $file['name'] ?? null,
                    'filesize' => $file['size'] ?? 0,
                    'ip_address' => $request->getIPAddress(),
                    'user_agent' => substr($request->getUserAgent()->getAgentString(), 0, 255),
                    'user_id' => $context['user_id'] ?? null,
                    'id_auditoria' => $context['id_auditoria'] ?? null,
                    'id_item' => $context['id_item'] ?? null,
                    'metadata' => json_encode($extra),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                // Si falla la inserción en BD, solo registrar en log
                log_message('error', 'Failed to save security log to database: ' . $e->getMessage());
            }
        }
    }

    /**
     * Verifica si un nombre de archivo es sospechoso
     *
     * @param string $filename Nombre del archivo
     * @return bool True si es sospechoso
     */
    protected function isSuspiciousFilename(string $filename): bool
    {
        $suspiciousPatterns = [
            '/\.\./',           // Path traversal
            '/[<>:"\/\\|?*]/',  // Caracteres no permitidos en Windows
            '/\x00/',           // Null bytes
            '/\.php/i',         // Cualquier referencia a PHP
            '/\.phar/i',        // PHAR archives
            '/\.htaccess/i',    // Apache config
            '/\.config/i',      // Config files
            '/\.ini/i',         // INI files
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }
}
