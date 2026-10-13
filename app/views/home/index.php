<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-chart-line text-primary me-2"></i>Dashboard
        </h1>
        <p class="text-muted mb-0 small">
            <i class="fas fa-clock me-1"></i><?php echo date('l, d \d\e F \d\e Y - H:i'); ?>
        </p>
    </div>
    <div>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.location.reload()">
            <i class="fas fa-sync-alt me-1"></i>Actualizar
        </button>
    </div>
</div>

<!-- ============================================================
     ALERTAS DEL SISTEMA
     ============================================================ -->
<?php if (!empty($alertas)): ?>
<div class="row g-2 mb-4">
    <?php foreach ($alertas as $alerta): ?>
    <div class="col-md-6 col-lg-4">
        <div class="alert alert-<?php echo $alerta['tipo']; ?> d-flex align-items-center mb-0 py-2 shadow-sm" role="alert">
            <i class="fas <?php echo $alerta['icono']; ?> fa-2x me-3"></i>
            <div class="flex-grow-1">
                <strong class="d-block small"><?php echo $alerta['titulo']; ?></strong>
                <span class="small"><?php echo $alerta['mensaje']; ?></span>
            </div>
            <?php if (isset($alerta['url'])): ?>
                <a href="<?php echo $alerta['url']; ?>" class="btn btn-sm btn-<?php echo $alerta['tipo']; ?>">
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ============================================================
     KPIs PRINCIPALES
     ============================================================ -->
<div class="row g-3 mb-4">
    <!-- Ventas Hoy -->
    <div class="col-md-6 col-lg-3">
        <div class="card kpi-card kpi-primary h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="kpi-label mb-1">Ventas Hoy</p>
                        <h2 class="kpi-value mb-1"><?php echo $kpis['ventas_hoy']['total']; ?></h2>
                        <p class="kpi-amount mb-2">S/ <?php echo number_format($kpis['ventas_hoy']['monto'], 2); ?></p>
                        
                        <?php 
                        $tendencia = $kpis['ventas_hoy']['tendencia'];
                        $clase = $tendencia >= 0 ? 'success' : 'danger';
                        $icono = $tendencia >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                        ?>
                        <small class="badge bg-<?php echo $clase; ?>">
                            <i class="fas <?php echo $icono; ?> me-1"></i>
                            <?php echo number_format(abs($tendencia), 1); ?>% vs ayer
                        </small>
                    </div>
                    <div class="kpi-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas Mes -->
    <div class="col-md-6 col-lg-3">
        <div class="card kpi-card kpi-success h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="kpi-label mb-1">Ventas del Mes</p>
                        <h2 class="kpi-value mb-1"><?php echo $kpis['ventas_mes']['total']; ?></h2>
                        <p class="kpi-amount mb-2">S/ <?php echo number_format($kpis['ventas_mes']['monto'], 2); ?></p>
                        
                        <?php 
                        $tendencia = $kpis['ventas_mes']['tendencia'];
                        $clase = $tendencia >= 0 ? 'success' : 'danger';
                        $icono = $tendencia >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                        ?>
                        <small class="badge bg-<?php echo $clase; ?>">
                            <i class="fas <?php echo $icono; ?> me-1"></i>
                            <?php echo number_format(abs($tendencia), 1); ?>% vs mes ant.
                        </small>
                    </div>
                    <div class="kpi-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Promedio -->
    <div class="col-md-6 col-lg-3">
        <div class="card kpi-card kpi-info h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="kpi-label mb-1">Ticket Promedio</p>
                        <h2 class="kpi-value mb-1">S/ <?php echo number_format($kpis['tickets_promedio'], 2); ?></h2>
                        <p class="kpi-amount mb-2">por venta</p>
                        <small class="text-white-50">
                            <i class="fas fa-users me-1"></i>
                            <?php echo $kpis['clientes_registrados']; ?> clientes
                        </small>
                    </div>
                    <div class="kpi-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ocupabilidad -->
    <div class="col-md-6 col-lg-3">
        <div class="card kpi-card kpi-warning h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="kpi-label mb-1">Ocupabilidad</p>
                        <h2 class="kpi-value mb-1"><?php echo $ocupabilidad['porcentaje']; ?>%</h2>
                        <p class="kpi-amount mb-2">
                            <?php echo $ocupabilidad['ocupados']; ?> / <?php echo $ocupabilidad['total']; ?> asientos
                        </p>
                        <small class="text-white-50">
                            <i class="fas fa-calendar me-1"></i>
                            Últimos 30 días
                        </small>
                    </div>
                    <div class="kpi-icon">
                        <i class="fas fa-chair"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     KPIs SECUNDARIOS
     ============================================================ -->
<div class="row g-3 mb-4">
    <!-- Caja Actual -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="fas fa-cash-register fa-lg text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Caja Actual</p>
                        <h4 class="mb-0">S/ <?php echo number_format($kpis['caja_actual'], 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Viajes Programados -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="fas fa-bus fa-lg text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Viajes Programados</p>
                        <h4 class="mb-0"><?php echo $kpis['viajes_programados']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Encomiendas Pendientes -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="fas fa-box fa-lg text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Encomiendas Pendientes</p>
                        <h4 class="mb-0"><?php echo $kpis['encomiendas_pendientes']; ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Giros Pendientes -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="fas fa-money-bill-transfer fa-lg text-info"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Giros Pendientes</p>
                        <h4 class="mb-0">
                            <?php echo $kpis['giros_pendientes']['total']; ?>
                            <small class="text-muted">/ S/ <?php echo number_format($kpis['giros_pendientes']['monto'], 0); ?></small>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     GRÁFICOS
     ============================================================ -->
<div class="row g-3 mb-4">
    <!-- Ventas últimos 7 días -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-area text-primary me-2"></i>Ventas Últimos 7 Días
                </h6>
                <span class="badge bg-primary">
                    Total: S/ <?php 
                        $total_semana = array_sum(array_column($ventas_7dias, 'monto'));
                        echo number_format($total_semana, 2); 
                    ?>
                </span>
            </div>
            <div class="card-body">
                <canvas id="chartVentas7Dias" height="90"></canvas>
            </div>
        </div>
    </div>

    <!-- Métodos de pago -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-pie text-success me-2"></i>Métodos de Pago
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartMetodosPago" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Top Rutas -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-route text-warning me-2"></i>Top 5 Rutas Más Vendidas
                </h6>
            </div>
            <div class="card-body">
                <canvas id="chartTopRutas" height="180"></canvas>
            </div>
        </div>
    </div>

    <!-- Rendimiento Vendedores -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-trophy text-danger me-2"></i>Rendimiento de Vendedores (Mes)
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Vendedor</th>
                                <th class="text-center">Ventas</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rendimiento_vendedores)): ?>
                                <?php $i = 1; foreach ($rendimiento_vendedores as $v): ?>
                                <tr>
                                    <td>
                                        <?php if ($i == 1): ?>
                                            <i class="fas fa-medal text-warning"></i>
                                        <?php elseif ($i == 2): ?>
                                            <i class="fas fa-medal text-secondary"></i>
                                        <?php elseif ($i == 3): ?>
                                            <i class="fas fa-medal" style="color:#cd7f32;"></i>
                                        <?php else: ?>
                                            <?php echo $i; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($v['vendedor']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?php echo $v['total_ventas']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong>S/ <?php echo number_format($v['monto'], 2); ?></strong>
                                    </td>
                                </tr>
                                <?php $i++; endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        Sin datos de vendedores
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     ACTIVIDAD RECIENTE
     ============================================================ -->
<div class="row g-3">
    <!-- Últimas Ventas -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-ticket-alt text-primary me-2"></i>Últimas Ventas
                </h6>
                <a href="<?php echo BASE_URL; ?>ventas" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($ultimas_ventas)): ?>
                        <?php foreach ($ultimas_ventas as $v): ?>
                        <a href="<?php echo BASE_URL; ?>ventas/show/<?php echo $v['id']; ?>" 
                           class="list-group-item list-group-item-action py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="fw-bold d-block">
                                        <?php echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-route me-1"></i>
                                        <?php echo $v['origen'] . ' → ' . $v['destino']; ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('d/m H:i', strtotime($v['fecha_venta'])); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php 
                                        echo $v['metodo_pago'] == 'EFECTIVO' ? 'success' : 
                                            ($v['metodo_pago'] == 'TARJETA' ? 'info' : 'primary'); 
                                    ?>">
                                        <?php echo $v['metodo_pago']; ?>
                                    </span>
                                    <br>
                                    <strong class="text-success small">S/ <?php echo number_format($v['precio_pagado'], 2); ?></strong>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <small>Sin ventas registradas</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Encomiendas -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-box text-warning me-2"></i>Últimas Encomiendas
                </h6>
                <a href="<?php echo BASE_URL; ?>encomiendas" class="btn btn-sm btn-outline-warning">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($ultimas_encomiendas)): ?>
                        <?php foreach ($ultimas_encomiendas as $e): ?>
                        <a href="<?php echo BASE_URL; ?>encomiendas/show/<?php echo $e['id']; ?>" 
                           class="list-group-item list-group-item-action py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="fw-bold d-block">
                                        <i class="fas fa-barcode me-1"></i><?php echo $e['guia']; ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        Para: <?php echo htmlspecialchars($e['destinatario_nombre'] . ' ' . $e['destinatario_apellido']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo $e['origen'] . ' → ' . $e['destino']; ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php 
                                        $badge = 'secondary';
                                        if ($e['estado'] == 'REGISTRADO') $badge = 'info';
                                        if ($e['estado'] == 'EN_BODEGA') $badge = 'warning';
                                        if ($e['estado'] == 'EN_TRANSITO') $badge = 'primary';
                                        if ($e['estado'] == 'ENTREGADO') $badge = 'success';
                                        if ($e['estado'] == 'DEVUELTO') $badge = 'danger';
                                        echo $badge;
                                    ?>" style="font-size:0.65rem;">
                                        <?php echo $e['estado']; ?>
                                    </span>
                                    <br>
                                    <strong class="text-warning small">S/ <?php echo number_format($e['costo_envio'], 2); ?></strong>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <small>Sin encomiendas</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Últimos Giros -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-money-bill-transfer text-info me-2"></i>Últimos Giros
                </h6>
                <a href="<?php echo BASE_URL; ?>giros" class="btn btn-sm btn-outline-info">
                    Ver todos
                </a>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (!empty($ultimos_giros)): ?>
                        <?php foreach ($ultimos_giros as $g): ?>
                        <a href="<?php echo BASE_URL; ?>giros/show/<?php echo $g['id']; ?>" 
                           class="list-group-item list-group-item-action py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="fw-bold d-block">
                                        <i class="fas fa-barcode me-1"></i><?php echo $g['codigo']; ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        Para: <?php echo htmlspecialchars($g['destinatario_nombre'] . ' ' . $g['destinatario_apellido']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?php echo date('d/m H:i', strtotime($g['fecha_registro'])); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php 
                                        $badge = 'secondary';
                                        if ($g['estado'] == 'REGISTRADO') $badge = 'warning';
                                        if ($g['estado'] == 'PAGADO') $badge = 'success';
                                        if ($g['estado'] == 'ANULADO') $badge = 'danger';
                                        echo $badge;
                                    ?>" style="font-size:0.65rem;">
                                        <?php echo $g['estado']; ?>
                                    </span>
                                    <br>
                                    <strong class="text-info small">
                                        <?php echo $g['moneda']; ?> <?php echo number_format($g['monto'], 2); ?>
                                    </strong>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            <small>Sin giros</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SCRIPTS PARA GRÁFICOS
     ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // Datos desde PHP
    // ============================================
    const ventas7dias = <?php echo json_encode($ventas_7dias); ?>;
    const metodosPago = <?php echo json_encode($metodos_pago); ?>;
    const topRutas = <?php echo json_encode($top_rutas); ?>;

    // Paleta de colores
    const colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14'];

    // ============================================
    // GRÁFICO 1: Ventas últimos 7 días
    // ============================================
    if (ventas7dias.length > 0) {
        new Chart(document.getElementById('chartVentas7Dias'), {
            type: 'line',
            data: {
                labels: ventas7dias.map(d => {
                    const fecha = new Date(d.fecha + 'T00:00:00');
                    const dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
                    return dias[fecha.getDay()] + ' ' + fecha.getDate();
                }),
                datasets: [
                    {
                        label: 'Monto (S/)',
                        data: ventas7dias.map(d => d.monto),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0d6efd',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Ventas',
                        data: ventas7dias.map(d => d.total),
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, padding: 15 }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Monto: S/ ' + context.parsed.y.toFixed(2);
                                }
                                return 'Ventas: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: { callback: v => 'S/ ' + v }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    }

    // ============================================
    // GRÁFICO 2: Métodos de Pago
    // ============================================
    if (metodosPago.length > 0) {
        new Chart(document.getElementById('chartMetodosPago'), {
            type: 'doughnut',
            data: {
                labels: metodosPago.map(d => d.metodo_pago),
                datasets: [{
                    data: metodosPago.map(d => d.monto),
                    backgroundColor: colors.slice(0, metodosPago.length),
                    borderWidth: 3,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const porcentaje = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': S/ ' + context.parsed.toFixed(2) + ' (' + porcentaje + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // ============================================
    // GRÁFICO 3: Top Rutas
    // ============================================
    if (topRutas.length > 0) {
        new Chart(document.getElementById('chartTopRutas'), {
            type: 'bar',
            data: {
                labels: topRutas.map(d => d.ruta),
                datasets: [{
                    label: 'Ventas',
                    data: topRutas.map(d => d.total),
                    backgroundColor: [
                        'rgba(13, 110, 253, 0.8)',
                        'rgba(25, 135, 84, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(13, 202, 240, 0.8)'
                    ],
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const item = topRutas[context.dataIndex];
                                return [
                                    'Ventas: ' + item.total,
                                    'Monto: S/ ' + parseFloat(item.monto).toFixed(2)
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
});
</script>

<!-- ============================================================
     ESTILOS PERSONALIZADOS
     ============================================================ -->
<style>
    /* Tarjetas KPI con gradientes */
    .kpi-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .kpi-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
    }
    
    .kpi-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: white;
    }
    
    .kpi-success {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
        color: white;
    }
    
    .kpi-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
        color: white;
    }
    
    .kpi-warning {
        background: linear-gradient(135deg, #ffc107 0%, #cc9a06 100%);
        color: white;
    }
    
    .kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
        font-weight: 600;
    }
    
    .kpi-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0;
    }
    
    .kpi-amount {
        font-size: 0.95rem;
        opacity: 0.9;
    }
    
    .kpi-icon {
        font-size: 3rem;
        opacity: 0.2;
    }
    
    /* Tarjetas generales */
    .card {
        border-radius: 10px;
    }
    
    /* List group items */
    .list-group-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        border-left-color: #0d6efd;
        background: #f8f9fa;
    }
    
    /* Chart containers */
    canvas {
        max-height: 300px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .kpi-value {
            font-size: 1.8rem;
        }
        .kpi-icon {
            font-size: 2.5rem;
        }
    }
</style>