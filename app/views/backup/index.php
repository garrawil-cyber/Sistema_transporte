<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-database text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Respaldo y restauración de la base de datos</p>
    </div>
    <div>
        <form method="POST" action="<?php echo BASE_URL; ?>backup/crear" class="d-inline">
            <input type="hidden" name="tipo" value="manual">
<button type="button" class="btn btn-primary" onclick="return confirmarCrearBackup()">
    <i class="fas fa-plus me-1"></i>Crear Backup
</button>
        </form>
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalLimpiar">
            <i class="fas fa-broom me-1"></i>Limpiar
        </button>
    </div>
</div>

<!-- ============================================================
     ADVERTENCIA
     ============================================================ -->
<div class="alert alert-info border-0 shadow-sm">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Recomendación:</strong>
    Descarga los backups regularmente y guárdalos fuera del servidor de producción.
</div>

<!-- ============================================================
     ESTADÍSTICAS
     ============================================================ -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Backups</small>
                        <h4 class="mb-0"><?php echo $estadisticas['total'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-database fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Manuales</small>
                        <h4 class="mb-0 text-success"><?php echo $estadisticas['manuales'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-hand-pointer fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Automáticos</small>
                        <h4 class="mb-0 text-info"><?php echo $estadisticas['automaticos'] ?? 0; ?></h4>
                    </div>
                    <i class="fas fa-robot fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Espacio Total</small>
                        <h4 class="mb-0 text-warning">
                            <?php 
                                $bytes = $estadisticas['tamano_total'] ?? 0;
                                if ($bytes < 1024) echo $bytes . ' B';
                                elseif ($bytes < 1048576) echo round($bytes / 1024, 2) . ' KB';
                                elseif ($bytes < 1073741824) echo round($bytes / 1048576, 2) . ' MB';
                                else echo round($bytes / 1073741824, 2) . ' GB';
                            ?>
                        </h4>
                    </div>
                    <i class="fas fa-hdd fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     FILTROS
     ============================================================ -->
<!-- Filtros -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" 
                       placeholder="Nombre del archivo..."
                       value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="manual" <?php echo ($filtros['tipo'] ?? '') == 'manual' ? 'selected' : ''; ?>>Manual</option>
                    <option value="automatico" <?php echo ($filtros['tipo'] ?? '') == 'automatico' ? 'selected' : ''; ?>>Automático</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_inicio'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" 
                       value="<?php echo htmlspecialchars($filtros['fecha_fin'] ?? ''); ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>backup" class="btn btn-sm btn-secondary">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     TABLA DE BACKUPS
     ============================================================ -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Archivo</th>
                <th class="text-center">Tipo</th>
                <th class="text-end">Tamaño</th>
                <th>Fecha Creación</th>
                <th>Usuario</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($backups)): ?>
                <?php foreach ($backups as $b): ?>
                <tr>
                    <td data-label="Archivo">
                        <i class="fas fa-file-code text-primary me-1"></i>
                        <strong><?php echo htmlspecialchars($b['nombre_archivo']); ?></strong>
                    </td>
                    <td data-label="Tipo" class="text-center">
                        <span class="badge bg-<?php echo $b['tipo'] == 'manual' ? 'primary' : 'info'; ?>">
                            <i class="fas fa-<?php echo $b['tipo'] == 'manual' ? 'hand-pointer' : 'robot'; ?> me-1"></i>
                            <?php echo ucfirst($b['tipo']); ?>
                        </span>
                    </td>
                    <td data-label="Tamaño" class="text-end">
                        <?php 
                            $bytes = $b['tamano_bytes'];
                            if ($bytes < 1024) echo $bytes . ' B';
                            elseif ($bytes < 1048576) echo round($bytes / 1024, 2) . ' KB';
                            elseif ($bytes < 1073741824) echo round($bytes / 1048576, 2) . ' MB';
                            else echo round($bytes / 1073741824, 2) . ' GB';
                        ?>
                    </td>
                    <td data-label="Fecha">
                        <small>
                            <?php echo date('d/m/Y', strtotime($b['fecha_creacion'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i:s', strtotime($b['fecha_creacion'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Usuario">
                        <small><?php echo htmlspecialchars($b['usuario_nombre'] ?? 'Sistema'); ?></small>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <span class="badge bg-<?php echo $b['estado'] == 'COMPLETADO' ? 'success' : 'danger'; ?>">
                            <?php echo $b['estado']; ?>
                        </span>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo BASE_URL; ?>backup/descargar/<?php echo $b['id']; ?>" 
                               class="btn btn-outline-primary" title="Descargar">
                                <i class="fas fa-download"></i>
                            </a>
                            <button type="button" class="btn btn-outline-warning" 
                                    onclick="confirmarRestaurar(<?php echo $b['id']; ?>, '<?php echo htmlspecialchars($b['nombre_archivo']); ?>')"
                                    title="Restaurar">
                                <i class="fas fa-undo"></i>
                            </button>
<a href="#" 
   class="btn btn-outline-danger" title="Eliminar"
   onclick="return confirmarEliminar('<?php echo BASE_URL; ?>backup/eliminar/<?php echo $b['id']; ?>', '<?php echo htmlspecialchars($b['nombre_archivo']); ?>')">
    <i class="fas fa-trash-alt"></i>
</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-database fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay backups creados</h5>
                        <p class="text-muted small mb-3">Crea tu primer backup para proteger la información</p>
                        <form method="POST" action="<?php echo BASE_URL; ?>backup/crear" class="d-inline">
                            <input type="hidden" name="tipo" value="manual">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Crear primer backup
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ============================================================
     MODAL: LIMPIAR BACKUPS ANTIGUOS
     ============================================================ -->
<div class="modal fade" id="modalLimpiar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>backup/limpiar">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-broom me-2"></i>Limpiar Backups Antiguos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Se eliminarán los <strong>backups automáticos</strong> con más días de antigüedad que:</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Días de antigüedad</label>
                        <select name="dias" class="form-select">
                            <option value="7">7 días</option>
                            <option value="15">15 días</option>
                            <option value="30" selected>30 días</option>
                            <option value="60">60 días</option>
                            <option value="90">90 días</option>
                        </select>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Los backups <strong>manuales NO se eliminarán</strong>.
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

<!-- ============================================================
     MODAL: RESTAURAR BACKUP
     ============================================================ -->
<div class="modal fade" id="modalRestaurar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="" id="formRestaurar">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Restaurar Base de Datos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>¡ADVERTENCIA!</h6>
                        <p class="mb-0">
                            Restaurar este backup <strong>SOBRESCRIBIRÁ TODA LA BASE DE DATOS ACTUAL</strong>.
                            Perderás cualquier cambio hecho después del backup.
                        </p>
                    </div>
                    <p>Archivo a restaurar:</p>
                    <p class="text-primary fw-bold" id="archivoRestaurar"></p>
                    <hr>
                    <p><strong>Se creará un backup de seguridad automáticamente antes de restaurar.</strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Escribe <code>RESTAURAR</code> para confirmar:</label>
                        <input type="text" name="confirmacion" class="form-control" 
                               placeholder="Escribe RESTAURAR" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo me-1"></i>Restaurar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (isset($paginator)): ?>
    <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
<?php endif; ?>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.querySelector('form[action*=\'backup/crear\']').submit();" title="Nuevo Backup">
    <i class="fas fa-database"></i>
</a>

<script>
function confirmarRestaurar(id, archivo) {
    document.getElementById('formRestaurar').action = '<?php echo BASE_URL; ?>backup/restaurar/' + id;
    document.getElementById('archivoRestaurar').textContent = archivo;
    
    const modal = new bootstrap.Modal(document.getElementById('modalRestaurar'));
    modal.show();
}
</script>
<script>
function confirmarCrearBackup() {
    Swal.fire({
        title: '¿Crear backup?',
        text: 'Se generará un respaldo completo de la base de datos.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-database me-1"></i>Sí, crear',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Creando backup...',
                html: 'Esto puede tardar unos segundos',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            document.querySelector('form[action*="backup/crear"]').submit();
        }
    });
    return false;
}
</script>