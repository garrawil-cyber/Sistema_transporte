<!-- KPIs del día -->
<?php if (!empty($stats)): ?>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Giros Hoy</div>
                    <div class="kpi-value"><?php echo $stats['total_hoy'] ?? 0; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Registrados</div>
                    <div class="kpi-value"><?php echo $stats['registrados'] ?? 0; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Pagados</div>
                    <div class="kpi-value"><?php echo $stats['pagados'] ?? 0; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card kpi-card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="kpi-label text-muted">Monto Hoy</div>
                    <div class="kpi-value text-primary" style="font-size: 1.1rem;">
                        S/ <?php echo number_format($stats['monto_hoy'] ?? 0, 2); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-money-bill-transfer text-info me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra un nuevo envío de dinero con comisión automática</p>
    </div>
    <a href="<?php echo BASE_URL; ?>giros" class="btn btn-secondary">
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

<form method="POST" id="formGiro" action="<?php echo BASE_URL; ?>giros/create">
    <input type="hidden" name="action" value="guardar">
    
    <div class="row g-3">
        <!-- ============================================================
             REMITENTE
             ============================================================ -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user me-2"></i>Remitente</span>
                    <small class="badge bg-light text-dark">Quien envía</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-id-card me-1"></i>Buscar por DNI
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="remitente_dni_buscar" class="form-control" 
                                   placeholder="Ingrese DNI y presione Enter" maxlength="8">
                            <button type="button" class="btn btn-primary" onclick="buscarCliente('remitente')">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>Si existe, se autocompletará
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">DNI / Documento *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="remitente_dni" id="remitente_dni" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($remitente['numero_documento'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Nombres *</label>
                            <input type="text" name="remitente_nombre" id="remitente_nombre" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($remitente['nombre'] ?? ''); ?>" 
                                   required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Apellidos *</label>
                            <input type="text" name="remitente_apellido" id="remitente_apellido" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($remitente['apellido'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" name="remitente_telefono" id="remitente_telefono" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($remitente['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="remitente_email" id="remitente_email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($remitente['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             DESTINATARIO
             ============================================================ -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user-check me-2"></i>Destinatario</span>
                    <small class="badge bg-light text-dark">Quien recibe</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-id-card me-1"></i>Buscar por DNI
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="destinatario_dni_buscar" class="form-control" 
                                   placeholder="Ingrese DNI y presione Enter" maxlength="8">
                            <button type="button" class="btn btn-success" onclick="buscarCliente('destinatario')">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>Si existe, se autocompletará
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">DNI / Documento *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="destinatario_dni" id="destinatario_dni" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($destinatario['numero_documento'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Nombres *</label>
                            <input type="text" name="destinatario_nombre" id="destinatario_nombre" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($destinatario['nombre'] ?? ''); ?>" 
                                   required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Apellidos *</label>
                            <input type="text" name="destinatario_apellido" id="destinatario_apellido" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($destinatario['apellido'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row g-2 mb-0">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" name="destinatario_telefono" id="destinatario_telefono" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($destinatario['telefono'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="destinatario_email" id="destinatario_email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($destinatario['email'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             DETALLES DEL GIRO
             ============================================================ -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <i class="fas fa-hand-holding-usd me-2"></i>Detalles del Giro
                </div>
                <div class="card-body">

                    <!-- ✅ FILA 1: RUTA DEL GIRO -->
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-route me-1"></i>Ruta del Giro
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt text-primary me-1"></i>Lugar de Origen
                            </label>
                            <input type="text" name="lugar_origen" class="form-control" 
                                   placeholder="Ej: Piura"
                                   value="<?php echo htmlspecialchars($_POST['lugar_origen'] ?? ''); ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>Lugar de Destino *
                            </label>
                            <input type="text" name="lugar_destino" class="form-control" 
                                   placeholder="Ej: Lima"
                                   value="<?php echo htmlspecialchars($_POST['lugar_destino'] ?? ''); ?>"
                                   required>
                            <small class="text-muted">Ciudad donde se cobrará el giro</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-comment me-1"></i>Concepto
                            </label>
                            <input type="text" name="concepto" class="form-control" 
                                   placeholder="Ej: Pago de servicio"
                                   value="<?php echo htmlspecialchars($_POST['concepto'] ?? ''); ?>">
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- ✅ FILA 2: MONTO, COMISIÓN, MONEDA, MÉTODOS -->
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-money-bill-wave me-1"></i>Datos del Envío
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-money-bill-wave me-1"></i>Monto (S/) *
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-success text-white">S/</span>
                                <input type="number" step="0.01" name="monto" id="monto" 
                                       class="form-control text-center fw-bold" 
                                       placeholder="0.00" 
                                       oninput="calcularComision()" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-percentage me-1 text-warning"></i>
                                Comisión (<?php echo $comision_porcentaje; ?>%)
                            </label>
                            <div class="alert alert-warning mb-0 py-2 text-center">
                                <h4 class="mb-0">S/ <span id="comision_display">0.00</span></h4>
                                <input type="hidden" name="comision" id="comision_hidden" value="0">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-coins me-1"></i>Moneda
                            </label>
                            <select name="moneda" class="form-select form-select-lg">
                                <option value="PEN">🇵🇪 PEN - Soles</option>
                                <option value="USD">🇺🇸 USD - Dólares</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-arrow-up me-1"></i>Pago Origen
                            </label>
                            <select name="metodo_pago_origen" class="form-select form-select-lg">
                                <option value="EFECTIVO">💵 Efectivo</option>
                                <option value="TARJETA">💳 Tarjeta</option>
                                <option value="TRANSFERENCIA">🏦 Transferencia</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-arrow-down me-1"></i>Pago Destino
                            </label>
                            <select name="metodo_pago_destino" class="form-select form-select-lg">
                                <option value="EFECTIVO">💵 Efectivo</option>
                                <option value="DEPOSITO">🏦 Depósito</option>
                                <option value="OTRO">📝 Otro</option>
                            </select>
                        </div>
                    </div>

                    <!-- Resumen visual -->
                    <div class="row mt-4">
                        <div class="col-md-6 mx-auto">
                            <div class="card bg-light border-0">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-calculator me-1"></i>Resumen del Giro
                                    </h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Monto:</span>
                                        <strong id="resumen_monto">S/ 0.00</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Comisión:</span>
                                        <strong class="text-warning" id="resumen_comision">S/ 0.00</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold">Total a Cobrar:</span>
                                        <h4 class="text-success mb-0" id="resumen_total">S/ 0.00</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ============================================================
             BOTONES
             ============================================================ -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Los campos con <span class="text-danger">*</span> son obligatorios
                        </small>
                        <div class="d-flex gap-2">
                            <a href="<?php echo BASE_URL; ?>giros" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-1"></i>Registrar Giro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formGiro').requestSubmit();" title="Registrar Giro">
    <i class="fas fa-save"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ============================================================
    // VALIDACIONES USANDO ARCHIVOS EXTERNOS
    // ============================================================
    
    // 1. Validación de DNI
    if (typeof aplicarValidacionDocumento === 'function') {
        aplicarValidacionDocumento('remitente_dni');
        aplicarValidacionDocumento('destinatario_dni');
        aplicarValidacionDocumento('remitente_dni_buscar');
        aplicarValidacionDocumento('destinatario_dni_buscar');
    }

    // 2. Solo letras en nombres/apellidos
    if (typeof aplicarSoloLetras === 'function') {
        ['remitente_nombre', 'remitente_apellido',
         'destinatario_nombre', 'destinatario_apellido'].forEach(id => {
            aplicarSoloLetras(id, 100);
        });
    }

    // 3. Teléfono (9 dígitos)
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('remitente_telefono', 9);
        aplicarTelefono('destinatario_telefono', 9);
    }

    // 4. Email
    if (typeof aplicarValidacionEmail === 'function') {
        aplicarValidacionEmail('remitente_email');
        aplicarValidacionEmail('destinatario_email');
    }

    // 5. Rango numérico del monto
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('monto', 0.01, 999999, 2);
    }

    // 6. Búsqueda por Enter
    ['remitente_dni_buscar', 'destinatario_dni_buscar'].forEach(function (inputId) {
        document.getElementById(inputId).addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tipo = inputId.includes('remitente') ? 'remitente' : 'destinatario';
                buscarCliente(tipo);
            }
        });
    });
});

function buscarCliente(tipo) {
    const dni = document.getElementById(tipo + '_dni_buscar').value.trim();
    
    if (dni.length < 8) {
        if (typeof mostrarToast === 'function') mostrarToast('Ingrese un DNI válido', 'warning');
        return;
    }
    
    if (typeof mostrarLoading === 'function') {
        mostrarLoading('Buscando cliente...');
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Buscando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }
    
    fetch('<?php echo BASE_URL; ?>clientes/buscarPorDni?dni=' + encodeURIComponent(dni))
        .then(response => response.json())
        .then(data => {
            if (typeof cerrarLoading === 'function') cerrarLoading();
            else if (typeof Swal !== 'undefined') Swal.close();
            
            if (data.error || !data.numero_documento) {
                document.getElementById(tipo + '_dni').value = dni;
                ['nombre', 'apellido', 'telefono', 'email'].forEach(campo => {
                    document.getElementById(tipo + '_' + campo).value = '';
                });
                if (typeof mostrarToast === 'function') mostrarToast('Cliente no encontrado. Complete manualmente.', 'info');
                document.getElementById(tipo + '_nombre').focus();
            } else {
                document.getElementById(tipo + '_dni').value = data.numero_documento || '';
                document.getElementById(tipo + '_nombre').value = data.nombre || '';
                document.getElementById(tipo + '_apellido').value = data.apellido || '';
                document.getElementById(tipo + '_telefono').value = data.telefono || '';
                document.getElementById(tipo + '_email').value = data.email || '';
                if (typeof mostrarToast === 'function') mostrarToast('✅ Cliente encontrado', 'success');
            }
        })
        .catch(err => {
            if (typeof cerrarLoading === 'function') cerrarLoading();
            else if (typeof Swal !== 'undefined') Swal.close();
            if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error');
        });
}

function calcularComision() {
    const monto = parseFloat(document.getElementById('monto').value) || 0;
    const porcentaje = <?php echo $comision_porcentaje; ?>;
    const comision = monto * (porcentaje / 100);
    const total = monto + comision;
    
    document.getElementById('comision_display').textContent = comision.toFixed(2);
    document.getElementById('comision_hidden').value = comision.toFixed(2);
    document.getElementById('resumen_monto').textContent = 'S/ ' + monto.toFixed(2);
    document.getElementById('resumen_comision').textContent = 'S/ ' + comision.toFixed(2);
    document.getElementById('resumen_total').textContent = 'S/ ' + total.toFixed(2);
}

document.getElementById('formGiro').addEventListener('submit', function(e) {
    e.preventDefault();
    const formRef = this;
    
    // Validar campos
    let valido = true;
    this.querySelectorAll('input[required], select[required]').forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valido = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!valido) {
        if (typeof mostrarErroresValidacion === 'function') {
            mostrarErroresValidacion(['Complete todos los campos obligatorios.'], 'Campos incompletos');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Campos incompletos', text: 'Revise los campos marcados en rojo.' });
        }
        return false;
    }
    
    const remitente = document.getElementById('remitente_nombre').value;
    const destinatario = document.getElementById('destinatario_nombre').value;
    const monto = parseFloat(document.getElementById('monto').value) || 0;
    const comision = parseFloat(document.getElementById('comision_hidden').value) || 0;
    const lugar_destino = document.querySelector('[name="lugar_destino"]').value;
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Registrar giro?',
            html: `
                <div class="text-start">
                    <p><strong>Remitente:</strong> ${remitente}</p>
                    <p><strong>Destinatario:</strong> ${destinatario}</p>
                    <p><strong>Destino:</strong> ${lugar_destino}</p>
                    <p><strong>Monto:</strong> S/ ${monto.toFixed(2)}</p>
                    <p><strong>Comisión:</strong> <span class="text-warning">S/ ${comision.toFixed(2)}</span></p>
                    <hr>
                    <p><strong>Total:</strong> <span class="text-success">S/ ${(monto + comision).toFixed(2)}</span></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, registrar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Registrando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                formRef.submit();
            }
        });
    } else {
        formRef.submit();
    }
});
</script>

<style>
    #formGiro .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    #formGiro .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    }
</style>
