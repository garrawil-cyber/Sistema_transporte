<?php
class Venta extends Model {
    protected $table = 'ventas_pasajes';

    public function getAllWithDetails() {
        $sql = "SELECT vp.*, 
                       c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.numero_documento,
                       r.origen, r.destino, 
                       v.fecha_salida, v.fecha_llegada,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido,
                       a.numero_asiento
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN usuarios u ON vp.usuario_id = u.id
                JOIN asientos a ON vp.asiento_id = a.id
                ORDER BY vp.fecha_venta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIdWithDetails($id) {
        $sql = "SELECT vp.*, 
                       c.tipo_documento, c.numero_documento, c.nombre as cliente_nombre, 
                       c.apellido as cliente_apellido, c.telefono, c.email,
                       r.origen, r.destino, r.distancia_km,
                       v.fecha_salida, v.fecha_llegada,
                       b.placa, b.tipo_servicio,
                       u.nombre as vendedor_nombre, u.apellido as vendedor_apellido,
                       a.numero_asiento,
                       a.tipo as asiento_tipo
                FROM ventas_pasajes vp
                JOIN clientes c ON vp.cliente_id = c.id
                JOIN viajes v ON vp.viaje_id = v.id
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                JOIN usuarios u ON vp.usuario_id = u.id
                JOIN asientos a ON vp.asiento_id = a.id
                WHERE vp.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Registrar una nueva venta (transacción)
     * 
     * ✅ CAPA 1: Validación de caja abierta
     * ✅ CAPA 2: Bloqueo FOR UPDATE + índice único
     * ✅ CAPA 3: Manejo elegante del error de índice único
     * ✅ FIX #1: execute() con array explícito de claves
     * ✅ FIX #2: Correlativo generado DENTRO de la transacción
     */
    public function registrar($data) {
        // CAPA 1: Verificar caja abierta
        $cajaModel = new Caja();
        if (!$cajaModel->tieneCajaAbierta($data['usuario_id'])) {
            throw new Exception('No se puede registrar una venta sin una caja abierta. Abra una caja primero.');
        }

        try {
            $this->db->beginTransaction();

            // CAPA 2: Bloquear la fila del asiento
            $check_sql = "SELECT disponible, estado 
                          FROM asientos 
                          WHERE id = ? AND viaje_id = ? 
                          FOR UPDATE";
            $check_stmt = $this->db->prepare($check_sql);
            $check_stmt->execute([$data['asiento_id'], $data['viaje_id']]);
            $asiento = $check_stmt->fetch();

            if (!$asiento) {
                throw new Exception('El asiento seleccionado no existe en este viaje.');
            }

            if (!$asiento['disponible'] || $asiento['estado'] === 'OCUPADO') {
                throw new Exception('⚠️ El asiento ya fue vendido por otro usuario hace unos segundos. Por favor seleccione otro asiento.');
            }

            // ✅ FIX #2: Generar comprobante DENTRO de la transacción
            $numero_comprobante = $data['numero_comprobante'] ?? $this->generarComprobanteInterno();

            // ✅ FIX #1: Insert con array explícito
            $sql = "INSERT INTO ventas_pasajes 
                    (viaje_id, cliente_id, usuario_id, asiento_id, precio_pagado, metodo_pago, numero_comprobante, estado, fecha_venta) 
                    VALUES (:viaje_id, :cliente_id, :usuario_id, :asiento_id, :precio_pagado, :metodo_pago, :numero_comprobante, 'ACTIVA', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'viaje_id'           => $data['viaje_id'],
                'cliente_id'         => $data['cliente_id'],
                'usuario_id'         => $data['usuario_id'],
                'asiento_id'         => $data['asiento_id'],
                'precio_pagado'      => $data['precio_pagado'],
                'metodo_pago'        => $data['metodo_pago'],
                'numero_comprobante' => $numero_comprobante,
            ]);
            $venta_id = (int)$this->db->lastInsertId();

            if ($venta_id <= 0) {
                throw new Exception('No se pudo obtener el ID de la venta recién creada.');
            }

            // Marcar asiento como ocupado
            $sql2 = "UPDATE asientos 
                     SET disponible = 0, 
                         estado = 'OCUPADO',
                         reservado_hasta = NULL 
                     WHERE id = :asiento_id";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute(['asiento_id' => $data['asiento_id']]);

            $this->db->commit();
            return $venta_id;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            if ($e->getCode() == '23000' || strpos($e->getMessage(), 'idx_venta_asiento_unico') !== false) {
                throw new Exception('⚠️ El asiento fue vendido por otro usuario hace unos segundos. Por favor seleccione otro asiento.');
            }

            throw new Exception('Error al registrar la venta: ' . $e->getMessage());

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Anular una venta (liberar asiento + anular comprobante + registrar egreso en caja)
     * 
     * ✅ FIX #3: Marca el comprobante electrónico como ANULADO
     */
    public function anular($venta_id) {
        try {
            $this->db->beginTransaction();

            $venta = $this->find($venta_id);
            if (!$venta || $venta['estado'] == 'ANULADA') {
                $this->db->rollBack();
                return false;
            }

            // 1. Marcar venta como anulada
            $sql1 = "UPDATE ventas_pasajes SET estado = 'ANULADA' WHERE id = ?";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([$venta_id]);

            // 2. Liberar asiento
            $sql2 = "UPDATE asientos 
                     SET disponible = 1, 
                         estado = 'DISPONIBLE',
                         reservado_hasta = NULL 
                     WHERE id = ?";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([$venta['asiento_id']]);

            // ✅ FIX #3: Marcar comprobante electrónico como ANULADO
            try {
                $sql3 = "UPDATE comprobantes_electronicos 
                         SET estado = 'ANULADO', 
                             observaciones = CONCAT(COALESCE(observaciones, ''), ' | Anulado por venta anulada el ', NOW())
                         WHERE venta_id = ? AND estado != 'ANULADO'";
                $stmt3 = $this->db->prepare($sql3);
                $stmt3->execute([$venta_id]);
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    try { Logger::error('Error al anular comprobante: ' . $e->getMessage()); }
                    catch (Exception $le) { error_log($le->getMessage()); }
                }
            }

            // 3. Registrar egreso en caja
            try {
                $cajaModel = new Caja();
                $caja_abierta = $cajaModel->getCajaAbierta($venta['usuario_id']);

                if ($caja_abierta) {
                    $cajaModel->registrarMovimiento(
                        $caja_abierta['id'],
                        $venta['usuario_id'],
                        'EGRESO',
                        "Anulación venta {$venta['numero_comprobante']}",
                        $venta['precio_pagado'],
                        $venta['metodo_pago'],
                        'ANU-' . $venta['numero_comprobante']
                    );
                }
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    try { Logger::error('Error al registrar anulación en caja: ' . $e->getMessage()); }
                    catch (Exception $le) { error_log($le->getMessage()); }
                }
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if (class_exists('Logger')) {
                try { Logger::error('Error al anular venta: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            return false;
        }
    }

    /**
     * Generar número de comprobante usando facturacion_correlativos
     * 
     * ⚠️ ADVERTENCIA: Este método abre su PROPIA transacción.
     *    Si lo llamas fuera de registrar(), puede quemar correlativos si
     *    el resto del flujo falla. Se recomienda usar generarComprobanteInterno()
     *    dentro de registrar().
     * 
     * @deprecated Usar generarComprobanteInterno() dentro de registrar()
     */
    public function generarComprobante() {
        $this->db->beginTransaction();
        try {
            $resultado = $this->generarComprobanteInterno();
            $this->db->commit();
            return $resultado;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * ✅ NUEVO: Generar comprobante SIN abrir transacción propia.
     *    Debe llamarse DENTRO de una transacción existente.
     * 
     * @return string Número de comprobante completo (ej: "B001-00000123")
     */
    private function generarComprobanteInterno() {
        $serie = 'B001';
        $tipo  = 'BOLETA';

        $stmt = $this->db->prepare(
            "SELECT ultimo_correlativo FROM facturacion_correlativos 
             WHERE tipo = ? AND serie = ? FOR UPDATE"
        );
        $stmt->execute([$tipo, $serie]);
        $row = $stmt->fetch();

        if ($row) {
            $nuevo = (int)$row['ultimo_correlativo'] + 1;
            $upd = $this->db->prepare(
                "UPDATE facturacion_correlativos 
                 SET ultimo_correlativo = ? 
                 WHERE tipo = ? AND serie = ?"
            );
            $upd->execute([$nuevo, $tipo, $serie]);
        } else {
            $nuevo = 1;
            $ins = $this->db->prepare(
                "INSERT INTO facturacion_correlativos (tipo, serie, ultimo_correlativo) 
                 VALUES (?, ?, ?)"
            );
            $ins->execute([$tipo, $serie, $nuevo]);
        }

        return $serie . '-' . str_pad($nuevo, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Totales filtrados (para cards de resumen en index)
     */
public function getTotalesFiltrados($filtros = [])
{
    $sql = "SELECT 
                COUNT(*) as total_ventas,
                COALESCE(SUM(CASE WHEN vp.estado = 'ACTIVA' THEN vp.precio_pagado ELSE 0 END), 0) as total_recaudado,
                SUM(CASE WHEN vp.estado = 'ACTIVA' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN vp.estado = 'ANULADA' THEN 1 ELSE 0 END) as anuladas
            FROM ventas_pasajes vp
            JOIN clientes c ON vp.cliente_id = c.id
            WHERE 1=1";

    $params = [];

    if (!empty($filtros['estado'])) {
        $sql .= " AND vp.estado = ?";
        $params[] = $filtros['estado'];
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(vp.fecha_venta) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(vp.fecha_venta) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.numero_documento LIKE ? OR vp.numero_comprobante LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
    }
    // ✅ NUEVO: filtro por vendedor
    if (!empty($filtros['vendedor_id'])) {
        $sql .= " AND vp.usuario_id = ?";
        $params[] = (int)$filtros['vendedor_id'];
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}
    /**
     * Obtener ventas paginadas
     */
public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = [])
{
    $sql = "SELECT vp.*, 
                   c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.numero_documento,
                   r.origen, r.destino, r.id as ruta_id,
                   v.fecha_salida,
                   b.placa,
                   a.numero_asiento,
                   a.tipo as asiento_tipo,
                   CONCAT(u.nombre, ' ', u.apellido) as vendedor
            FROM ventas_pasajes vp
            JOIN clientes c ON vp.cliente_id = c.id
            JOIN viajes v ON vp.viaje_id = v.id
            JOIN rutas r ON v.ruta_id = r.id
            JOIN buses b ON v.bus_id = b.id
            JOIN asientos a ON vp.asiento_id = a.id
            JOIN usuarios u ON vp.usuario_id = u.id
            WHERE 1=1";

    $params = [];

    if (!empty($filtros['estado'])) {
        $sql .= " AND vp.estado = ?";
        $params[] = $filtros['estado'];
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(vp.fecha_venta) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(vp.fecha_venta) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.numero_documento LIKE ? OR vp.numero_comprobante LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
    }
    // ✅ NUEVO: filtro por vendedor
    if (!empty($filtros['vendedor_id'])) {
        $sql .= " AND vp.usuario_id = ?";
        $params[] = (int)$filtros['vendedor_id'];
    }

    $sql .= " ORDER BY vp.fecha_venta DESC";

    return $this->paginate($sql, $params, $pagina, $por_pagina);
}

/**
 * Obtener TODAS las ventas que cumplan los filtros (sin paginación).
 * Usado por el PDF del listado.
 * 
 * @param array $filtros Mismos filtros que getPaginated()
 * @return array
 */
public function getAllWithFiltros($filtros = [])
{
    $sql = "SELECT vp.*, 
                   c.nombre as cliente_nombre, c.apellido as cliente_apellido, c.numero_documento,
                   r.origen, r.destino,
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
            WHERE 1=1";

    $params = [];

    if (!empty($filtros['estado'])) {
        $sql .= " AND vp.estado = ?";
        $params[] = $filtros['estado'];
    }
    if (!empty($filtros['fecha_inicio'])) {
        $sql .= " AND DATE(vp.fecha_venta) >= ?";
        $params[] = $filtros['fecha_inicio'];
    }
    if (!empty($filtros['fecha_fin'])) {
        $sql .= " AND DATE(vp.fecha_venta) <= ?";
        $params[] = $filtros['fecha_fin'];
    }
    if (!empty($filtros['buscar'])) {
        $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.numero_documento LIKE ? OR vp.numero_comprobante LIKE ?)";
        $buscar = "%{$filtros['buscar']}%";
        $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
    }
    // ✅ NUEVO: filtro por vendedor
    if (!empty($filtros['vendedor_id'])) {
        $sql .= " AND vp.usuario_id = ?";
        $params[] = (int)$filtros['vendedor_id'];
    }

    $sql .= " ORDER BY vp.fecha_venta DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Obtener vendedores que tienen ventas registradas.
 * Usado para el filtro por vendedor.
 */
public function getVendedoresConVentas()
{
    $sql = "SELECT DISTINCT u.id, u.nombre, u.apellido,
                   CONCAT(u.nombre, ' ', u.apellido) as nombre_completo,
                   COUNT(vp.id) as total_ventas
            FROM usuarios u
            INNER JOIN ventas_pasajes vp ON vp.usuario_id = u.id
            GROUP BY u.id, u.nombre, u.apellido
            ORDER BY u.nombre ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

}