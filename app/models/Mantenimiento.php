<?php
class Mantenimiento extends Model {
    protected $table = 'mantenimientos';

    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT m.*, 
                       b.placa, b.marca, b.modelo,
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM mantenimientos m
                JOIN buses b ON m.bus_id = b.id
                JOIN usuarios u ON m.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (b.placa LIKE ? OR m.descripcion LIKE ? OR m.taller_responsable LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['bus_id'])) {
            $sql .= " AND m.bus_id = ?";
            $params[] = $filtros['bus_id'];
        }
        if (!empty($filtros['tipo'])) {
            $sql .= " AND m.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND m.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(m.fecha_programada) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(m.fecha_programada) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $sql .= " ORDER BY m.fecha_programada DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    public function getResumen($filtros = []) {
    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN m.estado = 'PROGRAMADO' THEN 1 ELSE 0 END) as programados,
                SUM(CASE WHEN m.estado = 'EN_PROCESO' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN m.estado = 'COMPLETADO' THEN 1 ELSE 0 END) as completados,
                COALESCE(SUM(m.costo_total), 0) as total_gastado
            FROM mantenimientos m
            JOIN buses b ON m.bus_id = b.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['bus_id'])) {
        $sql .= " AND m.bus_id = ?";
        $params[] = $filtros['bus_id'];
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(m.fecha_programada) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(m.fecha_programada) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

    public function registrar($data) {
        $data['costo_total'] = ($data['costo_mano_obra'] ?? 0) + ($data['costo_repuestos'] ?? 0);
        return $this->create($data);
    }

    /**
     * Obtener próximos mantenimientos programados (alertas)
     */
    public function getProximos($dias = 30) {
        $dias = (int)$dias;
        $sql = "SELECT m.*, b.placa 
                FROM mantenimientos m
                JOIN buses b ON m.bus_id = b.id
                WHERE m.estado = 'PROGRAMADO'
                AND m.fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $dias DAY)
                ORDER BY m.fecha_programada ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cambiar estado del mantenimiento
     */
    public function cambiarEstado($id, $estado, $fecha_realizada = null) {
        $sql = "UPDATE mantenimientos SET estado = ?, fecha_realizada = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $fecha_realizada, $id]);
    }
}