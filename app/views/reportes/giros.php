<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-money-bill-transfer me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/giros?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
           class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Exportar PDF
        </a>
        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/giros?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i>Exportar Excel
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
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $filtros['fecha_fin']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="REGISTRADO" <?php echo ($filtros['estado'] == 'REGISTRADO') ? 'selected' : ''; ?>>Registrado</option>
                    <option value="PAGADO" <?php echo ($filtros['estado'] == 'PAGADO') ? 'selected' : ''; ?>>Pagado</option>
                    <option value="ANULADO" <?php echo ($filtros['estado'] == 'ANULADO') ? 'selected' : ''; ?>>Anulado</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Total Giros</h6>
                        <h3 class="mb-0"><?php echo $resumen['total_giros'] ?? 0; ?></h3>
                    </div>
                    <i class="fas fa-exchange-alt fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Monto Total</h6>
                        <h3 class="mb-0">S/ <?php echo number_format($resumen['total_monto'] ?? 0, 2); ?></h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Comisiones</h6>
                        <h3 class="mb-0">S/ <?php echo number_format($resumen['total_comision'] ?? 0, 2); ?></h3>
                    </div>
                    <i class="fas fa-percentage fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="mb-1">Por Estado</h6>
                <small>
                    📝 Registrados: <?php echo $resumen['registrados'] ?? 0; ?><br>
                    ✅ Pagados: <?php echo $resumen['pagados'] ?? 0; ?><br>
                    ❌ Anulados: <?php echo $resumen['anulados'] ?? 0; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Detalle de giros -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-list me-2"></i>Detalle de Giros
            <span class="badge bg-light text-dark ms-2"><?php echo count($giros); ?> registros</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="tablaGiros">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Fecha</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Moneda</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Comisión</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; $total_monto = 0; $total_comision = 0; foreach ($giros as $g): 
                        $total_monto += $g['monto'];
                        $total_comision += $g['comision'];
                    ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $g['codigo']; ?></strong></td>
                        <td>
                            <small>
                                <?php echo date('d/m/Y', strtotime($g['fecha_registro'])); ?><br>
                                <span class="text-muted"><?php echo date('H:i', strtotime($g['fecha_registro'])); ?></span>
                            </small>
                        </td>
                        <td>
                            <small>
                                <strong><?php echo $g['remitente_nombre'] . ' ' . $g['remitente_apellido']; ?></strong><br>
                                <span class="text-muted"><?php echo $g['remitente_dni']; ?></span>
                            </small>
                        </td>
                        <td>
                            <small>
                                <strong><?php echo $g['destinatario_nombre'] . ' ' . $g['destinatario_apellido']; ?></strong><br>
                                <span class="text-muted"><?php echo $g['destinatario_dni']; ?></span>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $g['moneda']; ?></span>
                        </td>
                        <td class="text-end">
                            <strong class="text-success">
                                S/ <?php echo number_format($g['monto'], 2); ?>
                            </strong>
                        </td>
                        <td class="text-end">
                            <span class="text-warning">
                                S/ <?php echo number_format($g['comision'], 2); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $badge = 'secondary';
                                $icon = 'fa-circle';
                                if ($g['estado'] == 'REGISTRADO') { $badge = 'warning'; $icon = 'fa-clock'; }
                                if ($g['estado'] == 'PAGADO') { $badge = 'success'; $icon = 'fa-check-circle'; }
                                if ($g['estado'] == 'ANULADO') { $badge = 'danger'; $icon = 'fa-times-circle'; }
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>">
                                <i class="fas <?php echo $icon; ?> me-1"></i>
                                <?php echo $g['estado']; ?>
                            </span>
                        </td>
                        <td><small><?php echo $g['registrado_por']; ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($giros)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-money-bill fa-4x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No hay giros en el período seleccionado</h5>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($giros)): ?>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="6" class="text-end">TOTALES:</th>
                        <th class="text-end">S/ <?php echo number_format($total_monto, 2); ?></th>
                        <th class="text-end">S/ <?php echo number_format($total_comision, 2); ?></th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Resumen de comisiones -->
<div class="row mt-4">
    <div class="col-md-6 mx-auto">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="text-center mb-3">
                    <i class="fas fa-calculator me-2"></i>Resumen Financiero
                </h5>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td><strong>Total Monto Girado:</strong></td>
                        <td class="text-end">S/ <?php echo number_format($total_monto, 2); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Total Comisiones:</strong></td>
                        <td class="text-end text-warning">S/ <?php echo number_format($total_comision, 2); ?></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>INGRESO TOTAL:</strong></td>
                        <td class="text-end">
                            <h5 class="text-success mb-0">
                                S/ <?php echo number_format($total_monto + $total_comision, 2); ?>
                            </h5>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>