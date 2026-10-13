<?php
class ClientesController extends Controller {
    private $clienteModel;

    public function __construct() {
        $this->requireAuth(); // 🔒 Proteger todas las acciones
        $this->clienteModel = new Cliente();
    }

    /**
     * Listar todos los clientes
     */
    public function index() {
        $buscar = $_GET['buscar'] ?? '';
        
        if (!empty($buscar)) {
            $clientes = $this->clienteModel->buscar($buscar);
        } else {
            $clientes = $this->clienteModel->getAllWithDetails();
        }
        
        $this->view('clientes/index', [
            'clientes' => $clientes,
            'buscar' => $buscar,
            'titulo' => 'Gestión de Clientes'
        ]);
    }

    /**
     * Mostrar formulario para crear nuevo cliente
     */
    public function create() {
        $this->view('clientes/create', [
            'titulo' => 'Nuevo Cliente'
        ]);
    }

    /**
     * Guardar nuevo cliente
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('clientes');
            return;
        }

        $data = [
            'tipo_documento' => $_POST['tipo_documento'] ?? 'DNI',
            'numero_documento' => trim($_POST['numero_documento']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'telefono' => trim($_POST['telefono']),
            'email' => trim($_POST['email']),
            'direccion' => trim($_POST['direccion'])
        ];

        // Validar
        $errors = $this->clienteModel->validate($data);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('clientes/create', ['data' => $data, 'titulo' => 'Nuevo Cliente']);
            return;
        }

        // Guardar
        if ($this->clienteModel->create($data)) {
            $this->setFlash('Cliente registrado correctamente', 'success');
        } else {
            $this->setFlash('Error al registrar el cliente', 'danger');
        }
        $this->redirect('clientes');
    }

    /**
     * Mostrar formulario para editar cliente
     */
    public function edit($id) {
        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            $this->setFlash('Cliente no encontrado', 'danger');
            $this->redirect('clientes');
            return;
        }

        $this->view('clientes/edit', [
            'cliente' => $cliente,
            'titulo' => 'Editar Cliente'
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('clientes');
            return;
        }

        $cliente = $this->clienteModel->find($id);
        if (!$cliente) {
            $this->setFlash('Cliente no encontrado', 'danger');
            $this->redirect('clientes');
            return;
        }

        $data = [
            'id' => $id,
            'tipo_documento' => $_POST['tipo_documento'] ?? 'DNI',
            'numero_documento' => trim($_POST['numero_documento']),
            'nombre' => trim($_POST['nombre']),
            'apellido' => trim($_POST['apellido']),
            'telefono' => trim($_POST['telefono']),
            'email' => trim($_POST['email']),
            'direccion' => trim($_POST['direccion'])
        ];

        // Validar
        $errors = $this->clienteModel->validate($data);
        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('clientes/edit', ['cliente' => array_merge(['id' => $id], $data), 'titulo' => 'Editar Cliente']);
            return;
        }

        // Actualizar
        if ($this->clienteModel->update($id, $data)) {
            $this->setFlash('Cliente actualizado correctamente', 'success');
        } else {
            $this->setFlash('Error al actualizar el cliente', 'danger');
        }
        $this->redirect('clientes');
    }

    /**
     * Eliminar cliente
     */
    public function delete($id) {
    $cliente = $this->clienteModel->find($id);
    if (!$cliente) {
        $this->setFlash('Cliente no encontrado', 'danger');
        $this->redirect('clientes');
        return;
    }

    // Verificar si tiene ventas asociadas
    $db = Database::getInstance()->getConnection(); // ✅ Obtener conexión
    $ventas = $db->prepare("SELECT COUNT(*) FROM ventas_pasajes WHERE cliente_id = ?");
    $ventas->execute([$id]);
    if ($ventas->fetchColumn() > 0) {
        $this->setFlash('No se puede eliminar el cliente porque tiene ventas asociadas', 'danger');
        $this->redirect('clientes');
        return;
    }

    if ($this->clienteModel->delete($id)) {
        $this->setFlash('Cliente eliminado correctamente', 'success');
    } else {
        $this->setFlash('Error al eliminar el cliente', 'danger');
    }
    $this->redirect('clientes');
}

    /**
     * Ver detalles del cliente con estadísticas
     */
    public function show($id) {
        $cliente = $this->clienteModel->getByIdWithStats($id);
        if (!$cliente) {
            $this->setFlash('Cliente no encontrado', 'danger');
            $this->redirect('clientes');
            return;
        }

        // Obtener historial de compras (últimas 10)
        $historial = $this->getHistorialCompras($id);

        $this->view('clientes/show', [
            'cliente' => $cliente,
            'historial' => $historial,
            'titulo' => 'Detalle del Cliente'
        ]);
    }

    /**
     * Obtener historial de compras del cliente
     */
   private function getHistorialCompras($id) {
    $db = Database::getInstance()->getConnection(); // ✅ Obtener conexión
    $sql = "SELECT vp.*, 
                   r.origen, r.destino, 
                   v.fecha_salida,
                   a.numero_asiento,
                   vp.fecha_venta
            FROM ventas_pasajes vp
            JOIN viajes v ON vp.viaje_id = v.id
            JOIN rutas r ON v.ruta_id = r.id
            JOIN asientos a ON vp.asiento_id = a.id
            WHERE vp.cliente_id = ? AND vp.estado = 'ACTIVA'
            ORDER BY vp.fecha_venta DESC
            LIMIT 10";
    $stmt = $db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetchAll();
}

    /**
     * Buscar cliente por DNI (para AJAX)
     */
    public function buscarPorDni() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['dni'])) {
            $this->redirect('clientes');
            return;
        }

        $dni = trim($_GET['dni']);
        $cliente = $this->clienteModel->findByDocumento($dni);
        
        if ($cliente) {
            header('Content-Type: application/json');
            echo json_encode($cliente);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cliente no encontrado']);
        }
        exit;
    }

    /**
 * Búsqueda AJAX de clientes (devuelve HTML parcial)
 */
public function buscarAjax() {
    $buscar = $_GET['buscar'] ?? '';
    $pagina = (int)($_GET['pagina'] ?? 1);
    $por_pagina = 15;
    
    $filtros = ['buscar' => $buscar];
    
    $resultado = $this->clienteModel->getPaginated($pagina, $por_pagina, $filtros);
    
    // Variables que usará la vista parcial
    $clientes = $resultado['datos'];
    $paginator = $resultado['paginator'];
    $filtros = $filtros;
    
    // Cargar solo la vista parcial (sin layout)
    include __DIR__ . '/../views/clientes/_tabla.php';
    exit;
}
}