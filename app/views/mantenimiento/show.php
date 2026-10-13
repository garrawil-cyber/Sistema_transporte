<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tools text-danger me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>mantenimiento/edit/<?php echo $mantenimiento['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <button onclick="window.print()" class="btn btn-secondary">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
        <a href="<?php echo BASE_URL; ?>mantenimiento" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-info-circle me-2"></i>Información General
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> #<?php echo $mantenimiento['id']; ?></p>
                <p><strong>Bus:</strong> <?php echo $mantenimiento['placa']; ?></p>
                <p><strong>Marca/Modelo:</strong> <?php echo $mantenimiento['marca'] . ' ' . $mantenimiento['modelo']; ?></p>
                <p><strong>Tipo:</strong> 
                    <span class="badge bg-<?php echo $mantenimiento['tipo'] == 'PREVENTIVO' ? 'info' : ($mantenimiento['tipo'] == 'CORRECTIVO' ? 'warning' : 'secondary'); ?>">
                        <?php echo $mantenimiento['tipo']; ?>
                    </span>
                </p>
                <p><strong>Estado:</strong> 
                    <span class="badge bg-<?php 
                        echo $mantenimiento['estado'] == 'PROGRAMADO' ? 'primary' : 
                            ($mantenimiento['estado'] == 'EN_PROCESO' ? 'warning' : 
                            ($mantenimiento['estado'] == 'COMPLETADO' ? 'success' : 'secondary')); 
                    ?>">
                        <?php echo $mantenimiento['estado']; ?>
                    </span>
                </p>
                <hr>
                <p><strong>Fecha Programada:</strong><br>
                    <?php echo $mantenimiento['fecha_programada'] ? date('d/m/Y', strtotime($mantenimiento['fecha_programada'])) : '-'; ?>
                </p>
                <p><strong>Fecha Realizada:</strong><br>
                    <?php echo $mantenimiento['fecha_realizada'] ? date('d/m/Y', strtotime($mantenimiento['fecha_realizada'])) : 'Pendiente'; ?>
                </p>
                <p><strong>Kilometraje:</strong> <?php echo $mantenimiento['kilometraje'] ? number_format($mantenimiento['kilometraje']) . ' km' : '-'; ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-clipboard-list me-2"></i>Descripción del Trabajo
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($mantenimiento['descripcion'])); ?></p>
                <?php if (!empty($mantenimiento['taller_responsable'])): ?>
                    <hr>
                    <p class="mb-0"><strong><i class="fas fa-wrench me-1"></i>Taller:</strong> <?php echo htmlspecialchars($mantenimiento['taller_responsable']); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <i class="fas fa-money-bill-wave me-2"></i>Costos
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <small class="text-muted d-block">Mano de Obra</small>
                        <h4 class="text-primary">S/ <?php echo number_format($mantenimiento['costo_mano_obra'], 2); ?></h4>
                    </div>
                    <div class="col-md-4 text-center border-end">
                        <small class="text-muted d-block">Repuestos</small>
                        <h4 class="text-info">S/ <?php echo number_format($mantenimiento['costo_repuestos'], 2); ?></h4>
                    </div>
                    <div class="col-md-4 text-center">
                        <small class="text-muted d-block">TOTAL</small>
                        <h4 class="text-success">S/ <?php echo number_format($mantenimiento['costo_total'], 2); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($mantenimiento['proximo_mantenimiento_fecha']) || !empty($mantenimiento['proximo_mantenimiento_km'])): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-calendar-plus me-2"></i>Próximo Mantenimiento
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Fecha:</small>
                        <p class="mb-0"><strong><?php echo $mantenimiento['proximo_mantenimiento_fecha'] ? date('d/m/Y', strtotime($mantenimiento['proximo_mantenimiento_fecha'])) : '-'; ?></strong></p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Kilometraje:</small>
                        <p class="mb-0"><strong><?php echo $mantenimiento['proximo_mantenimiento_km'] ? number_format($mantenimiento['proximo_mantenimiento_km']) . ' km' : '-'; ?></strong></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($mantenimiento['observaciones'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-comment me-2"></i>Observaciones
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($mantenimiento['observaciones'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>