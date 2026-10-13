<?php
/**
 * ============================================================
 * FRONT CONTROLLER - Sistema de Transporte
 * ============================================================
 * Punto de entrada único para todas las peticiones del sistema.
 * 
 * Orden de carga:
 * 1. Autoload de Composer (Dompdf, PHPMailer, etc.)
 * 2. Configuración (constantes globales)
 * 3. Conexión a base de datos
 * 4. Clases core (por orden de dependencias)
 * 5. Autoload personalizado (controllers, models, core)
 * 6. Inicialización del Router
 * ============================================================
 */

// ============================================================
// 1. INICIAR SESIÓN
// ============================================================
session_start();

// Cargar el manejo de errores lo antes posible
require_once __DIR__ . '/../app/core/Logger.php';
require_once __DIR__ . '/../app/core/ErrorHandler.php';

// ============================================================
// CARGAR VARIABLES DE ENTORNO
// ============================================================
require_once __DIR__ . '/../app/core/Env.php';

try {
    Env::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    // Fallback: si no existe .env, usar valores por defecto
    error_log("No se pudo cargar .env: " . $e->getMessage());
}


// Inicializar el manejador de errores
ErrorHandler::init();

// ============================================================
// 2. AUTOLOAD DE COMPOSER (Dompdf, PHPMailer, Endroid QR, etc.)
// ============================================================
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// ============================================================
// 3. CONFIGURACIÓN GLOBAL
// ============================================================
require_once __DIR__ . '/../app/config/config.php';

// ============================================================
// 4. CONEXIÓN A BASE DE DATOS
// ============================================================
require_once __DIR__ . '/../app/config/Database.php';

// ============================================================
// 5. CLASES CORE (en orden de dependencias)
// ============================================================
require_once __DIR__ . '/../app/core/Paginator.php';    // Sin dependencias
require_once __DIR__ . '/../app/core/PdfExporter.php';  // Dompdf + config
require_once __DIR__ . '/../app/core/Controller.php';   // Sin dependencias
require_once __DIR__ . '/../app/core/Model.php';        // Usa Database
require_once __DIR__ . '/../app/core/Router.php';       // Usa Controller

// ============================================================
// 6. AUTOLOAD PERSONALIZADO CON MAPA DE CLASES
// ============================================================
/**
 * Mapa de clases para carga rápida
 * Formato: 'NombreClase' => 'ruta/relativa/al/archivo.php'
 * 
 * Al agregar nuevas clases, añadir al mapa correspondiente.
 */
$classMap = [
    // ============================================
    // CORE (clases base del sistema)
    // ============================================
    'Controller'      => __DIR__ . '/../app/core/Controller.php',
    'Model'           => __DIR__ . '/../app/core/Model.php',
    'Router'          => __DIR__ . '/../app/core/Router.php',
    'Paginator'       => __DIR__ . '/../app/core/Paginator.php',
    'PdfExporter'     => __DIR__ . '/../app/core/PdfExporter.php',
    'Database'        => __DIR__ . '/../app/config/Database.php',

    // ============================================
    // CONTROLLERS
    // ============================================
    'HomeController'         => __DIR__ . '/../app/controllers/HomeController.php',
    'AuthController'         => __DIR__ . '/../app/controllers/AuthController.php',
    'UsuarioController'      => __DIR__ . '/../app/controllers/UsuariosController.php',
    'RutaController'         => __DIR__ . '/../app/controllers/RutasController.php',
    'BusController'          => __DIR__ . '/../app/controllers/BusesController.php',
    'ClienteController'      => __DIR__ . '/../app/controllers/ClientesController.php',
    'ViajeController'        => __DIR__ . '/../app/controllers/ViajesController.php',
    'VentasController'        => __DIR__ . '/../app/controllers/VentasController.php',
    'EncomiendaController'   => __DIR__ . '/../app/controllers/EncomiendasController.php',
    'GiroController'         => __DIR__ . '/../app/controllers/GirosController.php',
    'ManifiestoController'   => __DIR__ . '/../app/controllers/ManifiestoController.php',
    'CajaController'         => __DIR__ . '/../app/controllers/CajaController.php',
    'ReporteController'      => __DIR__ . '/../app/controllers/ReporteController.php',
    'FavoritoController'     => __DIR__ . '/../app/controllers/FavoritoController.php',
    'ValidarController'      => __DIR__ . '/../app/controllers/ValidarController.php',
    'FacturaController'      => __DIR__ . '/../app/controllers/FacturaController.php',

    'CombustibleController' => __DIR__ . '/../app/controllers/CombustibleController.php',
    'MantenimientoController' => __DIR__ . '/../app/controllers/MantenimientoController.php',
    'ConductoresController' => __DIR__ . '/../app/controllers/ConductoresController.php',
    'BackupController' => __DIR__ . '/../app/controllers/BackupController.php',
    'AuditoriaController' => __DIR__ . '/../app/controllers/AuditoriaController.php',
    'RastreoController' => __DIR__ . '/../app/controllers/RastreoController.php',

    // ============================================
    // MODELS
    // ============================================
    'Usuario'         => __DIR__ . '/../app/models/Usuario.php',
    'Rol'             => __DIR__ . '/../app/models/Rol.php',
    'Ruta'            => __DIR__ . '/../app/models/Ruta.php',
    'Bus'             => __DIR__ . '/../app/models/Bus.php',
    'Cliente'         => __DIR__ . '/../app/models/Cliente.php',
    'Viaje'           => __DIR__ . '/../app/models/Viaje.php',
    'Venta'           => __DIR__ . '/../app/models/Venta.php',
    'Encomienda'      => __DIR__ . '/../app/models/Encomienda.php',
    'Giro'            => __DIR__ . '/../app/models/Giro.php',
    'Manifiesto'      => __DIR__ . '/../app/models/Manifiesto.php',
    'Caja'            => __DIR__ . '/../app/models/Caja.php',
    'Reporte'         => __DIR__ . '/../app/models/Reporte.php',
    'Dashboard'       => __DIR__ . '/../app/models/Dashboard.php',
    'Favorito'        => __DIR__ . '/../app/models/Favorito.php',
    'FacturaElectronica' => __DIR__ . '/../app/models/FacturaElectronica.php',

    'Combustible' => __DIR__ . '/../app/models/Combustible.php',
    'Mantenimiento' => __DIR__ . '/../app/models/Mantenimiento.php',
    'Conductor' => __DIR__ . '/../app/models/Conductor.php',
    'Backup' => __DIR__ . '/../app/models/Backup.php',
    'Auditoria' => __DIR__ . '/../app/models/Auditoria.php',
    'ComprobanteEmail' => __DIR__ . '/../app/models/ComprobanteEmail.php',

];

/**
 * Autoloader basado en mapa de clases
 * Mucho más rápido que escanear directorios.
 */
spl_autoload_register(function ($class) use ($classMap) {
    // 1. Buscar en el mapa de clases (rápido)
    if (isset($classMap[$class])) {
        if (file_exists($classMap[$class])) {
            require_once $classMap[$class];
            return true;
        }
    }

    // 2. Fallback: buscar en directorios (lento, pero útil para clases nuevas)
    $paths = [
        __DIR__ . '/../app/controllers/',
        __DIR__ . '/../app/models/',
        __DIR__ . '/../app/core/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }

    return false;
});

// ============================================================
// 7. INICIALIZAR LA APLICACIÓN
// ============================================================
try {
    $router = new Router();
} catch (Exception $e) {
    // Manejo de errores centralizado
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo '<pre>';
        echo 'Error: ' . $e->getMessage() . "\n";
        echo 'Archivo: ' . $e->getFile() . ':' . $e->getLine() . "\n";
        echo '</pre>';
    } else {
        //echo 'Ha ocurrido un error. Contacte al administrador.';
        echo 'Ha ocurrido un error. Por favor, inténtelo más tarde.' . "\n";
    }
    exit;
}