<form method="POST" action="<?php echo BASE_URL; ?>auth/login">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com" required autofocus>
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
    </button>
</form>