<?php
// ============================================================
// Helpers locales seguros
// ============================================================

// ✅ FIX: Sanitizar nombre/apellido antes de extraer iniciales
$nombre_safe   = trim((string)($conductor['nombre']   ?? ''));
$apellido_safe = trim((string)($conductor['apellido'] ?? ''));

$iniciales = strtoupper(
    mb_substr($nombre_safe, 0, 1) . mb_substr($apellido_safe, 0, 1)
);
if ($iniciales === '') {
    $iniciales = '?';
}

// ✅ FIX: Verificar que UPLOAD_PATH esté definido antes de usarlo
$tiene_foto = !empty($conductor['foto'])
    && defined('UPLOAD_PATH')
    && file_exists(UPLOAD_PATH . 'conductores/' . $conductor['foto']);

// ✅ FIX: Helper para formatear fechas null-safe
$format_fecha = function($fecha) {
    if (empty($fecha)) return '—';
    $ts = strtotime($fecha);
    return $ts ? date('d/m/Y', $ts) : '—';
};

// ✅ FIX: Calcular días con protección
$dias       = (int)($conductor['dias_para_vencer'] ?? 0);
$estado_lic = $conductor['estado_licencia'] ?? 'VIGENTE';

// Badge de estado del conductor
$badge_estado = [
    'ACTIVO'     => 'success',
    'VACACIONES' => 'info',
    'LICENCIA'   => 'warning',
    'INACTIVO'   => 'secondary'
][$conductor['estado']] ?? 'secondary';

// Permisos
$puede_editar = isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
?>

<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="d-flex align-items-center gap-3 flex-grow-1">
            <?php if ($tiene_foto): ?>
                <img src="<?= (defined('UPLOAD_URL') ? UPLOAD_URL : BASE_URL . 'uploads/')
                            . 'conductores/' . htmlspecialchars($conductor['foto']) ?>"
                     class="rounded-circle object-fit-cover shadow-sm"
                     style="width: 80px; height: 80px;"
                     alt="Foto de <?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?>">
            <?php else: ?>
                <div class="rounded-circle bg-info bg-opacity-25 text-info d-flex align-items-center justify-content-center fw-bold shadow-sm"
                     style="width: 80px; height: 80px; font-size: 1.8rem;">
                    <?= htmlspecialchars($iniciales) ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <h1 class="h3 mb-1 text-truncate">
                    <?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?>
                </h1>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <span class="badge bg-<?= $badge_estado ?>">
                        <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                        <?= htmlspecialchars($conductor['estado']) ?>
                    </span>
                    <span class="text-muted">·</span>
                    <span class="text-muted font-monospace"><?= htmlspecialchars($conductor['numero_licencia'] ?? '—') ?></span>
                    <span class="text-muted">·</span>
                    <span class="text-muted"><?= htmlspecialchars($conductor['categoria_licencia'] ?? '—') ?></span>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($puede_editar): ?>
                <a href="<?= BASE_URL ?>conductores/edit/<?= $conductor['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i>Editar
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-outline-secondary">
                <i class="fas fa-print me-1"></i>Imprimir
            </button>
            <a href="<?= BASE_URL ?>conductores" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- SEMÁFORO DE LICENCIA -->
    <?php if ($estado_lic === 'VENCIDA'): ?>
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-3">
            <i class="fas fa-times-circle fa-2x me-3"></i>
            <div>
                <strong>Licencia VENCIDA</strong> hace <?= abs($dias) ?> días
                (<?= $format_fecha($conductor['fecha_vencimiento_licencia']) ?>).
                <br><small>Este conductor no debe ser asignado a viajes hasta renovarla.</small>
            </div>
        </div>
    <?php elseif ($estado_lic === 'POR_VENCER'): ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <strong>Licencia por vencer</strong> en <?= max(0, $dias) ?> días
                (<?= $format_fecha($conductor['fecha_vencimiento_licencia']) ?>).
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div>
                <?php if ($dias === 0): ?>
                    <strong>Licencia vence HOY</strong>
                    (<?= $format_fecha($conductor['fecha_vencimiento_licencia']) ?>).
                <?php else: ?>
                    <strong>Licencia vigente</strong> por <?= max(0, $dias) ?> días más
                    (Vence: <?= $format_fecha($conductor['fecha_vencimiento_licencia']) ?>).
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- KPIs ESTADÍSTICAS -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary"><i class="fas fa-bus"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Total Viajes</div>
                        <div class="kpi-value text-primary"><?= (int)($estadisticas['total_viajes'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Completados</div>
                        <div class="kpi-value text-success"><?= (int)($estadisticas['completados'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info"><i class="fas fa-users"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Pasajeros</div>
                        <div class="kpi-value text-info"><?= (int)($estadisticas['total_pasajeros'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm kpi-card">
                <div class="card-body d-flex align-items-center gap-2">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Recaudado</div>
                        <div class="kpi-value text-success">
                            S/ <?= number_format($estadisticas['total_recaudado'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- COLUMNA IZQUIERDA -->
        <div class="col-lg-4">

            <!-- Contacto -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-primary text-white border-0 py-3">
                    <i class="fas fa-address-card me-2"></i><strong>Contacto</strong>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-envelope text-primary mt-1" style="width: 18px;"></i>
                            <div class="min-w-0">
                                <div class="text-muted">Email</div>
                                <?php if (!empty($conductor['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($conductor['email']) ?>"
                                       class="text-decoration-none fw-semibold text-body text-truncate d-block">
                                        <?= htmlspecialchars($conductor['email']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="mb-3 d-flex align-items-start gap-2">
                            <i class="fas fa-phone text-success mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted">Teléfono</div>
                                <?php if (!empty($conductor['telefono'])): ?>
                                    <a href="tel:<?= htmlspecialchars($conductor['telefono']) ?>"
                                       class="text-decoration-none fw-semibold text-body">
                                        <?= htmlspecialchars($conductor['telefono']) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="mb-0 d-flex align-items-start gap-2">
                            <i class="fas fa-home text-muted mt-1" style="width: 18px;"></i>
                            <div>
                                <div class="text-muted">Dirección</div>
                                <div class="fw-semibold">
                                    <?= htmlspecialchars($conductor['direccion'] ?: '—') ?>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Emergencia -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-danger text-white border-0 py-3">
                    <i class="fas fa-phone-volume me-2"></i><strong>Contacto de Emergencia</strong>
                </div>
                <div class="card-body">
                    <div class="fw-bold">
                        <?= htmlspecialchars($conductor['contacto_emergencia_nombre'] ?: 'No registrado') ?>
                    </div>
                    <?php if (!empty($conductor['contacto_emergencia_telefono'])): ?>
                        <a href="tel:<?= htmlspecialchars($conductor['contacto_emergencia_telefono']) ?>"
                           class="text-danger text-decoration-none">
                            <i class="fas fa-phone me-1"></i><?= htmlspecialchars($conductor['contacto_emergencia_telefono']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Identificación -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white border-0 py-3">
                    <i class="fas fa-id-card me-2"></i><strong>Identificación</strong>
                </div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tipo Doc.</span>
                        <span class="fw-semibold"><?= htmlspecialchars($conductor['tipo_documento'] ?? 'DNI') ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">N° Doc.</span>
                        <span class="fw-semibold font-monospace">
                            <?= htmlspecialchars($conductor['numero_documento'] ?: '—') ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA -->
        <div class="col-lg-8">

            <!-- Datos laborales -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-dark text-white border-0 py-3">
                    <i class="fas fa-money-bill-wave me-2"></i><strong>Datos Laborales</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Fecha de Ingreso</div>
                            <div class="fw-semibold"><?= $format_fecha($conductor['fecha_ingreso']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Sueldo Base</div>
                            <div class="fw-bold text-primary h5 mb-0">
                                S/ <?= number_format((float)$conductor['sueldo_base'], 2) ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Comisión</div>
                            <div class="fw-bold text-success h5 mb-0">
                                <?= number_format((float)$conductor['comision_porcentaje'], 2) ?>%
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Nacimiento</div>
                            <div class="fw-semibold"><?= $format_fecha($conductor['fecha_nacimiento']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Emisión Licencia</div>
                            <div class="fw-semibold"><?= $format_fecha($conductor['fecha_emision_licencia']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Vencimiento Licencia</div>
                            <div class="fw-semibold"><?= $format_fecha($conductor['fecha_vencimiento_licencia']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Usuario activo</div>
                            <div class="fw-semibold">
                                <?php if (!empty($conductor['usuario_activo'])): ?>
                                    <span class="badge bg-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-muted small">Último acceso</div>
                            <div class="fw-semibold">
                                <?= !empty($conductor['usuario_ultimo_acceso'])
                                    ? date('d/m/Y H:i', strtotime($conductor['usuario_ultimo_acceso']))
                                    : '—' ?>
                            </div>
                        </div>
                        <?php if (!empty($conductor['observaciones'])): ?>
                            <div class="col-12">
                                <hr>
                                <div class="text-muted small">Observaciones</div>
                                <div><?= nl2br(htmlspecialchars($conductor['observaciones'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Historial de viajes -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div><i class="fas fa-history me-2"></i><strong>Últimos Viajes</strong></div>
                    <span class="badge bg-light text-dark"><?= count($historial ?? []) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($historial)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ruta</th>
                                        <th class="text-center">Bus</th>
                                        <th>Salida</th>
                                        <th class="text-center">Pasajeros</th>
                                        <th class="text-end">Recaudado</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historial as $v):
                                        $badge_v = [
                                            'PROGRAMADO' => 'primary',
                                            'EN_CURSO'   => 'warning',
                                            'COMPLETADO' => 'success',
                                            'CANCELADO'  => 'danger'
                                        ][$v['estado']] ?? 'secondary';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="small fw-semibold">
                                                <?= htmlspecialchars($v['origen']) ?>
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                <?= htmlspecialchars($v['destino']) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($v['placa']) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small><?= date('d/m/Y', strtotime($v['fecha_salida'])) ?></small>
                                                <small class="text-muted"><?= date('H:i', strtotime($v['fecha_salida'])) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= (int)$v['pasajeros'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">
                                                S/ <?= number_format((float)$v['recaudado'], 2) ?>
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $badge_v ?>">
                                                <?= htmlspecialchars($v['estado']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-bus fa-4x text-muted mb-3 d-block opacity-25"></i>
                            <h6 class="text-muted">Sin viajes registrados</h6>
                            <p class="text-muted small mb-0">Este conductor aún no tiene viajes asignados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>