<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-receipt me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <a href="<?php echo BASE_URL; ?>caja" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <!-- Encabezado -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h3 class="text-primary mb-1"><?php echo FACTURACION_RAZON_SOCIAL; ?></h3>
                    <p class="mb-1 text-muted small">RUC: <?php echo FACTURACION_RUC ?? '20561234567'; ?></p>
                    <p class="mb-1 text-muted small"><?php echo FACTURACION_DIRECCION ?? 'Av. Principal 123'; ?></p>
                    <hr>
                    <h4>COMPROBANTE DE CIERRE DE CAJA</h4>
                    <p class="mb-0 small">N° <?php echo str_pad($caja['id'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>

                <!-- Datos generales -->
                <div class="row mb-3">
                    <div class="col-6">
                        <p class="mb-1"><strong>Cajero:</strong> <?php echo htmlspecialchars($caja['usuario_nombre']); ?></p>
                        <p class="mb-1"><strong>Apertura:</strong> <?php echo date('d/m/Y H:i', strtotime($caja['fecha_apertura'])); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Cierre:</strong> <?php echo date('d/m/Y H:i', strtotime($caja['fecha_cierre'])); ?></p>
                        <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-success">CERRADA</span></p>
                    </div>
                </div>

                <!-- Resumen financiero -->
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Monto (S/)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Monto Inicial</td>
                            <td class="text-end"><?php echo number_format($caja['monto_inicial'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>+ Ventas en Efectivo</td>
                            <td class="text-end text-success"><?php echo number_format($caja['total_ventas_efectivo'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>+ Encomiendas</td>
                            <td class="text-end text-success"><?php echo number_format($caja['total_encomiendas'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>+ Comisiones de Giros</td>
                            <td class="text-end text-success"><?php echo number_format($caja['total_giros'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>+ Ingresos Manuales</td>
                            <td class="text-end text-success"><?php echo number_format($caja['total_ingresos'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>- Egresos Manuales</td>
                            <td class="text-end text-danger"><?php echo number_format($caja['total_egresos'], 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-primary">
                        <tr>
                            <th>MONTO EN SISTEMA</th>
                            <th class="text-end">S/ <?php echo number_format($caja['monto_final_sistema'], 2); ?></th>
                        </tr>
                        <tr>
                            <th>MONTO REAL (Arqueo)</th>
                            <th class="text-end">S/ <?php echo number_format($caja['monto_final_real'], 2); ?></th>
                        </tr>
                        <tr class="<?php echo abs($caja['diferencia']) < 0.01 ? 'table-success' : 'table-danger'; ?>">
                            <th>DIFERENCIA</th>
                            <th class="text-end">
                                S/ <?php echo number_format($caja['diferencia'], 2); ?>
                                <?php if (abs($caja['diferencia']) < 0.01): ?>
                                    ✅
                                <?php elseif ($caja['diferencia'] > 0): ?>
                                    (Sobra)
                                <?php else: ?>
                                    (Falta)
                                <?php endif; ?>
                            </th>
                        </tr>
                    </tfoot>
                </table>

                <!-- Detalle por método de pago -->
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center">
                            <small class="text-muted">Efectivo</small>
                            <h6 class="mb-0">S/ <?php echo number_format($caja['total_ventas_efectivo'], 2); ?></h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center">
                            <small class="text-muted">Tarjeta</small>
                            <h6 class="mb-0">S/ <?php echo number_format($caja['total_ventas_tarjeta'], 2); ?></h6>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center">
                            <small class="text-muted">Transferencia</small>
                            <h6 class="mb-0">S/ <?php echo number_format($caja['total_ventas_transferencia'], 2); ?></h6>
                        </div>
                    </div>
                </div>

                <?php if (!empty($caja['observaciones'])): ?>
                <div class="alert alert-warning mt-3">
                    <strong><i class="fas fa-comment me-1"></i>Observaciones:</strong>
                    <?php echo htmlspecialchars($caja['observaciones']); ?>
                </div>
                <?php endif; ?>

                <!-- Firmas -->
                <div class="row mt-5">
                    <div class="col-6 text-center">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 40px auto 5px;"></div>
                        <p class="mb-0"><strong>Cajero</strong></p>
                        <small><?php echo htmlspecialchars($caja['usuario_nombre']); ?></small>
                    </div>
                    <div class="col-6 text-center">
                        <div style="border-top: 1px solid #000; width: 200px; margin: 40px auto 5px;"></div>
                        <p class="mb-0"><strong>Supervisor</strong></p>
                        <small>Firma y Sello</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, nav, footer, .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>