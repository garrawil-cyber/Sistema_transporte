<!-- Vista: encomiendas/create.php -->
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-warning bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-box text-warning fa-lg"></i>
                </span>
                <span><?php echo $titulo; ?></span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">Registra una nueva encomienda con remitente, destinatario y ruta</p>
        </div>
        <a href="<?php echo BASE_URL; ?>encomiendas" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <!-- ALERTA DE ERRORES PHP -->
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6>
            <ul class="mb-0 ps-3">
                <?php foreach($errores as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

        <!-- KPIs MODERNOS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Encomiendas Hoy</div>
                        <div class="kpi-value"><?php echo $stats['total_hoy'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">En Bodega</div>
                        <div class="kpi-value"><?php echo $stats['en_bodega'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">En Tránsito</div>
                        <div class="kpi-value"><?php echo $stats['en_transito'] ?? 0; ?></div>
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
                        <div class="kpi-label text-muted">Entregadas</div>
                        <div class="kpi-value"><?php echo $stats['entregadas'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Ingresos del día (opcional) -->
<div class="alert alert-success border-0 shadow-sm d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <i class="fas fa-coins fa-lg"></i>
        <span class="fw-bold">Ingresos por encomiendas hoy:</span>
    </div>
    <span class="fs-4 fw-bold">S/ <?php echo number_format($stats['ingresos_hoy'] ?? 0, 2); ?></span>
</div>

    <!-- FORMULARIO CON PESTAÑAS -->
    <form method="POST" action="<?php echo BASE_URL; ?>encomiendas/create" id="formEncomienda" novalidate>
        <input type="hidden" name="action" value="guardar">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="encomiendaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="remitente-tab" data-bs-toggle="tab" data-bs-target="#remitente" type="button" role="tab">
                            <i class="fas fa-user me-2"></i>Remitente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="destinatario-tab" data-bs-toggle="tab" data-bs-target="#destinatario" type="button" role="tab">
                            <i class="fas fa-user-check me-2"></i>Destinatario
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="detalles-tab" data-bs-toggle="tab" data-bs-target="#detalles" type="button" role="tab">
                            <i class="fas fa-box-open me-2"></i>Detalles del Envío
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="encomiendaTabsContent">

                    <!-- TAB 1: REMITENTE -->
                    <div class="tab-pane fade show active" id="remitente" role="tabpanel">
                        <div class="row g-3">
                            <!-- Búsqueda DNI -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small"><i class="fas fa-search me-1"></i>Buscar por DNI</label>
                                <div class="input-group">
                                    <input type="text" id="remitente_dni_buscar" class="form-control" 
                                           placeholder="Ingrese DNI y presione Enter" maxlength="8" inputmode="numeric" autocomplete="off">
                                    <button type="button" class="btn btn-primary" onclick="buscarCliente('remitente')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Si existe, se autocompletará</small>
                            </div>

                            <!-- DNI -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">DNI / Documento <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="remitente_dni" id="remitente_dni" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($remitente['numero_documento'] ?? ''); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Nombres y Apellidos -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombres <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="remitente_nombre" id="remitente_nombre" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($remitente['nombre'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese solo letras (mínimo 3).</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Apellidos <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="remitente_apellido" id="remitente_apellido" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($remitente['apellido'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese solo letras (mínimo 3).</div>
                                </div>
                            </div>

                            <!-- Teléfono y Email -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="remitente_telefono" id="remitente_telefono" 
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($remitente['telefono'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese 9 dígitos numéricos.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="remitente_email" id="remitente_email" 
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($remitente['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese un correo válido.</div>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="col-12">
                                <label class="form-label fw-bold small">Dirección</label>
                                <input type="text" name="remitente_direccion" id="remitente_direccion" 
                                       class="form-control" maxlength="150"
                                       value="<?php echo htmlspecialchars($remitente['direccion'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: DESTINATARIO -->
                    <div class="tab-pane fade" id="destinatario" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small"><i class="fas fa-search me-1"></i>Buscar por DNI</label>
                                <div class="input-group">
                                    <input type="text" id="destinatario_dni_buscar" class="form-control" 
                                           placeholder="Ingrese DNI y presione Enter" maxlength="8" inputmode="numeric" autocomplete="off">
                                    <button type="button" class="btn btn-success" onclick="buscarCliente('destinatario')">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Si existe, se autocompletará</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">DNI / Documento <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" name="destinatario_dni" id="destinatario_dni" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($destinatario['numero_documento'] ?? ''); ?>">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombres <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="destinatario_nombre" id="destinatario_nombre" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($destinatario['nombre'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese solo letras (mínimo 3).</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Apellidos <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="text" name="destinatario_apellido" id="destinatario_apellido" 
                                           class="form-control" required
                                           value="<?php echo htmlspecialchars($destinatario['apellido'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese solo letras (mínimo 3).</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" name="destinatario_telefono" id="destinatario_telefono" 
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($destinatario['telefono'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese 9 dígitos numéricos.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email</label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="destinatario_email" id="destinatario_email" 
                                           class="form-control"
                                           value="<?php echo htmlspecialchars($destinatario['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Ingrese un correo válido.</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Dirección</label>
                                <input type="text" name="destinatario_direccion" id="destinatario_direccion" 
                                       class="form-control" maxlength="150"
                                       value="<?php echo htmlspecialchars($destinatario['direccion'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: DETALLES -->
                    <div class="tab-pane fade" id="detalles" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small"><i class="fas fa-route me-1"></i>Viaje / Ruta <span class="text-danger">*</span></label>
                                <select name="viaje_id" id="viaje_id" class="form-select" required>
                                    <option value="">-- Seleccione un viaje --</option>
                                    <?php foreach($viajes as $v): ?>
                                        <option value="<?php echo $v['id']; ?>">
                                            <?php echo htmlspecialchars($v['origen'] . ' → ' . $v['destino']); ?> 
                                            (<?php echo date('d/m/Y H:i', strtotime($v['fecha_salida'])); ?>) 
                                            - <?php echo $v['placa']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccione un viaje.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small"><i class="fas fa-weight-hanging me-1"></i>Peso (kg) <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <input type="number" step="0.01" min="0.1" name="peso_kg" id="peso_kg" 
                                           class="form-control" required oninput="calcularCosto()">
                                    <span class="input-group-text">kg</span>
                                    <div class="invalid-feedback">Ingrese un peso mayor a 0.</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small"><i class="fas fa-cube me-1"></i>Dimensiones</label>
                                <input type="text" name="dimensiones" id="dimensiones" class="form-control" 
                                       placeholder="Ej: 30x40x20 cm" maxlength="50">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small"><i class="fas fa-money-bill-wave me-1"></i>Costo de Envío (S/) <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text bg-success text-white">S/</span>
                                    <input type="number" step="0.01" min="0.1" name="costo_envio" id="costo_envio" 
                                           class="form-control" required>
                                    <div class="invalid-feedback">Ingrese un costo mayor a 0.</div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small"><i class="fas fa-dollar-sign me-1"></i>Valor Declarado</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" min="0" name="valor_declarado" id="valor_declarado" 
                                           class="form-control" value="0">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small"><i class="fas fa-calculator me-1"></i>Sugerencia</label>
                                <button type="button" class="btn btn-outline-info w-100" onclick="sugerirCosto()">
                                    <i class="fas fa-magic me-1"></i>Calcular
                                </button>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small"><i class="fas fa-comment me-1"></i>Descripción del contenido</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="2" maxlength="255"
                                          placeholder="Ej: Ropa, documentos, electrodomésticos..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?php echo BASE_URL; ?>encomiendas" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save me-1"></i>Registrar Encomienda
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- FAB Móvil -->
<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formEncomienda').requestSubmit();" title="Registrar Encomienda">
    <i class="fas fa-save"></i>
</a>

<!-- ============================================================
     VALIDACIONES USANDO ARCHIVOS EXTERNOS
     ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // 1. VALIDACIÓN DE DOCUMENTOS (validar-documento.js)
    //    Se aplica al input de DNI (sin select, siempre DNI)
    // ============================================================
    if (typeof aplicarValidacionDocumento === 'function') {
        // Remitente: el DNI real (8 dígitos)
        aplicarValidacionDocumento('remitente_dni');
        aplicarValidacionDocumento('remitente_dni_buscar');
        // Destinatario
        aplicarValidacionDocumento('destinatario_dni');
        aplicarValidacionDocumento('destinatario_dni_buscar');
    }

    // ============================================================
    // 2. SOLO LETRAS (form-helpers.js)
    //    Nombres y Apellidos
    // ============================================================
    if (typeof aplicarSoloLetras === 'function') {
        ['remitente_nombre', 'remitente_apellido',
         'destinatario_nombre', 'destinatario_apellido'].forEach(id => {
            aplicarSoloLetras(id, 100);
        });
    }

    // ============================================================
    // 3. TELÉFONO (form-helpers.js) - 9 dígitos
    // ============================================================
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('remitente_telefono', 9);
        aplicarTelefono('destinatario_telefono', 9);
    }

    // ============================================================
    // 4. EMAIL (form-helpers.js)
    // ============================================================
    if (typeof aplicarValidacionEmail === 'function') {
        aplicarValidacionEmail('remitente_email');
        aplicarValidacionEmail('destinatario_email');
    }

    // ============================================================
    // 5. RANGO NUMÉRICO (form-helpers.js)
    //    Peso: mín 0.1 kg | Costo: mín 0.1 S/ | Valor: mín 0 S/
    // ============================================================
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('peso_kg', 0.1, 9999, 2);
        aplicarRangoNumerico('costo_envio', 0.1, 99999, 2);
        aplicarRangoNumerico('valor_declarado', 0, 99999, 2);
    }

    // ============================================================
    // 6. BÚSQUEDA POR ENTER EN DNI
    // ============================================================
    ['remitente_dni_buscar', 'destinatario_dni_buscar'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarCliente(id.includes('remitente') ? 'remitente' : 'destinatario');
            }
        });
    });

    // ============================================================
    // 7. VALIDACIÓN AL ENVIAR
    // ============================================================
    const form = document.getElementById('formEncomienda');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar todos los requeridos
        let todosValidos = true;
        const errores = [];

        this.querySelectorAll('input[required], select[required]').forEach(function (input) {
            const valor = input.value.trim();
            if (!valor) {
                input.classList.add('is-invalid');
                todosValidos = false;
                errores.push(input.previousElementSibling?.textContent?.trim() || input.name);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!todosValidos) {
            // Encontrar la primera pestaña con error y activarla
            const primerError = this.querySelector('.is-invalid');
            if (primerError) {
                const tabPane = primerError.closest('.tab-pane');
                if (tabPane) {
                    const tabButton = document.querySelector(`[data-bs-target="#${tabPane.id}"]`);
                    if (tabButton && typeof bootstrap !== 'undefined') {
                        new bootstrap.Tab(tabButton).show();
                    }
                }
                primerError.focus();
            }

            // Usar mostrarErroresValidacion (form-helpers.js) o SweetAlert2 directamente
            if (typeof mostrarErroresValidacion === 'function') {
                mostrarErroresValidacion(['Complete todos los campos obligatorios.'], 'Campos incompletos');
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos incompletos',
                    text: 'Revise los campos marcados en rojo.',
                    confirmButtonColor: '#dc3545'
                });
            }
            return false;
        }

        // Confirmar con SweetAlert2
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Registrar encomienda?',
                html: `
                    <div class="text-start small">
                        <p class="mb-1"><strong>Remitente:</strong> ${document.getElementById('remitente_nombre').value} ${document.getElementById('remitente_apellido').value}</p>
                        <p class="mb-1"><strong>Destinatario:</strong> ${document.getElementById('destinatario_nombre').value} ${document.getElementById('destinatario_apellido').value}</p>
                        <p class="mb-1"><strong>Peso:</strong> ${document.getElementById('peso_kg').value} kg</p>
                        <p class="mb-0"><strong>Costo:</strong> <span class="text-success fw-bold">S/ ${document.getElementById('costo_envio').value}</span></p>
                    </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, registrar',
                cancelButtonText: 'Revisar',
                confirmButtonColor: '#198754'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Registrando...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        } else {
            form.submit();
        }
    });
});

// ============================================================
// BÚSQUEDA DE CLIENTE POR DNI (AJAX)
// Usa mostrarToast (custom.js) y Swal
// ============================================================
function buscarCliente(tipo) {
    const dniInput = document.getElementById(tipo + '_dni_buscar');
    if (!dniInput) return;
    const dni = dniInput.value.trim();

    if (dni.length !== 8) {
        if (typeof mostrarToast === 'function') {
            mostrarToast('Ingrese un DNI válido (8 dígitos)', 'warning');
        }
        dniInput.focus();
        return;
    }

    if (typeof mostrarLoading === 'function') {
        mostrarLoading('Buscando cliente...');
    } else if (typeof Swal !== 'undefined') {
        Swal.fire({ title: 'Buscando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    }

    fetch('<?php echo BASE_URL; ?>clientes/buscarPorDni?dni=' + encodeURIComponent(dni))
        .then(r => r.json())
        .then(data => {
            if (typeof cerrarLoading === 'function') cerrarLoading();
            else if (typeof Swal !== 'undefined') Swal.close();

            if (data.error || !data.numero_documento) {
                document.getElementById(tipo + '_dni').value = dni;
                ['nombre', 'apellido', 'telefono', 'email', 'direccion'].forEach(c => {
                    const el = document.getElementById(tipo + '_' + c);
                    if (el) el.value = '';
                });
                if (typeof mostrarToast === 'function') {
                    mostrarToast('Cliente no encontrado. Complete manualmente.', 'info');
                }
                document.getElementById(tipo + '_nombre').focus();
            } else {
                document.getElementById(tipo + '_dni').value = data.numero_documento || '';
                document.getElementById(tipo + '_nombre').value = data.nombre || '';
                document.getElementById(tipo + '_apellido').value = data.apellido || '';
                document.getElementById(tipo + '_telefono').value = data.telefono || '';
                document.getElementById(tipo + '_email').value = data.email || '';
                document.getElementById(tipo + '_direccion').value = data.direccion || '';
                if (typeof mostrarToast === 'function') {
                    mostrarToast('Cliente encontrado: ' + data.nombre + ' ' + data.apellido, 'success');
                }
            }
        })
        .catch(err => {
            if (typeof cerrarLoading === 'function') cerrarLoading();
            else if (typeof Swal !== 'undefined') Swal.close();
            if (typeof mostrarToast === 'function') {
                mostrarToast('Error de conexión', 'error');
            }
        });
}

// ============================================================
// COSTO SUGERIDO (usa SweetAlert2)
// ============================================================
function calcularCosto() {
    const peso = parseFloat(document.getElementById('peso_kg').value) || 0;
    const costoInput = document.getElementById('costo_envio');
    if (!costoInput.value || costoInput.value == 0) {
        costoInput.placeholder = 'Sugerido: S/ ' + (peso * 5).toFixed(2);
    }
}

function sugerirCosto() {
    const peso = parseFloat(document.getElementById('peso_kg').value) || 0;
    if (peso <= 0) {
        if (typeof mostrarToast === 'function') mostrarToast('Ingrese primero el peso', 'warning');
        return;
    }
    const sugerido = (peso * 5).toFixed(2);
    if (typeof Swal === 'undefined') return;

    Swal.fire({
        title: '¿Aplicar costo sugerido?',
        html: `Peso: <strong>${peso} kg</strong><br>Tarifa: <strong>S/ 5.00/kg</strong><br>Total: <strong class="text-success">S/ ${sugerido}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aplicar',
        cancelButtonText: 'No',
        confirmButtonColor: '#198754'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById('costo_envio').value = sugerido;
            if (typeof mostrarToast === 'function') {
                mostrarToast('Costo aplicado: S/ ' + sugerido, 'success');
            }
        }
    });
}
</script>