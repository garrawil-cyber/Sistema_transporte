<?php
// Calcular duración de la sesión
$fecha_apertura = new DateTime($resumen['caja']['fecha_apertura']);
$ahora = new DateTime();
$duracion = $fecha_apertura->diff($ahora);
$duracion_texto = '';
if ($duracion->h > 0) $duracion_texto .= $duracion->h . 'h ';
$duracion_texto .= $duracion->i . 'min';

// Calcular porcentaje de efectivo vs total
$total_ingresos = $resumen['ventas']['EFECTIVO'] + $resumen['ventas']['TARJETA'] + $resumen['ventas']['TRANSFERENCIA'];
$porcentaje_efectivo = $total_ingresos > 0 ? ($resumen['ventas']['EFECTIVO'] / $total_ingresos) * 100 : 0;
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-cash-register text-success me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">
            <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['nombre']; ?>
            <span class="mx-2">|</span>
            <i class="fas fa-clock me-1"></i>Sesión activa: <?php echo $duracion_texto; ?>
        </p>
    </div>
    <div>
        <a href="<?php echo BASE_URL; ?>caja/cerrar" class="btn btn-danger">
            <i class="fas fa-lock me-1"></i>Cerrar Caja
        </a>
    </div>
</div>

<!-- ============================================================
     ESTADO DE LA CAJA
     ============================================================ -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient bg-success text-white h-100">
            <div class="card-body position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-white-50 text-uppercase">Estado</small>
                        <h4 class="mb-1 mt-1">
                            <i class="fas fa-circle fa-xs me-1"></i>ABIERTA
                        </h4>
                        <small class="text-white-50">
                            <i class="fas fa-clock me-1"></i><?php echo $duracion_texto; ?>
                        </small>
                    </div>
                    <i class="fas fa-lock-open fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient bg-primary text-white h-100">
            <div class="card-body position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-white-50 text-uppercase">Monto Inicial</small>
                        <h3 class="mb-1 mt-1">S/ <?php echo number_format($resumen['caja']['monto_inicial'], 2); ?></h3>
                        <small class="text-white-50">
                            <i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($resumen['caja']['fecha_apertura'])); ?>
                        </small>
                    </div>
                    <i class="fas fa-wallet fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient bg-info text-white h-100">
            <div class="card-body position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-white-50 text-uppercase">Total Ingresos</small>
                        <h3 class="mb-1 mt-1">S/ <?php echo number_format($resumen['total_ingresos'], 2); ?></h3>
                        <small class="text-white-50">
                            <i class="fas fa-arrow-down me-1"></i>Movimientos manuales
                        </small>
                    </div>
                    <i class="fas fa-arrow-down fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-gradient bg-warning text-dark h-100">
            <div class="card-body position-relative">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <small class="text-dark-50 text-uppercase">Monto en Caja</small>
                        <h3 class="mb-1 mt-1">S/ <?php echo number_format($resumen['monto_final_sistema'], 2); ?></h3>
                        <small class="text-dark-50">
                            <i class="fas fa-calculator me-1"></i>Actual
                        </small>
                    </div>
                    <i class="fas fa-cash-register fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     ALERTA DE MOVIMIENTOS ALTOS
     ============================================================ -->
<?php 
$movimientos_altos = array_filter($movimientos ?? [], function($m) {
    return $m['monto'] >= 100 && $m['tipo'] == 'EGRESO';
});
if (!empty($movimientos_altos)): 
?>
<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
    <div>
        <strong>Atención:</strong> Hay <?php echo count($movimientos_altos); ?> egreso(s) superior(es) a S/ 100.00. Verifique que estén justificados.
    </div>
</div>
<?php endif; ?>

<!-- ============================================================
     RESUMEN POR MÉTODO DE PAGO
     ============================================================ -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success">
                        <?php echo number_format($porcentaje_efectivo, 1); ?>%
                    </span>
                </div>
                <small class="text-muted d-block">Ventas en Efectivo</small>
                <h4 class="text-success mb-0">S/ <?php echo number_format($resumen['ventas']['EFECTIVO'], 2); ?></h4>
                <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: <?php echo $porcentaje_efectivo; ?>%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 mb-3 d-inline-block">
                    <i class="fas fa-credit-card fa-2x text-info"></i>
                </div>
                <small class="text-muted d-block">Ventas con Tarjeta</small>
                <h4 class="text-info mb-0">S/ <?php echo number_format($resumen['ventas']['TARJETA'], 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 mb-3 d-inline-block">
                    <i class="fas fa-exchange-alt fa-2x text-primary"></i>
                </div>
                <small class="text-muted d-block">Transferencias</small>
                <h4 class="text-primary mb-0">S/ <?php echo number_format($resumen['ventas']['TRANSFERENCIA'], 2); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3 mb-3 d-inline-block">
                    <i class="fas fa-box fa-2x text-warning"></i>
                </div>
                <small class="text-muted d-block">Encomiendas + Giros</small>
                <h4 class="text-warning mb-0">
                    S/ <?php echo number_format($resumen['encomiendas']['total'] + $resumen['giros']['total'], 2); ?>
                </h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- ============================================================
         COLUMNA IZQUIERDA: REGISTRAR MOVIMIENTO
         ============================================================ -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus-circle me-2"></i>Registrar Movimiento
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>caja/movimiento" id="formMovimiento">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de Movimiento *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo" id="tipoIngreso" value="INGRESO" checked>
                            <label class="btn btn-outline-success py-3" for="tipoIngreso">
                                <i class="fas fa-arrow-down fa-2x d-block mb-1"></i>
                                <strong>INGRESO</strong>
                            </label>
                            
                            <input type="radio" class="btn-check" name="tipo" id="tipoEgreso" value="EGRESO">
                            <label class="btn btn-outline-danger py-3" for="tipoEgreso">
                                <i class="fas fa-arrow-up fa-2x d-block mb-1"></i>
                                <strong>EGRESO</strong>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment me-1"></i>Concepto *
                        </label>
                        <input type="text" name="concepto" class="form-control" 
                               placeholder="Ej: Pago de movilidad, Venta adicional..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-money-bill-wave me-1"></i>Monto *
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-success text-white">S/</span>
                            <input type="number" step="0.01" name="monto" class="form-control text-center fw-bold" 
                                   placeholder="0.00" required min="0.01">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Método</label>
                            <select name="metodo_pago" class="form-select form-select-sm">
                                <option value="EFECTIVO">💵 Efectivo</option>
                                <option value="TARJETA">💳 Tarjeta</option>
                                <option value="TRANSFERENCIA">🏦 Transferencia</option>
                                <option value="OTRO">📝 Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Referencia</label>
                            <input type="text" name="referencia" class="form-control form-control-sm" 
                                   placeholder="Recibo #123">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i>Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================
         COLUMNA DERECHA: TABS VENTAS / MOVIMIENTOS
         ============================================================ -->
    <div class="col-lg-7">
        
        <!-- TABS -->
        <ul class="nav nav-tabs nav-fill mb-2" id="tabsPanel" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" 
                        id="tab-ventas" 
                        data-bs-toggle="tab" 
                        data-bs-target="#content-ventas" 
                        type="button" 
                        role="tab">
                    <i class="fas fa-ticket-alt me-1"></i>Ventas del Día
                    <span class="badge bg-success ms-1"><?php echo $resumen['total_ventas_count']; ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" 
                        id="tab-movimientos" 
                        data-bs-toggle="tab" 
                        data-bs-target="#content-movimientos" 
                        type="button" 
                        role="tab">
                    <i class="fas fa-exchange-alt me-1"></i>Movimientos
                    <span class="badge bg-secondary ms-1"><?php echo count($movimientos ?? []); ?></span>
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            
            <!-- ============================================================
                 TAB 1: VENTAS DEL DÍA
                 ============================================================ -->
            <div class="tab-pane show active" id="content-ventas" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-ticket-alt me-2"></i>Ventas del Día
                            <span class="badge bg-light text-dark ms-2"><?php echo count($ventas_del_dia ?? []); ?></span>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-money-bill-wave me-1"></i>
                                S/ <?php echo number_format($resumen['total_ventas_monto'], 2); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 550px; overflow-y: auto;">
                        <?php if (!empty($ventas_del_dia)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ventas_del_dia as $v): ?>
                                <div class="list-group-item py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-2 me-3">
                                                <i class="fas fa-ticket-alt text-success"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block">
                                                    <?php echo htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']); ?>
                                                </strong>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?php echo date('H:i', strtotime($v['fecha_venta'])); ?>
                                                    <span class="mx-1">|</span>
                                                    <i class="fas fa-chair me-1"></i>Asiento <?php echo $v['numero_asiento']; ?>
                                                    <span class="mx-1">|</span>
                                                    <i class="fas fa-receipt me-1"></i><?php echo $v['numero_comprobante']; ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-route me-1"></i>
                                                    <?php echo htmlspecialchars($v['origen'] . ' → ' . $v['destino']); ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-0 text-success">
                                                S/ <?php echo number_format($v['precio_pagado'], 2); ?>
                                            </h6>
                                            <span class="badge bg-<?php echo $v['metodo_pago'] === 'EFECTIVO' ? 'success' : 'info'; ?> small">
                                                <?php echo $v['metodo_pago']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-ticket-alt fa-4x mb-3 d-block opacity-25"></i>
                                <h5>Sin ventas registradas</h5>
                                <p class="small mb-0">Las ventas del día aparecerán aquí</p>
                                <a href="<?php echo BASE_URL; ?>ventas/create" class="btn btn-sm btn-success mt-3">
                                    <i class="fas fa-plus me-1"></i>Registrar Venta
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- ============================================================
                 TAB 2: MOVIMIENTOS MANUALES
                 ============================================================ -->
            <div class="tab-pane" id="content-movimientos" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>Movimientos del Día
                            <span class="badge bg-light text-dark ms-2"><?php echo count($movimientos ?? []); ?></span>
                        </div>
                        <div>
                            <span class="badge bg-success">
                                <i class="fas fa-arrow-down me-1"></i>S/ <?php echo number_format($resumen['total_ingresos'], 2); ?>
                            </span>
                            <span class="badge bg-danger ms-1">
                                <i class="fas fa-arrow-up me-1"></i>S/ <?php echo number_format($resumen['total_egresos'], 2); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 550px; overflow-y: auto;">
                        <?php if (!empty($movimientos)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($movimientos as $m): ?>
                                <div class="list-group-item py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-<?php echo $m['tipo'] == 'INGRESO' ? 'success' : 'danger'; ?> bg-opacity-10 p-2 me-3">
                                                <i class="fas fa-arrow-<?php echo $m['tipo'] == 'INGRESO' ? 'down' : 'up'; ?> text-<?php echo $m['tipo'] == 'INGRESO' ? 'success' : 'danger'; ?>"></i>
                                            </div>
                                            <div>
                                                <strong class="d-block">
                                                    <?php echo htmlspecialchars($m['concepto']); ?>
                                                </strong>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i><?php echo date('H:i', strtotime($m['fecha'])); ?>
                                                    <?php if (!empty($m['referencia'])): ?>
                                                        <span class="mx-1">|</span>
                                                        <i class="fas fa-hashtag me-1"></i><?php echo htmlspecialchars($m['referencia']); ?>
                                                    <?php endif; ?>
                                                    <span class="mx-1">|</span>
                                                    <?php 
                                                        $iconos_metodo = [
                                                            'EFECTIVO' => '💵',
                                                            'TARJETA' => '💳',
                                                            'TRANSFERENCIA' => '🏦',
                                                            'OTRO' => '📝'
                                                        ];
                                                        echo ($iconos_metodo[$m['metodo_pago']] ?? '') . ' ' . $m['metodo_pago'];
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <h5 class="mb-0 text-nowrap <?php echo $m['tipo'] == 'INGRESO' ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $m['tipo'] == 'INGRESO' ? '+' : '-'; ?>S/ <?php echo number_format($m['monto'], 2); ?>
                                            </h5>
                                            <a href="#" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirmarEliminar('<?php echo BASE_URL; ?>caja/eliminarMovimiento/<?php echo $m['id']; ?>', 'movimiento: <strong><?php echo htmlspecialchars($m['concepto']); ?></strong> por S/ <?php echo number_format($m['monto'], 2); ?>')"
                                               title="Eliminar movimiento">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-4x mb-3 d-block opacity-25"></i>
                                <h5>Sin movimientos registrados</h5>
                                <p class="small mb-0">Los movimientos manuales aparecerán aquí</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- ============================================================
     RESUMEN FINANCIERO DEL DÍA
     ============================================================ -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-calculator me-2"></i>Resumen Financiero del Día
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 border-end">
                        <small class="text-muted d-block mb-1">Monto Inicial</small>
                        <h5 class="text-primary">S/ <?php echo number_format($resumen['caja']['monto_inicial'], 2); ?></h5>
                    </div>
                    <div class="col-md-2 border-end">
                        <small class="text-muted d-block mb-1">+ Ventas Efectivo</small>
                        <h5 class="text-success">S/ <?php echo number_format($resumen['ventas']['EFECTIVO'], 2); ?></h5>
                    </div>
                    <div class="col-md-2 border-end">
                        <small class="text-muted d-block mb-1">+ Encomiendas</small>
                        <h5 class="text-success">S/ <?php echo number_format($resumen['encomiendas']['total'], 2); ?></h5>
                    </div>
                    <div class="col-md-2 border-end">
                        <small class="text-muted d-block mb-1">+ Comisiones Giros</small>
                        <h5 class="text-success">S/ <?php echo number_format($resumen['giros']['total'], 2); ?></h5>
                    </div>
                    <div class="col-md-2 border-end">
                        <small class="text-muted d-block mb-1">+ Ingresos</small>
                        <h5 class="text-success">S/ <?php echo number_format($resumen['total_ingresos'], 2); ?></h5>
                    </div>
                    <div class="col-md-2">
                        <small class="text-muted d-block mb-1">- Egresos</small>
                        <h5 class="text-danger">S/ <?php echo number_format($resumen['total_egresos'], 2); ?></h5>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-12 text-center">
                        <small class="text-muted">MONTO ACTUAL EN CAJA</small>
                        <h2 class="text-warning mb-0">S/ <?php echo number_format($resumen['monto_final_sistema'], 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Confirmación de registrar movimiento
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formMovimiento');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const tipo = document.querySelector('input[name="tipo"]:checked').value;
        const concepto = document.querySelector('input[name="concepto"]').value;
        const monto = document.querySelector('input[name="monto"]').value;
        
        if (!concepto || !monto) {
            mostrarToast('Complete todos los campos', 'warning');
            return false;
        }
        
        const color = tipo === 'INGRESO' ? '#198754' : '#dc3545';
        const icono = tipo === 'INGRESO' ? 'arrow-down' : 'arrow-up';
        const formRef = this;
        
        Swal.fire({
            title: `¿Registrar ${tipo.toLowerCase()}?`,
            html: `
                <div class="text-start">
                    <p><strong>Concepto:</strong> ${concepto}</p>
                    <p><strong>Monto:</strong> <span class="text-${tipo === 'INGRESO' ? 'success' : 'danger'}">S/ ${monto}</span></p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `<i class="fas fa-${icono} me-1"></i>Sí, registrar`,
            cancelButtonText: 'Revisar',
            confirmButtonColor: color
        }).then((result) => {
            if (result.isConfirmed) {
                formRef.submit();
            }
        });
    });
});
</script>
