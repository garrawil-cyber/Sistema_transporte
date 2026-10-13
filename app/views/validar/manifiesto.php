<?php
$viaje = $data['viaje'];
$pasajeros = $data['pasajeros'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .container { max-width: 900px; margin: 0 auto; }
        .header-validacion { background: #0d6efd; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .footer-validacion { background: #e9ecef; padding: 10px; border-radius: 0 0 10px 10px; }
        .badge-valid { background: #198754; color: white; padding: 5px 15px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header-validacion">
        <h3><i class="fas fa-check-circle me-2"></i>VALIDACIÓN DE MANIFIESTO</h3>
        <p class="mb-0">Este documento ha sido verificado electrónicamente</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <p><strong>Empresa:</strong> <?php echo FACTURACION_RAZON_SOCIAL; ?></p>
                    <p><strong>RUC:</strong> <?php echo FACTURACION_RUC; ?></p>
                </div>
                <div class="col-6 text-end">
                    <span class="badge-valid"><i class="fas fa-check me-1"></i>Válido</span>
                    <p class="mt-2 small">Fecha de validación: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6>Viaje</h6>
                    <p><strong>Ruta:</strong> <?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?></p>
                    <p><strong>Salida:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?></p>
                    <p><strong>Llegada:</strong> <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_llegada'])); ?></p>
                </div>
                <div class="col-md-6">
                    <h6>Unidad</h6>
                    <p><strong>Placa:</strong> <?php echo $viaje['placa']; ?></p>
                    <p><strong>Modelo:</strong> <?php echo $viaje['marca'] . ' ' . $viaje['modelo']; ?></p>
                    <p><strong>Conductor:</strong> <?php echo htmlspecialchars($viaje['conductor_nombre']); ?></p>
                </div>
            </div>
            <hr>
            <h6>Pasajeros a bordo (<?php echo count($pasajeros); ?>)</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Asiento</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pasajeros as $p): ?>
                        <tr>
                            <td><?php echo $p['numero_asiento']; ?></td>
                            <td><?php echo htmlspecialchars($p['apellido'] . ' ' . $p['nombre']); ?></td>
                            <td><?php echo $p['tipo_documento'] . ': ' . $p['numero_documento']; ?></td>
                            <td><?php echo $p['numero_comprobante']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($pasajeros)): ?>
                        <tr><td colspan="4" class="text-center">No hay pasajeros registrados</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="footer-validacion text-center text-muted small">
        Este documento es una representación fiel del manifiesto original emitido por <?php echo FACTURACION_RAZON_SOCIAL; ?>.
        <br>Verifique la autenticidad escaneando el código QR impreso.
    </div>
</div>
</body>
</html>