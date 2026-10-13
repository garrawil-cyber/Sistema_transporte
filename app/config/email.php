<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE EMAIL
 * Lee las credenciales desde el archivo .env
 * ============================================================
 */

// Verificar que Env esté cargado
if (!class_exists('Env')) {
    throw new Exception('La clase Env no está cargada. Asegúrate de incluir app/core/Env.php');
}

define('MAIL_HOST',         Env::get('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT',         (int)Env::get('MAIL_PORT', 587));
define('MAIL_USERNAME',     Env::get('MAIL_USERNAME', ''));
define('MAIL_PASSWORD',     Env::get('MAIL_PASSWORD', ''));
define('MAIL_ENCRYPTION',   Env::get('MAIL_ENCRYPTION', 'tls'));
define('MAIL_FROM_EMAIL',   Env::get('MAIL_FROM_EMAIL', MAIL_USERNAME));
define('MAIL_FROM_NAME',    Env::get('MAIL_FROM_NAME', 'Sistema de Transporte'));
define('MAIL_REPLY_TO',     Env::get('MAIL_REPLY_TO', MAIL_FROM_EMAIL));

// Validación temprana
if (empty(MAIL_USERNAME) || empty(MAIL_PASSWORD)) {
    error_log('⚠️ ADVERTENCIA: Credenciales de email no configuradas en .env');
}