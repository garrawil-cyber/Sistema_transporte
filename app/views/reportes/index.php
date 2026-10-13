<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-chart-bar text-primary me-2"></i>Módulo de Reportes
        </h1>
        <p class="text-muted mb-0 small">
            Consulta y exporta reportes de ventas, encomiendas, giros, caja y más
        </p>
    </div>
    <div>
        <span class="badge bg-primary">
            <i class="fas fa-calendar-day me-1"></i><?php echo date('d/m/Y'); ?>
        </span>
    </div>
</div>

<!-- ============================================================
     TARJETAS DE ACCESO A REPORTES
     ============================================================ -->
<div class="row g-4">

    <!-- Reporte de Ventas -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-primary">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <h5 class="card-title fw-bold">Reporte de Ventas</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Consulta las ventas realizadas por rango de fechas, rutas, vendedores y métodos de pago.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/ventas" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/ventas" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </a>
                        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/ventas" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporte de Encomiendas -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-success">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-box"></i>
                </div>
                <h5 class="card-title fw-bold">Reporte de Encomiendas</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Consulta encomiendas por estado, fecha, ruta y destinatario. Incluye peso y costos.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/encomiendas" class="btn btn-success">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/encomiendas" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </a>
                        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/encomiendas" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporte de Giros -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-warning">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-money-bill-transfer"></i>
                </div>
                <h5 class="card-title fw-bold">Reporte de Giros</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Consulta giros realizados, comisiones, montos y estados de pago.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/giros" class="btn btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo BASE_URL; ?>reportes/exportarExcel/giros" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-1"></i>Excel
                        </a>
                        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/giros" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reporte de Ocupabilidad -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-info">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-chair"></i>
                </div>
                <h5 class="card-title fw-bold">Reporte de Ocupabilidad</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Analiza el porcentaje de ocupación de cada viaje y detecta rutas con baja demanda.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/ocupabilidad" class="btn btn-info text-white">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/ocupabilidad" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cierre de Caja -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-danger">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-cash-register"></i>
                </div>
                <h5 class="card-title fw-bold">Cierre de Caja</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Consulta los movimientos de caja por usuario y día. Incluye arqueo y diferencias.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/caja" class="btn btn-danger">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                    <div class="btn-group btn-group-sm">
                        <a href="<?php echo BASE_URL; ?>reportes/exportarPDF/caja" target="_blank" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-1"></i>PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Manifiestos -->
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm border-0 reporte-card reporte-secondary">
            <div class="card-body text-center d-flex flex-column">
                <div class="reporte-icon mb-3">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <h5 class="card-title fw-bold">Historial de Manifiestos</h5>
                <p class="card-text text-muted small flex-grow-1">
                    Consulta los manifiestos generados, enviados por email y validados por QR.
                </p>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>manifiesto/historial" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
     RESUMEN RÁPIDO
     ============================================================ -->
<div class="row g-3 mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-info-circle text-primary me-2"></i>Accesos Rápidos
                </h6>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>reportes/ventas?fecha_inicio=<?php echo date('Y-m-d'); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-day me-1"></i>Ventas de Hoy
                    </a>
                    <a href="<?php echo BASE_URL; ?>reportes/ventas?fecha_inicio=<?php echo date('Y-m-01'); ?>&fecha_fin=<?php echo date('Y-m-d'); ?>" 
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-calendar-alt me-1"></i>Ventas del Mes
                    </a>
                    
<a href="<?php echo BASE_URL; ?>reportes/caja" class="btn btn-sm btn-outline-danger">
    <i class="fas fa-cash-register me-1"></i>Mi Caja Hoy
</a>
                    <a href="<?php echo BASE_URL; ?>reportes/encomiendas?estado=ENTREGADO" 
                       class="btn btn-sm btn-outline-success">
                        <i class="fas fa-check-circle me-1"></i>Encomiendas Entregadas
                    </a>
                    <a href="<?php echo BASE_URL; ?>reportes/giros?estado=PAGADO" 
                       class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-money-bill-wave me-1"></i>Giros Pagados
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     ESTILOS
     ============================================================ -->
<style>
    .reporte-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .reporte-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        transition: height 0.3s ease;
    }

    .reporte-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
    }

    .reporte-card:hover::before {
        height: 100%;
        opacity: 0.03;
    }

    .reporte-primary::before { background: #0d6efd; }
    .reporte-success::before { background: #198754; }
    .reporte-warning::before { background: #ffc107; }
    .reporte-info::before { background: #0dcaf0; }
    .reporte-danger::before { background: #dc3545; }
    .reporte-secondary::before { background: #6c757d; }

    .reporte-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        transition: all 0.3s ease;
    }

    .reporte-primary .reporte-icon {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }
    .reporte-success .reporte-icon {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
    }
    .reporte-warning .reporte-icon {
        background: rgba(255, 193, 7, 0.15);
        color: #cc9a06;
    }
    .reporte-info .reporte-icon {
        background: rgba(13, 202, 240, 0.1);
        color: #0aa2c0;
    }
    .reporte-danger .reporte-icon {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    .reporte-secondary .reporte-icon {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .reporte-card:hover .reporte-icon {
        transform: scale(1.1) rotate(5deg);
    }

    @media (max-width: 768px) {
        .reporte-icon {
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
        }
    }
</style>