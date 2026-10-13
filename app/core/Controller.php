<?php
class Controller {
    public function view($view, $data = []) {
        // Agregar la vista al array de datos para que esté disponible en el layout
        $data['view'] = $view;
        // Si no se pasa título, usar uno por defecto
        if (!isset($data['titulo'])) {
            $data['titulo'] = 'Sistema de Transporte';
        }
        extract($data);
        
        // Seleccionar layout según el prefijo de la vista
        if (strpos($view, 'auth/') === 0) {
            require_once '../app/views/layouts/auth.php';
        } else {
            require_once '../app/views/layouts/main.php';
        }
    }

    public function redirect($url) {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    public function setFlash($mensaje, $tipo = 'success') {
        $_SESSION['flash'] = ['mensaje' => $mensaje, 'tipo' => $tipo];
    }

    public function getFlash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    // Métodos de autenticación
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->setFlash('Debe iniciar sesión para acceder a esta página', 'danger');
            $this->redirect('auth/login');
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireAuth();
        if ($_SESSION['rol_id'] != 1) {
            $this->setFlash('Acceso denegado. Se requieren permisos de administrador.', 'danger');
            $this->redirect('home');
            exit;
        }
    }

    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $usuario = new Usuario();
            return $usuario->find($_SESSION['user_id']);
        }
        return null;
    }

/**
 * Obtener parámetros de paginación de la URL
 */
public function getPaginacion() {
    return [
        'pagina' => (int)($_GET['pagina'] ?? 1),
        'por_pagina' => (int)($_GET['por_pagina'] ?? 15)
    ];
}

/**
 * Cargar una vista sin layout (para páginas públicas)
 */
public function viewStandalone($view, $data = []) {
    extract($data);
    $viewFile = __DIR__ . '/../views/' . $view . '.php';
    if (file_exists($viewFile)) {
        require_once $viewFile;
    } else {
        die('Vista no encontrada: ' . $viewFile);
    }
}

}