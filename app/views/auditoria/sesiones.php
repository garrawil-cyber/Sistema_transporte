<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-users text-info me-2"></i><?php echo $titulo; ?></h1>
    <a href="<?php echo BASE_URL; ?>auditoria" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="table-responsive table-responsive-mobile">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>IP</th>
                <th>Login</th>
                <th class="text-center">Tiempo Activo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($sesiones)): ?>
                <?php foreach ($sesiones as $s): 
                    $minutos = (time() - strtotime($s['fecha_login'])) / 60;
                    $horas = floor($minutos / 60);
                    $mins = $minutos % 60;
                ?>
                <tr>
                    <td data-label="Usuario">
                        <i class="fas fa-user-circle text-success me-1"></i>
                        <strong><?php echo htmlspecialchars($s['usuario_nombre']); ?></strong>
                        <br><small class="text-muted"><?php echo $s['email']; ?></small>
                    </td>
                    <td data-label="Rol">
                        <span class="badge bg-info"><?php echo $s['rol_nombre']; ?></span>
                    </td>
                    <td data-label="IP">
                        <small><?php echo $s['ip_origen']; ?></small>
                    </td>
                    <td data-label="Login">
                        <small>
                            <?php echo date('d/m/Y', strtotime($s['fecha_login'])); ?><br>
                            <span class="text-muted"><?php echo date('H:i:s', strtotime($s['fecha_login'])); ?></span>
                        </small>
                    </td>
                    <td data-label="Tiempo" class="text-center">
                        <span class="badge bg-success">
                            <?php 
                                if ($horas > 0) echo $horas . 'h ' . floor($mins) . 'm';
                                else echo floor($mins) . ' min';
                            ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <i class="fas fa-users-slash fa-4x text-muted mb-3 d-block opacity-50"></i>
                        <h5 class="text-muted">No hay sesiones activas</h5>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>