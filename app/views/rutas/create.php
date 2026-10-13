<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-route text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra una nueva ruta con origen, destino y precio base</p>
    </div>
    <a href="<?php echo BASE_URL; ?>rutas" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6>
        <ul class="mb-0">
            <?php foreach($errores as $e): ?>
                <li><?php echo $e; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>rutas/store" id="formRuta">
    <div class="row g-3">
        <!-- Origen y Destino -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-map-marker-alt me-2"></i>Origen y Destino
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-play-circle text-primary me-1"></i>Origen *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-flag"></i>
                            </span>
                            <input type="text" name="origen" id="origen" class="form-control" 
                                   placeholder="Ej: Lima" 
                                   value="<?php echo htmlspecialchars($data['origen'] ?? ''); ?>" 
                                   required autofocus>
                        </div>
                        <small class="text-muted">Ciudad o lugar de partida</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-flag-checkered text-danger me-1"></i>Destino *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-danger text-white">
                                <i class="fas fa-flag-checkered"></i>
                            </span>
                            <input type="text" name="destino" id="destino" class="form-control" 
                                   placeholder="Ej: Arequipa" 
                                   value="<?php echo htmlspecialchars($data['destino'] ?? ''); ?>" 
                                   required>
                        </div>
                        <small class="text-muted">Ciudad o lugar de llegada</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del viaje -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-info-circle me-2"></i>Detalles del Viaje
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-road me-1"></i>Distancia (km)
                        </label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="distancia_km" id="distancia_km" 
                                   class="form-control" placeholder="Ej: 1012.50"
                                   value="<?php echo htmlspecialchars($data['distancia_km'] ?? ''); ?>">
                            <span class="input-group-text">km</span>
                        </div>
                    </div>

<div class="mb-3">
    <label class="form-label fw-bold small">
        <i class="fas fa-clock me-1"></i>Duración Estimada
    </label>
    <div class="input-group">
        <span class="input-group-text">
            <i class="fas fa-clock"></i>
        </span>
        <select id="duracion_horas" class="form-select" style="max-width: 100px;">
            <?php 
            // Obtener valor actual si existe
            $horas_actual = 0;
            if (!empty($data['duracion_estimada'])) {
                $partes = explode(':', $data['duracion_estimada']);
                $horas_actual = (int)($partes[0] ?? 0);
            }
            for ($h = 0; $h <= 23; $h++): 
            ?>
                <option value="<?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?>" 
                        <?php echo ($horas_actual == $h) ? 'selected' : ''; ?>>
                    <?php echo str_pad($h, 2, '0', STR_PAD_LEFT); ?> h
                </option>
            <?php endfor; ?>
        </select>
        <select id="duracion_minutos" class="form-select" style="max-width: 100px;">
            <?php 
            // Obtener minutos actuales
            $minutos_actual = 0;
            if (!empty($data['duracion_estimada'])) {
                $partes = explode(':', $data['duracion_estimada']);
                $minutos_actual = (int)($partes[1] ?? 0);
            }
            $opciones_minutos = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55];
            foreach ($opciones_minutos as $m): 
            ?>
                <option value="<?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?>" 
                        <?php echo ($minutos_actual == $m) ? 'selected' : ''; ?>>
                    <?php echo str_pad($m, 2, '0', STR_PAD_LEFT); ?> min
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Campo oculto que se enviará al servidor -->
        <input type="hidden" name="duracion_estimada" id="duracion_estimada" 
               value="<?php echo htmlspecialchars($data['duracion_estimada'] ?? '00:00:00'); ?>">
    </div>
    <small class="text-muted">
        <i class="fas fa-info-circle me-1"></i>
        Seleccione horas y minutos de duración del viaje
    </small>
</div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-money-bill-wave text-success me-1"></i>Precio Base *
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" name="precio_base" id="precio_base" 
                                   class="form-control text-center fw-bold" placeholder="0.00"
                                   value="<?php echo htmlspecialchars($data['precio_base'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="checkbox" name="activo" id="activo" 
                               class="form-check-input" role="switch"
                               <?php echo (!isset($data['activo']) || $data['activo']) ? 'checked' : ''; ?>>
                        <label for="activo" class="form-check-label fw-bold">
                            <i class="fas fa-check-circle text-success me-1"></i>Ruta activa (visible para ventas)
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Los campos con <span class="text-danger">*</span> son obligatorios
                        </small>
                        <div class="d-flex gap-2">
                            <a href="<?php echo BASE_URL; ?>rutas" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-1"></i>Guardar Ruta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- FAB móvil -->
<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formRuta').requestSubmit();" title="Guardar Ruta">
    <i class="fas fa-save"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // SINCRONIZAR HORAS Y MINUTOS CON EL CAMPO OCULTO
    // ============================================================
    const horasSelect = document.getElementById('duracion_horas');
    const minutosSelect = document.getElementById('duracion_minutos');
    const duracionInput = document.getElementById('duracion_estimada');
    
    function actualizarDuracion() {
        if (horasSelect && minutosSelect && duracionInput) {
            const horas = horasSelect.value;
            const minutos = minutosSelect.value;
            duracionInput.value = horas + ':' + minutos + ':00';
            console.log('✅ Duración actualizada:', duracionInput.value);
        }
    }
    
    if (horasSelect && minutosSelect) {
        horasSelect.addEventListener('change', actualizarDuracion);
        minutosSelect.addEventListener('change', actualizarDuracion);
        actualizarDuracion(); // Inicializar
    }

    // ============================================================
    // CONFIRMACIÓN CON SWEETALERT2 ANTES DE GUARDAR
    // ============================================================
    const form = document.getElementById('formRuta');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const origen = document.getElementById('origen').value;
        const destino = document.getElementById('destino').value;
        const precio = document.getElementById('precio_base').value;
        const duracion = duracionInput ? duracionInput.value : '';
        
        // Validaciones
        if (!origen || !destino || !precio) {
            mostrarToast('Complete los campos obligatorios', 'warning');
            return false;
        }
        
        const formRef = this;
        
        Swal.fire({
            title: '¿Guardar ruta?',
            html: `
                <div class="text-start">
                    <p><strong>Ruta:</strong> ${origen} → ${destino}</p>
                    <p><strong>Duración:</strong> ${duracion || 'No especificada'}</p>
                    <p><strong>Precio:</strong> <span class="text-success">S/ ${precio}</span></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, guardar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                formRef.submit();
            }
        });
    });
});
</script>