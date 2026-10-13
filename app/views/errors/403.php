<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 403 - Acceso denegado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .error-container {
            text-align: center;
            color: white;
            padding: 40px;
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
            color: #f5576c;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            color: #f093fb;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <div class="error-icon">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="error-title">Acceso denegado</h1>
        <p class="error-message">
            No tienes permisos para acceder a esta página.<br>
            <?php if (!empty($mensaje) && $mensaje !== 'Acceso denegado'): ?>
                <small><?php echo htmlspecialchars($mensaje); ?></small>
            <?php endif; ?>
        </p>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL . 'home' : '/'; ?>" class="btn-home">
            <i class="fas fa-home me-2"></i>Volver al dashboard
        </a>
    </div>
</body>
</html>