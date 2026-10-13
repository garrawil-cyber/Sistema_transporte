<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-edit text-warning me-2"></i><?php echo $titulo ?? 'Editar Viaje'; ?>
        </h1>
        <p class="text-muted mb-0 small">
            Modifica los datos del viaje #<?php echo $viaje['id']; ?>
            <span class="badge bg-<?php 
                echo $viaje['estado'] == 'PROGRAMADO' ? 'primary' : 
                    ($viaje['estado'] == 'EN_CURSO' ? 'warning' : 
                    ($viaje['estado'] == 'COMPLETADO' ? 'success' : 'danger')); 
            ?> ms-2">
                <?php echo $viaje['estado']; ?>
            </span>
        </p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>viajes/show/<?php echo $viaje['id']; ?>" class="btn btn-info">
            <i class="fas fa-eye me-1"></i>Ver Detalle
        </a>
        <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<?php if (($viaje['pasajeros_abordo'] ?? 0) > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <strong>¡Atención!</strong> Este viaje tiene <strong><?php echo $viaje['pasajeros_abordo']; ?></strong> pasajeros a bordo.
            <br>
            <small>Cambiar el bus, ruta o fechas puede afectar las reservas existentes.</small>
        </div>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>viajes/update/<?php echo $viaje['id']; ?>" id="formViaje">
    <div class="row g-3">
        <!-- RUTA Y BUS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-route me-2"></i>Ruta y Bus
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-route me-1"></i>Ruta *
                        </label>
                        <select name="ruta_id" id="ruta_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione una ruta --</option>
                            <?php foreach ($rutas as $ruta): ?>
                                <option value="<?php echo $ruta['id']; ?>" 
                                        data-precio="<?php echo $ruta['precio_base']; ?>"
                                        data-origen="<?php echo htmlspecialchars($ruta['origen']); ?>"
                                        data-destino="<?php echo htmlspecialchars($ruta['destino']); ?>"
                                        data-duracion="<?php echo $ruta['duracion_estimada']; ?>"
                                        <?php echo ($viaje['ruta_id'] == $ruta['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ruta['origen'] . ' → ' . $ruta['destino']); ?>
                                    (S/ <?php echo number_format($ruta['precio_base'], 2); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Ruta actual del viaje</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-bus me-1"></i>Bus *
                        </label>
                        <select name="bus_id" id="bus_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione un bus --</option>
                            <?php foreach ($buses as $bus): ?>
                                <option value="<?php echo $bus['id']; ?>" 
                                        data-capacidad="<?php echo $bus['capacidad_total']; ?>"
                                        data-placa="<?php echo $bus['placa']; ?>"
                                        data-tipo-servicio="<?php echo $bus['tipo_servicio']; ?>"
                                        <?php echo ($viaje['bus_id'] == $bus['id']) ? 'selected' : ''; ?>>
                                    <?php echo $bus['placa']; ?> - <?php echo $bus['marca'] . ' ' . $bus['modelo']; ?>
                                    (<?php echo $bus['capacidad_total']; ?> asientos · <?php echo $bus['tipo_servicio']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Si cambias el bus, los asientos se regeneran automáticamente según su layout
                            (bloqueado si el viaje ya tiene pasajes vendidos)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONDUCTOR Y PRECIO -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-user-check me-2"></i>Conductor y Precio
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-user me-1"></i>Conductor *
                        </label>
                        <select name="conductor_id" id="conductor_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione un conductor --</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?php echo $conductor['id']; ?>"
                                        <?php echo ($viaje['usuario_creador'] == $conductor['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($conductor['nombre_completo']); ?>
                                    <?php if (!empty($conductor['telefono'])): ?>
                                        (📞 <?php echo $conductor['telefono']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-money-bill-wave me-1"></i>Precio Final (S/) *
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" name="precio_final" id="precio_final" 
                                   class="form-control text-center fw-bold"
                                   value="<?php echo htmlspecialchars($viaje['precio_final']); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FECHAS Y HORAS -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-clock me-2"></i>Fechas y Horas del Viaje
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-calendar-day me-1"></i>Fecha Salida *
                            </label>
                            <input type="date" name="fecha_salida" id="fecha_salida" 
                                   class="form-control form-control-lg"
                                   value="<?php echo date('Y-m-d', strtotime($viaje['fecha_salida'])); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-clock me-1"></i>Hora Salida *
                            </label>
                            <input type="time" name="hora_salida" id="hora_salida" 
                                   class="form-control form-control-lg"
                                   value="<?php echo date('H:i', strtotime($viaje['fecha_salida'])); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-calendar-day me-1"></i>Fecha Llegada *
                            </label>
                            <input type="date" name="fecha_llegada" id="fecha_llegada" 
                                   class="form-control form-control-lg"
                                   value="<?php echo date('Y-m-d', strtotime($viaje['fecha_llegada'])); ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-clock me-1"></i>Hora Llegada *
                            </label>
                            <input type="time" name="hora_llegada" id="hora_llegada" 
                                   class="form-control form-control-lg"
                                   value="<?php echo date('H:i', strtotime($viaje['fecha_llegada'])); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ESTADO Y OBSERVACIONES -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-info-circle me-2"></i>Estado y Observaciones
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-toggle-on me-1"></i>Estado
                            </label>
                            <select name="estado" id="estado" class="form-select form-select-lg">
                                <option value="PROGRAMADO" <?php echo ($viaje['estado'] == 'PROGRAMADO') ? 'selected' : ''; ?>>📅 Programado</option>
                                <option value="EN_CURSO" <?php echo ($viaje['estado'] == 'EN_CURSO') ? 'selected' : ''; ?>>🚌 En Curso</option>
                                <option value="COMPLETADO" <?php echo ($viaje['estado'] == 'COMPLETADO') ? 'selected' : ''; ?>>✅ Completado</option>
                                <option value="CANCELADO" <?php echo ($viaje['estado'] == 'CANCELADO') ? 'selected' : ''; ?>>❌ Cancelado</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-comment me-1"></i>Observaciones
                            </label>
                            <textarea name="observaciones" class="form-control" rows="3"
                                      placeholder="Observaciones adicionales..."><?php echo htmlspecialchars($viaje['observaciones'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RESUMEN -->
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-info-circle me-1"></i>Resumen del Viaje
                    </h6>
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Ruta</small>
                            <strong id="resumen_ruta">
                                <?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?>
                            </strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Bus</small>
                            <strong id="resumen_bus">
                                <?php echo $viaje['placa']; ?> (<?php echo $viaje['capacidad_total']; ?> asientos)
                            </strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block">Conductor</small>
                            <strong id="resumen_conductor">
                                <?php echo htmlspecialchars($viaje['conductor_nombre'] ?? 'Sin asignar'); ?>
                            </strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Pasajeros a Bordo</small>
                            <strong class="text-<?php echo ($viaje['pasajeros_abordo'] ?? 0) > 0 ? 'success' : 'muted'; ?>">
                                <?php echo $viaje['pasajeros_abordo'] ?? 0; ?> / <?php echo $viaje['asientos_libres'] ?? 0; ?> libres
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Los campos con <span class="text-danger">*</span> son obligatorios
                        </small>
                        <div class="d-flex gap-2">
                            <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-1"></i>Actualizar Viaje
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formViaje').requestSubmit();" title="Actualizar Viaje">
    <i class="fas fa-save"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rutaSelect = document.getElementById('ruta_id');
    const busSelect = document.getElementById('bus_id');
    const conductorSelect = document.getElementById('conductor_id');
    const precioInput = document.getElementById('precio_final');

    rutaSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const precio = selected.dataset.precio;
        const origen = selected.dataset.origen || '';
        const destino = selected.dataset.destino || '';
        
        if (precio && (!precioInput.value || precioInput.value == 0)) {
            precioInput.value = precio;
        }
        
        document.getElementById('resumen_ruta').textContent = origen + ' → ' + destino;
    });

    busSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const placa = selected.dataset.placa || '';
        const capacidad = selected.dataset.capacidad || '';
        document.getElementById('resumen_bus').textContent = placa + ' (' + capacidad + ' asientos)';
    });

    conductorSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('resumen_conductor').textContent = selected.text || '---';
    });
});

// ✅ Confirmación antes de enviar (con validación CORREGIDA)
document.getElementById('formViaje').addEventListener('submit', function(e) {
    e.preventDefault();
    const formRef = this;
    
    // Obtener valores
    const ruta = document.getElementById('ruta_id');
    const bus = document.getElementById('bus_id');
    const conductor = document.getElementById('conductor_id');
    const fechaSalida = document.getElementById('fecha_salida').value;
    const horaSalida = document.getElementById('hora_salida').value;
    const fechaLlegada = document.getElementById('fecha_llegada').value;
    const horaLlegada = document.getElementById('hora_llegada').value;
    
    // Validar campos requeridos
    if (!ruta.value || !bus.value || !conductor.value) {
        mostrarToast('Complete los campos obligatorios', 'warning');
        return false;
    }
    
    // ✅ VALIDACIÓN CORREGIDA: combinar fecha + hora
    const dtSalida = new Date(fechaSalida + 'T' + horaSalida);
    const dtLlegada = new Date(fechaLlegada + 'T' + horaLlegada);
    
    if (dtLlegada <= dtSalida) {
        mostrarToast('La fecha/hora de llegada debe ser posterior a la de salida', 'warning');
        return false;
    }
    
    // Detectar cambios críticos
    const rutaCambiada = ruta.value != '<?php echo $viaje['ruta_id']; ?>';
    const busCambiado = bus.value != '<?php echo $viaje['bus_id']; ?>';
    const fechaCambiada = fechaSalida != '<?php echo date('Y-m-d', strtotime($viaje['fecha_salida'])); ?>';
    const hayPasajeros = <?php echo ($viaje['pasajeros_abordo'] ?? 0) > 0 ? 'true' : 'false'; ?>;
    
    let advertencia = '';
    if (hayPasajeros && busCambiado) {
        advertencia = `
            <div class="alert alert-danger mt-3 mb-0 text-start">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>¡Cuidado!</strong> Este viaje tiene pasajeros y cambiaste el bus.
                El servidor rechazará este cambio para no invalidar los asientos vendidos.
            </div>
        `;
    } else if (hayPasajeros && (rutaCambiada || fechaCambiada)) {
        advertencia = `
            <div class="alert alert-warning mt-3 mb-0 text-start">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>¡Cuidado!</strong> Este viaje tiene pasajeros.
                Los cambios críticos pueden afectar sus reservas.
            </div>
        `;
    }
    
    Swal.fire({
        title: '¿Actualizar viaje?',
        html: `
            <div class="text-start">
                <p><strong>Ruta:</strong> ${ruta.options[ruta.selectedIndex].text}</p>
                <p><strong>Bus:</strong> ${bus.options[bus.selectedIndex].text}</p>
                <p><strong>Conductor:</strong> ${conductor.options[conductor.selectedIndex].text}</p>
                <p><strong>Salida:</strong> ${fechaSalida} ${horaSalida}</p>
                <p><strong>Llegada:</strong> ${fechaLlegada} ${horaLlegada}</p>
                ${advertencia}
            </div>
        `,
        icon: (advertencia) ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, actualizar',
        cancelButtonText: 'Revisar',
        confirmButtonColor: (advertencia) ? '#dc3545' : '#198754',
        width: advertencia ? 600 : 500
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ 
                title: 'Actualizando...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });
            formRef.submit();
        }
    });
});
</script>