<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-ticket-alt me-2 text-primary"></i><?php echo $titulo; ?></h1>
    <div class="btn-group">
        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/ventas?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i>Excel
        </a>
        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/ventas?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <a href="<?php echo BASE_URL; ?>reportes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?php echo $filtros['fecha_fin']; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Ruta</label>
                <select name="ruta_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($rutas as $r): ?>
                        <option value="<?php echo $r['id']; ?>" <?php echo $filtros['ruta_id'] == $r['id'] ? 'selected' : ''; ?>>
                            <?php echo $r['origen'] . ' → ' . $r['destino']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Vendedor</label>
                <select name="usuario_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo $filtros['usuario_id'] == $u['id'] ? 'selected' : ''; ?>>
                            <?php echo $u['nombre'] . ' ' . $u['apellido']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Resumen -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="mb-0">Total Ventas</h6>
                <h3 class="mb-0"><?php echo $resumen['total_ventas'] ?? 0; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="mb-0">Total Recaudado</h6>
                <h3 class="mb-0">S/ <?php echo number_format($resumen['total_recaudado'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="mb-0">Promedio</h6>
                <h3 class="mb-0">S/ <?php echo number_format($resumen['promedio'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="mb-0">Métodos de Pago</h6>
                <small>
                    💵 Efectivo: <?php echo $resumen['efectivo'] ?? 0; ?><br>
                    💳 Tarjeta: <?php echo $resumen['tarjeta'] ?? 0; ?><br>
                    🏦 Transf: <?php echo $resumen['transferencia'] ?? 0; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Ventas por ruta y vendedor -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white"><i class="fas fa-route me-2"></i>Ventas por Ruta</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Ruta</th><th class="text-end">Ventas</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_por_ruta as $v): ?>
                        <tr>
                            <td><?php echo $v['origen'] . ' → ' . $v['destino']; ?></td>
                            <td class="text-end"><?php echo $v['total_ventas']; ?></td>
                            <td class="text-end">S/ <?php echo number_format($v['total_recaudado'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white"><i class="fas fa-user-tie me-2"></i>Ventas por Vendedor</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Vendedor</th><th class="text-end">Ventas</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas_por_vendedor as $v): ?>
                        <tr>
                            <td><?php echo $v['vendedor']; ?></td>
                            <td class="text-end"><?php echo $v['total_ventas']; ?></td>
                            <td class="text-end">S/ <?php echo number_format($v['total_recaudado'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Detalle -->
<div class="card">
    <div class="card-header bg-dark text-white"><i class="fas fa-list me-2"></i>Detalle de Ventas (<?php echo count($ventas); ?> registros)</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Comprobante</th><th>Fecha</th><th>Cliente</th>
                        <th>Ruta</th><th>Asiento</th><th>Pago</th><th class="text-end">Total</th><th>Vendedor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($ventas as $v): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $v['numero_comprobante']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_venta'])); ?></td>
                        <td><?php echo $v['cliente_nombre'] . ' ' . $v['cliente_apellido']; ?></td>
                        <td><?php echo $v['origen'] . ' → ' . $v['destino']; ?></td>
                        <td><?php echo $v['numero_asiento']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $v['metodo_pago']; ?></span></td>
                        <td class="text-end"><strong>S/ <?php echo number_format($v['precio_pagado'], 2); ?></strong></td>
                        <td><?php echo $v['vendedor']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="7" class="text-end">TOTAL:</th>
                        <th class="text-end">S/ <?php echo number_format($resumen['total_recaudado'] ?? 0, 2); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>