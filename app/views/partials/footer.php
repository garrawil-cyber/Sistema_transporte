<footer class="border-top mt-4 pt-3 pb-2 text-muted small">
    <div class="container-fluid">
        <div class="row">
            <!-- Columna 1: Información de la empresa -->
            <div class="col-md-4">
                <h6 class="text-uppercase fw-bold mb-2">
                    <i class="fas fa-bus me-1 text-primary"></i><?php echo FACTURACION_RAZON_SOCIAL ?? 'Transporte Ejemplo'; ?>
                </h6>
                <p class="mb-1">
                    <i class="fas fa-map-pin me-1 text-primary"></i>
                    <?php echo FACTURACION_DIRECCION ?? 'Av. Principal 123, Lima - Perú'; ?>
                </p>
                <p class="mb-1">
                    <i class="fas fa-phone me-1 text-primary"></i>
                    <?php echo FACTURACION_TELEFONO ?? '(01) 555-5555'; ?>
                </p>
                <p class="mb-0">
                    <i class="fas fa-envelope me-1 text-primary"></i>
                    <?php echo FACTURACION_EMAIL ?? 'info@transporte.com'; ?>
                </p>
            </div>

            <!-- Columna 2: Enlaces rápidos -->
            <div class="col-md-4">
                <h6 class="text-uppercase fw-bold mb-2">Enlaces Rápidos</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?php echo BASE_URL; ?>home" class="text-decoration-none text-muted">
                        <i class="fas fa-chevron-right me-1" style="font-size: 0.6rem;"></i>Dashboard
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>ventas" class="text-decoration-none text-muted">
                        <i class="fas fa-chevron-right me-1" style="font-size: 0.6rem;"></i>Ventas
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>encomiendas" class="text-decoration-none text-muted">
                        <i class="fas fa-chevron-right me-1" style="font-size: 0.6rem;"></i>Encomiendas
                    </a></li>
                    <li><a href="<?php echo BASE_URL; ?>giros" class="text-decoration-none text-muted">
                        <i class="fas fa-chevron-right me-1" style="font-size: 0.6rem;"></i>Giros
                    </a></li>
                </ul>
            </div>

            <!-- Columna 3: Redes sociales y soporte -->
            <div class="col-md-4 text-md-end">
                <h6 class="text-uppercase fw-bold mb-2">Soporte</h6>
                <p class="mb-1">
                    <i class="fas fa-headset me-1 text-primary"></i>
                    <a href="mailto:soporte@transporte.com" class="text-decoration-none text-muted">
                        soporte@transporte.com
                    </a>
                </p>
                <div class="mt-2">
                    <a href="#" class="text-muted me-2" title="Facebook"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-muted me-2" title="Twitter"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-muted me-2" title="Instagram"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-muted" title="YouTube"><i class="fab fa-youtube fa-lg"></i></a>
                </div>
            </div>
        </div>

        <!-- Línea divisoria y copyright -->
        <div class="row mt-3">
            <div class="col-12 text-center border-top pt-3">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> 
                    <?php echo FACTURACION_RAZON_SOCIAL ?? 'Transporte Ejemplo'; ?> - 
                    Todos los derechos reservados
                    <span class="mx-2">|</span>
                    <span class="text-muted">Versión 1.0.0</span>
                    <span class="mx-2">|</span>
                    <span class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>Sistema Seguro
                    </span>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Estilos opcionales para el footer -->
<style>
    footer a:hover {
        color: #0d6efd !important;
        transition: color 0.2s ease;
    }
    footer .fa-chevron-right {
        color: #0d6efd;
    }
    footer .fab:hover {
        color: #0d6efd !important;
        transition: color 0.2s ease;
        transform: scale(1.1);
    }
    footer .fab {
        transition: all 0.2s ease;
    }
    @media (max-width: 768px) {
        footer .text-md-end {
            text-align: center !important;
            margin-top: 15px;
        }
        footer .col-md-4 {
            text-align: center !important;
            margin-bottom: 15px;
        }
    }
</style>