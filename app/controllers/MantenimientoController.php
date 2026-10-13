<?php
class MantenimientoController extends Controller {
    private $mantenimientoModel;
    private $busModel;

    public function __construct() {
        $this->requireAuth();
        $this->mantenimientoModel = new Mantenimiento();
        $this->busModel = new Bus();
    }

    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'bus_id' => $_GET['bus_id'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d')
        ];
        
        $resultado = $this->mantenimientoModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );
        
        $resumen = $this->mantenimientoModel->getResumen($filtros);
        $buses = $this->busModel->all();
        $proximos = $this->mantenimientoModel->getProximos(30);
        
        $this->view('mantenimiento/index', [
            'titulo' => 'Gestión de Mantenimiento',
            'mantenimientos' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'resumen' => $resumen,
            'buses' => $buses,
            'proximos' => $proximos,
            'filtros' => $filtros
        ]);
    }

    public function create() {
        $buses = $this->busModel->all();
        $this->view('mantenimiento/create', [
            'titulo' => 'Nuevo Mantenimiento',
            'buses' => $buses
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('mantenimiento');
        }

        $data = [
            'bus_id' => (int)$_POST['bus_id'],
            'usuario_id' => $_SESSION['user_id'],
            'tipo' => $_POST['tipo'] ?? 'PREVENTIVO',
            'fecha_programada' => !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null,
            'kilometraje' => !empty($_POST['kilometraje']) ? (int)$_POST['kilometraje'] : null,
            'descripcion' => trim($_POST['descripcion']),
            'taller_responsable' => trim($_POST['taller_responsable'] ?? ''),
            'costo_mano_obra' => (float)($_POST['costo_mano_obra'] ?? 0),
            'costo_repuestos' => (float)($_POST['costo_repuestos'] ?? 0),
            'proximo_mantenimiento_km' => !empty($_POST['proximo_mantenimiento_km']) ? (int)$_POST['proximo_mantenimiento_km'] : null,
            'proximo_mantenimiento_fecha' => !empty($_POST['proximo_mantenimiento_fecha']) ? $_POST['proximo_mantenimiento_fecha'] : null,
            'estado' => $_POST['estado'] ?? 'PROGRAMADO',
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        $errors = [];
        if (empty($data['bus_id'])) $errors[] = 'Debe seleccionar un bus';
        if (empty($data['descripcion'])) $errors[] = 'La descripción es obligatoria';

        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->redirect('mantenimiento/create');
            return;
        }

        if ($this->mantenimientoModel->registrar($data)) {
            $this->setFlash('Mantenimiento registrado correctamente', 'success');
        } else {
            $this->setFlash('Error al registrar', 'danger');
        }
        $this->redirect('mantenimiento');
    }

    public function edit($id) {
        $mantenimiento = $this->mantenimientoModel->find($id);
        if (!$mantenimiento) {
            $this->setFlash('Mantenimiento no encontrado', 'danger');
            $this->redirect('mantenimiento');
        }
        
        $buses = $this->busModel->all();
        $this->view('mantenimiento/edit', [
            'titulo' => 'Editar Mantenimiento',
            'mantenimiento' => $mantenimiento,
            'buses' => $buses
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('mantenimiento');
        }

        $data = [
            'bus_id' => (int)$_POST['bus_id'],
            'tipo' => $_POST['tipo'] ?? 'PREVENTIVO',
            'fecha_programada' => !empty($_POST['fecha_programada']) ? $_POST['fecha_programada'] : null,
            'fecha_realizada' => !empty($_POST['fecha_realizada']) ? $_POST['fecha_realizada'] : null,
            'kilometraje' => !empty($_POST['kilometraje']) ? (int)$_POST['kilometraje'] : null,
            'descripcion' => trim($_POST['descripcion']),
            'taller_responsable' => trim($_POST['taller_responsable'] ?? ''),
            'costo_mano_obra' => (float)($_POST['costo_mano_obra'] ?? 0),
            'costo_repuestos' => (float)($_POST['costo_repuestos'] ?? 0),
            'proximo_mantenimiento_km' => !empty($_POST['proximo_mantenimiento_km']) ? (int)$_POST['proximo_mantenimiento_km'] : null,
            'proximo_mantenimiento_fecha' => !empty($_POST['proximo_mantenimiento_fecha']) ? $_POST['proximo_mantenimiento_fecha'] : null,
            'estado' => $_POST['estado'] ?? 'PROGRAMADO',
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];
        
        $data['costo_total'] = $data['costo_mano_obra'] + $data['costo_repuestos'];

        if ($this->mantenimientoModel->update($id, $data)) {
            $this->setFlash('Mantenimiento actualizado', 'success');
        } else {
            $this->setFlash('Error al actualizar', 'danger');
        }
        $this->redirect('mantenimiento');
    }

public function show($viaje_id) {
    // ✅ FIX: Validar ID
    $viaje_id = (int)$viaje_id;
    if ($viaje_id <= 0) {
        $this->setFlash('ID de viaje inválido', 'danger');
        $this->redirect('manifiesto');
        return;
    }

    $data = $this->manifiestoModel->getManifiestoData($viaje_id);

    // ✅ FIX: return después de redirect
    if (!$data) {
        $this->setFlash('Viaje no encontrado', 'danger');
        $this->redirect('manifiesto');
        return;
    }

    // Si no tiene conductor, redirigir a asignar
    if (empty($data['viaje']['conductor_nombre'])) {
        $conductores = $this->manifiestoModel->getConductores();
        $this->view('manifiesto/asignar_conductor', [
            'viaje_id'    => $viaje_id,
            'conductores' => $conductores,
            'titulo'      => 'Asignar Conductor'
        ]);
        return;
    }

    // Generar/obtener hash y QR
    $hash   = $this->manifiestoModel->obtenerHash($viaje_id);
    $qrData = $this->generarQR($hash);

    $this->viewStandalone('manifiesto/show', [
        'data'   => $data,
        'qrData' => $qrData,
        'hash'   => $hash,
        'titulo' => 'Manifiesto de Pasajeros'
    ]);
}

    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('mantenimiento');
        }
        
        $estado = $_POST['estado'] ?? '';
        $fecha_realizada = ($estado == 'COMPLETADO') ? date('Y-m-d') : null;
        
        if ($this->mantenimientoModel->cambiarEstado($id, $estado, $fecha_realizada)) {
            $this->setFlash('Estado actualizado a ' . $estado, 'success');
        } else {
            $this->setFlash('Error al cambiar estado', 'danger');
        }
        $this->redirect('mantenimiento');
    }

    /**
 * Eliminar mantenimiento
 */
public function delete($id) {
    $this->requireAdmin();

    $mantenimiento = $this->mantenimientoModel->find($id);
    if (!$mantenimiento) {
        $this->setFlash('Mantenimiento no encontrado', 'danger');
        $this->redirect('mantenimiento');
    }

    if ($this->mantenimientoModel->delete($id)) {
        $this->setFlash('Mantenimiento eliminado correctamente', 'success');
    } else {
        $this->setFlash('Error al eliminar', 'danger');
    }
    $this->redirect('mantenimiento');
}

}