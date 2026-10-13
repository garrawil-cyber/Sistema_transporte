<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-gas-pump text-warning me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <?php if ($_SESSION['rol_id'] == 1): ?>
            <a href="<?php echo BASE_URL; ?>combustible/delete/<?php echo $carga['id']; ?>" 
               class="btn btn-danger"
               onclick="return confirm('¿Eliminar esta carga?')">
                <i class="fas fa-trash-alt me-1"></i>Eliminar
            </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>combustible" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-receipt me-2"></i>Comprobante de Carga #<?php echo $carga['id']; ?>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">BUS</h6>
                        <p class="h5 mb-0"><?php echo $carga['placa']; ?></p>
                        <small class="text-muted"><?php echo $carga['marca'] . ' ' . $carga['modelo']; ?></small>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6 class="text-muted">FECHA DE CARGA</h6>
                        <p class="h5 mb-0"><?php echo date('d/m/Y', strtotime($carga['fecha_carga'])); ?></p>
                        <small class="text-muted"><?php echo date('H:i:s', strtotime($carga['fecha_carga'])); ?></small>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-muted">Tipo de Combustible</h6>
                        <span class="badge bg-info fs-6"><?php echo $carga['tipo_combustible']; ?></span>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Cantidad</h6>
                        <p class="h5 mb-0"><?php echo number_format($carga['cantidad_galones'], 2); ?> gal</p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Precio por Galón</h6>
                        <p class="h5 mb-0">S/ <?php echo number_format($carga['precio_galon'], 2); ?></p>
                    </div>
                </div>

                <div class="alert alert-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong class="fs-5"><i class="fas fa-money-bill-wave me-2"></i>TOTAL PAGADO:</strong>
                        <h3 class="mb-0">S/ <?php echo number_format($carga['precio_total'], 2); ?></h3>
                    </div>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <h6 class="text-muted">Kilometraje Actual</h6>
                        <p class="mb-0"><?php echo $carga['kilometraje_actual'] ? number_format($carga['kilometraje_actual']) . ' km' : '-'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Kilometraje Anterior</h6>
                        <p class="mb-0"><?php echo $carga['kilometraje_anterior'] ? number_format($carga['kilometraje_anterior']) . ' km' : '-'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Rendimiento</h6>
                        <?php if (!empty($carga['rendimiento_km_galon'])): ?>
                            <span class="badge bg-<?php echo $carga['rendimiento_km_galon'] >= 5 ? 'success' : ($carga['rendimiento_km_galon'] >= 3 ? 'warning' : 'danger'); ?> fs-6">
                                <?php echo number_format($carga['rendimiento_km_galon'], 2); ?> km/gal
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Estación de Servicio</h6>
                        <p class="mb-0"><?php echo $carga['estacion_servicio'] ?: '-'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">N° Factura</h6>
                        <p class="mb-0"><?php echo $carga['numero_factura'] ?: '-'; ?></p>
                    </div>
                </div>

                <?php if (!empty($carga['observaciones'])): ?>
                    <hr>
                    <h6 class="text-muted">Observaciones</h6>
                    <p><?php echo nl2br(htmlspecialchars($carga['observaciones'])); ?></p>
                <?php endif; ?>

                <hr>
                <div class="row small text-muted">
                    <div class="col-md-6">
                        <i class="fas fa-user me-1"></i>Registrado por: <strong><?php echo $carga['usuario_nombre']; ?></strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <i class="fas fa-clock me-1"></i>Creado: <?php echo date('d/m/Y H:i', strtotime($carga['created_at'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>