<?php
class Caja extends Model {
    protected $table = 'cajas';

    public function getCajaAbierta($usuario_id) {
        $sql = "SELECT c.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.usuario_id = ? AND c.estado = 'ABIERTA'
                ORDER BY c.id DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch();
    }

    public function tieneCajaAbierta($usuario_id) {
        $caja = $this->getCajaAbierta($usuario_id);
        return $caja ? true : false;
    }

    public function abrir($usuario_id, $monto_inicial) {
        if ($this->tieneCajaAbierta($usuario_id)) {
            return false;
        }

        $sql = "INSERT INTO cajas (usuario_id, monto_inicial, estado) 
                VALUES (?, ?, 'ABIERTA')";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([$usuario_id, $monto_inicial])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Obtener resumen de la caja con rango de fechas correcto
     * y sin doble conteo de ventas.
     */
    public function getResumenCaja($caja_id) {
        $caja = $this->find($caja_id);
        if (!$caja) return null;

        $fecha_apertura = $caja['fecha_apertura'];
        $fecha_cierre   = $caja['fecha_cierre'];
        $usuario_id     = $caja['usuario_id'];

        $fecha_fin = $fecha_cierre ?: date('Y-m-d H:i:s');

        // 1. Ventas de pasajes por método de pago
        $sql = "SELECT 
                    metodo_pago,
                    COUNT(*) as cantidad,
                    COALESCE(SUM(precio_pagado), 0) as total
                FROM ventas_pasajes
                WHERE usuario_id = ?
                AND estado = 'ACTIVA'
                AND fecha_venta >= ?
                AND fecha_venta <= ?
                GROUP BY metodo_pago";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha_apertura, $fecha_fin]);
        $ventas = $stmt->fetchAll();

        $totales = [
            'EFECTIVO' => 0,
            'TARJETA' => 0,
            'TRANSFERENCIA' => 0,
            'OTRO' => 0
        ];
        $total_ventas_count = 0;
        $total_ventas_monto = 0;

        foreach ($ventas as $v) {
            $totales[$v['metodo_pago']] = $v['total'];
            $total_ventas_count += $v['cantidad'];
            $total_ventas_monto += $v['total'];
        }

        // 2. Encomiendas
        $sql = "SELECT 
                    COUNT(*) as cantidad,
                    COALESCE(SUM(costo_envio), 0) as total
                FROM encomiendas
                WHERE usuario_id = ?
                AND fecha_registro >= ?
                AND fecha_registro <= ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha_apertura, $fecha_fin]);
        $encomiendas = $stmt->fetch();

        // 3. Giros
        $sql = "SELECT 
                    COUNT(*) as cantidad,
                    COALESCE(SUM(comision), 0) as total
                FROM giros
                WHERE usuario_id = ?
                AND fecha_registro >= ?
                AND fecha_registro <= ?
                AND estado != 'ANULADO'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha_apertura, $fecha_fin]);
        $giros = $stmt->fetch();

        // 4. Movimientos manuales (excluir ventas B001-)
        $sql = "SELECT 
                    tipo,
                    COALESCE(SUM(monto), 0) as total
                FROM caja_movimientos
                WHERE caja_id = ?
                AND (referencia IS NULL OR referencia NOT LIKE 'B001-%')
                GROUP BY tipo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$caja_id]);
        $movimientos = $stmt->fetchAll();

        $total_ingresos = 0;
        $total_egresos = 0;
        foreach ($movimientos as $m) {
            if ($m['tipo'] == 'INGRESO') $total_ingresos = $m['total'];
            if ($m['tipo'] == 'EGRESO')  $total_egresos  = $m['total'];
        }

        // Monto final en sistema
        $monto_final_sistema = 
            $caja['monto_inicial'] 
            + $totales['EFECTIVO'] 
            + $encomiendas['total']
            + $giros['total']
            + $total_ingresos 
            - $total_egresos;

        return [
            'caja' => $caja,
            'ventas' => $totales,
            'total_ventas_count' => $total_ventas_count,
            'total_ventas_monto' => $total_ventas_monto,
            'encomiendas' => $encomiendas,
            'giros' => $giros,
            'total_ingresos' => $total_ingresos,
            'total_egresos' => $total_egresos,
            'monto_final_sistema' => $monto_final_sistema,
            'fecha_fin' => $fecha_fin
        ];
    }

    public function cerrar($caja_id, $monto_final_real, $observaciones = '') {
        $resumen = $this->getResumenCaja($caja_id);
        if (!$resumen) return false;

        $caja = $resumen['caja'];
        $monto_final_sistema = $resumen['monto_final_sistema'];
        $diferencia = $monto_final_real - $monto_final_sistema;

        try {
            $this->db->beginTransaction();

            $sql = "UPDATE cajas SET 
                        fecha_cierre = NOW(),
                        monto_final_sistema = ?,
                        monto_final_real = ?,
                        diferencia = ?,
                        total_ventas_efectivo = ?,
                        total_ventas_tarjeta = ?,
                        total_ventas_transferencia = ?,
                        total_encomiendas = ?,
                        total_giros = ?,
                        total_ingresos = ?,
                        total_egresos = ?,
                        total_ventas = ?,
                        estado = 'CERRADA',
                        observaciones = ?
                    WHERE id = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $monto_final_sistema,
                $monto_final_real,
                $diferencia,
                $resumen['ventas']['EFECTIVO'],
                $resumen['ventas']['TARJETA'],
                $resumen['ventas']['TRANSFERENCIA'],
                $resumen['encomiendas']['total'],
                $resumen['giros']['total'],
                $resumen['total_ingresos'],
                $resumen['total_egresos'],
                $resumen['total_ventas_count'],
                $observaciones,
                $caja_id
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function registrarMovimiento($caja_id, $usuario_id, $tipo, $concepto, $monto, $metodo_pago = 'EFECTIVO', $referencia = null) {
        $sql = "INSERT INTO caja_movimientos 
                (caja_id, usuario_id, tipo, concepto, monto, metodo_pago, referencia) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$caja_id, $usuario_id, $tipo, $concepto, $monto, $metodo_pago, $referencia]);
    }

    /**
     * ✅ Movimientos TODOS (incluye ventas automáticas)
     * Útil para auditoría completa.
     */
    public function getMovimientos($caja_id) {
        $sql = "SELECT m.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM caja_movimientos m
                JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.caja_id = ?
                ORDER BY m.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$caja_id]);
        return $stmt->fetchAll();
    }

    /**
     * ✅ Movimientos MANUALES (excluye ventas automáticas B001-)
     * Útil para el panel de caja del día.
     */
    public function getMovimientosManuales($caja_id) {
        $sql = "SELECT m.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM caja_movimientos m
                JOIN usuarios u ON m.usuario_id = u.id
                WHERE m.caja_id = ?
                AND (m.referencia IS NULL OR m.referencia NOT LIKE 'B001-%')
                ORDER BY m.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$caja_id]);
        return $stmt->fetchAll();
    }

    public function eliminarMovimiento($id) {
        $sql = "DELETE FROM caja_movimientos WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getHistorial($fecha_inicio = null, $fecha_fin = null, $usuario_id = null) {
        $sql = "SELECT c.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.estado = 'CERRADA'";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(c.fecha_apertura) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(c.fecha_apertura) <= ?";
            $params[] = $fecha_fin;
        }
        if (!empty($usuario_id)) {
            $sql .= " AND c.usuario_id = ?";
            $params[] = $usuario_id;
        }
        
        $sql .= " ORDER BY c.fecha_cierre DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener detalles de una caja cerrada (con movimientos y ventas separadas)
     */
    public function getCajaConDetalles($id) {
        $sql = "SELECT c.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $caja = $stmt->fetch();

        if ($caja) {
            // ✅ Movimientos manuales (sin ventas automáticas)
            $caja['movimientos'] = $this->getMovimientosManuales($id);
            $caja['movimientos_todos'] = $this->getMovimientos($id);
            
            // Ventas del período
            $fecha_fin = $caja['fecha_cierre'] ?: date('Y-m-d H:i:s');
            
            $sql = "SELECT vp.*, 
                           c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                           r.origen, r.destino, a.numero_asiento
                    FROM ventas_pasajes vp
                    JOIN clientes c ON vp.cliente_id = c.id
                    JOIN viajes v ON vp.viaje_id = v.id
                    JOIN rutas r ON v.ruta_id = r.id
                    JOIN asientos a ON vp.asiento_id = a.id
                    WHERE vp.usuario_id = ?
                    AND vp.estado = 'ACTIVA'
                    AND vp.fecha_venta >= ?
                    AND vp.fecha_venta <= ?
                    ORDER BY vp.fecha_venta ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$caja['usuario_id'], $caja['fecha_apertura'], $fecha_fin]);
            $caja['ventas_detalle'] = $stmt->fetchAll();
        }

        return $caja;
    }

    public function getHistorialPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT c.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.estado = 'CERRADA'";
        
        $params = [];
        
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND c.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(c.fecha_apertura) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(c.fecha_apertura) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $sql .= " ORDER BY c.fecha_cierre DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    /**
     * ✅ Totales globales para cards del historial (no solo página actual)
     */
    public function getTotalesHistorial($filtros = []) {
        $sql = "SELECT 
                    COUNT(*) as total_cierres,
                    SUM(CASE WHEN ABS(c.diferencia) < 0.01 THEN 1 ELSE 0 END) as cuadres_ok,
                    SUM(CASE WHEN ABS(c.diferencia) >= 0.01 THEN 1 ELSE 0 END) as con_diferencia,
                    COALESCE(SUM(c.diferencia), 0) as diferencia_total
                FROM cajas c
                WHERE c.estado = 'CERRADA'";
        
        $params = [];
        
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND c.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(c.fecha_apertura) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(c.fecha_apertura) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

     //✅ NUEVO: Obtener las últimas ventas del día para el panel
     
    public function getVentasDelDia($caja_id, $limite = 10) {
        $caja = $this->find($caja_id);
        if (!$caja) return [];

        $fecha_apertura = $caja['fecha_apertura'];
        $fecha_fin = $caja['fecha_cierre'] ?: date('Y-m-d H:i:s');
        $usuario_id = $caja['usuario_id'];

        $sql = "SELECT vp.*, 
                       c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                       r.origen, r.destino,
                       a.numero_asiento
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN asientos a ON vp.asiento_id = a.id
                WHERE vp.usuario_id = ?
                AND vp.estado = 'ACTIVA'
                AND vp.fecha_venta >= ?
                AND vp.fecha_venta <= ?
                ORDER BY vp.fecha_venta DESC
                LIMIT " . (int)$limite;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $fecha_apertura, $fecha_fin]);
        return $stmt->fetchAll();
    }

    /**
     * ✅ NUEVO: Obtener la última caja cerrada del usuario (referencia)
     */
    public function getUltimaCajaCerrada($usuario_id) {
        $sql = "SELECT c.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.usuario_id = ? AND c.estado = 'CERRADA'
                ORDER BY c.fecha_cierre DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch();
    }

}
