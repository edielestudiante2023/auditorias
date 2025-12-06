<?php

namespace App\Services;

use SendGrid;
use SendGrid\Mail\Mail;
use App\Models\NotificacionModel;

/**
 * Servicio de Email usando Twilio SendGrid
 */
class EmailService
{
    protected string $apiKey;
    protected string $fromEmail;
    protected string $fromName;
    protected NotificacionModel $notificacionModel;

    public function __construct()
    {
        // Intentar leer de .env primero, luego de configuración
        $this->apiKey = getenv('sendgrid.apiKey') ?:
                       $_ENV['sendgrid.apiKey'] ??
                       config('Email')->sendgridApiKey ??
                       '';

        $this->fromEmail = getenv('email.fromEmail') ?:
                          $_ENV['email.fromEmail'] ??
                          'notificacion.cycloidtalent@cycloidtalent.com';

        $this->fromName = getenv('email.fromName') ?:
                         $_ENV['email.fromName'] ??
                         'Cycloid Talent - Auditorías';

        $this->notificacionModel = new NotificacionModel();
    }

    /**
     * Envía invitación a proveedor para completar auditoría
     *
     * @param string $to Email del destinatario
     * @param string $usuario Usuario para login
     * @param string $clave Contraseña temporal
     * @param string $urlLogin URL de login
     * @param string|null $urlAuditoria URL directa a la auditoría (opcional)
     * @param string|null $nombreDestinatario Nombre del destinatario
     * @param array $items Ítems seleccionados por el consultor
     * @param array $clientes Clientes asignados a la auditoría
     * @param array|null $consultorData Datos del consultor (nombre_completo, email, telefono)
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function sendInviteProveedor(
        string $to,
        string $usuario,
        string $clave,
        string $urlLogin,
        ?string $urlAuditoria = null,
        ?string $nombreDestinatario = null,
        array $items = [],
        array $clientes = [],
        ?array $consultorData = null
    ): array {
        $tipo = 'invitacion_proveedor';
        $asunto = 'Invitación a completar auditoría - Cycloid Talent';
        $payload = [
            'usuario' => $usuario,
            'urlLogin' => $urlLogin,
            'urlAuditoria' => $urlAuditoria,
            'items_count' => count($items),
            'clientes_count' => count($clientes)
        ];

        try {
            // Extraer datos del consultor para el template
            $consultorNombre = $consultorData['nombre_completo'] ?? null;
            $consultorEmail = $consultorData['email'] ?? null;
            $consultorTelefono = $consultorData['telefono'] ?? null;

            log_message('info', 'EmailService - Datos consultor para template: Nombre=' . ($consultorNombre ?? 'NULL') .
                               ', Email=' . ($consultorEmail ?? 'NULL') .
                               ', Telefono=' . ($consultorTelefono ?? 'NULL'));

            // Cargar plantilla HTML
            $htmlContent = view('emails/invitacion_proveedor', [
                'usuario' => $usuario,
                'clave' => $clave,
                'urlLogin' => $urlLogin,
                'urlAuditoria' => $urlAuditoria,
                'nombreProveedor' => $nombreDestinatario ?? 'Usuario',
                'items' => $items,
                'clientes' => $clientes,
                'consultorNombre' => $consultorNombre,
                'consultorEmail' => $consultorEmail,
                'consultorTelefono' => $consultorTelefono,
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($to, $asunto, $htmlContent);
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'log_only', 'Sin API key, guardado en logs');

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setReplyTo($this->fromEmail, $this->fromName); // Agregar Reply-To
            $email->setSubject($asunto);
            $email->addTo($to);
            $email->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email enviado exitosamente a {$to}");
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'enviado');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email: ' . $e->getMessage());
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía notificación de clientes adicionales a auditoría existente
     *
     * @param string $to Email del destinatario
     * @param string $usuario Usuario para login
     * @param string $clave Contraseña temporal (nueva)
     * @param string $urlLogin URL de login
     * @param string $urlAuditoria URL directa a la auditoría
     * @param string $nombreDestinatario Nombre del destinatario
     * @param array $clientesNuevos Lista de nuevos clientes agregados
     * @param array $itemsPorCliente Items que debe completar por cliente
     * @param array|null $consultorData Datos del consultor (nombre_completo, email, telefono)
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function sendAdicionClientesProveedor(
        string $to,
        string $usuario,
        string $clave,
        string $urlLogin,
        string $urlAuditoria,
        string $nombreDestinatario,
        array $clientesNuevos = [],
        array $itemsPorCliente = [],
        ?array $consultorData = null
    ): array {
        $tipo = 'adicion_clientes_proveedor';
        $asunto = 'Nuevos clientes agregados a auditoría - Cycloid Talent';
        $payload = [
            'usuario' => $usuario,
            'urlLogin' => $urlLogin,
            'urlAuditoria' => $urlAuditoria,
            'clientes_nuevos_count' => count($clientesNuevos),
            'items_por_cliente_count' => count($itemsPorCliente)
        ];

        try {
            // Extraer datos del consultor para el template
            $consultorNombre = $consultorData['nombre_completo'] ?? null;
            $consultorEmail = $consultorData['email'] ?? null;
            $consultorTelefono = $consultorData['telefono'] ?? null;

            // Cargar plantilla HTML específica
            $htmlContent = view('emails/adicion_clientes_proveedor', [
                'usuario' => $usuario,
                'clave' => $clave,
                'urlLogin' => $urlLogin,
                'urlAuditoria' => $urlAuditoria,
                'nombreProveedor' => $nombreDestinatario,
                'clientesNuevos' => $clientesNuevos,
                'itemsPorCliente' => $itemsPorCliente,
                'consultorNombre' => $consultorNombre,
                'consultorEmail' => $consultorEmail,
                'consultorTelefono' => $consultorTelefono,
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($to, $asunto, $htmlContent);
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'log_only', 'Sin API key, guardado en logs');

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setReplyTo($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);
            $email->addTo($to);
            $email->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de adición de clientes enviado exitosamente a {$to}");
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'enviado');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email de adición de clientes: ' . $e->getMessage());
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía notificación general
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $htmlContent
     * @return array
     */
    public function sendGenericEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlContent
    ): array {
        try {
            if (empty($this->apiKey)) {
                throw new \Exception('No se ha configurado la API Key de SendGrid');
            }

            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setReplyTo($this->fromEmail, $this->fromName);
            $email->setSubject($subject);
            $email->addTo($toEmail, $toName);
            $email->addContent('text/html', $htmlContent);

            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return ['ok' => true, 'message' => 'Email enviado', 'error' => null];
            } else {
                throw new \Exception('Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error email genérico: ' . $e->getMessage());
            return ['ok' => false, 'message' => 'Error al enviar', 'error' => $e->getMessage()];
        }
    }

    /**
     * Envía notificación al consultor cuando la auditoría es cerrada
     *
     * @param string $toEmail Email del consultor
     * @param string $consultorNombre Nombre completo del consultor
     * @param int $idAuditoria ID de la auditoría
     * @param string $proveedorNombre Razón social del proveedor
     * @param string $proveedorNit NIT del proveedor
     * @param float $porcentajeGlobal Porcentaje global de cumplimiento
     * @param array $clientes Array con datos de clientes y rutas PDF
     * @param string $rutaPdfGlobal Ruta del PDF global (opcional)
     * @param string $urlAuditoria URL para ver la auditoría
     * @param string $fechaCierre Fecha de cierre formateada
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function sendAuditoriaCerrada(
        string $toEmail,
        string $consultorNombre,
        int $idAuditoria,
        string $proveedorNombre,
        string $proveedorNit,
        float $porcentajeGlobal,
        array $clientes,
        string $rutaPdfGlobal,
        string $urlAuditoria,
        string $fechaCierre,
        ?int $idUser = null
    ): array {
        $tipo = 'auditoria_cerrada';
        $asunto = "Auditoría #{$idAuditoria} cerrada - Informes PDF generados";
        $payload = [
            'id_auditoria' => $idAuditoria,
            'proveedor_nombre' => $proveedorNombre,
            'proveedor_nit' => $proveedorNit,
            'porcentaje_global' => $porcentajeGlobal,
            'num_clientes' => count($clientes)
        ];

        try {
            if (empty($this->apiKey)) {
                throw new \Exception('No se ha configurado la API Key de SendGrid');
            }

            // Cargar plantilla HTML
            $htmlContent = view('emails/auditoria_cerrada_consultor', [
                'consultor_nombre' => $consultorNombre,
                'id_auditoria' => $idAuditoria,
                'proveedor_nombre' => $proveedorNombre,
                'proveedor_nit' => $proveedorNit,
                'porcentaje_global' => $porcentajeGlobal,
                'clientes' => $clientes,
                'ruta_pdf_global' => $rutaPdfGlobal,
                'url_auditoria' => $urlAuditoria,
                'fecha_cierre' => $fechaCierre
            ]);

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);
            $email->addTo($toEmail, $consultorNombre);
            $email->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de auditoría cerrada enviado a {$toEmail}");

                // Registrar notificación exitosa
                $this->registrarNotificacion($idUser, $tipo, $asunto, $htmlContent, $payload, 'enviado');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email de auditoría cerrada: ' . $e->getMessage());

            // Registrar notificación fallida
            $this->registrarNotificacion($idUser, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Notifica al consultor cuando el proveedor finaliza la auditoría
     *
     * @param int $idAuditoria ID de la auditoría
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function notifyConsultorProveedorFinalizo(int $idAuditoria): array
    {
        $db = \Config\Database::connect();

        // Obtener datos de la auditoría
        $auditoria = $db->table('auditorias a')
            ->select('a.*, p.razon_social as proveedor_nombre, p.nit as proveedor_nit,
                      cons.nombre_completo as consultor_nombre,
                      u.email as consultor_email, u.id_users as id_user_consultor')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->join('users u', 'u.id_users = cons.id_users')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        if (!$auditoria) {
            return [
                'ok' => false,
                'message' => 'Auditoría no encontrada',
                'error' => 'ID inválido'
            ];
        }

        $tipo = 'proveedor_finalizo';
        $asunto = "El proveedor {$auditoria['proveedor_nombre']} completó la auditoría";
        $toEmail = $auditoria['consultor_email'];
        $consultorNombre = $auditoria['consultor_nombre'];
        $payload = [
            'id_auditoria' => $idAuditoria,
            'proveedor_nombre' => $auditoria['proveedor_nombre'],
            'proveedor_nit' => $auditoria['proveedor_nit']
        ];

        try {
            // Cargar plantilla HTML
            $htmlContent = view('emails/proveedor_finalizo', [
                'consultor_nombre' => $consultorNombre,
                'id_auditoria' => $idAuditoria,
                'proveedor_nombre' => $auditoria['proveedor_nombre'],
                'proveedor_nit' => $auditoria['proveedor_nit'],
                'fecha_finalizacion' => date('d/m/Y H:i', strtotime($auditoria['updated_at'])),
                'url_auditoria' => base_url('consultor/auditoria/' . $idAuditoria)
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($toEmail, $asunto, $htmlContent);
                $this->registrarNotificacion(
                    $auditoria['id_user_consultor'],
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'log_only',
                    'Sin API key, guardado en logs'
                );

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);
            $email->addTo($toEmail, $consultorNombre);
            $email->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de proveedor finalizado enviado a {$toEmail}");
                $this->registrarNotificacion(
                    $auditoria['id_user_consultor'],
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'enviado'
                );

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email proveedor finalizo: ' . $e->getMessage());
            $this->registrarNotificacion(
                $auditoria['id_user_consultor'] ?? null,
                $tipo,
                $asunto,
                '',
                $payload,
                'fallido',
                $e->getMessage()
            );

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Notifica al consultor cuando se cierra una auditoría
     *
     * @param int $idAuditoria ID de la auditoría
     * @param array $pathsPdfsPorCliente Array con paths de PDFs por cliente ['id_cliente' => path]
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function notifyCierreAuditoria(int $idAuditoria, array $pathsPdfsPorCliente): array
    {
        $db = \Config\Database::connect();

        // Obtener datos de la auditoría con consultor
        $auditoria = $db->table('auditorias a')
            ->select('a.*, cons.nombre_completo as consultor_nombre, u.email as consultor_email,
                      u.id_users as id_user_consultor, p.razon_social as proveedor_nombre, p.nit as proveedor_nit')
            ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
            ->join('users u', 'u.id_users = cons.id_users')
            ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
            ->where('a.id_auditoria', $idAuditoria)
            ->get()
            ->getRowArray();

        if (!$auditoria) {
            return [
                'ok' => false,
                'message' => 'Auditoría no encontrada',
                'error' => 'ID inválido'
            ];
        }

        // Obtener clientes y sus PDFs
        $clientes = [];
        foreach ($pathsPdfsPorCliente as $idCliente => $pdfPath) {
            $cliente = $db->table('clientes')->where('id_cliente', $idCliente)->get()->getRowArray();
            if ($cliente) {
                $clientes[] = [
                    'razon_social' => $cliente['razon_social'],
                    'pdf_path' => $pdfPath
                ];
            }
        }

        $tipo = 'auditoria_cerrada';
        $asunto = "Auditoría #{$idAuditoria} cerrada - Informes generados";
        $toEmail = $auditoria['consultor_email'];
        $consultorNombre = $auditoria['consultor_nombre'];

        $payload = [
            'id_auditoria' => $idAuditoria,
            'proveedor_nombre' => $auditoria['proveedor_nombre'],
            'num_clientes' => count($clientes),
            'paths_pdfs' => $pathsPdfsPorCliente
        ];

        try {
            // Cargar plantilla HTML
            $htmlContent = view('emails/auditoria_cerrada_consultor', [
                'consultor_nombre' => $consultorNombre,
                'id_auditoria' => $idAuditoria,
                'proveedor_nombre' => $auditoria['proveedor_nombre'],
                'proveedor_nit' => $auditoria['proveedor_nit'],
                'clientes' => $clientes,
                'fecha_cierre' => date('d/m/Y H:i'),
                'url_auditoria' => base_url('consultor/auditoria/' . $idAuditoria)
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($toEmail, $asunto, $htmlContent);
                $this->registrarNotificacion(
                    $auditoria['id_user_consultor'],
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'log_only',
                    'Sin API key, guardado en logs'
                );

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);
            $email->addTo($toEmail, $consultorNombre);
            $email->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Email de cierre de auditoría enviado a {$toEmail}");
                $this->registrarNotificacion(
                    $auditoria['id_user_consultor'],
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'enviado'
                );

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', 'Error al enviar email de cierre: ' . $e->getMessage());
            $this->registrarNotificacion(
                $auditoria['id_user_consultor'] ?? null,
                $tipo,
                $asunto,
                '',
                $payload,
                'fallido',
                $e->getMessage()
            );

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Guarda el contenido del email en archivo de log
     *
     * @param string $to Email destinatario
     * @param string $subject Asunto
     * @param string $htmlContent Contenido HTML
     */
    private function guardarEmailEnLog(string $to, string $subject, string $htmlContent): void
    {
        try {
            $logDir = WRITEPATH . 'email_logs';

            // Crear directorio si no existe
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Nombre de archivo con timestamp
            $filename = date('Y-m-d_His') . '_' . md5($to . $subject) . '.html';
            $filepath = $logDir . DIRECTORY_SEPARATOR . $filename;

            // Crear contenido del log
            $logContent = "<!-- \n";
            $logContent .= "Destinatario: {$to}\n";
            $logContent .= "Asunto: {$subject}\n";
            $logContent .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
            $logContent .= "-->\n\n";
            $logContent .= $htmlContent;

            // Guardar archivo
            file_put_contents($filepath, $logContent);

            log_message('info', "Email guardado en log: {$filename}");

        } catch (\Exception $e) {
            log_message('error', 'Error al guardar email en log: ' . $e->getMessage());
        }
    }

    /**
     * Envía invitación a proveedor para completar auditoría
     * Nueva versión con datos completos de auditoría
     *
     * @param string $email Email del proveedor
     * @param array $auditoria Datos completos de la auditoría
     * @param array $proveedor Datos del proveedor
     * @param string $linkAcceso URL directa al wizard de la auditoría
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function enviarInvitacionProveedor(
        string $email,
        array $auditoria,
        array $proveedor,
        string $linkAcceso
    ): array {
        $tipo = 'invitacion_proveedor_auditoria';
        $asunto = "Auditoría asignada | {$proveedor['razon_social']}";
        $payload = [
            'id_auditoria' => $auditoria['id_auditoria'] ?? null,
            'proveedor_nombre' => $proveedor['razon_social'] ?? '',
            'proveedor_nit' => $proveedor['nit'] ?? '',
            'link_acceso' => $linkAcceso
        ];

        try {
            // Cargar plantilla HTML
            $htmlContent = view('emails/invitacion_proveedor_auditoria', [
                'proveedor' => $proveedor,
                'auditoria' => $auditoria,
                'linkAcceso' => $linkAcceso,
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($email, $asunto, $htmlContent);
                $this->registrarNotificacion(
                    null,
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'log_only',
                    'Sin API key, guardado en logs'
                );

                log_message('info', "Email invitación proveedor guardado en logs: {$email}");

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email_obj = new Mail();
            $email_obj->setFrom($this->fromEmail, $this->fromName);
            $email_obj->setSubject($asunto);
            $email_obj->addTo($email);
            $email_obj->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email_obj);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Invitación proveedor enviada exitosamente a {$email}");
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'enviado');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', "Error al enviar invitación proveedor: {$e->getMessage()}");
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía notificación a consultor cuando proveedor cierra auditoría
     *
     * @param array $correosConsultor Array de emails del consultor (puede ser múltiples)
     * @param array $auditoria Datos de la auditoría
     * @param array $resumen Resumen de progreso y clientes
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function enviarCierreProveedor(
        array $correosConsultor,
        array $auditoria,
        array $resumen
    ): array {
        $tipo = 'proveedor_cierre_auditoria';
        $proveedorNombre = $auditoria['proveedor_nombre'] ?? 'Proveedor';
        $asunto = "Auditoría finalizada | {$proveedorNombre}";

        $payload = [
            'id_auditoria' => $auditoria['id_auditoria'] ?? null,
            'proveedor_nombre' => $proveedorNombre,
            'porcentaje_total' => $resumen['porcentaje_total'] ?? 0,
            'num_clientes' => count($resumen['clientes'] ?? [])
        ];

        try {
            // Cargar plantilla HTML
            $htmlContent = view('emails/proveedor_finalizo_auditoria', [
                'auditoria' => $auditoria,
                'resumen' => $resumen,
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                foreach ($correosConsultor as $email) {
                    $this->guardarEmailEnLog($email, $asunto, $htmlContent);
                }

                $this->registrarNotificacion(
                    null,
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'log_only',
                    'Sin API key, guardado en logs'
                );

                log_message('info', "Email cierre proveedor guardado en logs para " . count($correosConsultor) . " destinatario(s)");

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Enviar a cada consultor
            $sendgrid = new SendGrid($this->apiKey);
            $enviadosExitosamente = 0;
            $errores = [];

            foreach ($correosConsultor as $email) {
                try {
                    $email_obj = new Mail();
                    $email_obj->setFrom($this->fromEmail, $this->fromName);
                    $email_obj->setSubject($asunto);
                    $email_obj->addTo($email);
                    $email_obj->addContent('text/html', $htmlContent);

                    $response = $sendgrid->send($email_obj);

                    if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                        $enviadosExitosamente++;
                        log_message('info', "Email cierre proveedor enviado a {$email}");
                    } else {
                        $errores[] = "Error al enviar a {$email}: Status " . $response->statusCode();
                        log_message('error', "Error al enviar email cierre a {$email}: Status " . $response->statusCode());
                    }
                } catch (\Exception $e) {
                    $errores[] = "Error al enviar a {$email}: " . $e->getMessage();
                    log_message('error', "Excepción al enviar email cierre a {$email}: " . $e->getMessage());
                }
            }

            // Registrar notificación
            if ($enviadosExitosamente > 0) {
                $this->registrarNotificacion(
                    null,
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    $enviadosExitosamente === count($correosConsultor) ? 'enviado' : 'parcial'
                );
            } else {
                $this->registrarNotificacion(
                    null,
                    $tipo,
                    $asunto,
                    '',
                    $payload,
                    'fallido',
                    implode('; ', $errores)
                );
            }

            if ($enviadosExitosamente === count($correosConsultor)) {
                return [
                    'ok' => true,
                    'message' => "Email enviado exitosamente a {$enviadosExitosamente} destinatario(s)",
                    'error' => null,
                ];
            } elseif ($enviadosExitosamente > 0) {
                return [
                    'ok' => true,
                    'message' => "Email enviado parcialmente a {$enviadosExitosamente} de " . count($correosConsultor) . " destinatario(s)",
                    'error' => implode('; ', $errores),
                ];
            } else {
                throw new \Exception('No se pudo enviar a ningún destinatario: ' . implode('; ', $errores));
            }

        } catch (\Exception $e) {
            log_message('error', "Error al enviar email cierre proveedor: {$e->getMessage()}");
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Envía credenciales a un nuevo usuario
     *
     * @param string $email Email del usuario
     * @param string $nombreUsuario Nombre del usuario
     * @param string $nombreRol Nombre del rol (Admin, Consultor, Proveedor)
     * @param string $passwordTemporal Contraseña temporal generada
     * @param string $urlLogin URL para hacer login
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function enviarCredencialesNuevoUsuario(
        string $email,
        string $nombreUsuario,
        string $nombreRol,
        string $passwordTemporal,
        string $urlLogin
    ): array {
        $tipo = 'nuevo_usuario_credenciales';
        $asunto = 'Bienvenido al Sistema de Auditorías - Tus credenciales de acceso';
        $payload = [
            'email' => $email,
            'nombre_usuario' => $nombreUsuario,
            'rol' => $nombreRol
        ];

        try {
            // Cargar plantilla HTML
            $htmlContent = view('emails/nuevo_usuario_credenciales', [
                'nombreUsuario' => $nombreUsuario,
                'email' => $email,
                'nombreRol' => $nombreRol,
                'passwordTemporal' => $passwordTemporal,
                'urlLogin' => $urlLogin,
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($email, $asunto, $htmlContent);
                $this->registrarNotificacion(
                    null,
                    $tipo,
                    $asunto,
                    $htmlContent,
                    $payload,
                    'log_only',
                    'Sin API key, guardado en logs'
                );

                log_message('info', "Email de credenciales guardado en logs: {$email}");

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email_obj = new Mail();
            $email_obj->setFrom($this->fromEmail, $this->fromName);
            $email_obj->setSubject($asunto);
            $email_obj->addTo($email, $nombreUsuario);
            $email_obj->addContent('text/html', $htmlContent);

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email_obj);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Credenciales enviadas exitosamente a {$email}");
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'enviado');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null,
                ];
            } else {
                throw new \Exception('Error al enviar email. Status: ' . $response->statusCode());
            }

        } catch (\Exception $e) {
            log_message('error', "Error al enviar credenciales a {$email}: {$e->getMessage()}");
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'fallido', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'No se pudo enviar el email',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Registra una notificación en la base de datos
     *
     * @param int|null $idUser ID del usuario destinatario
     * @param string $tipo Tipo de notificación
     * @param string $asunto Asunto del email
     * @param string $mensaje Contenido del mensaje
     * @param array $payload Datos adicionales en JSON
     * @param string $estadoEnvio 'enviado', 'fallido', o 'log_only'
     * @param string|null $detalleError Detalle del error si falla
     */
    private function registrarNotificacion(
        ?int $idUser,
        string $tipo,
        string $asunto,
        string $mensaje,
        array $payload,
        string $estadoEnvio,
        ?string $detalleError = null
    ): void {
        try {
            // Estructura real de la tabla notificaciones:
            // - id_notificacion, id_auditoria, tipo, payload_json, fecha_envio, estado_envio, detalle_error
            // Si hay id_auditoria en el payload, extraerlo
            $idAuditoria = $payload['id_auditoria'] ?? null;

            $data = [
                'tipo' => $tipo,
                'payload_json' => json_encode($payload),
                'fecha_envio' => date('Y-m-d H:i:s'),
                'estado_envio' => $estadoEnvio,
                'detalle_error' => $detalleError
            ];

            // Solo agregar id_auditoria si no es null
            if ($idAuditoria !== null) {
                $data['id_auditoria'] = $idAuditoria;
            }

            $this->notificacionModel->insert($data);
        } catch (\Exception $e) {
            log_message('error', 'Error al registrar notificación: ' . $e->getMessage());
        }
    }

    /**
     * Enviar PDF de auditoría a un cliente por email
     *
     * @param int $idAuditoria
     * @param int $idCliente
     * @param string $emailCliente
     * @param string $nombreCliente
     * @param string $rutaPdf Ruta completa al archivo PDF
     * @return array ['ok' => bool, 'message' => string, 'error' => string|null]
     */
    public function enviarPdfCliente(int $idAuditoria, int $idCliente, string $emailCliente, string $nombreCliente, string $rutaPdf): array
    {
        $tipo = 'pdf_cliente';

        // Obtener el nombre del archivo PDF sin extensión para usarlo como asunto
        $nombreArchivoPdf = pathinfo($rutaPdf, PATHINFO_FILENAME);
        $asunto = $nombreArchivoPdf;

        $payload = [
            'id_auditoria' => $idAuditoria,
            'id_cliente' => $idCliente,
            'nombre_cliente' => $nombreCliente
        ];

        try {
            // Obtener email del consultor, datos del proveedor y cliente de la auditoría
            $db = \Config\Database::connect();
            $auditoria = $db->table('auditorias a')
                ->select('cons.nombre_completo as consultor_nombre, u.email as consultor_email, p.razon_social as proveedor_nombre')
                ->join('consultores cons', 'cons.id_consultor = a.id_consultor')
                ->join('users u', 'u.id_users = cons.id_users')
                ->join('proveedores p', 'p.id_proveedor = a.id_proveedor')
                ->where('a.id_auditoria', $idAuditoria)
                ->get()
                ->getRowArray();

            $emailConsultor = $auditoria['consultor_email'] ?? null;
            $nombreConsultor = $auditoria['consultor_nombre'] ?? 'Consultor';
            $nombreProveedor = $auditoria['proveedor_nombre'] ?? 'Proveedor';

            // Obtener email del cliente
            $cliente = $db->table('clientes')
                ->select('email_contacto, razon_social')
                ->where('id_cliente', $idCliente)
                ->get()
                ->getRowArray();

            $emailClienteContacto = $cliente['email_contacto'] ?? null;

            // Cargar plantilla HTML
            $htmlContent = view('emails/pdf_cliente', [
                'nombre_cliente' => $nombreCliente,
                'nombre_proveedor' => $nombreProveedor,
                'nombre_consultor' => $nombreConsultor,
                'id_auditoria' => $idAuditoria,
                'fecha_envio' => date('d/m/Y H:i')
            ]);

            // Modo log-only si no hay API key
            if (empty($this->apiKey)) {
                $this->guardarEmailEnLog($emailCliente, $asunto, $htmlContent);

                return [
                    'ok' => true,
                    'message' => 'Email guardado en logs (modo log-only)',
                    'error' => null,
                ];
            }

            // Crear email con SendGrid
            $email = new Mail();
            $email->setFrom($this->fromEmail, $this->fromName);
            $email->setSubject($asunto);

            // Destinatario principal: Proveedor (usuario responsable)
            $email->addTo($emailCliente, $nombreCliente);

            // Array para rastrear emails ya agregados y evitar duplicados
            $emailsAgregados = [strtolower($emailCliente)];

            // Copia: Cliente (si tiene email y no está duplicado)
            if ($emailClienteContacto && !in_array(strtolower($emailClienteContacto), $emailsAgregados)) {
                $email->addCc($emailClienteContacto, $cliente['razon_social']);
                $emailsAgregados[] = strtolower($emailClienteContacto);
            }

            // Copia: Consultor (si no está duplicado)
            if ($emailConsultor && !in_array(strtolower($emailConsultor), $emailsAgregados)) {
                $email->addCc($emailConsultor, $nombreConsultor);
                $emailsAgregados[] = strtolower($emailConsultor);
            }

            // Copia: Head consultant (si no está duplicado)
            $headConsultantEmail = 'head.consultant.cycloidtalent@gmail.com';
            if (!in_array(strtolower($headConsultantEmail), $emailsAgregados)) {
                $email->addCc($headConsultantEmail, 'Head Consultant');
            }

            $email->addContent('text/html', $htmlContent);

            // Adjuntar PDF
            if (file_exists($rutaPdf)) {
                $fileContent = base64_encode(file_get_contents($rutaPdf));
                $filename = basename($rutaPdf);

                $email->addAttachment(
                    $fileContent,
                    'application/pdf',
                    $filename,
                    'attachment'
                );
            }

            // Enviar email
            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            // Verificar respuesta
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "PDF enviado por email a {$emailCliente} - Auditoría {$idAuditoria}");

                // Registrar envío exitoso (estado_envio ENUM: 'ok', 'error', 'pendiente')
                $payload['email_destinatario'] = $emailCliente;
                $this->registrarNotificacion(null, $tipo, $asunto, $htmlContent, $payload, 'ok');

                return [
                    'ok' => true,
                    'message' => 'Email enviado exitosamente',
                    'error' => null
                ];
            } else {
                $errorBody = $response->body();
                log_message('error', "Error al enviar PDF por email: {$errorBody}");

                // Registrar fallo (estado_envio ENUM: 'ok', 'error', 'pendiente')
                $payload['email_destinatario'] = $emailCliente;
                $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'error', $errorBody);

                return [
                    'ok' => false,
                    'message' => 'Error al enviar email',
                    'error' => $errorBody
                ];
            }
        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar PDF por email: ' . $e->getMessage());

            // Registrar excepción (estado_envio ENUM: 'ok', 'error', 'pendiente')
            $payload['email_destinatario'] = $emailCliente;
            $this->registrarNotificacion(null, $tipo, $asunto, '', $payload, 'error', $e->getMessage());

            return [
                'ok' => false,
                'message' => 'Excepción al enviar email',
                'error' => $e->getMessage()
            ];
        }
    }
}
