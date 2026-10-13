<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-box me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/encomiendas?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
           class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf me-1"></i>Exportar PDF
        </a>
        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/encomiendas?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
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
                    <option value="EN_BODEGA" <?php echo ($filtros['estado'] == 'EN_BODEGA') ? 'selected' : ''; ?>>En Bodega</option>
                    <option value="EN_TRANSITO" <?php echo ($filtros['estado'] == 'EN_TRANSITO') ? 'selected' : ''; ?>>En Tránsito</option>
                    <option value="ENTREGADO" <?php echo ($filtros['estado'] == 'ENTREGADO') ? 'selected' : ''; ?>>Entregado</option>
                    <option value="DEVUELTO" <?php echo ($filtros['estado'] == 'DEVUELTO') ? 'selected' : ''; ?>>Devuelto</option>
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
                        <h6 class="mb-0">Total Encomiendas</h6>
                        <h3 class="mb-0"><?php echo $resumen['total_encomiendas'] ?? 0; ?></h3>
                    </div>
                    <i class="fas fa-boxes fa-2x opacity-50"></i>
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
                        <h3 class="mb-0">S/ <?php echo number_format($resumen['total_recaudado'] ?? 0, 2); ?></h3>
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
                        <h6 class="mb-0">Peso Total</h6>
                        <h3 class="mb-0"><?php echo number_format($resumen['total_peso'] ?? 0, 2); ?> kg</h3>
                    </div>
                    <i class="fas fa-weight-hanging fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <h6 class="mb-1">Por Estado</h6>
                <small>
                    📝 Registradas: <?php echo $resumen['registradas'] ?? 0; ?><br>
                    📦 En bodega: <?php echo $resumen['en_bodega'] ?? 0; ?><br>
                    🚚 En tránsito: <?php echo $resumen['en_transito'] ?? 0; ?><br>
                    ✅ Entregadas: <?php echo $resumen['entregadas'] ?? 0; ?><br>
                    ↩️ Devueltas: <?php echo $resumen['devueltas'] ?? 0; ?>
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Detalle de encomiendas -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-list me-2"></i>Detalle de Encomiendas
            <span class="badge bg-light text-dark ms-2"><?php echo count($encomiendas); ?> registros</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0" id="tablaEncomiendas">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Guía</th>
                        <th>Fecha</th>
                        <th>Remitente</th>
                        <th>Destinatario</th>
                        <th>Ruta</th>
                        <th>Peso</th>
                        <th>Estado</th>
                        <th class="text-end">Costo</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach ($encomiendas as $e): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo $e['guia']; ?></strong></td>
                        <td>
                            <small>
                                <?php echo date('d/m/Y', strtotime($e['fecha_registro'])); ?><br>
                                <span class="text-muted"><?php echo date('H:i', strtotime($e['fecha_registro'])); ?></span>
                            </small>
                        </td>
                        <td>
                            <small>
                                <strong><?php echo $e['remitente_nombre'] . ' ' . $e['remitente_apellido']; ?></strong><br>
                                <span class="text-muted"><?php echo $e['remitente_dni']; ?></span>
                            </small>
                        </td>
                        <td>
                            <small>
                                <strong><?php echo $e['destinatario_nombre'] . ' ' . $e['destinatario_apellido']; ?></strong><br>
                                <span class="text-muted"><?php echo $e['destinatario_dni']; ?></span>
                            </small>
                        </td>
                        <td>
                            <small>
                                <?php echo $e['origen']; ?> → <?php echo $e['destino']; ?><br>
                                <span class="text-muted">🚌 <?php echo $e['placa']; ?></span>
                            </small>
                        </td>
                        <td><?php echo $e['peso_kg']; ?> kg</td>
                        <td>
                            <?php 
                                $badge = 'secondary';
                                $icon = 'fa-circle';
                                if ($e['estado'] == 'REGISTRADO') { $badge = 'info'; $icon = 'fa-file-alt'; }
                                if ($e['estado'] == 'EN_BODEGA') { $badge = 'warning'; $icon = 'fa-warehouse'; }
                                if ($e['estado'] == 'EN_TRANSITO') { $badge = 'primary'; $icon = 'fa-truck'; }
                                if ($e['estado'] == 'ENTREGADO') { $badge = 'success'; $icon = 'fa-check-circle'; }
                                if ($e['estado'] == 'DEVUELTO') { $badge = 'danger'; $icon = 'fa-undo'; }
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>">
                                <i class="fas <?php echo $icon; ?> me-1"></i>
                                <?php echo $e['estado']; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <strong class="text-success">
                                S/ <?php echo number_format($e['costo_envio'], 2); ?>
                            </strong>
                        </td>
                        <td><small><?php echo $e['registrado_por']; ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($encomiendas)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="fas fa-box-open fa-4x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No hay encomiendas en el período seleccionado</h5>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($encomiendas)): ?>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="8" class="text-end">TOTAL RECAUDADO:</th>
                        <th class="text-end">S/ <?php echo number_format($resumen['total_recaudado'] ?? 0, 2); ?></th>
                        <th></th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>