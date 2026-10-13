<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $titulo; ?></h1>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user me-2"></i>Editar Perfil
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo BASE_URL; ?>auth/updateProfile">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombres *</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" name="apellido" class="form-control" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($user['telefono']); ?>">
                    </div>
                    <hr>
                    <h6 class="text-muted">Cambiar Contraseña (dejar vacío para mantener)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nueva Contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirmar Contraseña</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repita la contraseña">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Actualizar Perfil
                    </button>
                    <a href="<?php echo BASE_URL; ?>home" class="btn btn-secondary">Volver</a>
                </form>
            </div>
        </div>
    </div>
</div>