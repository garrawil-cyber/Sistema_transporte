<?php
/**
 * Componente reutilizable de paginación
 * 
 * Variables esperadas:
 * @var Paginator $paginator
 */
if (!isset($paginator) || !($paginator instanceof Paginator)) {
    return;
}

// Si solo hay una página, no mostrar la paginación
if ($paginator->total_paginas <= 1 && $paginator->total_registros <= $paginator->registros_por_pagina) {
    echo '<div class="text-muted small py-2">' . $paginator->getInfoRegistros() . '</div>';
    return;
}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-2">
    <!-- Info de registros -->
    <div class="text-muted small">
        <?php echo $paginator->getInfoRegistros(); ?>
    </div>

    <!-- Controles de paginación -->
    <nav aria-label="Paginación">
        <ul class="pagination pagination-sm mb-0">
            <!-- Primera página -->
            <?php if ($paginator->hayAnterior() && $paginator->pagina_actual > 3): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo $paginator->urlPagina(1); ?>" title="Primera página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Anterior -->
            <li class="page-item <?php echo !$paginator->hayAnterior() ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="<?php echo $paginator->hayAnterior() ? $paginator->urlPagina($paginator->pagina_actual - 1) : '#'; ?>"
                   title="Página anterior">
                    <i class="fas fa-angle-left"></i>
                </a>
            </li>

            <!-- Números de página -->
            <?php foreach ($paginator->getRangoPaginas() as $num): ?>
                <li class="page-item <?php echo $num == $paginator->pagina_actual ? 'active' : ''; ?>">
                    <a class="page-link" href="<?php echo $paginator->urlPagina($num); ?>">
                        <?php echo $num; ?>
                    </a>
                </li>
            <?php endforeach; ?>

            <!-- Siguiente -->
            <li class="page-item <?php echo !$paginator->haySiguiente() ? 'disabled' : ''; ?>">
                <a class="page-link" 
                   href="<?php echo $paginator->haySiguiente() ? $paginator->urlPagina($paginator->pagina_actual + 1) : '#'; ?>"
                   title="Página siguiente">
                    <i class="fas fa-angle-right"></i>
                </a>
            </li>

            <!-- Última página -->
            <?php if ($paginator->haySiguiente() && $paginator->pagina_actual < $paginator->total_paginas - 2): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo $paginator->urlPagina($paginator->total_paginas); ?>" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Selector de registros por página -->
    <div class="d-flex align-items-center gap-2">
        <label class="text-muted small mb-0">Mostrar:</label>
        <select class="form-select form-select-sm" style="width:auto;" onchange="cambiarPorPagina(this.value)">
            <?php foreach ([10, 15, 25, 50, 100] as $opcion): ?>
                <option value="<?php echo $opcion; ?>" <?php echo $paginator->registros_por_pagina == $opcion ? 'selected' : ''; ?>>
                    <?php echo $opcion; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<script>
function cambiarPorPagina(valor) {
    const url = new URL(window.location.href);
    url.searchParams.set('por_pagina', valor);
    url.searchParams.set('pagina', 1); // Volver a la primera página
    window.location.href = url.toString();
}
</script>