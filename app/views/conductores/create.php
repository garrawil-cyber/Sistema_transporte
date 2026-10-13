<?php
$prev = $data ?? [];
$usuario_preseleccionado = (int)($_GET['usuario_id'] ?? ($prev['usuario_id'] ?? 0));
$modo_default = $usuario_preseleccionado > 0 ? 'existente' : 'nuevo';
$categorias = $categorias_validas ?? [];
?>

<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-info bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-user-plus text-info fa-lg"></i>
                </span>
                <span>Nuevo Conductor</span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">
                <i class="fas fa-info-circle me-1"></i>Registra un conductor con sus datos personales, licencia y contrato
            </p>
        </div>
        <a href="<?= BASE_URL ?>conductores" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Errores:</h6>
            <ul class="mb-0">
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>conductores/store"
          enctype="multipart/form-data" id="formConductor" novalidate>

        <!-- FOTO -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div id="previewFoto" class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center"
                         style="width: 90px; height: 90px; flex-shrink: 0; overflow: hidden;">
                        <i class="fas fa-user-tie fa-2x text-secondary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <label for="foto" class="form-label fw-bold small mb-1">
                            <i class="fas fa-camera me-1"></i>Foto del Conductor (opcional)
                        </label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">
                            JPG, PNG o WEBP · Máx 2 MB · Recomendado 400×400 px
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECCIÓN USUARIO (2 modos) -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pb-0">
                <ul class="nav nav-tabs nav-fill" id="tabsModoUsuario" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $modo_default === 'existente' ? 'active' : '' ?>"
                                id="tab-existente" data-bs-toggle="tab" data-bs-target="#panel-existente"
                                type="button" role="tab">
                            <i class="fas fa-search me-1"></i>Elegir usuario existente
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $modo_default === 'nuevo' ? 'active' : '' ?>"
                                id="tab-nuevo" data-bs-toggle="tab" data-bs-target="#panel-nuevo"
                                type="button" role="tab">
                            <i class="fas fa-user-plus me-1"></i>Crear usuario nuevo
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <input type="hidden" name="modo_usuario" id="modoUsuario" value="<?= $modo_default ?>">

                <div class="tab-content" id="contenidoModoUsuario">

                    <!-- MODO A: USUARIO EXISTENTE -->
                    <div class="tab-pane fade <?= $modo_default === 'existente' ? 'show active' : '' ?>"
                         id="panel-existente" role="tabpanel">
                        <?php if (empty($pendientes)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                No hay usuarios pendientes. Todos los usuarios con rol
                                <strong>CONDUCTOR</strong> ya tienen perfil.
                                <a href="#" onclick="document.getElementById('tab-nuevo').click(); return false;"
                                   class="alert-link">Crear usuario nuevo</a>
                            </div>
                        <?php else: ?>
                            <label class="form-label fw-bold small">
                                Usuario Conductor <span class="text-danger">*</span>
                            </label>
                            <select name="usuario_id" id="usuario_id" class="form-select form-select-lg">
                                <option value="">— Seleccione un usuario —</option>
                                <?php foreach ($pendientes as $p): ?>
                                    <option value="<?= $p['usuario_id'] ?>"
                                            <?= $usuario_preseleccionado == $p['usuario_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>
                                        — <?= htmlspecialchars($p['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Solo se muestran usuarios con rol CONDUCTOR sin perfil asignado.
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- MODO B: CREAR USUARIO NUEVO -->
                    <div class="tab-pane fade <?= $modo_default === 'nuevo' ? 'show active' : '' ?>"
                         id="panel-nuevo" role="tabpanel">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nombres <span class="text-danger">*</span></label>
                                <input type="text" name="usuario_nombre" id="usuario_nombre" class="form-control"
                                       value="<?= htmlspecialchars($prev['usuario_nombre'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" name="usuario_apellido" id="usuario_apellido" class="form-control"
                                       value="<?= htmlspecialchars($prev['usuario_apellido'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="usuario_email" id="usuario_email" class="form-control"
                                           value="<?= htmlspecialchars($prev['usuario_email'] ?? '') ?>">
                                </div>
                                <small class="text-muted">Será usado para login y notificaciones</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="text" name="usuario_telefono" id="usuario_telefono" class="form-control"
                                           value="<?= htmlspecialchars($prev['usuario_telefono'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">
                                    Contraseña temporal <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="text" name="usuario_password" id="usuario_password"
                                           class="form-control font-monospace" value="conductor123">
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="generarPassword()" title="Generar nueva">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Se recomienda cambiarla al primer login</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IDENTIFICACIÓN -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-primary text-white border-0">
                <i class="fas fa-id-card me-2"></i><strong>Identificación Personal</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">
                            Tipo Documento <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_documento" id="tipo_documento" class="form-select" required>
                            <option value="DNI" <?= ($prev['tipo_documento'] ?? 'DNI') === 'DNI' ? 'selected' : '' ?>>DNI</option>
                            <option value="CE" <?= ($prev['tipo_documento'] ?? '') === 'CE' ? 'selected' : '' ?>>Carné de Extranjería</option>
                            <option value="PASAPORTE" <?= ($prev['tipo_documento'] ?? '') === 'PASAPORTE' ? 'selected' : '' ?>>Pasaporte</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold small">
                            N° Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" name="numero_documento" id="numero_documento"
                                   class="form-control font-monospace"
                                   placeholder="Ej: 12345678"
                                   value="<?= htmlspecialchars($prev['numero_documento'] ?? '') ?>"
                                   required autocomplete="off">
                        </div>
                        <small class="text-muted" id="ayuda_documento">
                            Exactamente 8 dígitos numéricos
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- LICENCIA -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white border-0">
                <i class="fas fa-id-card me-2"></i><strong>Datos de la Licencia</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">N° Licencia <span class="text-danger">*</span></label>
                        <input type="text" name="numero_licencia" id="numero_licencia"
                               class="form-control font-monospace" placeholder="Ej: Q12345678"
                               style="text-transform: uppercase;"
                               value="<?= htmlspecialchars($prev['numero_licencia'] ?? '') ?>">
                        <small class="text-muted">Solo letras, números y guiones</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria_licencia" id="categoria_licencia" class="form-select">
                            <option value="">— Seleccione —</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"
                                        <?= ($prev['categoria_licencia'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_vencimiento_licencia" id="fecha_vencimiento_licencia"
                               class="form-control" min="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($prev['fecha_vencimiento_licencia'] ?? '') ?>">
                        <small class="text-muted">Debe ser futura</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Fecha de Emisión</label>
                        <input type="date" name="fecha_emision_licencia" id="fecha_emision_licencia"
                               class="form-control"
                               value="<?= htmlspecialchars($prev['fecha_emision_licencia'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                               max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                               value="<?= htmlspecialchars($prev['fecha_nacimiento'] ?? '') ?>">
                        <small class="text-muted">Mínimo 18 años</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTACTO -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white border-0">
                <i class="fas fa-address-book me-2"></i><strong>Contacto y Emergencia</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label fw-bold small">Dirección</label>
                        <input type="text" name="direccion" class="form-control"
                               placeholder="Ej: Av. Principal 123, Chiclayo"
                               value="<?= htmlspecialchars($prev['direccion'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Contacto de Emergencia</label>
                        <input type="text" name="contacto_emergencia_nombre" id="contacto_emergencia_nombre"
                               class="form-control" placeholder="Nombre completo (solo letras)"
                               value="<?= htmlspecialchars($prev['contacto_emergencia_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Teléfono de Emergencia</label>
                        <input type="text" name="contacto_emergencia_telefono" id="contacto_emergencia_telefono"
                               class="form-control" placeholder="Ej: 987654321"
                               value="<?= htmlspecialchars($prev['contacto_emergencia_telefono'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- LABORAL -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white border-0">
                <i class="fas fa-money-bill-wave me-2"></i><strong>Datos Laborales</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Fecha de Ingreso</label>
                        <input type="date" name="fecha_ingreso" class="form-control"
                               value="<?= htmlspecialchars($prev['fecha_ingreso'] ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Sueldo Base <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" min="0" name="sueldo_base" id="sueldo_base"
                                   class="form-control text-end fw-bold"
                                   value="<?= htmlspecialchars($prev['sueldo_base'] ?? '0.00') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Comisión (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100"
                                   name="comision_porcentaje" id="comision_porcentaje"
                                   class="form-control text-end"
                                   value="<?= htmlspecialchars($prev['comision_porcentaje'] ?? '0.00') ?>">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="ACTIVO"     <?= ($prev['estado'] ?? 'ACTIVO') === 'ACTIVO'     ? 'selected' : '' ?>>✅ Activo</option>
                            <option value="VACACIONES" <?= ($prev['estado'] ?? '') === 'VACACIONES' ? 'selected' : '' ?>>🏖️ Vacaciones</option>
                            <option value="LICENCIA"   <?= ($prev['estado'] ?? '') === 'LICENCIA'   ? 'selected' : '' ?>>📋 Con Licencia</option>
                            <option value="INACTIVO"   <?= ($prev['estado'] ?? '') === 'INACTIVO'   ? 'selected' : '' ?>>⏸️ Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"
                                  placeholder="Notas internas..."><?= htmlspecialchars($prev['observaciones'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Los campos con <span class="text-danger">*</span> son obligatorios
                    </small>
                    <div class="d-flex gap-2">
                        <a href="<?= BASE_URL ?>conductores" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="btnGuardarConductor">
                            <i class="fas fa-save me-1"></i>Guardar Conductor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<a href="#" class="fab-mobile"
   onclick="event.preventDefault(); document.getElementById('formConductor').requestSubmit();"
   title="Guardar Conductor">
    <i class="fas fa-save"></i>
</a>

<script>
// ============================================================
// SCRIPT CONDUCTORES/CREATE — v2
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------------------------------------
    // 1. APLICAR HELPERS DE INPUTS
    // ------------------------------------------------------------
    if (typeof aplicarPreviewFoto === 'function') {
        aplicarPreviewFoto('foto', 'previewFoto', { maxMB: 2 });
    }

    // Documento (DNI/CE/Pasaporte) — con validación cruzada al select
    if (typeof aplicarValidacionDocumento === 'function') {
        aplicarValidacionDocumento('numero_documento', 'tipo_documento');
    }

    // Inputs de texto
    if (typeof aplicarSoloLetras === 'function') {
        aplicarSoloLetras('usuario_nombre', 100);
        aplicarSoloLetras('usuario_apellido', 100);
        aplicarSoloLetras('contacto_emergencia_nombre', 150);
    }
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('contacto_emergencia_telefono', 9);
        aplicarTelefono('usuario_telefono', 9);
    }
    if (typeof aplicarAlfanumerico === 'function') {
        aplicarAlfanumerico('numero_licencia', 20, true);
    }

    // Inputs numéricos con rango
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('sueldo_base', 0, 99999.99, 2);
        aplicarRangoNumerico('comision_porcentaje', 0, 100, 2);
    }

    // Email
    if (typeof aplicarValidacionEmail === 'function') {
        aplicarValidacionEmail('usuario_email');
    }

    // Fechas
    if (typeof aplicarOrdenFechas === 'function') {
        aplicarOrdenFechas('fecha_emision_licencia', 'fecha_vencimiento_licencia');
    }
    if (typeof aplicarFechaFutura === 'function') {
        aplicarFechaFutura('fecha_vencimiento_licencia');
    }

    // ------------------------------------------------------------
    // 2. SINCRONIZAR MODOS AL CAMBIAR TABS
    // ------------------------------------------------------------
    const modoInput = document.getElementById('modoUsuario');
    const panelExistente = document.getElementById('panel-existente');
    const panelNuevo = document.getElementById('panel-nuevo');

    function sincronizarModo(modo) {
        modoInput.value = modo;
        if (modo === 'existente') {
            panelNuevo.querySelectorAll('input, select').forEach(el => el.disabled = true);
            panelExistente.querySelectorAll('input, select').forEach(el => el.disabled = false);
        } else {
            panelExistente.querySelectorAll('input, select').forEach(el => el.disabled = true);
            panelNuevo.querySelectorAll('input, select').forEach(el => el.disabled = false);
        }
    }

    document.getElementById('tab-existente').addEventListener('shown.bs.tab', function() {
        sincronizarModo('existente');
    });
    document.getElementById('tab-nuevo').addEventListener('shown.bs.tab', function() {
        sincronizarModo('nuevo');
    });

    sincronizarModo(modoInput.value);

    // ------------------------------------------------------------
    // 3. BOTÓN GENERAR PASSWORD
    // ------------------------------------------------------------
    window.generarPassword = function() {
        if (typeof generarPasswordEnInput === 'function') {
            generarPasswordEnInput('usuario_password', 10);
        }
    };

    // ------------------------------------------------------------
    // 4. TEXTO DE AYUDA DINÁMICO DEL DOCUMENTO
    // ------------------------------------------------------------
    const selectTipoDoc = document.getElementById('tipo_documento');
    const ayudaDoc = document.getElementById('ayuda_documento');
    if (selectTipoDoc && ayudaDoc) {
        const textos = {
            'DNI':       'Exactamente 8 dígitos numéricos',
            'CE':        'Entre 9 y 12 caracteres alfanuméricos',
            'PASAPORTE': 'Entre 6 y 12 caracteres alfanuméricos'
        };
        selectTipoDoc.addEventListener('change', function() {
            ayudaDoc.textContent = textos[this.value] || textos.DNI;
        });
        ayudaDoc.textContent = textos[selectTipoDoc.value] || textos.DNI;
    }

    // ------------------------------------------------------------
    // 5. VALIDACIÓN CRUZADA: NACIMIENTO Y EDAD (18-75)
    // ------------------------------------------------------------
    const fechaNac = document.getElementById('fecha_nacimiento');
    if (fechaNac) {
        fechaNac.addEventListener('blur', function() {
            if (!this.value) return;
            const hoy = new Date();
            const [anio, mes, dia] = this.value.split('-').map(Number);
            const nac = new Date(anio, mes - 1, dia);
            let edad = hoy.getFullYear() - nac.getFullYear();
            const m = hoy.getMonth() - nac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) edad--;

            this.classList.remove('is-invalid', 'is-valid');
            if (edad < 18) {
                this.classList.add('is-invalid');
                mostrarToast('El conductor debe tener al menos 18 años', 'warning');
            } else if (edad > 75) {
                this.classList.add('is-invalid');
                mostrarToast('La edad máxima permitida es 75 años', 'warning');
            } else {
                this.classList.add('is-valid');
            }
        });
    }

    // ------------------------------------------------------------
    // 6. SUBMIT UNIFICADO CON VALIDACIÓN COMPLETA
    // ------------------------------------------------------------
    const form = document.getElementById('formConductor');
    const btnGuardar = document.getElementById('btnGuardarConductor');
    let enviando = false;

    form.addEventListener('submit', function(e) {
        if (enviando) return true;
        e.preventDefault();

        const modo = modoInput.value;
        const errores = [];

        // --- Validar según modo ---
        if (modo === 'existente') {
            const usuarioId = document.getElementById('usuario_id');
            if (!usuarioId.value) errores.push('Seleccione un usuario existente');
        } else {
            const nombre = document.getElementById('usuario_nombre');
            const apellido = document.getElementById('usuario_apellido');
            const email = document.getElementById('usuario_email');
            const password = document.getElementById('usuario_password');

            if (!nombre.value.trim()) errores.push('El nombre del usuario es obligatorio');
            else if (!apellido.value.trim()) errores.push('El apellido del usuario es obligatorio');
            else if (!email.value.trim()) errores.push('El email es obligatorio');
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) errores.push('El email no es válido');
            else if (!password.value.trim()) errores.push('La contraseña es obligatoria');
            else if (password.value.length < 6) errores.push('La contraseña debe tener al menos 6 caracteres');
        }

        // --- Validar documento ---
        const numDoc = document.getElementById('numero_documento');
        if (numDoc && typeof numDoc.validarDocumento === 'function') {
            if (!numDoc.validarDocumento()) {
                errores.push('El número de documento es inválido o está vacío');
            }
        }

        // --- Validar licencia ---
        const licencia = document.getElementById('numero_licencia');
        const categoria = document.getElementById('categoria_licencia');
        const emision = document.getElementById('fecha_emision_licencia');
        const vencimiento = document.getElementById('fecha_vencimiento_licencia');

        if (!licencia.value.trim()) {
            errores.push('El número de licencia es obligatorio');
        } else if (!/^[A-Z0-9\-]{6,20}$/.test(licencia.value.trim())) {
            errores.push('El número de licencia debe tener entre 6 y 20 caracteres alfanuméricos');
        }

        if (!categoria.value) errores.push('Seleccione una categoría de licencia');

        if (!vencimiento.value) {
            errores.push('La fecha de vencimiento es obligatoria');
        } else if (new Date(vencimiento.value) < new Date().setHours(0,0,0,0)) {
            errores.push('La fecha de vencimiento no puede ser anterior a hoy');
        }

        if (emision.value && vencimiento.value) {
            if (new Date(vencimiento.value) <= new Date(emision.value)) {
                errores.push('La fecha de vencimiento debe ser posterior a la de emisión');
            }
        }

        // --- Validar sueldo ---
        const sueldo = document.getElementById('sueldo_base');
        const sueldoVal = parseFloat(sueldo.value);
        if (sueldo.value === '' || isNaN(sueldoVal) || sueldoVal < 0) {
            errores.push('El sueldo base debe ser un número mayor o igual a 0');
        }

        // --- Validar comisión ---
        const comision = document.getElementById('comision_porcentaje');
        const comisionVal = parseFloat(comision.value);
        if (comision.value !== '' && (isNaN(comisionVal) || comisionVal < 0 || comisionVal > 100)) {
            errores.push('La comisión debe estar entre 0 y 100');
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

        // --- Confirmar y enviar ---
        const resumenHtml = `
            <div class="text-start small">
                <p class="mb-1"><strong>Modo:</strong> ${modo === 'existente' ? 'Usuario existente' : 'Usuario nuevo'}</p>
                <p class="mb-1"><strong>Documento:</strong> ${numDoc.value}</p>
                <p class="mb-1"><strong>Licencia:</strong> ${licencia.value} (${categoria.value})</p>
                <p class="mb-1"><strong>Vence:</strong> ${vencimiento.value}</p>
                <p class="mb-0"><strong>Sueldo:</strong> S/ ${sueldoVal.toFixed(2)}</p>
            </div>
        `;

        function ejecutarEnvio() {
            enviando = true;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Guardando...';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Guardando...',
                    html: 'Registrando el conductor',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
            form.submit();
        }

        if (typeof confirmarPersonalizado === 'function') {
            confirmarPersonalizado(
                '¿Guardar conductor?',
                resumenHtml,
                '<i class="fas fa-save me-1"></i>Sí, guardar',
                '#198754',
                ejecutarEnvio
            );
        } else if (confirm('¿Guardar este conductor?')) {
            ejecutarEnvio();
        }

        return false;
    });
});
</script>