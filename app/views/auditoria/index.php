<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-shield-alt text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registro completo de acciones del sistema</p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>auditoria/exportar?fecha_inicio=<?php echo $filtros['fecha_inicio']; ?>&fecha_fin=<?php echo $filtros['fecha_fin']; ?>" 
           class="btn btn-success">
            <i class="fas fa-file-excel me-1"></i>Exportar
        </a>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalLimpiar">
            <i class="fas fa-broom me-1"></i>Limpiar
        </button>
        <a href="<?php echo BASE_URL; ?>auditoria/sesiones" class="btn btn-info text-white">
            <i class="fas fa-users me-1"></i>Sesiones
        </a>
    </div>
</div>

<!-- ESTADÍSTICAS -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Acciones</small>
                        <h4 class="mb-0"><?php echo number_format($estadisticas['total'] ?? 0); ?></h4>
                    </div>
                    <i class="fas fa-list fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Inserciones</small>
                        <h4 class="mb-0 text-success"><?php echo number_format($estadisticas['inserts'] ?? 0); ?></h4>
                    </div>
                    <i class="fas fa-plus-circle fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Actualizaciones</small>
                        <h4 class="mb-0 text-warning"><?php echo number_format($estadisticas['updates'] ?? 0); ?></h4>
                    </div>
                    <i class="fas fa-edit fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Usuarios Activos</small>
                        <h4 class="mb-0 text-info"><?php echo number_format($estadisticas['usuarios_unicos'] ?? 0); ?></h4>
                    </div>
                    <i class="fas fa-users fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GRÁFICOS -->
<div class="row g-2 mb-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-line me-2"></i>Actividad Últimos 30 Días
            </div>
            <div class="card-body">
                <canvas id="chartActividad" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-pie me-2"></i>Por Módulo
            </div>
            <div class="card-body">
                <canvas id="chartModulos" height="180"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- FILTROS -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="Descripción, usuario o URL..."
                       value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Usuario</label>
                <select name="usuario_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios as $u): ?>
                        <option value="<?php echo $u['id']; ?>" 
                                <?php echo ($filtros['usuario_id'] == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="INSERT" <?php echo $filtros['accion'] == 'INSERT' ? 'selected' : ''; ?>>Insertar</option>
                    <option value="UPDATE" <?php echo $filtros['accion'] == 'UPDATE' ? 'selected' : ''; ?>>Actualizar</option>
                    <option value="DELETE" <?php echo $filtros['accion'] == 'DELETE' ? 'selected' : ''; ?>>Eliminar</option>
                    <option value="LOGIN" <?php echo $filtros['accion'] == 'LOGIN' ? 'selected' : ''; ?>>Login</option>
                    <option value="LOGOUT" <?php echo $filtros['accion'] == 'LOGOUT' ? 'selected' : ''; ?>>Logout</option>
                    <option value="EXPORT" <?php echo $filtros['accion'] == 'EXPORT' ? 'selected' : ''; ?>>Exportar</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_inicio']; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                       value="<?php echo $filtros['fecha_fin']; ?>">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TABLA -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Fecha/Hora</th>
                <th>Usuario</th>
                <th class="text-center">Acción</th>
                <th>Módulo</th>
                <th>Descripción</th>
                <th class="text-center">IP</th>
                <th class="text-center">Detalle</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($registros)): ?>
                <?php foreach ($registros as $r): ?>
                <tr>
                    <td data-label="Fecha/Hora">
                        <small>
                            <?php echo date('d/m/Y', strtotime($r['fecha_hora'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i:s', strtotime($r['fecha_hora'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Usuario">
                        <?php if (!empty($r['usuario_nombre'])): ?>
                            <i class="fas fa-user-circle text-primary me-1"></i>
                            <small><?php echo htmlspecialchars($r['usuario_nombre']); ?></small>
                        <?php else: ?>
                            <span class="text-muted">Sistema</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Acción" class="text-center">
                        <?php 
                            $colores = [
                                'INSERT' => 'success',
                                'UPDATE' => 'warning',
                                'DELETE' => 'danger',
                                'LOGIN' => 'info',
                                'LOGOUT' => 'secondary',
                                'EXPORT' => 'primary'
                            ];
                            $color = $colores[$r['accion']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?php echo $color; ?>">
                            <?php echo $r['accion']; ?>
                        </span>
                    </td>
                    <td data-label="Módulo">
                        <small class="text-muted"><?php echo htmlspecialchars($r['modulo'] ?? '-'); ?></small>
                    </td>
                    <td data-label="Descripción">
                        <small><?php echo htmlspecialchars(substr($r['descripcion'] ?? '', 0, 60)); ?><?php echo strlen($r['descripcion'] ?? '') > 60 ? '...' : ''; ?></small>
                    </td>
                    <td data-label="IP" class="text-center">
                        <small class="text-muted"><?php echo $r['ip_origen']; ?></small>
                    </td>
                    <td data-label="Detalle" class="text-center">
                        <a href="<?php echo BASE_URL; ?>auditoria/show/<?php echo $r['id']; ?>" 
                           class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-shield-alt fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay registros de auditoría</h5>
                        <p class="text-muted small">No se encontraron acciones en el período seleccionado</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($paginator)): ?>
    <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
<?php endif; ?>

<!-- MODAL LIMPIAR -->
<div class="modal fade" id="modalLimpiar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>auditoria/limpiar">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title"><i class="fas fa-broom me-2"></i>Limpiar Registros Antiguos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Se eliminarán los registros de auditoría con más de:</p>
                    <select name="dias" class="form-select">
                        <option value="30">30 días</option>
                        <option value="60">60 días</option>
                        <option value="90" selected>90 días</option>
                        <option value="180">180 días</option>
                        <option value="365">1 año</option>
                    </select>
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Esta acción no se puede deshacer.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-broom me-1"></i>Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de actividad
    const actividadDia = <?php echo json_encode($actividad_dia); ?>;
    const actividadModulo = <?php echo json_encode($actividad_modulo); ?>;
    
    if (actividadDia.length > 0) {
        new Chart(document.getElementById('chartActividad'), {
            type: 'line',
            data: {
                labels: actividadDia.map(d => {
                    const fecha = new Date(d.fecha);
                    return fecha.getDate() + '/' + (fecha.getMonth() + 1);
                }),
                datasets: [{
                    label: 'Acciones',
                    data: actividadDia.map(d => d.total),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
    
    if (actividadModulo.length > 0) {
        const colores = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14'];
        new Chart(document.getElementById('chartModulos'), {
            type: 'doughnut',
            data: {
                labels: actividadModulo.map(d => d.modulo),
                datasets: [{
                    data: actividadModulo.map(d => d.total),
                    backgroundColor: colores.slice(0, actividadModulo.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 } } }
                }
            }
        });
    }
});
</script>