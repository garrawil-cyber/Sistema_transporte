<?php
class Backup extends Model {
    protected $table = 'backups';
    private $backupDir;
    private $dbName;
    private $dbHost;
    private $dbUser;
    private $dbPass;

    public function __construct() {
        parent::__construct();
        $this->backupDir = dirname(__DIR__, 2) . '/backups';
        $this->dbName = DB_NAME;
        $this->dbHost = DB_HOST;
        $this->dbUser = DB_USER;
        $this->dbPass = DB_PASS;

        // Asegurar que existan las carpetas
        if (!is_dir($this->backupDir . '/manual')) {
            mkdir($this->backupDir . '/manual', 0777, true);
        }
        if (!is_dir($this->backupDir . '/automatico')) {
            mkdir($this->backupDir . '/automatico', 0777, true);
        }
    }

    /**
     * ============================================
     * CREAR BACKUP COMPLETO
     * ============================================
     * ✅ FIX: maneja el caso de cron sin sesión (usuario_id NULL).
     */
    public function crear($tipo = 'manual', $usuario_id = null) {
        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "backup_{$tipo}_{$fecha}.sql";
        $rutaCompleta = $this->backupDir . '/' . $tipo . '/' . $nombreArchivo;

        try {
            // Detectar la ruta de mysqldump
            $mysqldump = $this->encontrarMysqldump();

            if (!$mysqldump) {
                return ['success' => false, 'message' => 'No se encontró mysqldump.exe'];
            }

            // Construir comando
            $comando = sprintf(
                '"%s" --host=%s --user=%s %s --routines --triggers --events --single-transaction --default-character-set=utf8mb4 %s > "%s"',
                $mysqldump,
                $this->dbHost,
                $this->dbUser,
                empty($this->dbPass) ? '' : '--password=' . escapeshellarg($this->dbPass),
                escapeshellarg($this->dbName),
                $rutaCompleta
            );

            // Ejecutar
            exec($comando . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($rutaCompleta)) {
                return [
                    'success' => false,
                    'message' => 'Error al crear el backup',
                    'output' => implode("\n", $output)
                ];
            }

            // Verificar que el archivo tenga contenido
            $tamano = filesize($rutaCompleta);
            if ($tamano < 100) {
                unlink($rutaCompleta);
                return ['success' => false, 'message' => 'El backup generado está vacío o es inválido'];
            }

            // ✅ FIX: resolver usuario_id de forma segura (cron no tiene sesión)
            $usuario_id_final = $usuario_id;
            if ($usuario_id_final === null && isset($_SESSION['user_id'])) {
                $usuario_id_final = (int)$_SESSION['user_id'];
            }

            // Registrar en la BD
            $info = [
                'nombre_archivo' => $nombreArchivo,
                'tipo'           => $tipo,
                'tamano_bytes'   => $tamano,
                'ruta'           => $tipo . '/' . $nombreArchivo,
                'usuario_id'     => $usuario_id_final,
                'estado'         => 'COMPLETADO',
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'observaciones'  => null
            ];

            $this->create($info);

            Logger::info('Backup creado', [
                'archivo' => $nombreArchivo,
                'tipo'    => $tipo,
                'tamano'  => $tamano
            ]);

            return [
                'success' => true,
                'message' => 'Backup creado correctamente',
                'archivo' => $nombreArchivo,
                'tamano'  => $this->formatearTamano($tamano),
                'ruta'    => $rutaCompleta
            ];

        } catch (Exception $e) {
            Logger::error('Error al crear backup', [
                'tipo'  => $tipo,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ============================================
     * DETECTAR RUTA DE MYSQLDUMP
     * ============================================
     */
    private function encontrarMysqldump() {
        $rutasPosibles = [
            ...glob('C:/wamp64/bin/mysql/mysql*/bin/mysqldump.exe'),
            ...glob('C:/wamp/bin/mysql/mysql*/bin/mysqldump.exe'),
            'C:/xampp/mysql/bin/mysqldump.exe',
            ...glob('C:/laragon/bin/mysql/mysql*/bin/mysqldump.exe'),
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        foreach ($rutasPosibles as $ruta) {
            if (file_exists($ruta)) {
                return $ruta;
            }
        }

        // Último recurso: buscar en PATH
        $command = (PHP_OS_FAMILY === 'Windows') ? 'where mysqldump' : 'which mysqldump';
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        return null;
    }

    /**
     * ============================================
     * LISTAR BACKUPS
     * ============================================
     */
    public function listar($tipo = null) {
        $sql = "SELECT b.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM backups b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if ($tipo) {
            $sql .= " AND b.tipo = ?";
            $params[] = $tipo;
        }

        $sql .= " ORDER BY b.fecha_creacion DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * ============================================
     * ELIMINAR BACKUP
     * ============================================
     */
    public function eliminar($id) {
        $backup = $this->find($id);
        if (!$backup) {
            return ['success' => false, 'message' => 'Backup no encontrado'];
        }

        $rutaCompleta = $this->backupDir . '/' . $backup['ruta'];

        try {
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }

            $this->delete($id);

            Logger::info('Backup eliminado', ['id' => $id, 'archivo' => $backup['nombre_archivo']]);

            return ['success' => true, 'message' => 'Backup eliminado correctamente'];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    /**
     * ============================================
     * DESCARGAR BACKUP
     * ============================================
     */
    public function descargar($id) {
        $backup = $this->find($id);
        if (!$backup) {
            return false;
        }

        $rutaCompleta = $this->backupDir . '/' . $backup['ruta'];

        if (!file_exists($rutaCompleta)) {
            return false;
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $backup['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($rutaCompleta));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($rutaCompleta);
        exit;
    }

    /**
     * ============================================
     * RESTAURAR BACKUP
     * ============================================
     */
    public function restaurar($id) {
        $backup = $this->find($id);
        if (!$backup) {
            return ['success' => false, 'message' => 'Backup no encontrado'];
        }

        $rutaCompleta = $this->backupDir . '/' . $backup['ruta'];

        if (!file_exists($rutaCompleta)) {
            return ['success' => false, 'message' => 'Archivo de backup no encontrado'];
        }

        try {
            $mysql = $this->encontrarMysql();

            if (!$mysql) {
                return ['success' => false, 'message' => 'No se encontró mysql.exe'];
            }

            // Crear backup de seguridad antes de restaurar
            $this->crear('automatico', $_SESSION['user_id'] ?? null);

            $comando = sprintf(
                '"%s" --host=%s --user=%s %s %s < "%s"',
                $mysql,
                $this->dbHost,
                $this->dbUser,
                empty($this->dbPass) ? '' : '--password=' . escapeshellarg($this->dbPass),
                escapeshellarg($this->dbName),
                $rutaCompleta
            );

            exec($comando . ' 2>&1', $output, $returnCode);

            if ($returnCode !== 0) {
                return [
                    'success' => false,
                    'message' => 'Error al restaurar',
                    'output' => implode("\n", $output)
                ];
            }

            Logger::info('Backup restaurado', ['id' => $id, 'archivo' => $backup['nombre_archivo']]);

            return [
                'success' => true,
                'message' => 'Base de datos restaurada correctamente. Se creó un backup de seguridad previo.'
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Encontrar mysql.exe
     */
    private function encontrarMysql() {
        $rutasPosibles = [
            ...glob('C:/wamp64/bin/mysql/mysql*/bin/mysql.exe'),
            ...glob('C:/wamp/bin/mysql/mysql*/bin/mysql.exe'),
            'C:/xampp/mysql/bin/mysql.exe',
            ...glob('C:/laragon/bin/mysql/mysql*/bin/mysql.exe'),
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
        ];

        foreach ($rutasPosibles as $ruta) {
            if (file_exists($ruta)) return $ruta;
        }

        return null;
    }

    /**
     * Formatear tamaño
     */
    public function formatearTamano($bytes) {
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * Limpiar backups antiguos (más de N días)
     */
    public function limpiarAntiguos($dias = 30) {
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$dias} days"));

        $sql = "SELECT * FROM backups WHERE fecha_creacion < ? AND tipo = 'automatico'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$fechaLimite]);
        $antiguos = $stmt->fetchAll();

        $eliminados = 0;
        foreach ($antiguos as $b) {
            $resultado = $this->eliminar($b['id']);
            if ($resultado['success']) $eliminados++;
        }

        return $eliminados;
    }

    /**
     * Estadísticas
     */
    public function getEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN tipo = 'manual' THEN 1 ELSE 0 END) as manuales,
                    SUM(CASE WHEN tipo = 'automatico' THEN 1 ELSE 0 END) as automaticos,
                    COALESCE(SUM(tamano_bytes), 0) as tamano_total,
                    MAX(fecha_creacion) as ultimo_backup
                FROM backups";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Listar backups paginados
     */
    public function getPaginated($pagina = 1, $por_pagina = 15, $filtros = []) {
        $sql = "SELECT b.*, 
                       CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre
                FROM backups b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filtros['tipo'])) {
            $sql .= " AND b.tipo = ?";
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['buscar'])) {
            $sql .= " AND b.nombre_archivo LIKE ?";
            $params[] = "%{$filtros['buscar']}%";
        }
        if (!empty($filtros['fecha_inicio'])) {
            $sql .= " AND DATE(b.fecha_creacion) >= ?";
            $params[] = $filtros['fecha_inicio'];
        }
        if (!empty($filtros['fecha_fin'])) {
            $sql .= " AND DATE(b.fecha_creacion) <= ?";
            $params[] = $filtros['fecha_fin'];
        }

        $sql .= " ORDER BY b.fecha_creacion DESC";

        return $this->paginate($sql, $params, $pagina, $por_pagina);
    }
}