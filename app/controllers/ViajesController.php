<?php
class ViajesController extends Controller {
    private $viajeModel;
    private $rutaModel;
    private $busModel;

    public function __construct() {
        $this->requireAuth();
        $this->viajeModel = new Viaje();
        $this->rutaModel = new Ruta();
        $this->busModel = new Bus();
    }

    /**
     * ✅ NUEVO: Verifica si el usuario actual tiene caja abierta.
     *    Devuelve el array de la caja o false.
     */
    private function getCajaAbierta() {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT c.id, c.usuario_id, c.fecha_apertura, c.monto_inicial,
                   CONCAT(u.nombre, ' ', u.apellido) AS usuario_nombre
            FROM cajas c
            JOIN usuarios u ON c.usuario_id = u.id
            WHERE c.usuario_id = ? AND c.estado = 'ABIERTA'
            ORDER BY c.fecha_apertura DESC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        return $stmt->fetch() ?: false;
    }

    /**
     * Listar todos los viajes
     */
    public function index() {
        // ✅ Transición perezosa PROGRAMADO -> EN_CURSO por hora de salida
        $this->viajeModel->actualizarEstadosPorHora();

        $origen = $_GET['origen'] ?? '';
        $destino = $_GET['destino'] ?? '';
        $fecha = $_GET['fecha'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        if (!empty($origen) || !empty($destino) || !empty($fecha) || !empty($estado)) {
            $viajes = $this->viajeModel->buscar($origen, $destino, $fecha, $estado);
        } else {
            $viajes = $this->viajeModel->getAllWithDetails();
        }
        
        $this->view('viajes/index', [
            'viajes' => $viajes,
            'origen' => $origen,
            'destino' => $destino,
            'fecha' => $fecha,
            'estado' => $estado,
            'titulo' => 'Gestión de Viajes'
        ]);
    }

    /**
     * ✅ MEJORADO: Formulario de creación de viaje (programación).
     *    Ahora verifica caja abierta y la pasa a la vista.
     *    Se asume que existe una vista propia: views/viajes/create.php
     */
    public function create() {
        $rutas = $this->rutaModel->all();
        $buses = $this->busModel->getDisponibles();
        $conductores = $this->viajeModel->getConductores();
        
        if (empty($conductores)) {
            $this->setFlash('No hay conductores registrados. Cree un usuario con rol CONDUCTOR primero.', 'warning');
        }
        
        $caja_abierta = $this->getCajaAbierta();
        
        $this->view('viajes/create', [
            'rutas'         => $rutas,
            'buses'         => $buses,
            'conductores'   => $conductores,
            'caja_abierta'  => $caja_abierta,
            'titulo'        => 'Nuevo Viaje'
        ]);
    }

    /**
     * Guardar nuevo viaje
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('viajes');
            return;
        }

        $data = [
            'ruta_id'         => (int)($_POST['ruta_id'] ?? 0),
            'bus_id'          => (int)($_POST['bus_id'] ?? 0),
            'usuario_creador' => (int)($_POST['conductor_id'] ?? 0),
            'fecha_salida'    => $this->normalizarFechaHora($_POST['fecha_salida'] ?? '', $_POST['hora_salida'] ?? '00:00'),
            'fecha_llegada'   => $this->normalizarFechaHora($_POST['fecha_llegada'] ?? '', $_POST['hora_llegada'] ?? '00:00'),
            'precio_final'    => (float)($_POST['precio_final'] ?? 0),
            'estado'          => $_POST['estado'] ?? 'PROGRAMADO',
            'observaciones'   => trim($_POST['observaciones'] ?? '')
        ];

        $errors = [];

        if (empty($data['usuario_creador']) || $data['usuario_creador'] <= 0) {
            $errors[] = 'Debe seleccionar un conductor';
        }

        $errors = array_merge($errors, $this->viajeModel->validate($data));

        // Validar BUS disponible
        if (empty($errors) && !$this->viajeModel->busDisponible($data['bus_id'], $data['fecha_salida'], $data['fecha_llegada'])) {
            $conflicto = $this->viajeModel->getConflictoBus($data['bus_id'], $data['fecha_salida'], $data['fecha_llegada']);
            if ($conflicto) {
                $errors[] = "⚠️ El bus está asignado al viaje #{$conflicto['id']} " .
                            "({$conflicto['origen']} → {$conflicto['destino']}) " .
                            "programado del " . date('d/m/Y H:i', strtotime($conflicto['fecha_salida'])) . 
                            " al " . date('d/m/Y H:i', strtotime($conflicto['fecha_llegada'])) . 
                            ". Debe esperar a que se complete o cancelar ese viaje.";
            } else {
                $errors[] = 'El bus seleccionado ya está ocupado en ese horario';
            }
        }

        // Validar CONDUCTOR disponible
        if (empty($errors) && !$this->viajeModel->conductorDisponible($data['usuario_creador'], $data['fecha_salida'], $data['fecha_llegada'])) {
            $conflicto = $this->viajeModel->getConflictoConductor($data['usuario_creador'], $data['fecha_salida'], $data['fecha_llegada']);
            if ($conflicto) {
                $errors[] = "⚠️ El conductor está asignado al viaje #{$conflicto['id']} " .
                            "({$conflicto['origen']} → {$conflicto['destino']}) " .
                            "programado del " . date('d/m/Y H:i', strtotime($conflicto['fecha_salida'])) . 
                            " al " . date('d/m/Y H:i', strtotime($conflicto['fecha_llegada'])) . 
                            ". Debe esperar a que se complete o cancelar ese viaje.";
            } else {
                $errors[] = 'El conductor seleccionado ya está ocupado en ese horario';
            }
        }

        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('viajes/create', [
                'data'         => $data,
                'rutas'        => $this->rutaModel->all(),
                'buses'        => $this->busModel->getDisponibles(),
                'conductores'  => $this->viajeModel->getConductores(),
                'caja_abierta' => $this->getCajaAbierta(),
                'titulo'       => 'Nuevo Viaje'
            ]);
            return;
        }

        try {
            $viaje_id = $this->viajeModel->crearConAsientos($data);
            $this->setFlash('✅ Viaje creado correctamente. Se generaron los asientos automáticamente.', 'success');
            $this->redirect('viajes/show/' . $viaje_id);
        } catch (Exception $e) {
            $this->setFlash('Error al crear el viaje: ' . $e->getMessage(), 'danger');
            $this->redirect('viajes');
        }
    }

    /**
     * Mostrar formulario para editar viaje
     */
    public function edit($id) {
        $viaje = $this->viajeModel->getByIdWithDetails($id);
        if (!$viaje) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('viajes');
            return;
        }

        $this->view('viajes/edit', [
            'viaje'        => $viaje,
            'rutas'        => $this->rutaModel->all(),
            'buses'        => $this->busModel->getDisponibles(),
            'conductores'  => $this->viajeModel->getConductores(),
            'caja_abierta' => $this->getCajaAbierta(),
            'titulo'       => 'Editar Viaje'
        ]);
    }

    /**
     * Actualizar viaje
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('viajes');
            return;
        }

        $viaje = $this->viajeModel->find($id);
        if (!$viaje) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('viajes');
            return;
        }

        $data = [
            'ruta_id'         => (int)($_POST['ruta_id'] ?? 0),
            'bus_id'          => (int)($_POST['bus_id'] ?? 0),
            'usuario_creador' => (int)($_POST['conductor_id'] ?? 0),
            'fecha_salida'    => $this->normalizarFechaHora($_POST['fecha_salida'] ?? '', $_POST['hora_salida'] ?? '00:00'),
            'fecha_llegada'   => $this->normalizarFechaHora($_POST['fecha_llegada'] ?? '', $_POST['hora_llegada'] ?? '00:00'),
            'precio_final'    => (float)($_POST['precio_final'] ?? 0),
            'estado'          => $_POST['estado'] ?? 'PROGRAMADO',
            'observaciones'   => trim($_POST['observaciones'] ?? '')
        ];

        $errors = [];

        if (empty($data['usuario_creador']) || $data['usuario_creador'] <= 0) {
            $errors[] = 'Debe seleccionar un conductor';
        }

        $errors = array_merge($errors, $this->viajeModel->validate($data, false));

        // ✅ No permitir cambiar el bus si ya hay ventas activas
        $bus_cambiado = ((int)$viaje['bus_id'] !== $data['bus_id']);
        if ($bus_cambiado) {
            $estadisticas_actual = $this->viajeModel->getEstadisticas($id);
            $pasajeros_actuales = (int)($estadisticas_actual['total_ventas'] ?? 0);
            if ($pasajeros_actuales > 0) {
                $errors[] = "⚠️ No se puede cambiar el bus: este viaje ya tiene {$pasajeros_actuales} pasaje(s) vendido(s). " .
                            "Los asientos vendidos quedarían inválidos con otro bus. Cree un nuevo viaje si necesita asignar otro bus.";
            }
        }

        // Validar BUS disponible (excluyendo el propio viaje)
        if (empty($errors) && !$this->viajeModel->busDisponible($data['bus_id'], $data['fecha_salida'], $data['fecha_llegada'], $id)) {
            $conflicto = $this->viajeModel->getConflictoBus($data['bus_id'], $data['fecha_salida'], $data['fecha_llegada'], $id);
            if ($conflicto) {
                $errors[] = "⚠️ El bus está asignado al viaje #{$conflicto['id']} " .
                            "({$conflicto['origen']} → {$conflicto['destino']}) " .
                            "programado del " . date('d/m/Y H:i', strtotime($conflicto['fecha_salida'])) . 
                            " al " . date('d/m/Y H:i', strtotime($conflicto['fecha_llegada'])) . 
                            ". Debe esperar a que se complete o cancelar ese viaje.";
            } else {
                $errors[] = 'El bus seleccionado ya está ocupado en ese horario';
            }
        }

        // Validar CONDUCTOR disponible (excluyendo el propio viaje)
        if (empty($errors) && !$this->viajeModel->conductorDisponible($data['usuario_creador'], $data['fecha_salida'], $data['fecha_llegada'], $id)) {
            $conflicto = $this->viajeModel->getConflictoConductor($data['usuario_creador'], $data['fecha_salida'], $data['fecha_llegada'], $id);
            if ($conflicto) {
                $errors[] = "⚠️ El conductor está asignado al viaje #{$conflicto['id']} " .
                            "({$conflicto['origen']} → {$conflicto['destino']}) " .
                            "programado del " . date('d/m/Y H:i', strtotime($conflicto['fecha_salida'])) . 
                            " al " . date('d/m/Y H:i', strtotime($conflicto['fecha_llegada'])) . 
                            ". Debe esperar a que se complete o cancelar ese viaje.";
            } else {
                $errors[] = 'El conductor seleccionado ya está ocupado en ese horario';
            }
        }

        if (!empty($errors)) {
            $this->setFlash(implode('<br>', $errors), 'danger');
            $this->view('viajes/edit', [
                'viaje'        => array_merge(['id' => $id], $data),
                'rutas'        => $this->rutaModel->all(),
                'buses'        => $this->busModel->getDisponibles(),
                'conductores'  => $this->viajeModel->getConductores(),
                'caja_abierta' => $this->getCajaAbierta(),
                'titulo'       => 'Editar Viaje'
            ]);
            return;
        }

        try {
            $this->viajeModel->actualizarConAsientos($id, $data, $bus_cambiado);
            $mensaje = '✅ Viaje actualizado correctamente';
            if ($bus_cambiado) {
                $mensaje .= '. Como cambiaste el bus, los asientos se regeneraron según su nuevo layout.';
            }
            $this->setFlash($mensaje, 'success');
        } catch (Exception $e) {
            $this->setFlash('Error al actualizar el viaje: ' . $e->getMessage(), 'danger');
        }
        $this->redirect('viajes');
    }

    /**
     * Eliminar viaje (solo si no tiene ventas)
     */
    public function delete($id) {
        $viaje = $this->viajeModel->find($id);
        if (!$viaje) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('viajes');
            return;
        }

        $db = Database::getInstance()->getConnection();
        $ventas = $db->prepare("SELECT COUNT(*) FROM ventas_pasajes WHERE viaje_id = ? AND estado = 'ACTIVA'");
        $ventas->execute([$id]);
        if ($ventas->fetchColumn() > 0) {
            $this->setFlash('No se puede eliminar el viaje porque tiene ventas asociadas', 'danger');
            $this->redirect('viajes');
            return;
        }

        try {
            $db->beginTransaction();
            $db->prepare("DELETE FROM asientos WHERE viaje_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM viajes WHERE id = ?")->execute([$id]);
            $db->commit();
            $this->setFlash('Viaje eliminado correctamente', 'success');
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->setFlash('Error al eliminar el viaje', 'danger');
        }
        $this->redirect('viajes');
    }

    /**
     * Ver detalles del viaje
     */
    public function show($id) {
        // ✅ Transición perezosa PROGRAMADO -> EN_CURSO por hora de salida
        $this->viajeModel->actualizarEstadosPorHora();

        $viaje = $this->viajeModel->getByIdWithDetails($id);
        if (!$viaje) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('viajes');
            return;
        }

        $estadisticas = $this->viajeModel->getEstadisticas($id);
        $asientos = $this->viajeModel->getAsientos($id);

        $this->view('viajes/show', [
            'viaje'        => $viaje,
            'estadisticas' => $estadisticas,
            'asientos'     => $asientos,
            'titulo'       => 'Detalle del Viaje'
        ]);
    }

    /**
     * Cambiar estado del viaje
     */
    public function cambiarEstado($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('viajes');
            return;
        }

        $estado = $_POST['estado'] ?? '';
        $estados_validos = ['PROGRAMADO', 'EN_CURSO', 'COMPLETADO', 'CANCELADO'];
        
        if (!in_array($estado, $estados_validos, true)) {
            $this->setFlash('Estado inválido', 'danger');
            $this->redirect('viajes');
            return;
        }
        
        if ($this->viajeModel->cambiarEstado($id, $estado)) {
            $mensaje = 'Estado actualizado a ' . $estado;
            
            if (in_array($estado, ['COMPLETADO', 'CANCELADO'], true)) {
                $mensaje .= '. El bus y el conductor quedaron disponibles para nuevos viajes.';
            }
            
            $this->setFlash($mensaje, 'success');
        } else {
            $this->setFlash('Error al cambiar el estado', 'danger');
        }
        $this->redirect('viajes');
    }

    /**
     * Obtener precio de una ruta (para AJAX)
     */
    public function getPrecioRuta() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['ruta_id'])) {
            echo json_encode(['error' => 'Ruta no especificada']);
            exit;
        }

        $ruta = $this->rutaModel->find((int)$_GET['ruta_id']);
        if ($ruta) {
            echo json_encode(['precio' => $ruta['precio_base']]);
        } else {
            echo json_encode(['error' => 'Ruta no encontrada']);
        }
        exit;
    }

    // ============================================================
    // MÉTODO AUXILIAR: Normalizar fecha + hora
    // ============================================================

    /**
     * ✅ MEJORADO: Normalización robusta de fecha + hora.
     *    Soporta múltiples formatos de entrada:
     *      - 12h: "02:00 p.m.", "2:00 PM", "2:00PM", "02:00 a. m."
     *      - 24h: "14:00", "14:00:00"
     */
    private function normalizarFechaHora($fecha, $hora) {
        $fecha = trim($fecha);
        $hora  = trim($hora);

        if (empty($fecha)) {
            return date('Y-m-d H:i:s');
        }

        // Normalizar variantes de AM/PM
        $hora = str_ireplace(
            ['a. m.', 'a.m.', 'a m.', 'a m', 'am', 'p. m.', 'p.m.', 'p m.', 'p m', 'pm'],
            ['AM', 'AM', 'AM', 'AM', 'AM', 'PM', 'PM', 'PM', 'PM', 'PM'],
            $hora
        );
        $hora = preg_replace('/\s+/', ' ', trim($hora));

        // ✅ Asegurar espacio antes de AM/PM (ej: "2:00PM" → "2:00 PM")
        if (!preg_match('/\s+(AM|PM)$/i', $hora) && preg_match('/(AM|PM)$/i', $hora)) {
            $hora = preg_replace('/(AM|PM)$/i', ' $1', $hora);
        }

        $fecha_hora = $fecha . ' ' . $hora;

        try {
            $dt = new DateTime($fecha_hora);
            return $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $timestamp = strtotime($fecha_hora);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
            return $fecha . ' 00:00:00';
        }
    }
}