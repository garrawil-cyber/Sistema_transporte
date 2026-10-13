<?php
class Dashboard extends Model {

    public function getKPIs() {
        $hoy = date('Y-m-d');
        $mes = date('Y-m');
        $ayer = date('Y-m-d', strtotime('-1 day'));
        $mes_anterior = date('Y-m', strtotime('-1 month'));

        $stmt = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(precio_pagado),0) as monto 
                                    FROM ventas_pasajes WHERE DATE(fecha_venta) = ? AND estado = 'ACTIVA'");
        $stmt->execute([$hoy]);
        $ventas_hoy = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(precio_pagado),0) as monto 
                                    FROM ventas_pasajes WHERE DATE(fecha_venta) = ? AND estado = 'ACTIVA'");
        $stmt->execute([$ayer]);
        $ventas_ayer = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(precio_pagado),0) as monto 
                                    FROM ventas_pasajes WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = ? AND estado = 'ACTIVA'");
        $stmt->execute([$mes]);
        $ventas_mes = $stmt->fetch();

        $stmt = $this->db->prepare("SELECT COUNT(*) as total, COALESCE(SUM(precio_pagado),0) as monto 
                                    FROM ventas_pasajes WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = ? AND estado = 'ACTIVA'");
        $stmt->execute([$mes_anterior]);
        $ventas_mes_anterior = $stmt->fetch();

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM encomiendas 
                                  WHERE estado IN ('REGISTRADO', 'EN_BODEGA', 'EN_TRANSITO')");
        $encomiendas = $stmt->fetch();

        $stmt = $this->db->query("SELECT COUNT(*) as total, COALESCE(SUM(monto),0) as monto 
                                  FROM giros WHERE estado = 'REGISTRADO'");
        $giros = $stmt->fetch();

        // ✅ FIX: se quitó "AND fecha_salida > NOW()". El sistema no tiene
        //    transición automática de estado por tiempo (nada pasa
        //    PROGRAMADO -> EN_CURSO al llegar la hora de salida), así que
        //    ese filtro excluía viajes que la pantalla de Viajes sigue
        //    mostrando como PROGRAMADO apenas se pasa la hora de salida.
        //    El KPI debe contar por estado únicamente, para que coincida
        //    siempre con lo que el usuario ve en la lista de Viajes.
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM viajes 
                                  WHERE estado = 'PROGRAMADO'");
        $viajes = $stmt->fetch();

        // ============================================================
        // ✅ FIX CAJA ACTUAL:
        //    monto_final_sistema es NULL mientras la caja está ABIERTA.
        //    Se calcula el monto actual = monto_inicial + ingresos - egresos.
        // ============================================================
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.monto_inicial,
                c.fecha_apertura,
                COALESCE((
                    SELECT SUM(CASE WHEN cm.tipo = 'INGRESO' THEN cm.monto ELSE 0 END)
                    FROM caja_movimientos cm
                    WHERE cm.caja_id = c.id
                ), 0) AS total_ingresos,
                COALESCE((
                    SELECT SUM(CASE WHEN cm.tipo = 'EGRESO' THEN cm.monto ELSE 0 END)
                    FROM caja_movimientos cm
                    WHERE cm.caja_id = c.id
                ), 0) AS total_egresos
            FROM cajas c
            WHERE c.usuario_id = ? AND c.estado = 'ABIERTA'
            ORDER BY c.id DESC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        $caja = $stmt->fetch();

        $monto_actual_caja = 0;
        if ($caja) {
            $monto_actual_caja = (float)$caja['monto_inicial']
                               + (float)$caja['total_ingresos']
                               - (float)$caja['total_egresos'];
        }

        $tendencia_dia = $this->calcularTendencia($ventas_hoy['monto'], $ventas_ayer['monto']);
        $tendencia_mes = $this->calcularTendencia($ventas_mes['monto'], $ventas_mes_anterior['monto']);

        return [
            'ventas_hoy' => [
                'total' => $ventas_hoy['total'],
                'monto' => $ventas_hoy['monto'],
                'tendencia' => $tendencia_dia,
                'vs_ayer' => $ventas_ayer['monto']
            ],
            'ventas_mes' => [
                'total' => $ventas_mes['total'],
                'monto' => $ventas_mes['monto'],
                'tendencia' => $tendencia_mes,
                'vs_mes_anterior' => $ventas_mes_anterior['monto']
            ],
            'encomiendas_pendientes' => $encomiendas['total'],
            'giros_pendientes' => [
                'total' => $giros['total'],
                'monto' => $giros['monto']
            ],
            'viajes_programados' => $viajes['total'],
            'caja_actual' => $monto_actual_caja,
            'caja_info' => $caja ?: null,
            'clientes_registrados' => $this->contarClientes(),
            'tickets_promedio' => $ventas_mes['total'] > 0 ? $ventas_mes['monto'] / $ventas_mes['total'] : 0
        ];
    }

    private function calcularTendencia($actual, $anterior) {
        if ($anterior == 0) return $actual > 0 ? 100 : 0;
        return (($actual - $anterior) / $anterior) * 100;
    }

    private function contarClientes() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes");
        return $stmt->fetch()['total'];
    }

    public function getVentasUltimos7Dias() {
        $stmt = $this->db->query("
            SELECT DATE(fecha_venta) as fecha, 
                   COUNT(*) as total, 
                   COALESCE(SUM(precio_pagado),0) as monto 
            FROM ventas_pasajes 
            WHERE fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
              AND estado = 'ACTIVA'
            GROUP BY DATE(fecha_venta) 
            ORDER BY fecha ASC
        ");
        $datos = $stmt->fetchAll();
        
        $resultado = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i day"));
            $encontrado = null;
            foreach ($datos as $d) {
                if ($d['fecha'] == $fecha) {
                    $encontrado = $d;
                    break;
                }
            }
            $resultado[] = [
                'fecha' => $fecha,
                'dia' => date('D', strtotime($fecha)),
                'total' => $encontrado['total'] ?? 0,
                'monto' => $encontrado['monto'] ?? 0
            ];
        }
        return $resultado;
    }

    public function getTopRutas($limite = 5) {
        $limite = max(1, min(100, (int)$limite));
        
        $sql = "SELECT CONCAT(r.origen, ' → ', r.destino) as ruta, 
                       COUNT(vp.id) as total, 
                       COALESCE(SUM(vp.precio_pagado),0) as monto 
                FROM ventas_pasajes vp
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                WHERE vp.estado = 'ACTIVA'
                  AND vp.fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY r.id
                ORDER BY total DESC
                LIMIT $limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getMetodosPago() {
        $stmt = $this->db->query("
            SELECT metodo_pago, 
                   COUNT(*) as total, 
                   COALESCE(SUM(precio_pagado),0) as monto 
            FROM ventas_pasajes 
            WHERE estado = 'ACTIVA'
              AND fecha_venta >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY metodo_pago
        ");
        return $stmt->fetchAll();
    }

    /**
     * ✅ FIX OCUPABILIDAD:
     *    Ahora incluye viajes PROGRAMADOS, EN_CURSO y COMPLETADOS.
     *    Excluye CANCELADOS (no tiene sentido contarlos).
     */
    public function getOcupabilidadGlobal() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT a.id) as total_asientos,
                COUNT(DISTINCT CASE WHEN a.disponible = 0 AND a.es_vendible = 1 THEN a.id END) as ocupados
            FROM viajes v
            JOIN asientos a ON v.id = a.viaje_id
            WHERE v.fecha_salida >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
              AND v.estado IN ('PROGRAMADO', 'EN_CURSO', 'COMPLETADO')
              AND a.es_vendible = 1
        ");
        $data = $stmt->fetch();
        $porcentaje = $data['total_asientos'] > 0 
                    ? ($data['ocupados'] / $data['total_asientos']) * 100 
                    : 0;
        return [
            'total' => $data['total_asientos'],
            'ocupados' => $data['ocupados'],
            'porcentaje' => round($porcentaje, 1)
        ];
    }

    public function getAlertas() {
        $alertas = [];

        $stmt = $this->db->query("
            SELECT v.id, r.origen, r.destino, v.fecha_salida, b.placa,
                   (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros,
                   b.capacidad_total
            FROM viajes v
            JOIN rutas r ON v.ruta_id = r.id
            JOIN buses b ON v.bus_id = b.id
            WHERE v.estado = 'PROGRAMADO' 
              AND v.fecha_salida BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
            ORDER BY v.fecha_salida ASC
        ");
        $viajes_proximos = $stmt->fetchAll();
        
        foreach ($viajes_proximos as $v) {
            $porcentaje = $v['capacidad_total'] > 0 
                        ? ($v['pasajeros'] / $v['capacidad_total']) * 100 
                        : 0;
            
            if ($porcentaje < 30) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icono' => 'fa-exclamation-triangle',
                    'titulo' => 'Viaje con baja ocupación',
                    'mensaje' => $v['origen'] . ' → ' . $v['destino'] . ' (' . round($porcentaje) . '% ocupado)',
                    'hora' => date('H:i', strtotime($v['fecha_salida'])),
                    'url' => BASE_URL . 'viajes/show/' . $v['id']
                ];
            }
        }

        $stmt = $this->db->query("
            SELECT COUNT(*) as total, COALESCE(SUM(monto),0) as monto 
            FROM giros WHERE estado = 'REGISTRADO'
        ");
        $giros = $stmt->fetch();
        if ($giros['total'] > 0) {
            $alertas[] = [
                'tipo' => 'info',
                'icono' => 'fa-money-bill-transfer',
                'titulo' => 'Giros pendientes de pago',
                'mensaje' => $giros['total'] . ' giro(s) por un total de S/ ' . number_format($giros['monto'], 2),
                'url' => BASE_URL . 'giros'
            ];
        }

        $stmt = $this->db->query("
            SELECT COUNT(*) as total FROM encomiendas 
            WHERE estado IN ('REGISTRADO', 'EN_BODEGA') 
              AND fecha_registro < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ");
        $encomiendas_antiguas = $stmt->fetch();
        if ($encomiendas_antiguas['total'] > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'icono' => 'fa-box',
                'titulo' => 'Encomiendas antiguas',
                'mensaje' => $encomiendas_antiguas['total'] . ' encomienda(s) con más de 3 días sin entregar',
                'url' => BASE_URL . 'encomiendas'
            ];
        }

        $stmt = $this->db->query("SELECT COUNT(*) as total FROM buses WHERE estado = 'MANTENIMIENTO'");
        $buses_mant = $stmt->fetch();
        if ($buses_mant['total'] > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'icono' => 'fa-tools',
                'titulo' => 'Buses en mantenimiento',
                'mensaje' => $buses_mant['total'] . ' bus(es) en mantenimiento',
                'url' => BASE_URL . 'buses'
            ];
        }

        return $alertas;
    }

    public function getUltimasVentas($limite = 5) {
        $limite = max(1, min(100, (int)$limite));
        
        $sql = "SELECT vp.id, vp.numero_comprobante, vp.precio_pagado, vp.fecha_venta, vp.metodo_pago,
                       c.nombre as cliente_nombre, c.apellido as cliente_apellido,
                       r.origen, r.destino,
                       a.numero_asiento
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN asientos a ON vp.asiento_id = a.id
                WHERE vp.estado = 'ACTIVA'
                ORDER BY vp.fecha_venta DESC
                LIMIT $limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUltimasEncomiendas($limite = 5) {
        $limite = max(1, min(100, (int)$limite));
        
        $sql = "SELECT e.id, e.guia, e.costo_envio, e.estado, e.fecha_registro,
                       c.nombre as destinatario_nombre, c.apellido as destinatario_apellido,
                       r.origen, r.destino
                FROM encomiendas e
                JOIN clientes c ON e.destinatario_id = c.id
                JOIN viajes v ON e.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                ORDER BY e.fecha_registro DESC
                LIMIT $limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUltimosGiros($limite = 5) {
        $limite = max(1, min(100, (int)$limite));
        
        $sql = "SELECT g.id, g.codigo, g.monto, g.comision, g.estado, g.fecha_registro, g.moneda,
                       c.nombre as destinatario_nombre, c.apellido as destinatario_apellido
                FROM giros g
                JOIN clientes c ON g.destinatario_id = c.id
                ORDER BY g.fecha_registro DESC
                LIMIT $limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRendimientoVendedores() {
        $stmt = $this->db->query("
            SELECT u.id, CONCAT(u.nombre, ' ', u.apellido) as vendedor,
                   COUNT(vp.id) as total_ventas,
                   COALESCE(SUM(vp.precio_pagado),0) as monto
            FROM usuarios u
            LEFT JOIN ventas_pasajes vp ON u.id = vp.usuario_id 
                AND vp.estado = 'ACTIVA'
                AND DATE_FORMAT(vp.fecha_venta, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
            WHERE u.activo = 1 AND u.rol_id IN (1, 2)
            GROUP BY u.id
            ORDER BY monto DESC
            LIMIT 5
        ");
        return $stmt->fetchAll();
    }
}