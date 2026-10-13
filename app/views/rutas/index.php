<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-route text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Rutas de origen y destino con precios base</p>
    </div>
    <a href="<?php echo BASE_URL; ?>rutas/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nueva Ruta
    </a>
</div>

<!-- Tabla -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Origen</th>
                <th>Destino</th>
                <th class="text-center">Distancia</th>
                <th class="text-center">Duración</th>
                <th class="text-end">Precio Base</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($rutas)): ?>
                <?php foreach ($rutas as $ruta): ?>
                <tr>
                    <td data-label="Origen">
                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                        <strong><?php echo htmlspecialchars($ruta['origen']); ?></strong>
                    </td>
                    <td data-label="Destino">
                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                        <strong><?php echo htmlspecialchars($ruta['destino']); ?></strong>
                    </td>
                    <td data-label="Distancia" class="text-center">
                        <?php echo number_format($ruta['distancia_km'], 2); ?> km
                    </td>
                    <td data-label="Duración" class="text-center">
                        <span class="badge bg-secondary"><?php echo $ruta['duracion_estimada']; ?></span>
                    </td>
                    <td data-label="Precio Base" class="text-end">
                        <strong class="text-success">S/ <?php echo number_format($ruta['precio_base'], 2); ?></strong>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <span class="badge <?php echo $ruta['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo $ruta['activo'] ? 'Activo' : 'Inactivo'; ?>
                        </span>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>rutas/show/<?php echo $ruta['id']; ?>" 
                               class="btn btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>rutas/edit/<?php echo $ruta['id']; ?>" 
                               class="btn btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($ruta['activo']): ?>
                                <a href="#" 
                                   class="btn btn-outline-danger" title="Desactivar"
                                   onclick="return confirmarEliminar('<?php echo BASE_URL; ?>rutas/delete/<?php echo $ruta['id']; ?>', '<?php echo htmlspecialchars($ruta['origen'] . ' → ' . $ruta['destino']); ?>')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php else: ?>
                                <a href="#" 
                                   class="btn btn-outline-success" title="Activar"
                                   onclick="return confirmarPersonalizado('¿Activar ruta?', '¿Deseas activar esta ruta nuevamente?', 'Sí, activar', '#198754', function() { window.location.href='<?php echo BASE_URL; ?>rutas/activar/<?php echo $ruta['id']; ?>'; })">
                                    <i class="fas fa-check"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-route fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay rutas registradas</h5>
                        <a href="<?php echo BASE_URL; ?>rutas/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Crear primera ruta
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

<a href="<?php echo BASE_URL; ?>rutas/create" class="fab-mobile" title="Nueva Ruta">
    <i class="fas fa-plus"></i>
</a>