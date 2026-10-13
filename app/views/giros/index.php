<!-- Vista: giros/index.php -->
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-info bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-money-bill-transfer text-info fa-lg"></i>
                </span>
                <span><?php echo $titulo; ?></span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">Envío de dinero con comisión automática</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="fas fa-filter me-1"></i>Filtros
            </button>
            <a href="<?php echo BASE_URL; ?>giros/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Nuevo Giro
            </a>
        </div>
    </div>

    <!-- FILTROS COLAPSABLES -->
    <div class="collapse mb-4" id="filtrosCollapse">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm"
                               placeholder="Código, remitente, destinatario o DNI..."
                               value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="REGISTRADO" <?php echo ($filtros['estado'] ?? '') == 'REGISTRADO' ? 'selected' : ''; ?>>Registrado</option>
                            <option value="PAGADO"     <?php echo ($filtros['estado'] ?? '') == 'PAGADO'     ? 'selected' : ''; ?>>Pagado</option>
                            <option value="ANULADO"    <?php echo ($filtros['estado'] ?? '') == 'ANULADO'    ? 'selected' : ''; ?>>Anulado</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Desde</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" 
                               class="form-control form-control-sm"
                               value="<?php echo htmlspecialchars($filtros['fecha_inicio'] ?? ''); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Hasta</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" 
                               class="form-control form-control-sm"
                               value="<?php echo htmlspecialchars($filtros['fecha_fin'] ?? ''); ?>">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Filtrar">
                            <i class="fas fa-search"></i>
                        </button>
                        <a href="<?php echo BASE_URL; ?>giros" class="btn btn-sm btn-secondary" title="Limpiar">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KPIs MODERNOS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Registrados</div>
                        <div class="kpi-value"><?php echo $stats['registrados'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Pagados</div>
                        <div class="kpi-value"><?php echo $stats['pagados'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Monto Total</div>
                        <div class="kpi-value text-info" style="font-size: 1.1rem;">
                            S/ <?php echo number_format($stats['monto_total'] ?? 0, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Comisiones</div>
                        <div class="kpi-value text-primary" style="font-size: 1.1rem;">
                            S/ <?php echo number_format($stats['comision_total'] ?? 0, 2); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO + RESUMEN MONETARIO -->
    <div class="row g-3 mb-4">
        <?php if (!empty($chartData)): ?>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 d-flex align-items-center">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Giros últimos 7 días
                        </h6>
                        <small class="text-muted">
                            Total semana: <strong><?php 
                                $totalSemana = array_sum(array_column($chartData, 'total'));
                                echo $totalSemana;
                            ?></strong>
                        </small>
                    </div>
                    <div class="mini-chart">
                        <?php 
                        $maxTotal = max(array_column($chartData, 'total')) ?: 1;
                        $diasNombres = ['Sun'=>'DOM','Mon'=>'LUN','Tue'=>'MAR','Wed'=>'MIÉ','Thu'=>'JUE','Fri'=>'VIE','Sat'=>'SÁB'];
                        foreach ($chartData as $d): 
                            $altura = ($d['total'] / $maxTotal) * 100;
                            $esHoy  = $d['fecha'] === date('Y-m-d');
                            $diaTxt = $diasNombres[date('D', strtotime($d['fecha']))] ?? date('D', strtotime($d['fecha']));
                        ?>
                            <div class="mini-chart-col">
                                <div class="mini-chart-bar-wrapper">
                                    <div class="mini-chart-bar <?php echo $esHoy ? 'hoy' : ''; ?>"
                                         style="height: <?php echo max($altura, 3); ?>%;"
                                         title="<?php echo $d['total']; ?> giros | S/ <?php echo number_format($d['monto'], 2); ?>">
                                        <span class="mini-chart-value"><?php echo $d['total']; ?></span>
                                    </div>
                                </div>
                                <div class="mini-chart-label <?php echo $esHoy ? 'hoy' : ''; ?>">
                                    <div class="mini-chart-day"><?php echo $diaTxt; ?></div>
                                    <div class="mini-chart-date"><?php echo date('d/m', strtotime($d['fecha'])); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="<?php echo !empty($chartData) ? 'col-lg-4' : 'col-12'; ?>">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center text-center">
                    <div class="mb-2">
                        <i class="fas fa-coins fa-3x text-success opacity-75"></i>
                    </div>
                    <div class="kpi-label text-muted mb-1">Monto Pagado</div>
                    <div class="display-6 fw-bold text-success mb-0">
                        S/ <?php echo number_format($stats['monto_pagado'] ?? 0, 2); ?>
                    </div>
                    <div class="text-muted small mt-2">
                        <?php echo $stats['pagados'] ?? 0; ?> giros pagados
                    </div>
                    
                    <div class="d-flex justify-content-around mt-3 pt-3 border-top">
                        <div>
                            <div class="text-muted small">Total Giros</div>
                            <div class="fw-bold"><?php echo $stats['total'] ?? 0; ?></div>
                        </div>
                        <div>
                            <div class="text-muted small">Comisiones</div>
                            <div class="fw-bold text-warning">S/ <?php echo number_format($stats['comision_cobrada'] ?? 0, 2); ?></div>
                        </div>
                    </div>
                    
                    <?php if (($stats['anulados'] ?? 0) > 0): ?>
                        <div class="text-danger small mt-2">
                            <i class="fas fa-ban me-1"></i>
                            <?php echo $stats['anulados']; ?> anulados
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive table-responsive-mobile">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 130px;">Código</th>
                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th style="width: 100px;">Fecha</th>
                            <th class="text-center" style="width: 60px;">Mon.</th>
                            <th class="text-end" style="width: 100px;">Monto</th>
                            <th class="text-end" style="width: 90px;">Comisión</th>
                            <th class="text-center" style="width: 100px;">Estado</th>
                            <th class="text-center" style="width: 130px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($giros)): ?>
                            <?php foreach ($giros as $g): ?>
                            <?php
                                $badge = 'secondary';
                                if ($g['estado'] == 'REGISTRADO') $badge = 'warning';
                                if ($g['estado'] == 'PAGADO')     $badge = 'success';
                                if ($g['estado'] == 'ANULADO')    $badge = 'danger';

                                $codigo_js   = htmlspecialchars($g['codigo'], ENT_QUOTES, 'UTF-8');
                                $monto_js    = number_format($g['monto'], 2);
                                $comision_js = number_format($g['comision'], 2);
                                $url_pagar   = BASE_URL . 'giros/pagar/' . $g['id'];
                                $url_anular  = BASE_URL . 'giros/anular/' . $g['id'];
                            ?>
                            <tr>
                                <td data-label="Código">
                                    <span class="badge bg-dark font-monospace">
                                        <?php echo htmlspecialchars($g['codigo']); ?>
                                    </span>
                                </td>
                                <td data-label="Remitente">
                                    <div class="d-flex flex-column">
                                        <strong class="small">
                                            <?php echo htmlspecialchars($g['remitente_nombre'] . ' ' . $g['remitente_apellido']); ?>
                                        </strong>
                                        <small class="text-muted">
                                            <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($g['remitente_dni']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td data-label="Destinatario">
                                    <div class="d-flex flex-column">
                                        <strong class="small">
                                            <?php echo htmlspecialchars($g['destinatario_nombre'] . ' ' . $g['destinatario_apellido']); ?>
                                        </strong>
                                        <small class="text-muted">
                                            <i class="fas fa-id-card me-1"></i><?php echo htmlspecialchars($g['destinatario_dni']); ?>
                                        </small>
                                    </div>
                                </td>
                                <td data-label="Fecha">
                                    <div class="d-flex flex-column">
                                        <span class="small"><?php echo date('d/m/Y', strtotime($g['fecha_registro'])); ?></span>
                                        <small class="text-muted"><?php echo date('H:i', strtotime($g['fecha_registro'])); ?></small>
                                    </div>
                                </td>
                                <td data-label="Moneda" class="text-center">
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($g['moneda']); ?></span>
                                </td>
                                <td data-label="Monto" class="text-end">
                                    <strong class="text-success">S/ <?php echo $monto_js; ?></strong>
                                </td>
                                <td data-label="Comisión" class="text-end">
                                    <span class="text-warning-emphasis fw-semibold">S/ <?php echo $comision_js; ?></span>
                                </td>
                                <td data-label="Estado" class="text-center">
                                    <span class="badge bg-<?php echo $badge; ?>">
                                        <?php echo $g['estado']; ?>
                                    </span>
                                </td>
                                <td data-label="Acciones" class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?php echo BASE_URL; ?>giros/show/<?php echo $g['id']; ?>"
                                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($g['estado'] == 'REGISTRADO'): ?>
                                            <a href="#" class="btn btn-sm btn-outline-success" title="Marcar como pagado"
                                               onclick='return confirmarPersonalizado(
                                                   "¿Confirmar pago?",
                                                   "Se registrará el pago del giro <strong><?php echo $codigo_js; ?></strong><br>Monto: <strong>S/ <?php echo $monto_js; ?></strong><br>Comisión: S/ <?php echo $comision_js; ?>",
                                                   "Sí, pagar", "#198754",
                                                   function() { window.location.href = "<?php echo $url_pagar; ?>"; }
                                               )'>
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-danger" title="Anular giro"
                                               onclick='return confirmarAnular(
                                                   "<?php echo $url_anular; ?>",
                                                   "Código: <strong><?php echo $codigo_js; ?></strong> - Monto: S/ <?php echo $monto_js; ?>"
                                               )'>
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-money-bill fa-4x text-muted mb-3 d-block opacity-50"></i>
                                    <h5 class="text-muted">No hay giros registrados</h5>
                                    <a href="<?php echo BASE_URL; ?>giros/create" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-1"></i>Registrar primer giro
                                    </a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (isset($paginator) && $paginator->total_paginas > 1): ?>
            <div class="card-footer bg-white border-0">
                <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<a href="<?php echo BASE_URL; ?>giros/create" class="fab-mobile" title="Nuevo Giro">
    <i class="fas fa-plus"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Validar orden de fechas
    if (typeof aplicarOrdenFechas === 'function') {
        aplicarOrdenFechas('fecha_inicio', 'fecha_fin');
    }

    // Auto-abrir panel si hay filtros activos
    const filtrosCollapse = document.getElementById('filtrosCollapse');
    if (filtrosCollapse) {
        const tieneFiltros = <?php echo (
            !empty($filtros['buscar']) || 
            !empty($filtros['estado']) || 
            !empty($filtros['fecha_inicio']) || 
            !empty($filtros['fecha_fin'])
        ) ? 'true' : 'false'; ?>;
        
        if (tieneFiltros && typeof bootstrap !== 'undefined') {
            new bootstrap.Collapse(filtrosCollapse, { show: true });
        }
    }
});
</script>