<?php
class Cliente extends Model {
    protected $table = 'clientes';

    /**
     * Obtener todos los clientes con información adicional
     */
    public function getAllWithDetails() {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE cliente_id = c.id) as total_compras,
                       (SELECT COUNT(*) FROM encomiendas WHERE remitente_id = c.id OR destinatario_id = c.id) as total_encomiendas,
                       (SELECT COUNT(*) FROM giros WHERE remitente_id = c.id OR destinatario_id = c.id) as total_giros,
                       (SELECT SUM(precio_pagado) FROM ventas_pasajes WHERE cliente_id = c.id AND estado = 'ACTIVA') as total_gastado
                FROM clientes c
                ORDER BY c.apellido ASC, c.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buscar cliente por número de documento
     */
    public function findByDocumento($numero) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE numero_documento = ?");
        $stmt->execute([$numero]);
        return $stmt->fetch();
    }

    /**
     * Buscar clientes por término (nombre, apellido, documento)
     */
    public function buscar($termino) {
        $termino = "%$termino%";
        $sql = "SELECT * FROM clientes 
                WHERE nombre LIKE ? 
                OR apellido LIKE ? 
                OR numero_documento LIKE ? 
                OR email LIKE ? 
                ORDER BY apellido ASC, nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$termino, $termino, $termino, $termino]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un cliente por ID con estadísticas
     */
    public function getByIdWithStats($id) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE cliente_id = c.id) as total_compras,
                       (SELECT COUNT(*) FROM encomiendas WHERE remitente_id = c.id OR destinatario_id = c.id) as total_encomiendas,
                       (SELECT COUNT(*) FROM giros WHERE remitente_id = c.id OR destinatario_id = c.id) as total_giros,
                       (SELECT SUM(precio_pagado) FROM ventas_pasajes WHERE cliente_id = c.id AND estado = 'ACTIVA') as total_gastado,
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE cliente_id = c.id AND estado = 'ACTIVA' AND DATE(fecha_venta) >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as compras_ultimo_mes,
                       (SELECT SUM(precio_pagado) FROM ventas_pasajes WHERE cliente_id = c.id AND estado = 'ACTIVA' AND DATE(fecha_venta) >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as gasto_ultimo_mes
                FROM clientes c
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Validar datos antes de guardar
     */
    public function validate($data) {
        $errors = [];
        
        if (empty($data['numero_documento'])) {
            $errors[] = 'El número de documento es obligatorio';
        } else {
            // Verificar formato según tipo de documento
            if ($data['tipo_documento'] == 'DNI' && !preg_match('/^[0-9]{8}$/', $data['numero_documento'])) {
                $errors[] = 'DNI inválido. Debe tener 8 dígitos';
            }
            if ($data['tipo_documento'] == 'CE' && !preg_match('/^[0-9]{8,12}$/', $data['numero_documento'])) {
                $errors[] = 'Carné de Extranjería inválido';
            }
            
            // Verificar documento duplicado
            $id = $data['id'] ?? 0;
            $stmt = $this->db->prepare("SELECT id FROM clientes WHERE numero_documento = ? AND id != ?");
            $stmt->execute([$data['numero_documento'], $id]);
            if ($stmt->fetch()) {
                $errors[] = 'El número de documento ya está registrado';
            }
        }

        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio';
        }

        if (empty($data['apellido'])) {
            $errors[] = 'El apellido es obligatorio';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }

        if (!empty($data['telefono']) && !preg_match('/^[0-9]{7,15}$/', $data['telefono'])) {
            $errors[] = 'El teléfono debe tener entre 7 y 15 dígitos';
        }

        return $errors;
    }

    /**
     * Crear o actualizar cliente (si existe, devolverlo)
     * 
     * ✅ FIX #1: INSERT directo (garantiza lastInsertId)
     * ✅ FIX #2: Solo UPDATE si hay cambios reales
     * ✅ FIX #3: Valida lastInsertId > 0
     * ✅ FIX #4: Maneja correctamente valores null
     * ✅ FIX #5: Auditoría de INSERT
     */
    public function findOrCreate($data) {
        // 1. Validación previa
        if (empty($data['numero_documento'])) {
            throw new Exception('El número de documento es obligatorio para registrar un cliente');
        }

        // 2. Buscar cliente existente
        $existente = $this->findByDocumento($data['numero_documento']);
        
        if ($existente && !empty($existente['id'])) {
            // ✅ FIX #2: Detectar si hay cambios reales antes de actualizar
            $campos_comparables = ['tipo_documento', 'nombre', 'apellido', 'telefono', 'email', 'direccion'];
            $datos_actualizados = [];

            foreach ($campos_comparables as $campo) {
                $valor_actual = (string)($existente[$campo] ?? '');
                $valor_nuevo  = (string)($data[$campo]      ?? '');
                
                if ($valor_actual !== $valor_nuevo) {
                    $datos_actualizados[$campo] = $data[$campo];
                }
            }

            // Solo actualizar si hay cambios reales
            if (!empty($datos_actualizados)) {
                try {
                    $this->update((int)$existente['id'], $datos_actualizados);
                } catch (Exception $e) {
                    if (class_exists('Logger')) {
                        try { Logger::error('Error al actualizar cliente existente: ' . $e->getMessage()); }
                        catch (Exception $le) { error_log($le->getMessage()); }
                    }
                }
            }

            return (int)$existente['id'];
        }

        // 3. Crear cliente nuevo con INSERT directo
        // ✅ FIX #1: INSERT directo garantiza lastInsertId real
        $sql = "INSERT INTO clientes 
                (tipo_documento, numero_documento, nombre, apellido, telefono, email, direccion) 
                VALUES 
                (:tipo_documento, :numero_documento, :nombre, :apellido, :telefono, :email, :direccion)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tipo_documento'   => $data['tipo_documento']   ?? 'DNI',
            'numero_documento' => $data['numero_documento'],
            'nombre'           => $data['nombre']           ?? '',
            'apellido'         => $data['apellido']         ?? '',
            'telefono'         => !empty($data['telefono']) ? $data['telefono'] : null,
            'email'            => !empty($data['email'])    ? $data['email']    : null,
            'direccion'        => !empty($data['direccion'])? $data['direccion']: null,
        ]);

        $nuevo_id = (int)$this->db->lastInsertId();

        // ✅ FIX #3: Verificación crítica
        if ($nuevo_id <= 0) {
            throw new Exception(
                'No se pudo crear el cliente. lastInsertId devolvió: ' . var_export($nuevo_id, true)
            );
        }

        // ✅ FIX #5: Auditoría manual (porque usamos INSERT directo)
        if (class_exists('Auditoria')) {
            try {
                Auditoria::registrarCRUD('INSERT', 'clientes', $nuevo_id, null, [
                    'tipo_documento'   => $data['tipo_documento']   ?? 'DNI',
                    'numero_documento' => $data['numero_documento'],
                    'nombre'           => $data['nombre']           ?? '',
                    'apellido'         => $data['apellido']         ?? '',
                    'telefono'         => $data['telefono']         ?? null,
                    'email'            => $data['email']            ?? null,
                    'direccion'        => $data['direccion']        ?? null,
                ]);
            } catch (Exception $e) {
                if (class_exists('Logger')) {
                    try { Logger::error('Error en auditoría cliente: ' . $e->getMessage()); }
                    catch (Exception $le) { error_log($le->getMessage()); }
                }
            }
        }

        return $nuevo_id;
    }

    /**
     * Obtener clientes frecuentes (top compradores)
     */
    public function getFrecuentes($limite = 10) {
        $limite = (int)$limite;
        $sql = "SELECT c.*, 
                       COUNT(vp.id) as total_compras,
                       SUM(vp.precio_pagado) as total_gastado
                FROM clientes c
                JOIN ventas_pasajes vp ON c.id = vp.cliente_id
                WHERE vp.estado = 'ACTIVA'
                GROUP BY c.id
                ORDER BY total_compras DESC
                LIMIT $limite";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM ventas_pasajes WHERE cliente_id = c.id) as total_compras,
                       (SELECT COALESCE(SUM(precio_pagado),0) FROM ventas_pasajes WHERE cliente_id = c.id AND estado = 'ACTIVA') as total_gastado
                FROM clientes c
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (c.nombre LIKE ? OR c.apellido LIKE ? OR c.numero_documento LIKE ? OR c.email LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
        }
        
        $sql .= " ORDER BY c.apellido ASC, c.nombre ASC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }
}