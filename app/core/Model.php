<?php
class Model {
    protected $db;
    protected $table;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Verificar si la tabla tiene una columna específica
     */
    protected function tieneColumna($columna) {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$this->table} LIKE ?");
            $stmt->execute([$columna]);
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener todos los registros (solo activos si existe la columna)
     */
    public function all() {
        if ($this->tieneColumna('activo')) {
            $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE activo = 1 ORDER BY id DESC");
        } else {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * ============================================
     * CREATE con auditoría automática
     * ============================================
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        
        // ✅ Registro automático en auditoría
        if ($result && class_exists('Auditoria')) {
            try {
                $id = $this->db->lastInsertId();
                Auditoria::registrarCRUD('INSERT', $this->table, $id, null, $data);
            } catch (Exception $e) {
                // Silencioso: no interrumpir el flujo si falla la auditoría
                Logger::error('Error al registrar auditoría INSERT: ' . $e->getMessage());
            }
        }
        
        return $result;
    }

    /**
     * ============================================
     * UPDATE con auditoría automática
     * ============================================
     */
    public function update($id, $data) {
        // Obtener valores anteriores
        $valores_anteriores = $this->find($id);
        
        // Remover campos sensibles
        if (isset($valores_anteriores['password'])) {
            $valores_anteriores['password'] = '[PROTEGIDO]';
        }
        
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE {$this->table} SET $set WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        
        // ✅ Registro automático en auditoría
        if ($result && class_exists('Auditoria')) {
            try {
                $valores_nuevos = $data;
                unset($valores_nuevos['id']);
                
                // Ocultar contraseña
                if (isset($valores_nuevos['password'])) {
                    $valores_nuevos['password'] = '[PROTEGIDO]';
                }
                
                // Detectar solo cambios reales
                $cambios = [];
                foreach ($valores_nuevos as $key => $value) {
                    if (isset($valores_anteriores[$key]) && $valores_anteriores[$key] != $value) {
                        $cambios[$key] = [
                            'antes' => $valores_anteriores[$key],
                            'ahora' => $value
                        ];
                    }
                }
                
                Auditoria::registrarCRUD('UPDATE', $this->table, $id, $valores_anteriores, $valores_nuevos);
            } catch (Exception $e) {
                Logger::error('Error al registrar auditoría UPDATE: ' . $e->getMessage());
            }
        }
        
        return $result;
    }

    /**
     * ============================================
     * DELETE con auditoría automática
     * ============================================
     */
    public function delete($id) {
        // Obtener valores antes de eliminar
        $valores_anteriores = $this->find($id);
        
        // Ocultar contraseña si existe
        if ($valores_anteriores && isset($valores_anteriores['password'])) {
            $valores_anteriores['password'] = '[PROTEGIDO]';
        }
        
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        // ✅ Registro automático en auditoría
        if ($result && class_exists('Auditoria')) {
            try {
                Auditoria::registrarCRUD('DELETE', $this->table, $id, $valores_anteriores, null);
            } catch (Exception $e) {
                Logger::error('Error al registrar auditoría DELETE: ' . $e->getMessage());
            }
        }
        
        return $result;
    }

    /**
     * ============================================
     * PAGINACIÓN
     * ============================================
     */
    public function paginate($sql, $params = [], $pagina = 1, $por_pagina = 15) {
        $sql_count = "SELECT COUNT(*) as total FROM (" . $sql . ") as subquery";
        $stmt = $this->db->prepare($sql_count);
        $stmt->execute($params);
        $total = $stmt->fetch()['total'];

        $paginator = new Paginator($total, $por_pagina, $pagina);

        $sql_paginada = $sql . " LIMIT " . $paginator->registros_por_pagina . " OFFSET " . $paginator->offset;
        $stmt = $this->db->prepare($sql_paginada);
        $stmt->execute($params);
        $datos = $stmt->fetchAll();

        return [
            'datos' => $datos,
            'paginator' => $paginator
        ];
    }
}