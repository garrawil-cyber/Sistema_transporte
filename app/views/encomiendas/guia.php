<?php
// Cargar autoload de Composer
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

// Generar URL de rastreo público
$url_seguimiento = BASE_URL . 'rastreo?guia=' . urlencode($encomienda['guia']);

try {
    $renderer = new ImageRenderer(
        new RendererStyle(150, 1),
        new SvgImageBackEnd()
    );
    
    $writer = new Writer($renderer);
    $svgString = $writer->writeString($url_seguimiento);
    $qrDataUri = 'data:image/svg+xml;base64,' . base64_encode($svgString);
} catch (\Exception $e) {
    $qrDataUri = '';
    error_log('Error al generar QR: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guía de Envío - <?php echo htmlspecialchars($encomienda['guia']); ?></title>
    <style>
        @page {
            size: 10cm 15cm;
            margin: 5mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', 'Arial', sans-serif;
            font-size: 10pt;
            color: #000;
            background: #f0f0f0;
            padding: 20px;
        }
        
        .guia-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 2px solid #000;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .header h2 {
            font-size: 14pt;
            margin-bottom: 3px;
        }
        
        .header .ruc {
            font-size: 9pt;
            margin-bottom: 5px;
        }
        
        .header .direccion {
            font-size: 8pt;
            color: #555;
        }
        
        .guia-number {
            background: #000;
            color: #fff;
            padding: 8px;
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        
        .seccion {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px dotted #999;
        }
        
        .seccion:last-child {
            border-bottom: none;
        }
        
        .seccion-titulo {
            font-size: 8pt;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .info-row {
            margin: 3px 0;
            font-size: 9pt;
            line-height: 1.4;
        }
        
        .info-row strong {
            display: inline-block;
            min-width: 90px;
        }
        
        .qr-container {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            border: 2px dashed #000;
        }
        
        .qr-container img,
        .qr-container svg {
            max-width: 120px;
            height: auto;
        }
        
        .qr-container small {
            display: block;
            font-size: 7pt;
            color: #666;
            margin-top: 5px;
        }
        
        .firmas {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #000;
        }
        
        .firma {
            text-align: center;
            font-size: 8pt;
            flex: 1;
        }
        
        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 3px;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 7pt;
            color: #666;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            background: #000;
            color: #fff;
            font-size: 8pt;
            border-radius: 3px;
        }
        
        .no-print {
            text-align: center;
            margin: 20px 0;
        }
        
        .no-print button {
            padding: 12px 30px;
            font-size: 12pt;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 0 5px;
        }
        
        .no-print button:hover {
            background: #0a58ca;
        }
        
        .no-print .btn-cerrar {
            background: #6c757d;
        }
        
        .no-print .btn-cerrar:hover {
            background: #5a6268;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .guia-container {
                box-shadow: none;
                border: 2px solid #000;
                max-width: 100%;
                padding: 10px;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    
    <!-- Botones de acción (no se imprimen) -->
    <div class="no-print">
        <button onclick="window.print()">🖨️ Imprimir</button>
        <button class="btn-cerrar" onclick="window.close()">✖️ Cerrar</button>
    </div>
    
    <!-- Guía de envío -->
    <div class="guia-container">
        
        <!-- Encabezado -->
        <div class="header">
            <h2><?php echo defined('FACTURACION_NOMBRE_COMERCIAL') ? FACTURACION_NOMBRE_COMERCIAL : FACTURACION_RAZON_SOCIAL; ?></h2>
            <div class="ruc">RUC: <?php echo FACTURACION_RUC; ?></div>
            <div class="direccion">
                <?php echo FACTURACION_DIRECCION; ?><br>
                Tel: <?php echo FACTURACION_TELEFONO; ?>
            </div>
        </div>
        
        <!-- Número de guía -->
        <div class="guia-number">
            GUÍA: <?php echo htmlspecialchars($encomienda['guia']); ?>
        </div>
        
        <!-- Estado -->
        <div style="text-align: center; margin: 10px 0;">
            <span class="badge"><?php echo $encomienda['estado']; ?></span>
        </div>
        
        <!-- REMITENTE -->
        <div class="seccion">
            <div class="seccion-titulo">📤 Remitente</div>
            <div class="info-row">
                <strong>Nombre:</strong> 
                <?php echo htmlspecialchars($encomienda['remitente_nombre'] . ' ' . $encomienda['remitente_apellido']); ?>
            </div>
            <div class="info-row">
                <strong>DNI:</strong> <?php echo $encomienda['remitente_dni']; ?>
            </div>
            <?php if (!empty($encomienda['remitente_telefono'])): ?>
                <div class="info-row">
                    <strong>Teléfono:</strong> <?php echo $encomienda['remitente_telefono']; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- DESTINATARIO -->
        <div class="seccion">
            <div class="seccion-titulo">📥 Destinatario</div>
            <div class="info-row">
                <strong>Nombre:</strong> 
                <?php echo htmlspecialchars($encomienda['destinatario_nombre'] . ' ' . $encomienda['destinatario_apellido']); ?>
            </div>
            <div class="info-row">
                <strong>DNI:</strong> <?php echo $encomienda['destinatario_dni']; ?>
            </div>
            <?php if (!empty($encomienda['destinatario_telefono'])): ?>
                <div class="info-row">
                    <strong>Teléfono:</strong> <?php echo $encomienda['destinatario_telefono']; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- VIAJE -->
        <div class="seccion">
            <div class="seccion-titulo">🚌 Viaje</div>
            <div class="info-row">
                <strong>Ruta:</strong> 
                <?php echo htmlspecialchars($encomienda['origen'] . ' → ' . $encomienda['destino']); ?>
            </div>
            <div class="info-row">
                <strong>Bus:</strong> <?php echo $encomienda['placa']; ?>
            </div>
            <div class="info-row">
                <strong>Salida:</strong> 
                <?php echo date('d/m/Y H:i', strtotime($encomienda['fecha_salida'])); ?>
            </div>
        </div>
        
        <!-- DETALLES DEL PAQUETE -->
        <div class="seccion">
            <div class="seccion-titulo">📦 Detalles del Paquete</div>
            <div class="info-row">
                <strong>Peso:</strong> <?php echo $encomienda['peso_kg']; ?> kg
            </div>
            <?php if (!empty($encomienda['dimensiones'])): ?>
                <div class="info-row">
                    <strong>Dimensiones:</strong> <?php echo htmlspecialchars($encomienda['dimensiones']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($encomienda['descripcion'])): ?>
                <div class="info-row">
                    <strong>Descripción:</strong> <?php echo htmlspecialchars($encomienda['descripcion']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($encomienda['valor_declarado'])): ?>
                <div class="info-row">
                    <strong>Valor:</strong> S/ <?php echo number_format($encomienda['valor_declarado'], 2); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- COSTO -->
        <div class="seccion" style="text-align: center; background: #f8f8f8; padding: 10px; border: 2px solid #000;">
            <div class="seccion-titulo">💰 Costo de Envío</div>
            <div style="font-size: 18pt; font-weight: bold;">
                S/ <?php echo number_format($encomienda['costo_envio'], 2); ?>
            </div>
        </div>
        
        <!-- QR CODE -->
        <div class="qr-container">
            <?php if (!empty($qrDataUri)): ?>
                <img src="<?php echo $qrDataUri; ?>" alt="QR Code">
            <?php else: ?>
                <div style="font-size: 40pt;">▣</div>
                <small style="color: #dc3545;">QR no disponible</small>
            <?php endif; ?>
            <small>Escanee para rastrear</small>
            <small><?php echo htmlspecialchars($encomienda['guia']); ?></small>
        </div>
        
        <!-- Firmas -->
        <div class="firmas">
            <div class="firma">
                <div class="firma-linea">Remitente</div>
            </div>
            <div class="firma">
                <div class="firma-linea">Destinatario</div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div>Guía generada el <?php echo date('d/m/Y H:i:s'); ?></div>
            <div>Conserve este documento</div>
            <div><?php echo FACTURACION_RAZON_SOCIAL; ?></div>
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