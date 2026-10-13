<div class="container-fluid px-3 px-md-4 py-3">

    <!-- ============================================================
         HEADER: Título + Acciones principales
         ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-money-bill-transfer text-primary fa-lg"></i>
                </span>
                <span>Detalle de Giro</span>
            </h1>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small ms-5 ps-2">
                <span class="badge bg-dark-subtle text-dark-emphasis font-monospace">
                    <i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($giro['codigo']); ?>
                </span>
                <span class="text-muted">·</span>
                <span><i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($giro['fecha_registro'])); ?></span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>giros/printReceipt/<?php echo $giro['id']; ?>"
               target="_blank" class="btn btn-primary">
                <i class="fas fa-print me-1"></i><span class="d-none d-sm-inline">Imprimir</span>
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEmail">
                <i class="fas fa-envelope me-1"></i><span class="d-none d-sm-inline">Email</span>
            </button>
            <?php if ($giro['estado'] === 'REGISTRADO'): ?>
                <a href="<?php echo BASE_URL; ?>giros/pagar/<?php echo $giro['id']; ?>"
                   class="btn btn-info text-white"
                   onclick='return confirmarPersonalizado(
    "¿Marcar como PAGADO?",
    "El giro <strong><?php echo htmlspecialchars($giro['codigo'], ENT_QUOTES); ?></strong> se marcará como PAGADO.<br>Monto: <strong>S/ <?php echo number_format($monto, 2); ?></strong>",
    "Sí, pagar", "#0dcaf0",
    function() { window.location.href="<?php echo BASE_URL; ?>giros/pagar/<?php echo $giro['id']; ?>"; }
)'>
                    <i class="fas fa-check me-1"></i><span class="d-none d-sm-inline">Pagar</span>
                </a>
                <a href="<?php echo BASE_URL; ?>giros/anular/<?php echo $giro['id']; ?>"
                   class="btn btn-danger"
                   onclick='return confirmarAnular(
    "<?php echo BASE_URL; ?>giros/anular/<?php echo $giro['id']; ?>",
    "Código: <strong><?php echo htmlspecialchars($giro['codigo'], ENT_QUOTES); ?></strong><br>Esta acción no se puede deshacer."
)'>
                    <i class="fas fa-ban me-1"></i><span class="d-none d-sm-inline">Anular</span>
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>giros" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Volver</span>
            </a>
        </div>
    </div>

    <!-- ============================================================
         KPI CARDS: Estado, Monto Girado, Comisión (interno), Total
         ============================================================ -->
    <?php
        $estado_config = [
            'REGISTRADO' => ['bg' => 'warning', 'icon' => 'fa-clock',        'label' => 'Registrado'],
            'PAGADO'     => ['bg' => 'success', 'icon' => 'fa-check-circle', 'label' => 'Pagado'],
            'ANULADO'    => ['bg' => 'danger',  'icon' => 'fa-times-circle', 'label' => 'Anulado'],
        ];
        $cfg = $estado_config[$giro['estado']] ?? ['bg' => 'secondary', 'icon' => 'fa-question', 'label' => $giro['estado']];
        $monto      = (float)$giro['monto'];
        $comision   = (float)$giro['comision'];
        $total      = $monto + $comision;
    ?>

   <div class="row g-2 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body d-flex align-items-center gap-2">
                <div class="kpi-icon-badge bg-<?php echo $cfg['bg']; ?> bg-opacity-10 text-<?php echo $cfg['bg']; ?>">
                    <i class="fas <?php echo $cfg['icon']; ?>"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Estado</div>
                    <div>
                        <span class="badge bg-<?php echo $cfg['bg']; ?>" style="font-size: 0.75rem;">
                            <?php echo $cfg['label']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body d-flex align-items-center gap-2">
                <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Monto Girado</div>
                    <div class="kpi-value text-success">S/ <?php echo number_format($monto, 2); ?></div>
                    <small class="text-muted d-block">Lo que recibe el destinatario</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body d-flex align-items-center gap-2">
                <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">
                        Comisión <span class="badge bg-secondary" style="font-size: 0.55rem; padding: 2px 5px;">Interno</span>
                    </div>
                    <div class="kpi-value text-warning">S/ <?php echo number_format($comision, 2); ?></div>
                    <small class="text-muted d-block">Solo uso administrativo</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 kpi-card">
            <div class="card-body d-flex align-items-center gap-2">
                <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Total a Cobrar</div>
                    <div class="kpi-value text-primary">S/ <?php echo number_format($total, 2); ?></div>
                    <small class="text-muted d-block">Paga el remitente</small>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- ============================================================
         CONTENIDO: Datos (izq) + Resumen monetario (der)
         ============================================================ -->
    <div class="row g-4">

        <!-- COLUMNA IZQUIERDA -->
        <div class="col-lg-8">

            <!-- RUTA DEL GIRO -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-primary text-white border-0 py-3">
                    <i class="fas fa-route me-2"></i><strong>Ruta del Giro</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-5 text-center">
                            <div class="text-muted small text-uppercase" style="letter-spacing: 1px;">Origen</div>
                            <div class="h4 mb-0 text-primary fw-bold">
                                <?php echo htmlspecialchars($giro['lugar_origen'] ?: '—'); ?>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <i class="fas fa-arrow-right fa-2x text-primary"></i>
                        </div>
                        <div class="col-5 text-center">
                            <div class="text-muted small text-uppercase" style="letter-spacing: 1px;">Destino</div>
                            <div class="h4 mb-0 text-danger fw-bold">
                                <?php echo htmlspecialchars($giro['lugar_destino'] ?: '—'); ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($giro['concepto'])): ?>
                        <hr class="my-3">
                        <div class="d-flex align-items-start gap-2 text-muted">
                            <i class="fas fa-comment mt-1"></i>
                            <div>
                                <div class="small fw-bold">Concepto</div>
                                <div><?php echo htmlspecialchars($giro['concepto']); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- REMITENTE + DESTINATARIO -->
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white border-0 py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle me-2"></i>
                                <strong>Remitente</strong>
                                <span class="badge bg-light text-primary ms-auto">Envía</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">
                                <?php echo htmlspecialchars($giro['remitente_nombre'] . ' ' . $giro['remitente_apellido']); ?>
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-id-card text-muted me-2" style="width: 16px;"></i>
                                    <span><?php echo htmlspecialchars($giro['remitente_dni']); ?></span>
                                </li>
                                <?php if (!empty($giro['remitente_telefono'])): ?>
                                    <li class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-phone text-success me-2" style="width: 16px;"></i>
                                        <a href="tel:<?php echo htmlspecialchars($giro['remitente_telefono']); ?>" class="text-decoration-none text-body">
                                            <?php echo htmlspecialchars($giro['remitente_telefono']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-success text-white border-0 py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-check me-2"></i>
                                <strong>Destinatario</strong>
                                <span class="badge bg-light text-success ms-auto">Recibe</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">
                                <?php echo htmlspecialchars($giro['destinatario_nombre'] . ' ' . $giro['destinatario_apellido']); ?>
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-id-card text-muted me-2" style="width: 16px;"></i>
                                    <span><?php echo htmlspecialchars($giro['destinatario_dni']); ?></span>
                                </li>
                                <?php if (!empty($giro['destinatario_telefono'])): ?>
                                    <li class="mb-0 d-flex align-items-center">
                                        <i class="fas fa-phone text-success me-2" style="width: 16px;"></i>
                                        <a href="tel:<?php echo htmlspecialchars($giro['destinatario_telefono']); ?>" class="text-decoration-none text-body">
                                            <?php echo htmlspecialchars($giro['destinatario_telefono']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETALLES DE PAGO -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white border-0 py-3">
                    <i class="fas fa-credit-card me-2"></i><strong>Detalles del Pago</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-coins text-warning mt-1"></i>
                                <div>
                                    <div class="text-muted small">Moneda</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($giro['moneda']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-arrow-up text-success mt-1"></i>
                                <div>
                                    <div class="text-muted small">Pago Origen</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($giro['metodo_pago_origen']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-arrow-down text-danger mt-1"></i>
                                <div>
                                    <div class="text-muted small">Pago Destino</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($giro['metodo_pago_destino']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-calendar-plus text-primary mt-1"></i>
                                <div>
                                    <div class="text-muted small">Fecha Registro</div>
                                    <div class="fw-semibold"><?php echo date('d/m/Y H:i', strtotime($giro['fecha_registro'])); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($giro['fecha_pago'])): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fas fa-calendar-check text-success mt-1"></i>
                                    <div>
                                        <div class="text-muted small">Fecha Pago</div>
                                        <div class="fw-semibold text-success"><?php echo date('d/m/Y H:i', strtotime($giro['fecha_pago'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Resumen + Registrado por -->
        <div class="col-lg-4">

            <!-- Resumen monetario -->
            <div class="card border-0 shadow-sm mb-3 sticky-lg-top" style="top: 80px;">
                <div class="card-header bg-primary text-white border-0 py-3">
                    <i class="fas fa-calculator me-2"></i><strong>Resumen Monetario</strong>
                </div>
                <div class="card-body">

                    <!-- Monto girado (destacado) -->
                    <div class="p-3 rounded-3 bg-success bg-opacity-10 mb-3 border border-success border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-success small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-hand-holding-usd me-1"></i>Monto Girado
                                </div>
                                <small class="text-muted">Recibe el destinatario</small>
                            </div>
                            <h3 class="mb-0 text-success fw-bold">S/ <?php echo number_format($monto, 2); ?></h3>
                        </div>
                    </div>

                    <!-- Comisión (interna) -->
                    <div class="p-3 rounded-3 bg-warning bg-opacity-10 mb-3 border border-warning border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-warning-emphasis small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-percentage me-1"></i>Comisión
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.55rem;">Interno</span>
                                </div>
                                <small class="text-muted">Servicio del operador</small>
                            </div>
                            <h5 class="mb-0 text-warning-emphasis fw-bold">S/ <?php echo number_format($comision, 2); ?></h5>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="p-3 rounded-3 bg-primary bg-opacity-10 border border-primary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-primary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                    <i class="fas fa-receipt me-1"></i>Total a Cobrar
                                </div>
                                <small class="text-muted">Paga el remitente</small>
                            </div>
                            <h3 class="mb-0 text-primary fw-bold">S/ <?php echo number_format($total, 2); ?></h3>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-info d-flex align-items-start mb-0 py-2 small">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div>
                            El destinatario recibe únicamente <strong>S/ <?php echo number_format($monto, 2); ?></strong>.
                            La comisión es un cargo interno del operador.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registrado por -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white border-0 py-3">
                    <i class="fas fa-user-tie me-2"></i><strong>Registrado por</strong>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2">
                        <div class="kpi-icon-badge bg-secondary bg-opacity-10 text-secondary" style="width: 40px; height: 40px; font-size: 1rem;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-bold">
                                <?php echo htmlspecialchars(trim(($giro['usuario_nombre'] ?? '') . ' ' . ($giro['usuario_apellido'] ?? '')) ?: 'Sistema'); ?>
                            </div>
                            <small class="text-muted">Operador</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: ENVIAR POR EMAIL
     ============================================================ -->
<div class="modal fade" id="modalEmail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?php echo BASE_URL; ?>giros/enviarEmail/<?php echo $giro['id']; ?>">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>Enviar Comprobante por Email
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="email_envio" class="form-label fw-bold small">
                            Correo Destino <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email_envio"
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($giro['destinatario_email'] ?? $giro['remitente_email'] ?? ''); ?>"
                                   placeholder="correo@ejemplo.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="mensaje" class="form-label fw-bold small">Mensaje Personalizado (opcional)</label>
                        <textarea name="mensaje" id="mensaje" class="form-control" rows="3"
                                  placeholder="Escriba un mensaje adicional..."></textarea>
                    </div>

                    <div class="alert alert-info d-flex align-items-start mb-0 py-2">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div class="small">
                            Se adjuntará el PDF del comprobante.<br>
                            <strong>Nota:</strong> el comprobante mostrará solo el monto girado (S/ <?php echo number_format($monto, 2); ?>) como monto principal.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Enviar Comprobante
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>