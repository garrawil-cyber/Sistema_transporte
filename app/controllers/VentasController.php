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
        $this->ventaModel   = new Venta();
        $this->viajeModel   = new Viaje();
        $this->clienteModel = new Cliente();
    }

    // ============================================================
    // LISTADO
    // ============================================================

public function index()
{
    $paginacion = $this->getPaginacion();

    $filtros = [
        'buscar'       => $_GET['buscar']       ?? '',
        'estado'       => $_GET['estado']       ?? '',
        'vendedor_id'  => $_GET['vendedor_id']  ?? '',  // ✅ NUEVO
        'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
        'fecha_fin'    => $_GET['fecha_fin']    ?? ''
    ];

    $resultado = $this->ventaModel->getPaginated(
        $paginacion['pagina'],
        $paginacion['por_pagina'],
        $filtros
    );

    $totales = $this->ventaModel->getTotalesFiltrados($filtros);

    // ✅ NUEVO: lista de vendedores para el filtro
    $vendedores = $this->ventaModel->getVendedoresConVentas();

    $this->view('ventas/index', [
        'ventas'     => $resultado['datos'],
        'paginator'  => $resultado['paginator'],
        'filtros'    => $filtros,
        'totales'    => $totales,
        'vendedores' => $vendedores,
        'titulo'     => 'Historial de Ventas'
    ]);
}
    // ============================================================
    // PANTALLA PRINCIPAL DE VENTA
    // ============================================================

    public function create() {
        // ✅ Liberar reservas expiradas
        $this->viajeModel->liberarReservasExpiradas();

        // ✅ Liberar reservas del usuario según el contexto
        if (!empty($_SESSION['user_id'])) {
            $asiento_entrante = (int)($_GET['asiento_id'] ?? 0);

            if ($asiento_entrante === 0) {
                $this->viajeModel->liberarReservasDeUsuario($_SESSION['user_id']);
            } else {
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

        // ✅ Verificar caja abierta
        $cajaModel    = new Caja();
        $caja_abierta = $cajaModel->getCajaAbierta($_SESSION['user_id']);

        if (!$caja_abierta) {
            $this->setFlash('Debe abrir una caja antes de registrar ventas', 'warning');
            $this->redirect('caja?redirect=' . urlencode('ventas/create'));
            return;
        }

        $viajes               = [];
        $asientos             = [];
        $viaje_seleccionado   = null;
        $asiento_seleccionado = null;
        $cliente_data         = [];
        $errores              = [];
        $data_get             = ['origen' => '', 'destino' => '', 'fecha' => ''];

        $buscar_activo = isset($_GET['buscar']);

        // ---------- Viajes recientes ----------
        if (!$buscar_activo && !isset($_GET['viaje_id'])) {
            $viajes = $this->viajeModel->getProximosParaVenta(self::TOLERANCIA_VENTA_MINUTOS);
        }

        // ---------- Buscar viajes ----------
        if ($buscar_activo) {
            $origen  = trim($_GET['origen']  ?? '');
            $destino = trim($_GET['destino'] ?? '');
            $fecha   = $_GET['fecha'] ?? '';

            $viajes = $this->viajeModel->buscarParaVenta(
                $origen, $destino, $fecha, self::TOLERANCIA_VENTA_MINUTOS
            );

            if (empty($viajes)) {
                $errores[] = 'No se encontraron viajes disponibles para venta con esos filtros.';
            }

            $data_get = ['origen' => $origen, 'destino' => $destino, 'fecha' => $fecha];
        }

        // ---------- Viaje seleccionado ----------
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

        // ---------- Asiento seleccionado ----------
        if (isset($_GET['asiento_id']) && $viaje_seleccionado) {
            $asiento_id = (int)$_GET['asiento_id'];
            $asiento_seleccionado = $asiento_id;

            $this->viajeModel->liberarReservasExpiradas();
            $asientos = $this->viajeModel->getAsientos($viaje_seleccionado['id']);
        }

        // ============================================================
        // POST - Procesar venta
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['action'])
            && $_POST['action'] === 'vender'
        ) {
            // 1. Re-verificar caja abierta
            $caja_abierta = $cajaModel->getCajaAbierta($_SESSION['user_id']);
            if (!$caja_abierta) {
                $this->setFlash('Debe abrir una caja antes de registrar ventas', 'danger');
                $this->redirect('caja?redirect=' . urlencode('ventas/create'));
                return;
            }

            // 2. Recoger datos del POST
            $viaje_id    = (int)($_POST['viaje_id']   ?? 0);
            $asiento_id  = (int)($_POST['asiento_id'] ?? 0);
            $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';

            $cliente_data = [
                'tipo_documento'   => $_POST['tipo_documento'] ?? 'DNI',
                'numero_documento' => trim($_POST['numero_documento'] ?? ''),
                'nombre'           => trim($_POST['nombre']           ?? ''),
                'apellido'         => trim($_POST['apellido']         ?? ''),
                'telefono'         => trim($_POST['telefono']         ?? ''),
                'email'            => trim($_POST['email']            ?? ''),
                'direccion'        => trim($_POST['direccion']        ?? '')
            ];

            // 3. Validación básica del cliente
            if (empty($cliente_data['numero_documento'])
                || empty($cliente_data['nombre'])
                || empty($cliente_data['apellido'])
            ) {
                $errores[] = 'Los datos del cliente (Documento, Nombre, Apellido) son obligatorios';
            }

            // 4. Validación básica del viaje/asiento
            if (empty($viaje_id) || empty($asiento_id)) {
                $errores[] = 'Debe seleccionar un viaje y un asiento';
            }

            // ============================================================
            // ✅ VALIDAR EL VIAJE ANTES DE USARLO
            // ============================================================
            $viaje = null;
            if (empty($errores)) {
                $viaje = $this->viajeModel->getByIdWithDetails($viaje_id);

                if (!$viaje) {
                    $errores[] = 'El viaje seleccionado no existe';
                } else {
                    $motivo = $this->validarVendibilidad($viaje);
                    if ($motivo !== null) {
                        $errores[] = $motivo;
                    }
                }
            }

            // 5. Validar asiento disponible
            if (empty($errores)
                && !$this->viajeModel->asientoDisponible($viaje_id, $asiento_id, $_SESSION['user_id'] ?? null)
            ) {
                $errores[] = 'El asiento ya fue ocupado, o está siendo reservado por otro vendedor en este momento. Intente con otro.';
            }

            // ============================================================
            // OBTENER/CREAR CLIENTE
            // ============================================================
            $cliente_id = null;
            if (empty($errores)) {
                try {
                    $cliente_id = $this->clienteModel->findOrCreate($cliente_data);
                } catch (Exception $e) {
                    $errores[] = 'Error al registrar el cliente: ' . $e->getMessage();
                    $cliente_id = null;
                }

                if (empty($errores)) {
                    $cliente_id = (int)$cliente_id;

                    if ($cliente_id <= 0) {
                        $errores[] = "Error crítico: no se pudo obtener un ID de cliente válido";
                    } else {
                        $cliente_existe = $this->clienteModel->find($cliente_id);
                        if (!$cliente_existe) {
                            $errores[] = "Error crítico: el cliente ID {$cliente_id} no existe en BD";
                        }
                    }
                }
            }

            // ============================================================
            // ✅ REGISTRAR VENTA (con comprobante generado internamente)
            // ============================================================
            if (empty($errores)) {
                $precio = (float)$viaje['precio_final'];

                // ✅ FIX: NO generamos $comprobante aquí.
                //    Venta::registrar() lo generará DENTRO de la transacción.
                //    Esto evita quemar correlativos si algo falla después.
                $venta_data = [
                    'viaje_id'      => $viaje_id,
                    'cliente_id'    => $cliente_id,
                    'usuario_id'    => $_SESSION['user_id'] ?? 1,
                    'asiento_id'    => $asiento_id,
                    'precio_pagado' => $precio,
                    'metodo_pago'   => $metodo_pago
                    // 'numero_comprobante' se genera dentro de registrar()
                ];

                try {
                    $venta_id = $this->ventaModel->registrar($venta_data);

                    // ✅ Recuperar la venta recién creada para obtener su numero_comprobante
                    $venta_creada  = $this->ventaModel->find($venta_id);
                    $comprobante   = $venta_creada['numero_comprobante'] ?? 'N/A';

                    // ---------- Registrar VENDER en reservas_log ----------
                    try {
                        $db = Database::getInstance()->getConnection();
                        $db->prepare(
                            "INSERT INTO reservas_log (viaje_id, asiento_id, usuario_id, accion)
                             VALUES (?, ?, ?, 'VENDER')"
                        )->execute([$viaje_id, $asiento_id, $_SESSION['user_id']]);
                    } catch (Exception $e) {
                        if (class_exists('Logger')) {
                            try { Logger::error('Error en reservas_log VENDER: ' . $e->getMessage()); }
                            catch (Exception $le) { error_log($le->getMessage()); }
                        }
                    }

                    // ---------- Registrar movimiento en caja ----------
                    $error_caja = null;
                    try {
                        $cajaModel->registrarMovimiento(
                            $caja_abierta['id'],
                            $_SESSION['user_id'],
                            'INGRESO',
                            "Venta pasaje {$comprobante} — " . ($viaje['origen'] ?? '') . " → " . ($viaje['destino'] ?? '') . " (Asiento {$asiento_id})",
                            $precio,
                            $metodo_pago,
                            $comprobante
                        );
                    } catch (Exception $e) {
                        $error_caja = $e->getMessage();
                        if (class_exists('Logger')) {
                            try { Logger::error('Error en caja: ' . $e->getMessage()); }
                            catch (Exception $le) { error_log($le->getMessage()); }
                        }
                    }

                    // ============================================================
                    // EMITIR COMPROBANTE ELECTRÓNICO
                    // ============================================================
                    $email_cliente = $cliente_data['email'] ?? null;
                    $mensaje_venta = '¡Venta realizada con éxito! Comprobante: ' . $comprobante;
                    $tipo_flash    = 'success';

                    // 1. Emitir comprobante electrónico
                    $comprobante_id = null;
                    try {
                        if (class_exists('FacturaElectronica')) {
                            $facturaModel = new FacturaElectronica();
                            $emision = $facturaModel->emitir($venta_id, 'BOLETA');

                            if (!empty($emision['success'])) {
                                $comprobanteGuardado = $facturaModel->getByVenta($venta_id, 'BOLETA');
                                if ($comprobanteGuardado) {
                                    $comprobante_id = (int)$comprobanteGuardado['id'];
                                }
                            } else {
                                $mensaje_venta .= ' — Atención: ' . ($emision['message'] ?? 'No se pudo emitir el comprobante');
                                $tipo_flash = 'warning';
                            }
                        }
                    } catch (Exception $e) {
                        if (class_exists('Logger')) {
                            try { Logger::error('Error al emitir comprobante: ' . $e->getMessage()); }
                            catch (Exception $le) { error_log($le->getMessage()); }
                        }
                        $mensaje_venta .= ' — Atención: error al emitir comprobante';
                        $tipo_flash = 'warning';
                    }

                    // 2. Enviar email
                    if ($comprobante_id) {
                        if (!empty($email_cliente) && filter_var($email_cliente, FILTER_VALIDATE_EMAIL)) {
                            try {
                                $comprobanteModel = new ComprobanteEmail();
                                $resultado = $comprobanteModel->enviarPorEmail($comprobante_id, $email_cliente);

                                if (!empty($resultado['success'])) {
                                    $mensaje_venta .= ' (enviado a ' . $email_cliente . ')';
                                } else {
                                    $mensaje_venta .= ' (error al enviar email)';
                                    $tipo_flash = 'warning';
                                }
                            } catch (Exception $e) {
                                if (class_exists('Logger')) {
                                    try { Logger::error('Error al enviar comprobante: ' . $e->getMessage()); }
                                    catch (Exception $le) { error_log($le->getMessage()); }
                                }
                                $mensaje_venta .= ' (error al enviar email)';
                                $tipo_flash = 'warning';
                            }
                        } else {
                            $mensaje_venta .= ' — El comprobante se emitió. El cliente no tiene email, no se envió.';
                            $tipo_flash = 'info';
                        }
                    } else {
                        $mensaje_venta .= ' — No se pudo emitir el comprobante electrónico.';
                        $tipo_flash = 'warning';
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

        // Renderizar vista
        $this->view('ventas/create', [
            'titulo'               => 'Nueva Venta',
            'viajes'               => $viajes,
            'asientos'             => $asientos,
            'viaje_seleccionado'   => $viaje_seleccionado,
            'asiento_seleccionado' => $asiento_seleccionado,
            'cliente_data'         => $cliente_data,
            'errores'              => $errores,
            'data_get'             => $data_get,
            'caja_abierta'         => $caja_abierta
        ]);
    }

    // ============================================================
    // VALIDACIÓN DE VENDIBILIDAD
    // ============================================================

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
        $ahora       = time();

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
    // ENDPOINTS AJAX — Viajes vencidos
    // ============================================================

    public function viajesVencidos() {
        header('Content-Type: application/json');

        try {
            $viajes = $this->viajeModel->getViajesVencidos(self::TOLERANCIA_VENTA_MINUTOS);

            echo json_encode([
                'success'            => true,
                'tolerancia_minutos' => self::TOLERANCIA_VENTA_MINUTOS,
                'total'              => count($viajes),
                'viajes'             => $viajes
            ]);
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al detectar viajes vencidos: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error al consultar viajes vencidos',
                'viajes'  => []
            ]);
        }
    }

    public function resolverViajesVencidos() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $estado = $_POST['estado'] ?? '';
        $ids    = $_POST['ids']    ?? [];

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
                try { Logger::info("Actualizó {$actualizados} viaje(s) vencido(s) a {$estado}"); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }

            echo json_encode([
                'success'      => true,
                'actualizados' => $actualizados,
                'message'      => "Se actualizaron {$actualizados} viaje(s) a {$estado}"
            ]);
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al resolver viajes vencidos: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar los viajes'
            ]);
        }
    }

    // ============================================================
    // DETALLE / ANULACIÓN / TICKET
    // ============================================================

    public function show($id) {
        $venta = $this->ventaModel->getByIdWithDetails($id);
        if (!$venta) {
            $this->setFlash('Venta no encontrada', 'danger');
            $this->redirect('ventas');
            return;
        }
        $this->view('ventas/show', [
            'venta'  => $venta,
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
            'venta'  => $venta,
            'titulo' => 'Ticket ' . $venta['numero_comprobante']
        ]);
    }

    // ============================================================
    // REENVIAR COMPROBANTE POR EMAIL
    // ============================================================

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
            $comprobante  = $facturaModel->getByVenta($id);

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

    public function reservarAsiento() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $viaje_id   = (int)($_POST['viaje_id']   ?? 0);
        $asiento_id = (int)($_POST['asiento_id'] ?? 0);
        $usuario_id = (int)($_SESSION['user_id'] ?? 0);

        if (!$viaje_id || !$asiento_id || !$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $this->viajeModel->liberarReservasDeUsuario($usuario_id, $viaje_id);

        $resultado = $this->viajeModel->reservarAsiento($viaje_id, $asiento_id, $usuario_id, 5);
        echo json_encode($resultado);
    }

    public function liberarReserva() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $viaje_id   = (int)($_POST['viaje_id']   ?? 0);
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

    public function liberarReservaActual() {
        header('Content-Type: application/json');

        $usuario_id = (int)($_SESSION['user_id'] ?? 0);
        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'No hay sesión']);
            return;
        }

        $liberadas = $this->viajeModel->liberarReservasDeUsuario($usuario_id);

        echo json_encode([
            'success'   => true,
            'liberadas' => $liberadas
        ]);
    }

    public function estadoAsientos() {
        header('Content-Type: application/json');

        $viaje_id       = (int)($_GET['viaje_id']    ?? 0);
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
                'id'                   => (int)$a['id'],
                'numero_asiento'       => $a['numero_asiento'],
                'estado'               => $estado,
                'disponible'           => (int)$a['disponible'],
                'reservado_por_mi'     => ((int)$a['reservado_por_usuario_id'] === $usuario_actual),
                'reservado_por_nombre' => $a['reservado_por_nombre'],
                'segundos_restantes'   => max(0, (int)($a['segundos_restantes'] ?? 0))
            ];
        }

        echo json_encode([
            'success'   => true,
            'asientos'  => $resultado,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
 * Generar PDF del listado de ventas con filtros aplicados
 * 
 * @return void Descarga directa del PDF
 */

public function pdf()
{
    // 1. Filtros
    $filtros = [
        'buscar'       => $_GET['buscar']       ?? '',
        'estado'       => $_GET['estado']       ?? '',
        'vendedor_id'  => $_GET['vendedor_id']  ?? '',
        'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
        'fecha_fin'    => $_GET['fecha_fin']    ?? ''
    ];

    // 2. Ventas
    $ventas = $this->ventaModel->getAllWithFiltros($filtros);

    // 3. Totales
    $totales = $this->ventaModel->getTotalesFiltrados($filtros);

    // ✅ 4. CALCULAR $por_metodo y $por_estado (ANTES de usarlos)
    $por_metodo = [];
    $por_estado = [];
    foreach ($ventas as $v) {
        $metodo = $v['metodo_pago'] ?? 'OTRO';
        $estado = $v['estado'] ?? 'ACTIVA';
        $por_metodo[$metodo] = ($por_metodo[$metodo] ?? 0) + (float)($v['precio_pagado'] ?? 0);
        $por_estado[$estado] = ($por_estado[$estado] ?? 0) + 1;
    }

    // ✅ 5. Obtener nombre del vendedor
    $vendedor_nombre = '';
    if (!empty($filtros['vendedor_id'])) {
        foreach ($this->ventaModel->getVendedoresConVentas() as $v) {
            if ((int)$v['id'] === (int)$filtros['vendedor_id']) {
                $vendedor_nombre = $v['nombre_completo'];
                break;
            }
        }
    }

    // 6. Verificar Dompdf
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    }

    if (!class_exists('Dompdf\Dompdf')) {
        $this->setFlash('La librería Dompdf no está instalada.', 'danger');
        $this->redirect('ventas');
        return;
    }

    // ✅ 7. Generar HTML (ahora sí con todas las variables definidas)
    $html = $this->generarHtmlPdfListado(
        $ventas,
        $totales,
        $filtros,
        $por_metodo,
        $por_estado,
        $vendedor_nombre
    );

    // 8. Generar PDF
    try {
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);

        $dompdf = new \Dompdf\Dompdf($options);

        // Registrar fuentes DejaVu
        $fontMetrics = $dompdf->getFontMetrics();
        $fontPath = __DIR__ . '/../../vendor/dompdf/dompdf/lib/fonts/';

        if (file_exists($fontPath . 'DejaVuSans.ttf')) {
            $fontMetrics->registerFont(
                ['family' => 'DejaVu Sans', 'style' => 'normal', 'weight' => 'normal'],
                $fontPath . 'DejaVuSans.ttf'
            );
        }
        if (file_exists($fontPath . 'DejaVuSans-Bold.ttf')) {
            $fontMetrics->registerFont(
                ['family' => 'DejaVu Sans', 'style' => 'normal', 'weight' => 'bold'],
                $fontPath . 'DejaVuSans-Bold.ttf'
            );
        }
        if (file_exists($fontPath . 'DejaVuSans-Oblique.ttf')) {
            $fontMetrics->registerFont(
                ['family' => 'DejaVu Sans', 'style' => 'italic', 'weight' => 'normal'],
                $fontPath . 'DejaVuSans-Oblique.ttf'
            );
        }

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Listado_Ventas_' . date('Y-m-d_His') . '.pdf';
        $pdfContent = $dompdf->output();

        // ✅ Validaciones de seguridad
        if (empty($pdfContent)) {
            throw new Exception('El PDF generado está vacío');
        }
        if (substr($pdfContent, 0, 5) !== '%PDF-') {
            throw new Exception('El archivo generado no es un PDF válido');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $pdfContent;
        exit;

    } catch (Exception $e) {
        if (class_exists('Logger')) {
            try { Logger::error('Error generando PDF de ventas: ' . $e->getMessage()); }
            catch (Exception $le) { error_log($le->getMessage()); }
        }
        $this->setFlash('Error al generar el PDF: ' . $e->getMessage(), 'danger');
        $this->redirect('ventas');
    }
}

/**
 * Generar HTML completo del PDF del listado de ventas
 * 
 * ✅ FIX: Usa entidades HTML (&#8594;, &mdash;, &middot;) en lugar de caracteres
 *    Unicode crudos para evitar que Dompdf los reemplace por "?"
 */
private function generarHtmlPdfListado($ventas, $totales, $filtros, $por_metodo, $por_estado, $vendedor_nombre = '')
{
    $empresa_nombre = defined('FACTURACION_RAZON_SOCIAL') ? FACTURACION_RAZON_SOCIAL : 'Sistema de Transporte';
    $empresa_ruc    = defined('FACTURACION_RUC')          ? FACTURACION_RUC          : '';
    $empresa_dir    = defined('FACTURACION_DIRECCION')    ? FACTURACION_DIRECCION    : '';
    $empresa_tel    = defined('FACTURACION_TELEFONO')     ? FACTURACION_TELEFONO     : '';
    $empresa_email  = defined('FACTURACION_EMAIL')        ? FACTURACION_EMAIL        : '';

    $simbolo = 'S/';

    // Construir la lista de filtros aplicados
    $filtros_aplicados = [];
    if (!empty($filtros['buscar']))       $filtros_aplicados[] = 'Búsqueda: "' . htmlspecialchars($filtros['buscar']) . '"';
    if (!empty($filtros['estado']))       $filtros_aplicados[] = 'Estado: ' . htmlspecialchars($filtros['estado']);
    if (!empty($filtros['vendedor_id']))  $filtros_aplicados[] = 'Vendedor: ' . htmlspecialchars($vendedor_nombre);
    if (!empty($filtros['fecha_inicio'])) $filtros_aplicados[] = 'Desde: ' . date('d/m/Y', strtotime($filtros['fecha_inicio']));
    if (!empty($filtros['fecha_fin']))    $filtros_aplicados[] = 'Hasta: ' . date('d/m/Y', strtotime($filtros['fecha_fin']));

    $total_registros = count($ventas);

    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <style>
            /* ✅ FIX: Fuente con soporte Unicode completo */
            @font-face {
                font-family: 'DejaVu Sans';
                font-style: normal;
                font-weight: normal;
                src: url('<?= __DIR__ ?>/../../vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf') format('truetype');
            }
            @font-face {
                font-family: 'DejaVu Sans';
                font-style: normal;
                font-weight: bold;
                src: url('<?= __DIR__ ?>/../../vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf') format('truetype');
            }

            @page { margin: 30px 25px; }
            * { box-sizing: border-box; }
            body {
                font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
                font-size: 10pt;
                color: #212529;
                line-height: 1.35;
                margin: 0;
                padding: 0;
            }

            /* Header */
            .pdf-header {
                border-bottom: 3px double #0d6efd;
                padding-bottom: 12px;
                margin-bottom: 18px;
            }
            .pdf-header h1 {
                color: #0d6efd;
                margin: 0 0 6px 0;
                font-size: 18pt;
                letter-spacing: .5px;
            }
            .pdf-header .empresa-info {
                font-size: 8.5pt;
                color: #666;
                margin: 2px 0;
            }
            .pdf-titulo {
                text-align: center;
                font-size: 14pt;
                font-weight: bold;
                color: #0d6efd;
                text-transform: uppercase;
                letter-spacing: 2px;
                padding: 8px 0;
                background: linear-gradient(90deg, #e7f1ff 0%, #ffffff 50%, #e7f1ff 100%);
                border-radius: 4px;
                margin: 15px 0;
            }
            .pdf-meta {
                font-size: 8.5pt;
                color: #666;
                text-align: center;
                margin-bottom: 15px;
            }
            .pdf-meta strong { color: #212529; }

            /* Filtros aplicados */
            .filtros-aplicados {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 8px 12px;
                margin-bottom: 15px;
                font-size: 8.5pt;
                border-radius: 4px;
            }
            .filtros-aplicados strong { color: #664d03; }
            .filtros-aplicados ul {
                margin: 4px 0 0 0;
                padding-left: 20px;
                color: #664d03;
            }

            /* Resumen */
            .resumen-grid {
                display: table;
                width: 100%;
                margin-bottom: 15px;
                border-collapse: separate;
                border-spacing: 8px 0;
            }
            .resumen-card {
                display: table-cell;
                width: 25%;
                background: #f8f9fa;
                border-radius: 6px;
                padding: 10px 12px;
                border-left: 3px solid #0d6efd;
                vertical-align: top;
            }
            .resumen-card.success { border-left-color: #198754; background: #d1e7dd; }
            .resumen-card.info    { border-left-color: #0dcaf0; background: #cff4fc; }
            .resumen-card.danger  { border-left-color: #dc3545; background: #f8d7da; }
            .resumen-card .label {
                font-size: 7.5pt;
                text-transform: uppercase;
                color: #666;
                letter-spacing: .5px;
                font-weight: bold;
                display: block;
                margin-bottom: 3px;
            }
            .resumen-card .valor {
                font-size: 14pt;
                font-weight: bold;
                color: #0d6efd;
            }
            .resumen-card.success .valor { color: #0f5132; }
            .resumen-card.info    .valor { color: #055160; }
            .resumen-card.danger  .valor { color: #842029; }

            /* Tabla */
            table.ventas {
                width: 100%;
                border-collapse: collapse;
                font-size: 8.5pt;
                margin-top: 10px;
            }
            table.ventas thead th {
                background: #212529;
                color: white;
                padding: 7px 6px;
                text-align: left;
                font-size: 8pt;
                text-transform: uppercase;
                letter-spacing: .3px;
                border: 1px solid #212529;
            }
            table.ventas tbody td {
                padding: 6px;
                border-bottom: 1px solid #dee2e6;
                vertical-align: middle;
            }
            table.ventas tbody tr:nth-child(even) {
                background: #f8f9fa;
            }
            table.ventas .text-center { text-align: center; }
            table.ventas .text-end    { text-align: right; }

            /* Badges */
            .badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 7.5pt;
                font-weight: bold;
                white-space: nowrap;
            }
            .badge-success   { background: #198754; color: white; }
            .badge-danger    { background: #dc3545; color: white; }
            .badge-secondary { background: #6c757d; color: white; }
            .badge-info      { background: #0dcaf0; color: #000; }
            .badge-primary   { background: #0d6efd; color: white; }
            .badge-dark      { background: #212529; color: white; }

            /* Separador de ruta */
            .ruta-arrow {
                color: #0d6efd;
                font-weight: bold;
                margin: 0 3px;
            }

            /* Footer */
            .pdf-footer {
                margin-top: 25px;
                padding-top: 10px;
                border-top: 1px solid #dee2e6;
                font-size: 8pt;
                color: #666;
                text-align: center;
            }

            /* Detalle métodos de pago */
            .desglose {
                margin-top: 20px;
                padding: 10px 12px;
                background: #f8f9fa;
                border-radius: 6px;
                font-size: 8.5pt;
            }
            .desglose h4 {
                margin: 0 0 8px 0;
                font-size: 10pt;
                color: #495057;
                border-bottom: 1px solid #dee2e6;
                padding-bottom: 5px;
            }
            .desglose table {
                width: 100%;
                border-collapse: collapse;
            }
            .desglose td {
                padding: 3px 0;
            }
            .desglose td:first-child {
                font-weight: bold;
                color: #666;
                width: 40%;
            }
            .desglose td:last-child {
                text-align: right;
            }
        </style>
    </head>
    <body>

        <!-- HEADER EMPRESA -->
        <div class="pdf-header">
            <h1><?= htmlspecialchars($empresa_nombre) ?></h1>
            <p class="empresa-info">
                <?php if ($empresa_ruc): ?>RUC: <?= htmlspecialchars($empresa_ruc) ?><?php endif; ?>
                <?php if ($empresa_dir): ?> &middot; <?= htmlspecialchars($empresa_dir) ?><?php endif; ?>
            </p>
            <p class="empresa-info">
                <?php if ($empresa_tel): ?>Tel: <?= htmlspecialchars($empresa_tel) ?><?php endif; ?>
                <?php if ($empresa_email): ?> &middot; Email: <?= htmlspecialchars($empresa_email) ?><?php endif; ?>
            </p>
        </div>

        <!-- TÍTULO -->
        <div class="pdf-titulo">Listado de Ventas</div>

        <div class="pdf-meta">
            Generado el <strong><?= date('d/m/Y H:i:s') ?></strong>
            &middot; Total de registros: <strong><?= $total_registros ?></strong>
        </div>

        <!-- FILTROS APLICADOS -->
        <?php if (!empty($filtros_aplicados)): ?>
            <div class="filtros-aplicados">
                <strong>Filtros aplicados:</strong>
                <ul>
                    <?php foreach ($filtros_aplicados as $f): ?>
                        <li><?= $f ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- RESUMEN -->
        <div class="resumen-grid">
            <div class="resumen-card">
                <span class="label">Total Ventas</span>
                <span class="valor"><?= (int)($totales['total_ventas'] ?? 0) ?></span>
            </div>
            <div class="resumen-card success">
                <span class="label">Recaudado</span>
                <span class="valor"><?= $simbolo ?> <?= number_format((float)($totales['total_recaudado'] ?? 0), 2) ?></span>
            </div>
            <div class="resumen-card info">
                <span class="label">Activas</span>
                <span class="valor"><?= (int)($totales['activas'] ?? 0) ?></span>
            </div>
            <div class="resumen-card danger">
                <span class="label">Anuladas</span>
                <span class="valor"><?= (int)($totales['anuladas'] ?? 0) ?></span>
            </div>
        </div>

        <!-- TABLA DE VENTAS -->
        <table class="ventas">
            <thead>
                <tr>
                    <th style="width: 13%;">Comprobante</th>
                    <th style="width: 20%;">Cliente</th>
                    <th style="width: 18%;">Ruta</th>
                    <th style="width: 7%;" class="text-center">Asiento</th>
                    <th style="width: 11%;">Fecha</th>
                    <th style="width: 10%;" class="text-center">Pago</th>
                    <th style="width: 10%;" class="text-end">Total</th>
                    <th style="width: 11%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ventas)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 20px; color: #999;">
                            No hay ventas registradas con los filtros aplicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ventas as $v):
                        $badge_estado = [
                            'ACTIVA'  => 'success',
                            'ANULADA' => 'danger',
                            'USADA'   => 'secondary'
                        ][$v['estado'] ?? ''] ?? 'secondary';

                        $badge_pago = [
                            'EFECTIVO'      => 'success',
                            'TARJETA'       => 'info',
                            'TRANSFERENCIA' => 'primary',
                            'OTRO'          => 'secondary'
                        ][$v['metodo_pago'] ?? ''] ?? 'secondary';

                        $nombre_completo = trim(($v['cliente_nombre'] ?? '') . ' ' . ($v['cliente_apellido'] ?? ''));
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($v['numero_comprobante'] ?: '') ?: '&mdash;' ?></strong></td>
                        <td>
                            <?= htmlspecialchars($nombre_completo ?: '') ?: '&mdash;' ?>
                            <br>
                            <span style="color: #999; font-size: 7.5pt;">
                                <?= htmlspecialchars($v['numero_documento'] ?: '') ?: '&mdash;' ?>
                            </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($v['origen'] ?: '') ?: '&mdash;' ?>
                            <span class="ruta-arrow">&#8594;</span>
                            <?= htmlspecialchars($v['destino'] ?: '') ?: '&mdash;' ?>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-dark"><?= htmlspecialchars($v['numero_asiento'] ?: '') ?: '&mdash;' ?></span>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($v['fecha_venta'])) ?>
                            <br>
                            <span style="color: #999; font-size: 7.5pt;"><?= date('H:i', strtotime($v['fecha_venta'])) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= $badge_pago ?>">
                                <?= htmlspecialchars($v['metodo_pago'] ?: '') ?: '&mdash;' ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <strong><?= $simbolo ?> <?= number_format((float)($v['precio_pagado'] ?? 0), 2) ?></strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-<?= $badge_estado ?>">
                                <?= htmlspecialchars($v['estado'] ?: '') ?: '&mdash;' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- DESGLOSE POR MÉTODO DE PAGO -->
        <?php if (!empty($por_metodo)): ?>
        <div class="desglose">
            <h4>Desglose por método de pago</h4>
            <table>
                <?php foreach ($por_metodo as $metodo => $monto): ?>
                    <tr>
                        <td><?= htmlspecialchars($metodo) ?></td>
                        <td><strong><?= $simbolo ?> <?= number_format($monto, 2) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                <tr style="border-top: 1px solid #dee2e6;">
                    <td style="color: #0f5132;">TOTAL</td>
                    <td><strong style="color: #0f5132;"><?= $simbolo ?> <?= number_format(array_sum($por_metodo), 2) ?></strong></td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="pdf-footer">
            <p>
                <?= htmlspecialchars($empresa_nombre) ?>
                <?php if ($empresa_ruc): ?> &middot; RUC <?= htmlspecialchars($empresa_ruc) ?><?php endif; ?>
                &middot; Documento generado automáticamente por el sistema
            </p>
        </div>

    </body>
    </html>
    <?php
    return ob_get_clean();
}

}