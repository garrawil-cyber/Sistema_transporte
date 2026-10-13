<?php
class Combustible extends Model {
    protected $table = 'combustible_cargas';

    /**
     * Obtener cargas paginadas con filtros
     */
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT c.*, 
                       b.placa, b.marca, b.modelo,
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                       CONCAT(co.nombre, ' ', co.apellido) as conductor_nombre,
                       r.origen, r.destino
                FROM combustible_cargas c
                JOIN buses b ON c.bus_id = b.id
                JOIN usuarios u ON c.usuario_id = u.id
                LEFT JOIN usuarios co ON c.conductor_id = co.id
                LEFT JOIN viajes v ON c.viaje_id = v.id
                LEFT JOIN rutas r ON v.ruta_id = r.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (b.placa LIKE ? OR c.estacion_servicio LIKE ? OR c.numero_factura LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['bus_id'])) {
            $sql .= " AND c.bus_id = ?";
            $params[] = $filtros['bus_id'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(c.fecha_carga) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(c.fecha_carga) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $sql .= " ORDER BY c.fecha_carga DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    /**
     * Resumen de combustible
     */
    public function getResumen($filtros = []) {
        $sql = "SELECT 
                    COUNT(*) as total_cargas,
                    COALESCE(SUM(cantidad_galones), 0) as total_galones,
                    COALESCE(SUM(precio_total), 0) as total_gastado,
                    COALESCE(AVG(precio_galon), 0) as precio_promedio,
                    COALESCE(AVG(rendimiento_km_galon), 0) as rendimiento_promedio
                FROM combustible_cargas c
                JOIN buses b ON c.bus_id = b.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['bus_id'])) {
            $sql .= " AND c.bus_id = ?";
            $params[] = $filtros['bus_id'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(c.fecha_carga) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(c.fecha_carga) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Registrar carga (calcula automáticamente rendimiento)
     */
    public function registrar($data) {
        // Obtener el kilometraje anterior del bus
        $sql = "SELECT kilometraje_actual FROM combustible_cargas 
                WHERE bus_id = ? 
                ORDER BY fecha_carga DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$data['bus_id']]);
        $ultima = $stmt->fetch();
        
        $data['kilometraje_anterior'] = $ultima['kilometraje_actual'] ?? null;
        
        // Calcular kilómetros recorridos y rendimiento
        if (!empty($data['kilometraje_actual']) && !empty($data['kilometraje_anterior'])) {
            $data['kilometros_recorridos'] = $data['kilometraje_actual'] - $data['kilometraje_anterior'];
            if ($data['cantidad_galones'] > 0) {
                $data['rendimiento_km_galon'] = round($data['kilometros_recorridos'] / $data['cantidad_galones'], 2);
            }
        }
        
        // Calcular precio total
        $data['precio_total'] = $data['cantidad_galones'] * $data['precio_galon'];
        
        return $this->create($data);
    }

    /**
     * Estadísticas por bus
     */
    public function getEstadisticasPorBus($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    b.id, b.placa, b.marca, b.modelo,
                    COUNT(c.id) as total_cargas,
                    COALESCE(SUM(c.cantidad_galones), 0) as total_galones,
                    COALESCE(SUM(c.precio_total), 0) as total_gastado,
                    COALESCE(AVG(c.rendimiento_km_galon), 0) as rendimiento_promedio
                FROM buses b
                LEFT JOIN combustible_cargas c ON b.id = c.bus_id";
        
        $params = [];
        $where = [];
        
        if (!empty($fecha_inicio)) {
            $where[] = "DATE(c.fecha_carga) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $where[] = "DATE(c.fecha_carga) <= ?";
            $params[] = $fecha_fin;
        }
        
        if (!empty($where)) {
            $sql .= " AND " . implode(' AND ', $where);
        }
        
        $sql .= " GROUP BY b.id ORDER BY total_gastado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener cargas de un bus específico
     */
    public function getByBus($bus_id, $limite = 10) {
        $limite = (int)$limite;
        $sql = "SELECT * FROM combustible_cargas 
                WHERE bus_id = ? 
                ORDER BY fecha_carga DESC 
                LIMIT $limite";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bus_id]);
        return $stmt->fetchAll();
    }
    public function getById($id) {
    $sql = "SELECT c.*, 
                   b.placa, b.marca, b.modelo,
                   CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                   CONCAT(co.nombre, ' ', co.apellido) as conductor_nombre,
                   r.origen, r.destino
            FROM combustible_cargas c
            JOIN buses b ON c.bus_id = b.id
            JOIN usuarios u ON c.usuario_id = u.id
            LEFT JOIN usuarios co ON c.conductor_id = co.id
            LEFT JOIN viajes v ON c.viaje_id = v.id
            LEFT JOIN rutas r ON v.ruta_id = r.id
            WHERE c.id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

}