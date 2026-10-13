<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-bus text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra una nueva unidad de transporte</p>
    </div>
    <a href="<?php echo BASE_URL; ?>buses" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Atención:</h6>
        <ul class="mb-0">
            <?php foreach($errores as $e): ?>
                <li><?php echo $e; ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>buses/store" id="formBus" novalidate>
    <div class="row g-3">

        <!-- IDENTIFICACIÓN -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-id-card me-2"></i>Identificación del Bus
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-hashtag me-1"></i>Placa *
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-car"></i>
                            </span>
                            <input type="text" name="placa" id="placa"
                                   class="form-control font-monospace text-uppercase"
                                   placeholder="ABC-123" maxlength="8"
                                   style="letter-spacing: 2px;"
                                   value="<?php echo htmlspecialchars($data['placa'] ?? ''); ?>"
                                   required autofocus autocomplete="off">
                        </div>
                        <small class="text-muted">
                            Formato: 3 letras + guion + 3 o 4 dígitos (Ej: ABC-123)
                        </small>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-industry me-1"></i>Marca *
                            </label>
                            <input type="text" name="marca" id="marca" class="form-control"
                                   placeholder="Ej: Mercedes Benz" maxlength="50"
                                   value="<?php echo htmlspecialchars($data['marca'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-tag me-1"></i>Modelo *
                            </label>
                            <input type="text" name="modelo" id="modelo" class="form-control"
                                   placeholder="Ej: O500" maxlength="50"
                                   value="<?php echo htmlspecialchars($data['modelo'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-0 mt-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-calendar me-1"></i>Año
                        </label>
                        <input type="text" name="anio" id="anio" class="form-control"
                               placeholder="Ej: 2024" maxlength="4" inputmode="numeric"
                               value="<?php echo htmlspecialchars($data['anio'] ?? ''); ?>">
                        <small class="text-muted">Entre 1950 y <?php echo date('Y') + 1; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARACTERÍSTICAS -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-cog me-2"></i>Características
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-users me-1"></i>Capacidad Total (asientos) *
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">
                                <i class="fas fa-chair"></i>
                            </span>
                            <input type="number" name="capacidad_total" id="capacidad_total"
                                   class="form-control text-center fw-bold"
                                   placeholder="44" min="1" max="100"
                                   value="<?php echo htmlspecialchars($data['capacidad_total'] ?? 44); ?>"
                                   oninput="recalcularDistribucion()"
                                   required>
                            <span class="input-group-text">asientos</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-star me-1"></i>Tipo de Servicio *
                        </label>
                        <select name="tipo_servicio" id="tipo_servicio" class="form-select form-select-lg" required>
                            <option value="ECONOMICO" <?php echo (($data['tipo_servicio'] ?? '') == 'ECONOMICO') ? 'selected' : ''; ?>>💺 Económico</option>
                            <option value="SEMICAMA"  <?php echo (($data['tipo_servicio'] ?? '') == 'SEMICAMA')  ? 'selected' : ''; ?>>🛏️ Semicama</option>
                            <option value="CAMA"      <?php echo (($data['tipo_servicio'] ?? '') == 'CAMA')      ? 'selected' : ''; ?>>🛌 Cama</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONFIGURACIÓN DE LAYOUT -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-layer-group me-2"></i>Configuración del Mapa de Asientos
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            Configure cómo se distribuyen los asientos en el bus.
                            Esto determina cómo se ve el mapa al vender pasajes.
                        </small>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-layer-group me-1"></i>Número de Pisos *
                            </label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="pisos" id="pisos1" value="1"
                                       <?php echo (($data['pisos'] ?? 2) == 1) ? 'checked' : ''; ?>
                                       onchange="recalcularDistribucion()">
                                <label class="btn btn-outline-primary py-3" for="pisos1">
                                    <i class="fas fa-bus fa-2x d-block mb-1"></i>
                                    <strong>1 Piso</strong>
                                </label>
                                <input type="radio" class="btn-check" name="pisos" id="pisos2" value="2"
                                       <?php echo (($data['pisos'] ?? 2) == 2) ? 'checked' : ''; ?>
                                       onchange="recalcularDistribucion()">
                                <label class="btn btn-outline-primary py-3" for="pisos2">
                                    <i class="fas fa-layer-group fa-2x d-block mb-1"></i>
                                    <strong>2 Pisos</strong>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-chair me-1 text-primary"></i>Asientos Piso 1 *
                            </label>
                            <input type="number" name="asientos_piso_1" id="asientos_piso_1"
                                   class="form-control form-control-lg text-center fw-bold"
                                   value="<?php echo htmlspecialchars($data['asientos_piso_1'] ?? 12); ?>"
                                   min="1" max="100"
                                   oninput="recalcularDistribucion()">
                            <small class="text-muted">Cubierta inferior</small>
                        </div>

                        <div class="col-md-3" id="grupo_asientos_piso_2">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-chair me-1 text-success"></i>Asientos Piso 2
                            </label>
                            <input type="number" name="asientos_piso_2" id="asientos_piso_2"
                                   class="form-control form-control-lg text-center fw-bold"
                                   value="<?php echo htmlspecialchars($data['asientos_piso_2'] ?? 32); ?>"
                                   min="0" max="100"
                                   oninput="recalcularDistribucion()">
                            <small class="text-muted">Cubierta superior</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-calculator me-1"></i>Total Calculado
                            </label>
                            <div class="alert alert-light border mb-0 py-3 text-center">
                                <h3 class="mb-0" id="total_calculado">44</h3>
                                <small class="text-muted" id="estado_calculado">✓ Coincide</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted mb-3">
                        <i class="fas fa-sliders-h me-1"></i>Disposición por Fila
                    </h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-grip-horizontal me-1"></i>Asientos por Fila - Piso 1 *
                            </label>
                            <select name="asientos_por_fila_piso_1" id="asientos_por_fila_piso_1"
                                    class="form-select form-select-lg" onchange="recalcularDistribucion()">
                                <option value="2" <?php echo (($data['asientos_por_fila_piso_1'] ?? 3) == 2) ? 'selected' : ''; ?>>2 (1+1 - Ejecutivo)</option>
                                <option value="3" <?php echo (($data['asientos_por_fila_piso_1'] ?? 3) == 3) ? 'selected' : ''; ?>>3 (2+1 - Semicama/Cama)</option>
                                <option value="4" <?php echo (($data['asientos_por_fila_piso_1'] ?? 3) == 4) ? 'selected' : ''; ?>>4 (2+2 - Económico)</option>
                            </select>
                        </div>

                        <div class="col-md-4" id="grupo_por_fila_2">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-grip-horizontal me-1"></i>Asientos por Fila - Piso 2 *
                            </label>
                            <select name="asientos_por_fila_piso_2" id="asientos_por_fila_piso_2"
                                    class="form-select form-select-lg" onchange="recalcularDistribucion()">
                                <option value="2" <?php echo (($data['asientos_por_fila_piso_2'] ?? 4) == 2) ? 'selected' : ''; ?>>2 (1+1 - Ejecutivo)</option>
                                <option value="3" <?php echo (($data['asientos_por_fila_piso_2'] ?? 4) == 3) ? 'selected' : ''; ?>>3 (2+1 - Semicama/Cama)</option>
                                <option value="4" <?php echo (($data['asientos_por_fila_piso_2'] ?? 4) == 4) ? 'selected' : ''; ?>>4 (2+2 - Económico)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-info-circle me-1"></i>Vista Previa
                            </label>
                            <div class="alert alert-light border mb-0 py-2 small">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <span id="preview_distribucion">Piso 1: 4 filas × 3 asientos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ESTADO -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-bold">
                        <i class="fas fa-toggle-on me-1"></i>Estado del Bus
                    </label>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="estado" id="estadoActivo"
                                   value="ACTIVO" <?php echo (($data['estado'] ?? 'ACTIVO') == 'ACTIVO') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-success w-100 py-3" for="estadoActivo">
                                <i class="fas fa-check-circle fa-2x d-block mb-1"></i>
                                <strong>ACTIVO</strong>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="estado" id="estadoMant"
                                   value="MANTENIMIENTO" <?php echo (($data['estado'] ?? '') == 'MANTENIMIENTO') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-warning w-100 py-3" for="estadoMant">
                                <i class="fas fa-tools fa-2x d-block mb-1"></i>
                                <strong>MANTENIMIENTO</strong>
                            </label>
                        </div>
                        <div class="col-md-4">
                            <input type="radio" class="btn-check" name="estado" id="estadoBaja"
                                   value="BAJA" <?php echo (($data['estado'] ?? '') == 'BAJA') ? 'checked' : ''; ?>>
                            <label class="btn btn-outline-danger w-100 py-3" for="estadoBaja">
                                <i class="fas fa-times-circle fa-2x d-block mb-1"></i>
                                <strong>BAJA</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>buses" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="btnGuardarBus">
                            <i class="fas fa-save me-1"></i>Guardar Bus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formBus').requestSubmit();" title="Guardar Bus">
    <i class="fas fa-save"></i>
</a>

<script>
// ============================================================
// SCRIPT BUSES/CREATE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------------------------------------
    // 1. HELPERS DE INPUTS
    // ------------------------------------------------------------

    // ✅ FIX: placa con formato automático (ABC-123)
    if (typeof aplicarTransformacion === 'function') {
        aplicarTransformacion('placa', function(valor) {
            // Quitar todo lo que no sea letra o número
            let limpio = valor.toUpperCase().replace(/[^A-Z0-9]/g, '');
            // Insertar guión después de las primeras 3 letras
            if (limpio.length > 3) {
                limpio = limpio.slice(0, 3) + '-' + limpio.slice(3, 7);
            }
            return limpio;
        });
    }

    // ✅ FIX: marca y modelo solo letras y números
    if (typeof aplicarTransformacion === 'function') {
        aplicarTransformacion('marca', function(valor) {
            return valor.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\-\.]/g, '').slice(0, 50);
        });
        aplicarTransformacion('modelo', function(valor) {
            return valor.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\-\.]/g, '').slice(0, 50);
        });
    }

    // ✅ FIX: año solo números, máximo 4 dígitos
    if (typeof aplicarSoloNumeros === 'function') {
        aplicarSoloNumeros('anio', 4);
    }

    // ✅ FIX: capacidad y asientos con rango
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('capacidad_total', 1, 100, 0);
        aplicarRangoNumerico('asientos_piso_1', 1, 100, 0);
        aplicarRangoNumerico('asientos_piso_2', 0, 100, 0);
    }

    // ------------------------------------------------------------
    // 2. VALIDAR AÑO
    // ------------------------------------------------------------
    const anioInput = document.getElementById('anio');
    if (anioInput) {
        anioInput.addEventListener('blur', function() {
            if (!this.value) return;
            const anio = parseInt(this.value);
            const anioActual = new Date().getFullYear();
            this.classList.remove('is-invalid', 'is-valid');
            if (anio < 1950 || anio > anioActual + 1) {
                this.classList.add('is-invalid');
                if (typeof mostrarToast === 'function') {
                    mostrarToast(`El año debe estar entre 1950 y ${anioActual + 1}`, 'warning');
                }
            } else {
                this.classList.add('is-valid');
            }
        });
    }

    // ------------------------------------------------------------
    // 3. INICIALIZAR DISTRIBUCIÓN
    // ------------------------------------------------------------
    recalcularDistribucion();

    // ------------------------------------------------------------
    // 4. SUBMIT UNIFICADO
    // ------------------------------------------------------------
    const form = document.getElementById('formBus');
    const btnGuardar = document.getElementById('btnGuardarBus');
    let enviando = false;

    form.addEventListener('submit', function(e) {
        if (enviando) return true;
        e.preventDefault();

        const errores = [];

        // --- Recoger valores ---
        const placa    = document.getElementById('placa').value.trim();
        const marca    = document.getElementById('marca').value.trim();
        const modelo   = document.getElementById('modelo').value.trim();
        const anio     = document.getElementById('anio').value.trim();
        const capacidad = parseInt(document.getElementById('capacidad_total').value) || 0;
        const pisos    = parseInt(document.querySelector('input[name="pisos"]:checked')?.value || 2);
        const piso1    = parseInt(document.getElementById('asientos_piso_1').value) || 0;
        const piso2    = parseInt(document.getElementById('asientos_piso_2').value) || 0;
        const tipoServ = document.getElementById('tipo_servicio').value;

        // --- Validaciones ---
        if (!placa) {
            errores.push('La placa es obligatoria');
        } else if (!/^[A-Z]{3}-\d{3,4}$/.test(placa)) {
            errores.push('Formato de placa inválido. Debe ser 3 letras, guion, 3-4 dígitos (Ej: ABC-123)');
        }

        if (!marca)   errores.push('La marca es obligatoria');
        if (!modelo)  errores.push('El modelo es obligatorio');

        if (anio) {
            const anioNum = parseInt(anio);
            const anioActual = new Date().getFullYear();
            if (anioNum < 1950 || anioNum > anioActual + 1) {
                errores.push(`El año debe estar entre 1950 y ${anioActual + 1}`);
            }
        }

        if (capacidad <= 0)   errores.push('La capacidad debe ser mayor a 0');
        if (capacidad > 100)  errores.push('La capacidad no puede superar 100 asientos');

        const sumaPisos = piso1 + piso2;
        if (sumaPisos !== capacidad) {
            errores.push(`La suma de asientos por piso (${piso1} + ${piso2} = ${sumaPisos}) no coincide con la capacidad total (${capacidad})`);
        }

        // --- Mostrar errores ---
        if (errores.length > 0) {
            if (typeof mostrarErroresValidacion === 'function') {
                mostrarErroresValidacion(errores);
            } else {
                alert(errores.join('\n'));
            }
            return false;
        }

        // --- Confirmar ---
        const resumenHtml = `
            <div class="text-start small">
                <p class="mb-1"><strong>Placa:</strong> ${placa}</p>
                <p class="mb-1"><strong>Marca / Modelo:</strong> ${marca} ${modelo}</p>
                <p class="mb-1"><strong>Año:</strong> ${anio || '—'}</p>
                <p class="mb-1"><strong>Servicio:</strong> ${tipoServ}</p>
                <p class="mb-1"><strong>Capacidad:</strong> ${capacidad} asientos</p>
                <p class="mb-0"><strong>Distribución:</strong> Piso 1: ${piso1} | Piso 2: ${piso2} (${pisos} piso${pisos > 1 ? 's' : ''})</p>
            </div>
        `;

        function ejecutarEnvio() {
            enviando = true;
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Guardando...',
                    html: 'Registrando el bus',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
            form.submit();
        }

        if (typeof confirmarPersonalizado === 'function') {
            confirmarPersonalizado(
                '¿Guardar bus?',
                resumenHtml,
                '<i class="fas fa-save me-1"></i>Sí, guardar',
                '#198754',
                ejecutarEnvio
            );
        } else if (confirm('¿Guardar este bus?')) {
            ejecutarEnvio();
        }

        return false;
    });
});

// ============================================================
// RECALCULAR DISTRIBUCIÓN
// ============================================================
function recalcularDistribucion() {
    const capacidad = parseInt(document.getElementById('capacidad_total').value) || 0;
    const pisos = parseInt(document.querySelector('input[name="pisos"]:checked')?.value || 2);
    const piso1 = parseInt(document.getElementById('asientos_piso_1').value) || 0;
    const piso2 = parseInt(document.getElementById('asientos_piso_2').value) || 0;
    const porFila1 = parseInt(document.getElementById('asientos_por_fila_piso_1').value) || 3;
    const porFila2 = parseInt(document.getElementById('asientos_por_fila_piso_2').value) || 4;

    const grupoPiso2   = document.getElementById('grupo_asientos_piso_2');
    const grupoPorFila2 = document.getElementById('grupo_por_fila_2');

    const totalCalc = document.getElementById('total_calculado');
    const estadoCalc = document.getElementById('estado_calculado');

    if (pisos === 1) {
        grupoPiso2.style.opacity = '0.4';
        grupoPiso2.style.pointerEvents = 'none';
        document.getElementById('asientos_piso_2').value = 0;
        grupoPorFila2.style.opacity = '0.4';
        grupoPorFila2.style.pointerEvents = 'none';

        document.getElementById('asientos_piso_1').value = capacidad;
        totalCalc.textContent = capacidad;
        estadoCalc.textContent = '✓ Coincide';
        estadoCalc.className = 'text-success';
    } else {
        grupoPiso2.style.opacity = '1';
        grupoPiso2.style.pointerEvents = 'auto';
        grupoPorFila2.style.opacity = '1';
        grupoPorFila2.style.pointerEvents = 'auto';

        const total = piso1 + piso2;
        totalCalc.textContent = total;

        if (total === capacidad) {
            estadoCalc.textContent = '✓ Coincide';
            estadoCalc.className = 'text-success';
        } else {
            estadoCalc.textContent = `⚠ ${total > capacidad ? 'Sobran' : 'Faltan'} ${Math.abs(capacidad - total)}`;
            estadoCalc.className = 'text-danger';
        }
    }

    // Vista previa
    const filas1 = Math.ceil(piso1 / porFila1);
    const filas2 = Math.ceil(piso2 / porFila2);

    let preview = `Piso 1: ${filas1} filas × ${porFila1} asientos`;
    if (pisos === 2 && piso2 > 0) {
        preview += ` · Piso 2: ${filas2} filas × ${porFila2} asientos`;
    }
    document.getElementById('preview_distribucion').textContent = preview;
}
</script>