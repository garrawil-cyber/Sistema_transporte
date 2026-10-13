<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-bus text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Flota de buses y estado de unidades</p>
    </div>
    <a href="<?php echo BASE_URL; ?>buses/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Bus
    </a>
</div>

<!-- ============================================================
     RESUMEN (KPIs)
     ============================================================ -->
<?php
    $buses_arr = $buses ?? [];
    $total_activos       = 0;
    $total_mantenimiento = 0;
    $total_baja          = 0;
    foreach ($buses_arr as $b) {
        if (($b['estado'] ?? '') === 'ACTIVO')        $total_activos++;
        if (($b['estado'] ?? '') === 'MANTENIMIENTO') $total_mantenimiento++;
        if (($b['estado'] ?? '') === 'BAJA')          $total_baja++;
    }
    $total_flota = count($buses_arr);
?>

<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Activos</small>
                        <h4 class="mb-0"><?= $total_activos ?></h4>
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
                        <small class="text-muted d-block">Mantenimiento</small>
                        <h4 class="mb-0"><?= $total_mantenimiento ?></h4>
                    </div>
                    <i class="fas fa-tools fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">De Baja</small>
                        <h4 class="mb-0"><?= $total_baja ?></h4>
                    </div>
                    <i class="fas fa-times-circle fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Flota</small>
                        <h4 class="mb-0"><?= $total_flota ?></h4>
                    </div>
                    <i class="fas fa-bus fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     FILTROS
     ============================================================ -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="buscar" class="form-control form-control-sm"
                           placeholder="Placa, marca o modelo..."
                           value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ACTIVO"        <?= ($filtros['estado'] ?? '') == 'ACTIVO'        ? 'selected' : '' ?>>Activo</option>
                    <option value="MANTENIMIENTO" <?= ($filtros['estado'] ?? '') == 'MANTENIMIENTO' ? 'selected' : '' ?>>Mantenimiento</option>
                    <option value="BAJA"          <?= ($filtros['estado'] ?? '') == 'BAJA'          ? 'selected' : '' ?>>Baja</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Tipo de Servicio</label>
                <select name="tipo_servicio" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="ECONOMICO" <?= ($filtros['tipo_servicio'] ?? '') == 'ECONOMICO' ? 'selected' : '' ?>>Económico</option>
                    <option value="SEMICAMA"  <?= ($filtros['tipo_servicio'] ?? '') == 'SEMICAMA'  ? 'selected' : '' ?>>Semicama</option>
                    <option value="CAMA"      <?= ($filtros['tipo_servicio'] ?? '') == 'CAMA'      ? 'selected' : '' ?>>Cama</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?= BASE_URL ?>buses" class="btn btn-sm btn-secondary" title="Limpiar">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     TABLA
     ============================================================ -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Placa</th>
                <th>Marca / Modelo</th>
                <th class="text-center">Año</th>
                <th class="text-center">Capacidad</th>
                <th class="text-center">Servicio</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Viajes</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($buses)): ?>
                <?php foreach ($buses as $bus):
                    // Estado → badge
                    $badge_estado = [
                        'ACTIVO'        => 'success',
                        'MANTENIMIENTO' => 'warning',
                        'BAJA'          => 'danger'
                    ][$bus['estado'] ?? ''] ?? 'secondary';

                    // Tipo servicio → badge
                    $badge_servicio = [
                        'ECONOMICO' => 'info',
                        'SEMICAMA'  => 'primary',
                        'CAMA'      => 'dark'
                    ][$bus['tipo_servicio'] ?? ''] ?? 'secondary';

                    // ✅ Usar campos correctos del modelo corregido
                    $viajes_completados = (int)($bus['viajes_completados'] ?? 0);
                    $viajes_programados = (int)($bus['viajes_programados'] ?? 0);
                    $viajes_total       = $viajes_completados + $viajes_programados;

                    // ✅ FIX: Pasar datos como JSON (evita conflictos de comillas)
                    //    El onclick llama a confirmarBajaBus(this) que lee data-bus.
                    $baja_data = json_encode([
                        'id'    => (int)$bus['id'],
                        'placa' => $bus['placa'] ?? '',
                        'url'   => BASE_URL . 'buses/delete/' . (int)$bus['id']
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                ?>
                <tr>
                    <td data-label="Placa">
                        <strong class="font-monospace"><?= htmlspecialchars($bus['placa'] ?? '—') ?></strong>
                    </td>
                    <td data-label="Marca / Modelo">
                        <div class="small">
                            <strong><?= htmlspecialchars($bus['marca'] ?? '—') ?></strong>
                            <br>
                            <span class="text-muted"><?= htmlspecialchars($bus['modelo'] ?? '') ?></span>
                        </div>
                    </td>
                    <td data-label="Año" class="text-center">
                        <?= !empty($bus['anio']) ? (int)$bus['anio'] : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td data-label="Capacidad" class="text-center">
                        <span class="badge bg-secondary">
                            <i class="fas fa-chair me-1"></i><?= (int)($bus['capacidad_total'] ?? 0) ?>
                        </span>
                        <?php if (!empty($bus['pisos'])): ?>
                            <br><small class="text-muted"><?= (int)$bus['pisos'] ?> piso<?= $bus['pisos'] > 1 ? 's' : '' ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Servicio" class="text-center">
                        <span class="badge bg-<?= $badge_servicio ?>">
                            <?= htmlspecialchars($bus['tipo_servicio'] ?? '—') ?>
                        </span>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <span class="badge bg-<?= $badge_estado ?>">
                            <?= htmlspecialchars($bus['estado'] ?? '—') ?>
                        </span>
                    </td>
                    <td data-label="Viajes" class="text-center">
                        <?php if ($viajes_total > 0): ?>
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="badge bg-primary" title="Total viajes asignados">
                                    <i class="fas fa-route me-1"></i><?= $viajes_total ?>
                                </span>
                                <?php if ($viajes_programados > 0): ?>
                                    <small class="text-warning" title="Programados">
                                        <i class="fas fa-clock me-1"></i><?= $viajes_programados ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="<?= BASE_URL ?>buses/show/<?= (int)$bus['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>buses/edit/<?= (int)$bus['id'] ?>"
                               class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (($bus['estado'] ?? '') !== 'BAJA'): ?>
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Dar de baja <?= htmlspecialchars($bus['placa'] ?? '') ?>"
                                        data-bus="<?= htmlspecialchars($baja_data, ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="confirmarBajaBus(this)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-bus fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay buses registrados</h5>
                        <p class="text-muted small mb-3">Comienza agregando tu primera unidad a la flota</p>
                        <a href="<?= BASE_URL ?>buses/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Registrar primer bus
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

<a href="<?= BASE_URL ?>buses/create" class="fab-mobile" title="Nuevo Bus">
    <i class="fas fa-plus"></i>
</a>

<!-- ============================================================
     ✅ FIX: Script para confirmar baja de bus
     ============================================================ -->
<script>
/**
 * Confirma la baja de un bus.
 * Lee los datos desde el atributo data-bus del botón clickeado.
 * No usa onclick inline con HTML, evitando conflictos de comillas.
 */
function confirmarBajaBus(button) {
    let data;
    try {
        data = JSON.parse(button.dataset.bus);
    } catch (e) {
        console.error('Error parseando data-bus:', e);
        if (typeof mostrarToast === 'function') {
            mostrarToast('Error al procesar los datos del bus', 'error');
        }
        return;
    }

    const placaSegura = escapeHtmlBus(data.placa);

    const mensajeHtml = `El bus <strong>${placaSegura}</strong> será dado de baja del sistema.<br>
        <small class="text-muted">Esta acción no elimina los viajes históricos.</small>`;

    // ✅ Usar confirmarPersonalizado si está disponible
    if (typeof confirmarPersonalizado === 'function') {
        confirmarPersonalizado(
            '¿Dar de baja bus?',
            mensajeHtml,
            '<i class="fas fa-trash-alt me-1"></i>Sí, dar de baja',
            '#dc3545',
            function () {
                window.location.href = data.url;
            }
        );
    } else {
        // Fallback: confirm nativo
        if (confirm('¿Dar de baja el bus ' + data.placa + '?')) {
            window.location.href = data.url;
        }
    }
}

/**
 * Escapa HTML para evitar XSS en template strings
 */
function escapeHtmlBus(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>