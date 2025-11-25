<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false); // Security: disable PHP in templates
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('chroot', FCPATH);

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Genera PDF para un cliente específico de una auditoría
     *
     * @param int $idAuditoria
     * @param int $idCliente
     * @return string Ruta relativa del PDF generado
     */
    public function generarPdfCliente(int $idAuditoria, int $idCliente): string
    {
        // Obtener datos completos para el PDF
        $data = $this->obtenerDatosAuditoriaCliente($idAuditoria, $idCliente);

        // Renderizar vista
        $html = view('pdf/auditoria_cliente', $data);

        // Generar PDF
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        // Definir ruta de salida
        $dirBase = WRITEPATH . 'reports/' . $idAuditoria . '/clientes/' . $idCliente;
        if (!is_dir($dirBase)) {
            mkdir($dirBase, 0755, true);
        }

        // Crear nombre descriptivo del archivo según el servicio
        $nombreProveedor = $data['auditoria']['proveedor_nombre'] ?? 'Proveedor';
        $nombreCliente = $data['cliente']['razon_social'] ?? 'Cliente';

        // Obtener el servicio del contrato
        $db = \Config\Database::connect();
        $contrato = $db->table('contratos_proveedor_cliente cpc')
            ->select('s.id_servicio')
            ->join('servicios s', 's.id_servicio = cpc.id_servicio', 'left')
            ->where('cpc.id_proveedor', $data['auditoria']['id_proveedor'])
            ->where('cpc.id_cliente', $idCliente)
            ->where('cpc.estado', 'activo')
            ->get()
            ->getRowArray();

        $idServicio = $contrato['id_servicio'] ?? null;
        $tipoAuditoria = '';

        if ($idServicio == 1) {
            $tipoAuditoria = 'AUDITORIA PROVEEDOR DE ASEO';
        } elseif ($idServicio == 2) {
            $tipoAuditoria = 'AUDITORIA PROVEEDOR DE VIGILANCIA';
        } else {
            $tipoAuditoria = 'AUDITORIA OTROS PROVEEDORES';
        }

        // Formato para N8N: TIPO__CLIENTE___PROVEEDOR.pdf
        $clienteUpper = strtoupper($nombreCliente);
        $proveedorUpper = strtoupper($nombreProveedor);
        $filename = "{$tipoAuditoria}__{$clienteUpper}___{$proveedorUpper}.pdf";

        $fullPath = $dirBase . '/' . $filename;

        // Guardar archivo
        file_put_contents($fullPath, $this->dompdf->output());

        // Retornar ruta relativa
        return "reports/{$idAuditoria}/clientes/{$idCliente}/{$filename}";
    }

    /**
     * Sanitiza un nombre para usar en archivo
     */
    private function sanitizarNombreArchivo(string $nombre): string
    {
        // Remover caracteres especiales y espacios
        $nombre = preg_replace('/[^a-zA-Z0-9\s-]/', '', $nombre);
        // Reemplazar espacios por guiones
        $nombre = preg_replace('/\s+/', '-', trim($nombre));
        // Limitar longitud
        return substr($nombre, 0, 50);
    }

    /**
     * Genera PDF global de auditoría (opcional)
     *
     * @param int $idAuditoria
     * @return string Ruta relativa del PDF generado
     */
    public function generarPdfGlobal(int $idAuditoria): string
    {
        // Obtener datos completos para el PDF global
        $data = $this->obtenerDatosAuditoriaGlobal($idAuditoria);

        // Renderizar vista
        $html = view('pdf/auditoria_global', $data);

        // Generar PDF
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        // Definir ruta de salida
        $dirBase = WRITEPATH . 'reports/' . $idAuditoria;
        if (!is_dir($dirBase)) {
            mkdir($dirBase, 0755, true);
        }

        $filename = "auditoria-global-{$idAuditoria}.pdf";
        $fullPath = $dirBase . '/' . $filename;

        // Guardar archivo
        file_put_contents($fullPath, $this->dompdf->output());

        // Retornar ruta relativa
        return "reports/{$idAuditoria}/{$filename}";
    }

    /**
     * Genera PDF completo de auditoría con logos, evidencias y formato profesional
     *
     * @param int $idAuditoria ID de la auditoría
     * @param int|null $idCliente ID del cliente (null para reporte global)
     * @return string Ruta relativa del PDF generado
     */
    public function generarPdfCompleto(int $idAuditoria, ?int $idCliente = null): string
    {
        // Obtener datos según el tipo de reporte
        if ($idCliente) {
            $data = $this->obtenerDatosAuditoriaClienteCompleto($idAuditoria, $idCliente);
            $tipoReporte = 'cliente';
        } else {
            $data = $this->obtenerDatosAuditoriaGlobalCompleto($idAuditoria);
            $tipoReporte = 'global';
        }

        // Renderizar vista mejorada
        $html = view('pdf/auditoria_completa', $data);

        // Generar PDF
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();

        // Definir ruta de salida
        if ($idCliente) {
            $dirBase = WRITEPATH . 'reports/' . $idAuditoria . '/clientes/' . $idCliente;
            $filename = "auditoria-completa-{$idAuditoria}-cliente-{$idCliente}.pdf";
        } else {
            $dirBase = WRITEPATH . 'reports/' . $idAuditoria;
            $filename = "auditoria-completa-global-{$idAuditoria}.pdf";
        }

        if (!is_dir($dirBase)) {
            mkdir($dirBase, 0755, true);
        }

        $fullPath = $dirBase . '/' . $filename;

        // Guardar archivo
        file_put_contents($fullPath, $this->dompdf->output());

        // Retornar ruta relativa
        if ($idCliente) {
            return "reports/{$idAuditoria}/clientes/{$idCliente}/{$filename}";
        } else {
            return "reports/{$idAuditoria}/{$filename}";
        }
    }

    /**
     * Obtiene datos de auditoría para PDF de cliente específico
     * Separa ítems globales de ítems por cliente
     */
    private function obtenerDatosAuditoriaCliente(int $idAuditoria, int $idCliente): array
    {
        $db = \Config\Database::connect();

        // Datos principales de auditoría
        $auditoria = $db->table('auditorias a')
            ->select('a.*,
                      p.nit as proveedor_nit,
                      p.razon_social as proveedor_nombre,
                      p.email_contacto as proveedor_email,
                      cons.nombre_completo as consultor_nombre,
                      cons.firma_path,
                      cons.licencia_sst')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        // Datos del cliente
        $cliente = $db->table('clientes')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        // Porcentaje específico del cliente
        $resultPorcentaje = $db->table('auditoria_clientes')
            ->select('porcentaje_cumplimiento')
            ->where('id_auditoria', $idAuditoria)
            ->where('id_cliente', $idCliente)
            ->get();

        if ($resultPorcentaje === false) {
            log_message('error', "Error en query auditoria_clientes: " . print_r($db->error(), true));
            throw new \Exception("Error al consultar porcentaje del cliente");
        }

        $porcentajeCliente = $resultPorcentaje->getRow();

        // Si no existe el registro, significa que el porcentaje no fue calculado
        if (!$porcentajeCliente) {
            log_message('warning', "No existe registro en auditoria_clientes para auditoría {$idAuditoria}, cliente {$idCliente}");
            $porcentajeCliente = (object)['porcentaje_cumplimiento' => 0];
        }

        // ÍTEMS GLOBALES (alcance='global' o 'mixto')
        $itemsGlobales = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ib.alcance,
                ai.comentario_proveedor,
                ai.comentario_consultor,
                ai.calificacion_consultor as calificacion,
                ai.id_auditoria_item
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
            ORDER BY ib.orden ASC
        ", [$idAuditoria])->getResultArray();

        // Cargar evidencias globales
        foreach ($itemsGlobales as &$item) {
            $item['evidencias'] = $db->table('evidencias')
                ->where('id_auditoria_item', $item['id_auditoria_item'])
                ->get()
                ->getResultArray();
        }

        // ÍTEMS POR CLIENTE (alcance='por_cliente')
        $itemsPorCliente = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ib.alcance,
                aic.comentario_proveedor_cliente,
                aic.calificacion_ajustada as calificacion,
                aic.comentario_cliente,
                ai.id_auditoria_item,
                aic.id_auditoria_item_cliente
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            JOIN auditoria_item_cliente aic ON aic.id_auditoria_item = ai.id_auditoria_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance = 'por_cliente'
              AND aic.id_cliente = ?
            ORDER BY ib.orden ASC
        ", [$idAuditoria, $idCliente])->getResultArray();

        // Cargar evidencias por cliente
        foreach ($itemsPorCliente as &$item) {
            $item['evidencias'] = $db->table('evidencias_cliente')
                ->where('id_auditoria_item_cliente', $item['id_auditoria_item_cliente'])
                ->get()
                ->getResultArray();
        }

        // Obtener personal asignado del proveedor para este cliente
        $personalAsignado = $db->table('personal_asignado pa')
            ->select('pa.*, pa.nombres, pa.apellidos, pa.tipo_documento, pa.numero_documento, pa.cargo, pa.fecha_ingreso')
            ->where('pa.id_proveedor', $auditoria['id_proveedor'])
            ->where('pa.id_cliente', $idCliente)
            ->where('pa.estado', 'activo')
            ->orderBy('pa.apellidos', 'ASC')
            ->get()
            ->getResultArray();

        return [
            'auditoria' => $auditoria,
            'cliente' => $cliente,
            'porcentaje_cliente' => $porcentajeCliente ? $porcentajeCliente->porcentaje_cumplimiento : 0,
            'items_globales' => $itemsGlobales,
            'items_por_cliente' => $itemsPorCliente,
            'personal_asignado' => $personalAsignado,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtiene datos de auditoría para PDF global
     */
    private function obtenerDatosAuditoriaGlobal(int $idAuditoria): array
    {
        $db = \Config\Database::connect();

        // Datos principales de auditoría
        $auditoria = $db->table('auditorias a')
            ->select('a.*,
                      p.nit as proveedor_nit,
                      p.razon_social as proveedor_nombre,
                      p.email_contacto as proveedor_email,
                      cons.nombre_completo as consultor_nombre,
                      cons.firma_path,
                      cons.licencia_sst')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        // Clientes asignados
        $clientes = $db->table('auditoria_clientes ac')
            ->select('c.*, ac.porcentaje_cumplimiento')
            ->join('clientes c', 'c.id_cliente = ac.id_cliente')
            ->where('ac.id_auditoria', $idAuditoria)
            ->get()
            ->getResultArray();

        // Ítems con calificaciones globales
        $items = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ai.comentario_proveedor,
                ai.comentario_consultor,
                ai.calificacion_consultor as calificacion
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = {$idAuditoria}
            ORDER BY ib.orden ASC
        ")->getResultArray();

        return [
            'auditoria' => $auditoria,
            'clientes' => $clientes,
            'items' => $items,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtiene datos completos de auditoría para PDF de cliente específico
     * Incluye logos, evidencias y toda la información necesaria
     */
    private function obtenerDatosAuditoriaClienteCompleto(int $idAuditoria, int $idCliente): array
    {
        $db = \Config\Database::connect();

        // Datos principales de auditoría con información completa
        $auditoria = $db->table('auditorias a')
            ->select('a.*,
                      p.nit as proveedor_nit,
                      p.razon_social as proveedor_nombre,
                      p.email_contacto as proveedor_email,
                      p.logo_path as proveedor_logo,
                      cons.nombre_completo as consultor_nombre,
                      cons.firma_path,
                      cons.licencia_sst,
                      cons.email as consultor_email')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        // Datos del cliente
        $cliente = $db->table('clientes')
            ->select('*, logo_path as cliente_logo')
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRowArray();

        // Porcentaje específico del cliente
        $porcentajeCliente = $db->table('auditoria_clientes')
            ->select('porcentaje_cumplimiento')
            ->where('id_auditoria', $idAuditoria)
            ->where('id_cliente', $idCliente)
            ->get()
            ->getRow();

        // ÍTEMS GLOBALES (alcance='global' o 'mixto')
        $itemsGlobales = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ib.alcance,
                ai.comentario_proveedor,
                ai.comentario_consultor,
                ai.calificacion_consultor as calificacion,
                ai.id_auditoria_item
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
            ORDER BY ib.orden ASC
        ", [$idAuditoria])->getResultArray();

        // Cargar evidencias globales
        foreach ($itemsGlobales as &$item) {
            $item['evidencias'] = $db->table('evidencias')
                ->where('id_auditoria_item', $item['id_auditoria_item'])
                ->get()
                ->getResultArray();
        }

        // ÍTEMS POR CLIENTE (alcance='por_cliente')
        $itemsPorCliente = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ib.alcance,
                aic.comentario_proveedor_cliente,
                aic.calificacion_ajustada as calificacion,
                aic.comentario_cliente,
                ai.id_auditoria_item,
                aic.id_auditoria_item_cliente
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            JOIN auditoria_item_cliente aic ON aic.id_auditoria_item = ai.id_auditoria_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance = 'por_cliente'
              AND aic.id_cliente = ?
            ORDER BY ib.orden ASC
        ", [$idAuditoria, $idCliente])->getResultArray();

        // Cargar evidencias por cliente
        foreach ($itemsPorCliente as &$item) {
            $item['evidencias'] = $db->table('evidencias_cliente')
                ->where('id_auditoria_item_cliente', $item['id_auditoria_item_cliente'])
                ->get()
                ->getResultArray();
        }

        // Preparar rutas de logos
        $logoProveedor = null;
        $logoCliente = null;

        if (!empty($auditoria['proveedor_logo'])) {
            $rutaLogo = WRITEPATH . 'uploads/' . $auditoria['proveedor_logo'];
            if (file_exists($rutaLogo)) {
                $logoProveedor = $rutaLogo;
            }
        }

        if (!empty($cliente['cliente_logo'])) {
            $rutaLogo = WRITEPATH . 'uploads/' . $cliente['cliente_logo'];
            if (file_exists($rutaLogo)) {
                $logoCliente = $rutaLogo;
            }
        }

        return [
            'auditoria' => $auditoria,
            'cliente' => $cliente,
            'porcentaje_cumplimiento' => $porcentajeCliente ? $porcentajeCliente->porcentaje_cumplimiento : 0,
            'items_globales' => $itemsGlobales,
            'items_por_cliente' => $itemsPorCliente,
            'logo_proveedor' => $logoProveedor,
            'logo_cliente' => $logoCliente,
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Obtiene datos completos de auditoría para PDF global
     * Incluye logos, todos los clientes y evidencias
     */
    private function obtenerDatosAuditoriaGlobalCompleto(int $idAuditoria): array
    {
        $db = \Config\Database::connect();

        // Datos principales de auditoría con información completa
        $auditoria = $db->table('auditorias a')
            ->select('a.*,
                      p.nit as proveedor_nit,
                      p.razon_social as proveedor_nombre,
                      p.email_contacto as proveedor_email,
                      p.logo_path as proveedor_logo,
                      cons.nombre_completo as consultor_nombre,
                      cons.firma_path,
                      cons.licencia_sst,
                      cons.email as consultor_email')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        // Clientes asignados
        $clientes = $db->table('auditoria_clientes ac')
            ->select('c.*, ac.porcentaje_cumplimiento')
            ->join('clientes c', 'c.id_cliente = ac.id_cliente')
            ->where('ac.id_auditoria', $idAuditoria)
            ->get()
            ->getResultArray();

        // Ítems globales con evidencias
        $itemsGlobales = $db->query("
            SELECT
                ib.codigo_item,
                ib.titulo,
                ib.descripcion,
                ai.comentario_proveedor,
                ai.comentario_consultor,
                ai.calificacion_consultor as calificacion,
                ai.id_auditoria_item
            FROM auditoria_items ai
            JOIN items_banco ib ON ib.id_item = ai.id_item
            WHERE ai.id_auditoria = ?
              AND ib.alcance IN ('global', 'mixto')
            ORDER BY ib.orden ASC
        ", [$idAuditoria])->getResultArray();

        // Cargar evidencias para cada ítem global
        foreach ($itemsGlobales as &$item) {
            $item['evidencias'] = $db->table('evidencias')
                ->where('id_auditoria_item', $item['id_auditoria_item'])
                ->get()
                ->getResultArray();
        }

        // Preparar ruta de logo del proveedor
        $logoProveedor = null;
        if (!empty($auditoria['proveedor_logo'])) {
            $rutaLogo = WRITEPATH . 'uploads/' . $auditoria['proveedor_logo'];
            if (file_exists($rutaLogo)) {
                $logoProveedor = $rutaLogo;
            }
        }

        return [
            'auditoria' => $auditoria,
            'clientes' => $clientes,
            'items_globales' => $itemsGlobales,
            'items_por_cliente' => [], // No aplica en reporte global
            'logo_proveedor' => $logoProveedor,
            'logo_cliente' => null, // No aplica en reporte global
            'fecha_generacion' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Calcula porcentaje de cumplimiento según regla:
     * cumple=1, parcial=0.5, no_cumple=0, excluir no_aplica
     *
     * @param array $items Array de items con campo 'calificacion'
     * @return float Porcentaje (0-100)
     */
    public static function calcularPorcentajeCumplimiento(array $items): float
    {
        $aplicables = array_filter($items, function($item) {
            return isset($item['calificacion']) && $item['calificacion'] !== 'no_aplica';
        });

        if (count($aplicables) === 0) {
            return 0.0;
        }

        $puntos = 0;
        foreach ($aplicables as $item) {
            switch ($item['calificacion']) {
                case 'cumple':
                    $puntos += 1;
                    break;
                case 'parcial':
                    $puntos += 0.5;
                    break;
                case 'no_cumple':
                    $puntos += 0;
                    break;
            }
        }

        $porcentaje = ($puntos / count($aplicables)) * 100;
        return round($porcentaje, 2);
    }
}
