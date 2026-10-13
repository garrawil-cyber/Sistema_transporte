<?php
class Manifiesto extends Model {
    
    /**
     * Obtener todos los viajes programados o en curso para seleccionar
     */
    public function getViajesParaManifiesto($fecha = null) {
        $sql = "SELECT v.*, 
                       r.origen, r.destino, r.duracion_estimada,
                       b.placa, b.tipo_servicio, b.capacidad_total,
                       (SELECT COUNT(*) FROM ventas_pasajes vp WHERE vp.viaje_id = v.id AND vp.estado = 'ACTIVA') as pasajeros_abordo
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.estado IN ('PROGRAMADO', 'EN_CURSO')
                AND v.fecha_salida >= NOW() - INTERVAL 1 DAY";
        
        if ($fecha) {
            $sql .= " AND DATE(v.fecha_salida) = :fecha";
        }
        $sql .= " ORDER BY v.fecha_salida ASC";
        
        $stmt = $this->db->prepare($sql);
        if ($fecha) {
            $stmt->execute(['fecha' => $fecha]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Obtener todos los datos necesarios para el manifiesto de un viaje específico
     */
    public function getManifiestoData($viaje_id) {
    // ✅ FIX: validar ID antes de consultar
    $viaje_id = (int)$viaje_id;
    if ($viaje_id <= 0) {
        return null;
    }

    // Datos del viaje, ruta, bus
    $sql = "SELECT v.id as viaje_id, v.fecha_salida, v.fecha_llegada, v.precio_final, v.estado,
                   r.id as ruta_id, r.origen, r.destino, r.distancia_km, r.duracion_estimada,
                   b.id as bus_id, b.placa, b.marca, b.modelo, b.tipo_servicio, b.capacidad_total,
                   (SELECT CONCAT(nombre, ' ', apellido) FROM usuarios WHERE id = v.usuario_creador) as conductor_nombre
            FROM viajes v
            JOIN rutas r ON v.ruta_id = r.id
            JOIN buses b ON v.bus_id = b.id
            WHERE v.id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$viaje_id]);
    $viaje = $stmt->fetch();

    if (!$viaje) {
        return null;
    }

    // Lista de pasajeros con sus datos
    $sql2 = "SELECT vp.id as venta_id, vp.numero_comprobante, vp.precio_pagado,
                    a.numero_asiento,
                    c.tipo_documento, c.numero_documento, c.nombre, c.apellido, c.telefono, c.email
             FROM ventas_pasajes vp
             JOIN asientos a ON vp.asiento_id = a.id
             JOIN clientes c ON vp.cliente_id = c.id
             WHERE vp.viaje_id = ? AND vp.estado = 'ACTIVA'
             ORDER BY a.numero_asiento ASC";
    $stmt2 = $this->db->prepare($sql2);
    $stmt2->execute([$viaje_id]);
    $pasajeros = $stmt2->fetchAll();

    $total_asientos = (int)$viaje['capacidad_total'];
    $ocupados       = count($pasajeros);
    $libres         = $total_asientos - $ocupados;

    return [
        'viaje'          => $viaje,
        'pasajeros'      => $pasajeros,
        'total_asientos' => $total_asientos,
        'ocupados'       => $ocupados,
        'libres'         => $libres
    ];
}
/**
 * ✅ FIX: Usar JOIN roles (el rol CONDUCTOR es id=5, no 4)
 * En lugar de hardcodear rol_id, consultamos por nombre del rol.
 */
public function getConductores() {
    $sql = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE r.nombre = 'CONDUCTOR' AND u.activo = 1
            ORDER BY u.apellido ASC, u.nombre ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

    /**
     * Generar hash único para el manifiesto
     */
    public function generarHash($viaje_id) {
        // Clave secreta (definir en config.php)
        $secret = defined('MANIFIESTO_SECRET') ? MANIFIESTO_SECRET : 'clave_secreta_por_defecto_cambiar';
        $hash = hash('sha256', $viaje_id . $secret . date('Y-m-d'));
        return $hash;
    }

    /**
     * Guardar hash en el viaje (si no existe)
     */
    public function guardarHash($viaje_id) {
        $hash = $this->generarHash($viaje_id);
        $sql = "UPDATE viajes SET hash_manifiesto = ? WHERE id = ? AND hash_manifiesto IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hash, $viaje_id]);
        return $hash;
    }

    /**
     * Obtener hash del viaje (si no existe, lo genera)
     */
    public function obtenerHash($viaje_id) {
        $sql = "SELECT hash_manifiesto FROM viajes WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$viaje_id]);
        $row = $stmt->fetch();
        if ($row && $row['hash_manifiesto']) {
            return $row['hash_manifiesto'];
        } else {
            return $this->guardarHash($viaje_id);
        }
    }

    /**
     * Obtener datos del manifiesto por hash (para validación pública)
     */
    public function getManifiestoByHash($hash) {
        $sql = "SELECT v.id as viaje_id, v.fecha_salida, v.fecha_llegada, v.precio_final, v.estado,
                       r.origen, r.destino, r.distancia_km, r.duracion_estimada,
                       b.placa, b.marca, b.modelo, b.tipo_servicio, b.capacidad_total,
                       (SELECT CONCAT(nombre, ' ', apellido) FROM usuarios WHERE id = v.usuario_creador) as conductor_nombre
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.hash_manifiesto = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$hash]);
        $viaje = $stmt->fetch();

        if (!$viaje) {
            return null;
        }

        $sql2 = "SELECT vp.numero_comprobante,
                        a.numero_asiento,
                        c.tipo_documento, c.numero_documento, c.nombre, c.apellido, c.telefono, c.email
                 FROM ventas_pasajes vp
                 JOIN asientos a ON vp.asiento_id = a.id
                 JOIN clientes c ON vp.cliente_id = c.id
                 WHERE vp.viaje_id = ? AND vp.estado = 'ACTIVA'
                 ORDER BY a.numero_asiento ASC";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([$viaje['viaje_id']]);
        $pasajeros = $stmt2->fetchAll();

        return [
            'viaje' => $viaje,
            'pasajeros' => $pasajeros
        ];
    }


    /**
 * Obtener historial paginado de manifiestos
 */
public function getHistorialPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
    $sql = "SELECT m.*, 
                   v.fecha_salida,
                   r.origen, r.destino,
                   b.placa,
                   CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                   (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as total_pasajeros
            FROM manifiestos m
            JOIN viajes v ON m.viaje_id = v.id
            JOIN rutas r ON v.ruta_id = r.id
            JOIN buses b ON v.bus_id = b.id
            JOIN usuarios u ON m.usuario_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (r.origen LIKE ? OR r.destino LIKE ? OR b.placa LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar, $buscar]);
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(v.fecha_salida) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(v.fecha_salida) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    
    $sql .= " ORDER BY m.fecha_generacion DESC";
    
    return $this->paginate($sql, $params, $pagina, $por_pagina);
}
    
}