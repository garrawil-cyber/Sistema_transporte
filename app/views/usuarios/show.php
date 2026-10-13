<?php
// ✅ FIX: fallback por si $sesiones no existe
$sesiones = $sesiones ?? [];

// ✅ FIX: iniciales null-safe para el avatar
$nombre_safe   = trim((string)($usuario['nombre']   ?? ''));
$apellido_safe = trim((string)($usuario['apellido'] ?? ''));
$iniciales = strtoupper(
    mb_substr($nombre_safe, 0, 1) . mb_substr($apellido_safe, 0, 1)
);
if ($iniciales === '') $iniciales = '?';

// ✅ FIX: color del rol
$iconos_roles = [
    'ADMIN'     => '👑',
    'VENDEDOR'  => '🎫',
    'BODEGA'    => '📦',
    'CONTADOR'  => '📊',
    'CONDUCTOR' => '🚌'
];
$colores_roles = [
    'ADMIN'     => 'danger',
    'VENDEDOR'  => 'primary',
    'BODEGA'    => 'warning',
    'CONTADOR'  => 'info',
    'CONDUCTOR' => 'success'
];

$rol_nombre = $usuario['rol_nombre'] ?? '—';
$icono_rol  = $iconos_roles[$rol_nombre]  ?? '👤';
$color_rol  = $colores_roles[$rol_nombre] ?? 'secondary';

$es_mi_usuario = ((int)($usuario['id'] ?? 0) === (int)($_SESSION['user_id'] ?? 0));
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2 mb-0">
        <i class="fas fa-user-circle text-primary me-2"></i><?php echo $titulo; ?>
    </h1>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>usuarios/edit/<?= (int)$usuario['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="<?= BASE_URL ?>usuarios" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row g-3">
    <!-- ============================================================
         COLUMNA IZQUIERDA: INFO
         ============================================================ -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white text-center py-4">
                <div class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center fw-bold mb-3"
                     style="width: 80px; height: 80px; font-size: 1.8rem;">
                    <?= htmlspecialchars($iniciales) ?>
                </div>
                <h4 class="mb-1"><?= htmlspecialchars($nombre_safe . ' ' . $apellido_safe) ?></h4>
                <span class="badge bg-<?= $usuario['activo'] ? 'success' : 'secondary' ?> mt-2">
                    <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                    <?= $usuario['activo'] ? 'ACTIVO' : 'INACTIVO' ?>
                </span>
                <?php if ($es_mi_usuario): ?>
                    <div class="mt-2">
                        <span class="badge bg-info">
                            <i class="fas fa-user-circle me-1"></i>Tu cuenta
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="fas fa-envelope text-primary mt-1" style="width: 18px;"></i>
                        <div class="min-w-0">
                            <div class="text-muted small">Email</div>
                            <a href="mailto:<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                               class="text-decoration-none text-body fw-semibold text-truncate d-block">
                                <?= htmlspecialchars($usuario['email'] ?? '—') ?>
                            </a>
                        </div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <i class="fas fa-phone text-success mt-1" style="width: 18px;"></i>
                        <div>
                            <div class="text-muted small">Teléfono</div>
                            <?php if (!empty($usuario['telefono'])): ?>
                                <a href="tel:<?= htmlspecialchars($usuario['telefono']) ?>"
                                   class="text-decoration-none text-body fw-semibold">
                                    <?= htmlspecialchars($usuario['telefono']) ?>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </li>
                    <li class="mb-0 d-flex align-items-start gap-2">
                        <i class="fas fa-tag text-info mt-1" style="width: 18px;"></i>
                        <div>
                            <div class="text-muted small">Rol</div>
                            <span class="badge bg-<?= $color_rol ?>">
                                <?= $icono_rol ?> <?= htmlspecialchars($rol_nombre) ?>
                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-info-circle me-2"></i>Información
            </div>
            <div class="card-body small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">ID</span>
                    <strong class="font-monospace">#<?= (int)$usuario['id'] ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Creado</span>
                    <strong>
                        <?= !empty($usuario['created_at'])
                            ? date('d/m/Y H:i', strtotime($usuario['created_at']))
                            : '—' ?>
                    </strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Último acceso</span>
                    <strong>
                        <?= !empty($usuario['ultimo_acceso'])
                            ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso']))
                            : 'Nunca' ?>
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         COLUMNA DERECHA: HISTORIAL DE SESIONES
         ============================================================ -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-history me-2"></i><strong>Historial de Sesiones</strong>
                </div>
                <span class="badge bg-light text-dark"><?= count($sesiones) ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive table-responsive-mobile">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Login</th>
                                <th>Logout</th>
                                <th class="text-center">Duración</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($sesiones)): ?>
                                <?php foreach ($sesiones as $s):
                                    $duracion = (int)($s['duracion_minutos'] ?? 0);
                                    $activa = empty($s['fecha_logout']);
                                ?>
                                <tr>
                                    <td data-label="Login">
                                        <small>
                                            <?= !empty($s['fecha_login'])
                                                ? date('d/m/Y H:i', strtotime($s['fecha_login']))
                                                : '—' ?>
                                        </small>
                                    </td>
                                    <td data-label="Logout">
                                        <?php if ($activa): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>Activa
                                            </span>
                                        <?php else: ?>
                                            <small>
                                                <?= date('d/m/Y H:i', strtotime($s['fecha_logout'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Duración" class="text-center">
                                        <?php if ($duracion > 0): ?>
                                            <span class="badge bg-info"><?= $duracion ?> min</span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="IP">
                                        <small class="text-muted font-monospace">
                                            <?= htmlspecialchars($s['ip_origen'] ?? '—') ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-50"></i>
                                        Sin historial de sesiones registrado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>