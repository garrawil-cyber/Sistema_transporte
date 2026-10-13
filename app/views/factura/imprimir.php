<?php
// Cargar configuración de facturación
$configPath = __DIR__ . '/../../config/facturacion.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

// Valores con fallback
$empresa_nombre = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'MI EMPRESA SAC';
$empresa_ruc    = defined('FACTURACION_RUC') ? FACTURACION_RUC : '00000000000';
$empresa_dir    = defined('FACTURACION_DIRECCION') ? FACTURACION_DIRECCION : '';
$empresa_tel    = defined('FACTURACION_TELEFONO') ? FACTURACION_TELEFONO : '';
$empresa_email  = defined('FACTURACION_EMAIL') ? FACTURACION_EMAIL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $comprobante['tipo'] . ' ' . $comprobante['numero_completo']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            padding: 20px;
            font-size: 12px;
        }
        
        .comprobante-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .ruc-box {
            border: 3px solid #000;
            padding: 5px 15px;
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .tipo-comprobante {
            background: #000;
            color: #fff;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        
        .numero-comprobante {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .section-title {
            background: #e0e0e0;
            padding: 5px 10px;
            font-weight: bold;
            border: 1px solid #000;
            margin-top: 15px;
            margin-bottom: 10px;
        }
        
        .info-row {
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .info-label {
            display: inline-block;
            width: 130px;
            font-weight: bold;
        }
        
        table.detalle {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        table.detalle th {
            background: #000;
            color: #fff;
            padding: 8px;
            border: 1px solid #000;
            font-size: 11px;
        }
        
        table.detalle td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
        }
        
        .totales-table {
            width: 40%;
            margin-left: auto;
            margin-top: 10px;
        }
        
        .totales-table td {
            padding: 5px 10px;
            border-bottom: 1px solid #ccc;
        }
        
        .total-final {
            font-size: 16px;
            font-weight: bold;
            background: #000;
            color: #fff;
            padding: 10px !important;
        }
        
        .monto-letras {
            border: 2px solid #000;
            padding: 10px;
            margin-top: 15px;
            font-style: italic;
            font-size: 11px;
        }
        
        .qr-container {
            text-align: center;
            margin-top: 20px;
        }
        
        .qr-container img {
            width: 130px;
            height: 130px;
            border: 2px solid #000;
            padding: 5px;
        }
        
        .qr-placeholder {
            display: inline-block;
            padding: 10px;
            border: 2px solid #000;
            width: 130px;
            height: 130px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        
        .hash-cpe {
            font-size: 9px;
            word-break: break-all;
            margin-top: 10px;
            padding: 5px;
            background: #f5f5f5;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(0,0,0,0.04);
            z-index: -1;
            pointer-events: none;
            white-space: nowrap;
        }
        
        .watermark-simulado {
            color: rgba(255, 193, 7, 0.08);
        }

        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .comprobante-container {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
            }
            
            .no-print {
                display: none !important;
            }
            
            .watermark {
                color: rgba(0,0,0,0.03);
            }
            
            @page {
                margin: 10mm;
                size: A4;
            }
        }
        
        .btn-action {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 8px;
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <div class="watermark <?php echo $comprobante['estado'] == 'SIMULADO' ? 'watermark-simulado' : ''; ?>">
        <?php echo $comprobante['estado'] == 'SIMULADO' ? 'SIMULADO' : htmlspecialchars($comprobante['numero_completo']); ?>
    </div>

    <!-- Botones de acción (no imprimibles) -->
    <div class="btn-action no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <a href="<?php echo BASE_URL; ?>factura/show/<?php echo $comprobante['id']; ?>" class="btn btn-secondary">
            <i class="fas fa-times me-1"></i>Cerrar
        </a>
    </div>

    <!-- Comprobante -->
    <div class="comprobante-container">
        
        <!-- ============================================================
             ENCABEZADO
             ============================================================ -->
        <div class="header">
            <div class="ruc-box">
                R.U.C. <?php echo $empresa_ruc; ?>
            </div>
            <h2 class="mb-1"><?php echo $empresa_nombre; ?></h2>
            <p class="mb-1"><?php echo $empresa_dir; ?></p>
            <p class="mb-0">
                Telf: <?php echo $empresa_tel; ?> | 
                Email: <?php echo $empresa_email; ?>
            </p>
            
            <div class="tipo-comprobante">
                <?php echo $comprobante['tipo'] === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA'; ?>
            </div>
            <div class="numero-comprobante">
                <?php echo $comprobante['numero_completo']; ?>
            </div>
        </div>

        <!-- ============================================================
             FECHA DE EMISIÓN
             ============================================================ -->
        <div class="row mb-3">
            <div class="col-6">
                <div class="info-row">
                    <span class="info-label">FECHA EMISIÓN:</span>
                    <?php echo date('d/m/Y', strtotime($comprobante['fecha_emision'])); ?>
                </div>
                <div class="info-row">
                    <span class="info-label">HORA:</span>
                    <?php echo date('H:i:s', strtotime($comprobante['fecha_emision'])); ?>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="info-row">
                    <span class="info-label">MONEDA:</span>
                    <?php echo $comprobante['moneda']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">ESTADO:</span>
                    <span class="badge bg-<?php 
                        echo $comprobante['estado'] == 'ACEPTADO' ? 'success' : 
                            ($comprobante['estado'] == 'RECHAZADO' ? 'danger' : 
                            ($comprobante['estado'] == 'SIMULADO' ? 'warning' : 'secondary')); 
                    ?>">
                        <?php echo $comprobante['estado']; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ============================================================
             DATOS DEL CLIENTE
             ============================================================ -->
        <div class="section-title">
            <i class="fas fa-user me-1"></i>DATOS DEL ADQUIRIENTE
        </div>
        <div class="info-row">
            <span class="info-label">Razón Social:</span>
            <?php echo htmlspecialchars($comprobante['cliente_nombre']); ?>
        </div>
        <div class="info-row">
            <span class="info-label"><?php echo $comprobante['cliente_tipo_doc'] == 'DNI' ? 'DNI' : 'RUC'; ?>:</span>
            <?php echo $comprobante['cliente_numero_doc']; ?>
        </div>
        <?php if (!empty($venta['direccion'])): ?>
        <div class="info-row">
            <span class="info-label">Dirección:</span>
            <?php echo htmlspecialchars($venta['direccion']); ?>
        </div>
        <?php endif; ?>

        <!-- ============================================================
             DETALLE DE LA VENTA
             ============================================================ -->
        <div class="section-title">
            <i class="fas fa-list me-1"></i>DETALLE DEL SERVICIO
        </div>
        <table class="detalle">
            <thead>
                <tr>
                    <th style="width: 10%;">CANT.</th>
                    <th style="width: 60%;">DESCRIPCIÓN</th>
                    <th style="width: 15%;">P. UNIT.</th>
                    <th style="width: 15%;">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($venta)): ?>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <strong>SERVICIO DE TRANSPORTE DE PASAJEROS</strong><br>
                        <small>
                            Ruta: <?php echo htmlspecialchars($venta['origen'] . ' → ' . $venta['destino']); ?><br>
                            Asiento: <?php echo $venta['numero_asiento']; ?> | 
                            Bus: <?php echo $venta['placa']; ?><br>
                            Salida: <?php echo date('d/m/Y H:i', strtotime($venta['fecha_salida'])); ?>
                            <?php if (!empty($venta['numero_comprobante'])): ?>
                                <br>Ticket: <?php echo $venta['numero_comprobante']; ?>
                            <?php endif; ?>
                        </small>
                    </td>
                    <td class="text-end">
                        S/ <?php echo number_format($comprobante['subtotal'], 2); ?>
                    </td>
                    <td class="text-end">
                        S/ <?php echo number_format($comprobante['subtotal'], 2); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ============================================================
             TOTALES
             ============================================================ -->
        <table class="totales-table">
            <tr>
                <td>SUB TOTAL:</td>
                <td class="text-end">S/ <?php echo number_format($comprobante['subtotal'], 2); ?></td>
            </tr>
            <tr>
                <td>IGV (18%):</td>
                <td class="text-end">S/ <?php echo number_format($comprobante['igv'], 2); ?></td>
            </tr>
            <tr>
                <td>OP. GRAVADAS:</td>
                <td class="text-end">S/ <?php echo number_format($comprobante['subtotal'], 2); ?></td>
            </tr>
            <tr class="total-final">
                <td><strong>IMPORTE TOTAL:</strong></td>
                <td class="text-end"><strong>S/ <?php echo number_format($comprobante['total'], 2); ?></strong></td>
            </tr>
        </table>

        <!-- ============================================================
             MONTO EN LETRAS
             ============================================================ -->
        <div class="monto-letras">
            <strong>SON:</strong> 
            <?php echo strtoupper(number_format($comprobante['total'], 2)) . ' SOLES'; ?>
        </div>

        <!-- ============================================================
             INFORMACIÓN DEL COMPROBANTE ELECTRÓNICO
             ============================================================ -->
        <div class="section-title mt-4">
            <i class="fas fa-info-circle me-1"></i>INFORMACIÓN ELECTRÓNICA
        </div>
        <div class="row small">
            <div class="col-6">
                <div class="info-row">
                    <span class="info-label">Serie:</span>
                    <?php echo $comprobante['serie']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Correlativo:</span>
                    <?php echo $comprobante['correlativo']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Código CDR:</span>
                    <?php echo $comprobante['cdr_code'] ?? 'N/A'; ?>
                </div>
            </div>
            <div class="col-6">
                <div class="info-row">
                    <span class="info-label">F. Envío:</span>
                    <?php echo !empty($comprobante['fecha_envio']) ? date('d/m/Y H:i', strtotime($comprobante['fecha_envio'])) : 'Pendiente'; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Moneda:</span>
                    <?php echo $comprobante['moneda']; ?>
                </div>
                <div class="info-row">
                    <span class="info-label">Tipo Doc:</span>
                    <?php echo $comprobante['tipo']; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($comprobante['hash_cpe'])): ?>
        <div class="hash-cpe">
            <strong>Hash CPE:</strong> <?php echo $comprobante['hash_cpe']; ?>
        </div>
        <?php endif; ?>

        <!-- ============================================================
             QR
             ============================================================ -->
        <div class="qr-container">
            <?php 
            // Intentar generar QR real
            $qr_base64 = null;
            try {
                $autoload = __DIR__ . '/../../../vendor/autoload.php';
                if (file_exists($autoload)) {
                    require_once $autoload;
                    
                    if (class_exists('Endroid\QrCode\QrCode') && class_exists('Endroid\QrCode\Writer\PngWriter')) {
                        $qr_texto = $empresa_ruc . '|' . $comprobante['tipo'] . '|' . 
                                    $comprobante['numero_completo'] . '|' . 
                                    number_format($comprobante['total'], 2) . '|' . 
                                    date('Ymd', strtotime($comprobante['fecha_emision']));
                        
                        $qrCode = new \Endroid\QrCode\QrCode($qr_texto);
                        $writer = new \Endroid\QrCode\Writer\PngWriter();
                        $result = $writer->write($qrCode);
                        $qr_base64 = base64_encode($result->getString());
                    }
                }
            } catch (Exception $e) {
                // Silencioso
            }
            ?>
            
            <?php if ($qr_base64): ?>
                <img src="data:image/png;base64,<?php echo $qr_base64; ?>" 
                     alt="QR <?php echo $comprobante['numero_completo']; ?>">
            <?php else: ?>
                <div style="display:inline-block; padding:10px; border:2px solid #000; width:130px; height:130px;">
                    <i class="fas fa-qrcode" style="font-size: 80px;"></i>
                </div>
            <?php endif; ?>
            
            <p class="mb-0 mt-2 small">
                Escanee para validar
            </p>
        </div>

        <!-- ============================================================
             PIE DE PÁGINA
             ============================================================ -->
        <div class="footer">
            <p class="mb-1">
                <strong>Esta es una representación impresa de un comprobante electrónico</strong>
            </p>
            <p class="mb-1">
                Consulte su validez en: www.sunat.gob.pe
            </p>
            <p class="mb-0">
                Generado por <?php echo $empresa_nombre; ?> el <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>

    </div>

    <script>
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
    </script>
</body>
</html>
