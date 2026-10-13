<?php
class Bus extends Model {
    protected $table = 'buses';

    // Enum válidos (evita strings hardcodeados)
    const TIPOS_SERVICIO = ['ECONOMICO', 'SEMICAMA', 'CAMA'];
    const ESTADOS        = ['ACTIVO', 'MANTENIMIENTO', 'BAJA'];
    const PISOS_VALIDOS  = ['1', '2'];
    const POR_FILA_VALIDOS = [2, 3, 4];

    // ============================================================
    // CONSULTAS DE LISTADO
    // ============================================================

    public function getAllWithDetails() {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'COMPLETADO') as viajes_completados,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'PROGRAMADO') as viajes_programados
                FROM buses b
                ORDER BY
                    FIELD(b.estado, 'ACTIVO', 'MANTENIMIENTO', 'BAJA') ASC,
                    b.placa ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIdWithDetails($id) {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'COMPLETADO') as viajes_completados,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'PROGRAMADO') as viajes_programados
                FROM buses b
                WHERE b.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Buses paginados con filtros y contadores por estado
     */
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'COMPLETADO') as viajes_completados,
                       (SELECT COUNT(*) FROM viajes v
                        WHERE v.bus_id = b.id AND v.estado = 'PROGRAMADO') as viajes_programados
                FROM buses b
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (b.placa LIKE ? OR b.marca LIKE ? OR b.modelo LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['estado'])) {
            $sql .= " AND b.estado = ?";
            $params[] = $filtros['estado'];
        }
        if (!empty($filtros['tipo_servicio'])) {
            $sql .= " AND b.tipo_servicio = ?";
            $params[] = $filtros['tipo_servicio'];
        }

        $sql .= " ORDER BY
                    FIELD(b.estado, 'ACTIVO', 'MANTENIMIENTO', 'BAJA') ASC,
                    b.placa ASC";

        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    // ============================================================
    // VALIDACIÓN
    // ============================================================

    /**
     * Validación completa de datos del bus
     * @param array $data
     * @param bool  $es_creacion (true = INSERT, false = UPDATE)
     * @return array Lista de errores (vacía si todo OK)
     */
    public function validate($data, $es_creacion = true) {
        $errors = [];

        // ---------- PLACA ----------
        $placa = strtoupper(trim($data['placa'] ?? ''));
        if (empty($placa)) {
            $errors[] = 'La placa es obligatoria';
        } elseif (!preg_match('/^[A-Z]{3}-\d{3,4}$/', $placa)) {
            $errors[] = 'Formato de placa inválido. Ejemplo: ABC-1234';
        } else {
            // Duplicado (excluir el propio ID en modo edición)
            $id = $es_creacion ? 0 : (int)($data['id'] ?? 0);
            $stmt = $this->db->prepare("SELECT id FROM buses WHERE placa = ? AND id != ?");
            $stmt->execute([$placa, $id]);
            if ($stmt->fetch()) {
                $errors[] = 'La placa ya está registrada';
            }
        }

        // ---------- MARCA / MODELO ----------
        if (empty(trim($data['marca'] ?? ''))) {
            $errors[] = 'La marca es obligatoria';
        }
        if (empty(trim($data['modelo'] ?? ''))) {
            $errors[] = 'El modelo es obligatorio';
        }

        // ---------- AÑO ----------
        if (!empty($data['anio'])) {
            $anio = (int)$data['anio'];
            $anio_actual = (int)date('Y');
            if ($anio < 1950 || $anio > $anio_actual + 1) {
                $errors[] = "El año debe estar entre 1950 y " . ($anio_actual + 1);
            }
        }

        // ---------- CAPACIDAD TOTAL ----------
        $capacidad = (int)($data['capacidad_total'] ?? 0);
        if ($capacidad <= 0) {
            $errors[] = 'La capacidad debe ser mayor a 0';
        } elseif ($capacidad > 100) {
            $errors[] = 'La capacidad no puede superar los 100 asientos';
        }

        // ---------- TIPO DE SERVICIO ----------
        $tipo_servicio = $data['tipo_servicio'] ?? '';
        if (!in_array($tipo_servicio, self::TIPOS_SERVICIO, true)) {
            $errors[] = 'El tipo de servicio no es válido';
        }

        // ---------- ESTADO ----------
        $estado = $data['estado'] ?? '';
        if (!in_array($estado, self::ESTADOS, true)) {
            $errors[] = 'El estado no es válido';
        }

        // ---------- LAYOUT FÍSICO ----------
        $pisos = (string)($data['pisos'] ?? '2');
        if (!in_array($pisos, self::PISOS_VALIDOS, true)) {
            $errors[] = 'El número de pisos debe ser 1 o 2';
        }

        $por_fila_p1 = (int)($data['asientos_por_fila_piso_1'] ?? 0);
        if (!in_array($por_fila_p1, self::POR_FILA_VALIDOS, true)) {
            $errors[] = 'El ancho de fila del piso 1 debe ser 2 (1+1), 3 (2+1) o 4 (2+2)';
        }

        $asientos_p1 = (int)($data['asientos_piso_1'] ?? 0);
        $asientos_p2 = (int)($data['asientos_piso_2'] ?? 0);

        if ($asientos_p1 <= 0) {
            $errors[] = 'El piso 1 debe tener al menos 1 asiento';
        }

        if ($pisos === '2') {
            $por_fila_p2 = (int)($data['asientos_por_fila_piso_2'] ?? 0);
            if (!in_array($por_fila_p2, self::POR_FILA_VALIDOS, true)) {
                $errors[] = 'El ancho de fila del piso 2 debe ser 2 (1+1), 3 (2+1) o 4 (2+2)';
            }
            if ($asientos_p2 <= 0) {
                $errors[] = 'El piso 2 debe tener al menos 1 asiento (o cambia el bus a 1 piso)';
            }
        } else {
            $asientos_p2 = 0; // Un bus de 1 piso no tiene asientos en piso 2
        }

        // La capacidad total DEBE coincidir con la suma real
        if ($capacidad > 0 && ($asientos_p1 + $asientos_p2) !== $capacidad) {
            $errors[] = "La capacidad total ({$capacidad}) no coincide con la suma de asientos ({$asientos_p1} + {$asientos_p2} = " . ($asientos_p1 + $asientos_p2) . ")";
        }

        return $errors;
    }

    // ============================================================
    // DISPONIBILIDAD
    // ============================================================

    /**
     * Buses ACTIVOS (uso general: combos, formularios)
     * ✅ Solo devuelve buses correctamente configurados
     */
    public function getActivos() {
        $sql = "SELECT * FROM buses
                WHERE estado = 'ACTIVO'
                  AND capacidad_total > 0
                ORDER BY placa ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Alias por retrocompatibilidad
     */
    public function getDisponibles() {
        return $this->getActivos();
    }

    /**
     * ✅ NUEVO: Buses disponibles para una fecha/hora específica
     * Excluye buses con viajes solapados (PROGRAMADO o EN_CURSO)
     */
    public function getDisponiblesParaFecha($fecha_salida, $fecha_llegada, $excluir_viaje_id = null) {
        $sql = "SELECT b.*
                FROM buses b
                WHERE b.estado = 'ACTIVO'
                  AND b.capacidad_total > 0
                  AND NOT EXISTS (
                      SELECT 1 FROM viajes v
                      WHERE v.bus_id = b.id
                        AND v.estado IN ('PROGRAMADO', 'EN_CURSO')
                        AND (
                            (v.fecha_salida BETWEEN ? AND ?)
                            OR (v.fecha_llegada BETWEEN ? AND ?)
                            OR (v.fecha_salida <= ? AND v.fecha_llegada >= ?)
                        )";

        $params = [
            $fecha_salida, $fecha_llegada,
            $fecha_salida, $fecha_llegada,
            $fecha_salida, $fecha_llegada
        ];

        if ($excluir_viaje_id) {
            $sql .= " AND v.id != ?";
            $params[] = $excluir_viaje_id;
        }

        $sql .= " ) ORDER BY b.placa ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ============================================================
    // CAMBIO DE ESTADO
    // ============================================================

    public function cambiarEstado($id, $estado) {
        if (!in_array($estado, self::ESTADOS, true)) {
            return false;
        }

        $sql = "UPDATE buses SET estado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $id]);
    }

    public function tieneViajesProgramados($id) {
        $sql = "SELECT COUNT(*) FROM viajes WHERE bus_id = ? AND estado = 'PROGRAMADO'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ============================================================
    // ESTADÍSTICAS
    // ============================================================

    /**
     * ✅ FIX: COUNT DISTINCT para evitar inflado por JOIN con ventas
     */
/**
 * ✅ FIX: Devuelve TODOS los campos que la vista show.php espera
 *    Incluye: total_pasajeros y total_recaudado (faltaban)
 */
public function getEstadisticas($id) {
    $sql = "SELECT
                COUNT(DISTINCT v.id) as total_viajes,
                COUNT(DISTINCT CASE WHEN v.estado = 'COMPLETADO' THEN v.id END) as completados,
                COUNT(DISTINCT CASE WHEN v.estado = 'EN_CURSO'   THEN v.id END) as en_curso,
                COUNT(DISTINCT CASE WHEN v.estado = 'PROGRAMADO' THEN v.id END) as programados,
                COUNT(DISTINCT CASE WHEN v.estado = 'CANCELADO'  THEN v.id END) as cancelados,
                COUNT(DISTINCT vp.id) as total_pasajeros,
                COALESCE(SUM(vp.precio_pagado), 0) as total_recaudado,
                COALESCE(AVG(vp.precio_pagado), 0) as ingreso_promedio
            FROM viajes v
            LEFT JOIN ventas_pasajes vp
                ON v.id = vp.viaje_id
               AND vp.estado = 'ACTIVA'
            WHERE v.bus_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

    /**
     * ✅ NUEVO: Listado de viajes de un bus (para la vista show)
     */
    public function getViajes($bus_id, $limite = 20) {
        $limite = (int)$limite;
        $sql = "SELECT v.*,
                       r.origen, r.destino,
                       (SELECT COUNT(*) FROM ventas_pasajes vp
                        WHERE vp.viaje_id = v.id AND vp.estado = 'ACTIVA') as pasajeros,
                       (SELECT COALESCE(SUM(vp.precio_pagado), 0)
                        FROM ventas_pasajes vp
                        WHERE vp.viaje_id = v.id AND vp.estado = 'ACTIVA') as recaudado
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                WHERE v.bus_id = ?
                ORDER BY v.fecha_salida DESC
                LIMIT $limite";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$bus_id]);
        return $stmt->fetchAll();
    }
}