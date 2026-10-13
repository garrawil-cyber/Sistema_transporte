<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-tools text-warning me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Modifica los datos del mantenimiento #<?php echo $mantenimiento['id']; ?></p>
    </div>
    <a href="<?php echo BASE_URL; ?>mantenimiento" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<form method="POST" action="<?php echo BASE_URL; ?>mantenimiento/update/<?php echo $mantenimiento['id']; ?>" id="formMantenimiento">
    <div class="row g-3">
        <!-- Info principal -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-bus me-2"></i>Bus y Tipo de Mantenimiento
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Bus *</label>
                        <select name="bus_id" id="bus_id" class="form-select form-select-lg" required>
                            <?php foreach ($buses as $b): ?>
                                <option value="<?php echo $b['id']; ?>" 
                                        <?php echo ($mantenimiento['bus_id'] == $b['id']) ? 'selected' : ''; ?>>
                                    <?php echo $b['placa'] . ' - ' . $b['marca'] . ' ' . $b['modelo']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tipo *</label>
                        <select name="tipo" class="form-select form-select-lg" required>
                            <option value="PREVENTIVO" <?php echo $mantenimiento['tipo'] == 'PREVENTIVO' ? 'selected' : ''; ?>>🔧 Preventivo</option>
                            <option value="CORRECTIVO" <?php echo $mantenimiento['tipo'] == 'CORRECTIVO' ? 'selected' : ''; ?>>🛠️ Correctivo</option>
                            <option value="REVISION" <?php echo $mantenimiento['tipo'] == 'REVISION' ? 'selected' : ''; ?>>🔍 Revisión</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Estado *</label>
                        <select name="estado" class="form-select form-select-lg" required>
                            <option value="PROGRAMADO" <?php echo $mantenimiento['estado'] == 'PROGRAMADO' ? 'selected' : ''; ?>>📅 Programado</option>
                            <option value="EN_PROCESO" <?php echo $mantenimiento['estado'] == 'EN_PROCESO' ? 'selected' : ''; ?>>⚙️ En Proceso</option>
                            <option value="COMPLETADO" <?php echo $mantenimiento['estado'] == 'COMPLETADO' ? 'selected' : ''; ?>>✅ Completado</option>
                            <option value="CANCELADO" <?php echo $mantenimiento['estado'] == 'CANCELADO' ? 'selected' : ''; ?>>❌ Cancelado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fechas y km -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-calendar me-2"></i>Fechas y Kilometraje
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fecha Programada</label>
                            <input type="date" name="fecha_programada" class="form-control"
                                   value="<?php echo $mantenimiento['fecha_programada']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Fecha Realizada</label>
                            <input type="date" name="fecha_realizada" class="form-control"
                                   value="<?php echo $mantenimiento['fecha_realizada']; ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small">Kilometraje</label>
                            <input type="number" name="kilometraje" class="form-control"
                                   value="<?php echo $mantenimiento['kilometraje']; ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Próximo Mtto. (Fecha)</label>
                            <input type="date" name="proximo_mantenimiento_fecha" class="form-control"
                                   value="<?php echo $mantenimiento['proximo_mantenimiento_fecha']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Próximo Mtto. (Km)</label>
                            <input type="number" name="proximo_mantenimiento_km" class="form-control"
                                   value="<?php echo $mantenimiento['proximo_mantenimiento_km']; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-clipboard-list me-2"></i>Descripción del Trabajo
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Descripción *</label>
                        <textarea name="descripcion" class="form-control" rows="3" required><?php echo htmlspecialchars($mantenimiento['descripcion']); ?></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Taller Responsable</label>
                        <input type="text" name="taller_responsable" class="form-control"
                               value="<?php echo htmlspecialchars($mantenimiento['taller_responsable']); ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Costos -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-money-bill me-2"></i>Costos
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">S/</span>
                                <input type="number" step="0.01" name="costo_mano_obra" id="costo_mano_obra" 
                                       class="form-control" value="<?php echo $mantenimiento['costo_mano_obra']; ?>" 
                                       oninput="calcularTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Repuestos</label>
                            <div class="input-group">
                                <span class="input-group-text bg-info text-white">S/</span>
                                <input type="number" step="0.01" name="costo_repuestos" id="costo_repuestos" 
                                       class="form-control" value="<?php echo $mantenimiento['costo_repuestos']; ?>" 
                                       oninput="calcularTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Costo Total</label>
                            <div class="alert alert-success mb-0 py-2 text-center">
                                <h4 class="mb-0">S/ <span id="costo_total"><?php echo number_format($mantenimiento['costo_total'], 2); ?></span></h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-bold small">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"><?php echo htmlspecialchars($mantenimiento['observaciones']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>mantenimiento" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-1"></i>Actualizar Mantenimiento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function calcularTotal() {
    const mano = parseFloat(document.getElementById('costo_mano_obra').value) || 0;
    const rep = parseFloat(document.getElementById('costo_repuestos').value) || 0;
    document.getElementById('costo_total').textContent = (mano + rep).toFixed(2);
}

document.getElementById('formMantenimiento').addEventListener('submit', function(e) {
    e.preventDefault();
    const formRef = this;
    Swal.fire({
        title: '¿Actualizar mantenimiento?',
        text: 'Se guardarán los cambios realizados.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, actualizar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Actualizando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            formRef.submit();
        }
    });
});
</script>