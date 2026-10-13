<?php
class Conductor extends Model {
    protected $table = 'conductores';

    // Categorías de licencia de conducir válidas en Perú
    const CATEGORIAS_VALIDAS = [
        'A-I', 'A-IIa', 'A-IIb', 'A-IIIa', 'A-IIIb', 'A-IIIc',
        'B-I', 'B-IIa', 'B-IIb', 'B-IIc',
        'C-I', 'C-II', 'C-III',
        'D-I', 'D-II',
        'E-I', 'E-II'
    ];

    // ============================================================
    // CONSULTAS DE LISTADO
    // ============================================================

    /**
     * ✅ FIX #1: Filtro "licencia_por_vencer" ahora EXCLUYE las vencidas
     */
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT c.*,
                       u.nombre, u.apellido, u.email, u.telefono, u.activo AS usuario_activo,
                       DATEDIFF(c.fecha_vencimiento_licencia, CURDATE()) as dias_para_vencer,
                       CASE
                           WHEN c.fecha_vencimiento_licencia < CURDATE() THEN 'VENCIDA'
                           WHEN c.fecha_vencimiento_licencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_licencia
                FROM conductores c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR c.numero_licencia LIKE ? OR u.email LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND c.estado = ?";
            $params[] = $filtros['estado'];
        }

        // ✅ FIX: Ahora excluye las vencidas del filtro "por vencer"
        if (!empty($filtros['licencia_por_vencer'])) {
            $sql .= " AND c.fecha_vencimiento_licencia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        }

        if (!empty($filtros['licencia_vencida'])) {
            $sql .= " AND c.fecha_vencimiento_licencia < CURDATE()";
        }

        $sql .= " ORDER BY u.apellido ASC, u.nombre ASC";

        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    /**
     * ✅ NUEVO: Lista los usuarios con rol CONDUCTOR que NO tienen perfil
     * Se usan para mostrarlos como "pendientes de completar"
     */
    public function getPendientes() {
        $sql = "SELECT u.id AS usuario_id, u.nombre, u.apellido, u.email, u.telefono, u.activo
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE r.nombre = 'CONDUCTOR'
                  AND u.id NOT IN (SELECT usuario_id FROM conductores)
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ✅ FIX #9: Lista conductores disponibles (activos + licencia vigente)
     * Se usa para asignar a viajes
     */
    public function getDisponibles() {
        $sql = "SELECT c.id, c.usuario_id, c.numero_licencia, c.categoria_licencia,
                       c.fecha_vencimiento_licencia, c.foto,
                       u.nombre, u.apellido, u.telefono,
                       CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
                       DATEDIFF(c.fecha_vencimiento_licencia, CURDATE()) as dias_para_vencer
                FROM conductores c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.estado = 'ACTIVO'
                  AND u.activo = 1
                  AND c.fecha_vencimiento_licencia >= CURDATE()
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ============================================================
    // CONSULTAS DE DETALLE
    // ============================================================

    /**
     * ✅ FIX #2: Ahora incluye u.activo, u.rol_id, u.created_at y estado_licencia calculado
     */
    public function getByIdWithDetails($id) {
        $sql = "SELECT c.*,
                       u.nombre, u.apellido, u.email, u.telefono,
                       u.activo AS usuario_activo,
                       u.rol_id AS usuario_rol_id,
                       u.created_at AS usuario_created_at,
                       u.ultimo_acceso AS usuario_ultimo_acceso,
                       DATEDIFF(c.fecha_vencimiento_licencia, CURDATE()) as dias_para_vencer,
                       CASE
                           WHEN c.fecha_vencimiento_licencia < CURDATE() THEN 'VENCIDA'
                           WHEN c.fecha_vencimiento_licencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_licencia
                FROM conductores c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUsuario($usuario_id) {
        $sql = "SELECT c.*, u.nombre, u.apellido, u.email, u.telefono,
                       u.activo AS usuario_activo,
                       CASE
                           WHEN c.fecha_vencimiento_licencia < CURDATE() THEN 'VENCIDA'
                           WHEN c.fecha_vencimiento_licencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'POR_VENCER'
                           ELSE 'VIGENTE'
                       END AS estado_licencia
                FROM conductores c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.usuario_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetch();
    }

    // ============================================================
    // VALIDACIONES DE UNICIDAD
    // ============================================================

    /**
     * ✅ FIX #4: Verifica si un usuario ya tiene perfil de conductor
     */
    public function usuarioYaEsConductor($usuario_id, $excluir_conductor_id = null) {
        $sql = "SELECT id FROM conductores WHERE usuario_id = ?";
        $params = [$usuario_id];
        if ($excluir_conductor_id) {
            $sql .= " AND id != ?";
            $params[] = $excluir_conductor_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Verifica si un número de licencia ya existe
     */
    public function numeroLicenciaExiste($numero_licencia, $excluir_id = null) {
        $sql = "SELECT id FROM conductores WHERE numero_licencia = ?";
        $params = [$numero_licencia];
        if ($excluir_id) {
            $sql .= " AND id != ?";
            $params[] = $excluir_id;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }

    // ============================================================
    // VALIDACIONES DE NEGOCIO
    // ============================================================

    /**
     * ✅ FIX #7: Validaciones completas antes de crear/actualizar
     */
    public function validate($data, $es_creacion = true) {
        $errors = [];

        // Número de licencia (obligatorio, alfanumérico 6-20)
        if (empty($data['numero_licencia'])) {
            $errors[] = 'El número de licencia es obligatorio.';
        } elseif (!preg_match('/^[A-Z0-9\-]{6,20}$/i', $data['numero_licencia'])) {
            $errors[] = 'El número de licencia debe tener entre 6 y 20 caracteres alfanuméricos.';
        }

        // Categoría (obligatoria, válida)
        if (empty($data['categoria_licencia'])) {
            $errors[] = 'La categoría de licencia es obligatoria.';
        } elseif (!in_array($data['categoria_licencia'], self::CATEGORIAS_VALIDAS, true)) {
            $errors[] = 'La categoría de licencia no es válida.';
        }

        // Fecha de vencimiento (obligatoria, futura)
        if (empty($data['fecha_vencimiento_licencia'])) {
            $errors[] = 'La fecha de vencimiento de la licencia es obligatoria.';
        } elseif (strtotime($data['fecha_vencimiento_licencia']) < strtotime('today')) {
            $errors[] = 'La fecha de vencimiento no puede ser anterior a hoy.';
        }

        // Sueldo base (obligatorio, >= 0)
        if (!isset($data['sueldo_base']) || $data['sueldo_base'] === '') {
            $errors[] = 'El sueldo base es obligatorio.';
        } elseif (!is_numeric($data['sueldo_base']) || $data['sueldo_base'] < 0) {
            $errors[] = 'El sueldo base debe ser un número mayor o igual a 0.';
        }

        // Fecha de emisión (opcional, no futura)
        if (!empty($data['fecha_emision_licencia']) &&
            strtotime($data['fecha_emision_licencia']) > time()) {
            $errors[] = 'La fecha de emisión no puede ser futura.';
        }

        // Fecha de vencimiento debe ser posterior a la de emisión
        if (!empty($data['fecha_emision_licencia']) && !empty($data['fecha_vencimiento_licencia'])) {
            if (strtotime($data['fecha_vencimiento_licencia']) <= strtotime($data['fecha_emision_licencia'])) {
                $errors[] = 'La fecha de vencimiento debe ser posterior a la de emisión.';
            }
        }

        // Fecha de nacimiento (opcional, edad 18-75)
        if (!empty($data['fecha_nacimiento'])) {
            $edad = (int)date('Y') - (int)date('Y', strtotime($data['fecha_nacimiento']));
            if ($edad < 18) {
                $errors[] = 'El conductor debe tener al menos 18 años.';
            }
            if ($edad > 75) {
                $errors[] = 'La edad máxima permitida es 75 años.';
            }
        }

        // Contacto de emergencia: nombre solo letras (opcional, pero si
        // viene, debe tener formato de nombre)
        if (!empty($data['contacto_emergencia_nombre']) &&
            !preg_match('/^[\p{L}\s]{2,100}$/u', $data['contacto_emergencia_nombre'])) {
            $errors[] = 'El nombre del contacto de emergencia solo debe contener letras (sin números ni símbolos).';
        }

        // Comisión (0-100)
        if (isset($data['comision_porcentaje']) && $data['comision_porcentaje'] !== '') {
            if (!is_numeric($data['comision_porcentaje']) ||
                $data['comision_porcentaje'] < 0 ||
                $data['comision_porcentaje'] > 100) {
                $errors[] = 'La comisión debe estar entre 0 y 100.';
            }
        }

        return $errors;
    }

    // ============================================================
    // CREAR / ACTUALIZAR
    // ============================================================

    /**
 * Crear conductor con transacción y validación de duplicados
 */
public function crearConTransaccion($data) {
    try {
        $this->db->beginTransaction();

        // Validar usuario duplicado
        if ($this->usuarioYaEsConductor($data['usuario_id'])) {
            throw new Exception('Este usuario ya tiene un perfil de conductor asignado.');
        }

        // Validar licencia duplicada
        if ($this->numeroLicenciaExiste($data['numero_licencia'])) {
            throw new Exception('El número de licencia ya está registrado.');
        }

        // ✅ Validar documento duplicado (si se envió)
        if (!empty($data['numero_documento']) && $this->documentoExiste($data['numero_documento'])) {
            throw new Exception('El número de documento ya está registrado por otro conductor.');
        }

        $sql = "INSERT INTO conductores
                (usuario_id, tipo_documento, numero_documento, foto,
                 numero_licencia, categoria_licencia,
                 fecha_emision_licencia, fecha_vencimiento_licencia, fecha_nacimiento,
                 direccion, contacto_emergencia_nombre, contacto_emergencia_telefono,
                 fecha_ingreso, sueldo_base, comision_porcentaje, estado, observaciones)
                VALUES (:usuario_id, :tipo_documento, :numero_documento, :foto,
                        :numero_licencia, :categoria_licencia,
                        :fecha_emision_licencia, :fecha_vencimiento_licencia, :fecha_nacimiento,
                        :direccion, :contacto_emergencia_nombre, :contacto_emergencia_telefono,
                        :fecha_ingreso, :sueldo_base, :comision_porcentaje, :estado, :observaciones)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'usuario_id'                   => $data['usuario_id'],
            'tipo_documento'               => $data['tipo_documento'] ?? 'DNI',
            'numero_documento'             => $data['numero_documento'] ?? null,
            'foto'                         => $data['foto'] ?? null,
            'numero_licencia'              => $data['numero_licencia'],
            'categoria_licencia'           => $data['categoria_licencia'],
            'fecha_emision_licencia'       => $data['fecha_emision_licencia'] ?? null,
            'fecha_vencimiento_licencia'   => $data['fecha_vencimiento_licencia'],
            'fecha_nacimiento'             => $data['fecha_nacimiento'] ?? null,
            'direccion'                    => $data['direccion'] ?? null,
            'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
            'fecha_ingreso'                => $data['fecha_ingreso'] ?? date('Y-m-d'),
            'sueldo_base'                  => $data['sueldo_base'] ?? 0,
            'comision_porcentaje'          => $data['comision_porcentaje'] ?? 0,
            'estado'                       => $data['estado'] ?? 'ACTIVO',
            'observaciones'                => $data['observaciones'] ?? '',
        ]);

        $conductor_id = (int)$this->db->lastInsertId();

        $this->db->commit();
        return $conductor_id;

    } catch (Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        throw $e;
    }
}

/**
 * Actualizar conductor con validación de duplicados
 */
public function actualizarConTransaccion($id, $data) {
    try {
        $this->db->beginTransaction();

        if ($this->numeroLicenciaExiste($data['numero_licencia'], $id)) {
            throw new Exception('El número de licencia ya está registrado por otro conductor.');
        }

        // ✅ Validar documento duplicado
        if (!empty($data['numero_documento']) && $this->documentoExiste($data['numero_documento'], $id)) {
            throw new Exception('El número de documento ya está registrado por otro conductor.');
        }

        $sql = "UPDATE conductores SET
                    tipo_documento = :tipo_documento,
                    numero_documento = :numero_documento,
                    foto = :foto,
                    numero_licencia = :numero_licencia,
                    categoria_licencia = :categoria_licencia,
                    fecha_emision_licencia = :fecha_emision_licencia,
                    fecha_vencimiento_licencia = :fecha_vencimiento_licencia,
                    fecha_nacimiento = :fecha_nacimiento,
                    direccion = :direccion,
                    contacto_emergencia_nombre = :contacto_emergencia_nombre,
                    contacto_emergencia_telefono = :contacto_emergencia_telefono,
                    fecha_ingreso = :fecha_ingreso,
                    sueldo_base = :sueldo_base,
                    comision_porcentaje = :comision_porcentaje,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id'                           => $id,
            'tipo_documento'               => $data['tipo_documento'] ?? 'DNI',
            'numero_documento'             => $data['numero_documento'] ?? null,
            'foto'                         => $data['foto'] ?? null,
            'numero_licencia'              => $data['numero_licencia'],
            'categoria_licencia'           => $data['categoria_licencia'],
            'fecha_emision_licencia'       => $data['fecha_emision_licencia'] ?? null,
            'fecha_vencimiento_licencia'   => $data['fecha_vencimiento_licencia'],
            'fecha_nacimiento'             => $data['fecha_nacimiento'] ?? null,
            'direccion'                    => $data['direccion'] ?? null,
            'contacto_emergencia_nombre'   => $data['contacto_emergencia_nombre'] ?? null,
            'contacto_emergencia_telefono' => $data['contacto_emergencia_telefono'] ?? null,
            'fecha_ingreso'                => $data['fecha_ingreso'] ?? null,
            'sueldo_base'                  => $data['sueldo_base'] ?? 0,
            'comision_porcentaje'          => $data['comision_porcentaje'] ?? 0,
            'estado'                       => $data['estado'] ?? 'ACTIVO',
            'observaciones'                => $data['observaciones'] ?? '',
        ]);

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
 * Verifica si un número de documento ya existe
 */
public function documentoExiste($numero_documento, $excluir_id = null) {
    $sql = "SELECT id FROM conductores WHERE numero_documento = ?";
    $params = [$numero_documento];
    if ($excluir_id) {
        $sql .= " AND id != ?";
        $params[] = $excluir_id;
    }
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ? true : false;
}

    // ============================================================
    // RESUMEN / ESTADÍSTICAS
    // ============================================================

    public function getResumen() {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'ACTIVO' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'VACACIONES' THEN 1 ELSE 0 END) as vacaciones,
                    SUM(CASE WHEN estado = 'LICENCIA' THEN 1 ELSE 0 END) as licencia,
                    SUM(CASE WHEN estado = 'INACTIVO' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN fecha_vencimiento_licencia >= CURDATE()
                              AND fecha_vencimiento_licencia <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                             THEN 1 ELSE 0 END) as por_vencer,
                    SUM(CASE WHEN fecha_vencimiento_licencia < CURDATE()
                             THEN 1 ELSE 0 END) as vencidas
                FROM conductores";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getLicenciasPorVencer($dias = 30) {
        $dias = (int)$dias;
        $sql = "SELECT c.*, u.nombre, u.apellido, u.telefono, c.foto,
                       DATEDIFF(c.fecha_vencimiento_licencia, CURDATE()) as dias_restantes
                FROM conductores c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.fecha_vencimiento_licencia BETWEEN CURDATE()
                    AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                ORDER BY c.fecha_vencimiento_licencia ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$dias]);
        return $stmt->fetchAll();
    }

    /**
     * ✅ FIX #8: Estadísticas completas del conductor (incluye recaudado)
     */
    public function getEstadisticasViajes($conductor_id) {
        $sql = "SELECT
                    COUNT(v.id) AS total_viajes,
                    SUM(CASE WHEN v.estado = 'COMPLETADO' THEN 1 ELSE 0 END) AS completados,
                    SUM(CASE WHEN v.estado = 'PROGRAMADO' THEN 1 ELSE 0 END) AS programados,
                    SUM(CASE WHEN v.estado = 'EN_CURSO' THEN 1 ELSE 0 END) AS en_curso,
                    SUM(CASE WHEN v.estado = 'CANCELADO' THEN 1 ELSE 0 END) AS cancelados,
                    (SELECT COUNT(*)
                     FROM ventas_pasajes vp
                     JOIN viajes v2 ON vp.viaje_id = v2.id
                     WHERE v2.usuario_creador = ?
                       AND vp.estado = 'ACTIVA') AS total_pasajeros,
                    (SELECT COALESCE(SUM(vp.precio_pagado), 0)
                     FROM ventas_pasajes vp
                     JOIN viajes v2 ON vp.viaje_id = v2.id
                     WHERE v2.usuario_creador = ?
                       AND vp.estado = 'ACTIVA') AS total_recaudado
                FROM viajes v
                WHERE v.usuario_creador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conductor_id, $conductor_id, $conductor_id]);
        return $stmt->fetch();
    }

    /**
     * ✅ FIX #8: Historial de viajes del conductor
     */
    public function getHistorialViajes($conductor_id, $limite = 20) {
        $limite = (int)$limite;
        $sql = "SELECT v.*,
                       r.origen, r.destino,
                       b.placa, b.tipo_servicio,
                       (SELECT COUNT(*) FROM ventas_pasajes vp
                        WHERE vp.viaje_id = v.id AND vp.estado = 'ACTIVA') AS pasajeros,
                       (SELECT COALESCE(SUM(vp.precio_pagado), 0)
                        FROM ventas_pasajes vp
                        WHERE vp.viaje_id = v.id AND vp.estado = 'ACTIVA') AS recaudado
                FROM viajes v
                JOIN rutas r ON v.ruta_id = r.id
                JOIN buses b ON v.bus_id = b.id
                WHERE v.usuario_creador = ?
                ORDER BY v.fecha_salida DESC
                LIMIT $limite";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conductor_id]);
        return $stmt->fetchAll();
    }

    // ============================================================
    // ELIMINAR
    // ============================================================

    /**
     * ✅ FIX #8: Verifica si el conductor tiene viajes antes de eliminar
     */
    public function tieneViajesAsignados($conductor_id) {
        $sql = "SELECT COUNT(*) FROM viajes WHERE usuario_creador = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conductor_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Elimina el conductor y opcionalmente su usuario asociado
     */
    public function eliminarConUsuario($conductor_id, $eliminar_usuario = false) {
        try {
            $this->db->beginTransaction();

            $conductor = $this->find($conductor_id);
            if (!$conductor) {
                throw new Exception('Conductor no encontrado.');
            }

            // Si tiene viajes, no se puede eliminar
            if ($this->tieneViajesAsignados($conductor['usuario_id'])) {
                throw new Exception('No se puede eliminar: el conductor tiene viajes asignados. Márquelo como INACTIVO en su lugar.');
            }

            $this->db->prepare("DELETE FROM conductores WHERE id = ?")->execute([$conductor_id]);

            if ($eliminar_usuario) {
                $this->db->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$conductor['usuario_id']]);
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
}