<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-user-cog text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">Gestión de usuarios y permisos del sistema</p>
    </div>
    <a href="<?php echo BASE_URL; ?>usuarios/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Usuario
    </a>
</div>

<?php
// ✅ FIX: mapa de íconos de roles para consistencia visual con create/edit
$iconos_roles = [
    'ADMIN'     => '👑',
    'VENDEDOR'  => '🎫',
    'BODEGA'    => '📦',
    'CONTADOR'  => '📊',
    'CONDUCTOR' => '🚌'
];

// ✅ FIX: mapa de colores por rol
$colores_roles = [
    'ADMIN'     => 'danger',
    'VENDEDOR'  => 'primary',
    'BODEGA'    => 'warning',
    'CONTADOR'  => 'info',
    'CONDUCTOR' => 'success'
];
?>

<!-- Tabla -->
<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th class="text-center">Rol</th>
                <th>Teléfono</th>
                <th class="text-center">Estado</th>
                <th>Último Acceso</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $u):
                    $u_nombre   = trim((string)($u['nombre']   ?? ''));
                    $u_apellido = trim((string)($u['apellido'] ?? ''));
                    $iniciales  = strtoupper(
                        mb_substr($u_nombre, 0, 1) . mb_substr($u_apellido, 0, 1)
                    );
                    if ($iniciales === '') $iniciales = '?';

                    $rol_nombre   = $u['rol_nombre'] ?? '—';
                    $icono_rol    = $iconos_roles[$rol_nombre]  ?? '👤';
                    $color_rol    = $colores_roles[$rol_nombre] ?? 'secondary';

                    $nombre_completo = $u_nombre . ' ' . $u_apellido;

                    // ✅ FIX: usar json_encode para pasar strings seguras a JS
                    $nombre_js    = json_encode($nombre_completo, JSON_UNESCAPED_UNICODE);
                    $url_delete   = BASE_URL . 'usuarios/delete/'   . (int)$u['id'];
                    $url_activar  = BASE_URL . 'usuarios/activar/'  . (int)$u['id'];

                    $es_mi_usuario = ((int)$u['id'] === (int)($_SESSION['user_id'] ?? 0));
                ?>
                <tr>
                    <td data-label="Nombre">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                 style="width: 38px; height: 38px; flex-shrink: 0; font-size: 0.85rem;">
                                <?= htmlspecialchars($iniciales) ?>
                            </div>
                            <div class="min-w-0">
                                <strong class="d-block text-truncate">
                                    <?= htmlspecialchars($nombre_completo) ?>
                                </strong>
                                <?php if ($es_mi_usuario): ?>
                                    <small class="text-info">
                                        <i class="fas fa-user-circle me-1"></i>Tú
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td data-label="Email">
                        <small>
                            <a href="mailto:<?= htmlspecialchars($u['email'] ?? '') ?>"
                               class="text-decoration-none text-body">
                                <?= htmlspecialchars($u['email'] ?? '—') ?>
                            </a>
                        </small>
                    </td>
                    <td data-label="Rol" class="text-center">
                        <span class="badge bg-<?= $color_rol ?>">
                            <?= $icono_rol ?> <?= htmlspecialchars($rol_nombre) ?>
                        </span>
                    </td>
                    <td data-label="Teléfono">
                        <?php if (!empty($u['telefono'])): ?>
                            <a href="tel:<?= htmlspecialchars($u['telefono']) ?>"
                               class="text-decoration-none text-body">
                                <i class="fas fa-phone me-1 text-success"></i><?= htmlspecialchars($u['telefono']) ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Estado" class="text-center">
                        <span class="badge <?= $u['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                            <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td data-label="Último Acceso">
                        <small class="text-muted">
                            <?= !empty($u['ultimo_acceso'])
                                ? date('d/m/Y H:i', strtotime($u['ultimo_acceso']))
                                : 'Nunca' ?>
                        </small>
                    </td>
                    <td data-label="Acciones" class="text-center">
                        <div class="d-inline-flex gap-1">
                            <a href="<?= BASE_URL ?>usuarios/show/<?= (int)$u['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>usuarios/edit/<?= (int)$u['id'] ?>"
                               class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if (!$es_mi_usuario): ?>
                                <?php if ($u['activo']): ?>
                                    <a href="#" class="btn btn-sm btn-outline-danger"
                                       title="Desactivar <?= htmlspecialchars($nombre_completo) ?>"
                                       onclick='return confirmarEliminar(<?= json_encode($url_delete) ?>, <?= $nombre_js ?>)'>
                                        <i class="fas fa-user-slash"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="btn btn-sm btn-outline-success"
                                       title="Activar <?= htmlspecialchars($nombre_completo) ?>"
                                       onclick='return confirmarPersonalizado(
                                           "¿Activar usuario?",
                                           "Se reactivará la cuenta de <strong>" + <?= $nombre_js ?> + "</strong> y podrá volver a iniciar sesión.",
                                           "<i class=\'fas fa-user-check me-1\'></i>Sí, activar",
                                           "#198754",
                                           function() { window.location.href = "<?= $url_activar ?>"; }
                                       )'>
                                        <i class="fas fa-user-check"></i>
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-sm btn-outline-secondary" disabled
                                        title="No puedes modificar tu propia cuenta aquí">
                                    <i class="fas fa-lock"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay usuarios registrados</h5>
                        <a href="<?= BASE_URL ?>usuarios/create" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus me-1"></i>Registrar primer usuario
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (isset($paginator)): ?>
    <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
<?php endif; ?>

<a href="<?php echo BASE_URL; ?>usuarios/create" class="fab-mobile" title="Nuevo Usuario">
    <i class="fas fa-plus"></i>
</a>