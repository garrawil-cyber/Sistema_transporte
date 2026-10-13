<div class="container-fluid px-3 px-md-4 py-3">

    <!-- ============================================================
         HEADER
         ============================================================ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-user-circle text-primary fa-lg"></i>
                </span>
                <span><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></span>
            </h1>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small ms-5 ps-2">
                <span class="badge bg-info"><?php echo htmlspecialchars($cliente['tipo_documento']); ?></span>
                <span class="font-monospace"><?php echo htmlspecialchars($cliente['numero_documento']); ?></span>
                <span class="text-muted">·</span>
                <span><i class="fas fa-hashtag me-1"></i>ID #<?php echo $cliente['id']; ?></span>
                <?php if (($cliente['total_compras'] ?? 0) >= 10): ?>
                    <span class="badge bg-warning text-dark">
                        <i class="fas fa-star me-1"></i>Cliente frecuente
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>clientes/edit/<?php echo $cliente['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i>Editar
            </a>
            <a href="#" class="btn btn-outline-danger"
               onclick='return confirmarEliminar("<?php echo BASE_URL . 'clientes/delete/' . $cliente['id']; ?>", "<?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'], ENT_QUOTES, 'UTF-8'); ?>")'>
                <i class="fas fa-trash-alt me-1"></i>Eliminar
            </a>
            <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- ============================================================
         KPI CARDS
         ============================================================ -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Total Compras</div>
                        <div class="kpi-value text-primary"><?php echo (int)($cliente['total_compras'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Total Gastado</div>
                        <div class="kpi-value text-success">S/ <?php echo number_format($cliente['total_gastado'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Compras del Mes</div>
                        <div class="kpi-value text-info"><?php echo (int)($cliente['compras_ultimo_mes'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Gasto del Mes</div>
                        <div class="kpi-value text-warning">S/ <?php echo number_format($cliente['gasto_ultimo_mes'] ?? 0, 2); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         CONTENIDO: Datos + Historial
         ============================================================ -->
    <div class="row g-3">

        <!-- Datos del cliente -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-lg-top" style="top: 80px;">
                <div class="card-header bg-primary text-white border-0 py-3">
                    <i class="fas fa-id-card me-2"></i><strong>Datos del Cliente</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-user text-muted mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted small">Nombre completo</div>
                                <div class="fw-semibold"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-id-card text-muted mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted small">Documento</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-info"><?php echo htmlspecialchars($cliente['tipo_documento']); ?></span>
                                    <?php echo htmlspecialchars($cliente['numero_documento']); ?>
                                </div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-phone text-success mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted small">Teléfono</div>
                                <?php if (!empty($cliente['telefono'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none fw-semibold text-body">
                                        <?php echo htmlspecialchars($cliente['telefono']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-envelope text-primary mt-1" style="width: 18px;"></i>
                            <div class="min-w-0">
                                <div class="text-muted small">Email</div>
                                <?php if (!empty($cliente['email'])): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-decoration-none fw-semibold text-body text-truncate d-block">
                                        <?php echo htmlspecialchars($cliente['email']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="mb-0 d-flex align-items-start gap-2">
                            <i class="fas fa-home text-muted mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted small">Dirección</div>
                                <div class="fw-semibold"><?php echo !empty($cliente['direccion']) ? htmlspecialchars($cliente['direccion']) : '<span class="text-muted">—</span>'; ?></div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Historial de compras -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-history me-2"></i><strong>Últimas Compras</strong>
                    </div>
                    <span class="badge bg-light text-dark"><?php echo count($historial ?? []); ?> registros</span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($historial)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Ruta</th>
                                        <th class="text-center">Asiento</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $compra): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small"><?php echo date('d/m/Y', strtotime($compra['fecha_venta'])); ?></span>
                                                <small class="text-muted"><?php echo date('H:i', strtotime($compra['fecha_venta'])); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold small">
                                                <?php echo htmlspecialchars($compra['origen']); ?>
                                                <i class="fas fa-arrow-right mx-1 text-muted small"></i>
                                                <?php echo htmlspecialchars($compra['destino']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($compra['numero_asiento']); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">S/ <?php echo number_format($compra['precio_pagado'], 2); ?></strong>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-4x text-muted mb-3 d-block opacity-50"></i>
                            <h5 class="text-muted">Sin compras registradas</h5>
                            <p class="text-muted small mb-0">Este cliente aún no ha realizado compras</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>