<?php
class Viaje extends Model {
    protected $table = 'viajes';

    // ============================================================
    // CONSULTAS
    // ============================================================

    public function buscarParaVenta($origen = '', $destino = '', $fecha = null, $tolerancia_minutos = 30) {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.distancia_km,
                       b.placa, b.tipo_servicio, b.capacidad_total, b.pisos,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo,
                       (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1 AND es_vendible = 1) as asientos_disponibles
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE v.estado = 'PROGRAMADO'
                AND v.fecha_salida >= NOW() - INTERVAL ? MINUTE";
        
        $params = [$tolerancia_minutos];
        
        if (!empty($origen)) {
            $sql .= " AND r.origen LIKE ?";
            $params[] = "%$origen%";
        }
        if (!empty($destino)) {
            $sql .= " AND r.destino LIKE ?";
            $params[] = "%$destino%";
        }
        if (!empty($fecha)) {
            $sql .= " AND DATE(v.fecha_salida) = ?";
            $params[] = $fecha;
        }
        
        $sql .= " AND (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1 AND es_vendible = 1) > 0";
        $sql .= " ORDER BY v.fecha_salida ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * ✅ Reconoce el estado RESERVADO, no solo 'disponible'. Las reservas
     * mantienen disponible=1 a propósito (ver reservarAsiento()), así que
     * un chequeo que solo mire 'disponible' nunca detecta un asiento
     * reservado por otro vendedor — era exactamente el mismo hueco que
     * ya se corrigió en Venta::registrar(). Si se pasa $usuario_id, un
     * asiento reservado por ESE usuario cuenta como disponible para él
     * (flujo normal: reserva mientras llena el formulario, luego vende).
     */
    public function asientoDisponible($viaje_id, $asiento_id, $usuario_id = null) {
        $sql = "SELECT disponible, estado, reservado_hasta, reservado_por_usuario_id
                FROM asientos WHERE id = ? AND viaje_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$asiento_id, $viaje_id]);
        $asiento = $stmt->fetch();

        if (!$asiento || !$asiento['disponible'] || $asiento['estado'] === 'OCUPADO') {
            return false;
        }

        if ($asiento['estado'] === 'RESERVADO') {
            $reservado_por_otro = (int)($asiento['reservado_por_usuario_id'] ?? 0) !== (int)$usuario_id;
            $reserva_vigente = !empty($asiento['reservado_hasta']) && strtotime($asiento['reservado_hasta']) > time();

            if ($reservado_por_otro && $reserva_vigente) {
                return false;
            }
        }

        return true;
    }

    public function getAllWithDetails() {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.distancia_km, r.duracion_estimada,
                       b.placa, b.marca, b.modelo, b.tipo_servicio, b.capacidad_total,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo,
                       (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1) as asientos_libres
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                ORDER BY v.fecha_salida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function buscar($origen = '', $destino = '', $fecha = null, $estado = '') {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.distancia_km,
                       b.placa, b.tipo_servicio,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($origen)) {
            $sql .= " AND r.origen LIKE ?";
            $params[] = "%$origen%";
        }
        if (!empty($destino)) {
            $sql .= " AND r.destino LIKE ?";
            $params[] = "%$destino%";
        }
        if (!empty($fecha)) {
            $sql .= " AND DATE(v.fecha_salida) = ?";
            $params[] = $fecha;
        }
        if (!empty($estado)) {
            $sql .= " AND v.estado = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY v.fecha_salida ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDisponibles() {
        $sql = "SELECT v.*, 
                       r.origen, r.destino,
                       b.placa, b.tipo_servicio,
                       (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1) as asientos_libres
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.estado = 'PROGRAMADO' 
                AND v.fecha_salida > NOW()
                AND (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1) > 0
                ORDER BY v.fecha_salida ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProximosParaVenta($tolerancia_minutos = 30) {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.distancia_km,
                       b.placa, b.tipo_servicio, b.capacidad_total, b.pisos,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo,
                       (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1 AND es_vendible = 1) as asientos_disponibles
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE v.estado = 'PROGRAMADO'
                AND v.fecha_salida >= NOW() - INTERVAL ? MINUTE
                AND (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1 AND es_vendible = 1) > 0
                ORDER BY v.fecha_salida ASC
                LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tolerancia_minutos]);
        return $stmt->fetchAll();
    }

    public function getByIdWithDetails($id) {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.distancia_km, r.duracion_estimada, r.precio_base,
                       b.placa, b.marca, b.modelo, b.tipo_servicio, b.capacidad_total,
                       u.id as conductor_id,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       u.telefono as conductor_telefono,
                       u.email as conductor_email,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo,
                       (SELECT COUNT(*) FROM asientos WHERE viaje_id = v.id AND disponible = 1) as asientos_libres
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE v.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * ✅ MEJORADO: uso de JOIN directo con roles en lugar de subquery.
     *    Más eficiente y robusto ante renombres de rol.
     */
    public function getConductores() {
        $sql = "SELECT u.id, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_completo, 
                       u.email, 
                       u.telefono 
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE r.nombre = 'CONDUCTOR'
                AND u.activo = 1
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getViajesByConductor($conductor_id) {
        $sql = "SELECT v.*, 
                       r.origen, r.destino,
                       b.placa, b.tipo_servicio,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros_abordo
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.usuario_creador = ?
                ORDER BY v.fecha_salida DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conductor_id]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // VIAJES VENCIDOS (para alertas)
    // ============================================================

    public function getViajesVencidos($tolerancia_minutos = 30) {
        $sql = "SELECT v.id, 
                       v.fecha_salida, 
                       v.fecha_llegada, 
                       v.estado,
                       r.origen, 
                       r.destino,
                       b.placa,
                       b.tipo_servicio,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre,
                       TIMESTAMPDIFF(MINUTE, v.fecha_salida, NOW()) as minutos_vencidos
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE v.estado = 'PROGRAMADO'
                AND v.fecha_salida < NOW() - INTERVAL ? MINUTE
                ORDER BY v.fecha_salida DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tolerancia_minutos]);
        return $stmt->fetchAll();
    }

    public function cambiarEstadoMultiple($ids, $estado) {
        $estados_validos = ['PROGRAMADO', 'EN_CURSO', 'COMPLETADO', 'CANCELADO'];
        if (!in_array($estado, $estados_validos, true) || empty($ids)) {
            return 0;
        }
        
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, function($id) { return $id > 0; });
        
        if (empty($ids)) {
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE viajes SET estado = ? WHERE id IN ($placeholders)";
        $params = array_merge([$estado], $ids);
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    // ============================================================
    // VALIDACIÓN
    // ============================================================

    public function validate($data, $es_creacion = true) {
        $errors = [];
        
        if (empty($data['ruta_id']) || $data['ruta_id'] <= 0) {
            $errors[] = 'Debe seleccionar una ruta';
        }
        
        if (empty($data['bus_id']) || $data['bus_id'] <= 0) {
            $errors[] = 'Debe seleccionar un bus';
        }
        
        if (empty($data['fecha_salida'])) {
            $errors[] = 'La fecha de salida es obligatoria';
        } elseif ($es_creacion) {
            // Solo al CREAR: la fecha no puede ser anterior a hoy
            $fecha_salida_dia = date('Y-m-d', strtotime($data['fecha_salida']));
            $hoy = date('Y-m-d');
            
            if ($fecha_salida_dia < $hoy) {
                $errors[] = 'La fecha de salida no puede ser anterior a hoy';
            }
        }
        
        if (empty($data['fecha_llegada'])) {
            $errors[] = 'La fecha de llegada es obligatoria';
        } else {
            if (strtotime($data['fecha_llegada']) <= strtotime($data['fecha_salida'])) {
                $errors[] = 'La fecha/hora de llegada debe ser posterior a la de salida.';
            }
        }
        
        if (empty($data['precio_final']) || $data['precio_final'] <= 0) {
            $errors[] = 'El precio final debe ser mayor a 0';
        }
        
        return $errors;
    }

    // ============================================================
    // VALIDACIONES DE DISPONIBILIDAD (con solapamiento COMPLETO)
    // ============================================================

    /**
     * ✅ MEJORADO: query de solapamiento completa (4 casos):
     *    1. A empieza antes y termina dentro de B
     *    2. A empieza antes y termina después de B (contiene a B)
     *    3. A empieza dentro y termina dentro de B
     *    4. A empieza dentro y termina después de B
     *    Simplificado: A.fecha_salida < B.fecha_llegada AND A.fecha_llegada > B.fecha_salida
     */
    public function busDisponible($bus_id, $fecha_salida, $fecha_llegada, $viaje_id = null) {
        $sql = "SELECT COUNT(*) FROM viajes 
                WHERE bus_id = ? 
                AND estado IN ('PROGRAMADO', 'EN_CURSO')
                AND fecha_salida < ? 
                AND fecha_llegada > ?";
        
        $params = [$bus_id, $fecha_llegada, $fecha_salida];
        
        if ($viaje_id) {
            $sql .= " AND id != ?";
            $params[] = $viaje_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function getConflictoBus($bus_id, $fecha_salida, $fecha_llegada, $viaje_id = null) {
        $sql = "SELECT v.id, v.fecha_salida, v.fecha_llegada, v.estado,
                       r.origen, r.destino,
                       b.placa,
                       CONCAT(u.nombre, ' ', u.apellido) as conductor_nombre
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                LEFT JOIN usuarios u ON v.usuario_creador = u.id
                WHERE v.bus_id = ? 
                AND v.estado IN ('PROGRAMADO', 'EN_CURSO')
                AND v.fecha_salida < ? 
                AND v.fecha_llegada > ?";
        
        $params = [$bus_id, $fecha_llegada, $fecha_salida];
        
        if ($viaje_id) {
            $sql .= " AND v.id != ?";
            $params[] = $viaje_id;
        }
        
        $sql .= " ORDER BY v.fecha_salida ASC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function conductorDisponible($conductor_id, $fecha_salida, $fecha_llegada, $viaje_id = null) {
        $sql = "SELECT COUNT(*) FROM viajes 
                WHERE usuario_creador = ? 
                AND estado IN ('PROGRAMADO', 'EN_CURSO')
                AND fecha_salida < ? 
                AND fecha_llegada > ?";
        
        $params = [$conductor_id, $fecha_llegada, $fecha_salida];
        
        if ($viaje_id) {
            $sql .= " AND id != ?";
            $params[] = $viaje_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }

    public function getConflictoConductor($conductor_id, $fecha_salida, $fecha_llegada, $viaje_id = null) {
        $sql = "SELECT v.id, v.fecha_salida, v.fecha_llegada, v.estado,
                       r.origen, r.destino,
                       b.placa
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.usuario_creador = ? 
                AND v.estado IN ('PROGRAMADO', 'EN_CURSO')
                AND v.fecha_salida < ? 
                AND v.fecha_llegada > ?";
        
        $params = [$conductor_id, $fecha_llegada, $fecha_salida];
        
        if ($viaje_id) {
            $sql .= " AND v.id != ?";
            $params[] = $viaje_id;
        }
        
        $sql .= " ORDER BY v.fecha_salida ASC LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // ============================================================
    // CREAR / ACTUALIZAR VIAJE CON ASIENTOS
    // ============================================================

    public function crearConAsientos($data) {
        try {
            $this->db->beginTransaction();
            
            // ✅ Generar hash único del manifiesto
            $hash = hash('sha256', ($data['ruta_id'] ?? 0) . '|' . ($data['bus_id'] ?? 0) . '|' 
                       . ($data['fecha_salida'] ?? '') . '|' . microtime(true));
            
            $sql = "INSERT INTO viajes 
                    (ruta_id, bus_id, usuario_creador, hash_manifiesto, 
                     fecha_salida, fecha_llegada, precio_final, estado, observaciones) 
                    VALUES (:ruta_id, :bus_id, :usuario_creador, :hash_manifiesto,
                            :fecha_salida, :fecha_llegada, :precio_final, :estado, :observaciones)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ruta_id'         => $data['ruta_id'],
                'bus_id'          => $data['bus_id'],
                'usuario_creador' => $data['usuario_creador'],
                'hash_manifiesto' => $hash,
                'fecha_salida'    => $data['fecha_salida'],
                'fecha_llegada'   => $data['fecha_llegada'],
                'precio_final'    => $data['precio_final'],
                'estado'          => $data['estado'] ?? 'PROGRAMADO',
                'observaciones'   => $data['observaciones'] ?? ''
            ]);
            
            $viaje_id = $this->db->lastInsertId();
            
            // ✅ Validación defensiva: si lastInsertId devuelve 0, abortar
            if (!$viaje_id || $viaje_id <= 0) {
                throw new Exception('No se pudo obtener el ID del viaje recién creado.');
            }
            
            $this->generarAsientosParaViaje($viaje_id, $data['bus_id']);
            
            $this->db->commit();
            return $viaje_id;
            
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualizar un viaje existente. Si $regenerar_asientos es true (el bus
     * cambió), borra los asientos actuales y los regenera según el layout
     * del nuevo bus. El controlador es responsable de verificar antes que
     * el viaje no tenga ventas activas.
     */
    public function actualizarConAsientos($id, $data, $regenerar_asientos = false) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE viajes SET 
                        ruta_id = :ruta_id, 
                        bus_id = :bus_id, 
                        usuario_creador = :usuario_creador, 
                        fecha_salida = :fecha_salida, 
                        fecha_llegada = :fecha_llegada, 
                        precio_final = :precio_final, 
                        estado = :estado, 
                        observaciones = :observaciones
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'ruta_id'         => $data['ruta_id'],
                'bus_id'          => $data['bus_id'],
                'usuario_creador' => $data['usuario_creador'],
                'fecha_salida'    => $data['fecha_salida'],
                'fecha_llegada'   => $data['fecha_llegada'],
                'precio_final'    => $data['precio_final'],
                'estado'          => $data['estado'],
                'observaciones'   => $data['observaciones'],
                'id'              => $id,
            ]);

            if ($regenerar_asientos) {
                $this->db->prepare("DELETE FROM asientos WHERE viaje_id = ?")->execute([$id]);
                $this->generarAsientosParaViaje($id, $data['bus_id']);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
 * ✅ Genera asientos manejando correctamente:
 *    - Filas incompletas (cantidad no divisible por asientos_por_fila)
 *    - Columnas ocupadas por elementos especiales (TV, escalera, bloqueado)
 *    - Numeración continua entre pisos
 *    - Verificación final: cantidad generada == cantidad esperada
 *
 * El bucle usa `while` en lugar de `for` para poder generar filas
 * adicionales cuando una fila tenga especiales que consuman columnas.
 */
private function generarAsientosParaViaje($viaje_id, $bus_id) {
    $bus_sql = "SELECT tipo_servicio, pisos,
                       asientos_piso_1, asientos_piso_2,
                       asientos_por_fila_piso_1, asientos_por_fila_piso_2
                FROM buses WHERE id = ?";
    $bus_stmt = $this->db->prepare($bus_sql);
    $bus_stmt->execute([$bus_id]);
    $bus = $bus_stmt->fetch();

    if (!$bus) {
        throw new Exception('No se encontró el bus para generar los asientos.');
    }

    $num_pisos = (int)$bus['pisos'];

    $layout_pisos = [
        '1' => [
            'cantidad' => (int)$bus['asientos_piso_1'],
            'por_fila' => max(1, (int)$bus['asientos_por_fila_piso_1']),
        ],
    ];
    if ($num_pisos === 2) {
        $layout_pisos['2'] = [
            'cantidad' => (int)$bus['asientos_piso_2'],
            'por_fila' => max(1, (int)$bus['asientos_por_fila_piso_2']),
        ];
    }

    // Cargar elementos especiales del bus (TV, escalera, bloqueado)
    $especiales_sql = "SELECT piso, fila, columna, tipo
                        FROM bus_elementos_especiales
                        WHERE bus_id = ?
                        ORDER BY piso ASC, fila ASC, columna ASC";
    $especiales_stmt = $this->db->prepare($especiales_sql);
    $especiales_stmt->execute([$bus_id]);
    $especiales = $especiales_stmt->fetchAll();

    $reservas_columna = [];
    $reservas_pasillo  = [];
    foreach ($especiales as $e) {
        if ((int)$e['columna'] === 0) {
            $reservas_pasillo[$e['piso']][$e['fila']][] = $e['tipo'];
        } else {
            $reservas_columna[$e['piso']][$e['fila']][$e['columna']] = $e['tipo'];
        }
    }

    $asiento_sql = "INSERT INTO asientos
                    (viaje_id, numero_asiento, piso, fila, columna, tipo, es_vendible, disponible, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $asiento_stmt = $this->db->prepare($asiento_sql);

    $numero_global = 1;
    $contador_especiales = 0;

    foreach ($layout_pisos as $piso => $cfg_piso) {
        $por_fila = $cfg_piso['por_fila'];
        $cantidad_vendible = $cfg_piso['cantidad'];

        $vendibles_colocados = 0;
        $fila = 1;

        // ✅ FIX: continuar generando filas hasta colocar TODOS los vendibles.
        //    Cada fila puede tener especiales que consuman columnas, así que
        //    usamos `while` con un tope de seguridad para evitar bucles infinitos.
        $max_filas_seguridad = (int)ceil($cantidad_vendible / $por_fila) + 20;

        while ($vendibles_colocados < $cantidad_vendible && $fila <= $max_filas_seguridad) {

            for ($columna = 1; $columna <= $por_fila; $columna++) {

                // ✅ Reserva en columna real (TV, escalera, bloqueado)
                if (isset($reservas_columna[$piso][$fila][$columna])) {
                    $tipo_reservado = $reservas_columna[$piso][$fila][$columna];
                    $contador_especiales++;
                    $numero_especial = strtoupper(substr($tipo_reservado, 0, 3))
                                     . '-P' . $piso . '-F' . $fila . '-' . $contador_especiales;
                    $asiento_stmt->execute([
                        $viaje_id, $numero_especial, $piso, $fila, $columna,
                        $tipo_reservado, 0, 0, 'OCUPADO'
                    ]);
                    continue;
                }

                // ¿Ya colocamos todos los vendibles?
                if ($vendibles_colocados >= $cantidad_vendible) {
                    continue;
                }

                $tipo = $this->tipoAsientoPorColumna($columna, $por_fila);
                $asiento_stmt->execute([
                    $viaje_id, (string)$numero_global, $piso, $fila, $columna,
                    $tipo, 1, 1, 'DISPONIBLE'
                ]);
                $numero_global++;
                $vendibles_colocados++;
            }

            // Reservas de pasillo (columna=0)
            if (isset($reservas_pasillo[$piso][$fila])) {
                foreach ($reservas_pasillo[$piso][$fila] as $tipo_pasillo) {
                    $contador_especiales++;
                    $numero_especial = strtoupper(substr($tipo_pasillo, 0, 3))
                                     . '-P' . $piso . '-F' . $fila . '-' . $contador_especiales;
                    $asiento_stmt->execute([
                        $viaje_id, $numero_especial, $piso, $fila, 0,
                        $tipo_pasillo, 0, 0, 'OCUPADO'
                    ]);
                }
            }

            $fila++;
        }

        // ✅ Validación defensiva por piso
        if ($vendibles_colocados !== $cantidad_vendible) {
            throw new Exception(
                "Error al generar asientos del piso {$piso}: " .
                "se esperaban {$cantidad_vendible} vendibles pero se colocaron " .
                "{$vendibles_colocados}. Revise los elementos especiales del bus."
            );
        }
    }

    // ✅ Validación final: total generado == total esperado
    $total_esperado = 0;
    foreach ($layout_pisos as $cfg) {
        $total_esperado += $cfg['cantidad'];
    }

    $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM asientos
        WHERE viaje_id = ? AND es_vendible = 1
    ");
    $stmt->execute([$viaje_id]);
    $total_generado = (int)$stmt->fetchColumn();

    if ($total_generado !== $total_esperado) {
        throw new Exception(
            "Verificación final fallida: se esperaban {$total_esperado} asientos " .
            "vendibles pero se generaron {$total_generado} en la base de datos."
        );
    }
}
    

    private function tipoAsientoPorColumna($columna, $por_fila) {
        if ($por_fila === 4) {
            return ($columna == 1 || $columna == 4) ? 'VENTANA' : 'PASILLO';
        }
        if ($por_fila === 3) {
            return ($columna == 1 || $columna == 3) ? 'CAMA' : 'PASILLO';
        }
        if ($por_fila === 2) {
            return 'VENTANA';
        }
        return 'ESTANDAR';
    }

    // ============================================================
    // ASIENTOS
    // ============================================================

    public function getAsientos($viaje_id) {
        $sql = "SELECT * FROM asientos WHERE viaje_id = ? ORDER BY numero_asiento ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$viaje_id]);
        return $stmt->fetchAll();
    }

    public function getAsientosLayout($viaje_id) {
        $sql = "SELECT * FROM asientos 
                WHERE viaje_id = ? 
                ORDER BY piso ASC, fila ASC, columna ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$viaje_id]);
        $asientos = $stmt->fetchAll();
        
        $layout = ['1' => [], '2' => []];
        
        foreach ($asientos as $a) {
            $piso = $a['piso'] ?? '1';
            $fila = $a['fila'] ?? 1;
            
            if (!isset($layout[$piso][$fila])) {
                $layout[$piso][$fila] = [];
            }
            
            $layout[$piso][$fila][] = $a;
        }
        
        foreach ($layout as $piso => $filas) {
            ksort($layout[$piso]);
        }
        
        return $layout;
    }

    // ============================================================
    // ESTADÍSTICAS Y ESTADO
    // ============================================================

    public function getEstadisticas($id) {
        $sql = "SELECT 
                    COUNT(vp.id) as total_ventas,
                    COALESCE(SUM(vp.precio_pagado), 0) as total_recaudado,
                    COALESCE(AVG(vp.precio_pagado), 0) as promedio_pago,
                    COUNT(CASE WHEN vp.metodo_pago = 'EFECTIVO' THEN 1 END) as pagos_efectivo,
                    COUNT(CASE WHEN vp.metodo_pago = 'TARJETA' THEN 1 END) as pagos_tarjeta,
                    COUNT(CASE WHEN vp.metodo_pago = 'TRANSFERENCIA' THEN 1 END) as pagos_transferencia
                FROM ventas_pasajes vp
                WHERE vp.viaje_id = ? AND vp.estado = 'ACTIVA'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function cambiarEstado($id, $estado) {
        $estados_validos = ['PROGRAMADO', 'EN_CURSO', 'COMPLETADO', 'CANCELADO'];
        if (!in_array($estado, $estados_validos, true)) {
            return false;
        }
        
        $sql = "UPDATE viajes SET estado = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$estado, $id]);
    }

        // ============================================================
    // RESERVA TEMPORAL DE ASIENTOS (MULTI-VENDEDOR)
    // ============================================================

    /**
     * Reserva un asiento con bloqueo transaccional (FOR UPDATE).
     * Diferencias clave vs versión anterior:
     *   - Acepta $usuario_id para identificar al dueño de la reserva.
     *   - Usa FOR UPDATE para evitar race conditions entre vendedores.
     *   - Extiende la reserva si el mismo usuario vuelve a reservar.
     *   - Informa cuánto le queda al otro vendedor si está reservado.
     *   - Registra en reservas_log.
     */
    public function reservarAsiento($viaje_id, $asiento_id, $usuario_id, $minutos = 5) {
        try {
            $this->db->beginTransaction();

            // ✅ FOR UPDATE: bloquea la fila hasta el commit/rollback
            $sql = "SELECT id, disponible, estado, reservado_hasta, reservado_por_usuario_id
                    FROM asientos
                    WHERE id = ? AND viaje_id = ?
                    FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$asiento_id, $viaje_id]);
            $asiento = $stmt->fetch();

            if (!$asiento) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Asiento no encontrado'];
            }

            if (!$asiento['disponible'] || $asiento['estado'] === 'OCUPADO') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Este asiento ya fue vendido'];
            }

            $ahora = date('Y-m-d H:i:s');

            // Reservado por OTRO usuario y aún vigente
            if ($asiento['estado'] === 'RESERVADO'
                && $asiento['reservado_hasta'] > $ahora
                && (int)$asiento['reservado_por_usuario_id'] !== (int)$usuario_id
            ) {
                $minutos_restantes = max(1, ceil((strtotime($asiento['reservado_hasta']) - time()) / 60));
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => "Reservado por otro vendedor · expira en {$minutos_restantes} min",
                    'reservado_por_otro' => true,
                    'minutos_restantes' => $minutos_restantes
                ];
            }

            $reservado_hasta = date('Y-m-d H:i:s', strtotime("+{$minutos} minutes"));

            $upd = "UPDATE asientos
                    SET estado = 'RESERVADO',
                        reservado_hasta = ?,
                        reservado_por_usuario_id = ?
                    WHERE id = ? AND viaje_id = ? AND disponible = 1";
            $upd_stmt = $this->db->prepare($upd);
            $upd_stmt->execute([$reservado_hasta, $usuario_id, $asiento_id, $viaje_id]);

            if ($upd_stmt->rowCount() === 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se pudo reservar el asiento (conflicto)'];
            }

            // Log de reserva
            $this->db->prepare(
                "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion, ip_origen)
                 VALUES (?, ?, ?, 'RESERVAR', ?)"
            )->execute([$viaje_id, $asiento_id, $usuario_id, $_SERVER['REMOTE_ADDR'] ?? null]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Asiento reservado por {$minutos} minutos",
                'reservado_hasta' => $reservado_hasta,
                'segundos_restantes' => $minutos * 60
            ];

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if (class_exists('Logger')) {
                Logger::error('Error en reservarAsiento: ' . $e->getMessage());
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Libera la reserva de un asiento específico.
     * Si se pasa $usuario_id, solo libera si el asiento está reservado por ese usuario.
     */
    public function liberarReserva($viaje_id, $asiento_id, $usuario_id = null) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE asientos
                    SET estado = 'DISPONIBLE',
                        reservado_hasta = NULL,
                        reservado_por_usuario_id = NULL
                    WHERE id = ? AND viaje_id = ? AND estado = 'RESERVADO'";

            $params = [$asiento_id, $viaje_id];
            if ($usuario_id !== null) {
                $sql .= " AND reservado_por_usuario_id = ?";
                $params[] = $usuario_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $liberado = $stmt->rowCount() > 0;

            if ($liberado) {
                $this->db->prepare(
                    "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion, ip_origen)
                     VALUES (?, ?, ?, 'LIBERAR', ?)"
                )->execute([
                    $viaje_id,
                    $asiento_id,
                    $usuario_id ?? 0,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }

            $this->db->commit();
            return $liberado;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Libera TODAS las reservas activas del usuario.
     * Se llama al abrir /ventas/create y al cerrar la pestaña (beforeunload).
     */
    public function liberarReservasDeUsuario($usuario_id, $viaje_id = null) {
        try {
            $this->db->beginTransaction();

            $sql = "SELECT id, viaje_id, asiento_id FROM asientos
                    WHERE estado = 'RESERVADO'
                      AND reservado_por_usuario_id = ?";
            $params = [$usuario_id];
            if ($viaje_id) {
                $sql .= " AND viaje_id = ?";
                $params[] = $viaje_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll();

            if (empty($reservas)) {
                $this->db->commit();
                return 0;
            }

            // Log de cada una
            $log_stmt = $this->db->prepare(
                "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion, ip_origen)
                 VALUES (?, ?, ?, 'LIBERAR', ?)"
            );
            foreach ($reservas as $r) {
                $log_stmt->execute([
                    $r['viaje_id'],
                    $r['asiento_id'],
                    $usuario_id,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }

            // Liberar
            $upd_sql = "UPDATE asientos
                        SET estado = 'DISPONIBLE',
                            reservado_hasta = NULL,
                            reservado_por_usuario_id = NULL
                        WHERE estado = 'RESERVADO'
                          AND reservado_por_usuario_id = ?";
            $upd_params = [$usuario_id];
            if ($viaje_id) {
                $upd_sql .= " AND viaje_id = ?";
                $upd_params[] = $viaje_id;
            }

            $upd = $this->db->prepare($upd_sql);
            $upd->execute($upd_params);
            $liberadas = $upd->rowCount();

            $this->db->commit();
            return $liberadas;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return 0;
        }
    }

    /**
     * Libera reservas expiradas. Registra en reservas_log.
     */
    public function liberarReservasExpiradas() {
        try {
            $this->db->beginTransaction();

            $sql_log = "SELECT viaje_id, asiento_id, reservado_por_usuario_id
                        FROM asientos
                        WHERE estado = 'RESERVADO'
                          AND reservado_hasta < NOW()";
            $stmt_log = $this->db->prepare($sql_log);
            $stmt_log->execute();
            $expiradas = $stmt_log->fetchAll();

            if (!empty($expiradas)) {
                $log_stmt = $this->db->prepare(
                    "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion)
                     VALUES (?, ?, ?, 'EXPIRAR')"
                );
                foreach ($expiradas as $e) {
                    $log_stmt->execute([
                        $e['viaje_id'],
                        $e['asiento_id'],
                        $e['reservado_por_usuario_id'] ?? 0
                    ]);
                }
            }

            $sql = "UPDATE asientos
                    SET estado = 'DISPONIBLE',
                        reservado_hasta = NULL,
                        reservado_por_usuario_id = NULL
                    WHERE estado = 'RESERVADO'
                      AND reservado_hasta < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $liberadas = $stmt->rowCount();

            $this->db->commit();
            return $liberadas;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return 0;
        }
    }

    /**
     * Transición automática de estado: PROGRAMADO -> EN_CURSO en cuanto
     * llega (o pasa) la hora de salida. Es perezosa, igual patrón que
     * liberarReservasExpiradas(): se llama al cargar las pantallas donde
     * se listan/consultan viajes, en vez de depender de un cron del
     * servidor (que no siempre está disponible, ej. en hosting
     * compartido o WAMP). No toca EN_CURSO -> COMPLETADO: ese paso sigue
     * siendo manual, ya que "completado" normalmente implica que el bus
     * realmente llegó, algo que el reloj del servidor no puede confirmar
     * por sí solo.
     */
    public function actualizarEstadosPorHora() {
        $sql = "UPDATE viajes 
                SET estado = 'EN_CURSO' 
                WHERE estado = 'PROGRAMADO' AND fecha_salida <= NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Obtiene info completa de un asiento (incluye quién reservó).
     */
    public function getAsientoInfo($asiento_id) {
        $sql = "SELECT a.*,
                       CONCAT(u.nombre, ' ', u.apellido) AS reservado_por_nombre,
                       TIMESTAMPDIFF(SECOND, NOW(), a.reservado_hasta) AS segundos_restantes
                FROM asientos a
                LEFT JOIN usuarios u ON a.reservado_por_usuario_id = u.id
                WHERE a.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$asiento_id]);
        return $stmt->fetch();
    }
}