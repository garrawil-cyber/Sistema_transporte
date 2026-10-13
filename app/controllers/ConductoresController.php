<?php
class ConductoresController extends Controller {
    private $conductorModel;
    private $usuarioModel;

    public function __construct() {
        $this->requireAuth();
        $this->requireAdmin();
        $this->conductorModel = new Conductor();
        $this->usuarioModel = new Usuario();
    }

    // ============================================================
    // LISTADO
    // ============================================================

    public function index() {
        $paginacion = $this->getPaginacion();

        $filtros = [
            'buscar'              => $_GET['buscar'] ?? '',
            'estado'              => $_GET['estado'] ?? '',
            'licencia_por_vencer' => $_GET['licencia_por_vencer'] ?? '',
            'licencia_vencida'    => $_GET['licencia_vencida'] ?? ''
        ];

        $resultado = $this->conductorModel->getPaginated(
            $paginacion['pagina'],
            $paginacion['por_pagina'],
            $filtros
        );

        $resumen = $this->conductorModel->getResumen();
        $licencias_por_vencer = $this->conductorModel->getLicenciasPorVencer(30);
        $pendientes = $this->conductorModel->getPendientes();

        $this->view('conductores/index', [
            'titulo'              => 'Gestión de Conductores',
            'conductores'         => $resultado['datos'],
            'paginator'           => $resultado['paginator'],
            'resumen'             => $resumen,
            'licencias_por_vencer' => $licencias_por_vencer,
            'pendientes'          => $pendientes,
            'filtros'             => $filtros,
            'categorias_validas'  => Conductor::CATEGORIAS_VALIDAS
        ]);
    }

    // ============================================================
    // CREAR
    // ============================================================

    public function create() {
        $pendientes = $this->conductorModel->getPendientes();

        $this->view('conductores/create', [
            'titulo'             => 'Nuevo Conductor',
            'pendientes'         => $pendientes,
            'categorias_validas' => Conductor::CATEGORIAS_VALIDAS,
            'data'               => $_SESSION['form_data'] ?? []
        ]);

        unset($_SESSION['form_data']);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('conductores');
            return;
        }

        // ============================================
        // 1. Determinar modo (existente / nuevo)
        // ============================================
        $modo = $_POST['modo_usuario'] ?? 'existente';
        $usuario_id = null;
        $usuario_creado = false;

        // ============================================
        // MODO A: Usuario existente
        // ============================================
        if ($modo === 'existente') {
            $usuario_id = (int)($_POST['usuario_id'] ?? 0);

            if (empty($usuario_id)) {
                $this->setFlash('Debe seleccionar un usuario.', 'danger');
                $this->redirect('conductores/create');
                return;
            }

            $usuario = $this->usuarioModel->find($usuario_id);
            if (!$usuario || $usuario['rol_id'] != 5) {
                $this->setFlash('El usuario seleccionado no es válido o no tiene rol CONDUCTOR.', 'danger');
                $this->redirect('conductores/create');
                return;
            }

            if ($this->conductorModel->usuarioYaEsConductor($usuario_id)) {
                $this->setFlash('Este usuario ya tiene un perfil de conductor registrado.', 'warning');
                $this->redirect('conductores');
                return;
            }
        }

        // ============================================
        // MODO B: Crear usuario nuevo
        // ============================================
        else {
            $usuario_data = [
                'nombre'   => trim($_POST['usuario_nombre'] ?? ''),
                'apellido' => trim($_POST['usuario_apellido'] ?? ''),
                'email'    => trim($_POST['usuario_email'] ?? ''),
                'telefono' => trim($_POST['usuario_telefono'] ?? ''),
                'password' => $_POST['usuario_password'] ?? 'conductor123',
                'rol_id'   => 5,
                'activo'   => 1
            ];

            // ✅ Solo letras, tildes, ñ y espacios — 2 a 100 caracteres.
            //    \p{L} (propiedad Unicode "letra") cubre acentos y ñ sin
            //    necesidad de enumerar cada caso a mano.
            $patron_nombre = '/^[\p{L}\s]{2,100}$/u';

            $errores_usuario = [];
            if (empty($usuario_data['nombre'])) {
                $errores_usuario[] = 'El nombre del usuario es obligatorio.';
            } elseif (!preg_match($patron_nombre, $usuario_data['nombre'])) {
                $errores_usuario[] = 'El nombre del usuario solo debe contener letras (sin números ni símbolos).';
            }
            if (empty($usuario_data['apellido'])) {
                $errores_usuario[] = 'El apellido del usuario es obligatorio.';
            } elseif (!preg_match($patron_nombre, $usuario_data['apellido'])) {
                $errores_usuario[] = 'El apellido del usuario solo debe contener letras (sin números ni símbolos).';
            }
            if (empty($usuario_data['email']))    $errores_usuario[] = 'El email del usuario es obligatorio.';
            elseif (!filter_var($usuario_data['email'], FILTER_VALIDATE_EMAIL)) {
                $errores_usuario[] = 'El email no es válido.';
            }
            if (strlen($usuario_data['password']) < 6) {
                $errores_usuario[] = 'La contraseña debe tener al menos 6 caracteres.';
            }

            if (empty($errores_usuario) && $this->usuarioModel->findByEmail($usuario_data['email'])) {
                $errores_usuario[] = 'Ya existe un usuario con ese email.';
            }

            if (!empty($errores_usuario)) {
                $this->setFlash(implode('<br>', $errores_usuario), 'danger');
                $_SESSION['form_data'] = $_POST;
                $this->redirect('conductores/create');
                return;
            }

            try {
                $usuario_id = $this->usuarioModel->createWithPassword($usuario_data);
                $usuario_creado = true;
            } catch (Exception $e) {
                $this->setFlash('Error al crear el usuario: ' . $e->getMessage(), 'danger');
                $_SESSION['form_data'] = $_POST;
                $this->redirect('conductores/create');
                return;
            }
        }

        // ============================================
        // 2. Subir foto (opcional)
        // ============================================
        $foto_nombre = null;
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            try {
                $foto_nombre = $this->subirFoto($_FILES['foto']);
            } catch (Exception $e) {
                // Rollback: si creamos usuario, desactivarlo (preserva auditoría)
                if ($usuario_creado) {
                    $this->usuarioModel->desactivar($usuario_id);
                }
                $this->setFlash('Error al subir la foto: ' . $e->getMessage(), 'danger');
                $_SESSION['form_data'] = $_POST;
                $this->redirect('conductores/create');
                return;
            }
        }

        // ============================================
        // 3. Datos del conductor
        // ============================================
        $data = [
            'usuario_id'                    => $usuario_id,
            'foto'                          => $foto_nombre,
            'tipo_documento'                => trim($_POST['tipo_documento'] ?? 'DNI'),
            'numero_documento'              => trim($_POST['numero_documento'] ?? ''),
            'numero_licencia'               => trim($_POST['numero_licencia'] ?? ''),
            'categoria_licencia'            => trim($_POST['categoria_licencia'] ?? ''),
            'fecha_emision_licencia'        => !empty($_POST['fecha_emision_licencia']) ? $_POST['fecha_emision_licencia'] : null,
            'fecha_vencimiento_licencia'    => $_POST['fecha_vencimiento_licencia'] ?? '',
            'fecha_nacimiento'              => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'direccion'                     => trim($_POST['direccion'] ?? ''),
            'contacto_emergencia_nombre'    => trim($_POST['contacto_emergencia_nombre'] ?? ''),
            'contacto_emergencia_telefono'  => trim($_POST['contacto_emergencia_telefono'] ?? ''),
            'fecha_ingreso'                 => !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : date('Y-m-d'),
            'sueldo_base'                   => (float)($_POST['sueldo_base'] ?? 0),
            'comision_porcentaje'           => (float)($_POST['comision_porcentaje'] ?? 0),
            'estado'                        => $_POST['estado'] ?? 'ACTIVO',
            'observaciones'                 => trim($_POST['observaciones'] ?? '')
        ];

        // Validar
        $errores = $this->conductorModel->validate($data, true);

        if (!empty($errores)) {
            if ($usuario_creado) {
                $this->usuarioModel->desactivar($usuario_id);
            }
            if ($foto_nombre && file_exists(UPLOAD_PATH . 'conductores/' . $foto_nombre)) {
                unlink(UPLOAD_PATH . 'conductores/' . $foto_nombre);
            }
            $this->setFlash(implode('<br>', $errores), 'danger');
            $_SESSION['form_data'] = $_POST;
            $this->redirect('conductores/create');
            return;
        }

        // Insertar con transacción
        try {
            $conductor_id = $this->conductorModel->crearConTransaccion($data);

            if (class_exists('Auditoria')) {
                Auditoria::registrar('INSERT', [
                    'tabla_afectada' => 'conductores',
                    'modulo'         => 'conductores',
                    'registro_id'    => $conductor_id,
                    'descripcion'    => 'Creó conductor: ' . $data['numero_licencia'],
                    'valores_nuevos' => $data
                ]);
            }

            $this->setFlash('✅ Conductor registrado correctamente.', 'success');
            $this->redirect('conductores/show/' . $conductor_id);

        } catch (Exception $e) {
            if ($usuario_creado) {
                $this->usuarioModel->desactivar($usuario_id);
            }
            if ($foto_nombre && file_exists(UPLOAD_PATH . 'conductores/' . $foto_nombre)) {
                unlink(UPLOAD_PATH . 'conductores/' . $foto_nombre);
            }
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
            $_SESSION['form_data'] = $_POST;
            $this->redirect('conductores/create');
        }
    }

    // ============================================================
    // VER DETALLE
    // ============================================================

    public function show($id) {
        $conductor = $this->conductorModel->getByIdWithDetails($id);

        if (!$conductor) {
            $this->setFlash('Conductor no encontrado.', 'danger');
            $this->redirect('conductores');
            return;
        }

        $estadisticas = $this->conductorModel->getEstadisticasViajes($conductor['usuario_id']);
        $historial    = $this->conductorModel->getHistorialViajes($conductor['usuario_id'], 20);

        $this->view('conductores/show', [
            'titulo'       => 'Detalle del Conductor',
            'conductor'    => $conductor,
            'estadisticas' => $estadisticas,
            'historial'    => $historial
        ]);
    }

    // ============================================================
    // EDITAR
    // ============================================================

    public function edit($id) {
        $conductor = $this->conductorModel->getByIdWithDetails($id);

        if (!$conductor) {
            $this->setFlash('Conductor no encontrado.', 'danger');
            $this->redirect('conductores');
            return;
        }

        $this->view('conductores/edit', [
            'titulo'             => 'Editar Conductor',
            'conductor'          => $conductor,
            'categorias_validas' => Conductor::CATEGORIAS_VALIDAS
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('conductores');
            return;
        }

        $conductor = $this->conductorModel->find($id);
        if (!$conductor) {
            $this->setFlash('Conductor no encontrado.', 'danger');
            $this->redirect('conductores');
            return;
        }

        // Subir nueva foto (opcional)
        $foto_nombre = $conductor['foto'];
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            try {
                $foto_nombre = $this->subirFoto($_FILES['foto']);
                if (!empty($conductor['foto']) && file_exists(UPLOAD_PATH . 'conductores/' . $conductor['foto'])) {
                    unlink(UPLOAD_PATH . 'conductores/' . $conductor['foto']);
                }
            } catch (Exception $e) {
                $this->setFlash('Error al subir foto: ' . $e->getMessage(), 'danger');
                $this->redirect('conductores/edit/' . $id);
                return;
            }
        }

        $data = [
            'foto'                          => $foto_nombre,
            'tipo_documento'                => trim($_POST['tipo_documento'] ?? 'DNI'),
            'numero_documento'              => trim($_POST['numero_documento'] ?? ''),
            'numero_licencia'               => trim($_POST['numero_licencia'] ?? ''),
            'categoria_licencia'            => trim($_POST['categoria_licencia'] ?? ''),
            'fecha_emision_licencia'        => !empty($_POST['fecha_emision_licencia']) ? $_POST['fecha_emision_licencia'] : null,
            'fecha_vencimiento_licencia'    => $_POST['fecha_vencimiento_licencia'] ?? '',
            'fecha_nacimiento'              => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null,
            'direccion'                     => trim($_POST['direccion'] ?? ''),
            'contacto_emergencia_nombre'    => trim($_POST['contacto_emergencia_nombre'] ?? ''),
            'contacto_emergencia_telefono'  => trim($_POST['contacto_emergencia_telefono'] ?? ''),
            'fecha_ingreso'                 => !empty($_POST['fecha_ingreso']) ? $_POST['fecha_ingreso'] : null,
            'sueldo_base'                   => (float)($_POST['sueldo_base'] ?? 0),
            'comision_porcentaje'           => (float)($_POST['comision_porcentaje'] ?? 0),
            'estado'                        => $_POST['estado'] ?? 'ACTIVO',
            'observaciones'                 => trim($_POST['observaciones'] ?? '')
        ];

        $errores = $this->conductorModel->validate($data, false);
        if (!empty($errores)) {
            $this->setFlash(implode('<br>', $errores), 'danger');
            $this->redirect('conductores/edit/' . $id);
            return;
        }

        try {
            $this->conductorModel->actualizarConTransaccion($id, $data);

            // Actualizar teléfono del usuario si viene
            if (!empty($_POST['usuario_telefono'])) {
                $this->usuarioModel->update($conductor['usuario_id'], [
                    'telefono' => trim($_POST['usuario_telefono'])
                ]);
            }

            if (class_exists('Auditoria')) {
                Auditoria::registrar('UPDATE', [
                    'tabla_afectada'     => 'conductores',
                    'modulo'             => 'conductores',
                    'registro_id'        => $id,
                    'descripcion'        => 'Actualizó conductor: ' . $data['numero_licencia'],
                    'valores_anteriores' => $conductor,
                    'valores_nuevos'     => $data
                ]);
            }

            $this->setFlash('✅ Conductor actualizado correctamente.', 'success');
            $this->redirect('conductores/show/' . $id);

        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
            $this->redirect('conductores/edit/' . $id);
        }
    }

    // ============================================================
    // ELIMINAR
    // ============================================================

    public function delete($id) {
        $conductor = $this->conductorModel->find($id);
        if (!$conductor) {
            $this->setFlash('Conductor no encontrado.', 'danger');
            $this->redirect('conductores');
            return;
        }

        try {
            if ($this->conductorModel->tieneViajesAsignados($conductor['usuario_id'])) {
                $this->setFlash(
                    '⚠️ No se puede eliminar: el conductor tiene viajes asignados. ' .
                    'Márquelo como INACTIVO en su lugar.',
                    'warning'
                );
                $this->redirect('conductores');
                return;
            }

            if (!empty($conductor['foto']) && file_exists(UPLOAD_PATH . 'conductores/' . $conductor['foto'])) {
                unlink(UPLOAD_PATH . 'conductores/' . $conductor['foto']);
            }

            $this->conductorModel->eliminarConUsuario($id, false);

            if (class_exists('Auditoria')) {
                Auditoria::registrar('DELETE', [
                    'tabla_afectada'     => 'conductores',
                    'modulo'             => 'conductores',
                    'registro_id'        => $id,
                    'descripcion'        => 'Eliminó conductor: ' . $conductor['numero_licencia'],
                    'valores_anteriores' => $conductor
                ]);
            }

            $this->setFlash('Conductor eliminado correctamente.', 'success');

        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('conductores');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    private function subirFoto($archivo) {
        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $tamano_maximo = 2 * 1024 * 1024; // 2 MB

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $extensiones_permitidas)) {
            throw new Exception('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        if ($archivo['size'] > $tamano_maximo) {
            throw new Exception('La imagen no puede pesar más de 2 MB.');
        }

        $info = @getimagesize($archivo['tmp_name']);
        if ($info === false) {
            throw new Exception('El archivo no es una imagen válida.');
        }

        $dir = UPLOAD_PATH . 'conductores/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = 'conductor_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (!move_uploaded_file($archivo['tmp_name'], $dir . $nombre)) {
            throw new Exception('No se pudo guardar la imagen.');
        }

        return $nombre;
    }
}