<?php
// ============================================================
// CONFIGURACIÓN GENERAL DEL SISTEMA
// ============================================================

// Modo depuración (cambiar a false en producción)
define('APP_DEBUG', true);

// URL base
define('BASE_URL', 'http://localhost/Sistema_transporte/public/');
define('APP_URL', 'http://localhost/Sistema_transporte/app/');

// Base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_transporte');
define('DB_USER', 'root');
define('DB_PASS', '');

// Controlador y método por defecto
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_METHOD', 'index');

// Manifiesto
define('MANIFIESTO_SECRET', '16631277');

// ============================================================
// UPLOADS
// ============================================================
define('UPLOAD_PATH', __DIR__ . '/../../public/uploads/');
define('UPLOAD_URL',  BASE_URL . 'uploads/');

// Zona horaria
date_default_timezone_set('America/Lima');

// ============================================================
// FACTURACIÓN ELECTRÓNICA
// Todas las constantes FACTURACION_* están en facturacion.php
// ============================================================
require_once __DIR__ . '/facturacion.php';
// ============================================================
// EMAIL
// ============================================================
require_once __DIR__ . '/email.php';