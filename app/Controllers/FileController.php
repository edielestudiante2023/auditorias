<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class FileController extends BaseController
{
    /**
     * Sirve archivos de firmas de consultores
     */
    public function servirFirma(string $filename)
    {
        $filePath = WRITEPATH . 'uploads/firmas_consultor/' . $filename;

        // Verificar que el archivo existe
        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Detectar MIME type con fallback
        $mimeType = null;

        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
        }

        // Fallback: inferir por extensión
        if ($mimeType === null || $mimeType === false) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeMap = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];
            $mimeType = $mimeMap[$extension] ?? 'application/octet-stream';
        }

        // Verificar que es una imagen
        if (!str_starts_with($mimeType, 'image/')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Servir el archivo
        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Sirve logos de clientes
     */
    public function servirLogo(string $filename)
    {
        $filePath = WRITEPATH . 'uploads/logos_clientes/' . $filename;

        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Detectar MIME type con fallback
        $mimeType = null;

        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
        }

        // Fallback: inferir por extensión
        if ($mimeType === null || $mimeType === false) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $mimeMap = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
            ];
            $mimeType = $mimeMap[$extension] ?? 'application/octet-stream';
        }

        // Verificar que es una imagen
        if (!str_starts_with($mimeType, 'image/')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Sirve soportes de contratos
     */
    public function servirSoporte(string $filename)
    {
        $filePath = WRITEPATH . 'uploads/soportes_contratos/' . $filename;

        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mimeType = mime_content_type($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Length', filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }

    /**
     * Sirve PDFs de auditorías
     *
     * @param int $idAuditoria
     * @param int $idCliente
     */
    public function servirPdf(int $idAuditoria, int $idCliente)
    {
        // Verificar que el usuario esté autenticado
        if (!session()->get('logged_in')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Debe iniciar sesión');
        }

        $userId = session()->get('id_users');
        $role = session()->get('id_roles');

        log_message('debug', "Servir PDF - Auditoría: {$idAuditoria}, Cliente: {$idCliente}, Role: {$role}, UserId: {$userId}");

        // Verificar permisos básicos
        $tieneAcceso = false;

        if ($role == 1) {
            // Admin tiene acceso a todo
            $tieneAcceso = true;
        } elseif ($role == 2) {
            // Consultor: verificar que la auditoría le pertenece
            $consultorModel = model('App\Models\ConsultorModel');
            $consultor = $consultorModel->where('id_users', $userId)->first();

            if ($consultor) {
                $auditoriaModel = model('App\Models\AuditoriaModel');
                $auditoria = $auditoriaModel->find($idAuditoria);

                if ($auditoria && $auditoria['id_consultor'] == $consultor['id_consultor']) {
                    $tieneAcceso = true;
                    log_message('debug', "Consultor tiene acceso - id_consultor: {$consultor['id_consultor']}");
                } else {
                    log_message('debug', "Consultor NO tiene acceso - Auditoría: " . json_encode($auditoria));
                }
            }
        } elseif ($role == 3) {
            // Proveedor: verificar que tiene acceso a esta auditoría
            $db = \Config\Database::connect();
            $result = $db->query("
                SELECT COUNT(*) as tiene_acceso
                FROM auditorias a
                JOIN contratos_proveedor_cliente cpc ON cpc.id_proveedor = a.id_proveedor
                WHERE a.id_auditoria = ?
                  AND cpc.id_usuario_responsable = ?
            ", [$idAuditoria, $userId])->getRow();

            if ($result && $result->tiene_acceso > 0) {
                $tieneAcceso = true;
                log_message('debug', "Proveedor tiene acceso");
            }
        }

        if (!$tieneAcceso) {
            log_message('error', "Acceso denegado - Role: {$role}, UserId: {$userId}, Auditoría: {$idAuditoria}");
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('No autorizado - no tiene acceso a esta auditoría');
        }

        // Construir ruta del archivo
        $filePath = WRITEPATH . "reports/{$idAuditoria}/clientes/{$idCliente}/auditoria-proveedor-{$idAuditoria}-cliente-{$idCliente}.pdf";

        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('PDF no encontrado');
        }

        // Servir el PDF
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="auditoria-' . $idAuditoria . '-cliente-' . $idCliente . '.pdf"')
            ->setHeader('Content-Length', filesize($filePath))
            ->setBody(file_get_contents($filePath));
    }
}
