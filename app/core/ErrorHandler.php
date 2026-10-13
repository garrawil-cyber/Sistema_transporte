<?php
/**
 * Clase ErrorHandler - Manejo centralizado de errores
 * 
 * Captura:
 * - Excepciones no capturadas
 * - Errores de PHP (warnings, notices, fatales)
 * - Errores fatales al finalizar el script
 * 
 * Registra en Logger y muestra páginas de error personalizadas.
 */
class ErrorHandler {
    
    /**
     * Inicializar el manejador de errores
     */
    public static function init() {
        // 1. Capturar todas las excepciones
        set_exception_handler([self::class, 'handleException']);
        
        // 2. Capturar errores de PHP
        set_error_handler([self::class, 'handleError']);
        
        // 3. Capturar errores fatales
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // 4. Configurar reporte de errores según el modo
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            ini_set('display_errors', 0);
        }
        
        // 5. Establecer zona horaria
        date_default_timezone_set('America/Lima');
    }
    
    /**
     * Manejar excepciones no capturadas
     */
    public static function handleException($exception) {
        $codigo = $exception->getCode();
        $mensaje = $exception->getMessage();
        $archivo = $exception->getFile();
        $linea = $exception->getLine();
        $traza = $exception->getTraceAsString();
        
        // Registrar en log
        Logger::error("Excepción no capturada: {$mensaje}", [
            'archivo' => $archivo,
            'linea' => $linea,
            'codigo' => $codigo,
            'traza' => $traza,
            'tipo' => get_class($exception)
        ]);
        
        // Determinar código HTTP
        $httpCode = 500;
        if ($exception instanceof PDOException) {
            $httpCode = 500;
        }
        
        // Mostrar error
        self::mostrarError($httpCode, $mensaje, $archivo, $linea, $traza);
    }
    
    /**
     * Manejar errores de PHP
     */
    public static function handleError($errno, $errstr, $errfile = '', $errline = 0) {
        // No procesar si el error está suprimido con @
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        // Mapear nivel de error
        $niveles = [
            E_ERROR             => 'ERROR FATAL',
            E_WARNING           => 'WARNING',
            E_PARSE             => 'PARSE ERROR',
            E_NOTICE            => 'NOTICE',
            E_CORE_ERROR        => 'CORE ERROR',
            E_CORE_WARNING      => 'CORE WARNING',
            E_COMPILE_ERROR     => 'COMPILE ERROR',
            E_COMPILE_WARNING   => 'COMPILE WARNING',
            E_USER_ERROR        => 'USER ERROR',
            E_USER_WARNING      => 'USER WARNING',
            E_USER_NOTICE       => 'USER NOTICE',
            E_STRICT            => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED        => 'DEPRECATED',
            E_USER_DEPRECATED   => 'USER DEPRECATED',
        ];
        
        $nivel = $niveles[$errno] ?? 'ERROR DESCONOCIDO';
        $mensaje = "[{$nivel}] {$errstr}";
        
        // Registrar en log
        if (in_array($errno, [E_ERROR, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            Logger::error($mensaje, ['archivo' => $errfile, 'linea' => $errline]);
        } elseif (in_array($errno, [E_WARNING, E_USER_WARNING])) {
            Logger::warning($mensaje, ['archivo' => $errfile, 'linea' => $errline]);
        } else {
            Logger::debug($mensaje, ['archivo' => $errfile, 'linea' => $errline]);
        }
        
        // No detener la ejecución del script para errores no fatales
        return true;
    }
    
    /**
     * Capturar errores fatales al finalizar el script
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $mensaje = $error['message'];
            $archivo = $error['file'];
            $linea = $error['line'];
            
            Logger::error("Error fatal: {$mensaje}", [
                'archivo' => $archivo,
                'linea' => $linea,
                'tipo' => 'FATAL'
            ]);
            
            // Mostrar página de error (si no se ha mostrado ya)
            if (!headers_sent()) {
                self::mostrarError(500, $mensaje, $archivo, $linea);
            }
        }
    }
    
    /**
     * Mostrar página de error personalizada
     */
    public static function mostrarError($httpCode, $mensaje, $archivo = '', $linea = 0, $traza = '') {
        // Limpiar cualquier buffer
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        
        // Establecer código HTTP
        if (!headers_sent()) {
            http_response_code($httpCode);
        }
        
        // Verificar si es petición AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => true,
                'message' => (defined('APP_DEBUG') && APP_DEBUG) ? $mensaje : 'Ha ocurrido un error',
                'code' => $httpCode
            ]);
            exit;
        }
        
        // Preparar datos para la vista
        $error_data = [
            'httpCode' => $httpCode,
            'mensaje' => $mensaje,
            'archivo' => $archivo,
            'linea' => $linea,
            'traza' => $traza,
            'debug' => defined('APP_DEBUG') && APP_DEBUG
        ];
        
        // Cargar vista de error
        $vista = __DIR__ . '/../views/errors/' . $httpCode . '.php';
        if (!file_exists($vista)) {
            $vista = __DIR__ . '/../views/errors/error.php';
        }
        
        extract($error_data);
        
        if (file_exists($vista)) {
            include $vista;
        } else {
            // Fallback si no existe la vista
            echo "<h1>Error {$httpCode}</h1>";
            echo "<p>" . (defined('APP_DEBUG') && APP_DEBUG ? htmlspecialchars($mensaje) : 'Ha ocurrido un error') . "</p>";
        }
        
        exit;
    }
    
    /**
     * Lanzar un error 404
     */
    public static function error404($mensaje = 'Página no encontrada') {
        self::mostrarError(404, $mensaje);
    }
    
    /**
     * Lanzar un error 403
     */
    public static function error403($mensaje = 'Acceso denegado') {
        self::mostrarError(403, $mensaje);
    }
    
    /**
     * Lanzar un error 500
     */
    public static function error500($mensaje = 'Error interno del servidor') {
        self::mostrarError(500, $mensaje);
    }
}