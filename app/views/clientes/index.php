<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2 mb-0">
            <i class="fas fa-users text-primary me-2"></i><?php echo $titulo ?? 'Gestión de Clientes'; ?>
        </h1>
        <p class="text-muted mb-0 small">
            Administra tu cartera de clientes con búsqueda en tiempo real
        </p>
    </div>
    <a href="<?php echo BASE_URL; ?>clientes/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Nuevo Cliente
    </a>
</div>

<!-- ============================================================
     BÚSQUEDA AJAX EN TIEMPO REAL
     ============================================================ -->
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <div class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-primary text-white">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text"
                           id="buscarAjax"
                           class="form-control"
                           placeholder="Escribe para buscar por nombre, apellido, DNI o email..."
                           autocomplete="off"
                           value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                    <span class="input-group-text" id="loaderBuscar" style="display:none;">
                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    </span>
                    <button class="btn btn-outline-secondary" type="button" id="btnLimpiar" title="Limpiar búsqueda">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Los resultados se actualizan automáticamente mientras escribes
                    <kbd class="ms-2 d-none d-md-inline">Ctrl + K</kbd>
                </small>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportarExcel()" title="Exportar a Excel">
                        <i class="fas fa-file-excel me-1"></i>Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="exportarPDF()" title="Exportar a PDF">
                        <i class="fas fa-file-pdf me-1"></i>PDF
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()" title="Imprimir">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     TARJETAS DE RESUMEN
     ============================================================ -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Clientes</small>
                        <h4 class="mb-0" id="totalClientes">
                            <?php echo isset($paginator) ? $paginator->total_registros : count($clientes ?? []); ?>
                        </h4>
                    </div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-success bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Clientes Frecuentes</small>
                        <h4 class="mb-0" id="clientesFrecuentes">
                            <?php
                                $frecuentes = 0;
                                foreach ($clientes ?? [] as $c) {
                                    if (($c['total_compras'] ?? 0) >= 10) $frecuentes++;
                                }
                                echo $frecuentes;
                            ?>
                        </h4>
                    </div>
                    <i class="fas fa-star fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-info bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Compras del Mes</small>
                        <h4 class="mb-0" id="comprasMes">
                            <?php
                                $compras = 0;
                                foreach ($clientes ?? [] as $c) {
                                    $compras += (int)($c['compras_ultimo_mes'] ?? 0);
                                }
                                echo $compras;
                            ?>
                        </h4>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
            <div class="card-body py-2 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted d-block">Nuevos Este Mes</small>
                        <h4 class="mb-0" id="clientesNuevos">
                            <?php
                                $nuevos = 0;
                                $mes_actual = date('Y-m');
                                foreach ($clientes ?? [] as $c) {
                                    if (!empty($c['created_at']) && substr($c['created_at'], 0, 7) === $mes_actual) {
                                        $nuevos++;
                                    }
                                }
                                echo $nuevos;
                            ?>
                        </h4>
                    </div>
                    <i class="fas fa-user-plus fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     CONTENEDOR DE RESULTADOS (se llena con AJAX)
     ============================================================ -->
<div id="resultadosClientes">
    <?php if (isset($clientes)): ?>
        <?php require __DIR__ . '/_tabla.php'; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2">Cargando clientes...</p>
        </div>
    <?php endif; ?>
</div>

<!-- FAB móvil -->
<a href="<?php echo BASE_URL; ?>clientes/create" class="fab-mobile" title="Nuevo Cliente">
    <i class="fas fa-plus"></i>
</a>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('buscarAjax');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const loader = document.getElementById('loaderBuscar');
    const container = document.getElementById('resultadosClientes');
    const baseUrl = '<?php echo BASE_URL; ?>';

    let timeoutId = null;
    let ultimaBusqueda = inputBuscar.value.trim();
    let paginaActual = 1;

    // Cargar resultados iniciales si no vienen del server
    if (!container.querySelector('table')) {
        cargarResultados(ultimaBusqueda, 1);
    }

    inputBuscar.addEventListener('input', function() {
        const termino = this.value.trim();
        clearTimeout(timeoutId);

        if (termino !== ultimaBusqueda) {
            loader.style.display = 'inline-flex';
        }

        timeoutId = setTimeout(() => {
            if (termino !== ultimaBusqueda) {
                paginaActual = 1;
                cargarResultados(termino, 1);
                ultimaBusqueda = termino;
            }
        }, 350);
    });

    btnLimpiar.addEventListener('click', function() {
        inputBuscar.value = '';
        ultimaBusqueda = '';
        paginaActual = 1;
        cargarResultados('', 1);
        inputBuscar.focus();
    });

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            inputBuscar.focus();
            inputBuscar.select();
        }
        if (e.key === 'Escape' && document.activeElement === inputBuscar) {
            btnLimpiar.click();
        }
    });

    function cargarResultados(termino, pagina) {
        pagina = pagina || 1;
        loader.style.display = 'inline-flex';

        let url = baseUrl + 'clientes/buscarAjax?pagina=' + pagina;
        if (termino) url += '&buscar=' + encodeURIComponent(termino);

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Error en la respuesta');
                return res.text();
            })
            .then(html => {
                container.style.opacity = '0.5';
                setTimeout(() => {
                    container.innerHTML = html;
                    container.style.opacity = '1';
                    loader.style.display = 'none';
                    actualizarContadores();
                }, 100);
            })
            .catch(err => {
                console.error('Error:', err);
                loader.style.display = 'none';
                container.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                        <div>
                            <strong>Error de conexión</strong>
                            <p class="mb-0 small">No se pudieron cargar los resultados. Intente de nuevo.</p>
                        </div>
                    </div>
                `;
            });
    }

    // Recalcular contadores desde la tabla cargada
    function actualizarContadores() {
        const filas = container.querySelectorAll('tbody tr');
        let frecuentes = 0, nuevos = 0, compras = 0;

        filas.forEach(tr => {
            if (tr.querySelector('.badge.bg-warning[title="Cliente frecuente"]')) frecuentes++;
            const badgeCompras = tr.querySelector('[data-label="Compras"] .badge');
            if (badgeCompras) compras += parseInt(badgeCompras.textContent) || 0;
        });

        document.getElementById('clientesFrecuentes').textContent = frecuentes;
        document.getElementById('comprasMes').textContent = compras;
        // totalClientes y clientesNuevos se dejan tal cual del server
    }

    // Paginación AJAX
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('#resultadosClientes .page-link');
        if (pageLink && !pageLink.closest('.disabled')) {
            e.preventDefault();
            const url = new URL(pageLink.href, window.location.origin);
            const pagina = url.searchParams.get('pagina');
            const termino = inputBuscar.value.trim();
            paginaActual = parseInt(pagina) || 1;
            cargarResultados(termino, paginaActual);
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    window.exportarExcel = function() {
        const termino = inputBuscar.value.trim();
        let url = baseUrl + 'clientes/exportarExcel';
        if (termino) url += '?buscar=' + encodeURIComponent(termino);
        window.location.href = url;
    };

    window.exportarPDF = function() {
        const termino = inputBuscar.value.trim();
        let url = baseUrl + 'clientes/exportarPDF';
        if (termino) url += '?buscar=' + encodeURIComponent(termino);
        window.open(url, '_blank');
    };
});
</script>

<style>
    #buscarAjax { border-left: none; font-size: 0.95rem; }
    #buscarAjax:focus { box-shadow: none; border-color: #0d6efd; }
    .input-group:focus-within .input-group-text { border-color: #0d6efd; }
    #loaderBuscar { background: white; border-left: none; }
    #resultadosClientes { transition: opacity 0.2s ease; min-height: 200px; }

    mark.busqueda-resaltada {
        background: #fff3cd;
        color: #856404;
        padding: 0 2px;
        border-radius: 2px;
        font-weight: 600;
    }

    .card.bg-primary.bg-opacity-10,
    .card.bg-success.bg-opacity-10,
    .card.bg-info.bg-opacity-10,
    .card.bg-warning.bg-opacity-10 {
        transition: transform 0.2s ease;
    }
    .card.bg-primary.bg-opacity-10:hover,
    .card.bg-success.bg-opacity-10:hover,
    .card.bg-info.bg-opacity-10:hover,
    .card.bg-warning.bg-opacity-10:hover {
        transform: translateY(-3px);
    }

    @media (max-width: 768px) {
        .input-group-lg .form-control {
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        .input-group-lg .input-group-text,
        .input-group-lg .btn {
            padding: 0.5rem 0.75rem;
        }
        .btn-group .btn {
            padding: 0.4rem 0.6rem;
            font-size: 0.75rem;
        }
    }
</style>