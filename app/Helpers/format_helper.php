<?php

/**
 * Format Helper
 *
 * Funciones de utilidad para formateo de datos en vistas
 */

if (!function_exists('porcentaje')) {
    /**
     * Formatea un valor numérico como porcentaje con 1 decimal y símbolo %
     *
     * @param float|int|null $val Valor entre 0 y 100
     * @param int $decimales Cantidad de decimales (default: 1)
     * @return string Valor formateado como "85.5%"
     */
    function porcentaje($val, int $decimales = 1): string
    {
        if ($val === null || $val === '') {
            return '0.0%';
        }

        return number_format((float)$val, $decimales, '.', ',') . '%';
    }
}

if (!function_exists('semaforoClase')) {
    /**
     * Devuelve clase CSS de Bootstrap según el valor del porcentaje
     *
     * Reglas:
     * - >= 80: 'text-success' (verde)
     * - 50-79: 'text-warning' (amarillo)
     * - < 50: 'text-danger' (rojo)
     *
     * @param float|int|null $valor Valor del porcentaje (0-100)
     * @return string Clase CSS de Bootstrap
     */
    function semaforoClase($valor): string
    {
        if ($valor === null || $valor === '') {
            $valor = 0;
        }

        $valor = (float)$valor;

        if ($valor >= 80) {
            return 'text-success';
        } elseif ($valor >= 50) {
            return 'text-warning';
        } else {
            return 'text-danger';
        }
    }
}

if (!function_exists('semaforoBgClase')) {
    /**
     * Devuelve clase CSS de fondo de Bootstrap según el valor del porcentaje
     *
     * Útil para badges, progress bars, etc.
     *
     * @param float|int|null $valor Valor del porcentaje (0-100)
     * @return string Clase CSS de Bootstrap (bg-success, bg-warning, bg-danger)
     */
    function semaforoBgClase($valor): string
    {
        if ($valor === null || $valor === '') {
            $valor = 0;
        }

        $valor = (float)$valor;

        if ($valor >= 80) {
            return 'bg-success';
        } elseif ($valor >= 50) {
            return 'bg-warning';
        } else {
            return 'bg-danger';
        }
    }
}

if (!function_exists('estadoBadge')) {
    /**
     * Genera un badge HTML con color según el estado de la auditoría
     *
     * @param string $estado Estado de la auditoría
     * @return string HTML del badge
     */
    function estadoBadge(string $estado): string
    {
        $badges = [
            'pendiente' => '<span class="badge bg-secondary">Pendiente</span>',
            'en_progreso' => '<span class="badge bg-primary">En Progreso</span>',
            'en_revision_consultor' => '<span class="badge bg-info">En Revisión</span>',
            'cerrada' => '<span class="badge bg-success">Cerrada</span>',
            'cancelada' => '<span class="badge bg-danger">Cancelada</span>',
        ];

        return $badges[$estado] ?? '<span class="badge bg-secondary">' . ucfirst($estado) . '</span>';
    }
}

if (!function_exists('calificacionBadge')) {
    /**
     * Genera un badge HTML con color según la calificación
     *
     * @param string|null $calificacion Calificación del ítem
     * @return string HTML del badge
     */
    function calificacionBadge(?string $calificacion): string
    {
        if (!$calificacion) {
            return '<span class="badge bg-secondary">Sin calificar</span>';
        }

        $badges = [
            'cumple' => '<span class="badge bg-success">Cumple</span>',
            'parcial' => '<span class="badge bg-warning text-dark">Parcial</span>',
            'no_cumple' => '<span class="badge bg-danger">No Cumple</span>',
            'no_aplica' => '<span class="badge bg-secondary">No Aplica</span>',
        ];

        return $badges[$calificacion] ?? '<span class="badge bg-secondary">' . ucfirst($calificacion) . '</span>';
    }
}

if (!function_exists('progressBar')) {
    /**
     * Genera una barra de progreso HTML de Bootstrap
     *
     * @param float|int $valor Valor del porcentaje (0-100)
     * @param bool $mostrarTexto Si se muestra el porcentaje dentro de la barra
     * @param string $altura Altura de la barra (ej: '20px', '1.5rem')
     * @return string HTML de la barra de progreso
     */
    function progressBar($valor, bool $mostrarTexto = true, string $altura = '1.5rem'): string
    {
        if ($valor === null || $valor === '') {
            $valor = 0;
        }

        $valor = (float)$valor;
        $valor = max(0, min(100, $valor)); // Asegurar que esté entre 0 y 100

        $clase = semaforoBgClase($valor);
        $texto = $mostrarTexto ? porcentaje($valor) : '';

        return <<<HTML
        <div class="progress" style="height: {$altura};">
            <div class="progress-bar {$clase}" role="progressbar"
                 style="width: {$valor}%"
                 aria-valuenow="{$valor}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                {$texto}
            </div>
        </div>
        HTML;
    }
}

if (!function_exists('formatoFecha')) {
    /**
     * Formatea una fecha en formato legible en español
     *
     * @param string|null $fecha Fecha en formato Y-m-d H:i:s
     * @param string $formato Formato de salida (default: 'd/m/Y H:i')
     * @return string Fecha formateada o '-' si es null
     */
    function formatoFecha(?string $fecha, string $formato = 'd/m/Y H:i'): string
    {
        if (!$fecha) {
            return '-';
        }

        try {
            $dt = new DateTime($fecha);
            return $dt->format($formato);
        } catch (Exception $e) {
            return '-';
        }
    }
}

if (!function_exists('formatoFechaSolo')) {
    /**
     * Formatea solo la fecha sin hora
     *
     * @param string|null $fecha Fecha en formato Y-m-d H:i:s
     * @return string Fecha formateada (dd/mm/yyyy) o '-'
     */
    function formatoFechaSolo(?string $fecha): string
    {
        return formatoFecha($fecha, 'd/m/Y');
    }
}

if (!function_exists('tiempoRelativo')) {
    /**
     * Devuelve tiempo relativo en español (hace 5 minutos, hace 2 días, etc.)
     *
     * @param string|null $fecha Fecha en formato Y-m-d H:i:s
     * @return string Tiempo relativo o '-'
     */
    function tiempoRelativo(?string $fecha): string
    {
        if (!$fecha) {
            return '-';
        }

        try {
            $dt = new DateTime($fecha);
            $ahora = new DateTime();
            $diff = $ahora->diff($dt);

            if ($diff->y > 0) {
                return "hace {$diff->y} " . ($diff->y == 1 ? 'año' : 'años');
            } elseif ($diff->m > 0) {
                return "hace {$diff->m} " . ($diff->m == 1 ? 'mes' : 'meses');
            } elseif ($diff->d > 0) {
                return "hace {$diff->d} " . ($diff->d == 1 ? 'día' : 'días');
            } elseif ($diff->h > 0) {
                return "hace {$diff->h} " . ($diff->h == 1 ? 'hora' : 'horas');
            } elseif ($diff->i > 0) {
                return "hace {$diff->i} " . ($diff->i == 1 ? 'minuto' : 'minutos');
            } else {
                return 'hace unos segundos';
            }
        } catch (Exception $e) {
            return '-';
        }
    }
}

if (!function_exists('iniciales')) {
    /**
     * Obtiene las iniciales de un nombre completo
     *
     * @param string|null $nombre Nombre completo
     * @param int $max Máximo de iniciales (default: 2)
     * @return string Iniciales en mayúsculas (ej: "JD")
     */
    function iniciales(?string $nombre, int $max = 2): string
    {
        if (!$nombre) {
            return '??';
        }

        $palabras = explode(' ', trim($nombre));
        $iniciales = '';

        foreach (array_slice($palabras, 0, $max) as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper(mb_substr($palabra, 0, 1));
            }
        }

        return $iniciales ?: '??';
    }
}

if (!function_exists('truncar')) {
    /**
     * Trunca un texto a una longitud específica con elipsis
     *
     * @param string|null $texto Texto a truncar
     * @param int $longitud Longitud máxima
     * @param string $sufijo Sufijo a agregar (default: '...')
     * @return string Texto truncado
     */
    function truncar(?string $texto, int $longitud = 50, string $sufijo = '...'): string
    {
        if (!$texto) {
            return '';
        }

        if (mb_strlen($texto) <= $longitud) {
            return $texto;
        }

        return mb_substr($texto, 0, $longitud) . $sufijo;
    }
}

if (!function_exists('formatoCodigoFormato')) {
    /**
     * Formatea el código de formato de auditoría
     *
     * @param string|null $codigo Código de formato
     * @param string|null $version Versión del formato
     * @return string Código formateado (ej: "FRM-AUD-001 v2.0")
     */
    function formatoCodigoFormato(?string $codigo, ?string $version = null): string
    {
        if (!$codigo) {
            return '-';
        }

        $resultado = strtoupper($codigo);

        if ($version) {
            $resultado .= " v{$version}";
        }

        return $resultado;
    }
}

if (!function_exists('formatoNIT')) {
    /**
     * Formatea NIT con separadores de miles
     *
     * @param string|null $nit NIT sin formato
     * @return string NIT formateado (ej: "900.123.456-7")
     */
    function formatoNIT(?string $nit): string
    {
        if (!$nit) {
            return '-';
        }

        // Eliminar espacios y puntos existentes
        $nit = str_replace([' ', '.', '-'], '', $nit);

        // Si tiene dígito de verificación (último caracter no numérico o separado por guión)
        if (strlen($nit) > 1) {
            $digito = substr($nit, -1);
            $numero = substr($nit, 0, -1);

            // Formatear con puntos de miles
            $numeroFormateado = number_format((int)$numero, 0, '', '.');

            return $numeroFormateado . '-' . $digito;
        }

        return $nit;
    }
}

if (!function_exists('esImagen')) {
    /**
     * Verifica si un archivo es una imagen según su extensión
     *
     * @param string|null $nombreArchivo Nombre del archivo con extensión
     * @return bool True si es imagen, false en caso contrario
     */
    function esImagen(?string $nombreArchivo): bool
    {
        if (!$nombreArchivo) {
            return false;
        }

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        $extensionesImagen = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

        return in_array($extension, $extensionesImagen);
    }
}

if (!function_exists('iconoArchivo')) {
    /**
     * Retorna el icono de Bootstrap Icons según la extensión del archivo
     *
     * @param string|null $nombreArchivo Nombre del archivo con extensión
     * @return string Clase de icono de Bootstrap Icons
     */
    function iconoArchivo(?string $nombreArchivo): string
    {
        if (!$nombreArchivo) {
            return 'bi-file-earmark';
        }

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        $iconos = [
            'pdf' => 'bi-file-earmark-pdf',
            'doc' => 'bi-file-earmark-word',
            'docx' => 'bi-file-earmark-word',
            'xls' => 'bi-file-earmark-excel',
            'xlsx' => 'bi-file-earmark-excel',
            'ppt' => 'bi-file-earmark-ppt',
            'pptx' => 'bi-file-earmark-ppt',
            'txt' => 'bi-file-earmark-text',
            'zip' => 'bi-file-earmark-zip',
            'rar' => 'bi-file-earmark-zip',
            '7z' => 'bi-file-earmark-zip',
            'jpg' => 'bi-file-earmark-image',
            'jpeg' => 'bi-file-earmark-image',
            'png' => 'bi-file-earmark-image',
            'gif' => 'bi-file-earmark-image',
            'svg' => 'bi-file-earmark-image',
            'mp4' => 'bi-file-earmark-play',
            'avi' => 'bi-file-earmark-play',
            'mov' => 'bi-file-earmark-play',
            'mp3' => 'bi-file-earmark-music',
            'wav' => 'bi-file-earmark-music',
        ];

        return $iconos[$extension] ?? 'bi-file-earmark';
    }
}
