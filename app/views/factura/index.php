<!-- Banner de modo simulación -->
<?php if (defined('FACTURACION_MODO_SIMULACION') && FACTURACION_MODO_SIMULACION): ?>
<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
    <i class="fas fa-flask fa-2x me-3"></i>
    <div class="flex-grow-1">
        <strong>🧪 Modo Simulación Activo</strong>
        <p class="mb-0 small">
            Los comprobantes se generan localmente pero <strong>NO se envían a SUNAT</strong>. 
            Cambia <code>FACTURACION_MODO_SIMULACION</code> a <code>false</code> para emitir realmente.
        </p>
    </div>
</div>
<?php endif; ?>

<!-- Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-file-invoice text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Comprobantes electrónicos emitidos a SUNAT</p>
    </div>
    <a href="<?php echo BASE_URL; ?>ventas" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nueva Venta
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="N° comprobante, cliente o RUC/DNI..."
                       value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="FACTURA" <?php echo ($filtros['tipo'] ?? '') == 'FACTURA' ? 'selected' : ''; ?>>Factura</option>
                    <option value="BOLETA" <?php echo ($filtros['tipo'] ?? '') == 'BOLETA' ? 'selected' : ''; ?>>Boleta</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ACEPTADO" <?php echo ($filtros['estado'] ?? '') == 'ACEPTADO' ? 'selected' : ''; ?>>Aceptado</option>
                    <option value="RECHAZADO" <?php echo ($filtros['estado'] ?? '') == 'RECHAZADO' ? 'selected' : ''; ?>>Rechazado</option>
                    <option value="PENDIENTE" <?php echo ($filtros['estado'] ?? '') == 'PENDIENTE' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="SIMULADO" <?php echo ($filtros['estado'] ?? '') == 'SIMULADO' ? 'selected' : ''; ?>>Simulado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_inicio'] ?? ''; ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>factura" class="btn btn-sm btn-secondary">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Emitidos</small>
                        <h4 class="mb-0"><?php echo $resumen['total'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-file-invoice fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Aceptados</small>
                        <h4 class="mb-0 text-success"><?php echo $resumen['aceptados'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Rechazados</small>
                        <h4 class="mb-0 text-danger"><?php echo $resumen['rechazados'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Monto Total</small>
                        <h4 class="mb-0 text-info">S/ <?php echo number_format($resumen['monto_total'] ?? 0, 2); ?></h4>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>N° Comprobante</th>
                <th>Tipo</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Fecha</th>
                <th class="text-end">Total</th>
                <th class="text-center">Estado SUNAT</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($comprobantes)): ?>
                <?php foreach ($comprobantes as $c): ?>
                <tr>
                    <td data-label="N° Comprobante">
                        <strong><?php echo $c['numero_completo']; ?></strong>
                    </td>
                    <td data-label="Tipo">
                        <span class="badge bg-<?php echo $c['tipo'] == 'FACTURA' ? 'primary' : 'info'; ?>">
                            <?php echo $c['tipo']; ?>
                        </span>
                    </td>
                    <td data-label="Cliente">
                        <small><?php echo htmlspecialchars($c['cliente_nombre']); ?></small>
                    </td>
                    <td data-label="Documento">
                        <small><?php echo $c['cliente_numero_doc']; ?></small>
                    </td>
                    <td data-label="Fecha">
                        <small><?php echo date('d/m/Y H:i', strtotime($c['fecha_emision'])); ?></small>
                    </td>
                    <td data-label="Total" class="text-end">
                        <strong class="text-success">S/ <?php echo number_format($c['total'], 2); ?></strong>
                    </td>
                    <td data-label="Estado SUNAT" class="text-center">
                        <span class="badge bg-<?php 
                            echo $c['estado'] == 'ACEPTADO' ? 'success' : 
                                ($c['estado'] == 'RECHAZADO' ? 'danger' : 
                                ($c['estado'] == 'SIMULADO' ? 'warning' : 'secondary')); 
                        ?>">
                            <?php if ($c['estado'] == 'SIMULADO'): ?>
                                <i class="fas fa-flask me-1"></i>
                            <?php endif; ?>
                            <?php echo $c['estado']; ?>
                        </span>
                        <?php if (!empty($c['cdr_code'])): ?>
                            <br><small class="text-muted">Cód: <?php echo $c['cdr_code']; ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>factura/show/<?php echo $c['id']; ?>" 
                               class="btn btn-outline-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>factura/imprimir/<?php echo $c['id']; ?>" 
                               target="_blank" class="btn btn-outline-secondary" title="Imprimir">
                                <i class="fas fa-print"></i>
                            </a>
                            <?php if (!empty($c['xml'])): ?>
                                <a href="<?php echo BASE_URL; ?>factura/descargarXML/<?php echo $c['id']; ?>" 
                                   class="btn btn-outline-primary" title="Descargar XML">
                                    <i class="fas fa-download"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-file-invoice fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay comprobantes emitidos</h5>
                        <p class="text-muted small">Los comprobantes se generan desde el módulo de Ventas</p>
                        <a href="<?php echo BASE_URL; ?>ventas" class="btn btn-primary btn-sm">
                            <i class="fas fa-ticket-alt me-1"></i>Ir a Ventas
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($paginator)): ?>
    <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
<?php endif; ?>
