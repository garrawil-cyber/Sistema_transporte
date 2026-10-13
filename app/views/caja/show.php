<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cash-register me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>caja/comprobante/<?php echo $caja['id']; ?>" class="btn btn-primary" target="_blank">
            <i class="fas fa-print me-1"></i>Imprimir Comprobante
        </a>
        <a href="<?php echo BASE_URL; ?>caja/historial" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- Info general -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="mb-0">Cajero</h6>
                <h5 class="mb-0"><?php echo htmlspecialchars($caja['usuario_nombre']); ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="mb-0">Apertura</h6>
                <h5 class="mb-0"><?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="mb-0">Cierre</h6>
                <h5 class="mb-0"><?php echo date('d/m/Y H:i', strtotime($caja['fecha_cierre'])); ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-<?php echo abs($caja['diferencia']) < 0.01 ? 'success' : 'danger'; ?> text-white">
            <div class="card-body">
                <h6 class="mb-0">Diferencia</h6>
                <h3 class="mb-0">S/ <?php echo number_format($caja['diferencia'], 2); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Resumen financiero -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-pie me-2"></i>Resumen Financiero
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Monto Inicial</td>
                        <td class="text-end">S/ <?php echo number_format($caja['monto_inicial'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Ventas en Efectivo</td>
                        <td class="text-end text-success">+ S/ <?php echo number_format($caja['total_ventas_efectivo'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Encomiendas</td>
                        <td class="text-end text-success">+ S/ <?php echo number_format($caja['total_encomiendas'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Comisiones de Giros</td>
                        <td class="text-end text-success">+ S/ <?php echo number_format($caja['total_giros'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Ingresos Manuales</td>
                        <td class="text-end text-success">+ S/ <?php echo number_format($caja['total_ingresos'], 2); ?></td>
                    </tr>
                    <tr>
                        <td>Egresos Manuales</td>
                        <td class="text-end text-danger">- S/ <?php echo number_format($caja['total_egresos'], 2); ?></td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Monto en Sistema</strong></td>
                        <td class="text-end"><strong>S/ <?php echo number_format($caja['monto_final_sistema'], 2); ?></strong></td>
                    </tr>
                    <tr class="table-info">
                        <td><strong>Monto Real (Arqueo)</strong></td>
                        <td class="text-end"><strong>S/ <?php echo number_format($caja['monto_final_real'], 2); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-credit-card me-2"></i>Otros Métodos de Pago
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Tarjeta</label>
                    <h4 class="text-info">S/ <?php echo number_format($caja['total_ventas_tarjeta'], 2); ?></h4>
                </div>
                <div class="mb-3">
                    <label>Transferencia</label>
                    <h4 class="text-primary">S/ <?php echo number_format($caja['total_ventas_transferencia'], 2); ?></h4>
                </div>
                <div>
                    <label>Total Ventas</label>
                    <h4 class="text-success"><?php echo $caja['total_ventas']; ?> transacciones</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Movimientos -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-list me-2"></i>Movimientos Manuales
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Método</th>
                    <th class="text-end">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($caja['movimientos'])): ?>
                    <?php foreach ($caja['movimientos'] as $m): ?>
                    <tr>
                        <td><?php echo date('H:i', strtotime($m['fecha'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $m['tipo'] == 'INGRESO' ? 'success' : 'danger'; ?>">
                                <?php echo $m['tipo']; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($m['concepto']); ?></td>
                        <td><?php echo $m['metodo_pago']; ?></td>
                        <td class="text-end">
                            <strong>S/ <?php echo number_format($m['monto'], 2); ?></strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-3">Sin movimientos</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detalle de ventas -->
<div class="card mt-3">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-ticket-alt me-2"></i>Detalle de Ventas del Período
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-sm table-striped mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th>Hora</th>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th>Ruta</th>
                        <th>Asiento</th>
                        <th>Pago</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($caja['ventas_detalle'] as $v): ?>
                    <tr>
                        <td><?php echo date('H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td><?php echo $v['numero_comprobante']; ?></td>
                        <td><?php echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']); ?></td>
                        <td><?php echo htmlspecialchars($v['origen'] . ' → ' . $v['destino']); ?></td>
                        <td><?php echo $v['numero_asiento']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $v['metodo_pago']; ?></span></td>
                        <td class="text-end">S/ <?php echo number_format($v['precio_pagado'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($caja['ventas_detalle'])): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Sin ventas en el período</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($caja['observaciones'])): ?>
<div class="alert alert-warning mt-3">
    <strong><i class="fas fa-comment me-1"></i>Observaciones:</strong>
    <?php echo htmlspecialchars($caja['observaciones']); ?>
</div>
<?php endif; ?>