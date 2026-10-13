<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-cash-register me-2 text-danger"></i><?php echo $titulo; ?>
        </h1>
<p class="text-muted mb-0 small">
    <i class="fas fa-user-circle me-1"></i>
    <?php echo htmlspecialchars(($usuario_seleccionado['nombre'] ?? 'Usuario') . ' ' . ($usuario_seleccionado['apellido'] ?? '')); ?>
    <span class="mx-2">|</span>
    <i class="fas fa-calendar-day me-1"></i>
    <?php echo date('d/m/Y', strtotime($filtros['fecha'] ?? date('Y-m-d'))); ?>
</p>
    </div>
    <div class="btn-group">
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/caja?fecha=<?php echo $filtros['fecha']; ?>&usuario_id=<?php echo $filtros['usuario_id']; ?>" 
           target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <a href="<?php echo BASE_URL; ?>reportes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- ============================================================
     FILTROS
     ============================================================ -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">
                    <i class="fas fa-user me-1"></i>Usuario / Cajero
                </label>
<select name="usuario_id" class="form-select form-select-sm">
    <?php if (!empty($usuarios) && is_array($usuarios)): ?>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?php echo $u['id']; ?>" 
                    <?php echo (($filtros['usuario_id'] ?? 0) == $u['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')); ?>
                (<?php echo $u['rol_nombre'] ?? 'Sin rol'; ?>)
            </option>
        <?php endforeach; ?>
    <?php else: ?>
        <option value="">Sin usuarios disponibles</option>
    <?php endif; ?>
</select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">
                    <i class="fas fa-calendar-day me-1"></i>Fecha
                </label>
                <input type="date" name="fecha" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha']; ?>">
            </div>
            <div class="col-md-5">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search me-1"></i>Consultar
                </button>
                <a href="<?php echo BASE_URL; ?>reportes/caja" class="btn btn-secondary btn-sm">
                    <i class="fas fa-undo me-1"></i>Hoy
                </a>
                <a href="<?php echo BASE_URL; ?>reportes/caja?fecha=<?php echo date('Y-m-d', strtotime('-1 day')); ?>" 
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Ayer
                </a>
                <a href="<?php echo BASE_URL; ?>reportes/caja?fecha=<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                   class="btn btn-outline-secondary btn-sm">
                    Mañana<i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<?php 
// Calcular totales
$total_general = 0;
$total_ventas_count = 0;
foreach ($resumen as $r) {
    $total_general += $r['total'];
    $total_ventas_count += $r['cantidad'];
}
?>

<!-- ============================================================
     ENCABEZADO DEL REPORTE (visible al imprimir)
     ============================================================ -->
<div class="card mb-4 border-0 shadow-sm" id="encabezadoReporte">
    <div class="card-body text-center">
        <h3 class="text-primary mb-1"><?php echo FACTURACION_RAZON_SOCIAL; ?></h3>
        <p class="mb-1 text-muted small">
            RUC: <?php echo FACTURACION_RUC ?? '20561234567'; ?> | 
            <?php echo FACTURACION_DIRECCION ?? 'Av. Principal 123'; ?>
        </p>
        <p class="mb-0 text-muted small">
            <i class="fas fa-phone me-1"></i><?php echo FACTURACION_TELEFONO ?? '(01) 555-5555'; ?>
            <span class="mx-2">|</span>
            <i class="fas fa-envelope me-1"></i><?php echo FACTURACION_EMAIL ?? 'info@empresa.com'; ?>
        </p>
        <hr>
        <h4 class="mb-2">CIERRE DE CAJA</h4>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <p class="mb-0"><strong>Cajero:</strong> <?php echo htmlspecialchars($usuario_seleccionado['nombre'] . ' ' . $usuario_seleccionado['apellido']); ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-0"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($filtros['fecha'])); ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-0"><strong>Generado:</strong> <?php echo date('d/m/Y H:i'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     RESUMEN POR MÉTODO DE PAGO
     ============================================================ -->
<div class="row g-3 mb-4">
    <?php 
    $iconos = [
        'EFECTIVO' => 'fa-money-bill-wave',
        'TARJETA' => 'fa-credit-card',
        'TRANSFERENCIA' => 'fa-exchange-alt',
        'OTRO' => 'fa-wallet'
    ];
    $colores = [
        'EFECTIVO' => 'success',
        'TARJETA' => 'info',
        'TRANSFERENCIA' => 'primary',
        'OTRO' => 'secondary'
    ];
    
    // Si no hay datos, mostrar solo efectivo en cero
    if (empty($resumen)) {
        $resumen = [['metodo_pago' => 'EFECTIVO', 'cantidad' => 0, 'total' => 0]];
    }
    
    foreach ($resumen as $r): 
        $color = $colores[$r['metodo_pago']] ?? 'secondary';
        $icono = $iconos[$r['metodo_pago']] ?? 'fa-wallet';
    ?>
    <div class="col-md-3">
        <div class="card bg-<?php echo $color; ?> text-white shadow-sm">
            <div class="card-body text-center">
                <i class="fas <?php echo $icono; ?> fa-2x mb-2 opacity-75"></i>
                <h6 class="mb-1"><?php echo $r['metodo_pago']; ?></h6>
                <h3 class="mb-0"><?php echo $r['cantidad']; ?> <small class="fs-6">ventas</small></h3>
                <h4 class="mb-0 mt-2">S/ <?php echo number_format($r['total'], 2); ?></h4>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Total General -->
    <div class="col-md-3">
        <div class="card bg-dark text-white shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-calculator fa-2x mb-2 opacity-75"></i>
                <h6 class="mb-1">TOTAL GENERAL</h6>
                <h3 class="mb-0"><?php echo $total_ventas_count; ?> <small class="fs-6">ventas</small></h3>
                <h4 class="mb-0 mt-2">S/ <?php echo number_format($total_general, 2); ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     GRÁFICO DE DISTRIBUCIÓN
     ============================================================ -->
<?php if ($total_general > 0): ?>
<div class="row g-3 mb-4 no-print">
    <div class="col-md-4 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-pie text-primary me-2"></i>Distribución por Método
                </h6>
            </div>
            <div class="card-body">
                <canvas id="graficoCaja" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================
     DETALLE DE VENTAS
     ============================================================ -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-list me-2"></i>Detalle de Ventas del Día
            <span class="badge bg-light text-dark ms-2"><?php echo count($detalle); ?> registros</span>
        </div>
        <div>
            <span class="badge bg-success">
                <i class="fas fa-check-circle me-1"></i>Cerrado
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="tablaCaja">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 80px;">Hora</th>
                        <th>Comprobante</th>
                        <th>Cliente</th>
                        <th>Documento</th>
                        <th>Ruta</th>
                        <th style="width: 80px;" class="text-center">Asiento</th>
                        <th style="width: 130px;" class="text-center">Método Pago</th>
                        <th style="width: 120px;" class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detalle)): ?>
                        <?php $i = 1; foreach ($detalle as $d): ?>
                        <tr>
                            <td class="text-muted small"><?php echo $i++; ?></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?php echo date('H:i', strtotime($d['fecha_venta'])); ?>
                                </span>
                            </td>
                            <td><strong><?php echo $d['numero_comprobante']; ?></strong></td>
                            <td>
                                <i class="fas fa-user-circle text-primary me-1"></i>
                                <?php echo htmlspecialchars($d['cliente_nombre'] . ' ' . $d['cliente_apellido']); ?>
                            </td>
                            <td><small class="text-muted"><?php echo $d['numero_documento']; ?></small></td>
                            <td>
                                <i class="fas fa-route text-secondary me-1"></i>
                                <?php echo htmlspecialchars($d['origen'] . ' → ' . $d['destino']); ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo $d['numero_asiento']; ?></span>
                            </td>
                            <td class="text-center">
                                <?php 
                                    $badge = 'secondary';
                                    $icono = 'fa-wallet';
                                    if ($d['metodo_pago'] == 'EFECTIVO') { $badge = 'success'; $icono = 'fa-money-bill-wave'; }
                                    if ($d['metodo_pago'] == 'TARJETA') { $badge = 'info'; $icono = 'fa-credit-card'; }
                                    if ($d['metodo_pago'] == 'TRANSFERENCIA') { $badge = 'primary'; $icono = 'fa-exchange-alt'; }
                                ?>
                                <span class="badge bg-<?php echo $badge; ?>">
                                    <i class="fas <?php echo $icono; ?> me-1"></i>
                                    <?php echo $d['metodo_pago']; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <strong class="text-success">
                                    S/ <?php echo number_format($d['precio_pagado'], 2); ?>
                                </strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3 d-block opacity-50"></i>
                                <h5 class="text-muted">No hay ventas registradas</h5>
                                <p class="text-muted small mb-0">
                                    No se encontraron ventas para el usuario y fecha seleccionados.
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($detalle)): ?>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="8" class="text-end">
                            <i class="fas fa-calculator me-1"></i>TOTAL DEL DÍA:
                        </th>
                        <th class="text-end">
                            <h5 class="mb-0 text-warning">
                                S/ <?php echo number_format($total_general, 2); ?>
                            </h5>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================
     RESUMEN ADICIONAL (para impresión)
     ============================================================ -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-chart-bar text-primary me-2"></i>Resumen Financiero
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <?php foreach ($resumen as $r): ?>
                        <tr>
                            <td>
                                <?php echo $r['metodo_pago']; ?>
                                <small class="text-muted">(<?php echo $r['cantidad']; ?> ventas)</small>
                            </td>
                            <td class="text-end">
                                <strong>S/ <?php echo number_format($r['total'], 2); ?></strong>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <th>TOTAL GENERAL</th>
                            <th class="text-end">
                                <h5 class="mb-0 text-success">
                                    S/ <?php echo number_format($total_general, 2); ?>
                                </h5>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-info-circle text-primary me-2"></i>Información Adicional
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td><i class="fas fa-user me-1 text-muted"></i>Cajero:</td>
                            <td class="text-end">
                                <strong><?php echo htmlspecialchars($usuario_seleccionado['nombre'] . ' ' . $usuario_seleccionado['apellido']); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-calendar-day me-1 text-muted"></i>Fecha:</td>
                            <td class="text-end">
                                <strong><?php echo date('d/m/Y', strtotime($filtros['fecha'])); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-clock me-1 text-muted"></i>Generado:</td>
                            <td class="text-end">
                                <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="fas fa-shopping-cart me-1 text-muted"></i>Total transacciones:</td>
                            <td class="text-end">
                                <span class="badge bg-primary"><?php echo $total_ventas_count; ?></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     FIRMAS (solo impresión)
     ============================================================ -->
<div class="row mt-5 firma-section">
    <div class="col-6 text-center">
        <div style="border-top: 1px solid #000; width: 200px; margin: 60px auto 5px;"></div>
        <p class="mb-0"><strong>Firma del Cajero</strong></p>
        <small class="text-muted"><?php echo htmlspecialchars($usuario_seleccionado['nombre'] . ' ' . $usuario_seleccionado['apellido']); ?></small>
    </div>
    <div class="col-6 text-center">
        <div style="border-top: 1px solid #000; width: 200px; margin: 60px auto 5px;"></div>
        <p class="mb-0"><strong>Firma del Supervisor</strong></p>
        <small class="text-muted">Firma y Sello</small>
    </div>
</div>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($total_general > 0 && count($resumen) > 0): ?>
    // ============================================
    // Gráfico de distribución por método de pago
    // ============================================
    const metodos = <?php echo json_encode($resumen); ?>;
    const labels = metodos.map(m => m.metodo_pago);
    const datos = metodos.map(m => parseFloat(m.total));
    
    const colores = {
        'EFECTIVO': '#198754',
        'TARJETA': '#0dcaf0',
        'TRANSFERENCIA': '#0d6efd',
        'OTRO': '#6c757d'
    };
    const coloresArr = labels.map(l => colores[l] || '#6c757d');

    new Chart(document.getElementById('graficoCaja'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: datos,
                backgroundColor: coloresArr,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        usePointStyle: true, 
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const valor = context.parsed;
                            const porcentaje = total > 0 ? ((valor / total) * 100).toFixed(1) : 0;
                            return context.label + ': S/ ' + valor.toFixed(2) + ' (' + porcentaje + '%)';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // ============================================
    // Atajo de teclado: Ctrl + P para imprimir
    // ============================================
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
});
</script>

<!-- ============================================================
     ESTILOS PERSONALIZADOS
     ============================================================ -->
<style>
    /* Ocultar elementos al imprimir */
    @media print {
        .btn, .btn-group, nav, footer, .no-print, .sidebar, .navbar {
            display: none !important;
        }
        
        body {
            background: white !important;
            font-size: 11px;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            page-break-inside: avoid;
        }
        
        .card-header {
            background: #212529 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger, .bg-dark {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        .table-dark {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        #encabezadoReporte {
            margin-top: 0;
        }
        
        .firma-section {
            page-break-inside: avoid;
        }
        
        main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
    }
    
    /* Ajuste de tamaño de fuentes en pantallas pequeñas */
    @media (max-width: 768px) {
        .card-body h3 {
            font-size: 1.5rem;
        }
        .card-body h4 {
            font-size: 1.2rem;
        }
    }
</style>