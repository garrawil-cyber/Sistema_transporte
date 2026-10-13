<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos para impresión */
        @media print {
            body { background: white; margin: 1.5cm; }
            .no-print { display: none !important; }
            .manifiesto { font-size: 12px; }
            .manifiesto table { width: 100%; border-collapse: collapse; }
            .manifiesto table th, .manifiesto table td { 
                border: 1px solid #333; 
                padding: 6px 10px; 
                text-align: left;
            }
            .manifiesto table th { background: #f0f0f0; }
            .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
        }
        /* Estilos para pantalla */
        .manifiesto { max-width: 1000px; margin: 0 auto; padding: 20px; background: white; }
        .manifiesto h2 { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .manifiesto table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .manifiesto table th { background: #0d6efd; color: white; }
        .manifiesto table th, .manifiesto table td { border: 1px solid #dee2e6; padding: 8px 12px; }
        .manifiesto table tr:nth-child(even) { background: #f8f9fa; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .badge { font-size: 14px; }
        .footer { text-align: center; margin-top: 30px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="manifiesto">
            <!-- Encabezado -->
            <div class="text-center mb-3">
                <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo" height="60" class="mb-2" style="max-width:200px;">
                <h2>MANIFIESTO DE PASAJEROS</h2>
                <p class="text-muted">Documento de control de pasajeros a bordo</p>
            </div>

            <!-- Datos del Viaje -->
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="info-row"><strong>Ruta:</strong> <?php echo htmlspecialchars($data['origen'] . ' → ' . $data['destino']); ?></div>
                    <div class="info-row"><strong>Fecha Salida:</strong> <?php echo date('d/m/Y H:i', strtotime($data['fecha_salida'])); ?></div>
                    <div class="info-row"><strong>Fecha Llegada:</strong> <?php echo date('d/m/Y H:i', strtotime($data['fecha_llegada'])); ?></div>
                </div>
                <div class="col-md-6">
                    <div class="info-row"><strong>Bus:</strong> <?php echo $data['placa']; ?> (<?php echo $data['tipo_servicio']; ?>)</div>
                    <div class="info-row"><strong>Capacidad:</strong> <?php echo $data['capacidad_total']; ?> asientos</div>
                    <div class="info-row"><strong>Conductor:</strong> <?php echo isset($data['usuario_nombre']) ? htmlspecialchars($data['usuario_nombre'] . ' ' . $data['usuario_apellido']) : 'No asignado'; ?></div>
                </div>
            </div>

            <!-- Tabla de Pasajeros -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>N°</th>
                            <th>Asiento</th>
                            <th>Pasajero</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['pasajeros'])): ?>
                            <tr><td colspan="6" class="text-center">No hay pasajeros registrados para este viaje.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data['pasajeros'] as $i => $p): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo $p['numero_asiento']; ?></td>
                                <td><?php echo htmlspecialchars($p['pasajero']); ?></td>
                                <td><?php echo $p['tipo_documento'] . ': ' . $p['numero_documento']; ?></td>
                                <td><?php echo $p['telefono'] ?? '-'; ?></td>
                                <td>S/ <?php echo number_format($p['precio_pagado'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">Total Pasajeros:</th>
                            <th><?php echo $data['total_pasajeros']; ?></th>
                        </tr>
                        <tr>
                            <th colspan="5" class="text-end">Asientos Libres:</th>
                            <th><?php echo $data['asientos_libres']; ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Observaciones / Firma -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div><strong>Observaciones:</strong> _______________________________</div>
                    <div class="mt-2"><strong>Fecha Emisión:</strong> <?php echo date('d/m/Y H:i:s'); ?></div>
                </div>
                <div class="col-md-6 text-end">
                    <div><strong>Firma del Conductor:</strong> _______________________</div>
                    <div class="mt-2"><strong>Sello de la Empresa:</strong></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Este documento es de uso interno y control de pasajeros. <br>
                Empresa de Transportes Ejemplo SAC - RUC: 20561234567</p>
            </div>

            <!-- Botones de acción (solo en pantalla) -->
            <div class="no-print text-center mt-4">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
                <a href="<?php echo BASE_URL; ?>home" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>