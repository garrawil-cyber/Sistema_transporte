<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-calendar-plus text-primary me-2"></i><?php echo $titulo ?? 'Nuevo Viaje'; ?>
        </h1>
        <p class="text-muted mb-0 small">Programa un nuevo viaje asignando ruta, bus y conductor</p>
    </div>
    <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<form method="POST" action="<?php echo BASE_URL; ?>viajes/store" id="formViaje">
    <div class="row g-3">
        <!-- Asignación -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-route me-2"></i>Ruta y Bus
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ruta *</label>
                        <select name="ruta_id" id="ruta_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione una ruta --</option>
                            <?php foreach ($rutas as $ruta): ?>
                                <option value="<?php echo $ruta['id']; ?>" 
                                        data-precio="<?php echo $ruta['precio_base']; ?>"
                                        data-origen="<?php echo htmlspecialchars($ruta['origen']); ?>"
                                        data-destino="<?php echo htmlspecialchars($ruta['destino']); ?>"
                                        data-duracion="<?php echo $ruta['duracion_estimada']; ?>"
                                        <?php echo ((isset($data['ruta_id']) && $data['ruta_id'] == $ruta['id']) ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($ruta['origen'] . ' → ' . $ruta['destino']); ?>
                                    (S/ <?php echo number_format($ruta['precio_base'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Bus *</label>
                        <select name="bus_id" id="bus_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione un bus --</option>
                            <?php foreach ($buses as $bus): ?>
                                <option value="<?php echo $bus['id']; ?>" 
                                        data-capacidad="<?php echo $bus['capacidad_total']; ?>"
                                        data-placa="<?php echo $bus['placa']; ?>"
                                        data-marca="<?php echo htmlspecialchars($bus['marca'] . ' ' . $bus['modelo']); ?>"
                                        data-tipo-servicio="<?php echo $bus['tipo_servicio']; ?>"
                                        data-pisos="<?php echo $bus['pisos'] ?? 1; ?>"
                                        <?php echo ((isset($data['bus_id']) && $data['bus_id'] == $bus['id']) ? 'selected' : ''); ?>>
                                    <?php echo $bus['placa']; ?> - <?php echo $bus['marca'] . ' ' . $bus['modelo']; ?>
                                    (<?php echo $bus['capacidad_total']; ?> asientos · <?php echo $bus['tipo_servicio']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">El tipo de servicio se define por el bus seleccionado</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conductor y precio -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-user-check me-2"></i>Conductor y Precio
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Conductor *</label>
                        <select name="conductor_id" id="conductor_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione un conductor --</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?php echo $conductor['id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($conductor['nombre_completo']); ?>"
                                        data-telefono="<?php echo htmlspecialchars($conductor['telefono'] ?? ''); ?>"
                                        <?php echo ((isset($data['conductor_id']) && $data['conductor_id'] == $conductor['id']) ? 'selected' : ''); ?>>
                                    <?php echo htmlspecialchars($conductor['nombre_completo']); ?>
                                    <?php if (!empty($conductor['telefono'])): ?>
                                        (📞 <?php echo $conductor['telefono']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($conductores)): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>No hay conductores disponibles. 
                                    <a href="<?php echo BASE_URL; ?>usuarios/create" class="alert-link">Crear uno</a>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">Precio Final (S/) *</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" name="precio_final" id="precio_final" 
                                   class="form-control text-center fw-bold" placeholder="0.00"
                                   value="<?php echo htmlspecialchars($data['precio_final'] ?? ''); ?>" required>
                        </div>
                        <small class="text-muted">Se carga automáticamente al seleccionar la ruta</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ NUEVO: Preview del Bus seleccionado -->
        <div class="col-12" id="card_preview_bus" style="display: none;">
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-2 text-center">
                            <i class="fas fa-bus fa-3x text-primary"></i>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Bus Seleccionado</small>
                            <h5 class="mb-0" id="preview_placa">---</h5>
                            <small class="text-muted" id="preview_marca">---</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <small class="text-muted d-block">Tipo de Servicio</small>
                            <div id="preview_tipo_servicio_wrap">
                                <span class="badge bg-secondary fs-5" id="preview_tipo_servicio">---</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <small class="text-muted d-block">Capacidad</small>
                            <h5 class="mb-0">
                                <i class="fas fa-chair me-1"></i>
                                <span id="preview_capacidad">---</span> asientos
                            </h5>
                            <small class="text-muted" id="preview_pisos">---</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fechas -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clock me-2"></i>Fechas y Horas del Viaje
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Fecha Salida *</label>
                            <input type="date" name="fecha_salida" id="fecha_salida" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($data['fecha_salida'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Hora Salida *</label>
                            <input type="time" name="hora_salida" id="hora_salida" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($data['hora_salida'] ?? '08:00'); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Fecha Llegada *</label>
                            <input type="date" name="fecha_llegada" id="fecha_llegada" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($data['fecha_llegada'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Hora Llegada *</label>
                            <input type="time" name="hora_llegada" id="hora_llegada" class="form-control form-control-lg"
                                   value="<?php echo htmlspecialchars($data['hora_llegada'] ?? '12:00'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado y observaciones -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-info-circle me-2"></i>Estado y Observaciones
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Estado</label>
                            <select name="estado" id="estado" class="form-select form-select-lg">
                                <option value="PROGRAMADO" <?php echo (isset($data['estado']) && $data['estado'] == 'PROGRAMADO') ? 'selected' : ''; ?>>📅 Programado</option>
                                <option value="EN_CURSO" <?php echo (isset($data['estado']) && $data['estado'] == 'EN_CURSO') ? 'selected' : ''; ?>>🚌 En Curso</option>
                                <option value="COMPLETADO" <?php echo (isset($data['estado']) && $data['estado'] == 'COMPLETADO') ? 'selected' : ''; ?>>✅ Completado</option>
                                <option value="CANCELADO" <?php echo (isset($data['estado']) && $data['estado'] == 'CANCELADO') ? 'selected' : ''; ?>>❌ Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                      placeholder="Observaciones adicionales..."><?php echo htmlspecialchars($data['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen dinámico -->
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>Resumen del Viaje
                    </h6>
                    <div class="row text-center g-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Ruta</small>
                            <strong id="resumen_ruta">---</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Bus</small>
                            <strong id="resumen_bus">---</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Conductor</small>
                            <strong id="resumen_conductor">---</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Duración</small>
                            <strong id="resumen_duracion">---</strong>
                        </div>
                    </div>
                    
                    <!-- ✅ NUEVO: Tipo de servicio destacado -->
                    <hr class="my-3">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Tipo de Servicio del Bus</small>
                            <span class="badge bg-secondary fs-6" id="resumen_tipo_servicio">---</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block mb-1">Total de Asientos</small>
                            <strong id="resumen_capacidad">---</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-1"></i>Guardar Viaje
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formViaje').requestSubmit();" title="Guardar Viaje">
    <i class="fas fa-save"></i>
</a>

<script>
// ============================================================
// UTILIDAD: Devolver color según tipo de servicio
// ============================================================
function colorTipoServicio(tipo) {
    switch(tipo) {
        case 'CAMA':      return 'bg-danger';
        case 'SEMICAMA':  return 'bg-warning text-dark';
        case 'ECONOMICO': return 'bg-info text-dark';
        default:          return 'bg-secondary';
    }
}

// ============================================================
// UTILIDAD: Devolver etiqueta bonita según tipo de servicio
// ============================================================
function etiquetaTipoServicio(tipo) {
    switch(tipo) {
        case 'CAMA':      return '🛌 CAMA';
        case 'SEMICAMA':  return '🛏️ SEMICAMA';
        case 'ECONOMICO': return '💺 ECONÓMICO';
        default:          return tipo;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const rutaSelect = document.getElementById('ruta_id');
    const busSelect = document.getElementById('bus_id');
    const conductorSelect = document.getElementById('conductor_id');
    const precioInput = document.getElementById('precio_final');

    // ============================================================
    // CAMBIO DE RUTA
    // ============================================================
    rutaSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.dataset.precio) precioInput.value = selected.dataset.precio;
        document.getElementById('resumen_ruta').textContent = 
            (selected.dataset.origen || '') + ' → ' + (selected.dataset.destino || '');
        document.getElementById('resumen_duracion').textContent = selected.dataset.duracion || '---';
    });

    // ============================================================
    // CAMBIO DE BUS — Actualiza TODA la info del bus
    // ============================================================
    busSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        
        const placa = selected.dataset.placa || '';
        const marca = selected.dataset.marca || '';
        const capacidad = selected.dataset.capacidad || '0';
        const tipoServicio = selected.dataset.tipoServicio || '';
        const pisos = selected.dataset.pisos || '1';
        
        // ✅ Actualizar resumen
        document.getElementById('resumen_bus').textContent = placa + ' (' + capacidad + ' asientos)';
        document.getElementById('resumen_capacidad').textContent = capacidad + ' asientos';
        
        // ✅ Actualizar badge del tipo de servicio
        const badge = document.getElementById('resumen_tipo_servicio');
        if (tipoServicio) {
            badge.textContent = etiquetaTipoServicio(tipoServicio);
            badge.className = 'badge fs-6 ' + colorTipoServicio(tipoServicio);
        } else {
            badge.textContent = '---';
            badge.className = 'badge bg-secondary fs-6';
        }
        
        // ✅ Mostrar/actualizar tarjeta de preview del bus
        const cardPreview = document.getElementById('card_preview_bus');
        if (placa) {
            cardPreview.style.display = 'block';
            
            document.getElementById('preview_placa').textContent = placa;
            document.getElementById('preview_marca').textContent = marca;
            document.getElementById('preview_capacidad').textContent = capacidad;
            document.getElementById('preview_pisos').textContent = 
                pisos + ' piso' + (parseInt(pisos) > 1 ? 's' : '');
            
            const badgePreview = document.getElementById('preview_tipo_servicio');
            badgePreview.textContent = etiquetaTipoServicio(tipoServicio);
            badgePreview.className = 'badge fs-5 ' + colorTipoServicio(tipoServicio);
        } else {
            cardPreview.style.display = 'none';
        }
    });

    // ============================================================
    // CAMBIO DE CONDUCTOR
    // ============================================================
    conductorSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('resumen_conductor').textContent = selected.dataset.nombre || '---';
    });
    
    // ============================================================
    // INICIALIZAR: Si ya hay valores seleccionados, mostrarlos
    // ============================================================
    if (busSelect.value) busSelect.dispatchEvent(new Event('change'));
    if (rutaSelect.value) rutaSelect.dispatchEvent(new Event('change'));
    if (conductorSelect.value) conductorSelect.dispatchEvent(new Event('change'));
});

// ============================================================
// CONFIRMAR GUARDAR
// ============================================================
document.getElementById('formViaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const formRef = this;
    const ruta = document.getElementById('ruta_id');
    const bus = document.getElementById('bus_id');
    const conductor = document.getElementById('conductor_id');
    
    if (!ruta.value || !bus.value || !conductor.value) {
        mostrarToast('Complete los campos obligatorios', 'warning');
        return false;
    }
    
    const busData = bus.options[bus.selectedIndex].dataset;
    const tipoServicio = busData.tipoServicio || '---';
    
    Swal.fire({
        title: '¿Guardar viaje?',
        html: `
            <div class="text-start">
                <p><strong>Ruta:</strong> ${ruta.options[ruta.selectedIndex].text}</p>
                <p><strong>Bus:</strong> ${bus.options[bus.selectedIndex].text}</p>
                <p><strong>Tipo de Servicio:</strong> <span class="badge ${colorTipoServicio(tipoServicio)}">${etiquetaTipoServicio(tipoServicio)}</span></p>
                <p><strong>Conductor:</strong> ${conductor.options[conductor.selectedIndex].text}</p>
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
