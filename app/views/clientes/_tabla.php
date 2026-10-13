<?php
/**
 * Vista parcial: tabla de clientes
 * Se carga vía AJAX desde ClienteController::buscarAjax()
 */
?>
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th style="width: 60px;">ID</th>
                <th style="width: 150px;">Documento</th>
                <th>Nombres y Apellidos</th>
                <th style="width: 130px;">Teléfono</th>
                <th>Email</th>
                <th style="width: 100px;" class="text-center">Compras</th>
                <th style="width: 130px;" class="text-end">Total Gastado</th>
                <th style="width: 140px;" class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                <?php
                    // ✅ Preparar datos ANTES del HTML para evitar inyección en onclick
                    $nombre_completo = htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido'], ENT_QUOTES, 'UTF-8');
                    $url_eliminar    = BASE_URL . 'clientes/delete/' . $cliente['id'];
                    $es_frecuente    = ($cliente['total_compras'] ?? 0) >= 10;
                ?>
                <tr>
                    <td data-label="ID">
                        <span class="text-muted small">#<?php echo $cliente['id']; ?></span>
                    </td>
                    <td data-label="Documento">
                        <span class="badge bg-info"><?php echo htmlspecialchars($cliente['tipo_documento']); ?></span>
                        <span class="ms-1"><?php echo htmlspecialchars($cliente['numero_documento']); ?></span>
                    </td>
                    <td data-label="Nombres y Apellidos">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2"
                                 style="width: 35px; height: 35px; flex-shrink: 0;">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($cliente['apellido'] . ' ' . $cliente['nombre']); ?></strong>
                                <?php if ($es_frecuente): ?>
                                    <span class="badge bg-warning text-dark ms-1" title="Cliente frecuente">
                                        <i class="fas fa-star"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="Teléfono">
                        <?php if (!empty($cliente['telefono'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($cliente['telefono']); ?>" class="text-decoration-none">
                                <i class="fas fa-phone me-1 text-success"></i>
                                <?php echo htmlspecialchars($cliente['telefono']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Email">
                        <?php if (!empty($cliente['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" class="text-decoration-none small">
                                <i class="fas fa-envelope me-1 text-primary"></i>
                                <?php echo htmlspecialchars($cliente['email']); ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Compras" class="text-center">
                        <?php if (($cliente['total_compras'] ?? 0) > 0): ?>
                            <span class="badge bg-primary"><?php echo (int)$cliente['total_compras']; ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">0</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Total Gastado" class="text-end">
                        <?php if (($cliente['total_gastado'] ?? 0) > 0): ?>
                            <strong class="text-success">
                                S/ <?php echo number_format($cliente['total_gastado'], 2); ?>
                            </strong>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="<?php echo BASE_URL; ?>clientes/show/<?php echo $cliente['id']; ?>"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?php echo BASE_URL; ?>clientes/edit/<?php echo $cliente['id']; ?>"
                               class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="#"
                               class="btn btn-sm btn-outline-danger" title="Eliminar"
                               onclick='return confirmarEliminar("<?php echo $url_eliminar; ?>", "<?php echo $nombre_completo; ?>")'>
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-user-slash fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No se encontraron clientes</h5>
                        <p class="text-muted small mb-3">
                            <?php if (isset($filtros['buscar']) && !empty($filtros['buscar'])): ?>
                                No hay resultados para "<strong><?php echo htmlspecialchars($filtros['buscar']); ?></strong>"
                            <?php else: ?>
                                Aún no hay clientes registrados en el sistema
                            <?php endif; ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>clientes/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Registrar primer cliente
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