<?php
class Usuario extends Model {
    protected $table = 'usuarios';

    // ============================================================
    // AUTENTICACIÓN
    // ============================================================

    /**
     * Autenticar usuario por email + password.
     * ✅ FIX: se eliminaron los error_log() que exponían el hash
     *    de la contraseña en los logs del servidor. Se conserva
     *    un log mínimo solo si la constante DEBUG está definida.
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user) {
            if (defined('DEBUG') && DEBUG) {
                error_log("Login fallido: usuario no encontrado — {$email}");
            }
            return false;
        }

        if (!password_verify($password, $user['password'])) {
            if (defined('DEBUG') && DEBUG) {
                error_log("Login fallido: contraseña incorrecta — {$email}");
            }
            return false;
        }

        return $user;
    }

    /**
     * ✅ FIX: findByEmail() ahora acepta un ID a excluir.
     *    Evita falsos positivos al verificar duplicados durante
     *    la edición (permite mantener el mismo email propio).
     */
    public function findByEmail($email, $excludeId = null) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // ============================================================
    // CONSULTAS CON JOINS
    // ============================================================

    public function getAllWithRoles() {
        $sql = "SELECT u.id, u.rol_id, u.nombre, u.apellido, u.email,
                       u.telefono, u.activo, u.ultimo_acceso,
                       u.created_at, u.updated_at,
                       r.nombre as rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                ORDER BY u.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByIdWithRole($id) {
        $sql = "SELECT u.id, u.rol_id, u.nombre, u.apellido, u.email,
                       u.telefono, u.activo, u.ultimo_acceso,
                       u.created_at, u.updated_at,
                       r.nombre as rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ============================================================
    // CREAR / ACTUALIZAR (hasheo seguro)
    // ============================================================

    /**
     * ✅ FIX: create() detecta si la contraseña YA viene hasheada
     *    (patrón bcrypt) para evitar el doble hasheo, que rompía
     *    el login cuando se pasaba un hash ya procesado.
     */
    public function create($data) {
        if (isset($data['password']) && !empty($data['password'])) {
            if (!$this->esHashBcrypt($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        } else {
            unset($data['password']);
        }
        return parent::create($data);
    }

    public function update($id, $data) {
        if (isset($data['password']) && !empty($data['password'])) {
            if (!$this->esHashBcrypt($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        } else {
            unset($data['password']);
        }
        return parent::update($id, $data);
    }

    /**
     * Detecta si un string ya es un hash bcrypt válido
     */
    private function esHashBcrypt($valor) {
        return (bool) preg_match('/^\$2[ayb]\$.{56}$/', $valor);
    }

    // ============================================================
    // ACTIVAR / DESACTIVAR
    // ============================================================

    public function desactivar($id) {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function activar($id) {
        $sql = "UPDATE usuarios SET activo = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function registrarAcceso($id) {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ============================================================
    // CONDUCTORES
    // ============================================================

    /**
     * ✅ FIX: Se dejó de usar u.* y se listan columnas explícitas
     *    para NO exponer el campo password aunque el resultado
     *    llegue accidentalmente a una vista.
     */
    public function getConductores() {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.telefono,
                       u.activo, u.ultimo_acceso,
                       r.nombre as rol_nombre,
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE r.nombre = 'CONDUCTOR'
                  AND u.activo = 1
                ORDER BY u.apellido ASC, u.nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getConductorById($id) {
        $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.telefono,
                       u.activo, u.ultimo_acceso,
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_completo
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.id = ? AND r.nombre = 'CONDUCTOR'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ============================================================
    // PAGINACIÓN
    // ============================================================

    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT u.id, u.rol_id, u.nombre, u.apellido, u.email,
                       u.telefono, u.activo, u.ultimo_acceso,
                       u.created_at, u.updated_at,
                       r.nombre as rol_nombre
                FROM usuarios u
                JOIN roles r ON u.rol_id = r.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['buscar'])) {
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR u.telefono LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['rol_id'])) {
            $sql .= " AND u.rol_id = ?";
            $params[] = $filtros['rol_id'];
        }
        if (isset($filtros['activo']) && $filtros['activo'] !== '') {
            $sql .= " AND u.activo = ?";
            $params[] = $filtros['activo'];
        }

        $sql .= " ORDER BY u.id DESC";

        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    // ============================================================
    // CREACIÓN CON PASSWORD (usado desde ConductoresController)
    // ============================================================

    /**
     * ✅ FIX: createWithPassword() ahora:
     *    1. Valida email único ANTES de intentar el INSERT.
     *    2. Captura PDOException con código 23000 (duplicate key)
     *       para blindar contra race conditions.
     */
    public function createWithPassword($data) {
        // Validación temprana (evita un viaje innecesario a la BD)
        if (empty($data['email'])) {
            throw new Exception('El email es obligatorio.');
        }
        if ($this->findByEmail($data['email'])) {
            throw new Exception('El email ya está registrado.');
        }

        $password = $data['password'] ?? 'conductor123';
        if (!$this->esHashBcrypt($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $sql = "INSERT INTO usuarios (rol_id, nombre, apellido, email, password, telefono, activo)
                    VALUES (:rol_id, :nombre, :apellido, :email, :password, :telefono, :activo)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'rol_id'   => $data['rol_id']   ?? 5,
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'],
                'email'    => $data['email'],
                'password' => $password,
                'telefono' => $data['telefono'] ?? null,
                'activo'   => isset($data['activo']) ? (int)$data['activo'] : 1,
            ]);

            return (int)$this->db->lastInsertId();

        } catch (PDOException $e) {
            // 23000 = Integrity constraint violation (email duplicado)
            if ($e->getCode() === '23000') {
                throw new Exception('El email ya está registrado por otro usuario.');
            }
            throw $e;
        }
    }
}