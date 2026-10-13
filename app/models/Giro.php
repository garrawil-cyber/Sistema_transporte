<?php
class Giro extends Model {
    protected $table = 'giros';

    public function getAllWithDetails() {
        $sql = "SELECT g.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, r.numero_documento as remitente_dni,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, d.numero_documento as destinatario_dni,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM giros g
                JOIN clientes r ON g.remitente_id = r.id
                JOIN clientes d ON g.destinatario_id = d.id
                JOIN usuarios u ON g.usuario_id = u.id
                ORDER BY g.fecha_registro DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findByCodigo($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM giros WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetch();
    }

    /**
     * Registrar un nuevo giro (devuelve el ID)
     */
    public function registrar($data) {
        try {
            $sql = "INSERT INTO giros 
                    (codigo, remitente_id, destinatario_id, lugar_origen, lugar_destino, concepto,
                     usuario_id, monto, comision, moneda, metodo_pago_origen, metodo_pago_destino, estado) 
                    VALUES (:codigo, :remitente_id, :destinatario_id, :lugar_origen, :lugar_destino, :concepto,
                            :usuario_id, :monto, :comision, :moneda, :metodo_pago_origen, :metodo_pago_destino, 'REGISTRADO')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function pagar($id) {
        $sql = "UPDATE giros SET estado = 'PAGADO', fecha_pago = NOW() WHERE id = :id AND estado = 'REGISTRADO'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function anular($id) {
        $sql = "UPDATE giros SET estado = 'ANULADO' WHERE id = :id AND estado = 'REGISTRADO'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function generarCodigo() {
        $fecha = date('ymd');
        $stmt = $this->db->query("SELECT MAX(id) as ultimo FROM giros");
        $row = $stmt->fetch();
        $numero = ($row['ultimo'] ?? 0) + 1;
        return 'GIR-' . $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function getComisionPorcentaje() {
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = 'comision_giro_porcentaje'");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ? (float)$row['valor'] : 3.5;
    }

    // ============================================================
    // PAGINACIÓN CON FILTROS COMPLETOS
    // ============================================================
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT g.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido,
                       r.numero_documento as remitente_dni,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido,
                       d.numero_documento as destinatario_dni,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM giros g
                JOIN clientes r ON g.remitente_id = r.id
                JOIN clientes d ON g.destinatario_id = d.id
                JOIN usuarios u ON g.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['estado'])) {
            $sql .= " AND g.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (g.codigo LIKE ? OR r.nombre LIKE ? OR r.apellido LIKE ? 
                          OR d.nombre LIKE ? OR d.apellido LIKE ? 
                          OR r.numero_documento LIKE ? OR d.numero_documento LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar, $buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(g.fecha_registro) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(g.fecha_registro) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        
        $sql .= " ORDER BY g.fecha_registro DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    // ============================================================
    // ESTADÍSTICAS PARA KPIs
    // ============================================================

    /**
     * KPIs globales del módulo (respeta filtros aplicados)
     */
    public function getStatsFiltrados($filtros = []) {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN g.estado = 'REGISTRADO' THEN 1 ELSE 0 END) AS registrados,
                        SUM(CASE WHEN g.estado = 'PAGADO'     THEN 1 ELSE 0 END) AS pagados,
                        SUM(CASE WHEN g.estado = 'ANULADO'    THEN 1 ELSE 0 END) AS anulados,
                        COALESCE(SUM(g.monto), 0)    AS monto_total,
                        COALESCE(SUM(g.comision), 0) AS comision_total,
                        COALESCE(SUM(CASE WHEN g.estado = 'PAGADO' THEN g.monto ELSE 0 END), 0) AS monto_pagado,
                        COALESCE(SUM(CASE WHEN g.estado = 'PAGADO' THEN g.comision ELSE 0 END), 0) AS comision_cobrada
                    FROM giros g
                    JOIN clientes r ON g.remitente_id = r.id
                    JOIN clientes d ON g.destinatario_id = d.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['estado'])) {
                $sql .= " AND g.estado = ?";
                $params[] = $filtros['estado'];
            }
            if (!empty($filtros['buscar'])) {
                $sql .= " AND (g.codigo LIKE ? OR r.nombre LIKE ? OR r.apellido LIKE ? 
                              OR d.nombre LIKE ? OR d.apellido LIKE ? 
                              OR r.numero_documento LIKE ? OR d.numero_documento LIKE ?)";
                $buscar = "%{$filtros['buscar']}%";
                $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar, $buscar, $buscar, $buscar]);
            }
            if (!empty($filtros['fecha_inicio'])) {
                $sql .= " AND DATE(g.fecha_registro) >= ?";
                $params[] = $filtros['fecha_inicio'];
            }
            if (!empty($filtros['fecha_fin'])) {
                $sql .= " AND DATE(g.fecha_registro) <= ?";
                $params[] = $filtros['fecha_fin'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();

            return [
                'total'             => (int)($row['total'] ?? 0),
                'registrados'       => (int)($row['registrados'] ?? 0),
                'pagados'           => (int)($row['pagados'] ?? 0),
                'anulados'          => (int)($row['anulados'] ?? 0),
                'monto_total'       => (float)($row['monto_total'] ?? 0),
                'comision_total'    => (float)($row['comision_total'] ?? 0),
                'monto_pagado'      => (float)($row['monto_pagado'] ?? 0),
                'comision_cobrada'  => (float)($row['comision_cobrada'] ?? 0),
            ];
        } catch (Exception $e) {
            Logger::error('Error en Giro::getStatsFiltrados: ' . $e->getMessage());
            return [
                'total' => 0, 'registrados' => 0, 'pagados' => 0, 'anulados' => 0,
                'monto_total' => 0, 'comision_total' => 0,
                'monto_pagado' => 0, 'comision_cobrada' => 0
            ];
        }
    }

    /**
     * KPIs del día actual
     */
    public function getStatsHoy() {
        try {
            $sql = "SELECT 
                        COUNT(*) AS total_hoy,
                        SUM(CASE WHEN estado = 'REGISTRADO' THEN 1 ELSE 0 END) AS registrados,
                        SUM(CASE WHEN estado = 'PAGADO'     THEN 1 ELSE 0 END) AS pagados,
                        SUM(CASE WHEN estado = 'ANULADO'    THEN 1 ELSE 0 END) AS anulados,
                        COALESCE(SUM(monto), 0)    AS monto_hoy,
                        COALESCE(SUM(comision), 0) AS comision_hoy
                    FROM giros
                    WHERE DATE(fecha_registro) = CURDATE()";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();

            return [
                'total_hoy'     => (int)($row['total_hoy'] ?? 0),
                'registrados'   => (int)($row['registrados'] ?? 0),
                'pagados'       => (int)($row['pagados'] ?? 0),
                'anulados'      => (int)($row['anulados'] ?? 0),
                'monto_hoy'     => (float)($row['monto_hoy'] ?? 0),
                'comision_hoy'  => (float)($row['comision_hoy'] ?? 0),
            ];
        } catch (Exception $e) {
            Logger::error('Error en Giro::getStatsHoy: ' . $e->getMessage());
            return [
                'total_hoy' => 0, 'registrados' => 0, 'pagados' => 0,
                'anulados' => 0, 'monto_hoy' => 0, 'comision_hoy' => 0
            ];
        }
    }

    /**
     * Datos para el gráfico de los últimos 7 días
     */
    public function getStatsUltimos7Dias() {
        try {
            $sql = "SELECT 
                        DATE(fecha_registro) AS fecha,
                        COUNT(*) AS total,
                        COALESCE(SUM(monto), 0) AS monto,
                        COALESCE(SUM(comision), 0) AS comision
                    FROM giros
                    WHERE DATE(fecha_registro) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()
                    GROUP BY DATE(fecha_registro)
                    ORDER BY fecha ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll();
            
            // Inicializar los 7 días
            $dias = [];
            for ($i = 6; $i >= 0; $i--) {
                $fecha = date('Y-m-d', strtotime("-$i days"));
                $dias[$fecha] = [
                    'fecha'    => $fecha,
                    'total'    => 0,
                    'monto'    => 0,
                    'comision' => 0,
                ];
            }
            
            // Rellenar con datos reales
            foreach ($resultados as $r) {
                if (isset($dias[$r['fecha']])) {
                    $dias[$r['fecha']]['total']    = (int)$r['total'];
                    $dias[$r['fecha']]['monto']    = (float)$r['monto'];
                    $dias[$r['fecha']]['comision'] = (float)$r['comision'];
                }
            }
            
            return array_values($dias);
        } catch (Exception $e) {
            Logger::error('Error en Giro::getStatsUltimos7Dias: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Detalle con emails (para autocompletar modal de email)
     */
    public function getByIdWithDetails($id) {
        $sql = "SELECT g.*, 
                       r.nombre as remitente_nombre, r.apellido as remitente_apellido, 
                       r.numero_documento as remitente_dni, r.telefono as remitente_telefono,
                       r.email as remitente_email,
                       d.nombre as destinatario_nombre, d.apellido as destinatario_apellido, 
                       d.numero_documento as destinatario_dni, d.telefono as destinatario_telefono,
                       d.email as destinatario_email,
                       u.nombre as usuario_nombre, u.apellido as usuario_apellido
                FROM giros g
                JOIN clientes r ON g.remitente_id = r.id
                JOIN clientes d ON g.destinatario_id = d.id
                JOIN usuarios u ON g.usuario_id = u.id
                WHERE g.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

}
