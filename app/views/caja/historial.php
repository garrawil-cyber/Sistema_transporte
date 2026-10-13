<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-history text-danger me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Historial completo de cierres de caja</p>
    </div>
    <div class="btn-group">
        <a href="<?php echo BASE_URL; ?>caja" class="btn btn-primary">
            <i class="fas fa-cash-register me-1"></i>Caja Actual
        </a>
        <a href="<?php echo BASE_URL; ?>reportes/caja?fecha=<?php echo date('Y-m-d'); ?>&usuario_id=<?php echo $_SESSION['user_id']; ?>" 
           target="_blank" class="btn btn-outline-danger">
            <i class="fas fa-file-pdf me-1"></i>Reporte Hoy
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_inicio']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_fin']); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Cajero</label>
                <select name="usuario_id" class="form-select form-select-sm">
                    <option value="">Todos los cajeros</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" 
                                <?php echo ($filtros['usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>caja/historial" class="btn btn-sm btn-secondary">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ✅ Resumen con totales GLOBALES (no solo de la página) -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Cierres</small>
                        <h4 class="mb-0"><?php echo $totales['total_cierres'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-cash-register fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Cuadres Perfectos</small>
                        <h4 class="mb-0 text-success"><?php echo $totales['cuadres_ok'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Con Diferencia</small>
                        <h4 class="mb-0 text-warning"><?php echo $totales['con_diferencia'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-<?php echo ($totales['diferencia_total'] ?? 0) < 0 ? 'danger' : 'info'; ?> bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Diferencia Total</small>
                        <h4 class="mb-0 text-<?php echo ($totales['diferencia_total'] ?? 0) < 0 ? 'danger' : 'info'; ?>">
                            S/ <?php echo number_format($totales['diferencia_total'] ?? 0, 2); ?>
                        </h4>
                    </div>
                    <i class="fas fa-calculator fa-2x text-info opacity-50"></i>
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
                <th>#</th>
                <th>Cajero</th>
                <th>Apertura</th>
                <th>Cierre</th>
                <th class="text-end">Inicial</th>
                <th class="text-end">Sistema</th>
                <th class="text-end">Real</th>
                <th class="text-center">Diferencia</th>
                <th class="text-center">Ventas</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cajas)): ?>
                <?php foreach ($cajas as $c): ?>
                <tr>
                    <td data-label="#">
                        <strong>#<?php echo $c['id']; ?></strong>
                    </td>
                    <td data-label="Cajero">
                        <i class="fas fa-user-circle text-primary me-1"></i>
                        <small><?php echo htmlspecialchars($c['usuario_nombre']); ?></small>
                    </td>
                    <td data-label="Apertura">
                        <small>
                            <?php echo date('d/m/Y', strtotime($c['fecha_apertura'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i', strtotime($c['fecha_apertura'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Cierre">
                        <small>
                            <?php echo date('d/m/Y', strtotime($c['fecha_cierre'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i', strtotime($c['fecha_cierre'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Inicial" class="text-end">
                        S/ <?php echo number_format($c['monto_inicial'], 2); ?>
                    </td>
                    <td data-label="Sistema" class="text-end">
                        S/ <?php echo number_format($c['monto_final_sistema'], 2); ?>
                    </td>
                    <td data-label="Real" class="text-end">
                        S/ <?php echo number_format($c['monto_final_real'], 2); ?>
                    </td>
                    <td data-label="Diferencia" class="text-center">
                        <?php 
                            $diff = $c['diferencia'];
                            $badge = 'success';
                            $icon = 'fa-check';
                            if ($diff > 0.01) { $badge = 'info'; $icon = 'fa-arrow-up'; }
                            if ($diff < -0.01) { $badge = 'danger'; $icon = 'fa-arrow-down'; }
                        ?>
                        <span class="badge bg-<?php echo $badge; ?>">
                            <i class="fas <?php echo $icon; ?> me-1"></i>
                            S/ <?php echo number_format($diff, 2); ?>
                        </span>
                    </td>
                    <td data-label="Ventas" class="text-center">
                        <span class="badge bg-primary"><?php echo $c['total_ventas']; ?></span>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>caja/show/<?php echo $c['id']; ?>" 
                               class="btn btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>caja/comprobante/<?php echo $c['id']; ?>" 
                               target="_blank" class="btn btn-outline-secondary" title="Comprobante">
                                <i class="fas fa-receipt"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/caja?fecha=<?php echo date('Y-m-d', strtotime($c['fecha_apertura'])); ?>&usuario_id=<?php echo $c['usuario_id']; ?>" 
                               target="_blank" class="btn btn-outline-danger" title="PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="fas fa-cash-register fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay cierres registrados</h5>
                        <p class="text-muted small">No se encontraron cierres de caja en el período seleccionado</p>
                        <a href="<?php echo BASE_URL; ?>caja" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-cash-register me-1"></i>Ir a Caja
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
