<?php
class CombustibleController extends Controller {
    private $combustibleModel;
    private $busModel;

    public function __construct() {
        $this->requireAuth();
        $this->combustibleModel = new Combustible();
        $this->busModel = new Bus();
    }

    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'bus_id' => $_GET['bus_id'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d')
        ];
        
        $resultado = $this->combustibleModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );
        
        $resumen = $this->combustibleModel->getResumen($filtros);
        $buses = $this->busModel->all();
        $estadisticas_bus = $this->combustibleModel->getEstadisticasPorBus(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $this->view('combustible/index', [
            'titulo' => 'Control de Combustible',
            'cargas' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'resumen' => $resumen,
            'buses' => $buses,
            'estadisticas_bus' => $estadisticas_bus,
            'filtros' => $filtros
        ]);
    }

    public function create() {
        $buses = $this->busModel->getDisponibles();
        $usuarioModel = new Usuario();
        $conductores = $usuarioModel->getConductores();
        
        $this->view('combustible/create', [
            'titulo' => 'Nueva Carga de Combustible',
            'buses' => $buses,
            'conductores' => $conductores
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('combustible');
        }

        $data = [
            'bus_id' => (int)$_POST['bus_id'],
            'usuario_id' => $_SESSION['user_id'],
            'conductor_id' => !empty($_POST['conductor_id']) ? (int)$_POST['conductor_id'] : null,
            'tipo_combustible' => $_POST['tipo_combustible'] ?? 'DIESEL',
            'cantidad_galones' => (float)$_POST['cantidad_galones'],
            'precio_galon' => (float)$_POST['precio_galon'],
            'kilometraje_actual' => !empty($_POST['kilometraje_actual']) ? (int)$_POST['kilometraje_actual'] : null,
            'estacion_servicio' => trim($_POST['estacion_servicio'] ?? ''),
            'numero_factura' => trim($_POST['numero_factura'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        $errors = [];
        if (empty($data['bus_id'])) $errors[] = 'Debe seleccionar un bus';
        if ($data['cantidad_galones'] <= 0) $errors[] = 'La cantidad debe ser mayor a 0';
        if ($data['precio_galon'] <= 0) $errors[] = 'El precio debe ser mayor a 0';

        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->redirect('combustible/create');
            return;
        }

        if ($this->combustibleModel->registrar($data)) {
            $this->setFlash('Carga registrada correctamente', 'success');
        } else {
            $this->setFlash('Error al registrar la carga', 'danger');
        }
        $this->redirect('combustible');
    }

public function show($id) {
    $carga = $this->combustibleModel->getById($id); // ✅ Usar getById
    if (!$carga) {
        $this->setFlash('Carga no encontrada', 'danger');
        $this->redirect('combustible');
    }
    
    $this->view('combustible/show', [
        'titulo' => 'Detalle de Carga',
        'carga' => $carga
    ]);
}

    public function delete($id) {
        $this->requireAdmin();
        
        if ($this->combustibleModel->delete($id)) {
            $this->setFlash('Carga eliminada', 'success');
        } else {
            $this->setFlash('Error al eliminar', 'danger');
        }
        $this->redirect('combustible');
    }
}