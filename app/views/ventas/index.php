<?php
// ============================================================
// PREPARACIÓN DE DATOS
// ============================================================

// Datos para el mini-gráfico (últimos 7 días)
$ventas_por_dia = [];
$hoy = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $fecha = (clone $hoy)->modify("-{$i} days")->format('Y-m-d');
    $ventas_por_dia[$fecha] = [
        'cantidad' => 0,
        'monto'    => 0.0,
        'label'    => (clone $hoy)->modify("-{$i} days")->format('d/m'),
        'dia'      => (clone $hoy)->modify("-{$i} days")->format('D')
    ];
}

// Si hay ventas en los datos actuales, calcular el conteo por día
foreach ($ventas ?? [] as $v) {
    $fecha = date('Y-m-d', strtotime($v['fecha_venta'] ?? ''));
    if (isset($ventas_por_dia[$fecha])) {
        $ventas_por_dia[$fecha]['cantidad']++;
        $ventas_por_dia[$fecha]['monto'] += (float)($v['precio_pagado'] ?? 0);
    }
}

$max_cantidad = max(1, max(array_column($ventas_por_dia, 'cantidad')));

// ¿Hay filtros activos?
$filtros_activos = !empty($filtros['buscar'])
    || !empty($filtros['estado'])
    || !empty($filtros['fecha_inicio'])
    || !empty($filtros['fecha_fin']);
?>

<div class="container-fluid px-3 px-md-4 py-3">

    <!-- ═══════════════════════════════════════════════════════════
         HEADER
         ═══════════════════════════════════════════════════════════ -->
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4 pb-3 border-bottom no-print">
        <div>
            <h1 class="h3 mb-1 d-flex align-items-center">
                <span class="bg-primary bg-opacity-10 rounded-3 p-2 me-3 d-inline-flex">
                    <i class="fas fa-ticket-alt text-primary fa-lg"></i>
                </span>
                <span><?= htmlspecialchars($titulo ?? 'Historial de Ventas') ?></span>
            </h1>
            <p class="text-muted mb-0 small ms-5 ps-2">
                <i class="fas fa-info-circle me-1"></i>Historial completo de ventas con filtros avanzados
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
<button type="button"
        class="btn btn-outline-primary"
        data-bs-toggle="modal"
        data-bs-target="#modalExportarPDF"
        title="Descargar listado en PDF">
    <i class="fas fa-file-pdf me-1"></i>Exportar PDF
</button>
            <a href="<?= BASE_URL ?>ventas/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Nueva Venta
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         HEADER PARA IMPRESIÓN (solo visible al imprimir)
         ═══════════════════════════════════════════════════════════ -->
    <div class="print-only mb-4">
        <div class="text-center mb-3 pb-3 border-bottom">
            <h2 class="mb-1">
                <i class="fas fa-bus me-2"></i>
                <?php
                $empresa = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'Sistema de Transporte';
                echo htmlspecialchars($empresa);
                ?>
            </h2>
            <p class="text-muted mb-0 small">
                <?php if (defined('FACTURACION_RUC')): ?>
                    RUC: <?= htmlspecialchars(FACTURACION_RUC) ?> |
                <?php endif; ?>
                <?php if (defined('FACTURACION_DIRECCION')): ?>
                    <?= htmlspecialchars(FACTURACION_DIRECCION) ?>
                <?php endif; ?>
            </p>
            <h4 class="mt-3 mb-0">LISTADO DE VENTAS</h4>
            <p class="text-muted small mb-0">
                Generado el <?= date('d/m/Y H:i:s') ?>
                <?php if ($filtros_activos): ?>
                    · <strong>Filtros aplicados:</strong>
                    <?php if (!empty($filtros['buscar'])): ?>
                        "<?= htmlspecialchars($filtros['buscar']) ?>"
                    <?php endif; ?>
                    <?php if (!empty($filtros['estado'])): ?>
                        · Estado: <?= htmlspecialchars($filtros['estado']) ?>
                    <?php endif; ?>
                    <?php if (!empty($filtros['fecha_inicio'])): ?>
                        · Desde: <?= htmlspecialchars($filtros['fecha_inicio']) ?>
                    <?php endif; ?>
                    <?php if (!empty($filtros['fecha_fin'])): ?>
                        · Hasta: <?= htmlspecialchars($filtros['fecha_fin']) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         KPIs
         ═══════════════════════════════════════════════════════════ -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4 no-print">
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Total Ventas</div>
                        <div class="kpi-value text-primary"><?= (int)($totales['total_ventas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Recaudado</div>
                        <div class="kpi-value text-success">
                            S/ <?= number_format((float)($totales['total_recaudado'] ?? 0), 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-info bg-opacity-10 text-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Activas</div>
                        <div class="kpi-value text-info"><?= (int)($totales['activas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card kpi-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="kpi-label text-muted">Anuladas</div>
                        <div class="kpi-value text-danger"><?= (int)($totales['anuladas'] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         MINI-GRÁFICO — Ventas de los últimos 7 días
         ═══════════════════════════════════════════════════════════ -->
    <div class="card card-modern mb-3 no-print">
        <div class="card-header-icon">
            <div class="icon-circle" style="background: rgba(13, 110, 253, .1); color: var(--primary);">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h3>Ventas últimos 7 días</h3>
            <span class="badge bg-light text-dark ms-auto">
                <i class="fas fa-calendar-week me-1"></i>Última semana
            </span>
        </div>
        <div class="card-body">
            <div class="mini-chart">
                <?php foreach ($ventas_por_dia as $fecha => $data):
                    $altura_pct = $max_cantidad > 0 ? ($data['cantidad'] / $max_cantidad) * 100 : 0;
                    $altura_pct = max($altura_pct, 5); // mínimo 5% para que se vea la barra
                    $es_hoy = ($fecha === date('Y-m-d'));
                    $es_fin_semana = in_array($data['dia'], ['Sat', 'Sun']);
                ?>
                    <div class="mini-chart-col">
                        <div class="mini-chart-bar-wrapper">
                            <div class="mini-chart-bar <?= $es_hoy ? 'hoy' : '' ?>"
                                 style="height: <?= $altura_pct ?>%;"
                                 title="<?= $data['cantidad'] ?> ventas · S/ <?= number_format($data['monto'], 2) ?>">
                                <?php if ($data['cantidad'] > 0): ?>
                                    <span class="mini-chart-value"><?= $data['cantidad'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mini-chart-label <?= $es_hoy ? 'hoy' : '' ?>">
                            <div class="mini-chart-day"><?= $data['dia'] === 'Sat' ? 'Sáb' : ($data['dia'] === 'Sun' ? 'Dom' : ($data['dia'] === 'Mon' ? 'Lun' : ($data['dia'] === 'Tue' ? 'Mar' : ($data['dia'] === 'Wed' ? 'Mié' : ($data['dia'] === 'Thu' ? 'Jue' : 'Vie'))))) ?></div>
                            <div class="mini-chart-date"><?= $data['label'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         FILTROS COLAPSABLES
         ═══════════════════════════════════════════════════════════ -->
    <?php
// Contar filtros activos
$filtros_activos_count = 0;
if (!empty($filtros['buscar']))       $filtros_activos_count++;
if (!empty($filtros['estado']))       $filtros_activos_count++;
if (!empty($filtros['vendedor_id']))  $filtros_activos_count++;
if (!empty($filtros['fecha_inicio'])) $filtros_activos_count++;
if (!empty($filtros['fecha_fin']))    $filtros_activos_count++;
$filtros_activos = $filtros_activos_count > 0;
?>

<!-- ═══════════════════════════════════════════════════════════
     FILTROS COLAPSABLES
     ═══════════════════════════════════════════════════════════ -->
<div class="card card-modern mb-3 no-print">
    <div class="card-header-icon"
         role="button"
         data-bs-toggle="collapse"
         data-bs-target="#filtrosCollapse"
         aria-expanded="<?= $filtros_activos ? 'true' : 'false' ?>"
         aria-controls="filtrosCollapse"
         style="cursor: pointer;">
        <div class="icon-circle"><i class="fas fa-filter"></i></div>
        <h3>Filtros de búsqueda</h3>

        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if ($filtros_activos): ?>
                <span class="badge bg-primary">
                    <i class="fas fa-check me-1"></i><?= $filtros_activos_count ?>
                    <?= $filtros_activos_count === 1 ? 'filtro activo' : 'filtros activos' ?>
                </span>
            <?php endif; ?>
            <i class="fas fa-chevron-down collapse-icon <?= $filtros_activos ? 'rotated' : '' ?>"></i>
        </div>
    </div>

    <div class="collapse <?= $filtros_activos ? 'show' : '' ?>" id="filtrosCollapse">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>ventas" class="row g-2 align-items-end" id="formFiltros">

                <!-- Buscar -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold mb-1">
                        <i class="fas fa-search me-1"></i>Buscar
                    </label>
                    <input type="text" name="buscar" class="form-control form-control-sm"
                           placeholder="Cliente, DNI o comprobante..."
                           value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                </div>

                <!-- Estado -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="fas fa-toggle-on me-1"></i>Estado
                    </label>
                    <select name="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="ACTIVA"  <?= ($filtros['estado'] ?? '') === 'ACTIVA'  ? 'selected' : '' ?>>Activa</option>
                        <option value="ANULADA" <?= ($filtros['estado'] ?? '') === 'ANULADA' ? 'selected' : '' ?>>Anulada</option>
                        <option value="USADA"   <?= ($filtros['estado'] ?? '') === 'USADA'   ? 'selected' : '' ?>>Usada</option>
                    </select>
                </div>

                <!-- ✅ NUEVO: Vendedor -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="fas fa-user-tie me-1"></i>Vendedor
                    </label>
                    <select name="vendedor_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($vendedores ?? [] as $v): ?>
                            <option value="<?= (int)$v['id'] ?>"
                                    <?= ((int)($filtros['vendedor_id'] ?? 0) === (int)$v['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v['nombre_completo']) ?>
                                (<?= (int)$v['total_ventas'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Desde -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="fas fa-calendar-alt me-1"></i>Desde
                    </label>
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '') ?>">
                </div>

                <!-- Hasta -->
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">
                        <i class="fas fa-calendar-check me-1"></i>Hasta
                    </label>
                    <input type="date" name="fecha_fin" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '') ?>">
                </div>

                <!-- Botones -->
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Aplicar filtros">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="<?= BASE_URL ?>ventas" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>

            <!-- Filtros rápidos -->
            <div class="mt-3 pt-3 border-top">
                <small class="text-muted fw-bold d-block mb-2">
                    <i class="fas fa-bolt me-1"></i>Filtros rápidos
                </small>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="aplicarFiltroRapido('hoy')">
                        <i class="fas fa-sun me-1"></i>Hoy
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="aplicarFiltroRapido('ayer')">
                        <i class="fas fa-history me-1"></i>Ayer
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="aplicarFiltroRapido('semana')">
                        <i class="fas fa-calendar-week me-1"></i>Esta semana
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="aplicarFiltroRapido('mes')">
                        <i class="fas fa-calendar-alt me-1"></i>Este mes
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="aplicarFiltroRapido('activas')">
                        <i class="fas fa-check-circle me-1"></i>Solo activas
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="aplicarFiltroRapido('anuladas')">
                        <i class="fas fa-ban me-1"></i>Solo anuladas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TABLA
         ═══════════════════════════════════════════════════════════ -->
    <div class="card card-modern" id="tablaVentasContainer">
        <div class="card-header-icon no-print">
            <div class="icon-circle" style="background: rgba(25, 135, 84, .1); color: var(--success);">
                <i class="fas fa-list"></i>
            </div>
            <h3>Registro de Ventas</h3>

            <div class="ms-auto d-flex align-items-center gap-2">
                <!-- Buscador en vivo -->
                <div class="live-search-wrapper">
                    <i class="fas fa-search live-search-icon"></i>
                    <input type="text"
                           id="liveSearch"
                           class="form-control form-control-sm live-search-input"
                           placeholder="Buscar en la tabla..."
                           autocomplete="off">
                    <button type="button"
                            class="live-search-clear"
                            id="liveSearchClear"
                            style="display: none;"
                            title="Limpiar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <span class="badge bg-success" id="contadorVentas">
                    <?= count($ventas ?? []) ?> <?= count($ventas ?? []) === 1 ? 'venta' : 'ventas' ?>
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (!empty($ventas)): ?>
                <div class="table-responsive table-responsive-mobile">
                    <table class="table table-hover align-middle mb-0" id="tablaVentas">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 130px;">Comprobante</th>
                                <th style="min-width: 200px;">Cliente</th>
                                <th style="min-width: 180px;">Ruta</th>
                                <th class="text-center" style="min-width: 80px;">Asiento</th>
                                <th style="min-width: 110px;">Fecha</th>
                                <th class="text-center" style="min-width: 100px;">Pago</th>
                                <th class="text-end" style="min-width: 100px;">Total</th>
                                <th class="text-center" style="min-width: 100px;">Estado</th>
                                <th class="text-center no-print" style="min-width: 130px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyVentas">
                            <?php foreach ($ventas as $venta):
                                $badge_estado = [
                                    'ACTIVA'  => ['success', 'fa-check-circle'],
                                    'ANULADA' => ['danger',  'fa-ban'],
                                    'USADA'   => ['secondary', 'fa-history']
                                ][$venta['estado'] ?? ''] ?? ['secondary', 'fa-question'];

                                $badge_pago = [
                                    'EFECTIVO'      => ['success', 'fa-money-bill-wave'],
                                    'TARJETA'       => ['info',    'fa-credit-card'],
                                    'TRANSFERENCIA' => ['primary', 'fa-exchange-alt'],
                                    'OTRO'          => ['secondary', 'fa-ellipsis-h']
                                ][$venta['metodo_pago'] ?? ''] ?? ['secondary', 'fa-question'];

                                $nombre   = trim((string)($venta['cliente_nombre']   ?? ''));
                                $apellido = trim((string)($venta['cliente_apellido'] ?? ''));
                                $iniciales = strtoupper(mb_substr($nombre, 0, 1) . mb_substr($apellido, 0, 1));
                                if ($iniciales === '') $iniciales = '?';

                                // Texto completo para búsqueda en vivo
                                $search_text = strtolower(
                                    ($venta['numero_comprobante'] ?? '') . ' ' .
                                    $nombre . ' ' . $apellido . ' ' .
                                    ($venta['numero_documento'] ?? '') . ' ' .
                                    ($venta['origen'] ?? '') . ' ' .
                                    ($venta['destino'] ?? '') . ' ' .
                                    ($venta['numero_asiento'] ?? '') . ' ' .
                                    ($venta['metodo_pago'] ?? '') . ' ' .
                                    ($venta['estado'] ?? '')
                                );
                            ?>
                            <tr data-search="<?= htmlspecialchars($search_text) ?>" data-id="<?= (int)$venta['id'] ?>">
                                <td data-label="Comprobante">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-receipt text-primary"></i>
                                        <strong class="font-monospace small">
                                            <?= htmlspecialchars($venta['numero_comprobante'] ?? '—') ?>
                                        </strong>
                                    </div>
                                </td>

                                <td data-label="Cliente">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold"
                                             style="width: 38px; height: 38px; flex-shrink: 0; font-size: .75rem;">
                                            <?= htmlspecialchars($iniciales) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <strong class="d-block text-truncate small">
                                                <?= htmlspecialchars($nombre . ' ' . $apellido) ?>
                                            </strong>
                                            <small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i><?= htmlspecialchars($venta['numero_documento'] ?? '—') ?>
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Ruta">
                                    <div class="small">
                                        <strong><?= htmlspecialchars($venta['origen'] ?? '—') ?></strong>
                                        <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                        <strong><?= htmlspecialchars($venta['destino'] ?? '—') ?></strong>
                                    </div>
                                </td>

                                <td data-label="Asiento" class="text-center">
                                    <span class="badge bg-dark">
                                        <i class="fas fa-chair me-1"></i><?= htmlspecialchars($venta['numero_asiento'] ?? '—') ?>
                                    </span>
                                </td>

                                <td data-label="Fecha">
                                    <div class="small">
                                        <i class="fas fa-calendar-day me-1 text-muted"></i>
                                        <?= date('d/m/Y', strtotime($venta['fecha_venta'])) ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i><?= date('H:i', strtotime($venta['fecha_venta'])) ?>
                                        </small>
                                    </div>
                                </td>

                                <td data-label="Pago" class="text-center">
                                    <span class="badge bg-<?= $badge_pago[0] ?>">
                                        <i class="fas <?= $badge_pago[1] ?> me-1"></i><?= htmlspecialchars($venta['metodo_pago'] ?? '—') ?>
                                    </span>
                                </td>

                                <td data-label="Total" class="text-end">
                                    <strong class="text-success">
                                        S/ <?= number_format((float)($venta['precio_pagado'] ?? 0), 2) ?>
                                    </strong>
                                </td>

                                <td data-label="Estado" class="text-center">
                                    <span class="badge bg-<?= $badge_estado[0] ?>">
                                        <i class="fas <?= $badge_estado[1] ?> me-1"></i>
                                        <?= htmlspecialchars($venta['estado'] ?? '—') ?>
                                    </span>
                                </td>

                                <td data-label="Acciones" class="text-center no-print">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>ventas/show/<?= (int)$venta['id'] ?>"
                                           class="btn btn-sm btn-outline-info" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (($venta['estado'] ?? '') === 'ACTIVA'): ?>
                                            <a href="<?= BASE_URL ?>ventas/ticket/<?= (int)$venta['id'] ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-secondary" title="Imprimir ticket">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-danger" title="Anular venta"
                                               onclick="return confirmarAnular(
                                                   '<?= BASE_URL ?>ventas/anular/<?= (int)$venta['id'] ?>',
                                                   'Comprobante: <strong><?= htmlspecialchars($venta['numero_comprobante'] ?? '') ?></strong><br>Total: S/ <?= number_format((float)($venta['precio_pagado'] ?? 0), 2) ?>'
                                               )">
                                                <i class="fas fa-ban"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Estado vacío de búsqueda en vivo -->
                <div id="sinResultados" class="text-center py-5" style="display: none;">
                    <i class="fas fa-search fa-4x text-muted opacity-25 mb-3"></i>
                    <h5 class="text-muted">Sin resultados</h5>
                    <p class="text-muted small mb-0" id="textoSinResultados">
                        No hay ventas que coincidan con la búsqueda.
                    </p>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-ticket-alt fa-4x text-muted opacity-25"></i>
                    </div>
                    <h5 class="text-muted">No hay ventas registradas</h5>
                    <p class="text-muted small mb-3">
                        <?php if ($filtros_activos): ?>
                            No se encontraron resultados con los filtros aplicados.
                        <?php else: ?>
                            Comienza registrando tu primera venta.
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <?php if ($filtros_activos): ?>
                            <a href="<?= BASE_URL ?>ventas" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-undo me-1"></i>Limpiar filtros
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>ventas/create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Nueva Venta
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- PAGINACIÓN -->
    <?php if (isset($paginator)): ?>
        <div class="no-print">
            <?php require_once __DIR__ . '/../partials/paginacion.php'; ?>
        </div>
    <?php endif; ?>
</div>

<!-- FAB MÓVIL -->
<a href="<?= BASE_URL ?>ventas/create" class="fab-mobile no-print" title="Nueva Venta">
    <i class="fas fa-plus"></i>
</a>

<!-- ═══════════════════════════════════════════════════════════
     MODAL EXPORTAR PDF
     ═══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalExportarPDF" tabindex="-1" aria-labelledby="modalExportarPDFLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalExportarPDFLabel">
                    <i class="fas fa-file-pdf me-2"></i>Exportar listado a PDF
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="GET" action="<?= BASE_URL ?>ventas/pdf" target="_blank" id="formExportarPDF">
                <div class="modal-body">

                    <div class="alert alert-info small d-flex align-items-start gap-2">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>
                            Configure los filtros para exportar. El PDF se generará con <strong>todas</strong> las ventas que coincidan (no solo las de la página actual).
                        </div>
                    </div>

                    <!-- Rango de fechas -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-calendar-alt me-1"></i>Rango de fechas
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filtros['fecha_inicio'] ?? '') ?>"
                                       placeholder="Desde">
                            </div>
                            <div class="col-6">
                                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($filtros['fecha_fin'] ?? '') ?>"
                                       placeholder="Hasta">
                            </div>
                        </div>

                        <!-- Atajos de fecha -->
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('hoy')">Hoy</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('ayer')">Ayer</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('semana')">Esta semana</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('mes')">Este mes</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('mes_anterior')">Mes anterior</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setRangoPDF('limpiar')">Limpiar</button>
                        </div>
                    </div>

                    <hr>

                    <!-- Vendedor -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-user-tie me-1"></i>Vendedor
                        </label>
                        <select name="vendedor_id" class="form-select form-select-sm">
                            <option value="">Todos los vendedores</option>
                            <?php foreach ($vendedores ?? [] as $v): ?>
                                <option value="<?= (int)$v['id'] ?>"
                                        <?= ((int)($filtros['vendedor_id'] ?? 0) === (int)$v['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v['nombre_completo']) ?>
                                    (<?= (int)$v['total_ventas'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Estado -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-toggle-on me-1"></i>Estado
                        </label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="ACTIVA"  <?= ($filtros['estado'] ?? '') === 'ACTIVA'  ? 'selected' : '' ?>>Solo activas</option>
                            <option value="ANULADA" <?= ($filtros['estado'] ?? '') === 'ANULADA' ? 'selected' : '' ?>>Solo anuladas</option>
                            <option value="USADA"   <?= ($filtros['estado'] ?? '') === 'USADA'   ? 'selected' : '' ?>>Solo usadas</option>
                        </select>
                    </div>

                    <!-- Búsqueda -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small">
                            <i class="fas fa-search me-1"></i>Búsqueda (opcional)
                        </label>
                        <input type="text" name="buscar" class="form-control form-control-sm"
                               placeholder="Cliente, DNI o comprobante..."
                               value="<?= htmlspecialchars($filtros['buscar'] ?? '') ?>">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-pdf me-1"></i>Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SCRIPT
     ═══════════════════════════════════════════════════════════ -->
<script>
// ============================================================
// 1. FILTROS COLAPSABLES — Guardar estado en localStorage
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const filtrosCollapse = document.getElementById('filtrosCollapse');
    const collapseIcon    = document.querySelector('.collapse-icon');

    if (filtrosCollapse) {
        // Actualizar el ícono cuando se abre/cierra
        filtrosCollapse.addEventListener('show.bs.collapse', function() {
            if (collapseIcon) collapseIcon.classList.add('rotated');
            localStorage.setItem('ventas_filtros_abiertos', '1');
        });

        filtrosCollapse.addEventListener('hide.bs.collapse', function() {
            if (collapseIcon) collapseIcon.classList.remove('rotated');
            localStorage.setItem('ventas_filtros_abiertos', '0');
        });

        // Restaurar estado si no hay filtros activos
        const filtrosActivos = <?= $filtros_activos ? 'true' : 'false' ?>;
        const estadoGuardado = localStorage.getItem('ventas_filtros_abiertos');

        if (!filtrosActivos && estadoGuardado === '1') {
            // Abrir filtros si el usuario los dejó abiertos
            const bsCollapse = bootstrap.Collapse.getInstance(filtrosCollapse) || new bootstrap.Collapse(filtrosCollapse, { toggle: false });
            bsCollapse.show();
        }
    }
});

// ============================================================
// 2. FILTROS RÁPIDOS
// ============================================================
function aplicarFiltroRapido(tipo) {
    const form = document.getElementById('formFiltros');
    const url  = new URL(window.location.href);

    // Resetear parámetros
    url.searchParams.delete('fecha_inicio');
    url.searchParams.delete('fecha_fin');
    url.searchParams.delete('estado');
    url.searchParams.delete('buscar');

    const hoy = new Date();
    const fmt = (d) => d.toISOString().split('T')[0];

    switch (tipo) {
        case 'hoy':
            url.searchParams.set('fecha_inicio', fmt(hoy));
            url.searchParams.set('fecha_fin', fmt(hoy));
            break;

        case 'ayer':
            const ayer = new Date(hoy);
            ayer.setDate(ayer.getDate() - 1);
            url.searchParams.set('fecha_inicio', fmt(ayer));
            url.searchParams.set('fecha_fin', fmt(ayer));
            break;

        case 'semana':
            const inicioSemana = new Date(hoy);
            inicioSemana.setDate(hoy.getDate() - hoy.getDay()); // domingo
            url.searchParams.set('fecha_inicio', fmt(inicioSemana));
            url.searchParams.set('fecha_fin', fmt(hoy));
            break;

        case 'mes':
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            url.searchParams.set('fecha_inicio', fmt(inicioMes));
            url.searchParams.set('fecha_fin', fmt(hoy));
            break;

        case 'activas':
            url.searchParams.set('estado', 'ACTIVA');
            break;

        case 'anuladas':
            url.searchParams.set('estado', 'ANULADA');
            break;
    }

    window.location.href = url.toString();
}

// ============================================================
// 3. BUSCADOR EN VIVO (sin recargar página)
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const input       = document.getElementById('liveSearch');
    const clearBtn    = document.getElementById('liveSearchClear');
    const tbody       = document.getElementById('tbodyVentas');
    const sinResult   = document.getElementById('sinResultados');
    const contador    = document.getElementById('contadorVentas');

    if (!input || !tbody) return;

    let debounceTimer = null;

    function filtrarTabla(query) {
        const q = (query || '').toLowerCase().trim();
        const filas = tbody.querySelectorAll('tr[data-search]');
        let visibles = 0;

        filas.forEach(function(fila) {
            const texto = fila.dataset.search || '';
            if (!q || texto.includes(q)) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        // Mostrar mensaje si no hay resultados
        if (visibles === 0 && filas.length > 0) {
            sinResult.style.display = '';
            document.getElementById('textoSinResultados').textContent =
                `No hay ventas que coincidan con "${query}".`;
        } else {
            sinResult.style.display = 'none';
        }

        // Actualizar contador
        if (contador) {
            contador.textContent = visibles + ' ' + (visibles === 1 ? 'venta' : 'ventas');
            // Cambiar color si hay filtro activo
            contador.className = visibles === 0
                ? 'badge bg-danger'
                : (q ? 'badge bg-warning text-dark' : 'badge bg-success');
        }
    }

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const val = this.value;

        // Mostrar/ocultar botón de limpiar
        clearBtn.style.display = val.length > 0 ? 'flex' : 'none';

        // Debounce 150ms para no saturar
        debounceTimer = setTimeout(function() {
            filtrarTabla(val);
        }, 150);
    });

    clearBtn.addEventListener('click', function() {
        input.value = '';
        clearBtn.style.display = 'none';
        filtrarTabla('');
        input.focus();
    });

    // Atajo de teclado: Ctrl+K o Cmd+K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });
});

// ============================================================
// 5. LIMPIAR BÚSQUEDA AL NAVEGAR HACIA ATRÁS
// ============================================================
window.addEventListener('pageshow', function(e) {
    if (e.persisted) {
        const input = document.getElementById('liveSearch');
        if (input && input.value) {
            input.value = '';
            input.dispatchEvent(new Event('input'));
        }
    }
});


// ============================================================
// 6. SET RANGO DE FECHAS PARA EXPORTAR PDF
// ============================================================
function setRangoPDF(tipo) {
    const form = document.getElementById('formExportarPDF');
    if (!form) return;

    const fi = form.querySelector('input[name="fecha_inicio"]');
    const ff = form.querySelector('input[name="fecha_fin"]');
    const hoy = new Date();
    const fmt = (d) => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
    };

    switch (tipo) {
        case 'hoy':
            fi.value = fmt(hoy);
            ff.value = fmt(hoy);
            break;

        case 'ayer': {
            const ayer = new Date(hoy);
            ayer.setDate(ayer.getDate() - 1);
            fi.value = fmt(ayer);
            ff.value = fmt(ayer);
            break;
        }

        case 'semana': {
            const inicioSemana = new Date(hoy);
            const dia = hoy.getDay(); // 0 = domingo
            inicioSemana.setDate(hoy.getDate() - dia);
            fi.value = fmt(inicioSemana);
            ff.value = fmt(hoy);
            break;
        }

        case 'mes': {
            const inicioMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
            fi.value = fmt(inicioMes);
            ff.value = fmt(hoy);
            break;
        }

        case 'mes_anterior': {
            const inicioMesAnt = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
            const finMesAnt    = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
            fi.value = fmt(inicioMesAnt);
            ff.value = fmt(finMesAnt);
            break;
        }

        case 'limpiar':
        default:
            fi.value = '';
            ff.value = '';
            break;
    }
}

</script>