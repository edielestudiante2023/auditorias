<?php

namespace App\Controllers;

use App\Services\UploadService;

/**
 * Controlador de prueba para demostrar el uso de UploadService
 *
 * NOTA: Este controlador es solo para testing/demostración.
 * Eliminar en producción.
 */
class TestUploadController extends BaseController
{
    protected UploadService $uploadService;

    public function __construct()
    {
        $this->uploadService = new UploadService();
    }

    /**
     * Muestra el formulario de prueba
     */
    public function index()
    {
        return view('test/upload_form');
    }

    /**
     * Prueba de subida de firma de consultor
     */
    public function testFirmaConsultor()
    {
        if (!$this->request->getFile('firma')) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No se recibió archivo'
            ]);
        }

        $file = $this->request->getFile('firma');
        $idConsultor = $this->request->getPost('id_consultor') ?? 1;

        // Convertir FileInterface a array compatible
        $fileArray = [
            'name' => $file->getName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        $result = $this->uploadService->saveFirmaConsultor($fileArray, $idConsultor);

        return $this->response->setJSON($result);
    }

    /**
     * Prueba de subida de logo de cliente
     */
    public function testLogoCliente()
    {
        if (!$this->request->getFile('logo')) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No se recibió archivo'
            ]);
        }

        $file = $this->request->getFile('logo');
        $idCliente = $this->request->getPost('id_cliente') ?? 1;

        $fileArray = [
            'name' => $file->getName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        $result = $this->uploadService->saveLogoCliente($fileArray, $idCliente);

        return $this->response->setJSON($result);
    }

    /**
     * Prueba de subida de soporte de contrato
     */
    public function testSoporteContrato()
    {
        if (!$this->request->getFile('documento')) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No se recibió archivo'
            ]);
        }

        $file = $this->request->getFile('documento');
        $idContrato = $this->request->getPost('id_contrato') ?? 1;

        $fileArray = [
            'name' => $file->getName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        $result = $this->uploadService->saveSoporteContrato($fileArray, $idContrato);

        return $this->response->setJSON($result);
    }

    /**
     * Prueba de subida de evidencia
     */
    public function testEvidencia()
    {
        if (!$this->request->getFile('evidencia')) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No se recibió archivo'
            ]);
        }

        $file = $this->request->getFile('evidencia');
        $nit = $this->request->getPost('nit') ?? '900123456';
        $idAuditoria = $this->request->getPost('id_auditoria') ?? 1;
        $idItem = $this->request->getPost('id_item') ?? 1;

        $fileArray = [
            'name' => $file->getName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        $result = $this->uploadService->saveEvidencia($fileArray, $nit, $idAuditoria, $idItem);

        return $this->response->setJSON($result);
    }

    /**
     * Prueba usando helpers
     */
    public function testWithHelpers()
    {
        helper('upload');

        if (!$this->request->getFile('archivo')) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => 'No se recibió archivo'
            ]);
        }

        $file = $this->request->getFile('archivo');
        $tipo = $this->request->getPost('tipo') ?? 'firma';

        $fileArray = [
            'name' => $file->getName(),
            'type' => $file->getMimeType(),
            'tmp_name' => $file->getTempName(),
            'error' => $file->getError(),
            'size' => $file->getSize()
        ];

        // Validar archivo antes de subir
        $validation = validateUploadFile($fileArray);
        if (!$validation['valid']) {
            return $this->response->setJSON([
                'ok' => false,
                'error' => $validation['error']
            ]);
        }

        // Subir según tipo
        $result = match($tipo) {
            'firma' => saveFirmaConsultor($fileArray, 1),
            'logo' => saveLogoCliente($fileArray, 1),
            'contrato' => saveSoporteContrato($fileArray, 1),
            'evidencia' => saveEvidencia($fileArray, '900123456', 1, 1),
            default => ['ok' => false, 'error' => 'Tipo inválido']
        };

        // Agregar información adicional si fue exitoso
        if ($result['ok']) {
            $result['file_size_formatted'] = formatFileSize($result['size']);
            $result['file_exists'] = uploadExists($result['path']);
        }

        return $this->response->setJSON($result);
    }

    /**
     * Lista archivos subidos
     */
    public function listUploads()
    {
        helper('upload');

        $uploads = [];

        // Listar firmas
        $firmasPath = WRITEPATH . 'uploads/firmas_consultor/';
        if (is_dir($firmasPath)) {
            $files = scandir($firmasPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'index.html') {
                    $relativePath = 'uploads/firmas_consultor/' . $file;
                    $info = getUploadedFileInfo($relativePath);
                    if ($info) {
                        $uploads['firmas'][] = [
                            'name' => $file,
                            'size' => formatFileSize($info['size']),
                            'mime' => $info['mime'],
                            'path' => $relativePath
                        ];
                    }
                }
            }
        }

        // Listar logos
        $logosPath = WRITEPATH . 'uploads/logos_clientes/';
        if (is_dir($logosPath)) {
            $files = scandir($logosPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== 'index.html') {
                    $relativePath = 'uploads/logos_clientes/' . $file;
                    $info = getUploadedFileInfo($relativePath);
                    if ($info) {
                        $uploads['logos'][] = [
                            'name' => $file,
                            'size' => formatFileSize($info['size']),
                            'mime' => $info['mime'],
                            'path' => $relativePath
                        ];
                    }
                }
            }
        }

        return $this->response->setJSON($uploads);
    }
}
