<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Transporte - Login'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .login-container { max-width: 400px; margin: 80px auto; }
        .card { border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .card-header { background: #0d6efd; color: white; border-radius: 10px 10px 0 0; }
        .btn-primary { width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="text-center mb-4">
                <h3><i class="fas fa-bus"></i> MI CAUTIVO DE AYABACA</h3>
                <p class="text-muted">Sistema de Gestión</p>
            </div>
            
            <?php if ($flash = $this->getFlash()): ?>
                <div class="alert alert-<?php echo $flash['tipo']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header text-center">
                    <h5 class="mb-0"><?php echo $titulo; ?></h5>
                </div>
                <div class="card-body">
                    <?php require_once __DIR__ . "/../../views/{$view}.php"; ?>
                </div>
            </div>
            <div class="text-center mt-3 text-muted small">
                &copy; <?php echo date('Y'); ?> - Sistema Transporte
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>