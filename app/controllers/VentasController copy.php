<?php
class VentasController extends Controller {
    private $ventaModel;
    private $viajeModel;
    private $clienteModel;

    /**
     * ✅ CONSTANTE: Minutos de tolerancia después de la hora de salida
     * para permitir vender pasajes de viajes que se retrasaron.
     */
    const TOLERANCIA_VENTA_MINUTOS = 30;

    public function __construct() {
        $this->requireAuth();
        $this->ventaModel = new Venta();
        $this->viajeModel = new Viaje();
        $this->clienteModel = new Cliente();
    }

    /**
     * Listar todas las ventas
     */
    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? ''
        ];
        
        $resultado = $this->ventaModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );

        $totales = $this->ventaModel->getTotalesFiltrados($filtros);
        
        $this->view('ventas/index', [
            'ventas' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'filtros' => $filtros,
            'totales' => $totales,
            'titulo' => 'Historial de Ventas'
        ]);
    }

    /**
     * Pantalla principal de venta
     */
       public function create() {
            // ✅ Liberar solo reservas EXPIRADAS (a nivel global)
    $this->viajeModel->liberarReservasExpiradas();

    // ✅ Liberar las reservas del usuario SOLO si:
    //    - No viene con asiento_id (usuario abrió el form desde cero)
    //    - O viene con asiento_id DISTINTO al que tiene reservado actualmente
    if (!empty($_SESSION['user_id'])) {
        $asiento_entrante = (int)($_GET['asiento_id'] ?? 0);

        if ($asiento_entrante === 0) {
            // No viene asiento → liberar TODAS las reservas del usuario
            $this->viajeModel->liberarReservasDeUsuario($_SESSION['user_id']);
        } else {
            // Viene con asiento → liberar solo las reservas que NO sean ese asiento
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE asientos
                SET estado = 'DISPONIBLE',
                    reservado_hasta = NULL,
                    reservado_por_usuario_id = NULL
                WHERE estado = 'RESERVADO'
                  AND reservado_por_usuario_id = ?
                  AND id != ?
            ");
            $stmt->execute([$_SESSION['user_id'], $asiento_entrante]);
        }
    }

    // ✅ CAPA 1: Verificar caja abierta ANTES de mostrar la pantalla
    $cajaModel = new Caja();
    $caja_abierta = $cajaModel->getCajaAbierta($_SESSION['user_id']);

    if (!$caja_abierta) {
        $this->setFlash('Debe abrir una caja antes de registrar ventas', 'warning');
        $this->redirect('caja?redirect=' . urlencode('ventas/create'));
        return;
    }

        $viajes = [];
        $asientos = [];
        $viaje_seleccionado = null;
        $asiento_seleccionado = null;
        $cliente_data = [];
        $errores = [];
        $data_get = ['origen' => '', 'destino' => '', 'fecha' => ''];

        $buscar_activo = isset($_GET['buscar']);

        if (!$buscar_activo && !isset($_GET['viaje_id'])) {
            $viajes = $this->viajeModel->getProximosParaVenta(self::TOLERANCIA_VENTA_MINUTOS);
        }

        if ($buscar_activo) {
            $origen = trim($_GET['origen'] ?? '');
            $destino = trim($_GET['destino'] ?? '');
            $fecha = $_GET['fecha'] ?? '';

            $viajes = $this->viajeModel->buscarParaVenta(
                $origen, $destino, $fecha, self::TOLERANCIA_VENTA_MINUTOS
            );

            if (empty($viajes)) {
                $errores[] = 'No se encontraron viajes disponibles para venta con esos filtros.';
            }

            $data_get = ['origen' => $origen, 'destino' => $destino, 'fecha' => $fecha];
        }

        if (isset($_GET['viaje_id'])) {
            $viaje_id = (int)$_GET['viaje_id'];
            $viaje_seleccionado = $this->viajeModel->getByIdWithDetails($viaje_id);

            if ($viaje_seleccionado) {
                $motivo = $this->validarVendibilidad($viaje_seleccionado);
                if ($motivo !== null) {
                    $errores[] = $motivo;
                }

                $asientos_raw = $this->viajeModel->getAsientos($viaje_id);
                $asientos = array_values(array_filter($asientos_raw, function($a) {
                    return is_array($a);
                }));
            } else {
                $errores[] = 'Viaje no encontrado';
            }
        }

        // CASO 4: Asiento ya reservado (por AJAX o GET directo)
        if (isset($_GET['asiento_id']) && $viaje_seleccionado) {
            $asiento_id = (int)$_GET['asiento_id'];
            $asiento_seleccionado = $asiento_id;

            $this->viajeModel->liberarReservasExpiradas();

            $asientos = $this->viajeModel->getAsientos($viaje_seleccionado['id']);
        }

        // ============================================================
        // CASO 5: POST - Procesar venta
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vender') {

            $caja_abierta = $cajaModel->getCajaAbierta($_SESSION['user_id']);
            if (!$caja_abierta) {
                $this->setFlash('Debe abrir una caja antes de registrar ventas', 'danger');
                $this->redirect('caja?redirect=' . urlencode('ventas/create'));
                return;
            }

            $viaje_id = (int)$_POST['viaje_id'];
            $asiento_id = (int)$_POST['asiento_id'];
            $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';

            $cliente_data = [
                'tipo_documento' => $_POST['tipo_documento'] ?? 'DNI',
                'numero_documento' => trim($_POST['numero_documento']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'email' => trim($_POST['email']),
                'direccion' => trim($_POST['direccion'] ?? '')
            ];

            if (empty($cliente_data['numero_documento']) || empty($cliente_data['nombre']) || empty($cliente_data['apellido'])) {
                $errores[] = 'Los datos del cliente (Documento, Nombre, Apellido) son obligatorios';
            }

            if (empty($viaje_id) || empty($asiento_id)) {
                $errores[] = 'Debe seleccionar un viaje y un asiento';
            }

            $viaje = null;
            if (empty($errores)) {
                $viaje = $this->viajeModel->find($viaje_id);
                if (!$viaje) {
                    $errores[] = 'El viaje seleccionado no existe';
                } else {
                    $motivo = $this->validarVendibilidad($viaje);
                    if ($motivo !== null) {
                        $errores[] = $motivo;
                    }
                }
            }

            if (empty($errores) && !$this->viajeModel->asientoDisponible($viaje_id, $asiento_id, $_SESSION['user_id'] ?? null)) {
                $errores[] = 'El asiento ya fue ocupado, o está siendo reservado por otro vendedor en este momento. Intente con otro.';
            }

            if (empty($errores)) {
                $cliente_id = $this->clienteModel->findOrCreate($cliente_data);

                $precio = (float)$viaje['precio_final'];
                $comprobante = $this->ventaModel->generarComprobante();

                $venta_data = [
                    'viaje_id' => $viaje_id,
                    'cliente_id' => $cliente_id,
                    'usuario_id' => $_SESSION['user_id'] ?? 1,
                    'asiento_id' => $asiento_id,
                    'precio_pagado' => $precio,
                    'metodo_pago' => $metodo_pago,
                    'numero_comprobante' => $comprobante
                ];

                try {
                    $venta_id = $this->ventaModel->registrar($venta_data);

                    // ✅ Registrar el VENDER en reservas_log
                    try {
                        $db = Database::getInstance()->getConnection();
                        $db->prepare(
                            "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion)
                             VALUES (?, ?, ?, 'VENDER')"
                        )->execute([$viaje_id, $asiento_id, $_SESSION['user_id']]);
                    } catch (Exception $e) {
                        if (class_exists('Logger')) {
                            Logger::error('Error al registrar VENDER en reservas_log: ' . $e->getMessage());
                        }
                    }

                    $error_caja = null;
                    try {
                        $cajaModel->registrarMovimiento(
                            $caja_abierta['id'],
                            $_SESSION['user_id'],
                            'INGRESO',
                            "Venta pasaje {$comprobante} — {$viaje['origen']} → {$viaje['destino']} (Asiento {$asiento_id})",
                            $precio,
                            $metodo_pago,
                            $comprobante
                        );
                    } catch (Exception $e) {
                        $error_caja = $e->getMessage();
                        if (class_exists('Logger')) {
                            Logger::error('Error al registrar movimiento de caja: ' . $e->getMessage());
                        }
                    }

                    $email_cliente = $cliente_data['email'] ?? null;
                    $mensaje_venta = '¡Venta realizada con éxito! Comprobante: ' . $comprobante;
                    $tipo_flash = 'success';

                    if (!empty($email_cliente) && filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
                        try {
                            $comprobanteModel = new ComprobanteEmail();
                            $resultado = $comprobanteModel->enviarPorEmail($venta_id, $email_cliente);

                            if ($resultado['success']) {
                                $mensaje_venta .= ' (enviado a ' . $email_cliente . ')';
                            } else {
                                $mensaje_venta .= ' (error al enviar email)';
                                $tipo_flash = 'warning';
                            }
                        } catch (Exception $e) {
                            Logger::error('Error al enviar comprobante automático: ' . $e->getMessage());
                            $tipo_flash = 'info';
                        }
                    }

                    if ($error_caja !== null) {
                        $mensaje_venta .= ' — Atención: no se pudo registrar el movimiento en caja.';
                        $tipo_flash = 'warning';
                    }

                    $this->setFlash($mensaje_venta, $tipo_flash);
                    $this->redirect('ventas/show/' . $venta_id);
                    return;

                } catch (Exception $e) {
                    $errores[] = 'Error al registrar la venta: ' . $e->getMessage();
                }
            }
        }

        $this->view('ventas/create', [
            'titulo' => 'Nueva Venta',
            'viajes' => $viajes,
            'asientos' => $asientos,
            'viaje_seleccionado' => $viaje_seleccionado,
            'asiento_seleccionado' => $asiento_seleccionado,
            'cliente_data' => $cliente_data,
            'errores' => $errores,
            'data_get' => $data_get,
            'caja_abierta' => $caja_abierta
        ]);
    }
    /**
     * Valida si un viaje puede venderse según las reglas de negocio
     */
    private function validarVendibilidad($viaje) {
        if ($viaje['estado'] !== 'PROGRAMADO') {
            $estados_no_vendibles = [
                'EN_CURSO'   => 'Este viaje ya está en curso. No se puede vender.',
                'COMPLETADO' => 'Este viaje ya se completó. No se puede vender.',
                'CANCELADO'  => 'Este viaje fue cancelado. No se puede vender.'
            ];
            return $estados_no_vendibles[$viaje['estado']] 
                ?? "Este viaje no está disponible para venta (estado: {$viaje['estado']}).";
        }
        
        $hora_salida = strtotime($viaje['fecha_salida']);
        $ahora = time();
        
        if ($hora_salida >= $ahora) {
            return null;
        }
        
        $minutos_transcurridos = ($ahora - $hora_salida) / 60;
        
        if ($minutos_transcurridos > self::TOLERANCIA_VENTA_MINUTOS) {
            return "Este viaje ya salió hace más de " . self::TOLERANCIA_VENTA_MINUTOS . 
                   " minutos y no se puede vender.";
        }
        
        return null;
    }

    // ============================================================
    // ✅ NUEVOS ENDPOINTS AJAX — Viajes vencidos
    // ============================================================

    /**
     * AJAX — Detectar viajes vencidos (para el SweetAlert)
     */
    public function viajesVencidos() {
        header('Content-Type: application/json');
        
        try {
            $viajes = $this->viajeModel->getViajesVencidos(self::TOLERANCIA_VENTA_MINUTOS);
            
            echo json_encode([
                'success' => true,
                'tolerancia_minutos' => self::TOLERANCIA_VENTA_MINUTOS,
                'total' => count($viajes),
                'viajes' => $viajes
            ]);
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::error('Error al detectar viajes vencidos: ' . $e->getMessage());
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar viajes vencidos',
                'viajes' => []
            ]);
        }
    }

    /**
     * AJAX — Resolver viajes vencidos
     */
    public function resolverViajesVencidos() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $estado = $_POST['estado'] ?? '';
        $ids = $_POST['ids'] ?? [];
        
        $estados_validos = ['COMPLETADO', 'CANCELADO'];
        if (!in_array($estado, $estados_validos, true)) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            return;
        }
        
        if (empty($ids) || !is_array($ids)) {
            echo json_encode(['success' => false, 'message' => 'Sin viajes seleccionados']);
            return;
        }
        
        try {
            $actualizados = $this->viajeModel->cambiarEstadoMultiple($ids, $estado);
            
            if (class_exists('Logger')) {
                Logger::info("Actualizó {$actualizados} viaje(s) vencido(s) a {$estado}");
            }
            
            echo json_encode([
                'success' => true,
                'actualizados' => $actualizados,
                'message' => "Se actualizaron {$actualizados} viaje(s) a {$estado}"
            ]);
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::error('Error al resolver viajes vencidos: ' . $e->getMessage());
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar los viajes'
            ]);
        }
    }

    // ============================================================
    // OTROS MÉTODOS
    // ============================================================

    public function show($id) {
        $venta = $this->ventaModel->getByIdWithDetails($id);
        if (!$venta) {
            $this->setFlash('Venta no encontrada', 'danger');
            $this->redirect('ventas');
        }
        $this->view('ventas/show', [
            'venta' => $venta,
            'titulo' => 'Detalle de Venta'
        ]);
    }

    public function anular($id) {
        $cajaModel = new Caja();
        if (!$cajaModel->tieneCajaAbierta($_SESSION['user_id'])) {
            $this->setFlash('Debe tener una caja abierta para anular ventas', 'danger');
            $this->redirect('caja');
            return;
        }
        
        if ($this->ventaModel->anular($id)) {
            $this->setFlash('Venta anulada y asiento liberado correctamente', 'warning');
        } else {
            $this->setFlash('Error al anular la venta o ya estaba anulada', 'danger');
        }
        $this->redirect('ventas');
    }

    public function ticket($id) {
        $configPath = __DIR__ . '/../config/facturacion.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }
        
        $venta = $this->ventaModel->getByIdWithDetails($id);
        if (!$venta) {
            die('Venta no encontrada');
        }
        
        $this->viewStandalone('ventas/ticket', [
            'venta' => $venta, 
            'titulo' => 'Ticket ' . $venta['numero_comprobante']
        ]);
    }

    public function enviarEmail($id) {
    header('Content-Type: application/json');

    try {
        $venta = $this->ventaModel->getByIdWithDetails($id);
        if (!$venta) {
            echo json_encode(['success' => false, 'message' => 'Venta no encontrada']);
            return;
        }

        if (empty($venta['email']) || !filter_var($venta['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El cliente no tiene un email válido registrado']);
            return;
        }

        if (!class_exists('FacturaElectronica')) {
            echo json_encode(['success' => false, 'message' => 'Módulo de facturación no disponible']);
            return;
        }

        $facturaModel = new FacturaElectronica();
        $comprobante = $facturaModel->getByVenta($id);

        if (!$comprobante) {
            echo json_encode([
                'success' => false,
                'message' => 'Esta venta no tiene comprobante electrónico emitido. Emita uno primero.'
            ]);
            return;
        }

        $comprobanteModel = new ComprobanteEmail();
        $resultado = $comprobanteModel->enviarPorEmail((int)$comprobante['id'], $venta['email']);

        echo json_encode($resultado);

    } catch (Exception $e) {
        if (class_exists('Logger')) {
            try { Logger::error('Error al reenviar comprobante: ' . $e->getMessage()); }
            catch (Exception $le) { error_log($le->getMessage()); }
        }
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

        // ============================================================
    // SISTEMA DE RESERVAS MULTI-VENDEDOR
    // ============================================================

    /**
     * AJAX — Reservar un asiento.
     */
    public function reservarAsiento() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $viaje_id = (int)($_POST['viaje_id'] ?? 0);
        $asiento_id = (int)($_POST['asiento_id'] ?? 0);
        $usuario_id = (int)($_SESSION['user_id'] ?? 0);

        if (!$viaje_id || !$asiento_id || !$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        // Liberar reservas viejas del usuario antes de crear una nueva
        $this->viajeModel->liberarReservasDeUsuario($usuario_id, $viaje_id);

        $resultado = $this->viajeModel->reservarAsiento($viaje_id, $asiento_id, $usuario_id, 5);
        echo json_encode($resultado);
    }

    /**
     * AJAX — Liberar una reserva específica (solo si es del usuario).
     */
    public function liberarReserva() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $viaje_id = (int)($_POST['viaje_id'] ?? 0);
        $asiento_id = (int)($_POST['asiento_id'] ?? 0);
        $usuario_id = (int)($_SESSION['user_id'] ?? 0);

        if (!$viaje_id || !$asiento_id || !$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $liberado = $this->viajeModel->liberarReserva($viaje_id, $asiento_id, $usuario_id);

        echo json_encode([
            'success' => $liberado,
            'message' => $liberado ? 'Reserva liberada' : 'No se pudo liberar (o no era tu reserva)'
        ]);
    }

    /**
     * AJAX — Liberar TODAS las reservas del usuario actual.
     * Se llama desde beforeunload (sendBeacon) al cerrar la pestaña.
     */
    public function liberarReservaActual() {
        header('Content-Type: application/json');

        $usuario_id = (int)($_SESSION['user_id'] ?? 0);
        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No hay sesión']);
            return;
        }

        $liberadas = $this->viajeModel->liberarReservasDeUsuario($usuario_id);

        echo json_encode([
            'success' => true,
            'liberadas' => $liberadas
        ]);
    }

    /**
     * AJAX — Estado actual de los asientos del viaje.
     * Devuelve también quién reservó y cuántos segundos quedan.
     */
    public function estadoAsientos() {
        header('Content-Type: application/json');

        $viaje_id = (int)($_GET['viaje_id'] ?? 0);
        $usuario_actual = (int)($_SESSION['user_id'] ?? 0);

        if (!$viaje_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $this->viajeModel->liberarReservasExpiradas();

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT a.id, a.numero_asiento, a.estado, a.disponible,
                       a.reservado_hasta, a.reservado_por_usuario_id,
                       CONCAT(u.nombre, ' ', u.apellido) AS reservado_por_nombre,
                       TIMESTAMPDIFF(SECOND, NOW(), a.reservado_hasta) AS segundos_restantes
                FROM asientos a
                LEFT JOIN usuarios u ON a.reservado_por_usuario_id = u.id
                WHERE a.viaje_id = ?
                ORDER BY a.numero_asiento ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$viaje_id]);
        $asientos = $stmt->fetchAll();

        $resultado = [];
        foreach ($asientos as $a) {
            $estado = $a['estado'] ?? ($a['disponible'] ? 'DISPONIBLE' : 'OCUPADO');

            if ($estado === 'RESERVADO' && (int)($a['segundos_restantes'] ?? 0) <= 0) {
                $estado = 'DISPONIBLE';
            }

            $resultado[] = [
                'id' => (int)$a['id'],
                'numero_asiento' => $a['numero_asiento'],
                'estado' => $estado,
                'disponible' => (int)$a['disponible'],
                'reservado_por_mi' => ((int)$a['reservado_por_usuario_id'] === $usuario_actual),
                'reservado_por_nombre' => $a['reservado_por_nombre'],
                'segundos_restantes' => max(0, (int)($a['segundos_restantes'] ?? 0))
            ];
        }

        echo json_encode([
            'success' => true,
            'asientos' => $resultado,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}