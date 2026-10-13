<div class="container-fluid px-3 px-md-4 py-3">

    <!-- ============================================================
         HEADER: Título + Acciones principales
         ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-warning bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-box text-warning fa-lg"></i>
                </span>
                <span>Detalle de Encomienda</span>
            </h1>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small ms-5 ps-2">
                <span class="badge bg-dark-subtle text-dark-emphasis font-monospace">
                    <i class="fas fa-barcode me-1"></i><?php echo htmlspecialchars($encomienda['guia']); ?>
                </span>
                <span class="text-muted">·</span>
                <span><i class="fas fa-clock me-1"></i><?php echo date('d/m/Y H:i', strtotime($encomienda['fecha_registro'])); ?></span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>encomiendas/printGuide/<?php echo $encomienda['id']; ?>" 
               target="_blank" class="btn btn-primary">
                <i class="fas fa-print me-1"></i><span class="d-none d-sm-inline">Imprimir</span>
            </a>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEnviarEmail">
                <i class="fas fa-envelope me-1"></i><span class="d-none d-sm-inline">Email</span>
            </button>
            <?php if (($_SESSION['rol_id'] ?? 0) == 1 && $encomienda['estado'] != 'ENTREGADO'): ?>
                <a href="#" class="btn btn-outline-danger"
                   onclick="return confirmarEliminar('<?php echo BASE_URL; ?>encomiendas/delete/<?php echo $encomienda['id']; ?>', 'Guía <?php echo htmlspecialchars($encomienda['guia']); ?>')">
                    <i class="fas fa-trash-alt me-1"></i><span class="d-none d-sm-inline">Eliminar</span>
                </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>encomiendas" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i><span class="d-none d-sm-inline">Volver</span>
            </a>
        </div>
    </div>

    <!-- ============================================================
         KPI CARDS: Estado, Peso, Costo, Fecha
         ============================================================ -->
    <?php
        $estado_config = [
            'REGISTRADO'  => ['bg' => 'info',    'icon' => 'fa-clipboard-check', 'label' => 'Registrado'],
            'EN_BODEGA'   => ['bg' => 'warning', 'icon' => 'fa-warehouse',       'label' => 'En Bodega'],
            'EN_TRANSITO' => ['bg' => 'primary', 'icon' => 'fa-truck',           'label' => 'En Tránsito'],
            'ENTREGADO'   => ['bg' => 'success', 'icon' => 'fa-check-circle',    'label' => 'Entregado'],
            'DEVUELTO'    => ['bg' => 'danger',  'icon' => 'fa-undo',            'label' => 'Devuelto'],
        ];
        $cfg = $estado_config[$encomienda['estado']] ?? ['bg' => 'secondary', 'icon' => 'fa-question', 'label' => $encomienda['estado']];
    ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-<?php echo $cfg['bg']; ?> bg-opacity-10 text-<?php echo $cfg['bg']; ?>">
                        <i class="fas <?php echo $cfg['icon']; ?>"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Estado</div>
                        <div class="kpi-value">
                            <span class="badge bg-<?php echo $cfg['bg']; ?> fs-6"><?php echo $cfg['label']; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-weight-hanging"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Peso</div>
                        <div class="kpi-value"><?php echo number_format($encomienda['peso_kg'], 2); ?> <small class="text-muted fs-6">kg</small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Costo Envío</div>
                        <div class="kpi-value text-success">S/ <?php echo number_format($encomienda['costo_envio'], 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Registrada</div>
                        <div class="kpi-value small"><?php echo date('d/m/Y', strtotime($encomienda['fecha_registro'])); ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?php echo date('H:i', strtotime($encomienda['fecha_registro'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         CONTENIDO: Datos (izq) + Acciones (der)
         ============================================================ -->
    <div class="row g-4">

        <!-- COLUMNA IZQUIERDA: Datos -->
        <div class="col-lg-8">

            <!-- Remitente + Destinatario en 2 columnas -->
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
                                <?php echo htmlspecialchars($encomienda['remitente_nombre'] . ' ' . $encomienda['remitente_apellido']); ?>
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-id-card text-muted me-2" style="width: 16px;"></i>
                                    <span><?php echo htmlspecialchars($encomienda['remitente_dni']); ?></span>
                                </li>
                                <?php if (!empty($encomienda['remitente_telefono'])): ?>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-phone text-success me-2" style="width: 16px;"></i>
                                        <a href="tel:<?php echo htmlspecialchars($encomienda['remitente_telefono']); ?>" class="text-decoration-none text-body">
                                            <?php echo htmlspecialchars($encomienda['remitente_telefono']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (!empty($encomienda['remitente_email'])): ?>
                                    <li class="mb-0 d-flex align-items-center text-truncate">
                                        <i class="fas fa-envelope text-primary me-2" style="width: 16px;"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($encomienda['remitente_email']); ?>" class="text-decoration-none text-body text-truncate">
                                            <?php echo htmlspecialchars($encomienda['remitente_email']); ?>
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
                                <?php echo htmlspecialchars($encomienda['destinatario_nombre'] . ' ' . $encomienda['destinatario_apellido']); ?>
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="fas fa-id-card text-muted me-2" style="width: 16px;"></i>
                                    <span><?php echo htmlspecialchars($encomienda['destinatario_dni']); ?></span>
                                </li>
                                <?php if (!empty($encomienda['destinatario_telefono'])): ?>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="fas fa-phone text-success me-2" style="width: 16px;"></i>
                                        <a href="tel:<?php echo htmlspecialchars($encomienda['destinatario_telefono']); ?>" class="text-decoration-none text-body">
                                            <?php echo htmlspecialchars($encomienda['destinatario_telefono']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php if (!empty($encomienda['destinatario_email'])): ?>
                                    <li class="mb-0 d-flex align-items-center text-truncate">
                                        <i class="fas fa-envelope text-primary me-2" style="width: 16px;"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($encomienda['destinatario_email']); ?>" class="text-decoration-none text-body text-truncate">
                                            <?php echo htmlspecialchars($encomienda['destinatario_email']); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles del Envío -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark text-white border-0 py-3">
                    <i class="fas fa-truck me-2"></i><strong>Detalles del Envío</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-route text-primary mt-1"></i>
                                <div>
                                    <div class="text-muted small">Ruta</div>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars($encomienda['origen']); ?>
                                        <i class="fas fa-arrow-right mx-1 text-muted small"></i>
                                        <?php echo htmlspecialchars($encomienda['destino']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-bus text-secondary mt-1"></i>
                                <div>
                                    <div class="text-muted small">Bus</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($encomienda['placa']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-calendar-day text-primary mt-1"></i>
                                <div>
                                    <div class="text-muted small">Fecha del Viaje</div>
                                    <div class="fw-semibold"><?php echo date('d/m/Y H:i', strtotime($encomienda['fecha_salida'])); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-cube text-info mt-1"></i>
                                <div>
                                    <div class="text-muted small">Dimensiones</div>
                                    <div class="fw-semibold"><?php echo !empty($encomienda['dimensiones']) ? htmlspecialchars($encomienda['dimensiones']) : '—'; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-dollar-sign text-success mt-1"></i>
                                <div>
                                    <div class="text-muted small">Valor Declarado</div>
                                    <div class="fw-semibold">S/ <?php echo number_format($encomienda['valor_declarado'] ?? 0, 2); ?></div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($encomienda['fecha_entrega'])): ?>
                            <div class="col-md-6">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fas fa-check-circle text-success mt-1"></i>
                                    <div>
                                        <div class="text-muted small">Entregada</div>
                                        <div class="fw-semibold text-success"><?php echo date('d/m/Y H:i', strtotime($encomienda['fecha_entrega'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($encomienda['descripcion'])): ?>
                        <hr class="my-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-comment text-muted mt-1"></i>
                            <div>
                                <div class="text-muted small mb-1">Descripción del contenido</div>
                                <div class="fw-medium"><?php echo nl2br(htmlspecialchars($encomienda['descripcion'])); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($encomienda['observaciones'])): ?>
                        <hr class="my-3">
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-info-circle text-muted mt-1"></i>
                            <div>
                                <div class="text-muted small mb-1">Observaciones</div>
                                <div class="fw-medium"><?php echo nl2br(htmlspecialchars($encomienda['observaciones'])); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info de registro -->
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="fas fa-user-cog"></i>
                        <span>Registrado por:</span>
                        <strong class="text-body"><?php echo htmlspecialchars($encomienda['registrado_por'] ?? 'Sistema'); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: Acciones -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top: 80px;">
                <div class="card-header bg-secondary text-white border-0 py-3">
                    <i class="fas fa-cog me-2"></i><strong>Acciones</strong>
                </div>
                <div class="card-body">

                    <?php if ($encomienda['estado'] != 'ENTREGADO' && $encomienda['estado'] != 'DEVUELTO'): ?>
                        <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                            <i class="fas fa-arrow-right me-1"></i>Cambiar Estado
                        </div>
                        <div class="d-grid gap-2 mb-3">
                            <?php if ($encomienda['estado'] == 'REGISTRADO'): ?>
                                <a href="#" class="btn btn-outline-warning"
                                   onclick="return confirmarPersonalizado('¿Marcar en bodega?', 'La encomienda estará lista para su envío.', 'Sí, marcar', '#ffc107', function() { window.location.href='<?php echo BASE_URL; ?>encomiendas/updateStatus/<?php echo $encomienda['id']; ?>?estado=EN_BODEGA'; })">
                                    <i class="fas fa-warehouse me-1"></i>Marcar en Bodega
                                </a>
                            <?php endif; ?>

                            <?php if ($encomienda['estado'] == 'EN_BODEGA'): ?>
                                <a href="#" class="btn btn-outline-primary"
                                   onclick="return confirmarPersonalizado('¿Marcar en tránsito?', 'La encomienda está en camino al destino.', 'Sí, marcar', '#0d6efd', function() { window.location.href='<?php echo BASE_URL; ?>encomiendas/updateStatus/<?php echo $encomienda['id']; ?>?estado=EN_TRANSITO'; })">
                                    <i class="fas fa-truck me-1"></i>Marcar en Tránsito
                                </a>
                            <?php endif; ?>

                            <?php if ($encomienda['estado'] == 'EN_TRANSITO'): ?>
                                <a href="#" class="btn btn-success"
                                   onclick="return confirmarPersonalizado('¿Marcar como entregada?', 'La encomienda será marcada como ENTREGADA.', 'Sí, entregar', '#198754', function() { window.location.href='<?php echo BASE_URL; ?>encomiendas/updateStatus/<?php echo $encomienda['id']; ?>?estado=ENTREGADO'; })">
                                    <i class="fas fa-check-circle me-1"></i>Marcar Entregada
                                </a>
                            <?php endif; ?>

                            <a href="#" class="btn btn-outline-danger"
                               onclick="return confirmarPersonalizado('¿Marcar como devuelta?', 'La encomienda regresará al remitente.', 'Sí, devolver', '#dc3545', function() { window.location.href='<?php echo BASE_URL; ?>encomiendas/updateStatus/<?php echo $encomienda['id']; ?>?estado=DEVUELTO'; })">
                                <i class="fas fa-undo me-1"></i>Marcar Devuelta
                            </a>
                        </div>
                        <hr>
                    <?php else: ?>
                        <div class="alert alert-info d-flex align-items-center mb-3 py-2">
                            <i class="fas fa-info-circle me-2"></i>
                            <small class="mb-0">La encomienda ha finalizado su ciclo.</small>
                        </div>
                    <?php endif; ?>

                    <div class="text-muted small fw-bold text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        <i class="fas fa-tools me-1"></i>Opciones
                    </div>
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>encomiendas/printGuide/<?php echo $encomienda['id']; ?>"
                           target="_blank" class="btn btn-primary">
                            <i class="fas fa-print me-1"></i>Imprimir Guía
                        </a>

                        <?php if (!empty($encomienda['destinatario_email']) || !empty($encomienda['remitente_email'])): ?>
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalEnviarEmail">
                                <i class="fas fa-envelope me-1"></i>Enviar por Email
                            </button>
                        <?php endif; ?>

                        <?php if (($_SESSION['rol_id'] ?? 0) == 1 && $encomienda['estado'] != 'ENTREGADO'): ?>
                            <a href="#" class="btn btn-outline-danger"
                               onclick="return confirmarEliminar('<?php echo BASE_URL; ?>encomiendas/delete/<?php echo $encomienda['id']; ?>', 'Guía <?php echo htmlspecialchars($encomienda['guia']); ?>')">
                                <i class="fas fa-trash-alt me-1"></i>Eliminar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: ENVIAR POR EMAIL
     ============================================================ -->
<div class="modal fade" id="modalEnviarEmail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="<?php echo BASE_URL; ?>encomiendas/enviarEmail/<?php echo $encomienda['id']; ?>">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="fas fa-envelope me-2"></i>Enviar Guía por Email
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
                                   value="<?php echo htmlspecialchars($encomienda['destinatario_email'] ?? $encomienda['remitente_email'] ?? ''); ?>"
                                   placeholder="correo@ejemplo.com" required>
                        </div>
                        <small class="text-muted">Se enviará el PDF con la guía adjunta</small>
                    </div>

                    <div class="mb-3">
                        <label for="mensaje" class="form-label fw-bold small">Mensaje Personalizado (opcional)</label>
                        <textarea name="mensaje" id="mensaje" class="form-control" rows="3"
                                  placeholder="Escriba un mensaje adicional..."></textarea>
                    </div>

                    <div class="alert alert-info d-flex align-items-start mb-0 py-2">
                        <i class="fas fa-info-circle me-2 mt-1"></i>
                        <div class="small">
                            <strong>Se enviará:</strong>
                            <ul class="mb-0 mt-1 ps-3">
                                <li>Guía_<?php echo $encomienda['guia']; ?>.pdf</li>
                                <li>Enlace de rastreo público</li>
                                <li>Código QR para rastreo rápido</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Enviar Guía
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>