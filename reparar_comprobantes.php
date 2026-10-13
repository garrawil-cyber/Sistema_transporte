<?php
/**
 * Script de reparación: Emitir comprobantes electrónicos
 * para las ventas huérfanas (sin comprobante en comprobantes_electronicos).
 * 
 * ⚠️ ELIMINAR después de usar.
 */

// Bootstrap mínimo
require_once __DIR__ . '/app/core/Env.php';
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/config/Database.php';

require_once __DIR__ . '/app/core/Logger.php';
require_once __DIR__ . '/app/core/Model.php';
require_once __DIR__ . '/app/config/facturacion.php';

// Autoload de Composer (Greenter)
$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Cargar modelos necesarios
require_once __DIR__ . '/app/models/FacturaElectronica.php';

// Iniciar sesión (FacturaElectronica usa $_SESSION['user_id'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['user_id'] = $_SESSION['user_id'] ?? 1; // admin por defecto

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reparación de Comprobantes</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; line-height: 1.6; }
        h1 { color: #4ec9b0; border-bottom: 2px solid #4ec9b0; padding-bottom: 10px; }
        h2 { color: #569cd6; margin-top: 30px; }
        .ok { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        .venta { background: #252526; padding: 10px; margin: 5px 0; border-left: 4px solid #569cd6; }
        .venta-ok { border-left-color: #4ec9b0; }
        .venta-error { border-left-color: #f48771; }
        .resumen { background: #2d2d30; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .resumen-item { display: inline-block; margin-right: 20px; }
        .resumen-valor { font-size: 1.5em; font-weight: bold; }
        a.btn { display: inline-block; padding: 10px 20px; background: #0e639c; color: white; 
                text-decoration: none; border-radius: 4px; margin-top: 20px; }
        a.btn:hover { background: #1177bb; }
    </style>
</head>
<body>

<h1>🔧 Reparación de Comprobantes Electrónicos</h1>

<?php

// ============================================================
// 1. Verificar modo simulación
// ============================================================
$modo_simulacion = defined('FACTURACION_MODO_SIMULACION') && FACTURACION_MODO_SIMULACION;
?>
<p class="info">
    Modo facturación: 
    <?php if ($modo_simulacion): ?>
        <strong class="warning">🧪 SIMULACIÓN</strong> (no se envía a SUNAT)
    <?php else: ?>
        <strong class="error">⚠️ PRODUCCIÓN</strong> (se envía a SUNAT de verdad)
    <?php endif; ?>
</p>

<?php

// ============================================================
// 2. Obtener ventas huérfanas
// ============================================================
try {
    $db = Database::getInstance()->getConnection();

    $sql = "SELECT vp.id AS venta_id, vp.numero_comprobante, vp.fecha_venta,
                   vp.precio_pagado, c.nombre AS cliente, c.email
            FROM ventas_pasajes vp
            JOIN clientes c ON vp.cliente_id = c.id
            LEFT JOIN comprobantes_electronicos ce ON ce.venta_id = vp.id
            WHERE ce.id IS NULL AND vp.estado = 'ACTIVA'
            ORDER BY vp.id ASC";

    $ventas_huerfanas = $db->query($sql)->fetchAll();

} catch (Exception $e) {
    echo "<h2 class='error'>💥 Error al consultar la BD</h2>";
    echo "<p class='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// ============================================================
// 3. Mostrar resumen
// ============================================================
?>
<div class="resumen">
    <div class="resumen-item">
        <div class="info">Total huérfanas:</div>
        <div class="resumen-valor"><?= count($ventas_huerfanas) ?></div>
    </div>
    <?php if (!empty($ventas_huerfanas)): ?>
    <div class="resumen-item">
        <div class="info">Primera:</div>
        <div class="resumen-valor">#<?= $ventas_huerfanas[0]['venta_id'] ?></div>
    </div>
    <div class="resumen-item">
        <div class="info">Última:</div>
        <div class="resumen-valor">#<?= end($ventas_huerfanas)['venta_id'] ?></div>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($ventas_huerfanas)): ?>
    <h2 class="ok">✅ No hay ventas huérfanas. Todo está consistente.</h2>
    <a href="<?= BASE_URL ?>ventas" class="btn">Volver a Ventas</a>
    </body></html>
    <?php exit; ?>
<?php endif; ?>

<?php

// ============================================================
// 4. Procesar cada venta
// ============================================================
?>
<h2>📋 Procesando ventas...</h2>

<?php

$exitos = 0;
$fallos = 0;
$detalles_fallos = [];

$facturaModel = new FacturaElectronica();

foreach ($ventas_huerfanas as $venta) {
    $venta_id      = (int)$venta['venta_id'];
    $num_esperado  = $venta['numero_comprobante'];
    $cliente       = $venta['cliente'];

    echo "<div class='venta'>";
    echo "<strong>Venta #{$venta_id}</strong> — {$cliente}<br>";
    echo "Comprobante asignado: <span class='info'>{$num_esperado}</span><br>";

    try {
        // Verificar que no exista ya (por seguridad)
        $sql_check = "SELECT id FROM comprobantes_electronicos WHERE venta_id = ? LIMIT 1";
        $stmt_check = $db->prepare($sql_check);
        $stmt_check->execute([$venta_id]);

        if ($stmt_check->fetch()) {
            echo "<span class='warning'>⚠️ Ya tiene comprobante, saltando...</span>";
            echo "</div>";
            continue;
        }

        // Emitir comprobante
        $resultado = $facturaModel->emitir($venta_id, 'BOLETA');

        if (!empty($resultado['success'])) {
            echo "<span class='ok'>✅ Emitido: " . htmlspecialchars($resultado['numero_completo'] ?? '') . "</span>";
            $exitos++;
        } else {
            echo "<span class='error'>❌ Error: " . htmlspecialchars($resultado['message'] ?? 'desconocido') . "</span>";
            $fallos++;
            $detalles_fallos[] = [
                'venta_id' => $venta_id,
                'numero'   => $num_esperado,
                'error'    => $resultado['message'] ?? 'desconocido'
            ];
        }

    } catch (Exception $e) {
        echo "<span class='error'>💥 Excepción: " . htmlspecialchars($e->getMessage()) . "</span>";
        $fallos++;
        $detalles_fallos[] = [
            'venta_id' => $venta_id,
            'numero'   => $num_esperado,
            'error'    => $e->getMessage()
        ];
    }

    echo "</div>";
}

// ============================================================
// 5. Resumen final
// ============================================================
?>
<h2>📊 Resultado Final</h2>

<div class="resumen">
    <div class="resumen-item">
        <div class="ok">Exitosos:</div>
        <div class="resumen-valor ok"><?= $exitos ?></div>
    </div>
    <div class="resumen-item">
        <div class="error">Fallidos:</div>
        <div class="resumen-valor error"><?= $fallos ?></div>
    </div>
    <div class="resumen-item">
        <div class="info">Total:</div>
        <div class="resumen-valor"><?= count($ventas_huerfanas) ?></div>
    </div>
</div>

<?php if (!empty($detalles_fallos)): ?>
    <h2 class="error">⚠️ Detalles de fallos</h2>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #333;">
            <th style="padding: 8px; border: 1px solid #555;">Venta ID</th>
            <th style="padding: 8px; border: 1px solid #555;">Comprobante</th>
            <th style="padding: 8px; border: 1px solid #555;">Error</th>
        </tr>
        <?php foreach ($detalles_fallos as $fallo): ?>
            <tr>
                <td style="padding: 8px; border: 1px solid #555;"><?= $fallo['venta_id'] ?></td>
                <td style="padding: 8px; border: 1px solid #555;"><?= htmlspecialchars($fallo['numero']) ?></td>
                <td style="padding: 8px; border: 1px solid #555; color: #f48771;">
                    <?= htmlspecialchars($fallo['error']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<div class="resumen" style="margin-top: 30px;">
    <h3 class="warning">⚠️ Importante:</h3>
    <ul>
        <li><strong>ELIMINA este archivo</strong> después de usarlo (por seguridad)</li>
        <li>Verifica la consistencia con la query del final</li>
        <li>Si el modo es <strong>PRODUCCIÓN</strong>, revisa la bandeja de SUNAT</li>
    </ul>
</div>

<p>
    <a href="<?= BASE_URL ?>ventas" class="btn">Volver a Ventas</a>
</p>

<h3>🔍 Query de verificación:</h3>
<pre style="background: #1e1e1e; padding: 15px; border-radius: 5px; overflow-x: auto;">
SELECT 
    COUNT(*) AS total_huerfanas
FROM ventas_pasajes vp
LEFT JOIN comprobantes_electronicos ce ON ce.venta_id = vp.id
WHERE ce.id IS NULL AND vp.estado = 'ACTIVA';
</pre>

<p class="ok">Resultado esperado: <strong>0</strong></p>

</body>
</html>