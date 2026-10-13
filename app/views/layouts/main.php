<?php
$loggedIn = isset($_SESSION['user_id']);
$isAdmin = ($_SESSION['rol_id'] ?? 0) == 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#212529">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Transporte">
    
    <title><?php echo $titulo ?? 'Transporte'; ?></title>
    
    <link rel="manifest" href="<?php echo BASE_URL; ?>manifest.json">
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/img/favicon-96x96.png">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>assets/img/apple-touch-icon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/custom.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/validarDocumentos.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- 1. Funciones SweetAlert del sistema (usa Swal) -->
    <script src="<?= BASE_URL ?>assets/js/custom.js"></script>

    <!-- 2. Validadores específicos (sin dependencias) -->
    <script src="<?= BASE_URL ?>assets/js/validar-documento.js"></script>

    <!-- 3. Helpers genéricos (usan mostrarToast de custom.js) -->
    <script src="<?= BASE_URL ?>assets/js/form-helpers.js"></script>



</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php if ($loggedIn): ?>
                <!-- Overlay para móvil -->
                <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
                
                <!-- SIDEBAR -->
                <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
                
                <!-- WRAPPER PRINCIPAL (navbar + contenido) -->
                <div class="main-wrapper">
                    
                    <!-- NAVBAR SUPERIOR -->
                    <nav class="navbar-top">
                        <!-- Botón hamburguesa (izquierda) -->
                        <button type="button" class="btn-menu" onclick="toggleSidebar(); return false;" aria-label="Abrir/cerrar menú" title="Abrir/cerrar menú">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <!-- Contenido derecho (fecha + usuario) -->
                        <div class="navbar-right">
                            <span class="d-none d-md-inline text-muted small">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo date('d/m/Y H:i'); ?>
                            </span>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i>
                                    <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/profile"><i class="fas fa-id-card me-2"></i>Mi Perfil</a></li>
                                    <?php if ($isAdmin): ?>
                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>usuarios"><i class="fas fa-users me-2"></i>Usuarios</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </div>
                    </nav>
                    
                    <!-- CONTENIDO PRINCIPAL -->
                    <main class="main-content">
                        <?php if ($flash = $this->getFlash()): ?>
                            <div class="alert alert-<?php echo $flash['tipo']; ?> alert-dismissible fade show" role="alert">
                                <?php echo $flash['mensaje']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php require_once __DIR__ . "/../../views/{$view}.php"; ?>
                        
                        <?php require_once __DIR__ . '/../partials/footer.php'; ?>
                    </main>
                    
                </div>
            <?php else: ?>
                <main class="col-12">
                    <?php require_once __DIR__ . "/../../views/{$view}.php"; ?>
                </main>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/custom.js"></script>
    
    <!-- ============================================
         SCRIPT GLOBAL: TOGGLE SIDEBAR
         Desktop → colapsa/expande
         Móvil → abre/cierra con overlay
         ============================================ -->
    <script>
    // Recuperar estado guardado del sidebar (persistencia)
    (function() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed && window.innerWidth >= 992) {
            document.body.classList.add('sidebar-collapsed');
        }
    })();
    
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        if (!sidebar) return;
        
        if (window.innerWidth >= 992) {
            // ✅ DESKTOP: Colapsar/expandir sidebar
            document.body.classList.toggle('sidebar-collapsed');
            
            // Guardar preferencia
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        } else {
            // ✅ MÓVIL: Abrir/cerrar con overlay
            sidebar.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    };
    
    // Cerrar sidebar al hacer clic en enlace (solo móvil)
    document.addEventListener('DOMContentLoaded', function() {
        if (window.innerWidth < 992) {
            document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (!this.hasAttribute('data-bs-toggle')) {
                        const sidebar = document.getElementById('sidebar');
                        if (sidebar && sidebar.classList.contains('active')) {
                            window.toggleSidebar();
                        }
                    }
                });
            });
        }
    });
    
    // Cerrar sidebar móvil al redimensionar a desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (sidebar) sidebar.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
            
            // Restaurar estado guardado
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-collapsed');
            }
        }
    });
    
    // Cerrar con Escape (solo móvil)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (window.innerWidth < 992) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('active')) {
                    window.toggleSidebar();
                }
            }
        }
    });
    </script>
    
    <!-- Registrar Service Worker (PWA) -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('<?php echo BASE_URL; ?>sw.js')
                .catch(function(err) {
                    console.log('SW registro fallido:', err);
                });
        });
    }
    </script>
</body>
</html>