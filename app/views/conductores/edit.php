<?php
$cat_actual = $conductor['categoria_licencia'] ?? '';
$categorias = $categorias_validas ?? [];

// ✅ FIX: iniciales null-safe + UPLOAD_PATH verificado
$nombre_safe   = trim((string)($conductor['nombre']   ?? ''));
$apellido_safe = trim((string)($conductor['apellido'] ?? ''));
$iniciales = strtoupper(mb_substr($nombre_safe, 0, 1) . mb_substr($apellido_safe, 0, 1));
if ($iniciales === '') $iniciales = '?';

$tiene_foto = !empty($conductor['foto'])
    && defined('UPLOAD_PATH')
    && file_exists(UPLOAD_PATH . 'conductores/' . $conductor['foto']);

$upload_url = defined('UPLOAD_URL') ? UPLOAD_URL : BASE_URL . 'uploads/';
?>

<div class="container-fluid px-3 px-md-4 py-3">

    <!-- HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div class="flex-grow-1">
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-warning bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-user-edit text-warning fa-lg"></i>
                </span>
                <span>Editar Conductor</span>
            </h1>
            <div class="d-flex flex-wrap align-items-center gap-2 text-muted small ms-5 ps-2">
                <span class="badge bg-dark font-monospace">#<?= (int)$conductor['id'] ?></span>
                <span><?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?></span>
                <span>·</span>
                <span><?= htmlspecialchars($conductor['email'] ?? '') ?></span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>conductores/show/<?= (int)$conductor['id'] ?>" class="btn btn-outline-info">
                <i class="fas fa-eye me-1"></i>Ver
            </a>
            <a href="<?= BASE_URL ?>conductores" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <form method="POST" action="<?= BASE_URL ?>conductores/update/<?= (int)$conductor['id'] ?>"
          enctype="multipart/form-data" id="formConductor" novalidate>

        <!-- FOTO -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div id="previewFoto"
                         class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center fw-bold text-secondary"
                         style="width: 90px; height: 90px; flex-shrink: 0; overflow: hidden; font-size: 1.8rem;">
                        <?php if ($tiene_foto): ?>
                            <img src="<?= $upload_url . 'conductores/' . htmlspecialchars($conductor['foto']) ?>"
                                 alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <?= htmlspecialchars($iniciales) ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1">
                        <label for="foto" class="form-label fw-bold small mb-1">
                            <i class="fas fa-camera me-1"></i>Cambiar foto
                        </label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-sm"
                               accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">
                            JPG, PNG o WEBP · Máx 2 MB · Dejar vacío para mantener la actual
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFO USUARIO -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-secondary text-white border-0">
                <i class="fas fa-user-circle me-2"></i><strong>Datos del Usuario</strong>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Nombre completo</label>
                        <input type="text" class="form-control bg-light"
                               value="<?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?>" readonly>
                        <small class="text-muted">
                            El Nombre y el Email se cambian desde
                            <a href="<?= BASE_URL ?>usuarios/edit/<?= (int)$conductor['usuario_id'] ?>">Usuarios</a>
                        </small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Email</label>
                        <input type="email" class="form-control bg-light"
                               value="<?= htmlspecialchars($conductor['email'] ?? '') ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone text-success"></i></span>
                            <input type="text" name="usuario_telefono" id="usuario_telefono"
                                   class="form-control"
                                   value="<?= htmlspecialchars($conductor['telefono'] ?? '') ?>">
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
                            <option value="DNI"       <?= ($conductor['tipo_documento'] ?? 'DNI') === 'DNI'       ? 'selected' : '' ?>>DNI</option>
                            <option value="CE"        <?= ($conductor['tipo_documento'] ?? '') === 'CE'           ? 'selected' : '' ?>>Carné de Extranjería</option>
                            <option value="PASAPORTE" <?= ($conductor['tipo_documento'] ?? '') === 'PASAPORTE'   ? 'selected' : '' ?>>Pasaporte</option>
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
                                   value="<?= htmlspecialchars($conductor['numero_documento'] ?? '') ?>"
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
                               class="form-control font-monospace"
                               style="text-transform: uppercase;"
                               value="<?= htmlspecialchars($conductor['numero_licencia'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Categoría <span class="text-danger">*</span></label>
                        <select name="categoria_licencia" id="categoria_licencia" class="form-select" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>"
                                        <?= $cat_actual === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" name="fecha_vencimiento_licencia" id="fecha_vencimiento_licencia"
                               class="form-control"
                               value="<?= htmlspecialchars($conductor['fecha_vencimiento_licencia'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Emisión</label>
                        <input type="date" name="fecha_emision_licencia" id="fecha_emision_licencia"
                               class="form-control"
                               value="<?= htmlspecialchars($conductor['fecha_emision_licencia'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control"
                               max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                               value="<?= htmlspecialchars($conductor['fecha_nacimiento'] ?? '') ?>">
                        <small class="text-muted">Entre 18 y 75 años</small>
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
                               value="<?= htmlspecialchars($conductor['direccion'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Contacto Emergencia</label>
                        <input type="text" name="contacto_emergencia_nombre" id="contacto_emergencia_nombre"
                               class="form-control"
                               value="<?= htmlspecialchars($conductor['contacto_emergencia_nombre'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Teléfono Emergencia</label>
                        <input type="text" name="contacto_emergencia_telefono" id="contacto_emergencia_telefono"
                               class="form-control"
                               value="<?= htmlspecialchars($conductor['contacto_emergencia_telefono'] ?? '') ?>">
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
                               value="<?= htmlspecialchars($conductor['fecha_ingreso'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Sueldo Base <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" min="0" name="sueldo_base" id="sueldo_base"
                                   class="form-control text-end fw-bold"
                                   value="<?= number_format((float)$conductor['sueldo_base'], 2, '.', '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Comisión (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100"
                                   name="comision_porcentaje" id="comision_porcentaje"
                                   class="form-control text-end"
                                   value="<?= number_format((float)$conductor['comision_porcentaje'], 2, '.', '') ?>">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="ACTIVO"     <?= ($conductor['estado'] ?? 'ACTIVO') === 'ACTIVO'     ? 'selected' : '' ?>>✅ Activo</option>
                            <option value="VACACIONES" <?= ($conductor['estado'] ?? '') === 'VACACIONES' ? 'selected' : '' ?>>🏖️ Vacaciones</option>
                            <option value="LICENCIA"   <?= ($conductor['estado'] ?? '') === 'LICENCIA'   ? 'selected' : '' ?>>📋 Con Licencia</option>
                            <option value="INACTIVO"   <?= ($conductor['estado'] ?? '') === 'INACTIVO'   ? 'selected' : '' ?>>⏸️ Inactivo</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold small">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"
                                  maxlength="500"><?= htmlspecialchars($conductor['observaciones'] ?? '') ?></textarea>
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
                        <button type="submit" class="btn btn-warning btn-lg" id="btnActualizar">
                            <i class="fas fa-save me-1"></i>Actualizar Conductor
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<a href="#" class="fab-mobile"
   onclick="event.preventDefault(); document.getElementById('formConductor').requestSubmit();"
   title="Actualizar">
    <i class="fas fa-save"></i>
</a>

<script>
// ============================================================
// SCRIPT CONDUCTORES/EDIT — v2
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------------------------------------
    // 1. APLICAR HELPERS
    // ------------------------------------------------------------
    if (typeof aplicarPreviewFoto === 'function') {
        aplicarPreviewFoto('foto', 'previewFoto', { maxMB: 2 });
    }
    if (typeof aplicarValidacionDocumento === 'function') {
        aplicarValidacionDocumento('numero_documento', 'tipo_documento');
    }
    if (typeof aplicarSoloLetras === 'function') {
        aplicarSoloLetras('contacto_emergencia_nombre', 150);
    }
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('contacto_emergencia_telefono', 9);
        aplicarTelefono('usuario_telefono', 9);
    }
    if (typeof aplicarAlfanumerico === 'function') {
        aplicarAlfanumerico('numero_licencia', 20, true);
    }
    if (typeof aplicarRangoNumerico === 'function') {
        aplicarRangoNumerico('sueldo_base', 0, 99999.99, 2);
        aplicarRangoNumerico('comision_porcentaje', 0, 100, 2);
    }
    if (typeof aplicarOrdenFechas === 'function') {
        aplicarOrdenFechas('fecha_emision_licencia', 'fecha_vencimiento_licencia');
    }

    // ------------------------------------------------------------
    // 2. AYUDA DINÁMICA DEL DOCUMENTO + REVALIDACIÓN AL CAMBIAR TIPO
    // ------------------------------------------------------------
    const selectTipoDoc = document.getElementById('tipo_documento');
    const ayudaDoc = document.getElementById('ayuda_documento');
    const numDoc = document.getElementById('numero_documento');

    if (selectTipoDoc && ayudaDoc) {
        const textos = {
            'DNI':       'Exactamente 8 dígitos numéricos',
            'CE':        'Entre 9 y 12 caracteres alfanuméricos',
            'PASAPORTE': 'Entre 6 y 12 caracteres alfanuméricos'
        };
        selectTipoDoc.addEventListener('change', function() {
            ayudaDoc.textContent = textos[this.value] || textos.DNI;
            // ✅ FIX: forzar revalidación al cambiar tipo de documento
            if (numDoc && typeof numDoc.validarDocumento === 'function' && numDoc.value) {
                numDoc.validarDocumento();
            }
        });
        ayudaDoc.textContent = textos[selectTipoDoc.value] || textos.DNI;
    }

    // ------------------------------------------------------------
    // 3. VALIDACIÓN DE EDAD (18-75 años)
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
                if (typeof mostrarToast === 'function') {
                    mostrarToast('El conductor debe tener al menos 18 años', 'warning');
                }
            } else if (edad > 75) {
                this.classList.add('is-invalid');
                if (typeof mostrarToast === 'function') {
                    mostrarToast('La edad máxima permitida es 75 años', 'warning');
                }
            } else {
                this.classList.add('is-valid');
            }
        });
    }

    // ------------------------------------------------------------
    // 4. SUBMIT UNIFICADO
    // ------------------------------------------------------------
    const form = document.getElementById('formConductor');
    const btnActualizar = document.getElementById('btnActualizar');
    let enviando = false;

    form.addEventListener('submit', function(e) {
        if (enviando) return true;
        e.preventDefault();

        const errores = [];

        // Validar documento
        if (numDoc && typeof numDoc.validarDocumento === 'function') {
            if (!numDoc.validarDocumento()) {
                errores.push('El número de documento es inválido o está vacío');
            }
        }

        // Validar licencia
        const licencia    = document.getElementById('numero_licencia');
        const categoria   = document.getElementById('categoria_licencia');
        const emision     = document.getElementById('fecha_emision_licencia');
        const vencimiento = document.getElementById('fecha_vencimiento_licencia');

        if (!licencia.value.trim()) {
            errores.push('El número de licencia es obligatorio');
        } else if (!/^[A-Z0-9\-]{6,20}$/.test(licencia.value.trim())) {
            errores.push('El número de licencia debe tener entre 6 y 20 caracteres alfanuméricos');
        }
        if (!categoria.value) {
            errores.push('Seleccione una categoría de licencia');
        }
        if (!vencimiento.value) {
            errores.push('La fecha de vencimiento es obligatoria');
        }
        if (emision.value && vencimiento.value) {
            if (new Date(vencimiento.value) <= new Date(emision.value)) {
                errores.push('La fecha de vencimiento debe ser posterior a la de emisión');
            }
        }

        // Validar sueldo
        const sueldo = document.getElementById('sueldo_base');
        const sueldoVal = parseFloat(sueldo.value);
        if (sueldo.value === '' || isNaN(sueldoVal) || sueldoVal < 0) {
            errores.push('El sueldo base debe ser un número mayor o igual a 0');
        }

        // Validar comisión
        const comision = document.getElementById('comision_porcentaje');
        const comisionVal = parseFloat(comision.value);
        if (comision.value !== '' && (isNaN(comisionVal) || comisionVal < 0 || comisionVal > 100)) {
            errores.push('La comisión debe estar entre 0 y 100');
        }

        // Mostrar errores
        if (errores.length > 0) {
            if (typeof mostrarErroresValidacion === 'function') {
                mostrarErroresValidacion(errores);
            } else {
                alert(errores.join('\n'));
            }
            return false;
        }

        // Confirmar y enviar
        const resumenHtml = `
            <div class="text-start small">
                <p class="mb-1"><strong>Documento:</strong> ${numDoc.value}</p>
                <p class="mb-1"><strong>Licencia:</strong> ${licencia.value} (${categoria.value})</p>
                <p class="mb-1"><strong>Vence:</strong> ${vencimiento.value}</p>
                <p class="mb-0"><strong>Sueldo:</strong> S/ ${sueldoVal.toFixed(2)}</p>
            </div>
        `;

        function ejecutarEnvio() {
            enviando = true;
            btnActualizar.disabled = true;
            btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Actualizando...',
                    html: 'Guardando los cambios',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
            form.submit();
        }

        if (typeof confirmarPersonalizado === 'function') {
            confirmarPersonalizado(
                '¿Guardar cambios?',
                resumenHtml,
                '<i class="fas fa-save me-1"></i>Sí, actualizar',
                '#ffc107',
                ejecutarEnvio
            );
        } else if (confirm('¿Guardar los cambios en este conductor?')) {
            ejecutarEnvio();
        }

        return false;
    });
});
</script>