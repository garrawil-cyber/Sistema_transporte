<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error <?php echo $httpCode ?? 'Desconocido'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-center">
            <i class="fas fa-exclamation-circle fa-5x text-danger mb-3"></i>
            <h1 class="display-4">Error <?php echo $httpCode ?? 'Desconocido'; ?></h1>
            <p class="lead">
                <?php echo htmlspecialchars($mensaje ?? 'Ha ocurrido un error'); ?>
            </p>
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn btn-primary">
                <i class="fas fa-home me-2"></i>Volver al inicio
            </a>
            
            <?php if (!empty($debug) && $debug): ?>
            <div class="alert alert-warning text-start mt-4">
                <strong>Debug:</strong> <?php echo htmlspecialchars($archivo ?? ''); ?>:<?php echo $linea ?? ''; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>