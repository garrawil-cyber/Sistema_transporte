<?php
class Auditoria extends Model {
    protected $table = 'auditoria';

    /**
     * ============================================
     * REGISTRAR UNA ACCIÓN (método estático)
     * ============================================
     */
    public static function registrar($accion, $datos = []) {
        try {
            $db = Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO auditoria 
                    (usuario_id, tabla_afectada, modulo, accion, registro_id, descripcion, 
                     valores_anteriores, valores_nuevos, ip_origen, user_agent, metodo_http, 
                     url_accedida, fecha_hora) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $datos['usuario_id'] ?? ($_SESSION['user_id'] ?? null),
                $datos['tabla_afectada'] ?? null,
                $datos['modulo'] ?? null,
                $accion,
                $datos['registro_id'] ?? null,
                $datos['descripcion'] ?? null,
                isset($datos['valores_anteriores']) ? json_encode($datos['valores_anteriores'], JSON_UNESCAPED_UNICODE) : null,
                isset($datos['valores_nuevos']) ? json_encode($datos['valores_nuevos'], JSON_UNESCAPED_UNICODE) : null,
                self::getIP(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                $_SERVER['REQUEST_METHOD'] ?? 'CLI',
                substr($_SERVER['REQUEST_URI'] ?? 'CLI', 0, 255)
            ]);
            
            return true;
        } catch (Exception $e) {
            Logger::error('Error al registrar auditoría: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registrar acción CRUD
     */
    public static function registrarCRUD($accion, $tabla, $registro_id, $valores_anteriores = null, $valores_nuevos = null, $descripcion = null) {
        return self::registrar($accion, [
            'tabla_afectada' => $tabla,
            'modulo' => self::detectarModulo($tabla),
            'registro_id' => $registro_id,
            'descripcion' => $descripcion ?? self::generarDescripcion($accion, $tabla, $registro_id),
            'valores_anteriores' => $valores_anteriores,
            'valores_nuevos' => $valores_nuevos
        ]);
    }

    /**
     * Registrar login
     */
    public static function registrarLogin($usuario_id, $email = null) {
        $db = Database::getInstance()->getConnection();
        
        self::registrar('LOGIN', [
            'usuario_id' => $usuario_id,
            'modulo' => 'auth',
            'descripcion' => 'Inicio de sesión: ' . ($email ?? 'usuario #' . $usuario_id)
        ]);

        try {
            $sql = "INSERT INTO auditoria_sesiones (usuario_id, session_id, ip_origen, user_agent) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $usuario_id,
                session_id(),
                self::getIP(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ]);
        } catch (Exception $e) {
            Logger::error('Error al registrar sesión: ' . $e->getMessage());
        }
    }

    /**
     * Registrar logout
     */
    public static function registrarLogout($usuario_id, $email = null) {
        $db = Database::getInstance()->getConnection();
        
        self::registrar('LOGOUT', [
            'usuario_id' => $usuario_id,
            'modulo' => 'auth',
            'descripcion' => 'Cierre de sesión: ' . ($email ?? 'usuario #' . $usuario_id)
        ]);

        try {
            $sql = "UPDATE auditoria_sesiones 
                    SET fecha_logout = NOW(), 
                        duracion_minutos = TIMESTAMPDIFF(MINUTE, fecha_login, NOW()),
                        activo = 0
                    WHERE usuario_id = ? AND session_id = ? AND activo = 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$usuario_id, session_id()]);
        } catch (Exception $e) {
            Logger::error('Error al cerrar sesión: ' . $e->getMessage());
        }
    }

    /**
     * ============================================
     * LISTAR CON FILTROS AVANZADOS
     * ============================================
     */
    public function getPaginated($pagina = 1, $por_pagina = 20, $filtros = []) {
        $sql = "SELECT a.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                       u.email as usuario_email
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND a.usuario_id = ?";
            $params[] = $filtros['usuario_id'];
        }
        if (!empty($filtros['accion'])) {
            $sql .= " AND a.accion = ?";
            $params[] = $filtros['accion'];
        }
        if (!empty($filtros['tabla'])) {
            $sql .= " AND a.tabla_afectada = ?";
            $params[] = $filtros['tabla'];
        }
        if (!empty($filtros['modulo'])) {
            $sql .= " AND a.modulo = ?";
            $params[] = $filtros['modulo'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND (a.descripcion LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ? OR a.url_accedida LIKE ?)";
            $buscar = "%{$filtros['buscar']}%";
            $params = array_merge($params, [$buscar, $buscar, $buscar, $buscar]);
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(a.fecha_hora) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(a.fecha_hora) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['ip'])) {
            $sql .= " AND a.ip_origen = ?";
            $params[] = $filtros['ip'];
        }
        
        $sql .= " ORDER BY a.fecha_hora DESC";
        
        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }

    /**
     * ============================================
     * ESTADÍSTICAS
     * ============================================
     */
    public function getEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN accion = 'INSERT' THEN 1 ELSE 0 END) as inserts,
                    SUM(CASE WHEN accion = 'UPDATE' THEN 1 ELSE 0 END) as updates,
                    SUM(CASE WHEN accion = 'DELETE' THEN 1 ELSE 0 END) as deletes,
                    SUM(CASE WHEN accion = 'LOGIN' THEN 1 ELSE 0 END) as logins,
                    SUM(CASE WHEN accion = 'LOGOUT' THEN 1 ELSE 0 END) as logouts,
                    COUNT(DISTINCT usuario_id) as usuarios_unicos
                FROM auditoria
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha_hora) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha_hora) <= ?";
            $params[] = $fecha_fin;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    /**
     * Actividad por usuario
     */
    public function getActividadPorUsuario($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido) as usuario,
                       COUNT(a.id) as total_acciones,
                       SUM(CASE WHEN a.accion = 'LOGIN' THEN 1 ELSE 0 END) as logins
                FROM auditoria a
                JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(a.fecha_hora) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(a.fecha_hora) <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY u.id ORDER BY total_acciones DESC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Actividad por módulo
     */
    public function getActividadPorModulo($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT modulo, COUNT(*) as total
                FROM auditoria
                WHERE modulo IS NOT NULL";
        
        $params = [];
        
        if (!empty($fecha_inicio)) {
            $sql .= " AND DATE(fecha_hora) >= ?";
            $params[] = $fecha_inicio;
        }
        if (!empty($fecha_fin)) {
            $sql .= " AND DATE(fecha_hora) <= ?";
            $params[] = $fecha_fin;
        }
        
        $sql .= " GROUP BY modulo ORDER BY total DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Actividad por día (últimos 30 días)
     */
    public function getActividadPorDia($dias = 30) {
        $dias = (int)$dias;
        $sql = "SELECT DATE(fecha_hora) as fecha, COUNT(*) as total
                FROM auditoria
                WHERE fecha_hora >= DATE_SUB(CURDATE(), INTERVAL $dias DAY)
                GROUP BY DATE(fecha_hora)
                ORDER BY fecha ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Sesiones activas
     */
    public function getSesionesActivas() {
        $sql = "SELECT s.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                       u.email, r.nombre as rol_nombre
                FROM auditoria_sesiones s
                JOIN usuarios u ON s.usuario_id = u.id
                JOIN roles r ON u.rol_id = r.id
                WHERE s.activo = 1
                ORDER BY s.fecha_login DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
 * ============================================
 * OBTENER HISTORIAL DE SESIONES DE UN USUARIO
 * ============================================
 */
public function getHistorialSesiones($usuario_id, $limite = 50) {
    $limite = (int)$limite;
    
    $sql = "SELECT s.*, 
                   CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre,
                   u.email
            FROM auditoria_sesiones s
            JOIN usuarios u ON s.usuario_id = u.id
            WHERE s.usuario_id = ?
            ORDER BY s.fecha_login DESC
            LIMIT $limite";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$usuario_id]);
    return $stmt->fetchAll();
}

    /**
     * Exportar
     */
    public function exportarExcel($filtros = []) {
        $sql = "SELECT a.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM auditoria a
                LEFT JOIN usuarios u ON a.usuario_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(a.fecha_hora) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(a.fecha_hora) <= ?";
            $params[] = $filtros['fecha_fin'];
        }
        if (!empty($filtros['accion'])) {
            $sql .= " AND a.accion = ?";
            $params[] = $filtros['accion'];
        }
        
        $sql .= " ORDER BY a.fecha_hora DESC LIMIT 5000";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Limpiar registros antiguos
     */
    public function limpiarAntiguos($dias = 90) {
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));
        
        $sql = "DELETE FROM auditoria WHERE fecha_hora < ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaLimite]);
        
        return $stmt->rowCount();
    }

    /**
     * ============================================
     * HELPERS
     * ============================================
     */
    private static function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private static function detectarModulo($tabla) {
        $modulos = [
            'usuarios' => 'usuarios',
            'clientes' => 'clientes',
            'rutas' => 'rutas',
            'buses' => 'buses',
            'viajes' => 'viajes',
            'ventas_pasajes' => 'ventas',
            'encomiendas' => 'encomiendas',
            'giros' => 'giros',
            'manifiestos' => 'manifiesto',
            'cajas' => 'caja',
            'combustible_cargas' => 'combustible',
            'mantenimientos' => 'mantenimiento',
            'conductores' => 'conductores',
            'comprobantes_electronicos' => 'facturacion',
            'backups' => 'backup',
            'auditoria' => 'auditoria'
        ];
        return $modulos[$tabla] ?? 'otros';
    }

    private static function generarDescripcion($accion, $tabla, $registro_id) {
        $acciones = [
            'INSERT' => 'Creó',
            'UPDATE' => 'Actualizó',
            'DELETE' => 'Eliminó'
        ];
        $verbo = $acciones[$accion] ?? $accion;
        return "$verbo un registro en $tabla (ID: $registro_id)";
    }


}