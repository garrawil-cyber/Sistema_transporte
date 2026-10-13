<?php
// ✅ Detectar si el viaje tiene asientos en 2 pisos
$pisos_con_asientos = [];
foreach ($asientos as $a) {
    if (!empty($a['es_vendible']) && $a['es_vendible'] == 1) {
        $pisos_con_asientos[(string)$a['piso']] = true;
    }
}
$tiene_dos_pisos = isset($pisos_con_asientos['2']);

// Colores por estado
$estado_badge = [
    'PROGRAMADO' => ['primary',  'fa-clock'],
    'EN_CURSO'   => ['warning',  'fa-play-circle'],
    'COMPLETADO' => ['success',  'fa-check-circle'],
    'CANCELADO'  => ['danger',   'fa-times-circle'],
];
$eb = $estado_badge[$viaje['estado']] ?? ['secondary', 'fa-question'];
?>

<!-- ============================================================
     HEADER CON ACCIONES
     ============================================================ -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-route text-primary me-2"></i>Detalle del Viaje #<?php echo $viaje['id']; ?>
        </h1>
        <p class="text-muted mb-0 small">
            <?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?>
            · <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo BASE_URL; ?>viajes/edit/<?php echo $viaje['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="<?php echo BASE_URL; ?>viajes" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<!-- ============================================================
     PANEL DE ESTADO Y ACCIONES
     ============================================================ -->
<div class="alert alert-<?php echo $eb[0]; ?> border-0 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center">
        <i class="fas <?php echo $eb[1]; ?> fa-2x me-3"></i>
        <div>
            <h5 class="mb-0">
                Estado: <strong><?php echo $viaje['estado']; ?></strong>
            </h5>
            <?php if ($viaje['estado'] === 'COMPLETADO'): ?>
                <small>El bus y el conductor están disponibles para nuevos viajes.</small>
            <?php elseif ($viaje['estado'] === 'CANCELADO'): ?>
                <small>Este viaje fue cancelado.</small>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="d-flex gap-2 flex-wrap">
        <?php if ($viaje['estado'] === 'PROGRAMADO'): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>viajes/cambiarEstado/<?php echo $viaje['id']; ?>" 
                  class="d-inline form-cambiar-estado">
                <input type="hidden" name="estado" value="EN_CURSO">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-play me-1"></i>Iniciar Viaje
                </button>
            </form>
            <form method="POST" action="<?php echo BASE_URL; ?>viajes/cambiarEstado/<?php echo $viaje['id']; ?>" 
                  class="d-inline form-cambiar-estado">
                <input type="hidden" name="estado" value="CANCELADO">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Cancelar Viaje
                </button>
            </form>
        <?php elseif ($viaje['estado'] === 'EN_CURSO'): ?>
            <form method="POST" action="<?php echo BASE_URL; ?>viajes/cambiarEstado/<?php echo $viaje['id']; ?>" 
                  class="d-inline form-cambiar-estado">
                <input type="hidden" name="estado" value="COMPLETADO">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i>Completar Viaje
                </button>
            </form>
            <form method="POST" action="<?php echo BASE_URL; ?>viajes/cambiarEstado/<?php echo $viaje['id']; ?>" 
                  class="d-inline form-cambiar-estado">
                <input type="hidden" name="estado" value="CANCELADO">
                <button type="submit" class="btn btn-outline-danger">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     INFO Y ESTADÍSTICAS
     ============================================================ -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-bus me-2"></i>Detalles del Viaje
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Ruta</small>
                        <strong><?php echo htmlspecialchars($viaje['origen'] . ' → ' . $viaje['destino']); ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Bus</small>
                        <strong><?php echo $viaje['placa'] . ' (' . $viaje['tipo_servicio'] . ')'; ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Distancia</small>
                        <strong><?php echo number_format($viaje['distancia_km'], 2); ?> km</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Capacidad</small>
                        <strong><?php echo $viaje['capacidad_total']; ?> asientos</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Duración Estimada</small>
                        <strong><?php echo $viaje['duracion_estimada']; ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Conductor</small>
                        <strong><?php echo htmlspecialchars($viaje['conductor_nombre'] ?? 'Sin asignar'); ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Salida</small>
                        <strong>
                            <i class="fas fa-calendar-day me-1 text-muted"></i>
                            <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_salida'])); ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Llegada</small>
                        <strong>
                            <i class="fas fa-flag-checkered me-1 text-muted"></i>
                            <?php echo date('d/m/Y H:i', strtotime($viaje['fecha_llegada'])); ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Precio Final</small>
                        <h4 class="mb-0 text-success">S/ <?php echo number_format($viaje['precio_final'], 2); ?></h4>
                    </div>
                </div>
                
                <?php if (!empty($viaje['observaciones'])): ?>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-comment me-2"></i>
                        <?php echo htmlspecialchars($viaje['observaciones']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <i class="fas fa-chart-bar me-2"></i>Estadísticas
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <small class="text-muted d-block">Pasajeros</small>
                            <h4 class="mb-0"><?php echo $viaje['pasajeros_abordo'] ?? 0; ?></h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light rounded p-2 text-center">
                            <small class="text-muted d-block">Disponibles</small>
                            <h4 class="mb-0"><?php echo $viaje['asientos_libres'] ?? 0; ?></h4>
                        </div>
                    </div>
                </div>
                
                <?php if ($estadisticas && $estadisticas['total_ventas'] > 0): ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total ventas:</span>
                        <strong><?php echo $estadisticas['total_ventas']; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total recaudado:</span>
                        <strong class="text-success">S/ <?php echo number_format($estadisticas['total_recaudado'], 2); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Promedio:</span>
                        <strong>S/ <?php echo number_format($estadisticas['promedio_pago'], 2); ?></strong>
                    </div>
                    <hr>
                    <small class="text-muted d-block mb-2">Métodos de pago:</small>
                    <div class="d-flex justify-content-between small">
                        <span><i class="fas fa-money-bill-wave me-1"></i>Efectivo</span>
                        <strong><?php echo $estadisticas['pagos_efectivo'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span><i class="fas fa-credit-card me-1"></i>Tarjeta</span>
                        <strong><?php echo $estadisticas['pagos_tarjeta'] ?? 0; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span><i class="fas fa-exchange-alt me-1"></i>Transferencia</span>
                        <strong><?php echo $estadisticas['pagos_transferencia'] ?? 0; ?></strong>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-info-circle fa-2x mb-2 d-block opacity-25"></i>
                        <small>Sin ventas registradas</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MAPA DE ASIENTOS (DISEÑO PRO)
     ============================================================ -->
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <i class="fas fa-chair me-2"></i>Mapa de Asientos
        </div>
        <div class="small">
            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Disponible</span>
            <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Ocupado</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($asientos)): 
            // Agrupar por piso
            $asientos_por_piso = [];
            foreach ($asientos as $a) {
                $piso = (string)($a['piso'] ?? '1');
                if (!isset($asientos_por_piso[$piso])) {
                    $asientos_por_piso[$piso] = [];
                }
                $asientos_por_piso[$piso][] = $a;
            }
            
            // Ordenar cada piso por fila/columna
            foreach ($asientos_por_piso as $piso => $items) {
                usort($items, function($a, $b) {
                    $fa = $a['fila'] ?? 0; $fb = $b['fila'] ?? 0;
                    if ($fa != $fb) return $fa - $fb;
                    return ($a['columna'] ?? 0) - ($b['columna'] ?? 0);
                });
                $asientos_por_piso[$piso] = $items;
            }
        ?>
        
            <!-- Tabs para pisos -->
            <?php if ($tiene_dos_pisos): ?>
                <ul class="nav nav-pills nav-fill mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#viaje-piso-1" type="button">
                            <i class="fas fa-layer-group me-1"></i>Cubierta Inferior
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#viaje-piso-2" type="button">
                            <i class="fas fa-layer-group me-1"></i>Cubierta Superior
                        </button>
                    </li>
                </ul>
            <?php endif; ?>

            <div class="tab-content">
                <?php foreach ($asientos_por_piso as $piso => $lista): 
                    // ✅ FIX: $piso llega como int (PHP castea las claves de array
                    //    numéricas '1'/'2' a int automáticamente), mientras que la
                    //    comparación usaba '1' (string) con ===, que nunca coincidía.
                    //    Mismo bug ya corregido en ventas/create.php.
                    $activo = ((string)$piso === '1') ? 'show active' : '';
                    $clase_pane = ((string)$piso === '1' && !$tiene_dos_pisos) ? 'show active' : $activo;
                    if (!$tiene_dos_pisos) $clase_pane = 'show active';
                ?>
                    <div class="tab-pane fade <?php echo $clase_pane; ?>" id="viaje-piso-<?php echo $piso; ?>">
                        
                        <div class="bus-layout">
                            <!-- Frente del bus: solo referencia al conductor -->
                            <div class="bus-header-refs">
                                <div class="ref-item ref-conductor">
                                    <i class="fas fa-user-tie fa-lg"></i>
                                    <small>Conductor</small>
                                </div>
                            </div>

                            <!-- Filas de asientos -->
                            <div class="bus-body">
                                <?php 
                                if (!function_exists('renderElementoEspecialShow')) {
                                    function renderElementoEspecialShow($tipo) {
                                        // ✅ El hueco de la escalera sigue sin caja ni fondo de asiento
                                        //    (como en un bus real), pero ahora sí muestra el ícono como
                                        //    referencia visual dentro del espacio vacío. BLOQUEADO se
                                        //    mantiene completamente vacío. TV conserva su ícono con caja,
                                        //    porque vive en el pasillo.
                                        if ($tipo === 'BLOQUEADO') {
                                            return '<div class="bus-asiento-vacio"></div>';
                                        }

                                        if ($tipo === 'ESCALERA') {
                                            return '<div class="bus-asiento-vacio bus-asiento-vacio-escalera" title="Escaleras"><i class="fas fa-stairs"></i></div>';
                                        }

                                        $cfg = ['icon' => 'fa-tv', 'clase' => 'especial-tv', 'title' => 'Televisor'];
                                        ob_start();
                                        ?>
                                        <div class="bus-asiento especial <?php echo $cfg['clase']; ?>" title="<?php echo htmlspecialchars($cfg['title']); ?>">
                                            <i class="fas <?php echo $cfg['icon']; ?>"></i>
                                        </div>
                                        <?php
                                        return ob_get_clean();
                                    }
                                }

                                if (!function_exists('claseIconoTipoAsiento')) {
                                    function claseIconoTipoAsiento($tipo) {
                                        switch ($tipo) {
                                            case 'VENTANA': return ['asiento-ventana', 'fa-window-maximize'];
                                            case 'PASILLO': return ['asiento-pasillo', 'fa-grip-lines-vertical'];
                                            case 'CAMA':    return ['asiento-cama', 'fa-window-maximize'];
                                            //case 'CAMA':    return ['asiento-cama', 'fa-bed'];
                                            default:        return ['', '', ''];
                                        }
                                    }
                                }

                                // Agrupar por fila
                                $filas = [];
                                foreach ($lista as $a) {
                                    $f = $a['fila'] ?? 1;
                                    $filas[$f][] = $a;
                                }
                                ksort($filas);
                                
                                foreach ($filas as $fila_num => $asientos_fila):
                                    // ✅ columna = 0 son elementos de pasillo (ej. TV): no compiten
                                    //    por una posición real de asiento. columna>=1 (asientos
                                    //    reales o especiales que reemplazan un asiento, ej. escalera)
                                    //    se reparten a izq/der con un umbral dinámico según el
                                    //    ancho real de la fila, en vez de un "columna==3" fijo que
                                    //    ocultaba asientos reales en layouts 2+1.
                                    $pasillo_items = [];
                                    $reales_fila = [];
                                    foreach ($asientos_fila as $a) {
                                        if ((int)($a['columna'] ?? 1) === 0) {
                                            $pasillo_items[] = $a;
                                        } else {
                                            $reales_fila[] = $a;
                                        }
                                    }

                                    $max_col = 1;
                                    foreach ($reales_fila as $a) {
                                        $max_col = max($max_col, (int)($a['columna'] ?? 1));
                                    }
                                    $limite_izq = (int)ceil($max_col / 2);

                                    $izq = []; $der = [];
                                    foreach ($reales_fila as $a) {
                                        $col = (int)($a['columna'] ?? 1);
                                        if ($col <= $limite_izq) $izq[] = $a;
                                        else $der[] = $a;
                                    }
                                ?>
                                    <div class="bus-fila">
                                        <!-- Izquierda -->
                                        <div class="bus-lado bus-lado-izq">
                                            <?php foreach ($izq as $a): ?>
                                                <?php if (!empty($a['es_vendible']) && $a['es_vendible'] == 1): ?>
                                                    <?php [$clase_tipo, $icono_tipo, $label_tipo] = claseIconoTipoAsiento($a['tipo'] ?? ''); ?>
                                                    <div class="bus-asiento <?php echo $a['disponible'] ? 'disponible' : 'ocupado'; ?><?php echo $clase_tipo ? ' ' . $clase_tipo : ''; ?>" 
                                                         title="Asiento <?php echo $a['numero_asiento']; ?> - <?php echo $a['disponible'] ? 'Disponible' : 'Ocupado'; ?>">
                                                        <?php if ($icono_tipo): ?>
                                                            <i class="fas <?php echo $icono_tipo; ?> bus-asiento-tipo-icon"></i>
                                                        <?php endif; ?>
                                                        <span class="bus-asiento-num"><?php echo $a['numero_asiento']; ?></span>
                                                        <?php if ($label_tipo): ?>
                                                            <span class="bus-asiento-tipo-label"><?php echo $label_tipo; ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!$a['disponible']): ?>
                                                            <i class="fas fa-times" style="position:absolute;top:2px;right:2px;font-size:0.6rem;"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?php echo renderElementoEspecialShow($a['tipo'] ?? 'BLOQUEADO'); ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Pasillo (elementos con columna=0, ej. TV) -->
                                        <div class="bus-pasillo">
                                            <?php foreach ($pasillo_items as $a): ?>
                                                <?php echo renderElementoEspecialShow($a['tipo'] ?? 'TV'); ?>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- Derecha -->
                                        <div class="bus-lado bus-lado-der">
                                            <?php foreach ($der as $a): ?>
                                                <?php if (!empty($a['es_vendible']) && $a['es_vendible'] == 1): ?>
                                                    <?php [$clase_tipo, $icono_tipo, $label_tipo] = claseIconoTipoAsiento($a['tipo'] ?? ''); ?>
                                                    <div class="bus-asiento <?php echo $a['disponible'] ? 'disponible' : 'ocupado'; ?><?php echo $clase_tipo ? ' ' . $clase_tipo : ''; ?>" 
                                                         title="Asiento <?php echo $a['numero_asiento']; ?> - <?php echo $a['disponible'] ? 'Disponible' : 'Ocupado'; ?>">
                                                        <?php if ($icono_tipo): ?>
                                                            <i class="fas <?php echo $icono_tipo; ?> bus-asiento-tipo-icon"></i>
                                                        <?php endif; ?>
                                                        <span class="bus-asiento-num"><?php echo $a['numero_asiento']; ?></span>
                                                        <?php if ($label_tipo): ?>
                                                            <span class="bus-asiento-tipo-label"><?php echo $label_tipo; ?></span>
                                                        <?php endif; ?>
                                                        <?php if (!$a['disponible']): ?>
                                                            <i class="fas fa-times" style="position:absolute;top:2px;right:2px;font-size:0.6rem;"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?php echo renderElementoEspecialShow($a['tipo'] ?? 'BLOQUEADO'); ?>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-chair fa-3x mb-3 d-block opacity-25"></i>
                <p>No hay asientos configurados para este viaje</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     SCRIPT: Confirmación de cambio de estado
     ============================================================ -->
<script>
document.querySelectorAll('.form-cambiar-estado').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formRef = this;
        const estado = formRef.querySelector('input[name="estado"]').value;
        
        const config = {
            'EN_CURSO': {
                title: '¿Iniciar viaje?',
                text: 'El viaje pasará a estado EN CURSO. El bus y el conductor quedarán ocupados.',
                icon: 'question',
                color: '#ffc107',
                confirmText: 'Sí, iniciar'
            },
            'COMPLETADO': {
                title: '¿Completar viaje?',
                html: '<p>Se marcará como <strong>COMPLETADO</strong>.</p><p class="text-success"><i class="fas fa-check-circle me-1"></i>El bus y el conductor quedarán disponibles para nuevos viajes.</p>',
                icon: 'success',
                color: '#198754',
                confirmText: 'Sí, completar'
            },
            'CANCELADO': {
                title: '¿Cancelar viaje?',
                html: '<p class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Esta acción cancelará el viaje.</p><p>El bus y el conductor quedarán disponibles.</p>',
                icon: 'warning',
                color: '#dc3545',
                confirmText: 'Sí, cancelar'
            }
        };
        
        const cfg = config[estado] || {
            title: '¿Cambiar estado?',
            text: 'El estado cambiará a ' + estado,
            icon: 'question',
            color: '#0d6efd',
            confirmText: 'Sí, cambiar'
        };
        
        Swal.fire({
            title: cfg.title,
            html: cfg.html || '<p>' + (cfg.text || '') + '</p>',
            icon: cfg.icon,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check me-1"></i>' + cfg.confirmText,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: cfg.color
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: function() { Swal.showLoading(); }
                });
                formRef.submit();
            }
        });
    });
});
</script>