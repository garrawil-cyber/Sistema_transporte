<?php
class RutasController extends Controller {
    private $rutaModel;

    public function __construct() {
        $this->requireAuth(); // 🔒 Proteger
        $this->rutaModel = new Ruta();
    }

    public function index() {
        $rutas = $this->rutaModel->all();
        $this->view('rutas/index', ['rutas' => $rutas, 'titulo' => 'Lista de Rutas']);
    }

    public function create() {
        $this->view('rutas/create', ['titulo' => 'Nueva Ruta']);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('rutas');
        }

        $data = [
            'origen' => trim($_POST['origen']),
            'destino' => trim($_POST['destino']),
            'distancia_km' => $_POST['distancia_km'] ?? 0,
            'duracion_estimada' => $_POST['duracion_estimada'] ?? null,
            'precio_base' => $_POST['precio_base'] ?? 0,
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        $errors = $this->rutaModel->validate($data);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('rutas/create', ['data' => $data, 'titulo' => 'Nueva Ruta']);
            return;
        }

        if ($this->rutaModel->create($data)) {
            $this->setFlash('Ruta creada exitosamente', 'success');
        } else {
            $this->setFlash('Error al crear la ruta', 'danger');
        }
        $this->redirect('rutas');
    }

    public function edit($id) {
        $ruta = $this->rutaModel->find($id);
        if (!$ruta) {
            $this->setFlash('Ruta no encontrada', 'danger');
            $this->redirect('rutas');
        }
        $this->view('rutas/edit', ['ruta' => $ruta, 'titulo' => 'Editar Ruta']);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('rutas');
        }

        $data = [
            'origen' => trim($_POST['origen']),
            'destino' => trim($_POST['destino']),
            'distancia_km' => $_POST['distancia_km'] ?? 0,
            'duracion_estimada' => $_POST['duracion_estimada'] ?? null,
            'precio_base' => $_POST['precio_base'] ?? 0,
            'activo' => isset($_POST['activo']) ? 1 : 0
        ];

        $errors = $this->rutaModel->validate($data);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('rutas/edit', ['ruta' => (object)array_merge(['id' => $id], $data), 'titulo' => 'Editar Ruta']);
            return;
        }

        if ($this->rutaModel->update($id, $data)) {
            $this->setFlash('Ruta actualizada correctamente', 'success');
        } else {
            $this->setFlash('Error al actualizar la ruta', 'danger');
        }
        $this->redirect('rutas');
    }

    public function delete($id) {
        if ($this->rutaModel->delete($id)) {
            $this->setFlash('Ruta eliminada', 'success');
        } else {
            $this->setFlash('Error al eliminar la ruta', 'danger');
        }
        $this->redirect('rutas');
    }

    /**
 * Ver detalle de la ruta
 */
public function show($id) {
    $ruta = $this->rutaModel->find($id);
    if (!$ruta) {
        $this->setFlash('Ruta no encontrada', 'danger');
        $this->redirect('rutas');
    }

    // Obtener viajes que usan esta ruta
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT v.*, b.placa, r.origen, r.destino,
                   (SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = v.id AND estado = 'ACTIVA') as pasajeros
            FROM viajes v
            JOIN buses b ON v.bus_id = b.id
            JOIN rutas r ON v.ruta_id = r.id
            WHERE v.ruta_id = ?
            ORDER BY v.fecha_salida DESC
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    $viajes = $stmt->fetchAll();

    // Estadísticas
    $stmt = $db->prepare("SELECT 
                            COUNT(*) as total_viajes,
                            COALESCE(SUM(vp.precio_pagado), 0) as total_recaudado
                          FROM viajes v
                          LEFT JOIN ventas_pasajes vp ON v.id = vp.viaje_id AND vp.estado = 'ACTIVA'
                          WHERE v.ruta_id = ?");
    $stmt->execute([$id]);
    $estadisticas = $stmt->fetch();

    $this->view('rutas/show', [
        'titulo' => 'Detalle de la Ruta',
        'ruta' => $ruta,
        'viajes' => $viajes,
        'estadisticas' => $estadisticas
    ]);
}

}