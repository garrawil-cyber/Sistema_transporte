<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Giro - <?php echo htmlspecialchars($giro['codigo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A5 portrait;
            margin: 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Arial', sans-serif;
            font-size: 11pt;
            color: #000;
            background: #f0f0f0;
            padding: 20px;
        }

        .comprobante-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border: 2px solid #000;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        /* ============================================================
           HEADER
           ============================================================ */
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h2 {
            font-size: 15pt;
            margin-bottom: 3px;
            color: #0d6efd;
        }

        .header .ruc {
            font-size: 10pt;
            margin-bottom: 3px;
        }

        .header .direccion {
            font-size: 9pt;
            color: #555;
        }

        /* ============================================================
           CÓDIGO DEL GIRO
           ============================================================ */
        .codigo-giro {
            background: #0d6efd;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0;
            letter-spacing: 2px;
        }

        .estado {
            text-align: center;
            margin: 10px 0;
        }

        .badge-estado {
            padding: 5px 20px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            color: #fff;
        }

        .badge-registrado { background: #ffc107; color: #000; }
        .badge-pagado { background: #198754; }
        .badge-anulado { background: #dc3545; }

        /* ============================================================
           SECCIONES
           ============================================================ */
        .seccion {
            margin: 12px 0;
            padding: 10px 0;
            border-bottom: 1px dotted #999;
        }

        .seccion:last-child {
            border-bottom: none;
        }

        .seccion-titulo {
            font-size: 9pt;
            text-transform: uppercase;
            color: #666;
            font-weight: bold;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        /* ============================================================
           RUTA DEL GIRO
           ============================================================ */
        .ruta-giro {
            background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
            border: 2px solid #0d6efd;
            border-radius: 6px;
            padding: 12px;
            margin: 12px 0;
        }

        .ruta-giro .seccion-titulo {
            color: #0d6efd;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }

        .ruta-ciudades {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 8px 0;
        }

        .ruta-ciudad {
            text-align: center;
            flex: 1;
        }

        .ruta-ciudad .label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 3px;
        }

        .ruta-ciudad .valor {
            font-size: 13pt;
            font-weight: bold;
            color: #000;
            display: block;
        }

        .ruta-ciudad.origen .valor { color: #0d6efd; }
        .ruta-ciudad.destino .valor { color: #dc3545; }

        .ruta-flecha {
            font-size: 20pt;
            color: #0d6efd;
            padding: 0 10px;
        }

        .ruta-concepto {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #0d6efd;
            font-size: 9pt;
            color: #333;
        }

        /* ============================================================
           INFO ROWS (Remitente / Destinatario / Detalles)
           ============================================================ */
        .info-row {
            margin: 4px 0;
            font-size: 10pt;
            line-height: 1.4;
        }

        .info-row strong {
            display: inline-block;
            min-width: 100px;
        }

        /* ============================================================
           ✅ MONTO A RECIBIR (destacado para el destinatario)
           ============================================================ */
        .monto-recibir {
            text-align: center;
            background: linear-gradient(135deg, #d1e7dd 0%, #a3cfbb 100%);
            border: 3px solid #198754;
            border-radius: 10px;
            padding: 18px 15px;
            margin: 18px 0 10px 0;
        }

        .monto-recibir .label-recibir {
            font-size: 9pt;
            text-transform: uppercase;
            color: #0f5132;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .monto-recibir .valor-recibir {
            font-size: 26pt;
            font-weight: bold;
            color: #0f5132;
            line-height: 1;
            margin: 5px 0;
        }

        .monto-recibir .moneda-recibir {
            font-size: 8pt;
            color: #0f5132;
            font-style: italic;
        }

        /* ============================================================
           NOTA INTERNA (informativa, no descuenta)
           ============================================================ */
        .nota-interna {
            text-align: center;
            color: #666;
            font-size: 8pt;
            padding: 6px 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 12px;
            border-left: 3px solid #ffc107;
        }

        .nota-interna i {
            color: #ffc107;
            margin-right: 4px;
        }

        /* ============================================================
           DETALLE DEL PAGO (letra pequeña, informativo)
           ============================================================ */
        .detalle-pago {
            margin: 12px 0;
            padding: 8px 0;
            border-top: 1px dotted #999;
        }

        .detalle-pago table {
            width: 100%;
            font-size: 8pt;
            color: #555;
        }

        .detalle-pago td {
            padding: 3px 0;
        }

        .detalle-pago .total-pagado td {
            border-top: 1px solid #999;
            padding-top: 5px;
            color: #000;
            font-size: 9pt;
        }

        /* ============================================================
           FIRMAS
           ============================================================ */
        .firmas {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #000;
        }

        .firma {
            text-align: center;
            font-size: 9pt;
            flex: 1;
        }

        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 5px;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            font-size: 8pt;
            color: #666;
        }

        /* ============================================================
           BOTONES (no se imprimen)
           ============================================================ */
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

        /* ============================================================
           IMPRESIÓN
           ============================================================ */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .comprobante-container {
                box-shadow: none;
                border: none;
                max-width: 100%;
                padding: 10px;
            }

            .no-print {
                display: none !important;
            }

            .ruta-giro,
            .codigo-giro,
            .monto-recibir {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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

    <!-- Comprobante -->
    <div class="comprobante-container">

        <!-- Encabezado -->
        <div class="header">
            <h2><?php echo FACTURACION_RAZON_SOCIAL; ?></h2>
            <div class="ruc">RUC: <?php echo FACTURACION_RUC; ?></div>
            <div class="direccion">
                <?php echo FACTURACION_DIRECCION; ?><br>
                Tel: <?php echo FACTURACION_TELEFONO; ?>
            </div>
        </div>

        <!-- Código del giro -->
        <div class="codigo-giro">
            GIRO: <?php echo htmlspecialchars($giro['codigo']); ?>
        </div>

        <!-- Estado -->
        <div class="estado">
            <?php
                $clase_badge = 'badge-registrado';
                if ($giro['estado'] == 'PAGADO') $clase_badge = 'badge-pagado';
                if ($giro['estado'] == 'ANULADO') $clase_badge = 'badge-anulado';
            ?>
            <span class="badge-estado <?php echo $clase_badge; ?>">
                <?php echo $giro['estado']; ?>
            </span>
        </div>

        <!-- RUTA DEL GIRO -->
        <?php if (!empty($giro['lugar_origen']) || !empty($giro['lugar_destino']) || !empty($giro['concepto'])): ?>
        <div class="ruta-giro">
            <div class="seccion-titulo">📍 Ruta del Giro</div>

            <div class="ruta-ciudades">
                <div class="ruta-ciudad origen">
                    <span class="label">Origen</span>
                    <span class="valor">
                        <?php echo htmlspecialchars($giro['lugar_origen'] ?: '—'); ?>
                    </span>
                </div>

                <div class="ruta-flecha">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div class="ruta-ciudad destino">
                    <span class="label">Destino</span>
                    <span class="valor">
                        <?php echo htmlspecialchars($giro['lugar_destino'] ?: '—'); ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($giro['concepto'])): ?>
                <div class="ruta-concepto">
                    <i class="fas fa-comment me-1"></i>
                    <strong>Concepto:</strong> <?php echo htmlspecialchars($giro['concepto']); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- REMITENTE -->
        <div class="seccion">
            <div class="seccion-titulo">📤 Remitente</div>
            <div class="info-row">
                <strong>Nombre:</strong>
                <?php echo htmlspecialchars($giro['remitente_nombre'] . ' ' . $giro['remitente_apellido']); ?>
            </div>
            <div class="info-row">
                <strong>DNI:</strong> <?php echo htmlspecialchars($giro['remitente_dni']); ?>
            </div>
            <?php if (!empty($giro['remitente_telefono'])): ?>
                <div class="info-row">
                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($giro['remitente_telefono']); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- DESTINATARIO -->
        <div class="seccion">
            <div class="seccion-titulo">📥 Destinatario</div>
            <div class="info-row">
                <strong>Nombre:</strong>
                <?php echo htmlspecialchars($giro['destinatario_nombre'] . ' ' . $giro['destinatario_apellido']); ?>
            </div>
            <div class="info-row">
                <strong>DNI:</strong> <?php echo htmlspecialchars($giro['destinatario_dni']); ?>
            </div>
            <?php if (!empty($giro['destinatario_telefono'])): ?>
                <div class="info-row">
                    <strong>Teléfono:</strong> <?php echo htmlspecialchars($giro['destinatario_telefono']); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ✅ MONTO A RECIBIR (destacado para el destinatario) -->
        <div class="monto-recibir">
            <div class="label-recibir">Monto a Recibir</div>
            <div class="valor-recibir">
                S/ <?php echo number_format($giro['monto'], 2); ?>
            </div>
            <div class="moneda-recibir">
                <?php echo htmlspecialchars($giro['moneda']); ?> —
                <?php echo number_format($giro['monto'], 2); ?> con 00/100
            </div>
        </div>

        <!-- ✅ NOTA INTERNA (informativa, no descuenta del monto) -->
        <?php if ((float)$giro['comision'] > 0): ?>
            <div class="nota-interna">
                <i class="fas fa-info-circle"></i>
                Servicio de giro: S/ <?php echo number_format($giro['comision'], 2); ?>
                (cargo del operador, no se descuenta del monto a recibir).
            </div>
        <?php endif; ?>

        <!-- ✅ DETALLE ADICIONAL DEL PAGO -->
        <div class="detalle-pago">
            <table>
                <tr>
                    <td>Monto girado:</td>
                    <td class="text-end">S/ <?php echo number_format($giro['monto'], 2); ?></td>
                </tr>
                <tr>
                    <td>Comisión de servicio:</td>
                    <td class="text-end">S/ <?php echo number_format($giro['comision'], 2); ?></td>
                </tr>
                <tr class="total-pagado">
                    <td><strong>Total pagado por el remitente:</strong></td>
                    <td class="text-end"><strong>S/ <?php echo number_format($giro['monto'] + $giro['comision'], 2); ?></strong></td>
                </tr>
            </table>
        </div>

        <!-- DETALLES DEL GIRO -->
        <div class="seccion">
            <div class="seccion-titulo">💱 Detalles del Giro</div>
            <div class="info-row">
                <strong>Moneda:</strong> <?php echo htmlspecialchars($giro['moneda']); ?>
            </div>
            <div class="info-row">
                <strong>Pago Origen:</strong> <?php echo htmlspecialchars($giro['metodo_pago_origen']); ?>
            </div>
            <div class="info-row">
                <strong>Pago Destino:</strong> <?php echo htmlspecialchars($giro['metodo_pago_destino']); ?>
            </div>
            <div class="info-row">
                <strong>Fecha:</strong>
                <?php echo date('d/m/Y H:i:s', strtotime($giro['fecha_registro'])); ?>
            </div>
            <?php if (!empty($giro['fecha_pago'])): ?>
                <div class="info-row">
                    <strong>Pagado:</strong>
                    <?php echo date('d/m/Y H:i:s', strtotime($giro['fecha_pago'])); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- OBSERVACIONES -->
        <?php if (!empty($giro['observaciones'])): ?>
            <div class="seccion">
                <div class="seccion-titulo">📝 Observaciones</div>
                <div class="info-row">
                    <?php echo nl2br(htmlspecialchars($giro['observaciones'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Firmas -->
        <div class="firmas">
            <div class="firma">
                <div class="firma-linea">Remitente</div>
            </div>
            <div class="firma">
                <div class="firma-linea">Autorizado</div>
            </div>
            <div class="firma">
                <div class="firma-linea">Destinatario</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Comprobante generado el <?php echo date('d/m/Y H:i:s'); ?></div>
            <div>Conserve este documento</div>
            <div><?php echo FACTURACION_RAZON_SOCIAL; ?> - RUC <?php echo FACTURACION_RUC; ?></div>
        </div>

    </div>

    <script>
        // Atajo: Ctrl + P para imprimir
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>

</body>
</html>