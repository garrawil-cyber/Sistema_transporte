<?php
$viaje = $data['viaje'];
$pasajeros = $data['pasajeros'];
$total = $data['total_asientos'];
$ocupados = $data['ocupados'];
$libres = $data['libres'];
$qrData = $qrData ?? null;
$hash = $hash ?? '';

// Valores con fallback
$empresa_nombre = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'MI EMPRESA';
$empresa_ruc    = defined('FACTURACION_RUC') ? FACTURACION_RUC : '';
$empresa_dir    = defined('FACTURACION_DIRECCION') ? FACTURACION_DIRECCION : '';
$empresa_tel    = defined('FACTURACION_TELEFONO') ? FACTURACION_TELEFONO : '';
$empresa_email  = defined('FACTURACION_EMAIL') ? FACTURACION_EMAIL : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifiesto de Pasajeros - <?php echo htmlspecialchars($viaje['placa']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================================
           FORMATO A4 (impresión profesional)
           ============================================================ */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            font-size: 12px;
            color: #212529;
            background: #e9ecef;
            padding: 20px;
            margin: 0;
            line-height: 1.4;
        }

        .manifiesto-container {
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            padding: 15mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
        }

        /* ============================================================
           ENCABEZADO
           ============================================================ */
        .header-empresa {
            border-bottom: 3px double #0d6efd;
            padding-bottom: 12px;
            margin-bottom: 20px;
            position: relative;
            min-height: 110px;
        }

        .header-empresa h3 {
            color: #0d6efd;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .header-empresa p {
            font-size: 0.85rem;
            margin-bottom: 2px;
        }

        .qr-container {
            position: absolute;
            top: 0;
            right: 0;
            text-align: center;
        }

        .qr-container img {
            width: 100px;
            height: 100px;
            border: 1px solid #dee2e6;
            padding: 3px;
            border-radius: 4px;
            background: white;
        }

        .qr-container .texto-validacion {
            font-size: 0.65rem;
            color: #6c757d;
            margin-top: 3px;
        }

        /* ============================================================
           TÍTULO Y DATOS DEL VIAJE
           ============================================================ */
        .titulo-manifiesto {
            text-align: center;
            font-size: 1.15rem;
            font-weight: 700;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 8px;
            background: linear-gradient(90deg, #e7f1ff 0%, #ffffff 50%, #e7f1ff 100%);
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .info-viaje {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding: 10px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #0d6efd;
        }

        .info-viaje p {
            margin-bottom: 4px;
            font-size: 0.85rem;
        }

        .info-viaje strong {
            color: #495057;
            display: inline-block;
            min-width: 130px;
        }

        /* ============================================================
           RESUMEN DE OCUPACIÓN
           ============================================================ */
        .resumen-ocupacion {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            justify-content: center;
        }

        .badge-ocupacion {
            font-size: 0.85rem;
            padding: 8px 16px;
            font-weight: 600;
            border-radius: 6px;
        }

        /* ============================================================
           TABLA DE PASAJEROS
           ============================================================ */
        .table-pasajeros {
            margin-bottom: 20px;
        }

        .table-pasajeros th {
            background: #212529 !important;
            color: white !important;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            padding: 10px 8px;
            vertical-align: middle;
        }

        .table-pasajeros td {
            font-size: 0.85rem;
            padding: 8px;
            vertical-align: middle;
        }

        .table-pasajeros tbody tr:hover {
            background: #f8f9fa;
        }

        /* ============================================================
           OBSERVACIONES
           ============================================================ */
        .observaciones {
            margin-top: 20px;
            margin-bottom: 50px;
            padding: 12px;
            border: 1px dashed #adb5bd;
            border-radius: 4px;
            min-height: 50px;
        }

        .observaciones strong {
            display: block;
            margin-bottom: 25px;
            color: #495057;
        }

        /* ============================================================
           FIRMAS
           ============================================================ */
        .firmas {
            display: flex;
            justify-content: space-between;
            margin-top: 70px;
            padding-top: 20px;
            page-break-inside: avoid;
        }

        .firma {
            text-align: center;
            width: 30%;
        }

        .firma-linea {
            border-top: 2px solid #212529;
            margin: 45px auto 8px;
            width: 90%;
        }

        .firma strong {
            font-size: 0.9rem;
            display: block;
            margin-bottom: 3px;
        }

        .firma small {
            font-size: 0.75rem;
            color: #6c757d;
        }

        /* ============================================================
           PIE DE PÁGINA
           ============================================================ */
        .footer-manifiesto {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 0.75rem;
            color: #6c757d;
        }

        .footer-manifiesto .texto-validacion {
            font-size: 0.7rem;
            margin-top: 4px;
            word-break: break-all;
        }

        /* ============================================================
           BOTONES (no se imprimen)
           ============================================================ */
        .acciones-manifiesto {
            max-width: 210mm;
            margin: 20px auto 0;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ============================================================
           IMPRESIÓN
           ============================================================ */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
                font-size: 11px;
            }

            .manifiesto-container {
                box-shadow: none;
                border-radius: 0;
                padding: 0;
                max-width: 100%;
            }

            .no-print {
                display: none !important;
            }

            .firma-linea {
                border-top: 1px solid #000;
            }

            .table-pasajeros th {
                background: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .qr-container img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .titulo-manifiesto {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .info-viaje {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge-ocupacion {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                border: 1px solid #333;
            }
        }
    </style>
</head>
<body>

<div class="manifiesto-container">

    <!-- ============================================================
         ENCABEZADO EMPRESA + QR
         ============================================================ -->
    <div class="header-empresa">
        <div class="text-center">
            <h3>
                <i class="fas fa-bus me-2"></i><?php echo htmlspecialchars($empresa_nombre); ?>
            </h3>
            <p class="mb-0">
                <strong>RUC:</strong> <?php echo htmlspecialchars($empresa_ruc); ?>
                <?php if ($empresa_dir): ?>
                    <span class="mx-2">|</span><?php echo htmlspecialchars($empresa_dir); ?>
                <?php endif; ?>
            </p>
            <p class="mb-0">
                <?php if ($empresa_tel): ?>
                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($empresa_tel); ?>
                <?php endif; ?>
                <?php if ($empresa_email): ?>
                    <span class="mx-2">|</span>
                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($empresa_email); ?>
                <?php endif; ?>
            </p>
        </div>

        <!-- QR de validación -->
        <?php if ($qrData): ?>
        <div class="qr-container">
            <img src="<?php echo $qrData; ?>" alt="QR validación">
            <div class="texto-validacion">Escanee para validar</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ============================================================
         TÍTULO
         ============================================================ -->
    <div class="titulo-manifiesto">
        Manifiesto de Pasajeros
    </div>

    <!-- ============================================================
         DATOS DEL VIAJE
         ============================================================ -->
    <div class="info-viaje">
        <div>
            <p><strong>Ruta:</strong> <?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?></p>
            <p><strong>Fecha Salida:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></p>
            <p><strong>Fecha Llegada:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_llegada'])); ?></p>
        </div>
        <div>
            <p><strong>Bus:</strong> <?php echo htmlspecialchars($viaje['placa']); ?></p>
            <p><strong>Marca/Modelo:</strong> <?php echo htmlspecialchars($viaje['marca'] . ' ' . $viaje['modelo']); ?></p>
            <p><strong>Conductor:</strong> <?php echo htmlspecialchars($viaje['conductor_nombre'] ?: 'Sin asignar'); ?></p>
        </div>
    </div>

    <!-- ============================================================
         RESUMEN DE OCUPACIÓN
         ============================================================ -->
    <div class="resumen-ocupacion">
        <span class="badge bg-success badge-ocupacion">
            <i class="fas fa-user-check me-1"></i>Ocupados: <?php echo $ocupados; ?>
        </span>
        <span class="badge bg-warning text-dark badge-ocupacion">
            <i class="fas fa-chair me-1"></i>Libres: <?php echo $libres; ?>
        </span>
        <span class="badge bg-secondary badge-ocupacion">
            <i class="fas fa-users me-1"></i>Total: <?php echo $total; ?>
        </span>
    </div>

    <!-- ============================================================
         TABLA DE PASAJEROS
         ============================================================ -->
    <table class="table table-bordered table-pasajeros">
        <thead>
            <tr>
                <th style="width: 8%;">Asiento</th>
                <th style="width: 30%;">Apellidos y Nombres</th>
                <th style="width: 12%;">Tipo Doc.</th>
                <th style="width: 18%;">N° Documento</th>
                <th style="width: 14%;">Teléfono</th>
                <th style="width: 18%;">Comprobante</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pasajeros)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-info-circle me-2"></i>
                        No hay pasajeros registrados para este viaje.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($pasajeros as $p): ?>
                <tr>
                    <td class="text-center"><strong><?php echo $p['numero_asiento']; ?></strong></td>
                    <td><?php echo htmlspecialchars($p['apellido'] . ' ' . $p['nombre']); ?></td>
                    <td class="text-center"><?php echo $p['tipo_documento']; ?></td>
                    <td><?php echo $p['numero_documento']; ?></td>
                    <td><?php echo $p['telefono'] ?? '-'; ?></td>
                    <td class="text-center"><small><?php echo $p['numero_comprobante']; ?></small></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ============================================================
         OBSERVACIONES
         ============================================================ -->
    <div class="observaciones">
        <strong>Observaciones:</strong>
    </div>

    <!-- ============================================================
         FIRMAS
         ============================================================ -->
    <div class="firmas">
        <div class="firma">
            <div class="firma-linea"></div>
            <strong>Conductor</strong>
            <small><?php echo htmlspecialchars($viaje['conductor_nombre'] ?: '_________________________'); ?></small>
        </div>
        <div class="firma">
            <div class="firma-linea"></div>
            <strong>Coordinador de Viaje</strong>
            <small><?php echo date('d/m/Y H:i'); ?></small>
        </div>
        <div class="firma">
            <div class="firma-linea"></div>
            <strong>Sello de la Empresa</strong>
            <small>&nbsp;</small>
        </div>
    </div>

    <!-- ============================================================
         PIE DE PÁGINA
         ============================================================ -->
    <div class="footer-manifiesto">
        <div>
            <?php echo htmlspecialchars($empresa_nombre); ?> · RUC <?php echo htmlspecialchars($empresa_ruc); ?>
            · Manifiesto generado el <?php echo date('d/m/Y H:i:s'); ?>
        </div>
        <?php if ($hash): ?>
        <div class="texto-validacion">
            <i class="fas fa-link me-1"></i>
            Validación en línea: <?php echo BASE_URL . 'validar/manifiesto/' . $hash; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ============================================================
     BOTONES Y FORMULARIO (no se imprimen)
     ============================================================ -->
<div class="acciones-manifiesto no-print">
    <button class="btn btn-primary" onclick="window.print()">
        <i class="fas fa-print me-1"></i>Imprimir Manifiesto
    </button>
    <a href="<?php echo BASE_URL; ?>manifiesto" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="manifiesto-container no-print" style="margin-top: 15px;">
    <h6 class="mb-3">
        <i class="fas fa-envelope me-2"></i>Enviar este manifiesto por correo
    </h6>
<form method="POST" action="<?php echo BASE_URL; ?>manifiesto/enviarEmail/<?php echo $viaje['viaje_id']; ?>" 
      class="row g-2">
      <input type="hidden" name="viaje_id" value="<?php echo (int)$viaje['viaje_id']; ?>">
        <div class="col-md-6">
            <input type="email" name="email" class="form-control" 
                   placeholder="correo@destinatario.com" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">
                <i class="fas fa-paper-plane me-1"></i>Enviar
            </button>
        </div>
        <div class="col-md-3 text-muted small d-flex align-items-center">
            <i class="fas fa-info-circle me-1"></i>Se adjunta PDF
        </div>
    </form>
</div>

</body>
</html>
