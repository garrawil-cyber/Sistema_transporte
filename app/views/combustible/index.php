<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-gas-pump text-warning me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Control de consumo y gastos de combustible</p>
    </div>
    <a href="<?php echo BASE_URL; ?>combustible/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nueva Carga
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="Placa, estación o factura..."
                       value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Bus</label>
                <select name="bus_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($buses as $b): ?>
                        <option value="<?php echo $b['id']; ?>" 
                                <?php echo ($filtros['bus_id'] == $b['id']) ? 'selected' : ''; ?>>
                            <?php echo $b['placa']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_fin']; ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>combustible" class="btn btn-sm btn-secondary">
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
                        <small class="text-muted d-block">Total Cargas</small>
                        <h4 class="mb-0"><?php echo $resumen['total_cargas'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-gas-pump fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Galones</small>
                        <h4 class="mb-0"><?php echo number_format($resumen['total_galones'] ?? 0, 2); ?></h4>
                    </div>
                    <i class="fas fa-tint fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Gastado</small>
                        <h4 class="mb-0 text-success">S/ <?php echo number_format($resumen['total_gastado'] ?? 0, 2); ?></h4>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Rendimiento Prom.</small>
                        <h4 class="mb-0 text-warning"><?php echo number_format($resumen['rendimiento_promedio'] ?? 0, 2); ?> km/gl</h4>
                    </div>
                    <i class="fas fa-tachometer-alt fa-2x text-warning opacity-50"></i>
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
                <th>Fecha</th>
                <th>Bus</th>
                <th>Conductor</th>
                <th class="text-center">Combustible</th>
                <th class="text-center">Galones</th>
                <th class="text-end">Precio/Gl</th>
                <th class="text-end">Total</th>
                <th class="text-center">Km/Gl</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cargas)): ?>
                <?php foreach ($cargas as $c): ?>
                <tr>
                    <td data-label="Fecha">
                        <small>
                            <?php echo date('d/m/Y', strtotime($c['fecha_carga'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i', strtotime($c['fecha_carga'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Bus">
                        <span class="badge bg-secondary"><?php echo $c['placa']; ?></span>
                        <br><small class="text-muted"><?php echo $c['marca'] . ' ' . $c['modelo']; ?></small>
                    </td>
                    <td data-label="Conductor">
                        <?php if (!empty($c['conductor_nombre'])): ?>
                            <small><i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($c['conductor_nombre']); ?></small>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Combustible" class="text-center">
                        <span class="badge bg-info"><?php echo $c['tipo_combustible']; ?></span>
                    </td>
                    <td data-label="Galones" class="text-center">
                        <strong><?php echo number_format($c['cantidad_galones'], 2); ?></strong>
                    </td>
                    <td data-label="Precio/Gl" class="text-end">
                        S/ <?php echo number_format($c['precio_galon'], 2); ?>
                    </td>
                    <td data-label="Total" class="text-end">
                        <strong class="text-success">S/ <?php echo number_format($c['precio_total'], 2); ?></strong>
                    </td>
                    <td data-label="Km/Gl" class="text-center">
                        <?php if (!empty($c['rendimiento_km_galon'])): ?>
                            <span class="badge bg-<?php 
                                echo $c['rendimiento_km_galon'] >= 5 ? 'success' : 
                                    ($c['rendimiento_km_galon'] >= 3 ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo number_format($c['rendimiento_km_galon'], 2); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acciones" class="text-center">
    <div class="btn-group btn-group-sm">
        <a href="<?php echo BASE_URL; ?>combustible/show/<?php echo $c['id']; ?>" 
           class="btn btn-outline-info" title="Ver">
            <i class="fas fa-eye"></i>
        </a>
        <?php if ($_SESSION['rol_id'] == 1): ?>
            <a href="#" 
               class="btn btn-outline-danger" title="Eliminar"
               onclick="return confirmarEliminar('<?php echo BASE_URL; ?>combustible/delete/<?php echo $c['id']; ?>', 'Carga #<?php echo $c['id']; ?> de <?php echo htmlspecialchars($c['placa']); ?> por S/ <?php echo number_format($c['precio_total'], 2); ?>')">
                <i class="fas fa-trash-alt"></i>
            </a>
        <?php endif; ?>
    </div>
</td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-gas-pump fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay cargas registradas</h5>
                        <a href="<?php echo BASE_URL; ?>combustible/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Registrar primera carga
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

<a href="<?php echo BASE_URL; ?>combustible/create" class="fab-mobile" title="Nueva Carga">
    <i class="fas fa-plus"></i>
</a>