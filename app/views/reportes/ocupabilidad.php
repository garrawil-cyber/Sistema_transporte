<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-chair me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/ocupabilidad?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
           class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Exportar PDF
        </a>
        <a href="<?php echo BASE_URL; ?>reportes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $filtros['fecha_fin']; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// Calcular estadísticas generales
$total_viajes = count($viajes);
$total_recaudado = 0;
$total_ocupacion = 0;
$total_asientos = 0;
$total_ocupados = 0;

foreach ($viajes as $v) {
    $total_recaudado += $v['total_recaudado'] ?? 0;
    $total_ocupacion += $v['porcentaje_ocupacion'] ?? 0;
    $total_asientos += $v['total_asientos'] ?? 0;
    $total_ocupados += $v['asientos_ocupados'] ?? 0;
}

$promedio_ocupacion = $total_viajes > 0 ? $total_ocupacion / $total_viajes : 0;
$tasa_global = $total_asientos > 0 ? ($total_ocupados / $total_asientos) * 100 : 0;
?>

<!-- Resumen general -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Viajes</h6>
                        <h3 class="mb-0"><?php echo $total_viajes; ?></h3>
                    </div>
                    <i class="fas fa-bus fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Recaudado</h6>
                        <h3 class="mb-0">S/ <?php echo number_format($total_recaudado, 2); ?></h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Tasa Global Ocupación</h6>
                        <h3 class="mb-0"><?php echo number_format($tasa_global, 1); ?>%</h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Ocupación Promedio</h6>
                        <h3 class="mb-0"><?php echo number_format($promedio_ocupacion, 1); ?>%</h3>
                    </div>
                    <i class="fas fa-chart-pie fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de ocupación general -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-bar me-2"></i>Ocupabilidad por Viaje
            </div>
            <div class="card-body">
                <canvas id="graficoOcupabilidad" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-pie me-2"></i>Distribución Global
            </div>
            <div class="card-body">
                <canvas id="graficoDistribucion" height="240"></canvas>
                <div class="text-center mt-2">
                    <small class="text-muted">
                        <strong><?php echo number_format($total_ocupados); ?></strong> ocupados / 
                        <strong><?php echo number_format($total_asientos); ?></strong> totales
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de ocupabilidad -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-list me-2"></i>Detalle de Ocupabilidad
            <span class="badge bg-light text-dark ms-2"><?php echo count($viajes); ?> viajes</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Ruta</th>
                        <th>Bus</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th class="text-center">Asientos</th>
                        <th class="text-center">Ocupados</th>
                        <th class="text-center">Libres</th>
                        <th class="text-center">% Ocupación</th>
                        <th class="text-end">Recaudado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($viajes as $v): 
                        $porcentaje = $v['porcentaje_ocupacion'] ?? 0;
                        $bar_color = 'success';
                        if ($porcentaje >= 80) $bar_color = 'success';
                        elseif ($porcentaje >= 50) $bar_color = 'info';
                        elseif ($porcentaje >= 30) $bar_color = 'warning';
                        else $bar_color = 'danger';
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td>
                            <i class="fas fa-route text-primary me-1"></i>
                            <strong><?php echo $v['origen'] . ' → ' . $v['destino']; ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $v['placa']; ?></span>
                            <br>
                            <small class="text-muted">Cap: <?php echo $v['capacidad_total']; ?></small>
                        </td>
                        <td>
                            <small>
                                <i class="fas fa-calendar-day me-1 text-muted"></i>
                                <?php echo date('d/m/Y', strtotime($v['fecha_salida'])); ?><br>
                                <i class="fas fa-clock me-1 text-muted"></i>
                                <?php echo date('H:i', strtotime($v['fecha_salida'])); ?>
                            </small>
                        </td>
                        <td>
                            <?php 
                                $badge = 'secondary';
                                if ($v['estado_viaje'] == 'PROGRAMADO') $badge = 'primary';
                                if ($v['estado_viaje'] == 'EN_CURSO') $badge = 'warning';
                                if ($v['estado_viaje'] == 'COMPLETADO') $badge = 'success';
                                if ($v['estado_viaje'] == 'CANCELADO') $badge = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $v['estado_viaje']; ?></span>
                        </td>
                        <td class="text-center">
                            <strong><?php echo $v['total_asientos']; ?></strong>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary"><?php echo $v['asientos_ocupados']; ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success"><?php echo $v['asientos_libres']; ?></span>
                        </td>
                        <td class="text-center" style="min-width: 150px;">
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php echo $bar_color; ?>" 
                                     role="progressbar" 
                                     style="width: <?php echo $porcentaje; ?>%"
                                     aria-valuenow="<?php echo $porcentaje; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <strong><?php echo number_format($porcentaje, 1); ?>%</strong>
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <strong class="text-success">
                                S/ <?php echo number_format($v['total_recaudado'] ?? 0, 2); ?>
                            </strong>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($viajes)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No hay viajes en el período seleccionado</h5>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($viajes)): ?>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="5" class="text-end">TOTALES:</th>
                        <th class="text-center"><?php echo $total_asientos; ?></th>
                        <th class="text-center"><?php echo $total_ocupados; ?></th>
                        <th class="text-center"><?php echo $total_asientos - $total_ocupados; ?></th>
                        <th class="text-center">
                            <strong><?php echo number_format($tasa_global, 1); ?>%</strong>
                        </th>
                        <th class="text-end">
                            <strong>S/ <?php echo number_format($total_recaudado, 2); ?></strong>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para los gráficos
    const viajes = <?php echo json_encode(array_map(function($v) {
        return [
            'ruta' => $v['origen'] . ' → ' . $v['destino'],
            'fecha' => date('d/m', strtotime($v['fecha_salida'])),
            'ocupados' => (int)$v['asientos_ocupados'],
            'libres' => (int)$v['asientos_libres'],
            'porcentaje' => (float)$v['porcentaje_ocupacion']
        ];
    }, $viajes)); ?>;

    // 1. Gráfico de barras por viaje
    if (viajes.length > 0) {
        const labels = viajes.map(v => v.ruta + '\n' + v.fecha);
        const porcentajes = viajes.map(v => v.porcentaje);
        
        new Chart(document.getElementById('graficoOcupabilidad'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: '% Ocupación',
                    data: porcentajes,
                    backgroundColor: porcentajes.map(p => {
                        if (p >= 80) return 'rgba(25, 135, 84, 0.7)';
                        if (p >= 50) return 'rgba(13, 202, 240, 0.7)';
                        if (p >= 30) return 'rgba(255, 193, 7, 0.7)';
                        return 'rgba(220, 53, 69, 0.7)';
                    }),
                    borderColor: porcentajes.map(p => {
                        if (p >= 80) return '#198754';
                        if (p >= 50) return '#0dcaf0';
                        if (p >= 30) return '#ffc107';
                        return '#dc3545';
                    }),
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Ocupación: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Gráfico circular global
    new Chart(document.getElementById('graficoDistribucion'), {
        type: 'doughnut',
        data: {
            labels: ['Ocupados', 'Libres'],
            datasets: [{
                data: [<?php echo $total_ocupados; ?>, <?php echo $total_asientos - $total_ocupados; ?>],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(25, 135, 84, 0.8)'
                ],
                borderColor: [
                    '#0d6efd',
                    '#198754'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = <?php echo $total_asientos; ?>;
                            const valor = context.parsed;
                            const porcentaje = total > 0 ? ((valor / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + valor + ' (' + porcentaje + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>

<style>
@media print {
    .btn, .card-header .badge, nav, footer, .no-print {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }
    canvas {
        max-height: 300px !important;
    }
}
</style>