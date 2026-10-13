<!-- Vista: encomiendas/index.php -->
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-warning bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-box text-warning fa-lg"></i>
                </span>
                <span><?php echo $titulo; ?></span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">Gestión de encomiendas y paquetería</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                <i class="fas fa-filter me-1"></i>Filtros
            </button>
            <a href="<?php echo BASE_URL; ?>encomiendas/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Nueva Encomienda
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
                               placeholder="Guía, remitente, destinatario o DNI..."
                               value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Estado</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="REGISTRADO"  <?php echo ($filtros['estado'] ?? '') == 'REGISTRADO'  ? 'selected' : ''; ?>>Registrado</option>
                            <option value="EN_BODEGA"   <?php echo ($filtros['estado'] ?? '') == 'EN_BODEGA'   ? 'selected' : ''; ?>>En Bodega</option>
                            <option value="EN_TRANSITO" <?php echo ($filtros['estado'] ?? '') == 'EN_TRANSITO' ? 'selected' : ''; ?>>En Tránsito</option>
                            <option value="ENTREGADO"   <?php echo ($filtros['estado'] ?? '') == 'ENTREGADO'   ? 'selected' : ''; ?>>Entregado</option>
                            <option value="DEVUELTO"    <?php echo ($filtros['estado'] ?? '') == 'DEVUELTO'    ? 'selected' : ''; ?>>Devuelto</option>
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
                        <a href="<?php echo BASE_URL; ?>encomiendas" class="btn btn-sm btn-secondary" title="Limpiar">
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
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Registradas</div>
                        <div class="kpi-value"><?php echo $stats['registradas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">En Bodega</div>
                        <div class="kpi-value"><?php echo $stats['en_bodega'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">En Tránsito</div>
                        <div class="kpi-value"><?php echo $stats['en_transito'] ?? 0; ?></div>
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
                        <div class="kpi-label text-muted">Entregadas</div>
                        <div class="kpi-value"><?php echo $stats['entregadas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO + RESUMEN LATERAL -->
    <div class="row g-3 mb-4">
        <?php if (!empty($chartData)): ?>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 d-flex align-items-center">
                            <i class="fas fa-chart-bar text-primary me-2"></i>
                            Encomiendas últimos 7 días
                        </h6>
                        <small class="text-muted">
                            Total semana: <strong><?php echo $stats['total_semana'] ?? 0; ?></strong>
                            &nbsp;|&nbsp;
                            S/ <strong><?php echo number_format($stats['ingresos_semana'] ?? 0, 2); ?></strong>
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
                                         title="<?php echo $d['total']; ?> encomiendas | S/ <?php echo number_format($d['ingresos'], 2); ?>">
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
                    <div class="kpi-label text-muted mb-1">Ingresos Totales</div>
                    <div class="display-6 fw-bold text-success mb-0">
                        S/ <?php echo number_format($stats['ingresos_totales'] ?? 0, 2); ?>
                    </div>
                    
                    <!-- Desglose claro: Total vs Semana -->
                    <div class="d-flex justify-content-around mt-3 pt-3 border-top">
                        <div>
                            <div class="text-muted small">Histórico</div>
                            <div class="fw-bold"><?php echo $stats['total'] ?? 0; ?></div>
                        </div>
                        <div>
                            <div class="text-muted small">Esta semana</div>
                            <div class="fw-bold text-primary"><?php echo $stats['total_semana'] ?? 0; ?></div>
                        </div>
                    </div>
                    
                    <?php if (($stats['devueltas'] ?? 0) > 0): ?>
                        <div class="text-danger small mt-2">
                            <i class="fas fa-undo me-1"></i>
                            <?php echo $stats['devueltas']; ?> devueltas
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
                            <th>Guía</th>
                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th>Ruta</th>
                            <th class="text-center">Peso</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Costo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($encomiendas)): ?>
                            <?php foreach ($encomiendas as $e): ?>
                            <tr>
                                <td data-label="Guía"><strong><?php echo htmlspecialchars($e['guia']); ?></strong></td>
                                <td data-label="Remitente">
                                    <small><?php echo htmlspecialchars($e['remitente_nombre'] . ' ' . $e['remitente_apellido']); ?></small>
                                    <br><small class="text-muted"><?php echo $e['remitente_dni'] ?? '-'; ?></small>
                                </td>
                                <td data-label="Destinatario">
                                    <small><?php echo htmlspecialchars($e['destinatario_nombre'] . ' ' . $e['destinatario_apellido']); ?></small>
                                    <br><small class="text-muted"><?php echo $e['destinatario_dni'] ?? '-'; ?></small>
                                </td>
                                <td data-label="Ruta"><small><?php echo htmlspecialchars($e['origen'] . ' → ' . $e['destino']); ?></small></td>
                                <td data-label="Peso" class="text-center">
                                    <span class="badge bg-info"><?php echo $e['peso_kg']; ?> kg</span>
                                </td>
                                <td data-label="Estado" class="text-center">
                                    <?php 
                                        $badge = 'secondary';
                                        if ($e['estado'] == 'REGISTRADO')  $badge = 'info';
                                        if ($e['estado'] == 'EN_BODEGA')   $badge = 'warning';
                                        if ($e['estado'] == 'EN_TRANSITO') $badge = 'primary';
                                        if ($e['estado'] == 'ENTREGADO')   $badge = 'success';
                                        if ($e['estado'] == 'DEVUELTO')    $badge = 'danger';
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>"><?php echo $e['estado']; ?></span>
                                </td>
                                <td data-label="Costo" class="text-end">
                                    <strong class="text-success">S/ <?php echo number_format($e['costo_envio'], 2); ?></strong>
                                </td>
                                <td data-label="Acciones" class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo BASE_URL; ?>encomiendas/show/<?php echo $e['id']; ?>" class="btn btn-outline-info" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                        <a href="<?php echo BASE_URL; ?>encomiendas/printGuide/<?php echo $e['id']; ?>" target="_blank" class="btn btn-outline-secondary" title="Imprimir guía"><i class="fas fa-print"></i></a>
                                        <?php if ($e['estado'] != 'ENTREGADO' && $e['estado'] != 'DEVUELTO'): ?>
                                            <a href="#" class="btn btn-outline-success" title="Marcar como entregado"
                                               onclick="return confirmarPersonalizado('¿Marcar como entregado?', 'La encomienda <strong><?php echo htmlspecialchars($e['guia']); ?></strong> será marcada como ENTREGADA.', 'Sí, entregar', '#198754', function() { window.location.href='<?php echo BASE_URL; ?>encomiendas/updateStatus/<?php echo $e['id']; ?>?estado=ENTREGADO'; })">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (($_SESSION['rol_id'] ?? 0) == 1 && $e['estado'] != 'ENTREGADO'): ?>
                                            <a href="#" class="btn btn-outline-danger" title="Eliminar"
                                               onclick="return confirmarEliminar('<?php echo BASE_URL; ?>encomiendas/delete/<?php echo $e['id']; ?>', 'Guía <?php echo htmlspecialchars($e['guia']); ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-box-open fa-4x text-muted mb-3 d-block opacity-50"></i>
                                    <h5 class="text-muted">No hay encomiendas registradas</h5>
                                    <a href="<?php echo BASE_URL; ?>encomiendas/create" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-1"></i>Registrar primera encomienda
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

<a href="<?php echo BASE_URL; ?>encomiendas/create" class="fab-mobile" title="Nueva Encomienda">
    <i class="fas fa-plus"></i>
</a>