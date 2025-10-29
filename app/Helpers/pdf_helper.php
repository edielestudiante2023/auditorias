<?php

/**
 * Helper para formateo de PDFs
 */

if (!function_exists('formatearFechaPdf')) {
    /**
     * Formatea fecha para visualización en PDF
     *
     * @param string|null $fecha
     * @param string $formato
     * @return string
     */
    function formatearFechaPdf(?string $fecha, string $formato = 'd/m/Y'): string
    {
        if (empty($fecha)) {
            return 'N/A';
        }

        try {
            $timestamp = strtotime($fecha);
            return date($formato, $timestamp);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}

if (!function_exists('formatearFechaHoraPdf')) {
    /**
     * Formatea fecha y hora para PDF
     *
     * @param string|null $fecha
     * @return string
     */
    function formatearFechaHoraPdf(?string $fecha): string
    {
        return formatearFechaPdf($fecha, 'd/m/Y H:i');
    }
}

if (!function_exists('formatearCodigoAuditoria')) {
    /**
     * Formatea código de auditoría con prefijo
     *
     * @param int $idAuditoria
     * @param string|null $codigoFormato
     * @return string
     */
    function formatearCodigoAuditoria(int $idAuditoria, ?string $codigoFormato = null): string
    {
        if (!empty($codigoFormato)) {
            return strtoupper($codigoFormato);
        }

        return 'AUD-' . str_pad($idAuditoria, 6, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('obtenerColorCalificacion')) {
    /**
     * Obtiene color según calificación A/B/C
     *
     * @param string|null $calificacion
     * @return string Código de color hex
     */
    function obtenerColorCalificacion(?string $calificacion): string
    {
        if (empty($calificacion)) {
            return '#6c757d'; // Gris
        }

        switch (strtoupper($calificacion)) {
            case 'A':
                return '#28a745'; // Verde
            case 'B':
                return '#ffc107'; // Amarillo
            case 'C':
                return '#dc3545'; // Rojo
            default:
                return '#6c757d'; // Gris
        }
    }
}

if (!function_exists('obtenerTextoCalificacion')) {
    /**
     * Obtiene texto descriptivo de calificación
     *
     * @param string|null $calificacion
     * @return string
     */
    function obtenerTextoCalificacion(?string $calificacion): string
    {
        if (empty($calificacion)) {
            return 'Sin calificar';
        }

        switch (strtoupper($calificacion)) {
            case 'A':
                return 'Excelente';
            case 'B':
                return 'Aceptable';
            case 'C':
                return 'Deficiente';
            default:
                return 'N/A';
        }
    }
}

if (!function_exists('obtenerExtensionArchivo')) {
    /**
     * Obtiene extensión de un archivo
     *
     * @param string $nombreArchivo
     * @return string
     */
    function obtenerExtensionArchivo(string $nombreArchivo): string
    {
        return strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    }
}

if (!function_exists('esImagenPdf')) {
    /**
     * Verifica si un archivo es imagen
     *
     * @param string $nombreArchivo
     * @return bool
     */
    function esImagenPdf(string $nombreArchivo): bool
    {
        $extension = obtenerExtensionArchivo($nombreArchivo);
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
    }
}

if (!function_exists('obtenerIconoArchivoPdf')) {
    /**
     * Obtiene emoji/icono según tipo de archivo
     *
     * @param string $nombreArchivo
     * @return string
     */
    function obtenerIconoArchivoPdf(string $nombreArchivo): string
    {
        $extension = obtenerExtensionArchivo($nombreArchivo);

        $iconos = [
            'pdf' => '📄',
            'doc' => '📝',
            'docx' => '📝',
            'xls' => '📊',
            'xlsx' => '📊',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'mp4' => '🎥',
            'avi' => '🎥',
            'mov' => '🎥',
            'zip' => '📦',
            'rar' => '📦',
        ];

        return $iconos[$extension] ?? '📎';
    }
}

if (!function_exists('truncarTextoPdf')) {
    /**
     * Trunca texto con puntos suspensivos
     *
     * @param string|null $texto
     * @param int $longitud
     * @return string
     */
    function truncarTextoPdf(?string $texto, int $longitud = 100): string
    {
        if (empty($texto)) {
            return '';
        }

        if (mb_strlen($texto) <= $longitud) {
            return $texto;
        }

        return mb_substr($texto, 0, $longitud) . '...';
    }
}

if (!function_exists('formatearTamanioArchivo')) {
    /**
     * Formatea tamaño de archivo en bytes a formato legible
     *
     * @param int|null $bytes
     * @return string
     */
    function formatearTamanioArchivo(?int $bytes): string
    {
        if ($bytes === null || $bytes === 0) {
            return '0 B';
        }

        $unidades = ['B', 'KB', 'MB', 'GB'];
        $potencia = floor(log($bytes, 1024));
        $potencia = min($potencia, count($unidades) - 1);

        $tamanio = $bytes / pow(1024, $potencia);

        return round($tamanio, 2) . ' ' . $unidades[$potencia];
    }
}

if (!function_exists('obtenerRutaAbsolutaArchivo')) {
    /**
     * Convierte ruta relativa a absoluta para Dompdf
     *
     * @param string $rutaRelativa
     * @return string
     */
    function obtenerRutaAbsolutaArchivo(string $rutaRelativa): string
    {
        // Si ya es una ruta absoluta o una URL, devolverla tal cual
        if (preg_match('/^(http|https|file):/', $rutaRelativa)) {
            return $rutaRelativa;
        }

        // Si es una ruta relativa desde WRITEPATH
        if (file_exists(WRITEPATH . $rutaRelativa)) {
            return WRITEPATH . $rutaRelativa;
        }

        // Si es una ruta relativa desde FCPATH (public/)
        if (file_exists(FCPATH . $rutaRelativa)) {
            return FCPATH . $rutaRelativa;
        }

        // Intentar con barra inicial
        if (file_exists(FCPATH . ltrim($rutaRelativa, '/'))) {
            return FCPATH . ltrim($rutaRelativa, '/');
        }

        return $rutaRelativa;
    }
}

if (!function_exists('generarMiniaturaBase64')) {
    /**
     * Genera miniatura de imagen en base64 para embeber en PDF
     *
     * @param string $rutaArchivo
     * @param int $anchoMax
     * @param int $altoMax
     * @return string|null Base64 string o null si falla
     */
    function generarMiniaturaBase64(string $rutaArchivo, int $anchoMax = 150, int $altoMax = 150): ?string
    {
        $rutaAbsoluta = obtenerRutaAbsolutaArchivo($rutaArchivo);

        if (!file_exists($rutaAbsoluta)) {
            return null;
        }

        try {
            $extension = obtenerExtensionArchivo($rutaArchivo);

            // Crear imagen según extensión
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    $imagen = imagecreatefromjpeg($rutaAbsoluta);
                    break;
                case 'png':
                    $imagen = imagecreatefrompng($rutaAbsoluta);
                    break;
                case 'gif':
                    $imagen = imagecreatefromgif($rutaAbsoluta);
                    break;
                default:
                    return null;
            }

            if (!$imagen) {
                return null;
            }

            // Obtener dimensiones originales
            $anchoOriginal = imagesx($imagen);
            $altoOriginal = imagesy($imagen);

            // Calcular nuevas dimensiones manteniendo proporción
            $ratio = min($anchoMax / $anchoOriginal, $altoMax / $altoOriginal);
            $nuevoAncho = round($anchoOriginal * $ratio);
            $nuevoAlto = round($altoOriginal * $ratio);

            // Crear imagen redimensionada
            $miniatura = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            // Preservar transparencia para PNG
            if ($extension === 'png') {
                imagealphablending($miniatura, false);
                imagesavealpha($miniatura, true);
            }

            // Redimensionar
            imagecopyresampled(
                $miniatura,
                $imagen,
                0, 0, 0, 0,
                $nuevoAncho,
                $nuevoAlto,
                $anchoOriginal,
                $altoOriginal
            );

            // Convertir a base64
            ob_start();
            switch ($extension) {
                case 'png':
                    imagepng($miniatura);
                    $mime = 'image/png';
                    break;
                case 'gif':
                    imagegif($miniatura);
                    $mime = 'image/gif';
                    break;
                default:
                    imagejpeg($miniatura, null, 85);
                    $mime = 'image/jpeg';
            }
            $imagenData = ob_get_clean();

            // Liberar memoria
            imagedestroy($imagen);
            imagedestroy($miniatura);

            // Retornar data URI
            return 'data:' . $mime . ';base64,' . base64_encode($imagenData);

        } catch (\Exception $e) {
            log_message('error', 'Error generando miniatura: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('formatearNIT')) {
    /**
     * Formatea NIT con puntos y guión
     *
     * @param string|null $nit
     * @return string
     */
    function formatearNIT(?string $nit): string
    {
        if (empty($nit)) {
            return 'N/A';
        }

        // Remover caracteres no numéricos excepto guión
        $nit = preg_replace('/[^0-9-]/', '', $nit);

        // Si ya tiene formato, devolverlo
        if (strpos($nit, '-') !== false) {
            return $nit;
        }

        // Formatear: 900.123.456-7
        if (strlen($nit) >= 2) {
            $digito = substr($nit, -1);
            $numero = substr($nit, 0, -1);
            $numeroFormateado = number_format((float)$numero, 0, '', '.');
            return $numeroFormateado . '-' . $digito;
        }

        return $nit;
    }
}

if (!function_exists('escaparHTMLPdf')) {
    /**
     * Escapa HTML para prevenir XSS en PDFs
     *
     * @param string|null $texto
     * @return string
     */
    function escaparHTMLPdf(?string $texto): string
    {
        if (empty($texto)) {
            return '';
        }

        return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('contarEvidencias')) {
    /**
     * Cuenta evidencias de un ítem
     *
     * @param array $evidencias
     * @return int
     */
    function contarEvidencias(array $evidencias): int
    {
        return count($evidencias);
    }
}

if (!function_exists('tieneEvidencias')) {
    /**
     * Verifica si un ítem tiene evidencias
     *
     * @param array $evidencias
     * @return bool
     */
    function tieneEvidencias(array $evidencias): bool
    {
        return count($evidencias) > 0;
    }
}
