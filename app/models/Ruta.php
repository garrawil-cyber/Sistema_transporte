<?php
class Ruta extends Model {
    protected $table = 'rutas';

    // Validación personalizada
    public function validate($data) {
        $errors = [];
        if (empty($data['origen'])) $errors[] = 'El origen es obligatorio';
        if (empty($data['destino'])) $errors[] = 'El destino es obligatorio';
        if (!isset($data['precio_base']) || $data['precio_base'] <= 0) $errors[] = 'El precio debe ser mayor a 0';
        return $errors;
    }

    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
    $sql = "SELECT * FROM rutas WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (origen LIKE ? OR destino LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar]);
    }
    
    $sql .= " ORDER BY origen ASC, destino ASC";
    
    return $this->paginate($sql, $params, $pagina, $por_pagina);
}
}