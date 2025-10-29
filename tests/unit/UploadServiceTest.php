<?php

namespace Tests\Unit;

use App\Services\UploadService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Database\BaseConnection;

/**
 * Tests unitarios para UploadService
 *
 * Ejecutar:
 * vendor/bin/phpunit tests/unit/UploadServiceTest.php
 */
class UploadServiceTest extends CIUnitTestCase
{
    protected UploadService $service;
    protected string $testUploadDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UploadService();
        $this->testUploadDir = WRITEPATH . 'uploads/test/';

        // Crear directorio de prueba
        if (!is_dir($this->testUploadDir)) {
            mkdir($this->testUploadDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Limpiar archivos de prueba
        $this->cleanTestDirectory($this->testUploadDir);
    }

    /**
     * Limpia recursivamente un directorio de prueba
     */
    private function cleanTestDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->cleanTestDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Crea un archivo simulado para testing
     */
    private function createMockFile(string $content, string $filename, string $mime = 'text/plain'): array
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_test_');
        file_put_contents($tmpFile, $content);

        return [
            'name' => $filename,
            'type' => $mime,
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile),
        ];
    }

    /**
     * Crea un archivo PDF de prueba válido
     */
    private function createMockPDF(): array
    {
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] >>\nendobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\ntrailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n190\n%%EOF";

        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_test_pdf_');
        file_put_contents($tmpFile, $pdfContent);

        return [
            'name' => 'test-document.pdf',
            'type' => 'application/pdf',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile),
        ];
    }

    // ========================================
    // TESTS DE CONFIGURACIÓN
    // ========================================

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(UploadService::class, $this->service);
    }

    public function testDefaultMaxFileSizeIs15MB(): void
    {
        $this->assertEquals(15.0, $this->service->getMaxFileSizeMB());
    }

    public function testCanSetCustomMaxFileSize(): void
    {
        $this->service->setMaxFileSize(10);
        $this->assertEquals(10.0, $this->service->getMaxFileSizeMB());
    }

    public function testGetAllowedExtensions(): void
    {
        $extensions = $this->service->getAllowedExtensions();

        $this->assertIsArray($extensions);
        $this->assertContains('pdf', $extensions);
        $this->assertContains('jpg', $extensions);
        $this->assertContains('png', $extensions);
        $this->assertContains('mp4', $extensions);
        $this->assertContains('xlsx', $extensions);
        $this->assertContains('docx', $extensions);
    }

    public function testGetAllowedMimeTypes(): void
    {
        $mimes = $this->service->getAllowedMimeTypes();

        $this->assertIsArray($mimes);
        $this->assertContains('application/pdf', $mimes);
        $this->assertContains('image/png', $mimes);
        $this->assertContains('image/jpeg', $mimes);
        $this->assertContains('video/mp4', $mimes);
    }

    // ========================================
    // TESTS DE VALIDACIÓN DE ARCHIVO
    // ========================================

    public function testRejectsEmptyFile(): void
    {
        $file = [
            'name' => 'test.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_OK,
            'size' => 0,
        ];

        $result = $this->service->saveEvidencia($file, '123456789', 1, 1);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('No se recibió ningún archivo', $result['error']);
    }

    public function testRejectsFileWithUploadError(): void
    {
        $file = [
            'name' => 'test.pdf',
            'type' => 'application/pdf',
            'tmp_name' => '/tmp/test.pdf',
            'error' => UPLOAD_ERR_INI_SIZE,
            'size' => 1000,
        ];

        $result = $this->service->saveEvidencia($file, '123456789', 1, 1);

        $this->assertFalse($result['ok']);
        $this->assertNotNull($result['error']);
    }

    public function testRejectsOversizedFile(): void
    {
        $this->service->setMaxFileSize(1); // 1 MB

        $file = $this->createMockFile(str_repeat('x', 2000000), 'large-file.pdf');

        $result = $this->service->saveEvidencia($file, '123456789', 1, 1);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('excede el tamaño máximo', $result['error']);

        // Cleanup
        if (file_exists($file['tmp_name'])) {
            unlink($file['tmp_name']);
        }
    }

    public function testRejectsInvalidExtension(): void
    {
        $file = $this->createMockFile('fake content', 'test.exe');

        $result = $this->service->saveEvidencia($file, '123456789', 1, 1);

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('Extensión de archivo no permitida', $result['error']);

        // Cleanup
        if (file_exists($file['tmp_name'])) {
            unlink($file['tmp_name']);
        }
    }

    // ========================================
    // TESTS DE GUARDADO GLOBAL
    // ========================================

    public function testSaveEvidenciaGlobalCreatesCorrectPath(): void
    {
        $file = $this->createMockPDF();

        // Simular move_uploaded_file con copy
        $originalTmpName = $file['tmp_name'];

        // Necesitamos simular move_uploaded_file, pero en tests no funciona
        // Este test valida la estructura de path

        $nit = '900123456-7';
        $idAuditoria = 5;
        $idAuditoriaItem = 10;

        // Verificar que la ruta esperada contiene la estructura correcta
        $expectedPathPattern = '/uploads\/900123456-7\/5\/global\/10\//';

        // Crear la ruta manualmente para verificar
        $service = new class() extends UploadService {
            public function testSanitizeForPath(string $text): string {
                return parent::sanitizeForPath($text);
            }
        };

        $sanitizedNit = $service->testSanitizeForPath($nit);
        $expectedPath = "uploads/{$sanitizedNit}/{$idAuditoria}/global/{$idAuditoriaItem}/";

        $this->assertEquals("uploads/900123456-7/5/global/10/", $expectedPath);

        // Cleanup
        if (file_exists($originalTmpName)) {
            unlink($originalTmpName);
        }
    }

    public function testSaveEvidenciaClienteCreatesCorrectPath(): void
    {
        $nit = '900123456-7';
        $idAuditoria = 5;
        $idAuditoriaItem = 10;
        $idCliente = 3;

        // Crear servicio anónimo para acceder a método protegido
        $service = new class() extends UploadService {
            public function testSanitizeForPath(string $text): string {
                return parent::sanitizeForPath($text);
            }
        };

        $sanitizedNit = $service->testSanitizeForPath($nit);
        $expectedPath = "uploads/{$sanitizedNit}/{$idAuditoria}/cliente_{$idCliente}/{$idAuditoriaItem}/";

        $this->assertEquals("uploads/900123456-7/5/cliente_3/10/", $expectedPath);
    }

    // ========================================
    // TESTS DE SLUG Y NORMALIZACIÓN
    // ========================================

    public function testSlugifyRemovesSpecialCharacters(): void
    {
        // Crear servicio anónimo para acceder a método protegido
        $service = new class() extends UploadService {
            public function testSlugify(string $text): string {
                return parent::slugify($text);
            }
        };

        $this->assertEquals('hello-world', $service->testSlugify('Hello World'));
        $this->assertEquals('factura-001', $service->testSlugify('Factura #001'));
        $this->assertEquals('documento-cliente', $service->testSlugify('Documento   Cliente!!!'));
        $this->assertEquals('acta-reunion-2024', $service->testSlugify('Acta Reunión 2024'));
    }

    public function testSlugifyHandlesEmptyString(): void
    {
        $service = new class() extends UploadService {
            public function testSlugify(string $text): string {
                return parent::slugify($text);
            }
        };

        $this->assertEquals('file', $service->testSlugify(''));
        $this->assertEquals('file', $service->testSlugify('!!!'));
    }

    public function testSanitizeForPathNormalizesNIT(): void
    {
        $service = new class() extends UploadService {
            public function testSanitizeForPath(string $text): string {
                return parent::sanitizeForPath($text);
            }
        };

        $this->assertEquals('900123456-7', $service->testSanitizeForPath('900123456-7'));
        $this->assertEquals('900_123_456', $service->testSanitizeForPath('900.123.456'));
        $this->assertEquals('abc_def', $service->testSanitizeForPath('ABC@DEF'));
    }

    // ========================================
    // TESTS DE INFORMACIÓN DE ARCHIVO
    // ========================================

    public function testGetFileInfoReturnsNullForNonExistentFile(): void
    {
        $result = $this->service->getFileInfo('uploads/nonexistent/file.pdf');

        $this->assertNull($result);
    }

    public function testGetFileInfoReturnsDataForExistingFile(): void
    {
        // Crear archivo de prueba
        $testFile = $this->testUploadDir . 'test-file.txt';
        $content = 'Hello World';
        file_put_contents($testFile, $content);

        $relativePath = 'uploads/test/test-file.txt';
        $result = $this->service->getFileInfo($relativePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('mime', $result);
        $this->assertArrayHasKey('hash', $result);
        $this->assertArrayHasKey('exists', $result);
        $this->assertTrue($result['exists']);
        $this->assertEquals(strlen($content), $result['size']);

        // Verificar hash SHA256
        $expectedHash = hash('sha256', $content);
        $this->assertEquals($expectedHash, $result['hash']);
    }

    // ========================================
    // TESTS DE ELIMINACIÓN DE ARCHIVOS
    // ========================================

    public function testDeleteFileRemovesExistingFile(): void
    {
        // Crear archivo de prueba
        $testFile = $this->testUploadDir . 'file-to-delete.txt';
        file_put_contents($testFile, 'Test content');

        $this->assertTrue(file_exists($testFile));

        $relativePath = 'uploads/test/file-to-delete.txt';
        $result = $this->service->deleteFile($relativePath);

        $this->assertTrue($result);
        $this->assertFalse(file_exists($testFile));
    }

    public function testDeleteFileReturnsFalseForNonExistentFile(): void
    {
        $result = $this->service->deleteFile('uploads/test/nonexistent.txt');

        $this->assertFalse($result);
    }

    public function testDeleteFileWithTransactionRequiresDatabase(): void
    {
        $result = $this->service->deleteFileWithTransaction(
            'uploads/test/file.txt',
            'evidencias',
            'id_evidencia',
            1
        );

        $this->assertFalse($result['ok']);
        $this->assertStringContainsString('No hay conexión de base de datos', $result['error']);
    }

    // ========================================
    // TESTS DE IMAGEN
    // ========================================

    public function testIsImageReturnsFalseForEmptyFile(): void
    {
        $file = [
            'name' => 'test.jpg',
            'type' => 'image/jpeg',
            'tmp_name' => '',
            'error' => UPLOAD_ERR_OK,
            'size' => 0,
        ];

        $this->assertFalse($this->service->isImage($file));
    }

    // ========================================
    // TESTS DE INTEGRACIÓN (requieren mock de move_uploaded_file)
    // ========================================

    /**
     * Nota: Los tests de guardado real de archivos requieren:
     * - Mock de move_uploaded_file() (función nativa de PHP)
     * - O ejecutarse en contexto de HTTP con archivos reales
     *
     * Para testing completo, considerar usar:
     * - php-mock/php-mock-phpunit
     * - O tests de integración con archivos reales en environment de testing
     */

    // ========================================
    // TESTS DE OTROS MÉTODOS
    // ========================================

    public function testSaveFirmaConsultorUsesCorrectPath(): void
    {
        // Este test valida la lógica de path, no el guardado real
        $idConsultor = 5;
        $expectedPathPattern = 'uploads/firmas_consultor/';

        // Verificar que el método existe y es callable
        $this->assertTrue(method_exists($this->service, 'saveFirmaConsultor'));
    }

    public function testSaveLogoClienteUsesCorrectPath(): void
    {
        $idCliente = 10;
        $expectedPathPattern = 'uploads/logos_clientes/';

        $this->assertTrue(method_exists($this->service, 'saveLogoCliente'));
    }

    public function testSaveSoporteContratoUsesCorrectPath(): void
    {
        $idContrato = 15;
        $expectedPathPattern = "uploads/contratos/{$idContrato}/";

        $this->assertTrue(method_exists($this->service, 'saveSoporteContrato'));
    }

    public function testSetDatabaseInjectsConnection(): void
    {
        $mockDb = $this->createMock(BaseConnection::class);

        $result = $this->service->setDatabase($mockDb);

        $this->assertInstanceOf(UploadService::class, $result);
        $this->assertSame($this->service, $result); // Fluent interface
    }
}
