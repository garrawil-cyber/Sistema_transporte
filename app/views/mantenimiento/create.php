<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-tools text-danger me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra un nuevo mantenimiento para la flota</p>
    </div>
    <a href="<?php echo BASE_URL; ?>mantenimiento" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<form method="POST" action="<?php echo BASE_URL; ?>mantenimiento/store" id="formMantenimiento">
    <div class="row g-3">
        <!-- Info principal -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-bus me-2"></i>Bus y Tipo de Mantenimiento
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-bus me-1"></i>Bus *
                        </label>
                        <select name="bus_id" id="bus_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione bus --</option>
                            <?php foreach ($buses as $b): ?>
                                <option value="<?php echo $b['id']; ?>">
                                    <?php echo $b['placa'] . ' - ' . $b['marca'] . ' ' . $b['modelo']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-wrench me-1"></i>Tipo *
                        </label>
                        <select name="tipo" id="tipo" class="form-select form-select-lg" required>
                            <option value="PREVENTIVO">🔧 Preventivo</option>
                            <option value="CORRECTIVO">🛠️ Correctivo</option>
                            <option value="REVISION">🔍 Revisión</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-info-circle me-1"></i>Estado *
                        </label>
                        <select name="estado" class="form-select form-select-lg" required>
                            <option value="PROGRAMADO">📅 Programado</option>
                            <option value="EN_PROCESO">⚙️ En Proceso</option>
                            <option value="COMPLETADO">✅ Completado</option>
                            <option value="CANCELADO">❌ Cancelado</option>
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
                            <label class="form-label fw-bold small">
                                <i class="fas fa-calendar-day me-1"></i>Fecha Programada
                            </label>
                            <input type="date" name="fecha_programada" class="form-control"
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-tachometer-alt me-1"></i>Kilometraje
                            </label>
                            <input type="number" name="kilometraje" class="form-control" 
                                   placeholder="Ej: 125000">
                        </div>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-calendar-plus me-1"></i>Próximo Mtto. (Fecha)
                            </label>
                            <input type="date" name="proximo_mantenimiento_fecha" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-tachometer-alt me-1"></i>Próximo Mtto. (Km)
                            </label>
                            <input type="number" name="proximo_mantenimiento_km" class="form-control"
                                   placeholder="Ej: 130000">
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
                        <textarea name="descripcion" class="form-control" rows="3" required
                                  placeholder="Describa el trabajo a realizar..."></textarea>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-wrench me-1"></i>Taller Responsable
                        </label>
                        <input type="text" name="taller_responsable" class="form-control"
                               placeholder="Nombre del taller">
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
                            <label class="form-label fw-bold small">
                                <i class="fas fa-money-bill me-1"></i>Mano de Obra
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white">S/</span>
                                <input type="number" step="0.01" name="costo_mano_obra" id="costo_mano_obra" 
                                       class="form-control" value="0" oninput="calcularTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-cog me-1"></i>Repuestos
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-info text-white">S/</span>
                                <input type="number" step="0.01" name="costo_repuestos" id="costo_repuestos" 
                                       class="form-control" value="0" oninput="calcularTotal()">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Costo Total</label>
                            <div class="alert alert-success mb-0 py-2 text-center">
                                <h4 class="mb-0">S/ <span id="costo_total">0.00</span></h4>
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
                    <label class="form-label fw-bold small">
                        <i class="fas fa-comment me-1"></i>Observaciones
                    </label>
                    <textarea name="observaciones" class="form-control" rows="2"
                              placeholder="Observaciones adicionales..."></textarea>
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
                            <i class="fas fa-save me-1"></i>Guardar Mantenimiento
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formMantenimiento').requestSubmit();" title="Guardar">
    <i class="fas fa-save"></i>
</a>

<script>
function calcularTotal() {
    const mano = parseFloat(document.getElementById('costo_mano_obra').value) || 0;
    const rep = parseFloat(document.getElementById('costo_repuestos').value) || 0;
    document.getElementById('costo_total').textContent = (mano + rep).toFixed(2);
}

document.getElementById('formMantenimiento').addEventListener('submit', function(e) {
    e.preventDefault();
    const formRef = this;
    
    const busSelect = document.getElementById('bus_id');
    const tipo = document.getElementById('tipo');
    const costo = document.getElementById('costo_total').textContent;
    
    if (!busSelect.value) {
        mostrarToast('Seleccione un bus', 'warning');
        return false;
    }
    
    Swal.fire({
        title: '¿Guardar mantenimiento?',
        html: `
            <div class="text-start">
                <p><strong>Bus:</strong> ${busSelect.options[busSelect.selectedIndex].text}</p>
                <p><strong>Tipo:</strong> ${tipo.options[tipo.selectedIndex].text}</p>
                <p><strong>Costo Total:</strong> <span class="text-success">S/ ${costo}</span></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, guardar',
        confirmButtonColor: '#198754'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            formRef.submit();
        }
    });
});
</script>

<style>
    #formMantenimiento .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #formMantenimiento .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    }
</style>