<?php
$configPath = __DIR__ . '/../../config/facturacion.php';
if (file_exists($configPath)) require_once $configPath;

$empresa_nombre  = defined('FACTURACION_RAZON_SOCIAL')  ? FACTURACION_RAZON_SOCIAL  : 'MI EMPRESA SAC';
$empresa_ruc     = defined('FACTURACION_RUC')            ? FACTURACION_RUC            : '00000000000';
$empresa_dir     = defined('FACTURACION_DIRECCION')      ? FACTURACION_DIRECCION      : '';
$empresa_tel     = defined('FACTURACION_TELEFONO')       ? FACTURACION_TELEFONO       : '';

$total = (float)$venta['precio_pagado'];
$igv_porcentaje = defined('FACTURACION_IGV_PORCENTAJE') ? FACTURACION_IGV_PORCENTAJE : 18;
$subtotal = $total / (1 + ($igv_porcentaje / 100));
$igv = $total - $subtotal;

$estado = $venta['estado'] ?? 'ACTIVA';
$estado_badge = [
    'ACTIVA'  => ['success', 'fa-check-circle', 'ACTIVA'],
    'ANULADA' => ['danger',  'fa-ban',          'ANULADA'],
    'USADA'   => ['secondary','fa-history',      'USADA']
];
$eb = $estado_badge[$estado] ?? ['secondary', 'fa-question', $estado];

$tipo_mostrar = $venta['asiento_tipo'] ?? '';
if (strtoupper($tipo_mostrar) === 'CAMA') $tipo_mostrar = 'VENTANA';

// QR
$qr_base64 = null;
$qr_texto = 'COMPROBANTE:' . $venta['numero_comprobante']
          . '|RUC:' . $empresa_ruc
          . '|TOTAL:' . number_format($total, 2)
          . '|FECHA:' . date('Ymd', strtotime($venta['fecha_venta']))
          . '|ASIENTO:' . $venta['numero_asiento'];

try {
    $autoload = __DIR__ . '/../../../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
        if (class_exists('Endroid\QrCode\QrCode')) {
            if (class_exists('Endroid\QrCode\Writer\PngWriter')) {
                $qrCode = new \Endroid\QrCode\QrCode($qr_texto);
                $writer = new \Endroid\QrCode\Writer\PngWriter();
                $result = $writer->write($qrCode);
                $qr_base64 = base64_encode($result->getString());
            }
        }
    }
} catch (Exception $e) {
    if (class_exists('Logger')) Logger::error('Error al generar QR: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket <?= htmlspecialchars($venta['numero_comprobante']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page { size: 80mm auto; margin: 0; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #e9ecef;
            padding: 20px;
            font-family: 'Courier New', 'Consolas', monospace;
            font-size: 12px;
            line-height: 1.35;
            color: #000;
        }

        .ticket {
            width: 80mm;
            max-width: 320px;
            margin: 0 auto;
            background: white;
            padding: 6mm 4mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        /* Header */
        .tk-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .tk-header .logo {
            font-size: 24px;
            color: #0d6efd;
            margin-bottom: 4px;
        }
        .tk-header h2 {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 4px 0;
        }
        .tk-header p {
            font-size: 10px;
            margin: 2px 0;
            color: #333;
        }

        /* Comprobante */
        .tk-comprobante {
            text-align: center;
            padding: 6px 0;
            border-bottom: 1px dashed #000;
            margin-bottom: 8px;
        }
        .tk-estado {
            display: inline-block;
            padding: 2px 10px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .tk-estado.ACTIVA  { background: #198754; color: white; }
        .tk-estado.ANULADA { background: #dc3545; color: white; }
        .tk-estado.USADA   { background: #6c757d; color: white; }

        .tk-numero {
            font-size: 12px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        .tk-fecha {
            font-size: 10px;
            color: #555;
        }

        /* Secciones */
        .tk-section {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin: 8px 0 5px 0;
        }
        .tk-row {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            padding: 2px 0;
            gap: 6px;
        }
        .tk-row .label {
            color: #333;
            flex-shrink: 0;
        }
        .tk-row .value {
            font-weight: bold;
            text-align: right;
            word-break: break-word;
            flex-grow: 1;
        }
        .tk-row .value.destacado {
            font-size: 13px;
            color: #0d6efd;
        }

        /* Total */
        .tk-total {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin: 8px 0;
            text-align: center;
        }
        .tk-total .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .tk-total .amount {
            font-size: 22px;
            font-weight: bold;
            margin: 3px 0;
            font-family: 'Courier New', monospace;
        }
        .tk-total .igv {
            font-size: 9px;
            color: #333;
        }

        /* QR */
        .tk-qr {
            text-align: center;
            padding: 8px 0;
            border-bottom: 1px dashed #000;
            margin-bottom: 8px;
        }
        .tk-qr img {
            width: 110px;
            height: 110px;
            display: block;
            margin: 0 auto 4px;
        }
        .tk-qr .placeholder {
            width: 110px;
            height: 110px;
            border: 1px dashed #999;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            margin: 0 auto 4px;
            font-size: 9px;
            color: #999;
        }
        .tk-qr .placeholder i { font-size: 36px; margin-bottom: 4px; }
        .tk-qr .qr-text { font-size: 8.5px; color: #333; }

        /* Footer */
        .tk-footer {
            text-align: center;
            padding-top: 6px;
            font-size: 9.5px;
            color: #333;
        }
        .tk-footer .gracias {
            font-weight: bold;
            font-size: 11px;
            margin: 6px 0;
        }
        .tk-footer p { margin: 3px 0; }

        .no-print {
            text-align: center;
            margin-top: 15px;
        }

        @media print {
            body { background: white; padding: 0; margin: 0; }
            .ticket { width: 100%; max-width: none; box-shadow: none; padding: 4mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="ticket">

        <!-- HEADER -->
        <div class="tk-header">
            <div class="logo"><i class="fas fa-bus"></i></div>
            <h2><?= htmlspecialchars($empresa_nombre) ?></h2>
            <p><strong>RUC:</strong> <?= htmlspecialchars($empresa_ruc) ?></p>
            <?php if ($empresa_dir): ?><p><?= htmlspecialchars($empresa_dir) ?></p><?php endif; ?>
            <?php if ($empresa_tel): ?><p>Tel: <?= htmlspecialchars($empresa_tel) ?></p><?php endif; ?>
        </div>

        <!-- COMPROBANTE -->
        <div class="tk-comprobante">
            <span class="tk-estado <?= $estado ?>"><?= $eb[2] ?></span>
            <div class="tk-numero"><?= htmlspecialchars($venta['numero_comprobante']) ?></div>
            <div class="tk-fecha"><?= date('d/m/Y H:i', strtotime($venta['fecha_venta'])) ?></div>
        </div>

        <!-- PASAJERO -->
        <div class="tk-section"><i class="fas fa-user"></i> PASAJERO</div>
        <div class="tk-row">
            <span class="label">Nombre:</span>
            <span class="value"><?= htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']) ?></span>
        </div>
        <div class="tk-row">
            <span class="label">Documento:</span>
            <span class="value"><?= htmlspecialchars($venta['tipo_documento'] . ': ' . $venta['numero_documento']) ?></span>
        </div>
        <?php if (!empty($venta['telefono'])): ?>
        <div class="tk-row">
            <span class="label">Teléfono:</span>
            <span class="value"><?= htmlspecialchars($venta['telefono']) ?></span>
        </div>
        <?php endif; ?>

        <!-- VIAJE -->
        <div class="tk-section"><i class="fas fa-route"></i> VIAJE</div>
        <div class="tk-row">
            <span class="label">Ruta:</span>
            <span class="value"><?= htmlspecialchars($venta['origen'] . ' → ' . $venta['destino']) ?></span>
        </div>
        <div class="tk-row">
            <span class="label">Salida:</span>
            <span class="value"><?= date('d/m/Y H:i', strtotime($venta['fecha_salida'])) ?></span>
        </div>
        <div class="tk-row">
            <span class="label">Asiento:</span>
            <span class="value destacado">
                <?= $venta['numero_asiento'] ?>
                <?php if (!empty($tipo_mostrar)): ?>
                    <small>(<?= strtoupper($tipo_mostrar) ?>)</small>
                <?php endif; ?>
            </span>
        </div>
        <div class="tk-row">
            <span class="label">Bus:</span>
            <span class="value"><?= htmlspecialchars($venta['placa']) ?> (<?= $venta['tipo_servicio'] ?>)</span>
        </div>

        <!-- PAGO -->
        <div class="tk-section"><i class="fas fa-credit-card"></i> PAGO</div>
        <div class="tk-row">
            <span class="label">Método:</span>
            <span class="value"><?= $venta['metodo_pago'] ?></span>
        </div>
        <div class="tk-row">
            <span class="label">Vendedor:</span>
            <span class="value"><?= htmlspecialchars(trim(($venta['vendedor_nombre'] ?? '') . ' ' . ($venta['vendedor_apellido'] ?? ''))) ?></span>
        </div>

        <!-- TOTAL -->
        <div class="tk-total">
            <div class="label">TOTAL A PAGAR</div>
            <div class="amount">S/ <?= number_format($total, 2) ?></div>
            <div class="igv">IGV (<?= number_format($igv_porcentaje, 0) ?>%): S/ <?= number_format($igv, 2) ?></div>
        </div>

        <!-- QR -->
        <div class="tk-qr">
            <?php if ($qr_base64): ?>
                <img src="data:image/png;base64,<?= $qr_base64 ?>" alt="QR">
                <div class="qr-text">
                    Escanee para validar<br>
                    <strong><?= htmlspecialchars($venta['numero_comprobante']) ?></strong>
                </div>
            <?php else: ?>
                <div class="placeholder">
                    <i class="fas fa-qrcode"></i>
                    <span>QR pendiente</span>
                </div>
                <div class="qr-text">Código: <strong><?= htmlspecialchars($venta['numero_comprobante']) ?></strong></div>
            <?php endif; ?>
        </div>

        <!-- FOOTER -->
        <div class="tk-footer">
            <p class="gracias"><i class="fas fa-heart"></i> ¡Gracias por viajar!</p>
            <p>Presente este ticket al abordar.</p>
            <p>Conserve su comprobante durante el viaje.</p>
            <p style="font-size: 8.5px; margin-top: 6px;">
                <?= htmlspecialchars($empresa_nombre) ?><br>
                <?= date('d/m/Y H:i:s') ?>
            </p>
        </div>
    </div>

    <div class="no-print">
        <button class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <a href="<?= BASE_URL ?>ventas" class="btn btn-secondary btn-sm">
            <i class="fas fa-times me-1"></i>Cerrar
        </a>
    </div>
</body>
</html>