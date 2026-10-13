<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-user-edit text-warning me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">
            Editando a <strong><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></strong>
        </p>
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

<?php
// ✅ FIX: verificar si es el propio usuario logueado
$es_mi_usuario = ((int)$usuario['id'] === (int)($_SESSION['user_id'] ?? 0));
?>

<?php if ($es_mi_usuario): ?>
    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-info-circle me-2"></i>
        Estás editando <strong>tu propio usuario</strong>. Ten cuidado al cambiar tu rol o desactivar tu cuenta.
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>usuarios/update/<?php echo (int)$usuario['id']; ?>" id="formUsuario" novalidate>
    <div class="row g-3">

        <!-- DATOS PERSONALES -->
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
                                   value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>"
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="apellido" id="apellido" class="form-control"
                                   maxlength="100"
                                   value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>"
                                   required>
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
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                   inputmode="numeric">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-envelope me-1"></i>Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope text-primary"></i></span>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>"
                                   required maxlength="120">
                        </div>
                        <small class="text-muted">El email debe ser único en el sistema</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCESO Y ROL -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-lock me-2"></i>Acceso al Sistema
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-key me-1"></i>Nueva Contraseña
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Dejar vacío para mantener"
                                   minlength="6">
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
                            Dejar en blanco para conservar la contraseña actual
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-user-tag me-1"></i>Rol <span class="text-danger">*</span>
                        </label>
                        <select name="rol_id" id="rol_id" class="form-select form-select-lg" required
                                <?= $es_mi_usuario ? 'disabled' : '' ?>>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?php echo (int)$r['id']; ?>"
                                        <?php echo ($usuario['rol_id'] == $r['id']) ? 'selected' : ''; ?>>
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
                        <?php if ($es_mi_usuario): ?>
                            <input type="hidden" name="rol_id" value="<?= (int)$usuario['rol_id'] ?>">
                            <small class="text-warning">
                                <i class="fas fa-lock me-1"></i>No puedes cambiar tu propio rol
                            </small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-0">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="activo" id="activo"
                                   class="form-check-input" role="switch"
                                   <?php echo $usuario['activo'] ? 'checked' : ''; ?>
                                   <?= $es_mi_usuario ? 'disabled' : '' ?>>
                            <label for="activo" class="form-check-label fw-bold">
                                <i class="fas fa-check-circle text-success me-1"></i>Usuario activo
                            </label>
                        </div>
                        <?php if ($es_mi_usuario): ?>
                            <input type="hidden" name="activo" value="1">
                            <small class="text-warning">
                                <i class="fas fa-lock me-1"></i>No puedes desactivar tu propia cuenta
                            </small>
                        <?php endif; ?>
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
                            <i class="fas fa-clock me-1"></i>
                            Último acceso:
                            <?php echo !empty($usuario['ultimo_acceso'])
                                ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))
                                : 'Nunca'; ?>
                        </small>
                        <div class="d-flex gap-2">
                            <a href="<?php echo BASE_URL; ?>usuarios" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg" id="btnActualizarUsuario">
                                <i class="fas fa-save me-1"></i>Actualizar Usuario
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
// ============================================================
// SCRIPT USUARIOS/EDIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    // Helpers
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

    // Toggle password
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

    // Generar password
    window.generarPassword = function() {
        if (typeof generarPasswordEnInput === 'function') {
            generarPasswordEnInput('password', 10);
            const input = document.getElementById('password');
            input.type = 'text';
            const eye = document.getElementById('eye-password');
            if (eye) eye.className = 'fas fa-eye-slash';
        }
    };

    // Submit
    const form = document.getElementById('formUsuario');
    const btnActualizar = document.getElementById('btnActualizarUsuario');
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

        // Password solo se valida si se ingresó algo
        if (password && password.length < 6) {
            errores.push('Si cambias la contraseña, debe tener al menos 6 caracteres');
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

        const resumenHtml = `
            <div class="text-start small">
                <p class="mb-1"><strong>Nombre:</strong> ${nombre} ${apellido}</p>
                <p class="mb-1"><strong>Email:</strong> ${email}</p>
                ${telefono ? `<p class="mb-1"><strong>Teléfono:</strong> ${telefono}</p>` : ''}
                ${password ? '<p class="mb-1 text-warning"><i class="fas fa-key me-1"></i>Se cambiará la contraseña</p>' : ''}
                <p class="mb-0"><strong>Estado:</strong> ${document.getElementById('activo').checked ? 'Activo' : 'Inactivo'}</p>
            </div>
        `;

        function ejecutarEnvio() {
            enviando = true;
            if (btnActualizar) {
                btnActualizar.disabled = true;
                btnActualizar.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
            }
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
                '¿Actualizar usuario?',
                resumenHtml,
                '<i class="fas fa-save me-1"></i>Sí, actualizar',
                '#198754',
                ejecutarEnvio
            );
        } else if (confirm('¿Actualizar este usuario?')) {
            ejecutarEnvio();
        }

        return false;
    });
});
</script>