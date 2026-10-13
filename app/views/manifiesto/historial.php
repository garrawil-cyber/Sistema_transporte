<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-history text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Historial de manifiestos generados y enviados</p>
    </div>
    <a href="<?php echo BASE_URL; ?>manifiesto" class="btn btn-primary">
        <i class="fas fa-clipboard-list me-1"></i>Generar Manifiesto
    </a>
</div>

<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="Ruta o placa del bus..."
                       value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_inicio'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_fin'] ?? ''); ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search"></i>
                </button>
                <a href="<?php echo BASE_URL; ?>manifiesto/historial" class="btn btn-sm btn-secondary">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<?php 
$total_pasajeros = 0;
foreach ($manifiestos ?? [] as $m) {
    $total_pasajeros += $m['total_pasajeros'] ?? 0;
}
?>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Manifiestos</small>
                        <h4 class="mb-0"><?php echo $paginator->total_registros ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Pasajeros Totales</small>
                        <h4 class="mb-0"><?php echo number_format($total_pasajeros); ?></h4>
                    </div>
                    <i class="fas fa-users fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Promedio Pasajeros</small>
                        <h4 class="mb-0"><?php 
                            echo count($manifiestos ?? []) > 0 
                                ? number_format($total_pasajeros / count($manifiestos), 1) 
                                : '0.0';
                        ?></h4>
                    </div>
                    <i class="fas fa-chart-bar fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Con QR</small>
                        <h4 class="mb-0"><?php 
                            echo count(array_filter($manifiestos ?? [], fn($m) => !empty($m['hash_manifiesto'])));
                        ?></h4>
                    </div>
                    <i class="fas fa-qrcode fa-2x text-warning opacity-50"></i>
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
                <th>Ruta</th>
                <th>Bus</th>
                <th>Fecha Salida</th>
                <th class="text-center">Pasajeros</th>
                <th>Generado por</th>
                <th>Fecha Generación</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($manifiestos)): ?>
                <?php foreach ($manifiestos as $m): ?>
                <tr>
                    <td data-label="#">
                        <strong>#<?php echo $m['id']; ?></strong>
                    </td>
                    <td data-label="Ruta">
                        <i class="fas fa-route text-primary me-1"></i>
                        <small><strong><?php echo htmlspecialchars($m['origen'] . ' → ' . $m['destino']); ?></strong></small>
                    </td>
                    <td data-label="Bus">
                        <span class="badge bg-secondary"><?php echo $m['placa']; ?></span>
                    </td>
                    <td data-label="Fecha Salida">
                        <small>
                            <?php echo date('d/m/Y', strtotime($m['fecha_salida'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i', strtotime($m['fecha_salida'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Pasajeros" class="text-center">
                        <span class="badge bg-info">
                            <i class="fas fa-users me-1"></i><?php echo $m['total_pasajeros']; ?>
                        </span>
                    </td>
                    <td data-label="Generado por">
                        <i class="fas fa-user-circle text-primary me-1"></i>
                        <small><?php echo htmlspecialchars($m['usuario_nombre']); ?></small>
                    </td>
                    <td data-label="Fecha Generación">
                        <small>
                            <?php echo date('d/m/Y', strtotime($m['fecha_generacion'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i', strtotime($m['fecha_generacion'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>manifiesto/show/<?php echo $m['viaje_id']; ?>" 
                               class="btn btn-outline-info" target="_blank" title="Ver manifiesto">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (!empty($m['hash_manifiesto'])): ?>
                                <a href="<?php echo BASE_URL; ?>validar/manifiesto/<?php echo $m['hash_manifiesto']; ?>" 
                                   class="btn btn-outline-success" target="_blank" title="Validar QR">
                                    <i class="fas fa-qrcode"></i>
                                </a>
                            <?php endif; ?>
                            <button type="button" 
                                    class="btn btn-outline-primary" 
                                    onclick="enviarManifiesto(<?php echo $m['viaje_id']; ?>); return false;"
                                    title="Enviar por email">
                                <i class="fas fa-envelope"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-file-alt fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay manifiestos registrados</h5>
                        <p class="text-muted small">No se encontraron manifiestos en el período seleccionado</p>
                        <a href="<?php echo BASE_URL; ?>manifiesto" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Generar primer manifiesto
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

<!-- Modal para enviar por email -->
<div class="modal fade" id="modalEnviarManifiesto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>manifiesto/enviarEmail" id="formEnviarManifiesto">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i>Enviar Manifiesto por Email
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="viaje_id" id="viaje_id_modal">
                    <div class="mb-3">
                        <label for="email_destino" class="form-label fw-bold">
                            Correo electrónico *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" id="email_destino" 
                                   class="form-control" 
                                   placeholder="correo@ejemplo.com" required>
                        </div>
                        <small class="text-muted">
                            Se enviará el manifiesto en formato PDF adjunto
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i>Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function enviarManifiesto(viajeId) {
    document.getElementById('viaje_id_modal').value = viajeId;
    const modal = new bootstrap.Modal(document.getElementById('modalEnviarManifiesto'));
    modal.show();
}
</script>