<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// Cargar configuración de email (con soporte .env)
$emailConfig = __DIR__ . '/../config/email.php';
if (file_exists($emailConfig)) {
    require_once $emailConfig;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host       = defined('MAIL_HOST')       ? MAIL_HOST       : 'smtp.gmail.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = defined('MAIL_USERNAME')   ? MAIL_USERNAME   : '';
        $this->mailer->Password   = defined('MAIL_PASSWORD')   ? MAIL_PASSWORD   : '';
        $this->mailer->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'tls';
        $this->mailer->Port       = defined('MAIL_PORT')       ? MAIL_PORT       : 587;
        $this->mailer->CharSet    = 'UTF-8';

        if (defined('MAIL_FROM_EMAIL') && !empty(MAIL_FROM_EMAIL)) {
            $this->mailer->setFrom(
                MAIL_FROM_EMAIL,
                defined('MAIL_FROM_NAME') ? MAIL_FROM_NAME : 'Sistema de Transporte'
            );
        }
        if (defined('MAIL_REPLY_TO') && !empty(MAIL_REPLY_TO)) {
            $this->mailer->addReplyTo(MAIL_REPLY_TO);
        }
    }

    /**
     * Enviar manifiesto por email con HTML formateado
     * 
     * @param string $destinatario Email destino
     * @param int    $viaje_id
     * @param array  $data Datos del manifiesto (viaje, pasajeros, etc.)
     * @param string $hash Hash único de validación
     * @return bool
     */
    public function enviarManifiesto($destinatario, $viaje_id, $data, $hash) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($destinatario);

            $viaje = $data['viaje'] ?? [];
            $origen  = $viaje['origen']  ?? '';
            $destino = $viaje['destino'] ?? '';
            $asunto  = "Manifiesto de Pasajeros — {$origen} → {$destino}";

            $this->mailer->Subject = $asunto;
            $this->mailer->isHTML(true);

            $body = $this->generarCuerpoEmail($data, $hash);
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags($body);

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al enviar manifiesto: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            return false;
        }
    }

    /**
     * Cuerpo HTML del correo
     */
    private function generarCuerpoEmail($data, $hash) {
        $viaje     = $data['viaje']       ?? [];
        $pasajeros = $data['pasajeros']   ?? [];
        $total     = $data['total_asientos'] ?? 0;
        $ocupados  = $data['ocupados']    ?? 0;
        $libres    = $data['libres']      ?? 0;

        $empresa_nombre = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'MI EMPRESA';
        $empresa_ruc    = defined('FACTURACION_RUC') ? FACTURACION_RUC : '';
        $urlQR          = BASE_URL . 'validar/manifiesto/' . $hash;

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .container { max-width: 700px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0d6efd, #0a58ca); color: white; padding: 25px; text-align: center; }
                .header h1 { margin: 0 0 8px 0; font-size: 22px; }
                .header p { margin: 0; opacity: 0.9; font-size: 14px; }
                .content { padding: 25px; }
                .info-box { background: #f8f9fa; padding: 18px; border-radius: 8px; margin: 18px 0; border-left: 4px solid #0d6efd; }
                .info-box table { width: 100%; }
                .info-box td { padding: 5px 0; font-size: 13px; }
                .info-box td:first-child { font-weight: bold; color: #666; width: 42%; }
                table.pasajeros { width: 100%; border-collapse: collapse; margin-top: 15px; }
                table.pasajeros th { background: #0d6efd; color: white; padding: 10px; text-align: left; font-size: 12px; }
                table.pasajeros td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 12px; }
                table.pasajeros tr:nth-child(even) { background: #f8f9fa; }
                .badges { text-align: center; margin: 15px 0; }
                .badge { display: inline-block; padding: 8px 16px; border-radius: 6px; margin: 3px; font-size: 13px; font-weight: bold; }
                .badge-success { background: #198754; color: white; }
                .badge-warning { background: #ffc107; color: #000; }
                .badge-secondary { background: #6c757d; color: white; }
                .btn { display: inline-block; padding: 12px 25px; background: #0d6efd; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
                .qr-section { text-align: center; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px; }
                .qr-section img { max-width: 180px; }
                .footer { background: #212529; color: white; padding: 18px; text-align: center; font-size: 12px; }
                .footer p { margin: 4px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>📋 Manifiesto de Pasajeros</h1>
                    <p>' . htmlspecialchars($viaje['origen'] ?? '') . ' → ' . htmlspecialchars($viaje['destino'] ?? '') . '</p>
                </div>

                <div class="content">
                    <p>Estimado(a), adjunto encontrará el manifiesto de pasajeros correspondiente al siguiente viaje:</p>

                    <div class="info-box">
                        <table>
                            <tr><td>Fecha de Salida:</td><td>' . (!empty($viaje['fecha_salida']) ? date('d/m/Y H:i', strtotime($viaje['fecha_salida'])) : '-') . '</td></tr>
                            <tr><td>Fecha de Llegada:</td><td>' . (!empty($viaje['fecha_llegada']) ? date('d/m/Y H:i', strtotime($viaje['fecha_llegada'])) : '-') . '</td></tr>
                            <tr><td>Bus:</td><td>' . htmlspecialchars($viaje['placa'] ?? '-') . ' — ' . htmlspecialchars($viaje['tipo_servicio'] ?? '') . '</td></tr>
                            <tr><td>Conductor:</td><td>' . htmlspecialchars($viaje['conductor_nombre'] ?: 'Sin asignar') . '</td></tr>
                        </table>
                    </div>

                    <div class="badges">
                        <span class="badge badge-success">Ocupados: ' . $ocupados . '</span>
                        <span class="badge badge-warning">Libres: ' . $libres . '</span>
                        <span class="badge badge-secondary">Total: ' . $total . '</span>
                    </div>

                    <h3>Lista de Pasajeros</h3>';

        if (!empty($pasajeros)) {
            $html .= '
                    <table class="pasajeros">
                        <thead>
                            <tr>
                                <th>Asiento</th>
                                <th>Pasajero</th>
                                <th>Documento</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>
                        <tbody>';

            foreach ($pasajeros as $p) {
                $html .= '
                            <tr>
                                <td><strong>' . htmlspecialchars($p['numero_asiento'] ?? '-') . '</strong></td>
                                <td>' . htmlspecialchars(($p['apellido'] ?? '') . ' ' . ($p['nombre'] ?? '')) . '</td>
                                <td>' . htmlspecialchars(($p['tipo_documento'] ?? '') . ' ' . ($p['numero_documento'] ?? '')) . '</td>
                                <td>' . htmlspecialchars($p['numero_comprobante'] ?? '-') . '</td>
                            </tr>';
            }

            $html .= '
                        </tbody>
                    </table>';
        } else {
            $html .= '<p style="text-align: center; color: #999; padding: 20px;">Sin pasajeros registrados.</p>';
        }

        $html .= '
                    <div class="qr-section">
                        <p style="margin: 0 0 10px 0; font-weight: bold;">Valide este manifiesto escaneando el QR:</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($urlQR) . '" alt="QR Manifiesto">
                        <p style="font-size: 11px; color: #666; margin: 10px 0 0 0; word-break: break-all;">' . htmlspecialchars($urlQR) . '</p>
                    </div>

                    <p style="text-align: center; margin-top: 20px;">
                        <a href="' . htmlspecialchars($urlQR) . '" class="btn">Ver Manifiesto en Línea</a>
                    </p>
                </div>

                <div class="footer">
                    <p><strong>' . htmlspecialchars($empresa_nombre) . '</strong></p>
                    <p>RUC: ' . htmlspecialchars($empresa_ruc) . '</p>
                    <p style="font-size: 10px; opacity: 0.7; margin-top: 10px;">Este correo fue enviado automáticamente. Por favor no responder.</p>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}