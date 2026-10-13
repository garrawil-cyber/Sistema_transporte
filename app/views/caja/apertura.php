<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-cash-register me-2"></i><?php echo $titulo; ?></h1>
</div>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0"><i class="fas fa-lock-open me-2"></i>Apertura de Caja</h4>
            </div>
            <div class="card-body text-center py-4">
                <div class="display-1 text-primary mb-3">
                    <i class="fas fa-cash-register"></i>
                </div>
                
                <h5>Bienvenido(a), <?php echo $_SESSION['nombre']; ?></h5>
                <p class="text-muted mb-4">
                    Para comenzar a registrar ventas, debe abrir su caja con el monto inicial en efectivo.
                </p>

                <form method="POST" action="<?php echo BASE_URL; ?>caja/abrir">
                    <input type="hidden" name="redirect" value="<?php echo isset($redirect) ? htmlspecialchars($redirect) : ''; ?>">
                    
                    <div class="mb-3 text-start">
                        <label for="monto_inicial" class="form-label fw-bold">
                            <i class="fas fa-money-bill-wave text-success me-1"></i>
                            Monto Inicial en Caja (S/)
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">S/</span>
                            <input type="number" step="0.01" name="monto_inicial" id="monto_inicial" 
                                   class="form-control form-control-lg text-center" 
                                   value="0.00" required autofocus>
                        </div>
                        <small class="text-muted">
                            Ingrese el monto de efectivo con el que inicia el día
                        </small>
                    </div>

                    <!-- ✅ NUEVO: Referencia última caja cerrada -->
                    <?php if (!empty($ultima_caja)): ?>
                        <div class="alert alert-light border text-start small">
                            <strong class="d-block mb-1">
                                <i class="fas fa-history me-1 text-muted"></i>Última caja cerrada
                            </strong>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Fecha:</span>
                                <strong><?php echo date('d/m/Y H:i', strtotime($ultima_caja['fecha_cierre'])); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Monto real:</span>
                                <strong>S/ <?php echo number_format($ultima_caja['monto_final_real'], 2); ?></strong>
                            </div>
                            <?php if (abs($ultima_caja['diferencia'] ?? 0) > 0.01): ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Diferencia:</span>
                                    <strong class="text-<?php echo $ultima_caja['diferencia'] > 0 ? 'info' : 'danger'; ?>">
                                        S/ <?php echo number_format($ultima_caja['diferencia'], 2); ?>
                                    </strong>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cuadre:</span>
                                    <strong class="text-success">
                                        <i class="fas fa-check-circle me-1"></i>Perfecto
                                    </strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info text-start">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Importante:</strong> Al abrir la caja, quedará registrado su nombre y la hora de apertura.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-play-circle me-2"></i>Abrir Caja
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3 border-0 shadow-sm">
            <div class="card-body text-center text-muted small">
                <i class="fas fa-clock me-1"></i>
                Fecha y hora actual: <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
            </div>
        </div>
    </div>
</div>
