<?php
// ✅ FIX: Sanitizar datos del bus antes de usarlos
$placa   = $bus['placa']  ?? '—';
$marca   = $bus['marca']  ?? '—';
$modelo  = $bus['modelo'] ?? '—';
$anio    = !empty($bus['anio']) ? (int)$bus['anio'] : null;
$estado  = $bus['estado'] ?? '—';

// Badge de estado
$badge_estado = [
    'ACTIVO'        => 'success',
    'MANTENIMIENTO' => 'warning',
    'BAJA'          => 'danger'
][$estado] ?? 'secondary';

// Badge de tipo servicio
$tipo_servicio = $bus['tipo_servicio'] ?? '—';
$badge_servicio = [
    'ECONOMICO' => 'info',
    'SEMICAMA'  => 'primary',
    'CAMA'      => 'dark'
][$tipo_servicio] ?? 'secondary';

// ✅ FIX: valores null-safe de estadísticas
$total_viajes   = (int)($estadisticas['total_viajes']   ?? 0);
$completados    = (int)($estadisticas['completados']    ?? 0);
$en_curso       = (int)($estadisticas['en_curso']       ?? 0);
$programados    = (int)($estadisticas['programados']    ?? 0);
$cancelados     = (int)($estadisticas['cancelados']     ?? 0);
$total_pasajeros = (int)($estadisticas['total_pasajeros'] ?? 0);
$total_recaudado = (float)($estadisticas['total_recaudado'] ?? 0);
$ingreso_promedio = (float)($estadisticas['ingreso_promedio'] ?? 0);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-bus text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">
            Placa: <strong class="font-monospace"><?= htmlspecialchars($placa) ?></strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>buses/edit/<?= (int)$bus['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="<?= BASE_URL ?>buses" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3">

    <!-- ============================================================
         INFO DEL BUS
         ============================================================ -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-info-circle me-2"></i>Información del Bus
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Placa</small>
                        <strong class="fs-5 font-monospace"><?= htmlspecialchars($placa) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Año</small>
                        <strong><?= $anio ?: '—' ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Marca</small>
                        <strong><?= htmlspecialchars($marca) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Modelo</small>
                        <strong><?= htmlspecialchars($modelo) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Capacidad</small>
                        <strong>
                            <i class="fas fa-chair me-1"></i><?= (int)($bus['capacidad_total'] ?? 0) ?> asientos
                        </strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tipo de Servicio</small>
                        <span class="badge bg-<?= $badge_servicio ?> fs-6">
                            <?= htmlspecialchars($tipo_servicio) ?>
                        </span>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Estado</small>
                        <span class="badge bg-<?= $badge_estado ?> fs-6">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                            <?= htmlspecialchars($estado) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuración del mapa -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-layer-group me-2"></i>Configuración del Mapa
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Pisos</small>
                        <strong><?= (int)($bus['pisos'] ?? 2) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Asientos Piso 1</small>
                        <strong class="text-primary"><?= (int)($bus['asientos_piso_1'] ?? 0) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Asientos Piso 2</small>
                        <strong class="text-success"><?= (int)($bus['asientos_piso_2'] ?? 0) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Por Fila (P1 / P2)</small>
                        <strong>
                            <?= (int)($bus['asientos_por_fila_piso_1'] ?? 3) ?> / <?= (int)($bus['asientos_por_fila_piso_2'] ?? 4) ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de viajes -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-route me-2"></i>Actividad Reciente
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Viajes completados</span>
                    <strong class="text-success"><?= $completados ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">En curso</span>
                    <strong class="text-warning"><?= $en_curso ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Programados</span>
                    <strong class="text-primary"><?= $programados ?></strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Cancelados</span>
                    <strong class="text-danger"><?= $cancelados ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         ESTADÍSTICAS
         ============================================================ -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="fas fa-chart-bar me-2"></i>Estadísticas de Uso
            </div>
            <div class="card-body">

                <!-- Cards de viajes -->
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <div class="card border-0 bg-primary bg-opacity-10 text-center">
                            <div class="card-body py-3">
                                <i class="fas fa-route fa-2x text-primary opacity-50 mb-2"></i>
                                <h3 class="mb-0 text-primary"><?= $total_viajes ?></h3>
                                <small class="text-muted">Total Viajes</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 bg-success bg-opacity-10 text-center">
                            <div class="card-body py-3">
                                <i class="fas fa-check-circle fa-2x text-success opacity-50 mb-2"></i>
                                <h3 class="mb-0 text-success"><?= $completados ?></h3>
                                <small class="text-muted">Completados</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-0 bg-warning bg-opacity-10 text-center">
                            <div class="card-body py-3">
                                <i class="fas fa-clock fa-2x text-warning opacity-50 mb-2"></i>
                                <h3 class="mb-0 text-warning"><?= $programados ?></h3>
                                <small class="text-muted">Programados</small>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Ingresos y Pasajeros -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-users me-1"></i>Pasajeros transportados
                            </span>
                            <strong class="fs-5"><?= $total_pasajeros ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-money-bill-wave me-1"></i>Ingreso total
                            </span>
                            <strong class="fs-5 text-success">
                                S/ <?= number_format($total_recaudado, 2) ?>
                            </strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-chart-line me-1"></i>Ingreso promedio
                            </span>
                            <strong>S/ <?= number_format($ingreso_promedio, 2) ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <i class="fas fa-percentage me-1"></i>Ocupación promedio
                            </span>
                            <strong>
                                <?php
                                    $cap = max(1, (int)($bus['capacidad_total'] ?? 1));
                                    $promedio_pasajeros = $total_viajes > 0 ? $total_pasajeros / max(1, $completados) : 0;
                                    $ocupacion = min(100, round(($promedio_pasajeros / $cap) * 100));
                                ?>
                                <?= $ocupacion ?>%
                            </strong>
                        </div>
                    </div>
                </div>

                <?php if ($total_viajes === 0): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Este bus aún no tiene viajes registrados.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Historial de viajes -->
        <?php if (!empty($viajes)): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-history me-2"></i><strong>Últimos Viajes</strong>
                    </div>
                    <span class="badge bg-light text-dark"><?= count($viajes) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ruta</th>
                                    <th>Salida</th>
                                    <th class="text-center">Pasajeros</th>
                                    <th class="text-end">Recaudado</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viajes as $v):
                                    $badge_v = [
                                        'PROGRAMADO' => 'primary',
                                        'EN_CURSO'   => 'warning',
                                        'COMPLETADO' => 'success',
                                        'CANCELADO'  => 'danger'
                                    ][$v['estado'] ?? ''] ?? 'secondary';
                                ?>
                                <tr>
                                    <td>
                                        <small class="fw-semibold">
                                            <?= htmlspecialchars($v['origen'] ?? '—') ?>
                                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                            <?= htmlspecialchars($v['destino'] ?? '—') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <?= !empty($v['fecha_salida'])
                                                ? date('d/m/Y H:i', strtotime($v['fecha_salida']))
                                                : '—' ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= (int)($v['pasajeros'] ?? 0) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            S/ <?= number_format((float)($v['recaudado'] ?? 0), 2) ?>
                                        </strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $badge_v ?>">
                                            <?= htmlspecialchars($v['estado'] ?? '—') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>