<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-info bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-id-card text-info fa-lg"></i>
                </span>
                <span><?= htmlspecialchars($titulo ?? 'Gestión de Conductores') ?></span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">
                <i class="fas fa-info-circle me-1"></i>Administra conductores, licencias y comisiones
            </p>
        </div>
        <a href="<?= BASE_URL ?>conductores/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Nuevo Conductor
        </a>
    </div>

    <!-- ALERTA: Pendientes -->
    <?php if (!empty($pendientes)): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-user-clock fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <strong><?= count($pendientes) ?> usuario<?= count($pendientes) > 1 ? 's' : '' ?></strong>
                    con rol CONDUCTOR sin perfil completo
                </h6>
                <p class="mb-2 small">Completa la información de licencia y datos laborales:</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($pendientes as $p): ?>
                        <a href="<?= BASE_URL ?>conductores/create?usuario_id=<?= (int)$p['usuario_id'] ?>"
                           class="btn btn-sm btn-outline-dark">
                            <i class="fas fa-user-plus me-1"></i>
                            <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ALERTA: Licencias por vencer -->
    <?php if (!empty($licencias_por_vencer)): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-2">
                    <strong><?= count($licencias_por_vencer) ?></strong>
                    licencia<?= count($licencias_por_vencer) > 1 ? 's' : '' ?>
                    por vencer en los próximos 30 días
                </h6>
                <ul class="mb-0 small">
                    <?php foreach (array_slice($licencias_por_vencer, 0, 5) as $l): ?>
                        <li>
                            <strong><?= htmlspecialchars(($l['nombre'] ?? '') . ' ' . ($l['apellido'] ?? '')) ?></strong>
                            · Vence: <?= !empty($l['fecha_vencimiento_licencia'])
                                        ? date('d/m/Y', strtotime($l['fecha_vencimiento_licencia']))
                                        : '—' ?>
                            <span class="badge bg-danger ms-1"><?= (int)($l['dias_restantes'] ?? 0) ?> días</span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (count($licencias_por_vencer) > 5): ?>
                        <li class="text-muted mt-1">
                            <em>+ <?= count($licencias_por_vencer) - 5 ?> más...</em>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="buscar" class="form-control"
                               placeholder="Nombre, apellido, DNI, licencia o email..."
                               value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Estado</label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="ACTIVO"     <?= ($filtros['estado'] ?? '') == 'ACTIVO'     ? 'selected' : '' ?>>Activo</option>
                        <option value="INACTIVO"   <?= ($filtros['estado'] ?? '') == 'INACTIVO'   ? 'selected' : '' ?>>Inactivo</option>
                        <option value="VACACIONES" <?= ($filtros['estado'] ?? '') == 'VACACIONES' ? 'selected' : '' ?>>Vacaciones</option>
                        <option value="LICENCIA"   <?= ($filtros['estado'] ?? '') == 'LICENCIA'   ? 'selected' : '' ?>>Con Licencia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Filtro licencia</label>
                    <div class="d-flex gap-2">
                        <div class="form-check">
                            <input type="checkbox" name="licencia_por_vencer" id="lpv" class="form-check-input"
                                   value="1" <?= !empty($filtros['licencia_por_vencer']) ? 'checked' : '' ?>>
                            <label for="lpv" class="form-check-label small">Por vencer</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="licencia_vencida" id="lv" class="form-check-input"
                                   value="1" <?= !empty($filtros['licencia_vencida']) ? 'checked' : '' ?>>
                            <label for="lv" class="form-check-label small">Vencidas</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-filter me-1"></i>Filtrar
                    </button>
                    <a href="<?= BASE_URL ?>conductores" class="btn btn-sm btn-outline-secondary" title="Limpiar">
                        <i class="fas fa-undo"></i>
                    </a>
                    <a href="<?= BASE_URL ?>reportes/exportarExcel/conductores" class="btn btn-sm btn-success" title="Exportar">
                        <i class="fas fa-file-excel"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Total</div>
                        <div class="kpi-value text-primary"><?= (int)($resumen['total'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Activos</div>
                        <div class="kpi-value text-success"><?= (int)($resumen['activos'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Por Vencer</div>
                        <div class="kpi-value text-warning"><?= (int)($resumen['por_vencer'] ?? 0) ?></div>
                        <small class="text-muted d-block">Próximos 30 días</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Vencidas</div>
                        <div class="kpi-value text-danger"><?= (int)($resumen['vencidas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="table-responsive table-responsive-mobile">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th style="width: 250px;">Conductor</th>
                    <th style="width: 180px;">Licencia</th>
                    <th style="width: 140px;">Vencimiento</th>
                    <th style="width: 130px;">Teléfono</th>
                    <th class="text-end" style="width: 140px;">Sueldo + Com.</th>
                    <th class="text-center" style="width: 110px;">Estado</th>
                    <th class="text-center" style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($conductores)): ?>
                    <?php foreach ($conductores as $c):

                        // ✅ FIX: días y estado de licencia seguros
                        $dias       = (int)($c['dias_para_vencer'] ?? 0);
                        $estado_lic = $c['estado_licencia'] ?? 'VIGENTE';

                        // ✅ FIX: iniciales null-safe
                        $nombre_safe   = trim((string)($c['nombre']   ?? ''));
                        $apellido_safe = trim((string)($c['apellido'] ?? ''));
                        $iniciales = strtoupper(
                            mb_substr($nombre_safe, 0, 1) . mb_substr($apellido_safe, 0, 1)
                        );
                        if ($iniciales === '') $iniciales = '?';

                        // ✅ FIX: UPLOAD_PATH verificado
                        $tiene_foto = !empty($c['foto'])
                            && defined('UPLOAD_PATH')
                            && file_exists(UPLOAD_PATH . 'conductores/' . $c['foto']);

                        $upload_url = defined('UPLOAD_URL') ? UPLOAD_URL : BASE_URL . 'uploads/';

                        // ✅ FIX: usar json_encode para pasar strings seguras a JS
                        $nombre_js    = json_encode($nombre_safe . ' ' . $apellido_safe, JSON_UNESCAPED_UNICODE);
                        $url_eliminar = BASE_URL . 'conductores/delete/' . (int)$c['id'];

                        // ✅ FIX: fecha de vencimiento null-safe
                        $fecha_vence_str = !empty($c['fecha_vencimiento_licencia'])
                            ? date('d/m/Y', strtotime($c['fecha_vencimiento_licencia']))
                            : '—';

                        $badge_estado = [
                            'ACTIVO'     => 'success',
                            'VACACIONES' => 'info',
                            'LICENCIA'   => 'warning',
                            'INACTIVO'   => 'secondary'
                        ][$c['estado']] ?? 'secondary';
                    ?>
                    <tr>
                        <td data-label="Conductor">
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($tiene_foto): ?>
                                    <img src="<?= $upload_url . 'conductores/' . htmlspecialchars($c['foto']) ?>"
                                         alt="<?= htmlspecialchars($nombre_safe) ?>"
                                         class="rounded-circle object-fit-cover"
                                         style="width: 42px; height: 42px; flex-shrink: 0;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-info bg-opacity-25 text-info d-flex align-items-center justify-content-center fw-bold"
                                         style="width: 42px; height: 42px; flex-shrink: 0; font-size: 0.9rem;">
                                        <?= htmlspecialchars($iniciales) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <strong class="d-block text-truncate">
                                        <?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?>
                                    </strong>
                                    <small class="text-muted text-truncate d-block">
                                        <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($c['email'] ?? '—') ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td data-label="Licencia">
                            <strong class="font-monospace">
                                <?= htmlspecialchars($c['numero_licencia'] ?? '—') ?>
                            </strong>
                            <br><span class="badge bg-secondary">
                                <?= htmlspecialchars($c['categoria_licencia'] ?? '—') ?>
                            </span>
                        </td>
                        <td data-label="Vencimiento">
                            <div class="d-flex flex-column">
                                <small><?= $fecha_vence_str ?></small>
                                <?php if ($estado_lic === 'VENCIDA'): ?>
                                    <span class="badge bg-danger mt-1">
                                        <i class="fas fa-times me-1"></i>Vencida hace <?= abs($dias) ?>d
                                    </span>
                                <?php elseif ($estado_lic === 'POR_VENCER'): ?>
                                    <span class="badge bg-warning text-dark mt-1">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?= $dias ?>d
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success mt-1">
                                        <i class="fas fa-check me-1"></i>Vigente
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td data-label="Teléfono">
                            <?php if (!empty($c['telefono'])): ?>
                                <a href="tel:<?= htmlspecialchars($c['telefono']) ?>" class="text-decoration-none">
                                    <i class="fas fa-phone me-1 text-success"></i><?= htmlspecialchars($c['telefono']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Sueldo" class="text-end">
                            <strong>S/ <?= number_format((float)($c['sueldo_base'] ?? 0), 2) ?></strong>
                            <?php if (($c['comision_porcentaje'] ?? 0) > 0): ?>
                                <br><small class="text-success">
                                    +<?= number_format((float)$c['comision_porcentaje'], 2) ?>% com.
                                </small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Estado" class="text-center">
                            <span class="badge bg-<?= $badge_estado ?>">
                                <?= htmlspecialchars($c['estado'] ?? '—') ?>
                            </span>
                            <?php if (empty($c['usuario_activo'])): ?>
                                <br><small class="text-danger">
                                    <i class="fas fa-ban me-1"></i>Usuario inactivo
                                </small>
                            <?php endif; ?>
                        </td>
                        <td data-label="Acciones" class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="<?= BASE_URL ?>conductores/show/<?= (int)$c['id'] ?>"
                                   class="btn btn-sm btn-outline-info" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>conductores/edit/<?= (int)$c['id'] ?>"
                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-danger"
                                   title="Eliminar a <?= htmlspecialchars($nombre_safe) ?>"
                                   onclick='return confirmarEliminar(<?= json_encode($url_eliminar) ?>, <?= $nombre_js ?>)'>
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-user-tie fa-4x text-muted mb-3 d-block opacity-25"></i>
                            <h5 class="text-muted">No hay conductores registrados</h5>
                            <p class="text-muted small mb-3">Comienza creando el primer conductor</p>
                            <a href="<?= BASE_URL ?>conductores/create" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Registrar primer conductor
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
</div>

<a href="<?= BASE_URL ?>conductores/create" class="fab-mobile" title="Nuevo Conductor">
    <i class="fas fa-plus"></i>
</a>