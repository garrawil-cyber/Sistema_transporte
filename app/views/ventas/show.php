<?php
// ============================================================
// HELPERS LOCALES SEGUROS
// ============================================================

// Estado → badge
$estado_badge = [
    'ACTIVA'  => ['success',   'fa-check-circle', 'Activa'],
    'ANULADA' => ['danger',    'fa-ban',          'Anulada'],
    'USADA'   => ['secondary', 'fa-history',      'Usada']
];
$eb = $estado_badge[$venta['estado'] ?? ''] ?? ['secondary', 'fa-question', $venta['estado'] ?? '—'];

// Método de pago → badge
$metodo_pago = $venta['metodo_pago'] ?? 'OTRO';
$badge_metodo = [
    'EFECTIVO'      => 'success',
    'TARJETA'       => 'info',
    'TRANSFERENCIA' => 'primary',
    'OTRO'          => 'secondary'
][$metodo_pago] ?? 'secondary';

// ✅ FIX: Cargar comprobantes con protección
$comprobantes = [];
$tiene_comprobante = false;
try {
    if (class_exists('FacturaElectronica')) {
        $facturaModel = new FacturaElectronica();
        if (method_exists($facturaModel, 'getByVentaTodos')) {
            $comprobantes = $facturaModel->getByVentaTodos($venta['id']);
            $tiene_comprobante = !empty($comprobantes);
        }
    }
} catch (Exception $e) {
    if (defined('DEBUG') && DEBUG) {
        error_log("Error cargando comprobantes en ventas/show: " . $e->getMessage());
    }
}

// ✅ FIX: Modo simulación
$modo_simulacion = defined('FACTURACION_MODO_SIMULACION') && FACTURACION_MODO_SIMULACION;

// ✅ FIX: Datos para el JS (evitar inyección)
$anular_data = [
    'id'          => (int)$venta['id'],
    'comprobante' => $venta['numero_comprobante'] ?? '',
    'pasajero'    => trim(($venta['cliente_nombre'] ?? '') . ' ' . ($venta['cliente_apellido'] ?? '')),
    'total'       => (float)($venta['precio_pagado'] ?? 0),
    'asiento'     => $venta['numero_asiento'] ?? ''
];
?>

<!-- ============================================================
     HEADER
     ============================================================ -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-receipt text-primary me-2"></i>Detalle de Venta
        </h1>
        <p class="text-muted mb-0 small">
            Comprobante <strong><?= htmlspecialchars($venta['numero_comprobante'] ?? '—') ?></strong>
            · <?= !empty($venta['fecha_venta'])
                ? date('d/m/Y H:i', strtotime($venta['fecha_venta']))
                : '—' ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>ventas" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
        <a href="<?= BASE_URL ?>ventas/ticket/<?= (int)$venta['id'] ?>"
           target="_blank" class="btn btn-outline-dark">
            <i class="fas fa-print me-1"></i>Imprimir Ticket
        </a>
        <?php if (($venta['estado'] ?? '') === 'ACTIVA'): ?>
            <button type="button" class="btn btn-outline-danger"
                    onclick='confirmarAnular(<?= json_encode($anular_data, JSON_UNESCAPED_UNICODE) ?>)'>
                <i class="fas fa-ban me-1"></i>Anular
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     ESTADO
     ============================================================ -->
<div class="alert alert-<?= $eb[0] ?> border-0 shadow-sm d-flex align-items-center">
    <i class="fas <?= $eb[1] ?> fa-2x me-3"></i>
    <div>
        <strong>Estado: <?= htmlspecialchars($eb[2]) ?></strong>
        <?php if (($venta['estado'] ?? '') === 'ANULADA'): ?>
            <br><small>Esta venta fue anulada y el asiento fue liberado.</small>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <!-- ============================================================
         COLUMNA IZQUIERDA: DATOS
         ============================================================ -->
    <div class="col-lg-8">

        <!-- Card: Pasajero -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-info text-white">
                <i class="fas fa-user me-2"></i>Datos del Pasajero
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Nombre completo</small>
                        <strong>
                            <?= htmlspecialchars(trim(($venta['cliente_nombre'] ?? '') . ' ' . ($venta['cliente_apellido'] ?? ''))) ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Documento</small>
                        <strong>
                            <?= htmlspecialchars(($venta['tipo_documento'] ?? '') . ': ' . ($venta['numero_documento'] ?? '')) ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Teléfono</small>
                        <strong><?= htmlspecialchars($venta['telefono'] ?: '—') ?></strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email</small>
                        <strong><?= htmlspecialchars($venta['email'] ?: '—') ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Viaje -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white">
                <i class="fas fa-bus me-2"></i>Datos del Viaje
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Ruta</small>
                        <strong>
                            <?= htmlspecialchars($venta['origen'] ?? '—') ?>
                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                            <?= htmlspecialchars($venta['destino'] ?? '—') ?>
                        </strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Asiento</small>
                        <span class="badge bg-primary fs-6">
                            <i class="fas fa-chair me-1"></i><?= htmlspecialchars($venta['numero_asiento'] ?? '—') ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Bus</small>
                        <strong><?= htmlspecialchars($venta['placa'] ?? '—') ?></strong>
                        <br><small class="text-muted"><?= htmlspecialchars($venta['tipo_servicio'] ?? '') ?></small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Fecha de salida</small>
                        <strong>
                            <i class="fas fa-calendar-day me-1 text-muted"></i>
                            <?= !empty($venta['fecha_salida'])
                                ? date('d/m/Y H:i', strtotime($venta['fecha_salida']))
                                : '—' ?>
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Fecha de llegada</small>
                        <strong>
                            <i class="fas fa-calendar-check me-1 text-muted"></i>
                            <?= !empty($venta['fecha_llegada'])
                                ? date('d/m/Y H:i', strtotime($venta['fecha_llegada']))
                                : '—' ?>
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card: Vendedor -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-user-tie me-2"></i>Vendedor
            </div>
            <div class="card-body">
                <strong>
                    <?= htmlspecialchars(trim(($venta['vendedor_nombre'] ?? '') . ' ' . ($venta['vendedor_apellido'] ?? ''))) ?>
                </strong>
            </div>
        </div>

    </div>

    <!-- ============================================================
         COLUMNA DERECHA: RESUMEN
         ============================================================ -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-file-invoice-dollar me-2"></i>Resumen
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Comprobante:</span>
                    <strong><?= htmlspecialchars($venta['numero_comprobante'] ?? '—') ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Método de pago:</span>
                    <span class="badge bg-<?= $badge_metodo ?>">
                        <?= htmlspecialchars($metodo_pago) ?>
                    </span>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">TOTAL:</span>
                    <h3 class="mb-0 text-success">
                        S/ <?= number_format((float)($venta['precio_pagado'] ?? 0), 2) ?>
                    </h3>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <i class="fas fa-bolt me-2"></i>Acciones
            </div>
            <div class="card-body d-grid gap-2">

                <!-- Imprimir -->
                <a href="<?= BASE_URL ?>ventas/ticket/<?= (int)$venta['id'] ?>"
                   target="_blank" class="btn btn-outline-dark">
                    <i class="fas fa-print me-1"></i>Imprimir Ticket
                </a>

                <!-- ✅ FIX: Reenviar Email con validación de comprobante -->
                <?php if (!empty($venta['email'])): ?>
                    <?php if ($tiene_comprobante): ?>
                        <button type="button" class="btn btn-outline-primary"
                                onclick="enviarEmail(<?= (int)$venta['id'] ?>)">
                            <i class="fas fa-envelope me-1"></i>Reenviar por Email
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary" disabled
                                title="Primero emita un comprobante electrónico (ver más abajo)">
                            <i class="fas fa-envelope me-1"></i>Sin comprobante emitido
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <button type="button" class="btn btn-outline-secondary" disabled
                            title="El cliente no tiene email registrado">
                        <i class="fas fa-envelope me-1"></i>Sin email
                    </button>
                <?php endif; ?>

                <!-- Anular -->
                <?php if (($venta['estado'] ?? '') === 'ACTIVA'): ?>
                    <button type="button" class="btn btn-outline-danger"
                            onclick='confirmarAnular(<?= json_encode($anular_data, JSON_UNESCAPED_UNICODE) ?>)'>
                        <i class="fas fa-ban me-1"></i>Anular Venta
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     FACTURACIÓN ELECTRÓNICA
     ============================================================ -->
<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-file-invoice me-2"></i>Facturación Electrónica
        </div>
        <?php if ($modo_simulacion): ?>
            <span class="badge bg-warning text-dark">
                <i class="fas fa-flask me-1"></i>Modo Simulación
            </span>
        <?php else: ?>
            <span class="badge bg-success">
                <i class="fas fa-check me-1"></i>Modo Real
            </span>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <?php if ($modo_simulacion): ?>
            <div class="alert alert-warning mb-3">
                <i class="fas fa-info-circle me-2"></i>
                <small>
                    Los comprobantes se generarán localmente sin enviarse a SUNAT.
                    Útil para pruebas y desarrollo.
                </small>
            </div>
        <?php endif; ?>

        <?php if ($tiene_comprobante): ?>
            <h6 class="text-muted mb-3">Comprobantes emitidos:</h6>
            <div class="table-responsive table-responsive-mobile">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tipo</th>
                            <th>N° Comprobante</th>
                            <th>Fecha</th>
                            <th class="text-end">Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comprobantes as $c):
                            $badge_tipo = ($c['tipo'] ?? '') === 'FACTURA' ? 'primary' : 'info';
                            $badge_estado = [
                                'ACEPTADO'  => 'success',
                                'RECHAZADO' => 'danger',
                                'SIMULADO'  => 'warning',
                                'ANULADO'   => 'secondary'
                            ][$c['estado'] ?? ''] ?? 'secondary';
                        ?>
                        <tr>
                            <td data-label="Tipo">
                                <span class="badge bg-<?= $badge_tipo ?>">
                                    <?= htmlspecialchars($c['tipo'] ?? '—') ?>
                                </span>
                            </td>
                            <td data-label="N° Comprobante">
                                <strong><?= htmlspecialchars($c['numero_completo'] ?? '—') ?></strong>
                            </td>
                            <td data-label="Fecha">
                                <small>
                                    <?= !empty($c['fecha_emision'])
                                        ? date('d/m/Y H:i', strtotime($c['fecha_emision']))
                                        : '—' ?>
                                </small>
                            </td>
                            <td data-label="Total" class="text-end">
                                S/ <?= number_format((float)($c['total'] ?? 0), 2) ?>
                            </td>
                            <td data-label="Estado">
                                <span class="badge bg-<?= $badge_estado ?>">
                                    <?= htmlspecialchars($c['estado'] ?? '—') ?>
                                </span>
                            </td>
                            <td data-label="Acciones">
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>factura/show/<?= (int)$c['id'] ?>"
                                       class="btn btn-sm btn-outline-info" title="Ver comprobante">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!empty($venta['email'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                title="Enviar por email"
                                                onclick="enviarEmail(<?= (int)$venta['id'] ?>)">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Esta venta aún no tiene comprobantes electrónicos emitidos.
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <form method="POST"
                      action="<?= BASE_URL ?>factura/emitir/<?= (int)$venta['id'] ?>"
                      class="d-inline">
                    <button type="submit" name="tipo" value="BOLETA" class="btn btn-info">
                        <i class="fas fa-receipt me-1"></i>Emitir Boleta
                    </button>
                </form>

                <?php if (!empty($venta['numero_documento']) && strlen($venta['numero_documento']) == 11): ?>
                    <form method="POST"
                          action="<?= BASE_URL ?>factura/emitir/<?= (int)$venta['id'] ?>"
                          class="d-inline">
                        <button type="submit" name="tipo" value="FACTURA" class="btn btn-primary">
                            <i class="fas fa-file-invoice me-1"></i>Emitir Factura
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================================
     MENSAJE DE COMPROBANTE ENVIADO
     ============================================================ -->
<?php if (isset($_SESSION['comprobante_enviado']) && $_SESSION['comprobante_enviado']): ?>
    <div class="alert alert-info d-flex align-items-center mt-3">
        <i class="fas fa-envelope-circle-check fa-2x me-3"></i>
        <div>
            <strong>Comprobante enviado por email</strong>
            <br>
            <small>El cliente ha recibido su comprobante electrónico</small>
        </div>
    </div>
    <?php unset($_SESSION['comprobante_enviado']); ?>
<?php endif; ?>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->
<script>
// ============================================================
// ANULAR VENTA
// ============================================================
function confirmarAnular(venta) {
    Swal.fire({
        title: '¿Anular esta venta?',
        html: `
            <div class="text-start">
                <p class="mb-1"><strong>Comprobante:</strong> ${escapeHtml(venta.comprobante)}</p>
                <p class="mb-1"><strong>Pasajero:</strong> ${escapeHtml(venta.pasajero)}</p>
                <p class="mb-1"><strong>Total:</strong> S/ ${venta.total.toFixed(2)}</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    El asiento <strong>${escapeHtml(venta.asiento)}</strong> será liberado.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-ban me-1"></i>Sí, anular',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Anulando venta...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            window.location.href = '<?= BASE_URL ?>ventas/anular/' + venta.id;
        }
    });
}

// ============================================================
// ENVIAR EMAIL (async/await con timeout y manejo robusto)
// ============================================================
async function enviarEmail(ventaId) {
    Swal.fire({
        title: 'Enviando comprobante...',
        html: 'Por favor espera',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 20000);

        const res = await fetch(`<?= BASE_URL ?>ventas/enviarEmail/${ventaId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        // Validar que la respuesta sea JSON
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await res.text();
            console.error('Respuesta no JSON:', text.substring(0, 300));
            throw new Error('El servidor devolvió una respuesta inesperada');
        }

        const data = await res.json();

        if (data.success) {
            Swal.fire({
                title: '¡Enviado!',
                text: data.message || 'Comprobante enviado correctamente',
                icon: 'success',
                timer: 2500,
                timerProgressBar: true
            });
        } else {
            Swal.fire({
                title: 'No se pudo enviar',
                text: data.message || 'Error desconocido',
                icon: 'error'
            });
        }

    } catch (err) {
        if (err.name === 'AbortError') {
            Swal.fire('Tiempo agotado', 'El servidor tardó demasiado en responder', 'warning');
        } else {
            console.error('Error enviando email:', err);
            Swal.fire('Error', err.message || 'Error de conexión', 'error');
        }
    }
}

// ============================================================
// UTILIDAD: Escapar HTML en strings interpolados
// ============================================================
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>