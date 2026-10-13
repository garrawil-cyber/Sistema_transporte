<?php
class GirosController extends Controller {
    private $giroModel;
    private $clienteModel;

    public function __construct() {
        $this->requireAuth();
        $this->giroModel = new Giro();
        $this->clienteModel = new Cliente();
    }

    // ============================================================
    // LISTAR GIROS CON PAGINACIÓN + FILTROS + KPIs
    // ============================================================
    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar'       => $_GET['buscar'] ?? '',
            'estado'       => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin'    => $_GET['fecha_fin'] ?? ''
        ];
        
        $resultado = $this->giroModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );

        // ✅ KPIs filtrados
        $stats = $this->giroModel->getStatsFiltrados($filtros);

        // ✅ Gráfico últimos 7 días
        $chartData = $this->giroModel->getStatsUltimos7Dias();
        
        $this->view('giros/index', [
            'giros'       => $resultado['datos'],
            'paginator'   => $resultado['paginator'],
            'filtros'     => $filtros,
            'stats'       => $stats,
            'chartData'   => $chartData,
            'titulo'      => 'Gestión de Giros'
        ]);
    }

    public function create() {
        $errores = [];
        $remitente = null;
        $destinatario = null;
        $comision_porcentaje = $this->giroModel->getComisionPorcentaje();
        $monto_calculado = 0;
        $comision_calculada = 0;

        // Búsqueda de clientes
        if (isset($_GET['buscar_remitente'])) {
            $dni = trim($_GET['buscar_remitente']);
            $remitente = $this->clienteModel->findByDocumento($dni);
            if (!$remitente) $errores[] = 'Remitente no encontrado.';
        }
        if (isset($_GET['buscar_destinatario'])) {
            $dni = trim($_GET['buscar_destinatario']);
            $destinatario = $this->clienteModel->findByDocumento($dni);
            if (!$destinatario) $errores[] = 'Destinatario no encontrado.';
        }

        // Cálculo de comisión en tiempo real
        if (isset($_GET['monto']) && is_numeric($_GET['monto'])) {
            $monto_calculado = (float)$_GET['monto'];
            $comision_calculada = $monto_calculado * ($comision_porcentaje / 100);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar') {
            $remitente_data = [
                'tipo_documento' => $_POST['remitente_tipo_doc'] ?? 'DNI',
                'numero_documento' => trim($_POST['remitente_dni']),
                'nombre' => trim($_POST['remitente_nombre']),
                'apellido' => trim($_POST['remitente_apellido']),
                'telefono' => trim($_POST['remitente_telefono']),
                'email' => trim($_POST['remitente_email'])
            ];
            $destinatario_data = [
                'tipo_documento' => $_POST['destinatario_tipo_doc'] ?? 'DNI',
                'numero_documento' => trim($_POST['destinatario_dni']),
                'nombre' => trim($_POST['destinatario_nombre']),
                'apellido' => trim($_POST['destinatario_apellido']),
                'telefono' => trim($_POST['destinatario_telefono']),
                'email' => trim($_POST['destinatario_email'])
            ];

            $monto = (float)$_POST['monto'];
            $comision = $monto * ($comision_porcentaje / 100);

            // ✅ NUEVOS CAMPOS
            $lugar_origen  = trim($_POST['lugar_origen'] ?? '');
            $lugar_destino = trim($_POST['lugar_destino'] ?? '');
            $concepto      = trim($_POST['concepto'] ?? '');

            if ($monto <= 0) $errores[] = 'El monto debe ser mayor a 0.';
            if (empty($remitente_data['numero_documento']) || empty($remitente_data['nombre'])) $errores[] = 'Datos del remitente incompletos.';
            if (empty($destinatario_data['numero_documento']) || empty($destinatario_data['nombre'])) $errores[] = 'Datos del destinatario incompletos.';
            
            // ✅ Validación de lugar destino
            if (empty($lugar_destino)) $errores[] = 'Debe indicar el lugar de destino del giro.';

            if (empty($errores)) {
                $remitente_id = $this->clienteModel->findOrCreate($remitente_data);
                $destinatario_id = $this->clienteModel->findOrCreate($destinatario_data);

                $codigo = $this->giroModel->generarCodigo();
                $giro_data = [
                    'codigo' => $codigo,
                    'remitente_id' => $remitente_id,
                    'destinatario_id' => $destinatario_id,
                    'lugar_origen' => $lugar_origen,
                    'lugar_destino' => $lugar_destino,
                    'concepto' => $concepto,
                    'usuario_id' => $_SESSION['user_id'] ?? 1,
                    'monto' => $monto,
                    'comision' => $comision,
                    'moneda' => $_POST['moneda'] ?? 'PEN',
                    'metodo_pago_origen' => $_POST['metodo_pago_origen'] ?? 'EFECTIVO',
                    'metodo_pago_destino' => $_POST['metodo_pago_destino'] ?? 'EFECTIVO'
                ];

                try {
                    $giro_id = $this->giroModel->registrar($giro_data);

                    if ($giro_id) {
                        $email_destino = $destinatario_data['email'] ?? $remitente_data['email'] ?? null;

                        if (!empty($email_destino) && filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
                            try {
                                $giro = $this->giroModel->getByIdWithDetails($giro_id);
                                $this->enviarComprobanteEmail($giro, $email_destino);
                                $this->setFlash('Giro registrado con éxito. Código: ' . $codigo . ' (enviado a ' . $email_destino . ')', 'success');
                            } catch (Exception $e) {
                                Logger::error('Error al enviar comprobante automático: ' . $e->getMessage());
                                $this->setFlash('Giro registrado. Código: ' . $codigo, 'info');
                            }
                        } else {
                            $this->setFlash('Giro registrado con éxito. Código: ' . $codigo, 'success');
                        }

                        $this->redirect('giros/show/' . $giro_id);
                        return;
                    } else {
                        $errores[] = 'Error al registrar el giro en la base de datos';
                    }

                } catch (Exception $e) {
                    $errores[] = 'Error: ' . $e->getMessage();
                }
            }
        }

        // ✅ KPIs del día
        $stats = $this->giroModel->getStatsHoy();

        $this->view('giros/create', [
            'titulo'               => 'Nuevo Giro',
            'remitente'            => $remitente,
            'destinatario'         => $destinatario,
            'comision_porcentaje'  => $comision_porcentaje,
            'monto_calculado'      => $monto_calculado,
            'comision_calculada'   => $comision_calculada,
            'stats'                => $stats,           // ✅ NUEVO
            'errores'              => $errores
        ]);
    }

    public function show($id) {
        $giro = $this->giroModel->getByIdWithDetails($id);
        if (!$giro) {
            $this->setFlash('Giro no encontrado', 'danger');
            $this->redirect('giros');
        }
        $this->view('giros/show', ['giro' => $giro, 'titulo' => 'Detalle de Giro']);
    }

    public function pagar($id) {
        if ($this->giroModel->pagar($id)) {
            $this->setFlash('Giro marcado como PAGADO', 'success');
        } else {
            $this->setFlash('Error al pagar o ya está pagado/anulado', 'danger');
        }
        $this->redirect('giros');
    }

    public function anular($id) {
        if ($this->giroModel->anular($id)) {
            $this->setFlash('Giro anulado correctamente', 'warning');
        } else {
            $this->setFlash('Error al anular o ya está pagado/anulado', 'danger');
        }
        $this->redirect('giros');
    }

    public function enviarEmail($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('giros/show/' . $id);
        }

        $email = trim($_POST['email'] ?? '');
        $mensaje = trim($_POST['mensaje'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('Ingrese un correo electrónico válido', 'danger');
            $this->redirect('giros/show/' . $id);
        }

        $giro = $this->giroModel->getByIdWithDetails($id);
        if (!$giro) {
            $this->setFlash('Giro no encontrado', 'danger');
            $this->redirect('giros');
        }

        try {
            $this->enviarComprobanteEmail($giro, $email, $mensaje);
            $this->setFlash('✅ Comprobante enviado a ' . $email, 'success');
        } catch (Exception $e) {
            Logger::error('Error al enviar comprobante: ' . $e->getMessage());
            $this->setFlash('❌ Error al enviar: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('giros/show/' . $id);
    }

    private function enviarComprobanteEmail($giro, $email_destino, $mensaje_personalizado = null) {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $html = $this->generarHTMLComprobante($giro);
        
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();
        $pdfContent = $dompdf->output();
        
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = MAIL_HOST;
        $mailer->SMTPAuth = true;
        $mailer->Username = MAIL_USERNAME;
        $mailer->Password = MAIL_PASSWORD;
        $mailer->SMTPSecure = MAIL_ENCRYPTION;
        $mailer->Port = MAIL_PORT;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $mailer->addReplyTo(MAIL_REPLY_TO);
        
        $mailer->addAddress($email_destino);
        $mailer->addBCC(MAIL_FROM_EMAIL);
        
        $mailer->Subject = 'Comprobante de Giro ' . $giro['codigo'] . ' - ' . FACTURACION_RAZON_SOCIAL;
        $mailer->isHTML(true);
        $mailer->Body = $this->generarCuerpoEmail($giro, $mensaje_personalizado);
        $mailer->AltBody = strip_tags($this->generarCuerpoEmail($giro, $mensaje_personalizado));
        
        $nombreArchivo = 'Giro_' . $giro['codigo'] . '.pdf';
        $mailer->addStringAttachment($pdfContent, $nombreArchivo, 'base64', 'application/pdf');
        
        $mailer->send();
        
        if (class_exists('Auditoria')) {
            Auditoria::registrar('EXPORT', [
                'tabla_afectada' => 'giros',
                'modulo' => 'giros',
                'registro_id' => $giro['id'],
                'descripcion' => 'Envió comprobante del giro ' . $giro['codigo'] . ' por email a ' . $email_destino
            ]);
        }
    }

    private function generarHTMLComprobante($giro) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 20px; }
                body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; }
                .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 15px; }
                .header h2 { color: #0d6efd; margin: 0 0 5px 0; }
                .header p { margin: 2px 0; font-size: 9pt; color: #666; }
                .codigo { background: #0d6efd; color: white; padding: 10px; text-align: center; font-size: 14pt; font-weight: bold; margin: 15px 0; }
                .section { margin: 15px 0; padding: 10px 0; border-bottom: 1px dotted #ccc; }
                .section-title { font-size: 9pt; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 8px; }
                .info-row { margin: 5px 0; font-size: 10pt; }
                .info-row strong { display: inline-block; min-width: 100px; }
                .total-box { text-align: center; background: #f8f9fa; padding: 15px; border: 2px solid #0d6efd; margin: 15px 0; }
                .total-box h3 { margin: 0; color: #198754; }
                .footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8pt; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>' . FACTURACION_RAZON_SOCIAL . '</h2>
                <p>RUC: ' . FACTURACION_RUC . '</p>
                <p>' . FACTURACION_DIRECCION . '</p>
                <p>Tel: ' . FACTURACION_TELEFONO . '</p>
            </div>

            <div class="codigo">
                GIRO: ' . $giro['codigo'] . '
            </div>

            <div class="section">
                <div class="section-title">Remitente</div>
                <div class="info-row"><strong>Nombre:</strong> ' . htmlspecialchars($giro['remitente_nombre'] . ' ' . $giro['remitente_apellido']) . '</div>
                <div class="info-row"><strong>DNI:</strong> ' . $giro['remitente_dni'] . '</div>
                ' . (!empty($giro['remitente_telefono']) ? '<div class="info-row"><strong>Teléfono:</strong> ' . $giro['remitente_telefono'] . '</div>' : '') . '
            </div>

            <div class="section">
                <div class="section-title">Destinatario</div>
                <div class="info-row"><strong>Nombre:</strong> ' . htmlspecialchars($giro['destinatario_nombre'] . ' ' . $giro['destinatario_apellido']) . '</div>
                <div class="info-row"><strong>DNI:</strong> ' . $giro['destinatario_dni'] . '</div>
                ' . (!empty($giro['destinatario_telefono']) ? '<div class="info-row"><strong>Teléfono:</strong> ' . $giro['destinatario_telefono'] . '</div>' : '') . '
            </div>

            <div class="section">
                <div class="section-title">Ruta del Giro</div>
                <div class="info-row"><strong>Origen:</strong> ' . htmlspecialchars($giro['lugar_origen'] ?: '—') . '</div>
                <div class="info-row"><strong>Destino:</strong> ' . htmlspecialchars($giro['lugar_destino'] ?: '—') . '</div>
                ' . (!empty($giro['concepto']) ? '<div class="info-row"><strong>Concepto:</strong> ' . htmlspecialchars($giro['concepto']) . '</div>' : '') . '
            </div>

            <div class="section">
                <div class="section-title">Detalles</div>
                <div class="info-row"><strong>Moneda:</strong> ' . $giro['moneda'] . '</div>
                <div class="info-row"><strong>Pago Origen:</strong> ' . $giro['metodo_pago_origen'] . '</div>
                <div class="info-row"><strong>Pago Destino:</strong> ' . $giro['metodo_pago_destino'] . '</div>
                <div class="info-row"><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($giro['fecha_registro'])) . '</div>
                <div class="info-row"><strong>Estado:</strong> ' . $giro['estado'] . '</div>
            </div>

            <div class="total-box">
                <div style="font-size: 10pt; color: #666;">Monto Girado: S/ ' . number_format($giro['monto'], 2) . '</div>
                <div style="font-size: 10pt; color: #666;">Comisión: S/ ' . number_format($giro['comision'], 2) . '</div>
                <hr style="margin: 10px 0;">
                <div style="font-size: 12pt;">TOTAL:</div>
                <h3>S/ ' . number_format($giro['monto'] + $giro['comision'], 2) . '</h3>
            </div>

            <div class="footer">
                <p><strong>' . FACTURACION_RAZON_SOCIAL . '</strong></p>
                <p>Comprobante generado el ' . date('d/m/Y H:i:s') . '</p>
                <p>Conserve este documento</p>
            </div>
        </body>
        </html>';
    }

    private function generarCuerpoEmail($giro, $mensaje_personalizado = null) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0 0 10px 0; font-size: 22px; }
                .content { padding: 30px; }
                .codigo-box { background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
                .codigo-box h2 { margin: 0; letter-spacing: 2px; }
                .info-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0d6efd; }
                .info-box table { width: 100%; }
                .info-box td { padding: 6px 0; }
                .info-box td:first-child { font-weight: bold; color: #666; }
                .footer { background: #212529; color: white; padding: 20px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>💰 Comprobante de Giro</h1>
                    <p>' . FACTURACION_RAZON_SOCIAL . '</p>
                </div>

                <div class="content">
                    <p>Estimado(a),</p>
                    <p>' . ($mensaje_personalizado ? nl2br(htmlspecialchars($mensaje_personalizado)) : 'Adjunto encontrará el comprobante del giro realizado.') . '</p>

                    <div class="codigo-box">
                        <div style="font-size: 12px; opacity: 0.8;">CÓDIGO DE GIRO</div>
                        <h2>' . $giro['codigo'] . '</h2>
                    </div>

                    <div class="info-box">
                        <table>
                            <tr>
                                <td>Ruta:</td>
                                <td>' . htmlspecialchars(($giro['lugar_origen'] ?: '—') . ' → ' . ($giro['lugar_destino'] ?: '—')) . '</td>
                            </tr>
                            <tr>
                                <td>Remitente:</td>
                                <td>' . htmlspecialchars($giro['remitente_nombre'] . ' ' . $giro['remitente_apellido']) . '</td>
                            </tr>
                            <tr>
                                <td>Destinatario:</td>
                                <td>' . htmlspecialchars($giro['destinatario_nombre'] . ' ' . $giro['destinatario_apellido']) . '</td>
                            </tr>
                            <tr>
                                <td>Monto:</td>
                                <td><strong>S/ ' . number_format($giro['monto'], 2) . '</strong></td>
                            </tr>
                            <tr>
                                <td>Comisión:</td>
                                <td>S/ ' . number_format($giro['comision'], 2) . '</td>
                            </tr>
                            <tr>
                                <td>Estado:</td>
                                <td>' . $giro['estado'] . '</td>
                            </tr>
                        </table>
                    </div>

                    <p style="font-size: 12px; color: #666;">
                        📎 <strong>Adjunto:</strong> Giro_' . $giro['codigo'] . '.pdf
                    </p>
                </div>

                <div class="footer">
                    <p><strong>' . FACTURACION_RAZON_SOCIAL . '</strong></p>
                    <p>RUC: ' . FACTURACION_RUC . ' | Tel: ' . FACTURACION_TELEFONO . '</p>
                </div>
            </div>
        </body>
        </html>';
    }

    public function printReceipt($id) {
        $giro = $this->giroModel->getByIdWithDetails($id);
        if (!$giro) {
            $this->setFlash('Giro no encontrado', 'danger');
            $this->redirect('giros');
        }
        
        $this->viewStandalone('giros/comprobante', [
            'giro' => $giro,
            'titulo' => 'Comprobante de Giro'
        ]);
    }
}
