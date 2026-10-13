<?php
class Reporte extends Model {
    protected $table = 'ventas_pasajes';

    /**
     * ============================================
     * REPORTE DE VENTAS
     * ============================================
     */
    public function getVentas($fecha_inicio = null, $fecha_fin = null, $ruta_id = null, $usuario_id = null) {
        $sql = "SELECT vp.*, 
                       c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.numero_documento,
                       r.origen, r.destino, r.id as ruta_id,
                       v.fecha_salida,
                       b.placa,
                       a.numero_asiento,
                       CONCAT(u.nombre, ' ', u.apellido) as vendedor
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                JOIN asientos a ON vp.asiento_id = a.id
                JOIN usuarios u ON vp.usuario_id = u.id
                WHERE vp.estado = 'ACTIVA'";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(vp.fecha_venta) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(vp.fecha_venta) <= ?";
            $params[] = $fecha_fin;
        }
        if (!empty($ruta_id)) {
            $sql .= " AND r.id = ?";
            $params[] = $ruta_id;
        }
        if (!empty($usuario_id)) {
            $sql .= " AND vp.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        $sql .= " ORDER BY vp.fecha_venta DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Resumen de ventas (totales)
     */
    public function getResumenVentas($fecha_inicio = null, $fecha_fin = null, $usuario_id = null) {
        $sql = "SELECT 
                    COUNT(*) as total_ventas,
                    SUM(precio_pagado) as total_recaudado,
                    AVG(precio_pagado) as promedio,
                    COUNT(CASE WHEN metodo_pago = 'EFECTIVO' THEN 1 END) as efectivo,
                    COUNT(CASE WHEN metodo_pago = 'TARJETA' THEN 1 END) as tarjeta,
                    COUNT(CASE WHEN metodo_pago = 'TRANSFERENCIA' THEN 1 END) as transferencia,
                    SUM(CASE WHEN metodo_pago = 'EFECTIVO' THEN precio_pagado ELSE 0 END) as monto_efectivo,
                    SUM(CASE WHEN metodo_pago = 'TARJETA' THEN precio_pagado ELSE 0 END) as monto_tarjeta,
                    SUM(CASE WHEN metodo_pago = 'TRANSFERENCIA' THEN precio_pagado ELSE 0 END) as monto_transferencia
                FROM ventas_pasajes
                WHERE estado = 'ACTIVA'";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha_venta) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha_venta) <= ?";
            $params[] = $fecha_fin;
        }
        if (!empty($usuario_id)) {
            $sql .= " AND usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Ventas agrupadas por ruta
     */
    public function getVentasPorRuta($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    r.origen, r.destino,
                    COUNT(vp.id) as total_ventas,
                    SUM(vp.precio_pagado) as total_recaudado,
                    AVG(vp.precio_pagado) as promedio
                FROM ventas_pasajes vp
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                WHERE vp.estado = 'ACTIVA'";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(vp.fecha_venta) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(vp.fecha_venta) <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY r.id ORDER BY total_recaudado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ventas agrupadas por vendedor
     */
    public function getVentasPorVendedor($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    u.id, CONCAT(u.nombre, ' ', u.apellido) as vendedor,
                    COUNT(vp.id) as total_ventas,
                    SUM(vp.precio_pagado) as total_recaudado,
                    AVG(vp.precio_pagado) as promedio
                FROM ventas_pasajes vp
                JOIN usuarios u ON vp.usuario_id = u.id
                WHERE vp.estado = 'ACTIVA'";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(vp.fecha_venta) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(vp.fecha_venta) <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY u.id ORDER BY total_recaudado DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * ============================================
     * REPORTE DE ENCOMIENDAS
     * ============================================
     */
    public function getEncomiendas($fecha_inicio = null, $fecha_fin = null, $estado = null) {
        $sql = "SELECT e.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, r.numero_documento as remitente_dni,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.numero_documento as destinatario_dni,
                       rt.origen, rt.destino,
                       b.placa,
                       CONCAT(u.nombre, ' ', u.apellido) as registrado_por
                FROM encomiendas e
                JOIN clientes r ON e.remitente_id = r.id
                JOIN clientes d ON e.destinatario_id = d.id
                JOIN viajes v ON e.viaje_id = v.id
                JOIN rutas rt ON v.ruta_id = rt.id
                JOIN buses b ON v.bus_id = b.id
                JOIN usuarios u ON e.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(e.fecha_registro) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(e.fecha_registro) <= ?";
            $params[] = $fecha_fin;
        }
        if (!empty($estado)) {
            $sql .= " AND e.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY e.fecha_registro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getResumenEncomiendas($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    COUNT(*) as total_encomiendas,
                    SUM(costo_envio) as total_recaudado,
                    COUNT(CASE WHEN estado = 'REGISTRADO' THEN 1 END) as registradas,
                    COUNT(CASE WHEN estado = 'EN_BODEGA' THEN 1 END) as en_bodega,
                    COUNT(CASE WHEN estado = 'EN_TRANSITO' THEN 1 END) as en_transito,
                    COUNT(CASE WHEN estado = 'ENTREGADO' THEN 1 END) as entregadas,
                    COUNT(CASE WHEN estado = 'DEVUELTO' THEN 1 END) as devueltas,
                    SUM(peso_kg) as total_peso
                FROM encomiendas
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha_registro) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha_registro) <= ?";
            $params[] = $fecha_fin;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * ============================================
     * REPORTE DE GIROS
     * ============================================
     */
    public function getGiros($fecha_inicio = null, $fecha_fin = null, $estado = null) {
        $sql = "SELECT g.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, r.numero_documento as remitente_dni,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.numero_documento as destinatario_dni,
                       CONCAT(u.nombre, ' ', u.apellido) as registrado_por
                FROM giros g
                JOIN clientes r ON g.remitente_id = r.id
                JOIN clientes d ON g.destinatario_id = d.id
                JOIN usuarios u ON g.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(g.fecha_registro) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(g.fecha_registro) <= ?";
            $params[] = $fecha_fin;
        }
        if (!empty($estado)) {
            $sql .= " AND g.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY g.fecha_registro DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getResumenGiros($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    COUNT(*) as total_giros,
                    SUM(monto) as total_monto,
                    SUM(comision) as total_comision,
                    COUNT(CASE WHEN estado = 'REGISTRADO' THEN 1 END) as registrados,
                    COUNT(CASE WHEN estado = 'PAGADO' THEN 1 END) as pagados,
                    COUNT(CASE WHEN estado = 'ANULADO' THEN 1 END) as anulados
                FROM giros
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha_registro) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha_registro) <= ?";
            $params[] = $fecha_fin;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * ============================================
     * REPORTE DE OCUPABILIDAD
     * ============================================
     */
    public function getOcupabilidad($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    v.id as viaje_id,
                    r.origen, r.destino,
                    b.placa, b.capacidad_total,
                    v.fecha_salida,
                    v.estado as estado_viaje,
                    COUNT(DISTINCT a.id) as total_asientos,
                    COUNT(DISTINCT CASE WHEN a.disponible = 0 THEN a.id END) as asientos_ocupados,
                    COUNT(DISTINCT CASE WHEN a.disponible = 1 THEN a.id END) as asientos_libres,
                    ROUND((COUNT(DISTINCT CASE WHEN a.disponible = 0 THEN a.id END) / COUNT(DISTINCT a.id)) * 100, 2) as porcentaje_ocupacion,
                    COALESCE(SUM(vp.precio_pagado), 0) as total_recaudado
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN asientos a ON v.id = a.viaje_id
                LEFT JOIN ventas_pasajes vp ON v.id = vp.viaje_id AND vp.estado = 'ACTIVA'
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(v.fecha_salida) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(v.fecha_salida) <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY v.id ORDER BY v.fecha_salida DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * ============================================
     * REPORTE DE CAJA (por usuario/día)
     * ============================================
     */
public function getCierreCaja($usuario_id, $fecha) {
    try {
        $sql = "SELECT 
                    vp.metodo_pago,
                    COUNT(*) as cantidad,
                    COALESCE(SUM(vp.precio_pagado), 0) as total
                FROM ventas_pasajes vp
                WHERE vp.usuario_id = ?
                AND DATE(vp.fecha_venta) = ?
                AND vp.estado = 'ACTIVA'
                GROUP BY vp.metodo_pago";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha]);
        $resultado = $stmt->fetchAll();
        
        return is_array($resultado) ? $resultado : [];
    } catch (PDOException $e) {
        Logger::error('Error en getCierreCaja: ' . $e->getMessage());
        return [];
    }
}

public function getDetalleCaja($usuario_id, $fecha) {
    try {
        $sql = "SELECT 
                    vp.*,
                    c.nombre as cliente_nombre, 
                    c.apellido as cliente_apellido,
                    c.numero_documento,
                    r.origen, r.destino,
                    a.numero_asiento
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN asientos a ON vp.asiento_id = a.id
                WHERE vp.usuario_id = ?
                AND DATE(vp.fecha_venta) = ?
                AND vp.estado = 'ACTIVA'
                ORDER BY vp.fecha_venta ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha]);
        $resultado = $stmt->fetchAll();
        
        return is_array($resultado) ? $resultado : [];
    } catch (PDOException $e) {
        Logger::error('Error en getDetalleCaja: ' . $e->getMessage());
        return [];
    }
}
}