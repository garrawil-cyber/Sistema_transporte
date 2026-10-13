<?php
/**
 * ============================================================
 * SCRIPT DE BACKUP AUTOMÁTICO
 * ============================================================
 * 
 * Ejecutar:
 * - Manualmente: php scripts/backup_cron.php
 * - Programado (Windows Task Scheduler / Linux Cron)
 * 
 * Windows:
 *   schtasks /create /tn "BackupTransporte" /tr "C:\wamp64\bin\php\php8.x.x\php.exe C:\wamp64\www\Sistema_transporte\scripts\backup_cron.php" /sc daily /st 23:00
 * 
 * Linux:
 *   0 23 * * * /usr/bin/php /var/www/Sistema_transporte/scripts/backup_cron.php
 * ============================================================
 */

// ============================================================
// CARGA DE DEPENDENCIAS (en orden estricto)
// ============================================================
// 1. Configuración (define constantes DB_HOST, etc.)
require_once __DIR__ . '/../app/config/config.php';

// 2. Conexión a BD (usa las constantes)
require_once __DIR__ . '/../app/config/Database.php';

// 3. Clases core
require_once __DIR__ . '/../app/core/Logger.php';
require_once __DIR__ . '/../app/core/Model.php';

// 4. Modelo Backup (extiende Model)
require_once __DIR__ . '/../app/models/Backup.php';

// ============================================================
// RUTA DEL LOG
// ============================================================
$logFile = __DIR__ . '/../backups/cron_log.txt';

if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0777, true);
}

$fecha = date('Y-m-d H:i:s');
file_put_contents($logFile, "[{$fecha}] Iniciando backup automático\n", FILE_APPEND);

// ============================================================
// EJECUCIÓN
// ============================================================
try {
    $backup = new Backup();
    $resultado = $backup->crear('automatico', null);

    $log = "[{$fecha}] " . ($resultado['success'] ? '✅ ÉXITO' : '❌ ERROR') .
           ": " . $resultado['message'] . "\n";

    if ($resultado['success']) {
        $log .= "  Archivo: " . $resultado['archivo'] . "\n";
        $log .= "  Tamaño: " . $resultado['tamano'] . "\n";
    }

    file_put_contents($logFile, $log, FILE_APPEND);

    // Limpiar backups automáticos con más de 30 días
    $eliminados = $backup->limpiarAntiguos(30);
    if ($eliminados > 0) {
        file_put_contents(
            $logFile,
            "  Limpieza: {$eliminados} backups antiguos eliminados\n",
            FILE_APPEND
        );
    }

    echo $log;
    exit($resultado['success'] ? 0 : 1);

} catch (Exception $e) {
    $log = "[{$fecha}] ❌ ERROR FATAL: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $log, FILE_APPEND);
    echo $log;
    exit(1);
}