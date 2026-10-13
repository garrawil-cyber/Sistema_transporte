<?php
class BusesController extends Controller {
    private $busModel;

    public function __construct() {
        $this->requireAuth();
        $this->requireAdmin(); // ✅ Solo admin gestiona buses
        $this->busModel = new Bus();
    }

    // ============================================================
    // LISTADO
    // ============================================================

    public function index() {
        $paginacion = $this->getPaginacion();

        $filtros = [
            'buscar'        => $_GET['buscar'] ?? '',
            'estado'        => $_GET['estado'] ?? '',
            'tipo_servicio' => $_GET['tipo_servicio'] ?? ''
        ];

        $resultado = $this->busModel->getPaginated(
            $paginacion['pagina'],
            $paginacion['por_pagina'],
            $filtros
        );

        $this->view('buses/index', [
            'titulo'    => 'Gestión de Buses',
            'buses'     => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'filtros'   => $filtros
        ]);
    }

    // ============================================================
    // CREAR
    // ============================================================

    public function create() {
        $this->view('buses/create', [
            'titulo' => 'Nuevo Bus'
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('buses');
            return;
        }

        // ✅ FIX: usar ?? '' en cada $_POST
        $data = [
            'placa'                    => strtoupper(trim($_POST['placa']  ?? '')),
            'marca'                    => trim($_POST['marca']             ?? ''),
            'modelo'                   => trim($_POST['modelo']            ?? ''),
            'anio'                     => !empty($_POST['anio']) ? (int)$_POST['anio'] : null,
            'capacidad_total'          => (int)($_POST['capacidad_total']  ?? 0),
            'tipo_servicio'            => $_POST['tipo_servicio']          ?? 'ECONOMICO',
            'pisos'                    => in_array($_POST['pisos'] ?? '2', ['1', '2'], true)
                                            ? $_POST['pisos'] : '2',
            'asientos_piso_1'          => (int)($_POST['asientos_piso_1'] ?? 0),
            'asientos_piso_2'          => ($_POST['pisos'] ?? '2') === '2'
                                            ? (int)($_POST['asientos_piso_2'] ?? 0) : 0,
            'asientos_por_fila_piso_1' => (int)($_POST['asientos_por_fila_piso_1'] ?? 4),
            'asientos_por_fila_piso_2' => (int)($_POST['asientos_por_fila_piso_2'] ?? 4),
            'estado'                   => $_POST['estado'] ?? 'ACTIVO'
        ];

        // Validar
        $errors = $this->busModel->validate($data, true);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('buses/create', ['data' => $data, 'titulo' => 'Nuevo Bus']);
            return;
        }

        // Guardar
        try {
            $nuevo_id = $this->busModel->create($data);
            if ($nuevo_id) {
                // Auditoría
                if (class_exists('Auditoria')) {
                    Auditoria::registrar('INSERT', [
                        'tabla_afectada' => 'buses',
                        'modulo'         => 'buses',
                        'registro_id'    => $nuevo_id,
                        'descripcion'    => 'Creó bus: ' . $data['placa'],
                        'valores_nuevos' => $data
                    ]);
                }
                $this->setFlash('✅ Bus registrado correctamente', 'success');
            } else {
                $this->setFlash('Error al registrar el bus', 'danger');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('buses');
    }

    // ============================================================
    // EDITAR
    // ============================================================

    public function edit($id) {
        $bus = $this->busModel->find($id);
        if (!$bus) {
            $this->setFlash('Bus no encontrado', 'danger');
            $this->redirect('buses');
            return;
        }

        $this->view('buses/edit', [
            'bus'    => $bus,
            'titulo' => 'Editar Bus'
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('buses');
            return;
        }

        $bus = $this->busModel->find($id);
        if (!$bus) {
            $this->setFlash('Bus no encontrado', 'danger');
            $this->redirect('buses');
            return;
        }

        $data = [
            'id'                       => $id,
            'placa'                    => strtoupper(trim($_POST['placa']  ?? '')),
            'marca'                    => trim($_POST['marca']             ?? ''),
            'modelo'                   => trim($_POST['modelo']            ?? ''),
            'anio'                     => !empty($_POST['anio']) ? (int)$_POST['anio'] : null,
            'capacidad_total'          => (int)($_POST['capacidad_total']  ?? 0),
            'tipo_servicio'            => $_POST['tipo_servicio']          ?? 'ECONOMICO',
            'pisos'                    => in_array($_POST['pisos'] ?? '2', ['1', '2'], true)
                                            ? $_POST['pisos'] : '2',
            'asientos_piso_1'          => (int)($_POST['asientos_piso_1'] ?? 0),
            'asientos_piso_2'          => ($_POST['pisos'] ?? '2') === '2'
                                            ? (int)($_POST['asientos_piso_2'] ?? 0) : 0,
            'asientos_por_fila_piso_1' => (int)($_POST['asientos_por_fila_piso_1'] ?? 4),
            'asientos_por_fila_piso_2' => (int)($_POST['asientos_por_fila_piso_2'] ?? 4),
            'estado'                   => $_POST['estado'] ?? 'ACTIVO'
        ];

        // Validar
        $errors = $this->busModel->validate($data, false);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('buses/edit', [
                'bus'    => array_merge($bus, $data),
                'titulo' => 'Editar Bus'
            ]);
            return;
        }

        // Actualizar
        try {
            if ($this->busModel->update($id, $data)) {
                // Auditoría
                if (class_exists('Auditoria')) {
                    Auditoria::registrar('UPDATE', [
                        'tabla_afectada'     => 'buses',
                        'modulo'             => 'buses',
                        'registro_id'        => $id,
                        'descripcion'        => 'Actualizó bus: ' . $data['placa'],
                        'valores_anteriores' => $bus,
                        'valores_nuevos'     => $data
                    ]);
                }
                $this->setFlash('✅ Bus actualizado correctamente', 'success');
            } else {
                $this->setFlash('Error al actualizar el bus', 'danger');
            }
        } catch (Exception $e) {
            $this->setFlash('Error: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('buses');
    }

    // ============================================================
    // DAR DE BAJA (soft delete)
    // ============================================================

    public function delete($id) {
        $bus = $this->busModel->find($id);
        if (!$bus) {
            $this->setFlash('Bus no encontrado', 'danger');
            $this->redirect('buses');
            return;
        }

        // Verificar viajes programados
        if ($this->busModel->tieneViajesProgramados($id)) {
            $this->setFlash(
                '⚠️ No se puede dar de baja: el bus tiene viajes PROGRAMADOS. ' .
                'Cancele o complete esos viajes primero.',
                'danger'
            );
            $this->redirect('buses');
            return;
        }

        // Cambiar estado a BAJA
        if ($this->busModel->cambiarEstado($id, 'BAJA')) {
            if (class_exists('Auditoria')) {
                Auditoria::registrar('UPDATE', [
                    'tabla_afectada'     => 'buses',
                    'modulo'             => 'buses',
                    'registro_id'        => $id,
                    'descripcion'        => 'Dio de baja el bus: ' . $bus['placa'],
                    'valores_anteriores' => ['estado' => $bus['estado']],
                    'valores_nuevos'     => ['estado' => 'BAJA']
                ]);
            }
            $this->setFlash('Bus dado de baja correctamente', 'success');
        } else {
            $this->setFlash('Error al dar de baja el bus', 'danger');
        }

        $this->redirect('buses');
    }

    // ============================================================
    // CAMBIO DE ESTADO
    // ============================================================

    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('buses');
            return;
        }

        $estado = $_POST['estado'] ?? '';
        if ($this->busModel->cambiarEstado($id, $estado)) {
            if (class_exists('Auditoria')) {
                Auditoria::registrar('UPDATE', [
                    'tabla_afectada' => 'buses',
                    'modulo'         => 'buses',
                    'registro_id'    => $id,
                    'descripcion'    => 'Cambió estado del bus a: ' . $estado,
                    'valores_nuevos' => ['estado' => $estado]
                ]);
            }
            $this->setFlash('Estado actualizado a ' . $estado, 'success');
        } else {
            $this->setFlash('Error al cambiar el estado', 'danger');
        }

        $this->redirect('buses');
    }

    // ============================================================
    // DETALLE
    // ============================================================

    public function show($id) {
        $bus = $this->busModel->getByIdWithDetails($id);
        if (!$bus) {
            $this->setFlash('Bus no encontrado', 'danger');
            $this->redirect('buses');
            return;
        }

        $estadisticas = $this->busModel->getEstadisticas($id);
        $viajes       = $this->busModel->getViajes($id, 20);

        $this->view('buses/show', [
            'bus'          => $bus,
            'estadisticas' => $estadisticas,
            'viajes'       => $viajes,
            'titulo'       => 'Detalle del Bus'
        ]);
    }
}