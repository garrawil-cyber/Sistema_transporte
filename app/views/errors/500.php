<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 500 - Error interno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b 0%, #cc2b2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .error-container {
            text-align: center;
            color: white;
            padding: 40px;
            max-width: 800px;
        }
        .error-code {
            font-size: 150px;
            font-weight: 900;
            line-height: 1;
            text-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .error-icon {
            font-size: 80px;
            margin: 20px 0;
        }
        .error-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .error-message {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .btn-home {
            background: white;
            color: #cc2b2b;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
            margin: 5px;
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: #ff6b6b;
        }
        .debug-info {
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 300px;
            overflow-y: auto;
        }
        .debug-info pre {
            color: #fff;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="error-title">Error interno del servidor</h1>
        <p class="error-message">
            Lo sentimos, ha ocurrido un error inesperado.<br>
            El equipo técnico ha sido notificado.
        </p>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL . 'home' : '/'; ?>" class="btn-home">
            <i class="fas fa-home me-2"></i>Volver al dashboard
        </a>
        <a href="javascript:location.reload()" class="btn-home">
            <i class="fas fa-redo me-2"></i>Intentar de nuevo
        </a>
        
        <?php if (!empty($debug) && $debug): ?>
        <div class="debug-info">
            <h6><i class="fas fa-bug me-2"></i>Información de depuración:</h6>
            <p><strong>Mensaje:</strong> <?php echo htmlspecialchars($mensaje ?? ''); ?></p>
            <p><strong>Archivo:</strong> <?php echo htmlspecialchars($archivo ?? ''); ?>:<?php echo $linea ?? ''; ?></p>
            <?php if (!empty($traza)): ?>
            <details>
                <summary>Ver traza completa</summary>
                <pre><?php echo htmlspecialchars($traza); ?></pre>
            </details>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>