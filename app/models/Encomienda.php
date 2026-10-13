<?php
class Encomienda extends Model {
    protected $table = 'encomiendas';

    // Obtener todas las encomiendas con detalles (remitente, destinatario, viaje, usuario)
    public function getAllWithDetails() {
        $sql = "SELECT e.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, r.numero_documento as remitente_dni,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.numero_documento as destinatario_dni,
                       v.fecha_salida, v.fecha_llegada,
                       rt.origen, rt.destino,
                       b.placa,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM encomiendas e
                JOIN clientes r ON e.remitente_id = r.id
                JOIN clientes d ON e.destinatario_id = d.id
                JOIN viajes v ON e.viaje_id = v.id
                JOIN rutas rt ON v.ruta_id = rt.id
                JOIN buses b ON v.bus_id = b.id
                JOIN usuarios u ON e.usuario_id = u.id
                ORDER BY e.fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Buscar por número de guía
    public function findByGuia($guia) {
        $stmt = $this->db->prepare("SELECT * FROM encomiendas WHERE guia = ?");
        $stmt->execute([$guia]);
        return $stmt->fetch();
    }

    // Obtener una encomienda con todos sus detalles (para mostrar)
    public function getByIdWithDetails($id) {
        $sql = "SELECT e.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, r.numero_documento as remitente_dni, r.telefono as remitente_telefono,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.numero_documento as destinatario_dni, d.telefono as destinatario_telefono,
                       v.fecha_salida, v.fecha_llegada,
                       rt.origen, rt.destino,
                       b.placa, b.tipo_servicio,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM encomiendas e
                JOIN clientes r ON e.remitente_id = r.id
                JOIN clientes d ON e.destinatario_id = d.id
                JOIN viajes v ON e.viaje_id = v.id
                JOIN rutas rt ON v.ruta_id = rt.id
                JOIN buses b ON v.bus_id = b.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE e.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Registrar una nueva encomienda
public function registrar($data) {
    try {
        $sql = "INSERT INTO encomiendas 
                (guia, remitente_id, destinatario_id, viaje_id, usuario_id,
                 peso_kg, dimensiones, descripcion, valor_declarado, costo_envio, estado) 
                VALUES (:guia, :remitente_id, :destinatario_id, :viaje_id, :usuario_id,
                        :peso_kg, :dimensiones, :descripcion, :valor_declarado, :costo_envio, 'REGISTRADO')";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'guia'             => $data['guia'],
            'remitente_id'     => $data['remitente_id'],
            'destinatario_id'  => $data['destinatario_id'],
            'viaje_id'         => $data['viaje_id'],
            'usuario_id'       => $data['usuario_id'],
            'peso_kg'          => $data['peso_kg'],
            'dimensiones'      => $data['dimensiones'],
            'descripcion'      => $data['descripcion'],
            'valor_declarado'  => $data['valor_declarado'],
            'costo_envio'      => $data['costo_envio'],
        ]);
        
        // ✅ Devolver el ID del registro recién creado
        return (int)$this->db->lastInsertId();
        
    } catch (Exception $e) {
        Logger::error('Error al registrar encomienda: ' . $e->getMessage());
        return false;
    }
}

    // Actualizar el estado de la encomienda
    public function actualizarEstado($id, $estado, $fecha_entrega = null) {
        $sql = "UPDATE encomiendas SET estado = :estado, fecha_entrega = :fecha_entrega WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id, 'estado' => $estado, 'fecha_entrega' => $fecha_entrega]);
    }

    // Generar número de guía (ej: GUI-20260001)
    public function generarGuia() {
        $anio = date('Y');
        $stmt = $this->db->query("SELECT MAX(id) as ultimo FROM encomiendas");
        $row = $stmt->fetch();
        $numero = ($row['ultimo'] ?? 0) + 1;
        return 'GUI-' . $anio . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    // Obtener viajes disponibles para encomiendas (con asientos libres, pero aquí solo necesitamos la fecha)
    public function getViajesDisponibles() {
        $sql = "SELECT v.*, r.origen, r.destino, b.placa 
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.estado = 'PROGRAMADO' 
                AND v.fecha_salida > NOW()
                ORDER BY v.fecha_salida ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
    $sql = "SELECT e.*, 
                   r.nombre as remitente_nombre, r.apellido as remitente_apellido,
                   d.nombre as destinatario_nombre, d.apellido as destinatario_apellido,
                   v.fecha_salida, rt.origen, rt.destino, b.placa
            FROM encomiendas e
            JOIN clientes r ON e.remitente_id = r.id
            JOIN clientes d ON e.destinatario_id = d.id
            JOIN viajes v ON e.viaje_id = v.id
            JOIN rutas rt ON v.ruta_id = rt.id
            JOIN buses b ON v.bus_id = b.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['estado'])) {
        $sql .= " AND e.estado = ?";
        $params[] = $filtros['estado'];
    }
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (e.guia LIKE ? OR r.nombre LIKE ? OR r.apellido LIKE ? OR d.nombre LIKE ? OR d.apellido LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar, $buscar]);
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(e.fecha_registro) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(e.fecha_registro) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    
    $sql .= " ORDER BY e.fecha_registro DESC";
    
    return $this->paginate($sql, $params, $pagina, $por_pagina);
}


/**
 * ============================================
 * GENERAR PDF DE LA GUÍA DE ENCOMIENDA
 * ============================================
 */
public function generarPDFGuia($encomienda_id) {
    try {
        // Obtener datos completos
        $encomienda = $this->getByIdWithDetails($encomienda_id);
        if (!$encomienda) {
            return ['success' => false, 'message' => 'Encomienda no encontrada'];
        }

        // Generar HTML
        $html = $this->generarHTMLGuiaPDF($encomienda);

        // Generar QR
        $qrDataUri = $this->generarQR($encomienda['guia']);

        // Configurar Dompdf
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return [
            'success' => true,
            'pdf' => $dompdf->output(),
            'nombre' => 'Guia_' . $encomienda['guia'] . '.pdf',
            'encomienda' => $encomienda
        ];

    } catch (\Exception $e) {
        Logger::error('Error al generar PDF guía: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error al generar PDF: ' . $e->getMessage()];
    }
}

/**
 * HTML del PDF de la guía
 */
private function generarHTMLGuiaPDF($encomienda) {
    // Generar QR
    $qrDataUri = $this->generarQR($encomienda['guia']);
    
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { margin: 20px; }
            body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
            .header h2 { color: #0d6efd; margin: 0 0 5px 0; }
            .header p { margin: 2px 0; font-size: 9pt; color: #666; }
            .guia-number { background: #212529; color: white; padding: 10px; text-align: center; font-size: 14pt; font-weight: bold; margin: 15px 0; }
            .section { margin: 15px 0; padding: 10px 0; border-bottom: 1px dotted #ccc; }
            .section-title { font-size: 9pt; text-transform: uppercase; color: #666; font-weight: bold; margin-bottom: 8px; letter-spacing: 1px; }
            .info-row { margin: 5px 0; font-size: 10pt; }
            .info-row strong { display: inline-block; min-width: 100px; }
            .qr-container { text-align: center; margin: 20px 0; padding: 15px; border: 2px dashed #000; }
            .qr-container img { max-width: 150px; }
            .costo-box { text-align: center; background: #f8f9fa; padding: 15px; border: 2px solid #000; margin: 15px 0; }
            .costo-box h3 { margin: 0; font-size: 18pt; }
            .footer { text-align: center; margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8pt; color: #666; }
            .badge { display: inline-block; padding: 5px 15px; background: #198754; color: white; border-radius: 20px; font-size: 10pt; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>' . FACTURACION_RAZON_SOCIAL . '</h2>
            <p>RUC: ' . FACTURACION_RUC . '</p>
            <p>' . FACTURACION_DIRECCION . '</p>
            <p>Tel: ' . FACTURACION_TELEFONO . ' | Email: ' . FACTURACION_EMAIL . '</p>
        </div>

        <div class="guia-number">
            GUÍA DE ENVÍO: ' . $encomienda['guia'] . '
        </div>

        <div class="section">
            <div class="section-title">📤 Remitente</div>
            <div class="info-row"><strong>Nombre:</strong> ' . htmlspecialchars($encomienda['remitente_nombre'] . ' ' . $encomienda['remitente_apellido']) . '</div>
            <div class="info-row"><strong>DNI:</strong> ' . $encomienda['remitente_dni'] . '</div>
            ' . (!empty($encomienda['remitente_telefono']) ? '<div class="info-row"><strong>Teléfono:</strong> ' . $encomienda['remitente_telefono'] . '</div>' : '') . '
        </div>

        <div class="section">
            <div class="section-title">📥 Destinatario</div>
            <div class="info-row"><strong>Nombre:</strong> ' . htmlspecialchars($encomienda['destinatario_nombre'] . ' ' . $encomienda['destinatario_apellido']) . '</div>
            <div class="info-row"><strong>DNI:</strong> ' . $encomienda['destinatario_dni'] . '</div>
            ' . (!empty($encomienda['destinatario_telefono']) ? '<div class="info-row"><strong>Teléfono:</strong> ' . $encomienda['destinatario_telefono'] . '</div>' : '') . '
        </div>

        <div class="section">
            <div class="section-title">🚌 Viaje</div>
            <div class="info-row"><strong>Ruta:</strong> ' . htmlspecialchars($encomienda['origen'] . ' → ' . $encomienda['destino']) . '</div>
            <div class="info-row"><strong>Bus:</strong> ' . $encomienda['placa'] . '</div>
            <div class="info-row"><strong>Salida:</strong> ' . date('d/m/Y H:i', strtotime($encomienda['fecha_salida'])) . '</div>
            <div class="info-row"><strong>Llegada:</strong> ' . date('d/m/Y H:i', strtotime($encomienda['fecha_llegada'])) . '</div>
        </div>

        <div class="section">
            <div class="section-title">📦 Detalles del Paquete</div>
            <div class="info-row"><strong>Peso:</strong> ' . $encomienda['peso_kg'] . ' kg</div>
            ' . (!empty($encomienda['dimensiones']) ? '<div class="info-row"><strong>Dimensiones:</strong> ' . htmlspecialchars($encomienda['dimensiones']) . '</div>' : '') . '
            ' . (!empty($encomienda['descripcion']) ? '<div class="info-row"><strong>Descripción:</strong> ' . htmlspecialchars($encomienda['descripcion']) . '</div>' : '') . '
            ' . (!empty($encomienda['valor_declarado']) && $encomienda['valor_declarado'] > 0 ? '<div class="info-row"><strong>Valor:</strong> S/ ' . number_format($encomienda['valor_declarado'], 2) . '</div>' : '') . '
        </div>

        <div class="costo-box">
            <div style="font-size: 9pt; color: #666;">COSTO DE ENVÍO</div>
            <h3>S/ ' . number_format($encomienda['costo_envio'], 2) . '</h3>
        </div>

        <div class="qr-container">
            ' . (!empty($qrDataUri) ? '<img src="' . $qrDataUri . '" alt="QR">' : '<div style="font-size: 60pt;">▣</div>') . '
            <p style="margin: 10px 0 0 0; font-size: 9pt; color: #666;">Escanee para rastrear su encomienda</p>
        </div>

        <div class="footer">
            <p><strong>' . FACTURACION_RAZON_SOCIAL . '</strong></p>
            <p>Guía generada el ' . date('d/m/Y H:i:s') . '</p>
            <p>Conserve este documento</p>
        </div>
    </body>
    </html>';
}

/**
 * ============================================
 * GENERAR QR EN SVG (BaconQrCode)
 * ============================================
 */
public function generarQR($guia) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $url_rastreo = BASE_URL . 'rastreo?guia=' . urlencode($guia);
        
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 1),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        
        $writer = new \BaconQrCode\Writer($renderer);
        $svgString = $writer->writeString($url_rastreo);
        
        return 'data:image/svg+xml;base64,' . base64_encode($svgString);
        
    } catch (\Exception $e) {
        Logger::error('Error al generar QR: ' . $e->getMessage());
        return '';
    }
}

/**
 * ============================================
 * ENVIAR GUÍA POR EMAIL
 * ============================================
 */
public function enviarGuiaPorEmail($encomienda_id, $email_destino = null, $mensaje_personalizado = null) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        // Generar PDF
        $pdfData = $this->generarPDFGuia($encomienda_id);
        if (!$pdfData['success']) {
            return $pdfData;
        }

        $encomienda = $pdfData['encomienda'];

        // Determinar destinatario
        if (empty($email_destino)) {
            $email_destino = $encomienda['destinatario_email'] ?? $encomienda['remitente_email'] ?? null;
        }

        if (empty($email_destino) || !filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'No hay un correo electrónico válido para enviar'];
        }

        // Configurar PHPMailer
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
        
        $mailer->Subject = 'Guía de Envío ' . $encomienda['guia'] . ' - ' . FACTURACION_RAZON_SOCIAL;
        $mailer->isHTML(true);
        $mailer->Body = $this->generarCuerpoEmailGuia($encomienda, $mensaje_personalizado);
        $mailer->AltBody = strip_tags($this->generarCuerpoEmailGuia($encomienda, $mensaje_personalizado));

        // Adjuntar PDF
        $mailer->addStringAttachment(
            $pdfData['pdf'],
            $pdfData['nombre'],
            'base64',
            'application/pdf'
        );

        $mailer->send();

        // Registrar en auditoría
        if (class_exists('Auditoria')) {
            Auditoria::registrar('EXPORT', [
                'tabla_afectada' => 'encomiendas',
                'modulo' => 'encomiendas',
                'registro_id' => $encomienda_id,
                'descripcion' => 'Envió guía ' . $encomienda['guia'] . ' por email a ' . $email_destino
            ]);
        }

        return [
            'success' => true,
            'message' => 'Guía enviada a ' . $email_destino,
            'email' => $email_destino
        ];

    } catch (\Exception $e) {
        Logger::error('Error al enviar guía por email: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Error al enviar: ' . $e->getMessage()];
    }
}

/**
 * Cuerpo HTML del correo
 */
private function generarCuerpoEmailGuia($encomienda, $mensaje_personalizado = null) {
    $url_rastreo = BASE_URL . 'rastreo?guia=' . urlencode($encomienda['guia']);
    $qrDataUri = $this->generarQR($encomienda['guia']);

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0 0 10px 0; font-size: 22px; }
            .header p { margin: 0; opacity: 0.9; }
            .content { padding: 30px; }
            .content p { line-height: 1.6; color: #333; }
            .guia-box { background: #212529; color: white; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
            .guia-box h2 { margin: 0; font-size: 24px; letter-spacing: 2px; }
            .info-box { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
            .info-box table { width: 100%; }
            .info-box td { padding: 6px 0; }
            .info-box td:first-child { font-weight: bold; color: #666; width: 40%; }
            .btn { display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea, #764ba2); color: white !important; text-decoration: none; border-radius: 50px; margin: 10px 0; font-weight: bold; font-size: 16px; }
            .qr-box { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0; }
            .qr-box img { max-width: 150px; }
            .footer { background: #212529; color: white; padding: 20px; text-align: center; font-size: 12px; }
            .footer a { color: #667eea; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>📦 Tu Encomienda está en Camino</h1>
                <p>' . FACTURACION_RAZON_SOCIAL . '</p>
            </div>

            <div class="content">
                <p>Estimado(a) <strong>' . htmlspecialchars($encomienda['destinatario_nombre'] . ' ' . $encomienda['destinatario_apellido']) . '</strong>,</p>
                
                <p>' . ($mensaje_personalizado ? nl2br(htmlspecialchars($mensaje_personalizado)) : 'Le informamos que tiene una encomienda en camino. Adjunto encontrará la guía de envío en formato PDF.') . '</p>

                <div class="guia-box">
                    <div style="font-size: 12px; opacity: 0.7;">NÚMERO DE GUÍA</div>
                    <h2>' . $encomienda['guia'] . '</h2>
                </div>

                <div class="info-box">
                    <table>
                        <tr>
                            <td>Ruta:</td>
                            <td><strong>' . htmlspecialchars($encomienda['origen'] . ' → ' . $encomienda['destino']) . '</strong></td>
                        </tr>
                        <tr>
                            <td>Fecha de Salida:</td>
                            <td>' . date('d/m/Y H:i', strtotime($encomienda['fecha_salida'])) . '</td>
                        </tr>
                        <tr>
                            <td>Fecha de Llegada:</td>
                            <td>' . date('d/m/Y H:i', strtotime($encomienda['fecha_llegada'])) . '</td>
                        </tr>
                        <tr>
                            <td>Peso:</td>
                            <td>' . $encomienda['peso_kg'] . ' kg</td>
                        </tr>
                        <tr>
                            <td>Bus:</td>
                            <td>' . $encomienda['placa'] . '</td>
                        </tr>
                        <tr>
                            <td>Costo:</td>
                            <td><strong>S/ ' . number_format($encomienda['costo_envio'], 2) . '</strong></td>
                        </tr>
                    </table>
                </div>

                <p style="text-align: center;">
                    <a href="' . $url_rastreo . '" class="btn">🔍 Rastrear mi Encomienda</a>
                </p>

                <div class="qr-box">
                    ' . (!empty($qrDataUri) ? '<img src="' . $qrDataUri . '" alt="QR">' : '') . '
                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">
                        Escanee el código QR para rastrear en tiempo real
                    </p>
                </div>

                <p style="font-size: 12px; color: #666;">
                    📎 <strong>Adjunto:</strong> Guía_' . $encomienda['guia'] . '.pdf
                </p>
            </div>

            <div class="footer">
                <p><strong>' . FACTURACION_RAZON_SOCIAL . '</strong></p>
                <p>RUC: ' . FACTURACION_RUC . ' | Tel: ' . FACTURACION_TELEFONO . '</p>
                <p>' . FACTURACION_DIRECCION . '</p>
                <p style="margin-top: 10px; font-size: 10px; opacity: 0.7;">
                    Este correo fue enviado automáticamente. Por favor, no responder.
                </p>
            </div>
        </div>
    </body>
    </html>';
}

    /**
     * ============================================================
     * ESTADÍSTICAS PARA KPIs
     * ============================================================
     */

    /**
     * Estadísticas de encomiendas de HOY (para KPIs del create.php)
     * Retorna: total_hoy, registradas, en_bodega, en_transito, entregadas
     */
    public function getStatsHoy() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_hoy,
                        SUM(CASE WHEN estado = 'REGISTRADO'  THEN 1 ELSE 0 END) AS registradas,
                        SUM(CASE WHEN estado = 'EN_BODEGA'   THEN 1 ELSE 0 END) AS en_bodega,
                        SUM(CASE WHEN estado = 'EN_TRANSITO' THEN 1 ELSE 0 END) AS en_transito,
                        SUM(CASE WHEN estado = 'ENTREGADO'   THEN 1 ELSE 0 END) AS entregadas,
                        COALESCE(SUM(costo_envio), 0) AS ingresos_hoy
                    FROM encomiendas
                    WHERE DATE(fecha_registro) = CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();

            return [
                'total_hoy'    => (int)($row['total_hoy']    ?? 0),
                'registradas'  => (int)($row['registradas']  ?? 0),
                'en_bodega'    => (int)($row['en_bodega']    ?? 0),
                'en_transito'  => (int)($row['en_transito']  ?? 0),
                'entregadas'   => (int)($row['entregadas']   ?? 0),
                'ingresos_hoy' => (float)($row['ingresos_hoy'] ?? 0),
            ];
        } catch (Exception $e) {
            Logger::error('Error en getStatsHoy: ' . $e->getMessage());
            return [
                'total_hoy' => 0, 'registradas' => 0, 'en_bodega' => 0,
                'en_transito' => 0, 'entregadas' => 0, 'ingresos_hoy' => 0
            ];
        }
    }

    /**
     * Estadísticas GLOBALES (para KPIs del index.php)
     * Respeta los filtros aplicados (buscar, estado, fechas).
     */
    public function getStatsFiltrados($filtros = []) {
    try {
        $sql = "SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN e.estado = 'REGISTRADO'  THEN 1 ELSE 0 END) AS registradas,
                    SUM(CASE WHEN e.estado = 'EN_BODEGA'   THEN 1 ELSE 0 END) AS en_bodega,
                    SUM(CASE WHEN e.estado = 'EN_TRANSITO' THEN 1 ELSE 0 END) AS en_transito,
                    SUM(CASE WHEN e.estado = 'ENTREGADO'   THEN 1 ELSE 0 END) AS entregadas,
                    SUM(CASE WHEN e.estado = 'DEVUELTO'    THEN 1 ELSE 0 END) AS devueltas,
                    COALESCE(SUM(e.costo_envio), 0) AS ingresos_totales,
                    SUM(CASE WHEN DATE(e.fecha_registro) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                              AND DATE(e.fecha_registro) <= CURDATE() 
                             THEN 1 ELSE 0 END) AS total_semana,
                    COALESCE(SUM(CASE WHEN DATE(e.fecha_registro) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                                       AND DATE(e.fecha_registro) <= CURDATE() 
                                      THEN e.costo_envio ELSE 0 END), 0) AS ingresos_semana
                FROM encomiendas e
                JOIN clientes r ON e.remitente_id = r.id
                JOIN clientes d ON e.destinatario_id = d.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND e.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (e.guia LIKE ? OR r.nombre LIKE ? OR r.apellido LIKE ? 
                          OR d.nombre LIKE ? OR d.apellido LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(e.fecha_registro) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(e.fecha_registro) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return [
            'total'             => (int)($row['total'] ?? 0),
            'registradas'       => (int)($row['registradas'] ?? 0),
            'en_bodega'         => (int)($row['en_bodega'] ?? 0),
            'en_transito'       => (int)($row['en_transito'] ?? 0),
            'entregadas'        => (int)($row['entregadas'] ?? 0),
            'devueltas'         => (int)($row['devueltas'] ?? 0),
            'ingresos_totales'  => (float)($row['ingresos_totales'] ?? 0),
            'total_semana'      => (int)($row['total_semana'] ?? 0),      // ✅ NUEVO
            'ingresos_semana'   => (float)($row['ingresos_semana'] ?? 0), // ✅ NUEVO
        ];
    } catch (Exception $e) {
        Logger::error('Error en getStatsFiltrados: ' . $e->getMessage());
        return [
            'total' => 0, 'registradas' => 0, 'en_bodega' => 0,
            'en_transito' => 0, 'entregadas' => 0, 'devueltas' => 0,
            'ingresos_totales' => 0, 'total_semana' => 0, 'ingresos_semana' => 0
        ];
    }
}

        /**
     * Encomiendas por día (últimos 7 días)
     */
   public function getStatsUltimos7Dias() {
    try {
        // ✅ CORREGIDO: usar rangos de fecha más robustos
        $sql = "SELECT 
                    DATE(fecha_registro) AS fecha,
                    COUNT(*) AS total,
                    COALESCE(SUM(costo_envio), 0) AS ingresos
                FROM encomiendas
                WHERE DATE(fecha_registro) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                GROUP BY DATE(fecha_registro)
                ORDER BY fecha ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        
        // Inicializar los 7 días en orden cronológico
        $dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $dias[$fecha] = [
                'fecha'    => $fecha,
                'total'    => 0,
                'ingresos' => 0,
            ];
        }
        
        // Rellenar con datos reales
        foreach ($resultados as $r) {
            if (isset($dias[$r['fecha']])) {
                $dias[$r['fecha']]['total']    = (int)$r['total'];
                $dias[$r['fecha']]['ingresos'] = (float)$r['ingresos'];
            }
        }
        
        return array_values($dias);
    } catch (Exception $e) {
        Logger::error('Error en getStatsUltimos7Dias: ' . $e->getMessage());
        return [];
    }
}

}