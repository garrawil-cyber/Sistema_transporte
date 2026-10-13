<?php
/**
 * ============================================================
 * Script de liberación de reservas expiradas
 * ============================================================
 * 
 * USO:
 *   - Manual: http://localhost/Sistema_transporte/scripts/liberar_reservas.php
 *   - Automático: Windows Task Scheduler cada 1-5 min
 * ============================================================
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/Database.php';

$es_cli = (php_sapi_name() === 'cli');

try {
    $db = Database::getInstance()->getConnection();
    
    // Ver cuántas reservas hay antes
    $sql_check = "SELECT COUNT(*) as total FROM asientos WHERE estado = 'RESERVADO'";
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->execute();
    $antes = $stmt_check->fetch();
    
    // Liberar reservas expiradas
    $sql = "UPDATE asientos 
            SET estado = 'DISPONIBLE', 
                reservado_hasta = NULL 
            WHERE estado = 'RESERVADO' 
            AND reservado_hasta < NOW()";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $liberados = $stmt->rowCount();
    
    $fecha = date('Y-m-d H:i:s');
    $mensaje = "[{$fecha}] Liberados: {$liberados} asientos";
    
    // Salida
    if ($es_cli) {
        echo $mensaje . "\n";
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liberar Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 500px;">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-clock me-2"></i>Liberar Reservas Expiradas
            </div>
            <div class="card-body text-center py-4">
                <i class="fas fa-' . ($liberados > 0 ? 'check-circle text-success' : 'info-circle text-info') . ' fa-4x mb-3"></i>
                <h4 class="mb-3">' . ($liberados > 0 ? '¡Reservas liberadas!' : 'Sin reservas expiradas') . '</h4>
                <p class="text-muted mb-4">
                    <strong>' . $liberados . '</strong> asiento' . ($liberados !== 1 ? 's' : '') . ' liberado' . ($liberados !== 1 ? 's' : '') . '
                </p>
                <div class="alert alert-secondary small mb-3">
                    <i class="fas fa-clock me-1"></i>
                    Ejecutado: <strong>' . $fecha . '</strong>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <a href="" class="btn btn-primary btn-sm">
                        <i class="fas fa-redo me-1"></i>Ejecutar de nuevo
                    </a>
                    <a href="' . BASE_URL . 'ventas/create?viaje_id=4" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Ir a Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>';
    }
    
    if (class_exists('Logger')) {
        Logger::info($mensaje);
    }
    
} catch (Exception $e) {
    $error = "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage();
    error_log($error);
    
    if ($es_cli) {
        echo $error . "\n";
    } else {
        echo '<pre>' . htmlspecialchars($error) . '</pre>';
    }
    
    exit(1);
}
?>