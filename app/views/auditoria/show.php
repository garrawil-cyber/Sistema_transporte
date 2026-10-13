<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-search text-primary me-2"></i><?php echo $titulo; ?></h1>
    <a href="<?php echo BASE_URL; ?>auditoria" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-info-circle me-2"></i>Detalle del Registro #<?php echo $registro['id']; ?>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Fecha/Hora:</strong><br><?php echo date('d/m/Y H:i:s', strtotime($registro['fecha_hora'])); ?></p>
                        <p><strong>Usuario:</strong><br>
                            <?php if (!empty($registro['usuario_id'])): ?>
                                <?php echo htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')); ?>
                                <br><small class="text-muted"><?php echo $usuario['email'] ?? ''; ?></small>
                            <?php else: ?>
                                <span class="text-muted">Sistema</span>
                            <?php endif; ?>
                        </p>
                        <p><strong>Acción:</strong><br>
                            <?php 
                                $colores = [
                                    'INSERT' => 'success', 'UPDATE' => 'warning', 'DELETE' => 'danger',
                                    'LOGIN' => 'info', 'LOGOUT' => 'secondary', 'EXPORT' => 'primary'
                                ];
                            ?>
                            <span class="badge bg-<?php echo $colores[$registro['accion']] ?? 'secondary'; ?> fs-6">
                                <?php echo $registro['accion']; ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Módulo:</strong><br><?php echo htmlspecialchars($registro['modulo'] ?? '-'); ?></p>
                        <p><strong>Tabla:</strong><br><?php echo htmlspecialchars($registro['tabla_afectada'] ?? '-'); ?></p>
                        <p><strong>ID Afectado:</strong><br><?php echo $registro['registro_id'] ?? '-'; ?></p>
                    </div>
                </div>

                <hr>

                <p><strong>Descripción:</strong></p>
                <div class="alert alert-info"><?php echo nl2br(htmlspecialchars($registro['descripcion'] ?? '')); ?></div>

                <?php if (!empty($registro['valores_anteriores'])): ?>
                    <p><strong>Valores Anteriores:</strong></p>
                    <pre class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto;"><?php 
                        $ant = json_decode($registro['valores_anteriores'], true);
                        echo htmlspecialchars(json_encode($ant, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    ?></pre>
                <?php endif; ?>

                <?php if (!empty($registro['valores_nuevos'])): ?>
                    <p><strong>Valores Nuevos:</strong></p>
                    <pre class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto;"><?php 
                        $nuevo = json_decode($registro['valores_nuevos'], true);
                        echo htmlspecialchars(json_encode($nuevo, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    ?></pre>
                <?php endif; ?>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <p><strong>IP:</strong> <?php echo $registro['ip_origen']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Método:</strong> <?php echo $registro['metodo_http'] ?? '-'; ?></p>
                    </div>
                </div>
                <p><strong>URL:</strong> <code><?php echo htmlspecialchars($registro['url_accedida'] ?? '-'); ?></code></p>
                <p><strong>User Agent:</strong> <small class="text-muted"><?php echo htmlspecialchars($registro['user_agent'] ?? '-'); ?></small></p>
            </div>
        </div>
    </div>
</div>