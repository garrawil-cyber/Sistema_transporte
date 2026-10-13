<?php
$hay_viaje_seleccionado = !empty($viaje_seleccionado);
$hay_caja_abierta       = isset($caja_abierta) && $caja_abierta;

$total_disponibles = 0;
if ($hay_viaje_seleccionado && !empty($asientos)) {
    $total_disponibles = count(array_filter($asientos, fn($a) => ($a['disponible'] ?? 0) == 1 && ($a['es_vendible'] ?? 1) == 1));
}

// Paso activo para el stepper
$paso_actual = 1;
if ($hay_viaje_seleccionado && !$asiento_seleccionado) $paso_actual = 2;
if ($asiento_seleccionado) $paso_actual = 3;
?>

<div class="container-fluid px-3 px-md-4 py-3">

    <!-- ═══════════════════════════════════════════════════════════
         HEADER
         ═══════════════════════════════════════════════════════════ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-success bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-cash-register text-success fa-lg"></i>
                </span>
                <span>Nueva Venta</span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">
                <i class="fas fa-info-circle me-1"></i>Selecciona el asiento y registra la venta
            </p>
        </div>
        <a href="<?= BASE_URL ?>ventas" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         STEPPER
         ═══════════════════════════════════════════════════════════ -->
    <div class="stepper-vendedor">
        <div class="step <?= $paso_actual === 1 ? 'active' : ($paso_actual > 1 ? 'completed' : '') ?>">
            <div class="step-num"><span>1</span></div>
            <div class="step-label">Buscar Viaje</div>
        </div>
        <div class="step-line <?= $paso_actual > 1 ? 'completed' : '' ?>"></div>
        <div class="step <?= $paso_actual === 2 ? 'active' : ($paso_actual > 2 ? 'completed' : '') ?>">
            <div class="step-num"><span>2</span></div>
            <div class="step-label">Elegir Asiento</div>
        </div>
        <div class="step-line <?= $paso_actual > 2 ? 'completed' : '' ?>"></div>
        <div class="step <?= $paso_actual === 3 ? 'active' : '' ?>">
            <div class="step-num"><span>3</span></div>
            <div class="step-label">Cobrar</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         CAJA ABIERTA
         ═══════════════════════════════════════════════════════════ -->
    <?php if ($hay_caja_abierta): ?>
        <div class="alert alert-success d-flex align-items-center border-0 shadow-sm py-2 mb-3">
            <i class="fas fa-cash-register me-2"></i>
            <small>
                <strong>Caja abierta:</strong>
                <?= htmlspecialchars($caja_abierta['usuario_nombre'] ?? '') ?>
                · Desde: <?= date('d/m/Y H:i', strtotime($caja_abierta['fecha_apertura'])) ?>
                · Monto inicial: S/ <?= number_format($caja_abierta['monto_inicial'], 2) ?>
            </small>
        </div>
    <?php else: ?>
        <div class="alert alert-warning d-flex align-items-center border-0 shadow-sm mb-3">
            <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
            <div>
                <strong>Atención:</strong> No tiene una caja abierta.
                <a href="<?= BASE_URL ?>caja" class="alert-link">Abrir caja ahora</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         ERRORES
         ═══════════════════════════════════════════════════════════ -->
    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <h6 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Atención:</h6>
            <ul class="mb-0">
                <?php foreach ($errores as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         PASO 1: BUSCADOR DE VIAJES
         ═══════════════════════════════════════════════════════════ -->
    <?php if (!$hay_viaje_seleccionado): ?>

        <div class="card card-modern mb-3">
            <div class="card-header-icon">
                <div class="icon-circle"><i class="fas fa-search"></i></div>
                <h3>Buscar Viaje</h3>
                <span class="badge bg-light text-dark ms-auto">
                    <i class="fas fa-calendar-day me-1"></i><?= date('d/m/Y') ?>
                </span>
            </div>
            <div class="card-body">
                <form method="GET" action="<?= BASE_URL ?>ventas/create" class="row g-2 align-items-end">
                    <input type="hidden" name="buscar" value="1">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold mb-1">Origen</label>
                        <input type="text" name="origen" class="form-control form-control-sm"
                               placeholder="Ej: Lima" value="<?= htmlspecialchars($data_get['origen'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Destino</label>
                        <input type="text" name="destino" class="form-control form-control-sm"
                               placeholder="Ej: Arequipa" value="<?= htmlspecialchars($data_get['destino'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold mb-1">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($data_get['fecha'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         LISTA DE VIAJES
         ═══════════════════════════════════════════════════════════ -->
    <?php if (!empty($viajes)): ?>
        <div class="card card-modern mb-3">
            <div class="card-header-icon">
                <div class="icon-circle" style="background: rgba(25, 135, 84, .1); color: var(--success);">
                    <i class="fas fa-bus"></i>
                </div>
                <h3>Viajes Disponibles</h3>
                <span class="badge bg-success ms-auto"><?= count($viajes) ?> viajes</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive table-responsive-mobile">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Ruta</th>
                                <th>Salida</th>
                                <th>Bus</th>
                                <th class="text-center">Disponibles</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($viajes as $v): ?>
                            <tr>
                                <td data-label="Ruta">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-route text-primary small"></i>
                                        </div>
                                        <div>
                                            <strong class="small"><?= htmlspecialchars($v['origen']) ?></strong>
                                            <i class="fas fa-arrow-right mx-1 text-muted small"></i>
                                            <strong class="small"><?= htmlspecialchars($v['destino']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Salida">
                                    <div class="small">
                                        <i class="fas fa-calendar-day me-1 text-muted"></i>
                                        <?= date('d/m/Y', strtotime($v['fecha_salida'])) ?>
                                        <br>
                                        <i class="fas fa-clock me-1 text-muted"></i>
                                        <span class="badge bg-light text-dark"><?= date('H:i', strtotime($v['fecha_salida'])) ?></span>
                                    </div>
                                </td>
                                <td data-label="Bus">
                                    <strong class="small"><?= $v['placa'] ?></strong>
                                    <br><small class="text-muted"><?= $v['tipo_servicio'] ?></small>
                                </td>
                                <td data-label="Disponibles" class="text-center">
                                    <span class="badge bg-<?= $v['asientos_disponibles'] > 5 ? 'success' : 'warning' ?> fs-6">
                                        <i class="fas fa-chair me-1"></i><?= $v['asientos_disponibles'] ?>
                                    </span>
                                </td>
                                <td data-label="Precio" class="text-end">
                                    <strong class="text-success fs-6">S/ <?= number_format($v['precio_final'], 2) ?></strong>
                                </td>
                                <td data-label="Acción" class="text-center">
                                    <?php if ($v['asientos_disponibles'] > 0): ?>
                                        <a href="<?= BASE_URL ?>ventas/create?buscar=1&viaje_id=<?= $v['id'] ?>"
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-check me-1"></i>Seleccionar
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Completo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($hay_caja_abierta && !$hay_viaje_seleccionado): ?>
        <div class="alert alert-warning border-0 shadow-sm text-center py-4">
            <i class="fas fa-exclamation-triangle fa-3x d-block mb-3"></i>
            <h5>No se encontraron viajes disponibles</h5>
            <p class="mb-0">Intente con otros filtros o consulte otras fechas.</p>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         PASO 2/3: MAPA DE ASIENTOS + FORMULARIO
         ═══════════════════════════════════════════════════════════ -->
    <?php if ($viaje_seleccionado && !empty($asientos)):
        // Agrupar asientos por piso
        $asientos_por_piso = ['1' => [], '2' => []];
        foreach ($asientos as $a) {
            $piso = $a['piso'] ?? '1';
            $asientos_por_piso[$piso][] = $a;
        }
        foreach ($asientos_por_piso as $piso => $items) {
            usort($items, function($a, $b) {
                $fa = $a['fila'] ?? 0; $fb = $b['fila'] ?? 0;
                if ($fa != $fb) return $fa - $fb;
                return ($a['columna'] ?? 0) - ($b['columna'] ?? 0);
            });
            $asientos_por_piso[$piso] = $items;
        }

        // ✅ FIX: Solo mostrar tabs si AMBOS pisos tienen asientos reales
        $tiene_dos_pisos = !empty($asientos_por_piso['1']) && !empty($asientos_por_piso['2']);

        $piso_activo = '1';
        if ($asiento_seleccionado) {
            foreach ($asientos as $a) {
                if ($a['id'] == $asiento_seleccionado) {
                    $piso_activo = (string)($a['piso'] ?? '1');
                    break;
                }
            }
        }

        // Helper: elemento especial (TV, escalera, bloqueado)
        if (!function_exists('renderElementoEspecial')) {
            function renderElementoEspecial($tipo) {
                if ($tipo === 'BLOQUEADO') {
                    return '<div class="bus-asiento-vacio"></div>';
                }
                if ($tipo === 'ESCALERA') {
                    return '<div class="bus-asiento-vacio bus-asiento-vacio-escalera" title="Escaleras"><i class="fas fa-stairs"></i></div>';
                }
                return '<div class="bus-asiento especial" title="Televisor"><i class="fas fa-tv"></i></div>';
            }
        }

        // Helper: render de asiento
        if (!function_exists('renderAsiento')) {
            function renderAsiento($a, $viaje_seleccionado, $asiento_seleccionado) {
                $tipo_actual = $a['tipo'] ?? 'ESTANDAR';
                $es_vendible = array_key_exists('es_vendible', $a) ? (int)$a['es_vendible'] : 1;
                if (!$es_vendible || in_array($tipo_actual, ['TV', 'ESCALERA', 'BLOQUEADO'], true)) {
                    return renderElementoEspecial($tipo_actual);
                }

                $disponible = (int)($a['disponible'] ?? 1);
                $estado = $a['estado'] ?? ($disponible ? 'DISPONIBLE' : 'OCUPADO');
                $es_seleccionado = ($asiento_seleccionado == $a['id']);

                $reserva_expirada = false;
                if ($estado === 'RESERVADO' && !empty($a['reservado_hasta'])) {
                    if (strtotime($a['reservado_hasta']) < time()) {
                        $estado = 'DISPONIBLE';
                        $reserva_expirada = true;
                    }
                }

                $clase = 'bus-asiento ';
                if ($es_seleccionado) {
                    $clase .= 'seleccionado';
                } elseif ($estado === 'OCUPADO' || !$disponible) {
                    $clase .= 'ocupado';
                } elseif ($estado === 'RESERVADO' && !$reserva_expirada) {
                    $clase .= 'reservado';
                } else {
                    $clase .= 'disponible';
                }

                $icono_tipo = '';
                switch ($tipo_actual) {
                    case 'VENTANA': $icono_tipo = 'fa-window-maximize'; break;
                    case 'PASILLO': $icono_tipo = 'fa-grip-lines-vertical'; break;
                    case 'CAMA':    $icono_tipo = 'fa-window-maximize'; break;
                }

                $url = BASE_URL . 'ventas/create?buscar=1'
                    . '&viaje_id=' . $viaje_seleccionado['id']
                    . '&asiento_id=' . $a['id'];

                $tag = ($clase === 'bus-asiento disponible') ? 'a' : 'div';
                $href = ($tag === 'a') ? 'href="' . $url . '"' : '';

                $titulo = "Asiento {$a['numero_asiento']}";
                if ($es_seleccionado) {
                    $titulo .= " - Seleccionado";
                } elseif ($estado === 'OCUPADO') {
                    $titulo .= " - Ocupado";
                } elseif ($estado === 'RESERVADO') {
                    $tiempo = strtotime($a['reservado_hasta']) - time();
                    $minutos = ceil($tiempo / 60);
                    $titulo .= " - Reservado por {$minutos} min";
                } else {
                    $titulo .= " - Disponible";
                }

                ob_start();
                ?>
                <<?php echo $tag; ?> <?php echo $href; ?>
                    class="<?php echo $clase; ?>"
                    title="<?php echo htmlspecialchars($titulo); ?>"
                    data-asiento-id="<?php echo $a['id']; ?>"
                    data-asiento="<?php echo $a['numero_asiento']; ?>"
                    data-estado="<?php echo $estado; ?>">
                    <?php if ($icono_tipo): ?>
                        <i class="fas <?php echo $icono_tipo; ?>"></i>
                    <?php endif; ?>
                    <span class="bus-asiento-num"><?php echo $a['numero_asiento']; ?></span>
                    <?php if ($estado === 'RESERVADO'): ?>
                        <i class="fas fa-clock bus-asiento-clock"></i>
                    <?php endif; ?>
                </<?php echo $tag; ?>>
                <?php
                return ob_get_clean();
            }
        }
    ?>

        <div class="row g-3">

            <!-- ══════════════════════════════════════════════════
                 COLUMNA IZQUIERDA: MAPA DE ASIENTOS
                 ══════════════════════════════════════════════════ -->
            <div class="col-lg-7">
                <div class="card card-modern">

                    <div class="card-header-icon">
                        <div class="icon-circle"><i class="fas fa-chair"></i></div>
                        <h3>Mapa de Asientos</h3>
                        <span class="badge bg-success ms-auto">
                            <i class="fas fa-chair me-1"></i><?= $total_disponibles ?> disponibles
                        </span>
                    </div>

                    <!-- Info compacta del viaje -->
                    <div class="p-3 bg-light border-bottom">
                        <div class="row g-2 text-center row-cols-2 row-cols-md-4">
                            <div class="col">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: .65rem;">Ruta</small>
                                <strong class="small text-primary"><?= htmlspecialchars(($viaje_seleccionado['origen'] ?? '') . ' → ' . ($viaje_seleccionado['destino'] ?? '')) ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: .65rem;">Salida</small>
                                <strong class="small"><?= date('d/m · H:i', strtotime($viaje_seleccionado['fecha_salida'])) ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: .65rem;">Bus</small>
                                <strong class="small"><?= htmlspecialchars($viaje_seleccionado['placa'] ?? '') ?></strong>
                            </div>
                            <div class="col">
                                <small class="text-muted d-block text-uppercase fw-bold" style="font-size: .65rem;">Precio</small>
                                <strong class="small text-success">S/ <?= number_format($viaje_seleccionado['precio_final'] ?? 0, 2) ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ FIX: Tabs pisos con estructura Bootstrap correcta -->
                    <?php if ($tiene_dos_pisos): ?>
                    <div class="p-3 bg-light border-bottom">
                        <ul class="nav nav-pills gap-2" id="tabsPisos" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                        class="nav-link <?= $piso_activo === '1' ? 'active' : '' ?>"
                                        id="tab-piso-1"
                                        data-bs-toggle="pill"
                                        data-bs-target="#piso-1"
                                        role="tab"
                                        aria-controls="piso-1"
                                        aria-selected="<?= $piso_activo === '1' ? 'true' : 'false' ?>">
                                    <i class="fas fa-layer-group me-1"></i>Piso 1
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button type="button"
                                        class="nav-link <?= $piso_activo === '2' ? 'active' : '' ?>"
                                        id="tab-piso-2"
                                        data-bs-toggle="pill"
                                        data-bs-target="#piso-2"
                                        role="tab"
                                        aria-controls="piso-2"
                                        aria-selected="<?= $piso_activo === '2' ? 'true' : 'false' ?>">
                                    <i class="fas fa-layer-group me-1"></i>Piso 2
                                </button>
                            </li>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- Mapa -->
                    <div class="p-3 p-md-4 bg-light">
                        <div class="tab-content" id="contenidoPisos">
                            <?php foreach ($asientos_por_piso as $piso => $lista):
                                if (empty($lista)) continue;
                                $activo = ((string)$piso === (string)$piso_activo) ? 'show active' : '';
                            ?>
                                <div class="tab-pane fade <?= $activo ?>" id="piso-<?= $piso ?>" role="tabpanel" aria-labelledby="tab-piso-<?= $piso ?>">
                                    <div class="bus-layout">
                                        <div class="bus-header-refs">
                                            <div class="ref-item">
                                                <?php if ((string)$piso === '1'): ?>
                                                    <i class="fas fa-user-tie fa-lg text-secondary"></i>
                                                    <small>Conductor</small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ref-item text-end">
                                                <?php if ((string)$piso === '1'): ?>
                                                    <i class="fas fa-door-open fa-lg text-secondary"></i>
                                                    <small>Puerta</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="bus-body">
                                            <?php
                                            $filas = [];
                                            foreach ($lista as $a) {
                                                $f = $a['fila'] ?? 1;
                                                $filas[$f][] = $a;
                                            }
                                            ksort($filas);

                                            foreach ($filas as $fila_num => $asientos_fila):
                                                $pasillo_items = [];
                                                $asientos_reales_fila = [];
                                                foreach ($asientos_fila as $a) {
                                                    if ((int)($a['columna'] ?? 1) === 0) {
                                                        $pasillo_items[] = $a;
                                                    } else {
                                                        $asientos_reales_fila[] = $a;
                                                    }
                                                }

                                                $max_col = 1;
                                                foreach ($asientos_reales_fila as $a) {
                                                    $max_col = max($max_col, (int)($a['columna'] ?? 1));
                                                }
                                                $limite_izq = (int)ceil($max_col / 2);

                                                $izq = []; $der = [];
                                                foreach ($asientos_reales_fila as $a) {
                                                    $col = $a['columna'] ?? 1;
                                                    if ($col <= $limite_izq) $izq[] = $a;
                                                    else $der[] = $a;
                                                }
                                            ?>
                                                <div class="bus-fila">
                                                    <div class="bus-lado bus-lado-izq">
                                                        <?php foreach ($izq as $a): ?>
                                                            <?php echo renderAsiento($a, $viaje_seleccionado, $asiento_seleccionado); ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="bus-pasillo<?php echo !empty($pasillo_items) ? ' bus-pasillo-con-elemento' : ''; ?>">
                                                        <?php foreach ($pasillo_items as $a): ?>
                                                            <?php echo renderAsiento($a, $viaje_seleccionado, $asiento_seleccionado); ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <div class="bus-lado bus-lado-der">
                                                        <?php foreach ($der as $a): ?>
                                                            <?php echo renderAsiento($a, $viaje_seleccionado, $asiento_seleccionado); ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <!-- Leyenda -->
                                    <div class="d-flex flex-wrap justify-content-center gap-3 mt-3 small">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bus-asiento disponible d-inline-flex" style="width: 24px; height: 24px;"></span>
                                            <span>Disponible</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bus-asiento seleccionado d-inline-flex" style="width: 24px; height: 24px;"></span>
                                            <span>Seleccionado</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bus-asiento reservado d-inline-flex" style="width: 24px; height: 24px;"></span>
                                            <span>Reservado</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="bus-asiento ocupado d-inline-flex" style="width: 24px; height: 24px;"></span>
                                            <span>Ocupado</span>
                                        </div>
                                    </div>

                                    <?php if ($asiento_seleccionado): ?>
                                        <div class="timer-reserva-modern" id="timerReservaContainer">
                                            <i class="fas fa-hourglass-half"></i>
                                            <div class="timer-content">
                                                <small>Tu reserva expira en</small>
                                                <strong id="reservaTimer">5:00</strong>
                                                <div class="timer-progress-modern">
                                                    <div class="bar" id="timerProgressBar" style="width: 100%;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted small mt-3 mb-0 text-center">
                                            <i class="fas fa-hand-pointer me-1"></i>Haga clic en un asiento disponible para continuar
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 COLUMNA DERECHA: PANEL PASAJERO
                 ══════════════════════════════════════════════════ -->
            <div class="col-lg-5">
                <div class="panel-pasajero-sticky">
                    <div class="card card-modern">

                        <?php if ($asiento_seleccionado):
                            $asiento_num = '';
                            foreach ($asientos as $a) {
                                if ($a['id'] == $asiento_seleccionado) { $asiento_num = $a['numero_asiento']; break; }
                            }
                        ?>
                            <div class="asiento-destacado">
                                <small>Asiento seleccionado</small>
                                <div class="num-grande"><?= htmlspecialchars($asiento_num) ?></div>
                                <div class="ruta-mini">
                                    <i class="fas fa-bus me-1"></i><?= htmlspecialchars($viaje_seleccionado['placa'] ?? '') ?> · <?= htmlspecialchars($viaje_seleccionado['tipo_servicio'] ?? '') ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="asiento-destacado" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                <small>Sin asiento seleccionado</small>
                                <div class="num-grande"><i class="fas fa-chair"></i></div>
                                <div class="ruta-mini">Elige un asiento del mapa</div>
                            </div>
                        <?php endif; ?>

                        <div class="card-body p-3">
                            <form method="POST" action="<?= BASE_URL ?>ventas/create" id="formVenta">
                                <input type="hidden" name="action" value="vender">
                                <input type="hidden" name="viaje_id" value="<?= $viaje_seleccionado['id'] ?>">
                                <input type="hidden" name="asiento_id" value="<?= $asiento_seleccionado ?>">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-uppercase">
                                        Documento <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <select name="tipo_documento" id="tipo_documento" class="form-select" style="max-width: 85px;">
                                            <option value="DNI">DNI</option>
                                            <option value="CE">CE</option>
                                            <option value="PASAPORTE">Pas.</option>
                                        </select>
                                        <input type="text" name="numero_documento" id="dniInput" class="form-control"
                                               value="<?= htmlspecialchars($cliente_data['numero_documento'] ?? '') ?>"
                                               required autocomplete="off" placeholder="12345678">
                                        <button type="button" class="btn btn-outline-primary" onclick="buscarCliente()">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-uppercase">
                                        Nombres <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="nombre" id="nombreInput" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($cliente_data['nombre'] ?? '') ?>"
                                           required placeholder="Nombres">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-uppercase">
                                        Apellidos <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="apellido" id="apellidoInput" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($cliente_data['apellido'] ?? '') ?>"
                                           required placeholder="Apellidos">
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase">Teléfono</label>
                                        <input type="text" name="telefono" id="telefonoInput" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($cliente_data['telefono'] ?? '') ?>"
                                               placeholder="987654321">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-uppercase">Email</label>
                                        <input type="email" name="email" id="emailInput" class="form-control form-control-sm"
                                               value="<?= htmlspecialchars($cliente_data['email'] ?? '') ?>"
                                               placeholder="cliente@email.com">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-uppercase">Dirección</label>
                                    <input type="text" name="direccion" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($cliente_data['direccion'] ?? '') ?>"
                                           placeholder="Opcional">
                                </div>

                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-uppercase">Método de Pago</label>
                                    <select name="metodo_pago" class="form-select form-select-sm">
                                        <option value="EFECTIVO">💵 Efectivo</option>
                                        <option value="TARJETA">💳 Tarjeta</option>
                                        <option value="TRANSFERENCIA">🏦 Transferencia</option>
                                        <option value="OTRO">📝 Otro</option>
                                    </select>
                                </div>
                            </form>
                        </div>

                        <div class="total-box-modern">
                            <div class="total-row-modern">
                                <span class="label">TOTAL</span>
                                <span class="amount">S/ <?= number_format($viaje_seleccionado['precio_final'] ?? 0, 2) ?></span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <button type="submit" form="formVenta" class="btn btn-success btn-lg w-100 mb-2"
                                    <?= $asiento_seleccionado ? '' : 'disabled' ?>>
                                <i class="fas fa-check-circle me-1"></i>Confirmar Venta
                            </button>
                            <?php if (!$asiento_seleccionado): ?>
                                <p class="text-danger small text-center mb-2">
                                    <i class="fas fa-hand-pointer me-1"></i>Seleccione un asiento primero
                                </p>
                            <?php endif; ?>

                            <?php if ($asiento_seleccionado): ?>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-warning flex-fill"
                                            onclick="liberarAsientoManual()">
                                        <i class="fas fa-unlock me-1"></i>Liberar
                                    </button>
                                    <a href="<?= BASE_URL ?>ventas/create" class="btn btn-sm btn-outline-secondary flex-fill">
                                        <i class="fas fa-search me-1"></i>Cambiar viaje
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
// ============================================================
// SISTEMA DE RESERVAS MULTI-VENDEDOR v5 — Con SweetAlert2
// ============================================================
document.addEventListener('DOMContentLoaded', function() {

    const VIAJE_ID = <?= (int)($viaje_seleccionado['id'] ?? 0) ?>;
    const USUARIO_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
    const BASE_URL_JS = '<?= BASE_URL ?>';
    const ASIENTO_SELECCIONADO = <?= (int)($asiento_seleccionado ?? 0) ?>;

    const TIMEOUT_RESERVA_SEG      = 300;
    const TIMEOUT_INACTIVIDAD_SEG  = 120;
    const TIMEOUT_BLUR_SEG         = 30;
    const AVISO_PRE_EXPIRACION_SEG = 30;

    let reservaActualId       = ASIENTO_SELECCIONADO || null;
    let timerInterval         = null;
    let segundosRestantes     = TIMEOUT_RESERVA_SEG;
    let autoRefreshInterval   = null;
    let ventaEnProceso        = false;
    let avisoMostrado         = false;
    let inactividadInterval   = null;
    let segundosInactividad   = 0;
    let blurStart             = null;
    let blurInterval          = null;

    // ============================================================
    // 1. INTERCEPTAR CLICS EN ASIENTOS
    // ============================================================
    document.querySelectorAll('.bus-asiento').forEach(function(asiento) {
        if (asiento.classList.contains('especial')) return;

        if (asiento.classList.contains('disponible')) {
            asiento.addEventListener('click', function(e) {
                e.preventDefault();
                const asientoId = this.dataset.asientoId;
                if (!asientoId || !VIAJE_ID) return;
                if (this.dataset.reservando === '1') return;
                this.dataset.reservando = '1';
                reservarAsiento(asientoId, this);
            });
        }

        if (asiento.classList.contains('seleccionado')) {
            asiento.style.cursor = 'pointer';
            asiento.title = `Asiento ${asiento.dataset.asiento} - Tuyo · Doble clic para liberar`;

            asiento.addEventListener('click', function(e) { e.preventDefault(); });

            // ✅ FIX: SweetAlert2 en lugar de confirm() nativo
            asiento.addEventListener('dblclick', function(e) {
                e.preventDefault();
                mostrarConfirmLiberarAsiento(
                    this.dataset.asientoId,
                    this.dataset.asiento,
                    'Doble clic'
                );
            });
        }

        if (asiento.classList.contains('reservado') && !asiento.classList.contains('seleccionado')) {
            asiento.style.cursor = 'not-allowed';
            asiento.addEventListener('click', function(e) {
                e.preventDefault();
                mostrarToast('⚠️ Asiento reservado por otro vendedor', 'warning');
            });
        }

        if (asiento.classList.contains('ocupado')) {
            asiento.style.cursor = 'not-allowed';
        }
    });

    // ============================================================
    // 2. RESERVAR ASIENTO
    // ============================================================
    function reservarAsiento(asientoId, elemento) {
        elemento.style.opacity = '0.5';
        elemento.style.pointerEvents = 'none';

        const liberarPrimero = (reservaActualId && reservaActualId != asientoId)
            ? fetch(BASE_URL_JS + 'ventas/liberarReserva', {
                  method: 'POST',
                  body: new URLSearchParams({ viaje_id: VIAJE_ID, asiento_id: reservaActualId })
              }).catch(() => {})
            : Promise.resolve();

        liberarPrimero
            .then(() => {
                const fd = new FormData();
                fd.append('viaje_id', VIAJE_ID);
                fd.append('asiento_id', asientoId);
                return fetch(BASE_URL_JS + 'ventas/reservarAsiento', { method: 'POST', body: fd });
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    reservaActualId = asientoId;
                    segundosRestantes = data.segundos_restantes || TIMEOUT_RESERVA_SEG;
                    mostrarToast('✅ Asiento reservado por 5 min', 'success');
                    setTimeout(() => {
                        window.location.href = BASE_URL_JS + 'ventas/create?buscar=1&viaje_id=' + VIAJE_ID + '&asiento_id=' + asientoId;
                    }, 500);
                } else {
                    elemento.style.opacity = '';
                    elemento.style.pointerEvents = '';
                    elemento.dataset.reservando = '';
                    mostrarToast('⚠️ ' + data.message, 'warning');
                    setTimeout(actualizarEstadoAsientos, 500);
                }
            })
            .catch(() => {
                elemento.style.opacity = '';
                elemento.style.pointerEvents = '';
                elemento.dataset.reservando = '';
                mostrarToast('Error de conexión al reservar', 'error');
            });
    }

    // ============================================================
    // 3. LIBERAR ASIENTO
    // ============================================================
    function liberarAsientoPropio(asientoId, motivo = 'Manual') {
        if (!asientoId) return Promise.resolve();

        const fd = new FormData();
        fd.append('viaje_id', VIAJE_ID);
        fd.append('asiento_id', asientoId);

        detenerTimers();
        reservaActualId = null;

        return fetch(BASE_URL_JS + 'ventas/liberarReserva', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarToast(`🔓 Asiento liberado (${motivo})`, 'info');
                    const el = document.querySelector(`[data-asiento-id="${asientoId}"]`);
                    if (el) {
                        el.classList.remove('seleccionado');
                        el.classList.add('disponible');
                        el.dataset.estado = 'DISPONIBLE';
                    }
                    setTimeout(() => {
                        window.location.href = BASE_URL_JS + 'ventas/create?buscar=1&viaje_id=' + VIAJE_ID;
                    }, 700);
                }
            })
            .catch(() => {});
    }

    // ============================================================
    // ✅ NUEVO: Confirmación elegante con SweetAlert2
    //    Reemplaza el confirm() nativo feo del navegador
    // ============================================================
    function mostrarConfirmLiberarAsiento(asientoId, numAsiento, motivo = 'Manual') {
        if (typeof Swal === 'undefined') {
            // Fallback si SweetAlert2 no está cargado
            if (confirm(`¿Liberar el asiento ${numAsiento}?`)) {
                liberarAsientoPropio(asientoId, motivo);
            }
            return;
        }

        Swal.fire({
            title: '¿Liberar este asiento?',
            html: `
                <div class="text-start">
                    <p class="mb-2">
                        Vas a liberar el asiento
                        <strong class="text-primary">N° ${escapeHtmlVenta(numAsiento)}</strong>.
                    </p>
                    <div class="alert alert-warning py-2 px-3 mb-0 small">
                        <i class="fas fa-info-circle me-1"></i>
                        Quedará disponible para otros vendedores inmediatamente.
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-unlock me-1"></i>Sí, liberar',
            cancelButtonText: '<i class="fas fa-lock me-1"></i>Mantener',
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                liberarAsientoPropio(asientoId, motivo);
            }
        });
    }

    // Exponer al window para el onclick del botón
    window.liberarAsientoManual = function() {
        if (!reservaActualId) return;

        const el = document.querySelector(`[data-asiento-id="${reservaActualId}"]`);
        const numAsiento = el ? el.dataset.asiento : '';
        mostrarConfirmLiberarAsiento(reservaActualId, numAsiento, 'Manual');
    };

    function detenerTimers() {
        if (timerInterval)       { clearInterval(timerInterval); timerInterval = null; }
        if (inactividadInterval) { clearInterval(inactividadInterval); inactividadInterval = null; }
        if (blurInterval)        { clearInterval(blurInterval); blurInterval = null; }
        if (autoRefreshInterval) { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
    }

    // ============================================================
    // 4. LIBERAR AL CERRAR PESTAÑA
    // ============================================================
    window.addEventListener('beforeunload', function() {
        if (ventaEnProceso || !reservaActualId) return;
        const fd = new FormData();
        fd.append('viaje_id', VIAJE_ID);
        fd.append('asiento_id', reservaActualId);
        navigator.sendBeacon(BASE_URL_JS + 'ventas/liberarReservaActual', fd);
    });

    // ============================================================
    // 5. LIBERAR AL CLIC EN "VOLVER" / "CAMBIAR VIAJE"
    // ============================================================
    document.querySelectorAll('a').forEach(function(link) {
        const href = link.getAttribute('href') || '';
        if (href.endsWith('/ventas') || (href.includes('/ventas/create') && !href.includes('asiento_id'))) {
            link.addEventListener('click', function() {
                if (reservaActualId) {
                    fetch(BASE_URL_JS + 'ventas/liberarReserva', {
                        method: 'POST',
                        body: new URLSearchParams({ viaje_id: VIAJE_ID, asiento_id: reservaActualId }),
                        keepalive: true
                    });
                }
            });
        }
    });

    // ============================================================
    // 6. AUTO-REFRESH
    // ============================================================
    function iniciarAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(actualizarEstadoAsientos, 5000);
    }
    function pausarAutoRefresh() {
        if (autoRefreshInterval) { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
    }
    function actualizarEstadoAsientos() {
        if (!VIAJE_ID) return;
        fetch(BASE_URL_JS + 'ventas/estadoAsientos?viaje_id=' + VIAJE_ID)
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;
                data.asientos.forEach(function(asiento) {
                    const el = document.querySelector(`[data-asiento-id="${asiento.id}"]`);
                    if (!el || el.classList.contains('seleccionado')) return;

                    el.classList.remove('disponible', 'ocupado', 'reservado');
                    el.classList.add(asiento.estado.toLowerCase());
                    el.dataset.estado = asiento.estado;

                    if (asiento.estado !== 'DISPONIBLE') {
                        el.style.pointerEvents = 'none';
                        el.style.opacity = asiento.estado === 'OCUPADO' ? '0.7' : '1';
                    } else {
                        el.style.pointerEvents = '';
                        el.style.opacity = '';
                    }
                });
            })
            .catch(() => {});
    }

    // ============================================================
    // 7. TIMER DE RESERVA
    // ============================================================
    function iniciarTimerReserva(asientoId) {
        const badge = document.getElementById('reservaTimer');
        const bar = document.getElementById('timerProgressBar');
        if (!badge) return;

        if (segundosRestantes <= 0 || segundosRestantes > TIMEOUT_RESERVA_SEG) {
            segundosRestantes = TIMEOUT_RESERVA_SEG;
        }
        avisoMostrado = false;

        if (timerInterval) clearInterval(timerInterval);

        function actualizar() {
            const min = Math.floor(segundosRestantes / 60);
            const seg = segundosRestantes % 60;
            badge.textContent = `${min}:${seg.toString().padStart(2, '0')}`;

            if (bar) {
                const pct = (segundosRestantes / TIMEOUT_RESERVA_SEG) * 100;
                bar.style.width = pct + '%';
            }

            if (!avisoMostrado && segundosRestantes <= AVISO_PRE_EXPIRACION_SEG && segundosRestantes > 0) {
                avisoMostrado = true;
                mostrarToast('⚠️ Tu reserva expira en 30 segundos', 'warning', 5000);
            }

            if (segundosRestantes <= 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                mostrarToast('⏰ Tu reserva ha expirado', 'warning', 5000);
                setTimeout(() => {
                    window.location.href = BASE_URL_JS + 'ventas/create?buscar=1&viaje_id=' + VIAJE_ID;
                }, 2000);
                return;
            }
            segundosRestantes--;
        }
        actualizar();
        timerInterval = setInterval(actualizar, 1000);
    }

    // ============================================================
    // 8. TIMER INACTIVIDAD — también con SweetAlert2
    // ============================================================
    function iniciarTimerInactividad() {
        if (!reservaActualId) return;
        segundosInactividad = 0;
        if (inactividadInterval) clearInterval(inactividadInterval);
        inactividadInterval = setInterval(function() {
            if (!reservaActualId) { clearInterval(inactividadInterval); inactividadInterval = null; return; }
            if (ventaEnProceso) return;
            segundosInactividad++;
            if (segundosInactividad >= TIMEOUT_INACTIVIDAD_SEG) {
                clearInterval(inactividadInterval);
                inactividadInterval = null;

                // ✅ SweetAlert2 en lugar de confirm() nativo
                if (typeof Swal !== 'undefined') {
                    const el = document.querySelector(`[data-asiento-id="${reservaActualId}"]`);
                    const numAsiento = el ? el.dataset.asiento : '';

                    Swal.fire({
                        title: '💤 ¿Sigues ahí?',
                        html: `
                            <div class="text-start small">
                                <p class="mb-2">No has interactuado en <strong>2 minutos</strong>.</p>
                                <p class="mb-0">El asiento <strong class="text-primary">N° ${escapeHtmlVenta(numAsiento)}</strong> será liberado automáticamente.</p>
                            </div>
                        `,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-unlock me-1"></i>Sí, liberar',
                        cancelButtonText: '<i class="fas fa-hand-paper me-1"></i>Mantener',
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#198754',
                        reverseButtons: true,
                        timer: 30000,
                        timerProgressBar: true
                    }).then((result) => {
                        if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                            liberarAsientoPropio(reservaActualId, 'Inactividad');
                        } else {
                            segundosInactividad = 0;
                            iniciarTimerInactividad();
                        }
                    });
                } else {
                    // Fallback nativo
                    if (confirm('No has interactuado en 2 minutos. ¿Liberar el asiento?')) {
                        liberarAsientoPropio(reservaActualId, 'Inactividad');
                    } else {
                        segundosInactividad = 0;
                        iniciarTimerInactividad();
                    }
                }
            }
        }, 1000);
    }

    function resetearInactividad() {
        segundosInactividad = 0;
        if (!inactividadInterval && reservaActualId) iniciarTimerInactividad();
    }

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(evt) {
        document.addEventListener(evt, resetearInactividad, { passive: true });
    });

    // ============================================================
    // 9. TIMER BLUR
    // ============================================================
    document.addEventListener('visibilitychange', function() {
        if (!reservaActualId || ventaEnProceso) return;
        if (document.hidden) {
            blurStart = Date.now();
            if (blurInterval) clearInterval(blurInterval);
            blurInterval = setInterval(function() {
                if (!document.hidden || !reservaActualId) { clearInterval(blurInterval); blurInterval = null; return; }
                if (Math.floor((Date.now() - blurStart) / 1000) >= TIMEOUT_BLUR_SEG) {
                    clearInterval(blurInterval);
                    blurInterval = null;
                    liberarAsientoPropio(reservaActualId, 'Ausencia prolongada');
                }
            }, 1000);
        } else {
            if (blurInterval) { clearInterval(blurInterval); blurInterval = null; }
            blurStart = null;
        }
    });

    // ============================================================
    // 10. ARRANCAR TIMERS
    // ============================================================
    if (reservaActualId) {
        iniciarTimerReserva(reservaActualId);
        iniciarTimerInactividad();
    }

    // ============================================================
    // 11. PAUSAR AUTO-REFRESH AL ESCRIBIR
    // ============================================================
    document.querySelectorAll('#formVenta input, #formVenta select, #formVenta textarea')
        .forEach(function(input) {
            input.addEventListener('focus', pausarAutoRefresh);
            input.addEventListener('blur', iniciarAutoRefresh);
        });
    iniciarAutoRefresh();

    // ============================================================
    // 12. VALIDACIONES
    // ============================================================
    if (typeof aplicarValidacionDocumento === 'function') {
        aplicarValidacionDocumento('dniInput', 'tipo_documento');
    }
    if (typeof aplicarSoloLetras === 'function') {
        aplicarSoloLetras('nombreInput');
        aplicarSoloLetras('apellidoInput');
    }
    if (typeof aplicarTelefono === 'function') {
        aplicarTelefono('telefonoInput', 9);
    }
    if (typeof aplicarValidacionEmail === 'function') {
        aplicarValidacionEmail('emailInput');
    }

    // ============================================================
    // 13. CONFIRMAR VENTA
    // ============================================================
    const formVenta = document.getElementById('formVenta');
    if (formVenta) {
        formVenta.addEventListener('submit', function(e) {
            e.preventDefault();
            const formRef = this;

            const asientoId = formRef.querySelector('[name="asiento_id"]').value;
            const dniInput = document.getElementById('dniInput');
            const nombre = document.getElementById('nombreInput').value.trim();
            const apellido = document.getElementById('apellidoInput').value.trim();
            const email = document.getElementById('emailInput').value.trim();
            const precio = '<?= number_format($viaje_seleccionado['precio_final'] ?? 0, 2) ?>';

            const errores = [];
            if (!asientoId) errores.push('Debe seleccionar un asiento antes de continuar');
            if (dniInput && typeof dniInput.validarDocumento === 'function' && !dniInput.validarDocumento()) {
                errores.push('El número de documento es inválido o está vacío');
            }
            if (!nombre) errores.push('El nombre del pasajero es obligatorio');
            if (!apellido) errores.push('El apellido del pasajero es obligatorio');
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errores.push('El email ingresado no es válido');
            }

            if (errores.length > 0) {
                if (typeof mostrarErroresValidacion === 'function') {
                    mostrarErroresValidacion(errores, 'Revise los datos del pasajero');
                } else {
                    alert(errores.join('\n'));
                }
                return false;
            }

            const numAsiento = document.querySelector('.asiento-destacado .num-grande')?.textContent || '';

            Swal.fire({
                title: '¿Confirmar venta?',
                html: `<div class="text-start small">
                    <p class="mb-1"><strong>Pasajero:</strong> ${escapeHtmlVenta(nombre)} ${escapeHtmlVenta(apellido)}</p>
                    <p class="mb-1"><strong>Ruta:</strong> <?= htmlspecialchars(($viaje_seleccionado['origen'] ?? '') . ' → ' . ($viaje_seleccionado['destino'] ?? '')) ?></p>
                    <p class="mb-1"><strong>Asiento:</strong> ${escapeHtmlVenta(numAsiento)}</p>
                    <p class="mb-0"><strong>Total:</strong> <span class="text-success">S/ ${precio}</span></p>
                    <hr class="my-2">
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>Si cancelas, el asiento se liberará.
                    </p>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-check me-1"></i>Sí, vender',
                cancelButtonText: 'Cancelar y liberar',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.isConfirmed) {
                    ventaEnProceso = true;
                    detenerTimers();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Procesando venta...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    }
                    reservaActualId = null;
                    formRef.submit();
                } else {
                    if (reservaActualId) liberarAsientoPropio(reservaActualId, 'Venta cancelada');
                }
            });
        });
    }
});

// ============================================================
// BUSCAR CLIENTE POR DNI
// ============================================================
function buscarCliente() {
    const dni = document.getElementById('dniInput').value;
    if (dni.length < 6) {
        mostrarToast('Ingrese un DNI válido', 'warning');
        return;
    }
    fetch('<?= BASE_URL ?>clientes/buscarPorDni?dni=' + dni)
        .then(res => res.json())
        .then(data => {
            if (data.error || !data.numero_documento) {
                mostrarToast('Cliente no encontrado. Complete los datos.', 'info');
            } else {
                document.getElementById('nombreInput').value = data.nombre || '';
                document.getElementById('apellidoInput').value = data.apellido || '';
                document.getElementById('telefonoInput').value = data.telefono || '';
                document.getElementById('emailInput').value = data.email || '';
                document.getElementById('dniInput').value = data.numero_documento || '';
                mostrarToast('✅ Cliente encontrado', 'success');
            }
        })
        .catch(() => mostrarToast('Error de conexión', 'error'));
}

// ============================================================
// UTILIDAD: escapeHtml
// ============================================================
function escapeHtmlVenta(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>