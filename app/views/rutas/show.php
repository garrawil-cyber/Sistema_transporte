<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-route text-primary me-2"></i><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>rutas/edit/<?php echo $ruta['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="<?php echo BASE_URL; ?>rutas" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-5">
                        <i class="fas fa-map-marker-alt text-primary fa-2x mb-2"></i>
                        <h4 class="mb-0"><?php echo htmlspecialchars($ruta['origen']); ?></h4>
                        <small class="text-muted">Origen</small>
                    </div>
                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-arrow-right fa-2x text-success"></i>
                    </div>
                    <div class="col-md-5">
                        <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
                        <h4 class="mb-0"><?php echo htmlspecialchars($ruta['destino']); ?></h4>
                        <small class="text-muted">Destino</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Distancia</small>
                        <h5><?php echo number_format($ruta['distancia_km'], 2); ?> km</h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Duración</small>
                        <h5><?php echo $ruta['duracion_estimada']; ?></h5>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Precio Base</small>
                        <h5 class="text-success">S/ <?php echo number_format($ruta['precio_base'], 2); ?></h5>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <span class="badge <?php echo $ruta['activo'] ? 'bg-success' : 'bg-secondary'; ?> fs-6">
                        <?php echo $ruta['activo'] ? 'ACTIVA' : 'INACTIVA'; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <i class="fas fa-bus fa-3x text-info mb-2"></i>
                <h3><?php echo $estadisticas['total_viajes'] ?? 0; ?></h3>
                <small class="text-muted">Viajes Realizados</small>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-money-bill-wave fa-3x text-success mb-2"></i>
                <h3>S/ <?php echo number_format($estadisticas['total_recaudado'] ?? 0, 2); ?></h3>
                <small class="text-muted">Total Recaudado</small>
            </div>
        </div>
    </div>
</div>

<!-- Viajes que usan esta ruta -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-history me-2"></i>Últimos Viajes con esta Ruta
    </div>
    <div class="card-body p-0">
        <div class="table-responsive table-responsive-mobile">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha Salida</th>
                        <th>Bus</th>
                        <th class="text-center">Pasajeros</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($viajes)): ?>
                        <?php foreach ($viajes as $v): ?>
                        <tr>
                            <td data-label="Fecha">
                                <small><?php echo date('d/m/Y H:i', strtotime($v['fecha_salida'])); ?></small>
                            </td>
                            <td data-label="Bus">
                                <span class="badge bg-secondary"><?php echo $v['placa']; ?></span>
                            </td>
                            <td data-label="Pasajeros" class="text-center">
                                <span class="badge bg-info"><?php echo $v['pasajeros']; ?></span>
                            </td>
                            <td data-label="Estado" class="text-center">
                                <span class="badge bg-<?php 
                                    echo $v['estado'] == 'PROGRAMADO' ? 'primary' : 
                                        ($v['estado'] == 'EN_CURSO' ? 'warning' : 
                                        ($v['estado'] == 'COMPLETADO' ? 'success' : 'danger')); 
                                ?>">
                                    <?php echo $v['estado']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="fas fa-info-circle me-1"></i>Sin viajes registrados
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>