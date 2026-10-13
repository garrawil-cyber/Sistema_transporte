<?php
/**
 * Clase auxiliar para manejar la paginación
 */
class Paginator {
    public $total_registros;
    public $registros_por_pagina;
    public $pagina_actual;
    public $total_paginas;
    public $offset;
    
    /**
     * @param int $total_registros Total de filas de la consulta
     * @param int $registros_por_pagina Cuántos se muestran por página
     * @param int $pagina_actual Página actual (desde $_GET)
     */
    public function __construct($total_registros, $registros_por_pagina = 15, $pagina_actual = 1) {
        $this->total_registros = (int)$total_registros;
        $this->registros_por_pagina = (int)$registros_por_pagina;
        $this->pagina_actual = max(1, (int)$pagina_actual);
        $this->total_paginas = max(1, ceil($this->total_registros / $this->registros_por_pagina));
        
        // Asegurar que la página actual no exceda el total
        if ($this->pagina_actual > $this->total_paginas) {
            $this->pagina_actual = $this->total_paginas;
        }
        
        $this->offset = ($this->pagina_actual - 1) * $this->registros_por_pagina;
    }
    
    /**
     * ¿Hay página anterior?
     */
    public function hayAnterior() {
        return $this->pagina_actual > 1;
    }
    
    /**
     * ¿Hay página siguiente?
     */
    public function haySiguiente() {
        return $this->pagina_actual < $this->total_paginas;
    }
    
    /**
     * Obtener el rango de páginas para mostrar (ej: 1, 2, 3 ... 8, 9, 10)
     */
    public function getRangoPaginas($max_visible = 7) {
        $inicio = max(1, $this->pagina_actual - floor($max_visible / 2));
        $fin = min($this->total_paginas, $inicio + $max_visible - 1);
        
        // Ajustar si estamos cerca del final
        if ($fin - $inicio + 1 < $max_visible) {
            $inicio = max(1, $fin - $max_visible + 1);
        }
        
        return range($inicio, $fin);
    }
    
    /**
     * Mostrar info "Mostrando X a Y de Z registros"
     */
    public function getInfoRegistros() {
        if ($this->total_registros == 0) {
            return 'No hay registros';
        }
        
        $desde = $this->offset + 1;
        $hasta = min($this->offset + $this->registros_por_pagina, $this->total_registros);
        
        return "Mostrando <strong>{$desde}</strong> a <strong>{$hasta}</strong> de <strong>{$this->total_registros}</strong> registros";
    }
    
    /**
     * Generar URL preservando filtros actuales
     */
    public function urlPagina($pagina) {
        $params = $_GET;
        $params['pagina'] = $pagina;
        return '?' . http_build_query($params);
    }
}