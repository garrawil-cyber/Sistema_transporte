<!-- Banner de modo simulación -->
<?php if (defined('FACTURACION_MODO_SIMULACION') && FACTURACION_MODO_SIMULACION && $comprobante['estado'] == 'SIMULADO'): ?>
<div class="alert alert-warning border-0 shadow-sm mb-3">
    <i class="fas fa-flask me-2"></i>
    <strong>🧪 Comprobante Simulado</strong>
    <p class="mb-0 small">
        Este comprobante fue generado en <strong>MODO SIMULACIÓN</strong> y no tiene validez ante SUNAT.
    </p>
</div>
<?php endif; ?>

<!-- ============================================================
     HEADER CON BOTONES
     ============================================================ -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-file-invoice text-primary me-2"></i><?php echo $titulo; ?>
        </h1>
        <p class="text-muted mb-0 small">
            <i class="fas fa-barcode me-1"></i><?php echo $comprobante['numero_completo']; ?>
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?php echo BASE_URL; ?>factura/imprimir/<?php echo $comprobante['id']; ?>" 
           target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-print me-1"></i>Imprimir
        </a>
        <a href="<?php echo BASE_URL; ?>factura/pdf/<?php echo $comprobante['id']; ?>" 
           class="btn btn-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i>PDF
        </a>
        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalEmail">
            <i class="fas fa-envelope me-1"></i>Email
        </button>
        <?php if (!empty($comprobante['xml'])): ?>
            <a href="<?php echo BASE_URL; ?>factura/descargarXML/<?php echo $comprobante['id']; ?>" 
               class="btn btn-warning btn-sm">
                <i class="fas fa-download me-1"></i>XML
            </a>
        <?php endif; ?>
        <a href="<?php echo BASE_URL; ?>factura" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        
        <!-- ============================================================
             ESTADO SUNAT
             ============================================================ -->
        <div class="alert alert-<?php 
            echo $comprobante['estado'] == 'ACEPTADO' ? 'success' : 
                ($comprobante['estado'] == 'RECHAZADO' ? 'danger' : 
                ($comprobante['estado'] == 'SIMULADO' ? 'warning' : 'secondary')); 
        ?> border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-<?php 
                            echo $comprobante['estado'] == 'ACEPTADO' ? 'check-circle' : 
                                ($comprobante['estado'] == 'RECHAZADO' ? 'times-circle' : 
                                ($comprobante['estado'] == 'SIMULADO' ? 'flask' : 'clock')); 
                        ?> me-2"></i>
                        Estado: <strong><?php echo $comprobante['estado']; ?></strong>
                    </h5>
                    <?php if (!empty($comprobante['cdr_description'])): ?>
                        <p class="mb-0 small">
                            <?php echo htmlspecialchars($comprobante['cdr_description']); ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($comprobante['fecha_envio'])): ?>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Enviado el <?php echo date('d/m/Y H:i:s', strtotime($comprobante['fecha_envio'])); ?>
                        </small>
                    <?php endif; ?>
                </div>
                <?php if (!empty($comprobante['cdr_code'])): ?>
                    <div class="text-end">
                        <small class="d-block">Código CDR</small>
                        <h3 class="mb-0"><?php echo $comprobante['cdr_code']; ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ============================================================
             TARJETAS DE RESUMEN
             ============================================================ -->
        <div class="row g-2 mb-3">
            <div class="col-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <small class="text-muted d-block mb-1">Tipo</small>
                        <span class="badge bg-<?php echo $comprobante['tipo'] == 'FACTURA' ? 'primary' : 'info'; ?> fs-6">
                            <i class="fas fa-<?php echo $comprobante['tipo'] == 'FACTURA' ? 'file-invoice' : 'receipt'; ?> me-1"></i>
                            <?php echo $comprobante['tipo']; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3 text-center">
                        <small class="text-muted d-block mb-1">Moneda</small>
                        <span class="badge bg-secondary fs-6">
                            <i class="fas fa-dollar-sign me-1"></i><?php echo $comprobante['moneda']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             COMPROBANTE
             ============================================================ -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                
                <!-- Encabezado empresa -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h3 class="text-primary mb-1">
                        <i class="fas fa-bus me-2"></i><?php echo FACTURACION_RAZON_SOCIAL; ?>
                    </h3>
                    <p class="mb-1 small">RUC: <strong><?php echo FACTURACION_RUC; ?></strong></p>
                    <p class="mb-1 small"><?php echo FACTURACION_DIRECCION; ?></p>
                    <p class="mb-0 small">
                        <i class="fas fa-phone me-1"></i><?php echo FACTURACION_TELEFONO; ?>
                        <span class="mx-2">|</span>
                        <i class="fas fa-envelope me-1"></i><?php echo FACTURACION_EMAIL; ?>
                    </p>
                    <hr>
                    <h4 class="mb-1">
                        <?php echo $comprobante['tipo'] === 'FACTURA' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA'; ?>
                    </h4>
                    <h3 class="text-primary"><?php echo $comprobante['numero_completo']; ?></h3>
                </div>

                <!-- Datos del cliente y fecha -->
                <div class="row mb-4">
                    <div class="col-md-7">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-user me-1"></i>DATOS DEL CLIENTE
                        </h6>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($comprobante['cliente_nombre']); ?></strong></p>
                        <p class="mb-1">
                            <span class="badge bg-secondary"><?php echo $comprobante['cliente_tipo_doc']; ?></span>
                            <?php echo $comprobante['cliente_numero_doc']; ?>
                        </p>
                        <?php if (!empty($venta['email'])): ?>
                            <p class="mb-1 small text-muted">
                                <i class="fas fa-envelope me-1"></i><?php echo $venta['email']; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($venta['direccion'])): ?>
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i><?php echo $venta['direccion']; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-calendar me-1"></i>DATOS DE EMISIÓN
                        </h6>
                        <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($comprobante['fecha_emision'])); ?></p>
                        <p class="mb-1"><strong>Hora:</strong> <?php echo date('H:i:s', strtotime($comprobante['fecha_emision'])); ?></p>
                        <p class="mb-0"><strong>Moneda:</strong> <?php echo $comprobante['moneda']; ?></p>
                    </div>
                </div>

                <!-- Detalle de venta -->
                <?php if (!empty($venta)): ?>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 60%;">Descripción del Servicio</th>
                                <th class="text-center" style="width: 15%;">Cant.</th>
                                <th class="text-end" style="width: 25%;">Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Servicio de Transporte de Pasajeros</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-route me-1"></i>
                                        <?php echo htmlspecialchars($venta['origen'] . ' → ' . $venta['destino']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-chair me-1"></i>Asiento: <strong><?php echo $venta['numero_asiento']; ?></strong>
                                        <span class="mx-2">|</span>
                                        <i class="fas fa-bus me-1"></i>Bus: <strong><?php echo $venta['placa']; ?></strong>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>Salida: <?php echo date('d/m/Y H:i', strtotime($venta['fecha_salida'])); ?>
                                    </small>
                                    <?php if (!empty($venta['numero_comprobante'])): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-ticket-alt me-1"></i>Ticket: <?php echo $venta['numero_comprobante']; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center align-middle">1</td>
                                <td class="text-end align-middle">
                                    S/ <?php echo number_format($comprobante['total'], 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm">
                            <tr>
                                <td>Sub Total:</td>
                                <td class="text-end">
                                    S/ <?php echo number_format($comprobante['subtotal'], 2); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>IGV (18%):</td>
                                <td class="text-end">
                                    S/ <?php echo number_format($comprobante['igv'], 2); ?>
                                </td>
                            </tr>
                            <tr class="table-primary">
                                <td class="fw-bold">IMPORTE TOTAL:</td>
                                <td class="text-end">
                                    <h4 class="mb-0">S/ <?php echo number_format($comprobante['total'], 2); ?></h4>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Monto en letras -->
                <div class="alert alert-light border mb-0">
                    <small class="text-muted">
                        <strong>SON:</strong> 
                        <?php 
                            $entero = floor($comprobante['total']);
                            $decimales = round(($comprobante['total'] - $entero) * 100);
                            echo strtoupper(number_format($comprobante['total'], 2)) . ' SOLES';
                        ?>
                    </small>
                </div>
                <?php endif; ?>

                <hr class="my-4">

                <!-- Pie de página -->
                <div class="text-center text-muted small">
                    <p class="mb-1">
                        <i class="fas fa-info-circle me-1"></i>
                        Esta es una representación impresa de un comprobante electrónico
                    </p>
                    <p class="mb-0">
                        Generado por el sistema el <?php echo date('d/m/Y H:i:s'); ?>
                    </p>
                </div>

            </div>
        </div>

        <!-- ============================================================
             INFORMACIÓN TÉCNICA (solo para admin)
             ============================================================ -->
        <?php if (($_SESSION['rol_id'] ?? 0) == 1): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-info-circle me-2"></i>Información Técnica
            </div>
            <div class="card-body">
                <div class="row small">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>ID Comprobante:</strong> #<?php echo $comprobante['id']; ?></p>
                        <p class="mb-1"><strong>Serie:</strong> <?php echo $comprobante['serie']; ?></p>
                        <p class="mb-1"><strong>Correlativo:</strong> <?php echo $comprobante['correlativo']; ?></p>
                        <p class="mb-0"><strong>Usuario:</strong> <?php echo $comprobante['usuario_nombre'] ?? 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Fecha Emisión:</strong> <?php echo date('d/m/Y H:i:s', strtotime($comprobante['fecha_emision'])); ?></p>
                        <p class="mb-1"><strong>Fecha Envío:</strong> 
                            <?php echo !empty($comprobante['fecha_envio']) ? date('d/m/Y H:i:s', strtotime($comprobante['fecha_envio'])) : 'Pendiente'; ?>
                        </p>
                        <p class="mb-1"><strong>Código CDR:</strong> <?php echo $comprobante['cdr_code'] ?? 'N/A'; ?></p>
                        <p class="mb-0"><strong>Estado:</strong> 
                            <span class="badge bg-<?php 
                                echo $comprobante['estado'] == 'ACEPTADO' ? 'success' : 
                                    ($comprobante['estado'] == 'RECHAZADO' ? 'danger' : 
                                    ($comprobante['estado'] == 'SIMULADO' ? 'warning' : 'secondary')); 
                            ?>">
                                <?php echo $comprobante['estado']; ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($comprobante['xml'])): ?>
                    <hr>
                    <details>
                        <summary class="text-primary" style="cursor: pointer;">
                            <i class="fas fa-code me-1"></i>Ver XML firmado
                        </summary>
                        <pre class="mt-2 p-3 bg-light border rounded" style="max-height: 400px; overflow-y: auto; font-size: 0.7rem;"><?php echo htmlspecialchars(substr($comprobante['xml'], 0, 5000)); ?><?php echo strlen($comprobante['xml']) > 5000 ? "\n\n... (truncado)" : ''; ?></pre>
                    </details>
                <?php endif; ?>

                <?php if (!empty($comprobante['cdr'])): ?>
                    <details class="mt-2">
                        <summary class="text-primary" style="cursor: pointer;">
                            <i class="fas fa-check-circle me-1"></i>Ver CDR de SUNAT
                        </summary>
                        <pre class="mt-2 p-3 bg-light border rounded" style="max-height: 300px; overflow-y: auto; font-size: 0.7rem;"><?php echo htmlspecialchars($comprobante['cdr']); ?></pre>
                    </details>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ============================================================
     MODAL: ENVIAR POR EMAIL
     ============================================================ -->
<div class="modal fade" id="modalEmail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo BASE_URL; ?>factura/email/<?php echo $comprobante['id']; ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i>Enviar Comprobante por Email
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Correo Destino *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($venta['email'] ?? ''); ?>"
                                   placeholder="correo@ejemplo.com" required>
                        </div>
                        <small class="text-muted">Se adjuntará el PDF y el XML del comprobante</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mensaje Personalizado (opcional)</label>
                        <textarea name="mensaje" class="form-control" rows="3" 
                                  placeholder="Escriba un mensaje adicional..."></textarea>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Se adjuntarán:</strong>
                        <ul class="mb-0 mt-1 small">
                            <li>📄 <?php echo $comprobante['tipo']; ?>_<?php echo $comprobante['numero_completo']; ?>.pdf</li>
                            <?php if (!empty($comprobante['xml'])): ?>
                                <li>📋 <?php echo $comprobante['numero_completo']; ?>.xml</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
