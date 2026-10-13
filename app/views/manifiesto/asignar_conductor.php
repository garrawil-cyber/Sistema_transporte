<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $titulo; ?></h1>
</div>

<div class="card">
    <div class="card-body">
        <p>El viaje seleccionado no tiene un conductor asignado. Por favor, asigne uno para poder generar el manifiesto.</p>
        <form method="POST" action="<?php echo BASE_URL; ?>manifiesto/asignarConductor">
            <input type="hidden" name="viaje_id" value="<?php echo $viaje_id; ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Conductor</label>
                    <select name="conductor_id" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($conductores as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre_completo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Asignar</button>
                </div>
            </div>
        </form>
        <a href="<?php echo BASE_URL; ?>manifiesto" class="btn btn-secondary mt-3">Volver</a>
    </div>
</div>