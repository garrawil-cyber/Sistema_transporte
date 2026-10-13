<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-user-plus text-primary me-2"></i><?php echo $titulo ?? 'Nuevo Cliente'; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra un nuevo cliente en el sistema</p>
    </div>
    <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Errores encontrados:</h6>
        <ul class="mb-0">
            <?php foreach ($errores as $e): ?>
                <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>clientes/store" id="formCliente">
    <div class="row g-3">

        <!-- Identificación -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white border-0">
                    <i class="fas fa-id-card me-2"></i>Identificación
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-passport me-1"></i>Tipo de Documento <span class="text-danger">*</span>
                        </label>
                        <select name="tipo_documento" id="tipo_documento" class="form-select form-select-lg" required>
                            <option value="DNI" <?php echo (($data['tipo_documento'] ?? 'DNI') == 'DNI') ? 'selected' : ''; ?>>DNI</option>
                            <option value="CE" <?php echo (($data['tipo_documento'] ?? '') == 'CE') ? 'selected' : ''; ?>>Carné de Extranjería</option>
                            <option value="PASAPORTE" <?php echo (($data['tipo_documento'] ?? '') == 'PASAPORTE') ? 'selected' : ''; ?>>Pasaporte</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-hashtag me-1"></i>Número de Documento <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-primary text-white">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text"
                                   name="numero_documento"
                                   id="numero_documento"
                                   class="form-control"
                                   placeholder="Ej: 12345678"
                                   value="<?php echo htmlspecialchars($data['numero_documento'] ?? ''); ?>"
                                   required autofocus
                                   autocomplete="off">
                        </div>
                        <small class="text-muted" id="ayuda_documento">Exactamente 8 dígitos numéricos</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Datos personales -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white border-0">
                    <i class="fas fa-user me-2"></i>Datos Personales
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-user me-1"></i>Nombres <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                   placeholder="Ej: Juan Carlos"
                                   value="<?php echo htmlspecialchars($data['nombre'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                <i class="fas fa-user me-1"></i>Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="apellido" id="apellido" class="form-control"
                                   placeholder="Ej: Pérez López"
                                   value="<?php echo htmlspecialchars($data['apellido'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-phone me-1"></i>Teléfono
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone text-success"></i></span>
                            <input type="text" name="telefono" id="telefono" class="form-control"
                                   placeholder="Ej: 987654321"
                                   value="<?php echo htmlspecialchars($data['telefono'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" name="email" id="email" class="form-control"
                                   placeholder="Ej: cliente@email.com"
                                   value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dirección -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white border-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Dirección
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-home"></i></span>
                        <textarea name="direccion" id="direccion" class="form-control" rows="2"
                                  placeholder="Ej: Av. Principal 123, Dpto 4B"><?php echo htmlspecialchars($data['direccion'] ?? ''); ?></textarea>
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
                            <a href="<?php echo BASE_URL; ?>clientes" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-1"></i>Guardar Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<a href="#" class="fab-mobile" onclick="event.preventDefault(); document.getElementById('formCliente').requestSubmit();" title="Guardar Cliente">
    <i class="fas fa-save"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // 1. Aplicar validación de documento
    // ============================================
    aplicarValidacionDocumento('numero_documento', 'tipo_documento');

    // ============================================
    // 2. Actualizar texto de ayuda según tipo
    // ============================================
    const select = document.getElementById('tipo_documento');
    const ayuda  = document.getElementById('ayuda_documento');
    const textos = {
        'DNI':       'Exactamente 8 dígitos numéricos',
        'CE':        'Entre 9 y 12 caracteres alfanuméricos',
        'PASAPORTE': 'Entre 6 y 12 caracteres alfanuméricos'
    };

    function actualizarAyuda() {
        ayuda.textContent = textos[select.value] || textos.DNI;
    }
    select.addEventListener('change', actualizarAyuda);
    actualizarAyuda();

    // ============================================
    // 3. Submit unificado: valida + confirma + envía
    //    ⚠️ UN SOLO listener. Sin conflictos.
    // ============================================
    const form = document.getElementById('formCliente');
    let enviando = false;

    form.addEventListener('submit', function(e) {
        // Si ya estamos enviando (confirmado en el modal), dejar pasar
        if (enviando) return true;

        e.preventDefault();

        // 3.1 Validar documento
        const inputDoc = document.getElementById('numero_documento');
        if (inputDoc.validarDocumento && !inputDoc.validarDocumento()) {
            inputDoc.focus();
            mostrarToast('Corrija el número de documento', 'warning');
            return false;
        }

        // 3.2 Validar campos obligatorios
        const numero   = inputDoc.value.trim();
        const nombre   = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();

        if (!numero || !nombre || !apellido) {
            mostrarToast('Complete los campos obligatorios', 'warning');
            return false;
        }

        // 3.3 Confirmar con SweetAlert
        Swal.fire({
            title: '¿Guardar cliente?',
            html: `
                <div class="text-start">
                    <p class="mb-1"><strong>Documento:</strong> ${numero}</p>
                    <p class="mb-0"><strong>Nombre:</strong> ${nombre} ${apellido}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i>Sí, guardar',
            cancelButtonText: 'Revisar',
            confirmButtonColor: '#198754'
        }).then((result) => {
            if (result.isConfirmed) {
                enviando = true;
                Swal.fire({
                    title: 'Guardando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                form.submit();   // ✅ Envío real (salta este listener)
            }
        });

        return false;
    });
});
</script>