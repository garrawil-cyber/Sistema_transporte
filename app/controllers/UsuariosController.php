<?php
class UsuariosController extends Controller {
    private $usuarioModel;
    private $rolModel;

    public function __construct() {
        $this->requireAuth();
        $this->requireAdmin();
        $this->usuarioModel = new Usuario();
        $this->rolModel = new Rol();
    }

    // ============================================================
    // LISTADO
    // ============================================================

    public function index() {
        $paginacion = $this->getPaginacion();

        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'rol_id' => $_GET['rol_id'] ?? '',
            'activo' => $_GET['activo'] ?? ''
        ];

        $resultado = $this->usuarioModel->getPaginated(
            $paginacion['pagina'],
            $paginacion['por_pagina'],
            $filtros
        );

        $roles = $this->rolModel->all();

        $this->view('usuarios/index', [
            'usuarios'  => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'roles'     => $roles,
            'filtros'   => $filtros,
            'titulo'    => 'Gestión de Usuarios'
        ]);
    }

    // ============================================================
    // CREAR
    // ============================================================

    public function create() {
        $roles = $this->rolModel->all();
        $this->view('usuarios/create', [
            'roles'  => $roles,
            'titulo' => 'Nuevo Usuario'
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
            return;
        }

        $data = [
            'rol_id'   => (int)($_POST['rol_id'] ?? 0),
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'telefono' => trim($_POST['telefono'] ?? ''),
            'activo'   => isset($_POST['activo']) ? 1 : 0
        ];

        $errors = [];
        if (empty($data['nombre']))    $errors[] = 'El nombre es obligatorio';
        if (empty($data['apellido']))  $errors[] = 'El apellido es obligatorio';
        if (empty($data['email']))     $errors[] = 'El email es obligatorio';
        elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }
        if (empty($data['password']))  $errors[] = 'La contraseña es obligatoria';
        elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        if ($data['rol_id'] <= 0)      $errors[] = 'Debe seleccionar un rol';
        if ($this->usuarioModel->findByEmail($data['email'])) {
            $errors[] = 'El email ya está registrado';
        }

        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $roles = $this->rolModel->all();
            $this->view('usuarios/create', [
                'data'   => $data,
                'roles'  => $roles,
                'titulo' => 'Nuevo Usuario'
            ]);
            return;
        }

        try {
            if ($this->usuarioModel->create($data)) {
                $this->setFlash('Usuario creado exitosamente', 'success');
            } else {
                $this->setFlash('Error al crear el usuario', 'danger');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('usuarios');
    }

    // ============================================================
    // EDITAR
    // ============================================================

    public function edit($id) {
        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            $this->setFlash('Usuario no encontrado', 'danger');
            $this->redirect('usuarios');
            return;
        }

        $roles = $this->rolModel->all();
        $this->view('usuarios/edit', [
            'usuario' => $usuario,
            'roles'   => $roles,
            'titulo'  => 'Editar Usuario'
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
            return;
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            $this->setFlash('Usuario no encontrado', 'danger');
            $this->redirect('usuarios');
            return;
        }

        $data = [
            'rol_id'   => (int)($_POST['rol_id'] ?? 0),
            'nombre'   => trim($_POST['nombre'] ?? ''),
            'apellido' => trim($_POST['apellido'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'activo'   => isset($_POST['activo']) ? 1 : 0
        ];

        // Validar password solo si viene
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $this->setFlash('La contraseña debe tener al menos 6 caracteres', 'danger');
                $roles = $this->rolModel->all();
                $this->view('usuarios/edit', [
                    'usuario' => array_merge($usuario, $data),
                    'roles'   => $roles,
                    'titulo'  => 'Editar Usuario'
                ]);
                return;
            }
            $data['password'] = $_POST['password'];
        }

        // ✅ FIX: usar el nuevo parámetro $excludeId de findByEmail()
        $existente = $this->usuarioModel->findByEmail($data['email'], $id);
        if ($existente) {
            $this->setFlash('El email ya está registrado por otro usuario', 'danger');
            $roles = $this->rolModel->all();
            $this->view('usuarios/edit', [
                'usuario' => array_merge($usuario, $data),
                'roles'   => $roles,
                'titulo'  => 'Editar Usuario'
            ]);
            return;
        }

        try {
            if ($this->usuarioModel->update($id, $data)) {
                $this->setFlash('Usuario actualizado correctamente', 'success');
            } else {
                $this->setFlash('Error al actualizar el usuario', 'danger');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('usuarios');
    }

    // ============================================================
    // DESACTIVAR / ACTIVAR
    // ============================================================

    public function delete($id) {
        if ($id == $_SESSION['user_id']) {
            $this->setFlash('No puede desactivar su propia cuenta', 'danger');
            $this->redirect('usuarios');
            return;
        }

        if ($this->usuarioModel->desactivar($id)) {
            $this->setFlash('Usuario desactivado correctamente', 'success');
        } else {
            $this->setFlash('Error al desactivar el usuario', 'danger');
        }
        $this->redirect('usuarios');
    }

    public function activar($id) {
        if ($this->usuarioModel->activar($id)) {
            $this->setFlash('Usuario activado correctamente', 'success');
        } else {
            $this->setFlash('Error al activar el usuario', 'danger');
        }
        $this->redirect('usuarios');
    }

    // ============================================================
    // DETALLE
    // ============================================================

    /**
     * ✅ FIX: show() ahora:
     *    1. Verifica class_exists('Auditoria') antes de instanciarla.
     *    2. Envuelve la llamada en try/catch por si el método no
     *       existe o falla la consulta (evita pantalla blanca).
     *    3. Usa error_log() solo para depurar, sin romper la vista.
     */
    public function show($id) {
        $usuario = $this->usuarioModel->getByIdWithRole($id);
        if (!$usuario) {
            $this->setFlash('Usuario no encontrado', 'danger');
            $this->redirect('usuarios');
            return;
        }

        $sesiones = [];

        if (class_exists('Auditoria')) {
            try {
                $auditoriaModel = new Auditoria();
                if (method_exists($auditoriaModel, 'getHistorialSesiones')) {
                    $sesiones = $auditoriaModel->getHistorialSesiones($id, 20);
                }
            } catch (Exception $e) {
                if (defined('DEBUG') && DEBUG) {
                    error_log("Error obteniendo historial de sesiones: " . $e->getMessage());
                }
            }
        }

        $this->view('usuarios/show', [
            'titulo'   => 'Detalle del Usuario',
            'usuario'  => $usuario,
            'sesiones' => $sesiones
        ]);
    }
}