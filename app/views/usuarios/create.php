<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-user-plus text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Registra un nuevo usuario del sistema</p>
    </div>
    <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-secondary">
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

<form method="POST" action="<?php echo BASE_URL; ?>usuarios/store" id="formUsuario" novalidate>
    <div class="row g-3">

        <!-- ============================================================
             DATOS PERSONALES
             ============================================================ -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user me-2"></i>Datos Personales
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                Nombres <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nombre" id="nombre" class="form-control"
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($data['nombre'] ?? ''); ?>"
                                   required autofocus autocomplete="given-name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="apellido" id="apellido" class="form-control"
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($data['apellido'] ?? ''); ?>"
                                   required autocomplete="family-name">
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
                                   value="<?php echo htmlspecialchars($data['telefono'] ?? ''); ?>"
                                   autocomplete="tel" inputmode="numeric">
                        </div>
                        <small class="text-muted">Opcional · Solo números (máx 9 dígitos)</small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-envelope me-1"></i>Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>"
                                   required autocomplete="email" maxlength="120">
                        </div>
                        <small class="text-muted">Será usado para iniciar sesión</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             ACCESO Y ROL
             ============================================================ -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-lock me-2"></i>Acceso al Sistema
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-key me-1"></i>Contraseña <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Mínimo 6 caracteres" required minlength="6">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePassword('password')" tabindex="-1">
                                <i class="fas fa-eye" id="eye-password"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="generarPassword()" title="Generar contraseña"
                                    tabindex="-1">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                        <small class="text-muted">
                            Mínimo 6 caracteres. Puedes generar una aleatoria.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-user-tag me-1"></i>Rol <span class="text-danger">*</span>
                        </label>
                        <select name="rol_id" id="rol_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione un rol --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo (int)$r['id']; ?>"
                                        <?php echo (($data['rol_id'] ?? '') == $r['id']) ? 'selected' : ''; ?>>
                                    <?php
                                        $iconos = [
                                            'ADMIN'     => '👑',
                                            'VENDEDOR'  => '🎫',
                                            'BODEGA'    => '📦',
                                            'CONTADOR'  => '📊',
                                            'CONDUCTOR' => '🚌'
                                        ];
                                        echo ($iconos[$r['nombre']] ?? '👤') . ' ' . htmlspecialchars($r['nombre']);
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-0">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="activo" id="activo"
                                   class="form-check-input" role="switch"
                                   <?php echo (!isset($data['activo']) || $data['activo']) ? 'checked' : ''; ?>>
                            <label for="activo" class="form-check-label fw-bold">
                                <i class="fas fa-check-circle text-success me-1"></i>Usuario activo
                            </label>
                        </div>
                        <small class="text-muted">Los usuarios inactivos no pueden iniciar sesión</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- BOTONES -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="btnGuardarUsuario">
                            <i class="fas fa-save me-1"></i>Guardar Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// ============================================================
// SCRIPT USUARIOS/CREATE
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // ------------------------------------------------------------
    // 1. HELPERS
    // ------------------------------------------------------------
    if (typeof aplicarSoloLetras === 'function') {
        aplicarSoloLetras('nombre', 100);
        aplicarSoloLetras('apellido', 100);
    }
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('telefono', 9);
    }
    if (typeof aplicarValidacionEmail === 'function') {
        aplicarValidacionEmail('email');
    }

    // ------------------------------------------------------------
    // 2. TOGGLE PASSWORD
    // ------------------------------------------------------------
    window.togglePassword = function(inputId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById('eye-' + inputId);
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (eye) eye.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            if (eye) eye.className = 'fas fa-eye';
        }
    };

    // ------------------------------------------------------------
    // 3. GENERAR PASSWORD
    // ------------------------------------------------------------
    window.generarPassword = function() {
        if (typeof generarPasswordEnInput === 'function') {
            generarPasswordEnInput('password', 10);
            // Mostrar la contraseña generada
            const input = document.getElementById('password');
            input.type = 'text';
            const eye = document.getElementById('eye-password');
            if (eye) eye.className = 'fas fa-eye-slash';
        }
    };

    // ------------------------------------------------------------
    // 4. SUBMIT
    // ------------------------------------------------------------
    const form = document.getElementById('formUsuario');
    const btnGuardar = document.getElementById('btnGuardarUsuario');
    let enviando = false;

    form.addEventListener('submit', function(e) {
        if (enviando) return true;
        e.preventDefault();

        const errores = [];

        const nombre   = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const email    = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const rol_id   = document.getElementById('rol_id').value;
        const telefono = document.getElementById('telefono').value.trim();

        if (!nombre)   errores.push('El nombre es obligatorio');
        if (!apellido) errores.push('El apellido es obligatorio');

        if (!email) {
            errores.push('El email es obligatorio');
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errores.push('El email no tiene un formato válido');
        }

        if (!password) {
            errores.push('La contraseña es obligatoria');
        } else if (password.length < 6) {
            errores.push('La contraseña debe tener al menos 6 caracteres');
        }

        if (!rol_id) errores.push('Debe seleccionar un rol');

        if (errores.length > 0) {
            if (typeof mostrarErroresValidacion === 'function') {
                mostrarErroresValidacion(errores);
            } else {
                alert(errores.join('\n'));
            }
            return false;
        }

        const rolTexto = document.getElementById('rol_id').options[
            document.getElementById('rol_id').selectedIndex
        ].text.trim();

        const resumenHtml = `
            <div class="text-start small">
                <p class="mb-1"><strong>Nombre:</strong> ${nombre} ${apellido}</p>
                <p class="mb-1"><strong>Email:</strong> ${email}</p>
                ${telefono ? `<p class="mb-1"><strong>Teléfono:</strong> ${telefono}</p>` : ''}
                <p class="mb-0"><strong>Rol:</strong> ${rolTexto}</p>
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
                    html: 'Creando el usuario',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
            form.submit();
        }

        if (typeof confirmarPersonalizado === 'function') {
            confirmarPersonalizado(
                '¿Guardar usuario?',
                resumenHtml,
                '<i class="fas fa-save me-1"></i>Sí, guardar',
                '#198754',
                ejecutarEnvio
            );
        } else if (confirm('¿Guardar este usuario?')) {
            ejecutarEnvio();
        }

        return false;
    });
});
</script>