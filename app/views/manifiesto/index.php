<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $titulo; ?></h1>
</div>

<!-- Filtro por fecha -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha de Viaje</label>
                <input type="date" name="fecha" class="form-control" value="<?php echo $fecha; ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de viajes -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>Ruta</th>
                <th>Fecha / Hora</th>
                <th>Bus</th>
                <th>Pasajeros</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($viajes as $v): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($v['origen'] . ' → ' . $v['destino']); ?></strong></td>
                <td><?php echo date('d/m/Y H:i', strtotime($v['fecha_salida'])); ?></td>
                <td><?php echo $v['placa'] . ' (' . $v['tipo_servicio'] . ')'; ?></td>
                <td>
                    <span class="badge bg-success"><?php echo $v['pasajeros_abordo']; ?></span> 
                    / <?php echo $v['capacidad_total']; ?>
                </td>
                <td>
                    <a href="<?php echo BASE_URL; ?>manifiesto/show/<?php echo $v['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                        <i class="fas fa-file-alt me-1"></i>Generar Manifiesto
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($viajes)): ?>
            <tr><td colspan="5" class="text-center">No hay viajes programados para esta fecha</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>