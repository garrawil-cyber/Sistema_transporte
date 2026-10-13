<?php
class AuthController extends Controller {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->setFlash('Complete todos los campos', 'danger');
                $this->view('auth/login');
                return;
            }

            $user = $this->usuarioModel->authenticate($email, $password);
            if ($user) {
                if ($user['activo'] == 0) {
                    $this->setFlash('Su cuenta está desactivada. Contacte al administrador.', 'danger');
                    $this->view('auth/login');
                    return;
                }

                $_SESSION['user_id'] = $user['id'];
                // Registrar login en auditoría
                Auditoria::registrarLogin($user['id'], $user['email']);
                $_SESSION['nombre'] = $user['nombre'] . ' ' . $user['apellido'];
                $_SESSION['rol_id'] = $user['rol_id'];
                $_SESSION['email'] = $user['email'];

                $this->usuarioModel->registrarAcceso($user['id']);
                $this->setFlash('Bienvenido ' . $_SESSION['nombre'], 'success');
                $this->redirect('home');
            } else {
                $this->setFlash('Email o contraseña incorrectos', 'danger');
                $this->view('auth/login');
            }
        } else {
            $this->view('auth/login', ['titulo' => 'Iniciar Sesión']);
        }
    }

    public function logout() {
        // Registrar logout
        if (isset($_SESSION['user_id'])) {
            Auditoria::registrarLogout($_SESSION['user_id'], $_SESSION['email'] ?? null);
        }
        session_destroy();
        $this->setFlash('Sesión cerrada correctamente', 'info');
        $this->redirect('auth/login');
    }

    public function profile() {
        $this->requireAuth();
        $user = $this->getCurrentUser();
        $this->view('auth/profile', ['user' => $user, 'titulo' => 'Mi Perfil']);
    }

    public function updateProfile() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $data = [
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'email' => trim($_POST['email']),
                'telefono' => trim($_POST['telefono'])
            ];

            $existente = $this->usuarioModel->findByEmail($data['email']);
            if ($existente && $existente['id'] != $user_id) {
                $this->setFlash('El email ya está registrado por otro usuario', 'danger');
                $this->redirect('auth/profile');
                return;
            }

            if (!empty($_POST['password'])) {
                if ($_POST['password'] !== $_POST['confirm_password']) {
                    $this->setFlash('Las contraseñas no coinciden', 'danger');
                    $this->redirect('auth/profile');
                    return;
                }
                if (strlen($_POST['password']) < 6) {
                    $this->setFlash('La contraseña debe tener al menos 6 caracteres', 'danger');
                    $this->redirect('auth/profile');
                    return;
                }
                $data['password'] = $_POST['password'];
            }

            if ($this->usuarioModel->update($user_id, $data)) {
                $_SESSION['nombre'] = $data['nombre'] . ' ' . $data['apellido'];
                $_SESSION['email'] = $data['email'];
                $this->setFlash('Perfil actualizado correctamente', 'success');
            } else {
                $this->setFlash('Error al actualizar el perfil', 'danger');
            }
            $this->redirect('auth/profile');
        }
    }
}