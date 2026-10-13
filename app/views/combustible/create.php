<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-gas-pump text-warning me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registrar una nueva carga de combustible</p>
    </div>
    <a href="<?php echo BASE_URL; ?>combustible" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-lg-9 mx-auto">
        <form method="POST" action="<?php echo BASE_URL; ?>combustible/store" id="formCombustible">
            
            <!-- ============================================
                 SECCIÓN 1: VEHÍCULO
                 ============================================ -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-bus me-2"></i>Información del Vehículo
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Bus -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                Bus <span class="text-danger">*</span>
                            </label>
                            <select name="bus_id" id="bus_id" class="form-select" required>
                                <option value="">-- Seleccione un bus --</option>
                                <?php foreach ($buses as $b): ?>
                                    <option value="<?php echo $b['id']; ?>"
                                            data-placa="<?php echo htmlspecialchars($b['placa']); ?>">
                                        <?php echo htmlspecialchars($b['placa']); ?>
                                        - <?php echo htmlspecialchars($b['marca'] . ' ' . $b['modelo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Seleccione el bus que recibió la carga</div>
                        </div>

                        <!-- Conductor -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Conductor</label>
                            <select name="conductor_id" class="form-select">
                                <option value="">-- Sin conductor asignado --</option>
                                <?php if (!empty($conductores)): ?>
                                    <?php foreach ($conductores as $c): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo htmlspecialchars($c['nombre'] . ' ' . $c['apellido']); ?>
                                            <?php if (!empty($c['numero_licencia'])): ?>
                                                - Lic: <?php echo htmlspecialchars($c['numero_licencia']); ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">Opcional. Conductor que realizó la carga</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 SECCIÓN 2: DATOS DE LA CARGA
                 ============================================ -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-tint me-2"></i>Datos de la Carga
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Tipo Combustible -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                Tipo de Combustible <span class="text-danger">*</span>
                            </label>
                            <select name="tipo_combustible" class="form-select" required>
                                <option value="DIESEL" selected>Diesel</option>
                                <option value="GASOLINA">Gasolina</option>
                                <option value="GNV">GNV</option>
                                <option value="GAS">Gas</option>
                            </select>
                        </div>

                        <!-- Galones -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                Cantidad (Galones) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tint"></i></span>
                                <input type="number" name="cantidad_galones" id="cantidad_galones"
                                       class="form-control" step="0.01" min="0.01" max="500"
                                       placeholder="0.00" required>
                                <span class="input-group-text">gal</span>
                            </div>
                        </div>

                        <!-- Precio por Galón -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">
                                Precio por Galón (S/) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" name="precio_galon" id="precio_galon"
                                       class="form-control" step="0.01" min="0.01" max="99.99"
                                       placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Total (solo lectura) -->
                        <div class="col-12">
                            <div class="alert alert-secondary mb-0 d-flex justify-content-between align-items-center" id="totalAlert">
                                <strong class="fs-6">
                                    <i class="fas fa-calculator me-2"></i>TOTAL A PAGAR:
                                </strong>
                                <h4 class="mb-0">
                                    S/ <span id="totalPreview">0.00</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 SECCIÓN 3: INFORMACIÓN ADICIONAL
                 ============================================ -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Información Adicional
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Kilometraje -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Kilometraje Actual</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                <input type="text" name="kilometraje_actual" id="kilometraje_actual"
                                       class="form-control" placeholder="Ej: 15000"
                                       inputmode="numeric" maxlength="7">
                                <span class="input-group-text">km</span>
                            </div>
                            <div class="form-text">Opcional. Permite calcular rendimiento km/gal</div>
                        </div>

                        <!-- Estación de Servicio -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Estación de Servicio</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="estacion_servicio" id="estacion_servicio"
                                       class="form-control" placeholder="Ej: Primax - Av. Larco"
                                       maxlength="150">
                            </div>
                        </div>

                        <!-- N° Factura -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">N° de Factura / Boleta</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                <input type="text" name="numero_factura" id="numero_factura"
                                       class="form-control" placeholder="Ej: F001-000123"
                                       maxlength="20" style="text-transform: uppercase;">
                            </div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <label class="form-label fw-bold small">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"
                                      placeholder="Notas adicionales sobre la carga..."
                                      maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================
                 BOTONES
                 ============================================ -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="<?php echo BASE_URL; ?>combustible" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save me-1"></i>Guardar Carga
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     SCRIPTS
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================
    // 1. CÁLCULO AUTOMÁTICO DEL TOTAL
    // ============================================================
    const cantidadInput  = document.getElementById('cantidad_galones');
    const precioInput    = document.getElementById('precio_galon');
    const totalPreview   = document.getElementById('totalPreview');
    const totalAlert     = document.getElementById('totalAlert');

    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const precio   = parseFloat(precioInput.value) || 0;
        const total    = cantidad * precio;

        totalPreview.textContent = total.toFixed(2);

        // Cambiar color de la alerta según el monto
        totalAlert.classList.remove('alert-secondary', 'alert-success', 'alert-warning');
        if (total > 0) {
            totalAlert.classList.add('alert-success');
        } else {
            totalAlert.classList.add('alert-secondary');
        }
    }

    cantidadInput.addEventListener('input', calcularTotal);
    precioInput.addEventListener('input', calcularTotal);

    // ============================================================
    // 2. VALIDACIONES CON form-helpers.js
    // ============================================================

    // Kilometraje: solo números, máx 7 dígitos
    if (typeof aplicarSoloNumeros === 'function') {
        aplicarSoloNumeros('kilometraje_actual', 7);
    }

    // N° Factura: alfanumérico, mayúsculas, máx 20, permite guiones
    if (typeof aplicarAlfanumerico === 'function') {
        aplicarAlfanumerico('numero_factura', 20, true);
    }

    // Estación: solo letras, números y espacios (para nombres como "Primax 123")
    if (typeof aplicarTransformacion === 'function') {
        aplicarTransformacion('estacion_servicio', function(valor) {
            return valor.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\-\.]/g, '').slice(0, 150);
        });
    }

    // Galones: rango 0.01 - 500
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('cantidad_galones', 0.01, 500, 2);
    }

    // Precio: rango 0.01 - 99.99
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('precio_galon', 0.01, 99.99, 2);
    }

    // ============================================================
    // 3. VALIDACIÓN Y CONFIRMACIÓN AL ENVIAR
    // ============================================================
    const form = document.getElementById('formCombustible');
    const btnGuardar = document.getElementById('btnGuardar');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // --- Recoger valores ---
        const busSelect = document.getElementById('bus_id');
        const busTexto  = busSelect.options[busSelect.selectedIndex]?.text || '';
        const cantidad  = parseFloat(cantidadInput.value) || 0;
        const precio    = parseFloat(precioInput.value) || 0;
        const total     = cantidad * precio;
        const tipoComb  = document.querySelector('select[name="tipo_combustible"]').value;
        const estacion  = document.getElementById('estacion_servicio').value.trim();
        const factura   = document.getElementById('numero_factura').value.trim();
        const km        = document.getElementById('kilometraje_actual').value.trim();

        // --- Validaciones ---
        const errores = [];
        if (!busSelect.value) errores.push('Debe seleccionar un bus');
        if (cantidad <= 0)    errores.push('La cantidad de galones debe ser mayor a 0');
        if (precio <= 0)      errores.push('El precio por galón debe ser mayor a 0');

        if (errores.length > 0) {
            if (typeof mostrarErroresValidacion === 'function') {
                mostrarErroresValidacion(errores);
            } else {
                alert(errores.join('\n'));
            }
            return false;
        }

        // --- Construir HTML de confirmación ---
        const detalle = `
            <div class="text-start small">
                <p class="mb-1"><strong>Bus:</strong> ${busTexto}</p>
                <p class="mb-1"><strong>Combustible:</strong> ${tipoComb}</p>
                <p class="mb-1"><strong>Cantidad:</strong> ${cantidad.toFixed(2)} gal</p>
                <p class="mb-1"><strong>Precio/gl:</strong> S/ ${precio.toFixed(2)}</p>
                ${estacion ? `<p class="mb-1"><strong>Estación:</strong> ${estacion}</p>` : ''}
                ${factura  ? `<p class="mb-1"><strong>Factura:</strong> ${factura}</p>`   : ''}
                ${km       ? `<p class="mb-1"><strong>Kilometraje:</strong> ${km} km</p>`: ''}
                <hr class="my-2">
                <p class="mb-0 fs-5 text-success text-center">
                    <strong>TOTAL: S/ ${total.toFixed(2)}</strong>
                </p>
            </div>
        `;

        // --- Confirmar con SweetAlert2 ---
        if (typeof confirmarPersonalizado === 'function') {
            confirmarPersonalizado(
                '¿Registrar carga?',
                detalle,
                '<i class="fas fa-save me-1"></i>Sí, guardar',
                '#0d6efd',
                function() {
                    Swal.fire({
                        title: 'Guardando...',
                        html: 'Registrando la carga de combustible',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                    // Deshabilitar botón para evitar doble envío
                    btnGuardar.disabled = true;
                    form.submit();
                }
            );
        } else if (confirm('¿Registrar esta carga de combustible?')) {
            btnGuardar.disabled = true;
            form.submit();
        }

        return false;
    });

    // ============================================================
    // 4. FOCUS INICIAL
    // ============================================================
    document.getElementById('bus_id').focus();

});
</script>