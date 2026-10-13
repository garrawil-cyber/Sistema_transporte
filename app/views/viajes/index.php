<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-calendar-alt text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Programación de viajes y asignación de buses</p>
    </div>
    <a href="<?php echo BASE_URL; ?>viajes/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Viaje
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Origen</label>
                <input type="text" name="origen" class="form-control form-control-sm" 
                       placeholder="Ej: Lima"
                       value="<?php echo htmlspecialchars($filtros['origen'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Destino</label>
                <input type="text" name="destino" class="form-control form-control-sm" 
                       placeholder="Ej: Arequipa"
                       value="<?php echo htmlspecialchars($filtros['destino'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Fecha</label>
                <input type="date" name="fecha" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="PROGRAMADO" <?php echo ($filtros['estado'] ?? '') == 'PROGRAMADO' ? 'selected' : ''; ?>>Programado</option>
                    <option value="EN_CURSO" <?php echo ($filtros['estado'] ?? '') == 'EN_CURSO' ? 'selected' : ''; ?>>En Curso</option>
                    <option value="COMPLETADO" <?php echo ($filtros['estado'] ?? '') == 'COMPLETADO' ? 'selected' : ''; ?>>Completado</option>
                    <option value="CANCELADO" <?php echo ($filtros['estado'] ?? '') == 'CANCELADO' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-sm btn-secondary">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Ruta</th>
                <th>Bus</th>
                <th>Conductor</th>
                <th>Salida</th>
                <th class="text-end">Precio</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Pasajeros</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($viajes)): ?>
                <?php foreach ($viajes as $viaje): ?>
                <tr>
                    <td data-label="Ruta">
                        <strong><?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?></strong>
                    </td>
                    <td data-label="Bus">
                        <span class="badge bg-secondary"><?php echo $viaje['placa']; ?></span>
                        <br><small class="text-muted"><?php echo $viaje['tipo_servicio']; ?></small>
                    </td>
                    <td data-label="Conductor">
                        <?php if (!empty($viaje['conductor_nombre'])): ?>
                            <i class="fas fa-user-circle text-success me-1"></i>
                            <small><?php echo htmlspecialchars($viaje['conductor_nombre']); ?></small>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Sin asignar</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Salida">
                        <small><?php echo date('d/m/Y', strtotime($viaje['fecha_salida'])); ?><br>
                        <span class="text-muted"><?php echo date('H:i', strtotime($viaje['fecha_salida'])); ?></span></small>
                    </td>
                    <td data-label="Precio" class="text-end">
                        <strong class="text-success">S/ <?php echo number_format($viaje['precio_final'], 2); ?></strong>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <?php 
                            $badge = 'secondary';
                            if ($viaje['estado'] == 'PROGRAMADO') $badge = 'primary';
                            if ($viaje['estado'] == 'EN_CURSO') $badge = 'warning';
                            if ($viaje['estado'] == 'COMPLETADO') $badge = 'success';
                            if ($viaje['estado'] == 'CANCELADO') $badge = 'danger';
                        ?>
                        <span class="badge bg-<?php echo $badge; ?>"><?php echo $viaje['estado']; ?></span>
                    </td>
                    <td data-label="Pasajeros" class="text-center">
                        <span class="badge bg-info"><?php echo $viaje['pasajeros_abordo'] ?? 0; ?></span>
                        <br><small class="text-muted">Libres: <?php echo $viaje['asientos_libres'] ?? 0; ?></small>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>viajes/show/<?php echo $viaje['id']; ?>" 
                               class="btn btn-outline-info" title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>viajes/edit/<?php echo $viaje['id']; ?>" 
                               class="btn btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-calendar-alt fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay viajes registrados</h5>
                        <a href="<?php echo BASE_URL; ?>viajes/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Programar primer viaje
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

<a href="<?php echo BASE_URL; ?>viajes/create" class="fab-mobile" title="Nuevo Viaje">
    <i class="fas fa-plus"></i>
</a>