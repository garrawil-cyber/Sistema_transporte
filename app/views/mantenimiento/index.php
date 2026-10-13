<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-tools text-danger me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Control de mantenimiento preventivo y correctivo</p>
    </div>
    <a href="<?php echo BASE_URL; ?>mantenimiento/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Mantenimiento
    </a>
</div>

<!-- Alertas de próximos mantenimientos -->
<?php if (!empty($proximos)): ?>
<div class="alert alert-warning border-0 shadow-sm mb-3">
    <h6 class="alert-heading">
        <i class="fas fa-bell me-2"></i>Mantenimientos Próximos (30 días)
    </h6>
    <ul class="mb-0 small">
        <?php foreach (array_slice($proximos, 0, 5) as $p): ?>
            <li>
                <strong><?php echo $p['placa']; ?></strong> - 
                <?php echo date('d/m/Y', strtotime($p['fecha_programada'])); ?> - 
                <?php echo htmlspecialchars(substr($p['descripcion'], 0, 50)); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="Placa, descripción o taller..."
                       value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="PREVENTIVO" <?php echo $filtros['tipo'] == 'PREVENTIVO' ? 'selected' : ''; ?>>Preventivo</option>
                    <option value="CORRECTIVO" <?php echo $filtros['tipo'] == 'CORRECTIVO' ? 'selected' : ''; ?>>Correctivo</option>
                    <option value="REVISION" <?php echo $filtros['tipo'] == 'REVISION' ? 'selected' : ''; ?>>Revisión</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="PROGRAMADO" <?php echo $filtros['estado'] == 'PROGRAMADO' ? 'selected' : ''; ?>>Programado</option>
                    <option value="EN_PROCESO" <?php echo $filtros['estado'] == 'EN_PROCESO' ? 'selected' : ''; ?>>En Proceso</option>
                    <option value="COMPLETADO" <?php echo $filtros['estado'] == 'COMPLETADO' ? 'selected' : ''; ?>>Completado</option>
                    <option value="CANCELADO" <?php echo $filtros['estado'] == 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>mantenimiento" class="btn btn-sm btn-secondary">
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
                        <small class="text-muted d-block">Total</small>
                        <h4 class="mb-0"><?php echo $resumen['total'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-tools fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Programados</small>
                        <h4 class="mb-0"><?php echo $resumen['programados'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-calendar-alt fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Completados</small>
                        <h4 class="mb-0"><?php echo $resumen['completados'] ?? 0; ?></h4>
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
                        <small class="text-muted d-block">Total Gastado</small>
                        <h4 class="mb-0 text-danger">S/ <?php echo number_format($resumen['total_gastado'] ?? 0, 2); ?></h4>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-danger opacity-50"></i>
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
                <th>Bus</th>
                <th class="text-center">Tipo</th>
                <th>Fecha Prog.</th>
                <th>Descripción</th>
                <th class="text-end">Costo</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($mantenimientos)): ?>
                <?php foreach ($mantenimientos as $m): ?>
                <tr>
                    <td data-label="Bus">
                        <span class="badge bg-secondary"><?php echo $m['placa']; ?></span>
                        <br><small class="text-muted"><?php echo $m['marca'] . ' ' . $m['modelo']; ?></small>
                    </td>
                    <td data-label="Tipo" class="text-center">
                        <span class="badge bg-<?php 
                            echo $m['tipo'] == 'PREVENTIVO' ? 'info' : 
                                ($m['tipo'] == 'CORRECTIVO' ? 'warning' : 'secondary'); 
                        ?>">
                            <?php echo $m['tipo']; ?>
                        </span>
                    </td>
                    <td data-label="Fecha Prog.">
                        <small>
                            <?php echo $m['fecha_programada'] ? date('d/m/Y', strtotime($m['fecha_programada'])) : '-'; ?>
                        </small>
                        <?php if (!empty($m['fecha_realizada'])): ?>
                            <br><small class="text-success">
                                <i class="fas fa-check me-1"></i><?php echo date('d/m/Y', strtotime($m['fecha_realizada'])); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Descripción">
                        <small><?php echo htmlspecialchars(substr($m['descripcion'], 0, 50)); ?><?php echo strlen($m['descripcion']) > 50 ? '...' : ''; ?></small>
                        <?php if (!empty($m['taller_responsable'])): ?>
                            <br><small class="text-muted"><i class="fas fa-wrench me-1"></i><?php echo htmlspecialchars($m['taller_responsable']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Costo" class="text-end">
                        <strong class="text-danger">S/ <?php echo number_format($m['costo_total'], 2); ?></strong>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <span class="badge bg-<?php 
                            echo $m['estado'] == 'PROGRAMADO' ? 'primary' : 
                                ($m['estado'] == 'EN_PROCESO' ? 'warning' : 
                                ($m['estado'] == 'COMPLETADO' ? 'success' : 'secondary')); 
                        ?>">
                            <?php echo $m['estado']; ?>
                        </span>
                    </td>
<td data-label="Acciones" class="text-center">
    <div class="btn-group btn-group-sm">
        <a href="<?php echo BASE_URL; ?>mantenimiento/show/<?php echo $m['id']; ?>" 
           class="btn btn-outline-info" title="Ver">
            <i class="fas fa-eye"></i>
        </a>
        <a href="<?php echo BASE_URL; ?>mantenimiento/edit/<?php echo $m['id']; ?>" 
           class="btn btn-outline-warning" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
        <?php if ($_SESSION['rol_id'] == 1): ?>
            <a href="#" 
               class="btn btn-outline-danger" title="Eliminar"
               onclick="return confirmarEliminar('<?php echo BASE_URL; ?>mantenimiento/delete/<?php echo $m['id']; ?>', 'Mantenimiento de <strong><?php echo htmlspecialchars($m['placa']); ?></strong>')">
                <i class="fas fa-trash-alt"></i>
            </a>
        <?php endif; ?>
    </div>
</td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-tools fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay mantenimientos registrados</h5>
                        <a href="<?php echo BASE_URL; ?>mantenimiento/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Registrar primer mantenimiento
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

<a href="<?php echo BASE_URL; ?>mantenimiento/create" class="fab-mobile" title="Nuevo Mantenimiento">
    <i class="fas fa-plus"></i>
</a>