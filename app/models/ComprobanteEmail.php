<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class ComprobanteEmail extends Model {
    protected $table = 'comprobantes_electronicos';
    private $mailer;

    public function __construct() {
        parent::__construct();
        $this->initMailer();
    }

    /**
     * Inicializar PHPMailer
     */
    private function initMailer() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = MAIL_HOST;
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = MAIL_USERNAME;
        $this->mailer->Password   = MAIL_PASSWORD;
        $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
        $this->mailer->Port       = MAIL_PORT;
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $this->mailer->addReplyTo(MAIL_REPLY_TO);
    }

    /**
     * ============================================
     * GENERAR PDF DEL COMPROBANTE
     * ============================================
     */
    public function generarPDF($comprobante_id) {
        try {
            // ✅ FIX: cast y validación temprana
            $comprobante_id = (int)$comprobante_id;
            if ($comprobante_id <= 0) {
                return ['success' => false, 'message' => 'ID de comprobante inválido'];
            }

            $comprobante = $this->find($comprobante_id);
            if (!$comprobante) {
                return ['success' => false, 'message' => 'Comprobante no encontrado'];
            }

            $ventaModel = new Venta();
            $venta = $ventaModel->getByIdWithDetails($comprobante['venta_id']);

            $html = $this->generarHTMLPDF($comprobante, $venta);

            $options = new Options();
            $options->set('defaultFont', 'Helvetica');
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return [
                'success'     => true,
                'pdf'         => $dompdf->output(),
                'nombre'      => $comprobante['tipo'] . '_' . $comprobante['numero_completo'] . '.pdf',
                'comprobante' => $comprobante,
                'venta'       => $venta
            ];

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al generar PDF: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            return ['success' => false, 'message' => 'Error al generar PDF: ' . $e->getMessage()];
        }
    }

    /**
     * HTML del PDF (formato oficial)
     */
    private function generarHTMLPDF($comprobante, $venta) {
        $moneda = $comprobante['moneda'] ?? 'PEN';
        $simbolo = ($moneda === 'USD') ? '$' : 'S/';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 20px 30px; }
                body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #333; }
                .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
                .header h2 { color: #0d6efd; margin: 0 0 5px 0; font-size: 16pt; }
                .header p { margin: 2px 0; font-size: 9pt; color: #666; }
                .ruc-box { border: 2px solid #000; display: inline-block; padding: 5px 15px; margin: 10px 0; font-weight: bold; }
                .tipo-doc { background: #0d6efd; color: white; padding: 8px 20px; display: inline-block; margin: 10px 0; font-weight: bold; }
                .numero { font-size: 14pt; font-weight: bold; margin-top: 5px; }
                .datos { margin: 20px 0; }
                .datos table { width: 100%; }
                .datos td { padding: 4px 0; vertical-align: top; }
                .label { font-weight: bold; width: 130px; }
                .seccion { background: #f0f0f0; padding: 5px 10px; font-weight: bold; margin: 15px 0 10px; border-left: 4px solid #0d6efd; }
                table.detalle { width: 100%; border-collapse: collapse; margin: 10px 0; }
                table.detalle th { background: #0d6efd; color: white; padding: 8px; text-align: left; font-size: 9pt; }
                table.detalle td { border: 1px solid #ddd; padding: 8px; font-size: 9pt; }
                .totales { width: 40%; margin-left: auto; margin-top: 10px; }
                .totales table { width: 100%; }
                .totales td { padding: 5px 10px; }
                .total-final { background: #0d6efd; color: white; font-weight: bold; font-size: 12pt; }
                .monto-letras { border: 1px solid #333; padding: 10px; margin-top: 15px; font-style: italic; font-size: 9pt; }
                .qr-section { text-align: center; margin-top: 20px; }
                .footer { text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8pt; color: #666; }
                .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
                .badge-success { background: #198754; color: white; }
                .badge-warning { background: #ffc107; color: #000; }
                .badge-danger { background: #dc3545; color: white; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="ruc-box">R.U.C. ' . FACTURACION_RUC . '</div>
                <h2>' . FACTURACION_RAZON_SOCIAL . '</h2>
                <p>' . FACTURACION_DIRECCION . '</p>
                <p>Tel: ' . FACTURACION_TELEFONO . ' | Email: ' . FACTURACION_EMAIL . '</p>
                <div class="tipo-doc">' . ($comprobante['tipo'] === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA') . '</div>
                <div class="numero">' . $comprobante['numero_completo'] . '</div>
            </div>

            <div class="datos">
                <table>
                    <tr>
                        <td class="label">Fecha Emisión:</td>
                        <td>' . date('d/m/Y H:i:s', strtotime($comprobante['fecha_emision'])) . '</td>
                        <td class="label">Moneda:</td>
                        <td>' . $moneda . '</td>
                    </tr>
                    <tr>
                        <td class="label">Cliente:</td>
                        <td>' . htmlspecialchars($comprobante['cliente_nombre']) . '</td>
                        <td class="label">Documento:</td>
                        <td>' . $comprobante['cliente_tipo_doc'] . ': ' . $comprobante['cliente_numero_doc'] . '</td>
                    </tr>
                </table>
            </div>

            <div class="seccion">DETALLE DEL SERVICIO</div>
            <table class="detalle">
                <thead>
                    <tr>
                        <th style="width: 10%;">Cant.</th>
                        <th style="width: 60%;">Descripción</th>
                        <th style="width: 15%; text-align: right;">P. Unit.</th>
                        <th style="width: 15%; text-align: right;">Importe</th>
                    </tr>
                </thead>
                <tbody>';

        if ($venta) {
            $html .= '
                    <tr>
                        <td>1</td>
                        <td>
                            <strong>Servicio de Transporte de Pasajeros</strong><br>
                            <small>Ruta: ' . htmlspecialchars($venta['origen'] . ' → ' . $venta['destino']) . '</small><br>
                            <small>Asiento: ' . $venta['numero_asiento'] . ' | Bus: ' . $venta['placa'] . '</small><br>
                            <small>Salida: ' . date('d/m/Y H:i', strtotime($venta['fecha_salida'])) . '</small>
                        </td>
                        <td style="text-align: right;">' . $simbolo . ' ' . number_format($comprobante['subtotal'], 2) . '</td>
                        <td style="text-align: right;">' . $simbolo . ' ' . number_format($comprobante['subtotal'], 2) . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>

            <div class="totales">
                <table>
                    <tr>
                        <td>Sub Total:</td>
                        <td style="text-align: right;">' . $simbolo . ' ' . number_format($comprobante['subtotal'], 2) . '</td>
                    </tr>
                    <tr>
                        <td>IGV (18%):</td>
                        <td style="text-align: right;">' . $simbolo . ' ' . number_format($comprobante['igv'], 2) . '</td>
                    </tr>
                    <tr class="total-final">
                        <td>IMPORTE TOTAL:</td>
                        <td style="text-align: right;">' . $simbolo . ' ' . number_format($comprobante['total'], 2) . '</td>
                    </tr>
                </table>
            </div>

            <div class="monto-letras">
                <strong>SON:</strong> ' . strtoupper($this->numeroALetras($comprobante['total'])) . ' SOLES
            </div>

            <div class="qr-section">
                <p style="font-size: 8pt; color: #666;">Representación impresa del comprobante electrónico</p>
                <p style="font-size: 8pt; color: #666;">Consulte su validez en www.sunat.gob.pe</p>
                <span class="badge badge-' . (
                    $comprobante['estado'] == 'ACEPTADO' ? 'success' :
                    ($comprobante['estado'] == 'RECHAZADO' ? 'danger' : 'warning')
                ) . '">' . $comprobante['estado'] . '</span>
            </div>

            <div class="footer">
                <p>' . FACTURACION_RAZON_SOCIAL . ' - RUC ' . FACTURACION_RUC . '</p>
                <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * ============================================
     * ENVIAR COMPROBANTE POR EMAIL
     * ============================================
     */
    public function enviarPorEmail($comprobante_id, $email_destino = null, $mensaje_personalizado = null) {
        try {
            // ✅ FIX: validar ID numérico antes de continuar
            $comprobante_id = (int)$comprobante_id;
            if ($comprobante_id <= 0) {
                return [
                    'success' => false,
                    'message' => 'ID de comprobante inválido'
                ];
            }

            // Generar PDF (que ya valida que exista)
            $pdfData = $this->generarPDF($comprobante_id);

            if (empty($pdfData['success'])) {
                return [
                    'success' => false,
                    'message' => $pdfData['message'] ?? 'Comprobante no encontrado'
                ];
            }

            $comprobante = $pdfData['comprobante'];
            $venta       = $pdfData['venta'];

            // Determinar destinatario
            if (empty($email_destino)) {
                $email_destino = $venta['email'] ?? null;
            }

            if (empty($email_destino) || !filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'No hay un correo electrónico válido para enviar'
                ];
            }

            // Configurar correo
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            $this->mailer->addAddress($email_destino, $comprobante['cliente_nombre']);
            $this->mailer->addBCC(MAIL_FROM_EMAIL);

            $this->mailer->Subject = $comprobante['tipo'] . ' Electrónica ' . $comprobante['numero_completo'] . ' - ' . FACTURACION_RAZON_SOCIAL;
            $this->mailer->isHTML(true);
            $this->mailer->Body    = $this->generarCuerpoEmail($comprobante, $venta, $mensaje_personalizado);
            $this->mailer->AltBody = strip_tags($this->generarCuerpoEmail($comprobante, $venta, $mensaje_personalizado));

            // Adjuntar PDF
            $this->mailer->addStringAttachment(
                $pdfData['pdf'],
                $pdfData['nombre'],
                'base64',
                'application/pdf'
            );

            // Adjuntar XML si existe
            if (!empty($comprobante['xml'])) {
                $nombreXml = $comprobante['cliente_numero_doc'] . '-' . $comprobante['tipo'] . '-' . $comprobante['numero_completo'] . '.xml';
                $this->mailer->addStringAttachment(
                    $comprobante['xml'],
                    $nombreXml,
                    'base64',
                    'application/xml'
                );
            }

            // Enviar
            $this->mailer->send();

            // Registrar en auditoría
            if (class_exists('Auditoria')) {
                try {
                    Auditoria::registrar('EXPORT', [
                        'tabla_afectada' => 'comprobantes_electronicos',
                        'modulo'         => 'facturacion',
                        'registro_id'    => $comprobante_id,
                        'descripcion'    => 'Envió ' . $comprobante['tipo'] . ' ' . $comprobante['numero_completo'] . ' por email a ' . $email_destino
                    ]);
                } catch (Exception $ae) {
                    if (class_exists('Logger')) {
                        try { Logger::error('Error al registrar auditoría: ' . $ae->getMessage()); }
                        catch (Exception $le) { error_log($le->getMessage()); }
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Comprobante enviado a ' . $email_destino,
                'email'   => $email_destino
            ];

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al enviar comprobante por email: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            return [
                'success' => false,
                'message' => 'Error al enviar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cuerpo HTML del correo
     */
    private function generarCuerpoEmail($comprobante, $venta, $mensaje_personalizado = null) {
        $moneda  = $comprobante['moneda'] ?? 'PEN';
        $simbolo = ($moneda === 'USD') ? '$' : 'S/';

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; padding: 30px; text-align: center; }
                .header h1 { margin: 0 0 10px 0; font-size: 22px; }
                .header p { margin: 0; opacity: 0.9; }
                .content { padding: 30px; }
                .content p { line-height: 1.6; color: #333; }
                .info-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0d6efd; }
                .info-box table { width: 100%; }
                .info-box td { padding: 5px 0; }
                .info-box td:first-child { font-weight: bold; color: #666; width: 40%; }
                .btn { display: inline-block; padding: 12px 30px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; font-weight: bold; }
                .btn:hover { background: #0a58ca; }
                .footer { background: #212529; color: white; padding: 20px; text-align: center; font-size: 12px; }
                .footer a { color: #0dcaf0; text-decoration: none; }
                .monto { font-size: 24px; color: #198754; font-weight: bold; text-align: center; margin: 15px 0; }
                .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; }
                .badge-success { background: #198754; color: white; }
                .badge-warning { background: #ffc107; color: #000; }
                .badge-danger { background: #dc3545; color: white; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📄 ' . $comprobante['tipo'] . ' Electrónica</h1>
                    <p>' . $comprobante['numero_completo'] . '</p>
                </div>

                <div class="content">
                    <p>Estimado(a) <strong>' . htmlspecialchars($comprobante['cliente_nombre']) . '</strong>,</p>

                    <p>' . ($mensaje_personalizado ? nl2br(htmlspecialchars($mensaje_personalizado)) : 'Adjunto encontrará su ' . strtolower($comprobante['tipo']) . ' electrónica correspondiente al servicio de transporte adquirido.') . '</p>

                    <div class="info-box">
                        <table>
                            <tr>
                                <td>N° Comprobante:</td>
                                <td><strong>' . $comprobante['numero_completo'] . '</strong></td>
                            </tr>
                            <tr>
                                <td>Fecha Emisión:</td>
                                <td>' . date('d/m/Y H:i', strtotime($comprobante['fecha_emision'])) . '</td>
                            </tr>';

        if ($venta) {
            $html .= '
                            <tr>
                                <td>Ruta:</td>
                                <td>' . htmlspecialchars($venta['origen'] . ' → ' . $venta['destino']) . '</td>
                            </tr>
                            <tr>
                                <td>Asiento:</td>
                                <td>' . $venta['numero_asiento'] . '</td>
                            </tr>
                            <tr>
                                <td>Fecha de Viaje:</td>
                                <td>' . date('d/m/Y H:i', strtotime($venta['fecha_salida'])) . '</td>
                            </tr>';
        }

        $html .= '
                            <tr>
                                <td>Estado SUNAT:</td>
                                <td>
                                    <span class="badge badge-' . (
                                        $comprobante['estado'] == 'ACEPTADO' ? 'success' :
                                        ($comprobante['estado'] == 'RECHAZADO' ? 'danger' : 'warning')
                                    ) . '">' . $comprobante['estado'] . '</span>
                                </td>
                            </tr>
                        </table>
                        <div class="monto">' . $simbolo . ' ' . number_format($comprobante['total'], 2) . '</div>
                    </div>

                    <p style="text-align: center;">
                        <a href="' . BASE_URL . 'factura/show/' . $comprobante['id'] . '" class="btn">Ver Comprobante en Línea</a>
                    </p>

                    <p style="font-size: 12px; color: #666; margin-top: 20px;">
                        📎 Se adjuntan los siguientes archivos:
                    </p>
                    <ul style="font-size: 12px; color: #666;">
                        <li><strong>' . $comprobante['tipo'] . '_' . $comprobante['numero_completo'] . '.pdf</strong> - Comprobante en PDF</li>
                        ' . (!empty($comprobante['xml']) ? '<li><strong>' . $comprobante['numero_completo'] . '.xml</strong> - XML firmado</li>' : '') . '
                    </ul>

                    <p style="font-size: 12px; color: #666;">
                        Si tiene alguna consulta, no dude en contactarnos.
                    </p>
                </div>

                <div class="footer">
                    <p><strong>' . FACTURACION_RAZON_SOCIAL . '</strong></p>
                    <p>RUC: ' . FACTURACION_RUC . ' | Tel: ' . FACTURACION_TELEFONO . '</p>
                    <p>' . FACTURACION_DIRECCION . '</p>
                    <p style="margin-top: 10px;">
                        <a href="' . BASE_URL . '">Visitar sitio web</a>
                    </p>
                    <p style="font-size: 10px; opacity: 0.7;">
                        Este correo fue enviado automáticamente. Por favor, no responder.
                    </p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Convertir número a letras
     */
    private function numeroALetras($numero) {
        $entero = floor($numero);
        $decimales = round(($numero - $entero) * 100);

        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($entero == 0) return 'CERO CON ' . str_pad($decimales, 2, '0', STR_PAD_LEFT) . '/100';

        $letras = '';

        if ($entero >= 1000) {
            $miles = floor($entero / 1000);
            $letras .= ($miles == 1) ? 'MIL ' : $this->enteroALetras($miles) . ' MIL ';
            $entero %= 1000;
        }

        if ($entero >= 100) {
            $letras .= ($entero == 100) ? 'CIEN ' : $centenas[floor($entero / 100)] . ' ';
            $entero %= 100;
        }

        if ($entero >= 20) {
            $letras .= $decenas[floor($entero / 10)];
            if ($entero % 10 > 0) {
                $letras .= ' Y ' . $unidades[$entero % 10];
            }
        } elseif ($entero >= 10) {
            $letras .= $especiales[$entero - 10];
        } elseif ($entero > 0) {
            $letras .= $unidades[$entero];
        }

        return trim($letras) . ' CON ' . str_pad($decimales, 2, '0', STR_PAD_LEFT) . '/100';
    }

    private function enteroALetras($numero) {
        $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($numero == 0) return 'CERO';
        if ($numero == 100) return 'CIEN';

        $letras = '';

        if ($numero >= 1000) {
            $miles = floor($numero / 1000);
            $letras .= ($miles == 1) ? 'MIL ' : $this->enteroALetras($miles) . ' MIL ';
            $numero %= 1000;
        }

        if ($numero >= 100) {
            $letras .= $centenas[floor($numero / 100)] . ' ';
            $numero %= 100;
        }

        if ($numero >= 20) {
            $letras .= $decenas[floor($numero / 10)];
            if ($numero % 10 > 0) {
                $letras .= ' Y ' . $unidades[$numero % 10];
            }
        } elseif ($numero >= 10) {
            $letras .= $especiales[$numero - 10];
        } elseif ($numero > 0) {
            $letras .= $unidades[$numero];
        }

        return trim($letras);
    }

    /**
     * Descargar PDF
     */
    public function descargarPDF($comprobante_id) {
        $resultado = $this->generarPDF($comprobante_id);

        if (empty($resultado['success'])) {
            return false;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $resultado['nombre'] . '"');
        header('Content-Length: ' . strlen($resultado['pdf']));
        echo $resultado['pdf'];
        exit;
    }
}