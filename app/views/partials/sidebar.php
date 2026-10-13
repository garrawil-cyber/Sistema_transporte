<?php
// ============================================================
// VARIABLES GLOBALES Y HELPERS
// ============================================================

// Detectar la sección activa según la URL
$url_actual = $_GET['url'] ?? 'home';
$seccion_actual = explode('/', $url_actual)[0];

// Inicializar arrays (ANTES de las funciones)
$favoritos = [];
$favoritos_modulos = [];

// Obtener favoritos del usuario (con protección total)
if (isset($_SESSION['user_id'])) {
    try {
        if (class_exists('Favorito')) {
            $favoritoModel = new Favorito();
            $result = $favoritoModel->getByUsuario($_SESSION['user_id']);
            if (is_array($result)) {
                $favoritos = $result;
                $favoritos_modulos = array_column($favoritos, 'modulo');
            }
        }
    } catch (Exception $e) {
        $favoritos = [];
        $favoritos_modulos = [];
    }
}

if (!is_array($favoritos))        $favoritos = [];
if (!is_array($favoritos_modulos)) $favoritos_modulos = [];

// ============================================================
// FUNCIONES HELPER (sin "global", con paso por parámetro)
// ============================================================

if (!function_exists('isActive')) {
    function isActive($seccion, $seccion_actual = null) {
        if ($seccion_actual === null) {
            $url_actual = $_GET['url'] ?? 'home';
            $seccion_actual = explode('/', $url_actual)[0];
        }
        return $seccion_actual == $seccion ? 'active' : '';
    }
}

if (!function_exists('isGroupActive')) {
    function isGroupActive($grupo, $seccion_actual = null) {
        if ($seccion_actual === null) {
            $url_actual = $_GET['url'] ?? 'home';
            $seccion_actual = explode('/', $url_actual)[0];
        }
        if (!is_array($grupo)) return '';
        return in_array($seccion_actual, $grupo, true) ? 'show' : '';
    }
}

if (!function_exists('isFavorito')) {
    function isFavorito($modulo, $favoritos_modulos = null) {
        if ($favoritos_modulos === null) {
            $favoritos_modulos = $GLOBALS['favoritos_modulos'] ?? [];
        }
        if (!is_array($favoritos_modulos)) return '';
        return in_array($modulo, $favoritos_modulos, true) ? 'favorito-activo' : '';
    }
}
?>

<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar" id="sidebar">
    <button type="button" class="btn-cerrar-sidebar" onclick="toggleSidebar(); return false;" aria-label="Cerrar menú">
        <i class="fas fa-times"></i>
    </button>

    <div class="sidebar-sticky-wrapper">
        <div class="sidebar-header">
            <h5 class="text-white text-center py-3 mb-0">
                <i class="fas fa-bus me-2"></i>Transporte
            </h5>
        </div>

        <div class="sidebar-menu">

            <!-- ⭐ ACCESOS RÁPIDOS -->
            <div class="favoritos-section" id="favoritosSection">
                <div class="favoritos-header">
                    <i class="fas fa-star text-warning me-1"></i>
                    <span>Accesos Rápidos</span>
                </div>
                <div class="favoritos-container" id="favoritosContainer">
                    <?php if (!empty($favoritos)): ?>
                        <?php foreach ($favoritos as $fav): ?>
                            <a href="<?php echo BASE_URL . $fav['url']; ?>" 
                               class="favorito-item <?php echo isActive($fav['modulo']); ?>"
                               title="<?php echo htmlspecialchars($fav['titulo']); ?>">
                                <i class="fas <?php echo $fav['icono']; ?>"></i>
                                <span><?php echo htmlspecialchars($fav['titulo']); ?></span>
                                <button class="btn-quitar-fav" 
                                        data-modulo="<?php echo $fav['modulo']; ?>"
                                        title="Quitar de favoritos">
                                    <i class="fas fa-times"></i>
                                </button>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="favoritos-vacio" id="favoritosVacio">
                            <i class="fas fa-star"></i>
                            <small>Marca tus módulos favoritos con ⭐</small>
                        </div>
                    <?php endif; ?>
                </div>
                <hr class="divisor-favoritos">
            </div>

            <!-- MENÚ PRINCIPAL -->
            <ul class="nav flex-column">

                <!-- 🏠 Dashboard -->
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white <?php echo isActive('home'); ?>" href="<?php echo BASE_URL; ?>home">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                        <button class="btn-favorito <?php echo isFavorito('home'); ?>" 
                                data-modulo="home" 
                                data-titulo="Dashboard" 
                                data-icono="fa-home" 
                                data-url="home"
                                title="Marcar como favorito">
                            <i class="fas fa-star"></i>
                        </button>
                    </div>
                </li>

                <!-- 🚌 OPERACIONES -->
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuOperaciones" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['viajes','ventas','encomiendas','giros']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-bus me-2"></i>Operaciones</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['viajes','ventas','encomiendas','giros']); ?>" id="submenuOperaciones">
                        <ul class="nav flex-column submenu">
                            <?php 
                            $operaciones = [
                                ['viajes', 'Viajes', 'fa-calendar-alt'],
                                ['ventas', 'Ventas', 'fa-ticket-alt'],
                                ['encomiendas', 'Encomiendas', 'fa-box'],
                                ['giros', 'Giros', 'fa-money-bill-transfer']
                            ];
                            foreach ($operaciones as $op): ?>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive($op[0]); ?>" href="<?php echo BASE_URL . $op[0]; ?>">
                                        <i class="fas <?php echo $op[2]; ?> me-2"></i><?php echo $op[1]; ?>
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito($op[0]); ?>" 
                                            data-modulo="<?php echo $op[0]; ?>" 
                                            data-titulo="<?php echo $op[1]; ?>" 
                                            data-icono="<?php echo $op[2]; ?>" 
                                            data-url="<?php echo $op[0]; ?>"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>

                <!-- 🏢 GESTIÓN -->
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuGestion" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['rutas','buses','clientes']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-building me-2"></i>Gestión</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['rutas','buses','clientes']); ?>" id="submenuGestion">
                        <ul class="nav flex-column submenu">
                            <?php 
                            $gestion = [
                                ['rutas', 'Rutas', 'fa-route'],
                                ['buses', 'Buses', 'fa-bus-alt'],
                                ['clientes', 'Clientes', 'fa-users']
                            ];
                            foreach ($gestion as $g): ?>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive($g[0]); ?>" href="<?php echo BASE_URL . $g[0]; ?>">
                                        <i class="fas <?php echo $g[2]; ?> me-2"></i><?php echo $g[1]; ?>
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito($g[0]); ?>" 
                                            data-modulo="<?php echo $g[0]; ?>" 
                                            data-titulo="<?php echo $g[1]; ?>" 
                                            data-icono="<?php echo $g[2]; ?>" 
                                            data-url="<?php echo $g[0]; ?>"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>

                <!-- 📄 DOCUMENTOS -->
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuDocumentos" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['manifiesto']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-file-alt me-2"></i>Documentos</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['manifiesto']); ?>" id="submenuDocumentos">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive('manifiesto'); ?>" href="<?php echo BASE_URL; ?>manifiesto">
                                        <i class="fas fa-clipboard-list me-2"></i>Manifiesto
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito('manifiesto'); ?>" 
                                            data-modulo="manifiesto" 
                                            data-titulo="Manifiesto" 
                                            data-icono="fa-clipboard-list" 
                                            data-url="manifiesto"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>manifiesto/historial">
                                    <i class="fas fa-history me-2"></i>Historial
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>

                <!-- 💰 FINANZAS (admin) -->
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuFinanzas" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['caja','reportes','factura']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-coins me-2"></i>Finanzas</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['caja','reportes','factura']); ?>" id="submenuFinanzas">
                        <ul class="nav flex-column submenu">
                            <?php 
                            $finanzas = [
                                ['caja', 'Caja', 'fa-cash-register'],
                                ['reportes', 'Reportes', 'fa-chart-bar'],
                                ['factura', 'Comprobantes', 'fa-file-invoice']
                            ];
                            foreach ($finanzas as $f): ?>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive($f[0]); ?>" href="<?php echo BASE_URL . $f[0]; ?>">
                                        <i class="fas <?php echo $f[2]; ?> me-2"></i><?php echo $f[1]; ?>
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito($f[0]); ?>" 
                                            data-modulo="<?php echo $f[0]; ?>" 
                                            data-titulo="<?php echo $f[1]; ?>" 
                                            data-icono="<?php echo $f[2]; ?>" 
                                            data-url="<?php echo $f[0]; ?>"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- 🔧 MANTENIMIENTO -->
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuMantenimiento" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['combustible','mantenimiento','conductores']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-tools me-2"></i>Mantenimiento</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['combustible','mantenimiento','conductores']); ?>" id="submenuMantenimiento">
                        <ul class="nav flex-column submenu">
                            <?php 
                            $mant_items = [
                                ['combustible', 'Combustible', 'fa-gas-pump'],
                                ['mantenimiento', 'Mantenimiento', 'fa-tools'],
                                ['conductores', 'Conductores', 'fa-id-card']
                            ];
                            foreach ($mant_items as $item): ?>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive($item[0]); ?>" href="<?php echo BASE_URL . $item[0]; ?>">
                                        <i class="fas <?php echo $item[2]; ?> me-2"></i><?php echo $item[1]; ?>
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito($item[0]); ?>" 
                                            data-modulo="<?php echo $item[0]; ?>" 
                                            data-titulo="<?php echo $item[1]; ?>" 
                                            data-icono="<?php echo $item[2]; ?>" 
                                            data-url="<?php echo $item[0]; ?>"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

                <!-- ⚙️ ADMINISTRACIÓN -->
                <?php if ($isAdmin): ?>
                <li class="nav-item">
                    <div class="menu-item-wrapper">
                        <a class="nav-link text-white d-flex justify-content-between align-items-center" 
                           data-bs-toggle="collapse" 
                           href="#submenuAdmin" 
                           role="button"
                           aria-expanded="<?php echo isGroupActive(['usuarios','backup','auditoria']) ? 'true' : 'false'; ?>">
                            <span><i class="fas fa-cog me-2"></i>Administración</span>
                            <i class="fas fa-chevron-down small"></i>
                        </a>
                    </div>
                    <div class="collapse <?php echo isGroupActive(['usuarios','backup','auditoria']); ?>" id="submenuAdmin">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive('usuarios'); ?>" href="<?php echo BASE_URL; ?>usuarios">
                                        <i class="fas fa-user-cog me-2"></i>Usuarios
                                    </a>
                                </div>
                            </li>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive('backup'); ?>" href="<?php echo BASE_URL; ?>backup">
                                        <i class="fas fa-database me-2"></i>Backups
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito('backup'); ?>" 
                                            data-modulo="backup" 
                                            data-titulo="Backups" 
                                            data-icono="fa-database" 
                                            data-url="backup"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                            <li class="nav-item">
                                <div class="menu-item-wrapper">
                                    <a class="nav-link text-white <?php echo isActive('auditoria'); ?>" href="<?php echo BASE_URL; ?>auditoria">
                                        <i class="fas fa-shield-alt me-2"></i>Auditoría
                                    </a>
                                    <button class="btn-favorito <?php echo isFavorito('auditoria'); ?>" 
                                            data-modulo="auditoria" 
                                            data-titulo="Auditoría" 
                                            data-icono="fa-shield-alt" 
                                            data-url="auditoria"
                                            title="Marcar como favorito">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>

<!-- ============================================
     SCRIPT DE FAVORITOS
     ============================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = '<?php echo BASE_URL; ?>';

    // ============================================
    // 1. Agregar/Quitar favorito (estrella)
    // ============================================
    document.querySelectorAll('.btn-favorito').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const modulo = this.dataset.modulo;
            const titulo = this.dataset.titulo;
            const icono = this.dataset.icono;
            const url = this.dataset.url;

            fetch(BASE_URL + 'favorito/toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `modulo=${encodeURIComponent(modulo)}&titulo=${encodeURIComponent(titulo)}&icono=${encodeURIComponent(icono)}&url=${encodeURIComponent(url)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        this.classList.add('favorito-activo');
                        mostrarNotificacion('⭐ Agregado a favoritos', 'success');
                    } else if (data.action === 'removed') {
                        this.classList.remove('favorito-activo');
                        mostrarNotificacion('Eliminado de favoritos', 'info');
                    }
                    actualizarFavoritos();
                } else {
                    mostrarNotificacion(data.message, 'warning');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                mostrarNotificacion('Error al procesar', 'danger');
            });
        });
    });

    // ============================================
    // 2. Quitar favorito desde la sección superior
    // ============================================
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-quitar-fav')) {
            e.preventDefault();
            e.stopPropagation();

            const btn = e.target.closest('.btn-quitar-fav');
            const modulo = btn.dataset.modulo;

            fetch(BASE_URL + 'favorito/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `modulo=${encodeURIComponent(modulo)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('Eliminado de favoritos', 'info');
                    actualizarFavoritos();
                    const estrella = document.querySelector(`.btn-favorito[data-modulo="${modulo}"]`);
                    if (estrella) estrella.classList.remove('favorito-activo');
                }
            });
        }
    });

    // ============================================
    // 3. Actualizar sección de favoritos dinámicamente
    // ============================================
    function actualizarFavoritos() {
        fetch(BASE_URL + 'favorito/listar')
        .then(res => res.json())
        .then(favoritos => {
            const container = document.getElementById('favoritosContainer');
            
            if (favoritos.length === 0) {
                container.innerHTML = `
                    <div class="favoritos-vacio">
                        <i class="fas fa-star"></i>
                        <small>Marca tus módulos favoritos con ⭐</small>
                    </div>
                `;
                return;
            }

            let html = '';
            favoritos.forEach(fav => {
                const activo = '<?php echo $seccion_actual; ?>' === fav.modulo ? 'active' : '';
                html += `
                    <a href="${BASE_URL}${fav.url}" class="favorito-item ${activo}" title="${fav.titulo}">
                        <i class="fas ${fav.icono}"></i>
                        <span>${fav.titulo}</span>
                        <button class="btn-quitar-fav" data-modulo="${fav.modulo}" title="Quitar de favoritos">
                            <i class="fas fa-times"></i>
                        </button>
                    </a>
                `;
            });
            container.innerHTML = html;
        });
    }

    // ============================================
    // 4. Notificaciones toast
    // ============================================
    function mostrarNotificacion(mensaje, tipo = 'success') {
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
            document.body.appendChild(toastContainer);
        }

        const colores = {
            'success': '#198754',
            'info': '#0dcaf0',
            'warning': '#ffc107',
            'danger': '#dc3545'
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: ${colores[tipo]};
            color: ${tipo === 'warning' ? '#000' : '#fff'};
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 0.9rem;
            animation: slideIn 0.3s ease;
            min-width: 220px;
        `;
        toast.textContent = mensaje;
        toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
});
</script>

<style>
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }

    .sidebar { z-index: 1000; }

    .favoritos-section {
        padding: 0 0.5rem 0.5rem 0.5rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .favoritos-header {
        color: #ffc107;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        padding: 0.5rem 0.5rem 0.4rem 0.5rem;
        display: flex;
        align-items: center;
    }

    .favoritos-container {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .favorito-item {
        display: flex;
        align-items: center;
        padding: 0.45rem 0.7rem;
        background: rgba(255, 193, 7, 0.08);
        border-left: 3px solid #ffc107;
        border-radius: 4px;
        color: #ffd75e;
        text-decoration: none;
        font-size: 0.82rem;
        transition: all 0.2s ease;
        position: relative;
    }

    .favorito-item:hover {
        background: rgba(255, 193, 7, 0.18);
        color: #fff;
        transform: translateX(3px);
    }

    .favorito-item.active {
        background: rgba(255, 193, 7, 0.25);
        color: #fff;
        font-weight: 600;
    }

    .favorito-item i:first-child {
        width: 18px;
        text-align: center;
        margin-right: 8px;
        font-size: 0.85rem;
    }

    .favorito-item span {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .favorito-item .btn-quitar-fav {
        background: transparent;
        border: none;
        color: #adb5bd;
        padding: 0;
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
        font-size: 0.65rem;
        cursor: pointer;
    }

    .favorito-item:hover .btn-quitar-fav { opacity: 1; }
    .favorito-item .btn-quitar-fav:hover {
        color: #dc3545;
        transform: scale(1.2);
    }

    .favoritos-vacio {
        text-align: center;
        color: #6c757d;
        padding: 0.6rem 0.3rem;
        font-size: 0.75rem;
        line-height: 1.3;
    }
    .favoritos-vacio i {
        display: block;
        font-size: 1.1rem;
        margin-bottom: 4px;
        opacity: 0.4;
    }

    .sidebar .nav-link {
        font-weight: 500;
        color: #adb5bd;
        padding: 0.65rem 1.2rem;
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        white-space: nowrap;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .sidebar .nav-link i:first-child {
        width: 20px;
        text-align: center;
        font-size: 0.9rem;
    }

    .sidebar .nav-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, .08);
        border-left-color: #0d6efd;
    }

    .sidebar .nav-link.active {
        color: #fff;
        background: rgba(13, 110, 253, .15);
        border-left-color: #0d6efd;
        font-weight: 600;
    }

    .sidebar .nav-link[data-bs-toggle="collapse"] .fa-chevron-down {
        transition: transform 0.3s ease;
        font-size: 0.65rem;
        color: #6c757d;
    }
    .sidebar .nav-link[aria-expanded="true"] .fa-chevron-down {
        transform: rotate(180deg);
        color: #0d6efd;
    }

    .sidebar .submenu {
        background: rgba(0, 0, 0, 0.25);
        padding: 0.3rem 0;
    }

    .sidebar .submenu .nav-link {
        padding-left: 2.5rem;
        font-size: 0.85rem;
        color: #9ca3af;
    }
    .sidebar .submenu .nav-link i {
        font-size: 0.78rem;
        opacity: 0.7;
    }
    .sidebar .submenu .nav-link:hover {
        color: #fff;
        background: rgba(255, 255, 255, .05);
        border-left-color: #0dcaf0;
    }
    .sidebar .submenu .nav-link.active {
        color: #fff;
        background: rgba(13, 110, 253, .25);
        border-left-color: #0dcaf0;
        font-weight: 600;
    }
    .sidebar .submenu .nav-link.active i {
        color: #0dcaf0;
        opacity: 1;
    }

    .menu-item-wrapper {
        display: flex;
        align-items: center;
        position: relative;
    }

    .menu-item-wrapper .nav-link { flex: 1; }

    .btn-favorito {
        background: transparent;
        border: none;
        color: #495057;
        padding: 0.4rem 0.7rem;
        cursor: pointer;
        font-size: 0.75rem;
        transition: all 0.2s ease;
        opacity: 0;
        flex-shrink: 0;
    }

    .menu-item-wrapper:hover .btn-favorito {
        opacity: 1;
        color: #6c757d;
    }

    .btn-favorito:hover {
        color: #ffc107 !important;
        transform: scale(1.2);
    }

    .btn-favorito.favorito-activo {
        opacity: 1;
        color: #ffc107;
        text-shadow: 0 0 8px rgba(255, 193, 7, 0.6);
    }

    @media (max-width: 767.98px) {
        .btn-favorito { opacity: 1; }
    }
</style>