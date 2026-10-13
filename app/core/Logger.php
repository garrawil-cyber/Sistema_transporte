<?php
/**
 * Clase Logger - Sistema de registro de logs
 * 
 * Registra eventos en archivos organizados por fecha.
 * 
 * Uso:
 *   Logger::error('Mensaje de error');
 *   Logger::info('Información');
 *   Logger::warning('Advertencia');
 *   Logger::debug('Mensaje de depuración');
 */
class Logger {
    
    // Niveles de log
    const ERROR   = 'ERROR';
    const WARNING = 'WARNING';
    const INFO    = 'INFO';
    const DEBUG   = 'DEBUG';
    
    // Directorio de logs
    private static $logDir = null;
    
    /**
     * Obtener el directorio de logs (lo crea si no existe)
     */
    private static function getLogDir() {
        if (self::$logDir === null) {
            self::$logDir = dirname(__DIR__, 2) . '/logs';
        }
        
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
        
        return self::$logDir;
    }
    
    /**
     * Registrar un mensaje en el log
     * 
     * @param string $nivel Nivel del log (ERROR, WARNING, INFO, DEBUG)
     * @param string $mensaje Mensaje a registrar
     * @param array $contexto Datos adicionales
     */
    public static function log($nivel, $mensaje, $contexto = []) {
        // No registrar DEBUG si no estamos en modo debug
        if ($nivel === self::DEBUG && !(defined('APP_DEBUG') && APP_DEBUG)) {
            return;
        }
        
        $fecha = date('Y-m-d');
        $archivo = self::getLogDir() . "/{$nivel}-{$fecha}.log";
        
        // Construir línea de log
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getIP();
        $usuario = self::getUsuario();
        $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
        
        $linea = "[{$timestamp}] [{$nivel}] [IP: {$ip}] [User: {$usuario}] [URL: {$url}]\n";
        $linea .= "Mensaje: {$mensaje}\n";
        
        if (!empty($contexto)) {
            $linea .= "Contexto: " . json_encode($contexto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
        
        $linea .= str_repeat('-', 80) . "\n";
        
        // Escribir al archivo (con bloqueo para evitar conflictos)
        file_put_contents($archivo, $linea, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Registrar un error
     */
    public static function error($mensaje, $contexto = []) {
        self::log(self::ERROR, $mensaje, $contexto);
    }
    
    /**
     * Registrar una advertencia
     */
    public static function warning($mensaje, $contexto = []) {
        self::log(self::WARNING, $mensaje, $contexto);
    }
    
    /**
     * Registrar información
     */
    public static function info($mensaje, $contexto = []) {
        self::log(self::INFO, $mensaje, $contexto);
    }
    
    /**
     * Registrar un mensaje de depuración
     */
    public static function debug($mensaje, $contexto = []) {
        self::log(self::DEBUG, $mensaje, $contexto);
    }
    
    /**
     * Obtener la IP del cliente
     */
    private static function getIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Obtener el usuario actual
     */
    private static function getUsuario() {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'] . ' (' . ($_SESSION['email'] ?? 'sin email') . ')';
        }
        return 'Anónimo';
    }
    
    /**
     * Limpiar logs antiguos (más de N días)
     */
    public static function limpiarAntiguos($dias = 30) {
        $dir = self::getLogDir();
        $archivos = glob($dir . '/*.log');
        $limite = strtotime("-{$dias} days");
        
        $eliminados = 0;
        foreach ($archivos as $archivo) {
            if (filemtime($archivo) < $limite) {
                unlink($archivo);
                $eliminados++;
            }
        }
        
        return $eliminados;
    }
    
    /**
     * Obtener todos los logs de un tipo
     */
    public static function obtenerLogs($nivel = 'ERROR', $fecha = null) {
        $fecha = $fecha ?? date('Y-m-d');
        $archivo = self::getLogDir() . "/{$nivel}-{$fecha}.log";
        
        if (!file_exists($archivo)) {
            return [];
        }
        
        $contenido = file_get_contents($archivo);
        $bloques = array_filter(explode(str_repeat('-', 80), $contenido));
        
        $logs = [];
        foreach ($bloques as $bloque) {
            $bloque = trim($bloque);
            if (empty($bloque)) continue;
            
            $logs[] = $bloque;
        }
        
        return array_reverse($logs); // Los más recientes primero
    }
}