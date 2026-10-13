<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $titulo; ?></h1>
    <div>
        <a href="<?php echo BASE_URL; ?>manifiesto" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver
        </a>
        <a href="<?php echo BASE_URL; ?>manifiesto/pdf/<?php echo $viaje['id']; ?>" target="_blank" class="btn btn-danger">
            <i class="fas fa-file-pdf me-1"></i>Descargar PDF
        </a>
        <button onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print me-1"></i>Imprimir
        </button>
    </div>
</div>

<!-- Aquí se incluye el mismo HTML del manifiesto (reutilizando la lógica) -->
<style>
    /* Estilos para la vista */
    .manifiesto-container {
        background: white;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    @media print {
        .no-print { display: none !important; }
        .manifiesto-container { box-shadow: none; padding: 10px; }
        .btn { display: none; }
    }
</style>

<div class="manifiesto-container">
    <?php
    // Reutilizar el generador de HTML del manifiesto
    $controller = new ManifiestoController();
    $html = $controller->generarHTMLManifiesto(
        $viaje, 
        $pasajeros, 
        $resumen_ocupacion, 
        $total_pasajeros, 
        $total_asientos, 
        $porcentaje_ocupacion
    );
    echo $html;
    ?>
</div>