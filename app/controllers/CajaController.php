<?php
class CajaController extends Controller {
    private $cajaModel;

    public function __construct() {
        $this->requireAuth();
        $this->cajaModel = new Caja();
    }

     /**
     * Página principal de caja
     */
    public function index() {
        $usuario_id = $_SESSION['user_id'];
        $caja = $this->cajaModel->getCajaAbierta($usuario_id);
        $redirect = $_GET['redirect'] ?? null;

        if ($caja) {
            $resumen = $this->cajaModel->getResumenCaja($caja['id']);
            // Solo movimientos manuales en el panel
            $movimientos = $this->cajaModel->getMovimientosManuales($caja['id']);
            
            // ✅ NUEVO: Ventas del día (últimas 10)
            $ventas_del_dia = $this->cajaModel->getVentasDelDia($caja['id'], 10);
            
            $this->view('caja/panel', [
                'titulo' => 'Panel de Caja',
                'resumen' => $resumen,
                'movimientos' => $movimientos,
                'ventas_del_dia' => $ventas_del_dia,
                'redirect' => $redirect
            ]);
        } else {
            // ✅ NUEVO: Última caja cerrada (referencia para el usuario)
            $ultima_caja = $this->cajaModel->getUltimaCajaCerrada($usuario_id);
            
            $this->view('caja/apertura', [
                'titulo' => 'Apertura de Caja',
                'redirect' => $redirect,
                'ultima_caja' => $ultima_caja
            ]);
        }
    }

    /**
     * Abrir caja con soporte de ?redirect=
     */
    public function abrir() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('caja');
            return;
        }

        $usuario_id = $_SESSION['user_id'];
        $monto_inicial = (float)($_POST['monto_inicial'] ?? 0);
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? null;

        if ($monto_inicial < 0) {
            $this->setFlash('El monto inicial no puede ser negativo', 'danger');
            $this->redirect('caja' . ($redirect ? '?redirect=' . urlencode($redirect) : ''));
            return;
        }

        $caja_id = $this->cajaModel->abrir($usuario_id, $monto_inicial);
        
        if ($caja_id) {
            $this->setFlash('Caja abierta correctamente. ¡Buen día de ventas!', 'success');
            
            if ($redirect) {
                $this->redirect($redirect);
                return;
            }
        } else {
            $this->setFlash('Error al abrir caja. Ya tiene una caja abierta.', 'danger');
        }
        
        $this->redirect('caja');
    }

    /**
     * Registrar movimiento manual (ingreso/egreso)
     */
    public function movimiento() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('caja');
            return;
        }

        $usuario_id = $_SESSION['user_id'];
        $caja = $this->cajaModel->getCajaAbierta($usuario_id);
        
        if (!$caja) {
            $this->setFlash('No tiene una caja abierta', 'danger');
            $this->redirect('caja');
            return;
        }

        $tipo = $_POST['tipo'] ?? 'INGRESO';
        $concepto = trim($_POST['concepto'] ?? '');
        $monto = (float)($_POST['monto'] ?? 0);
        $metodo_pago = $_POST['metodo_pago'] ?? 'EFECTIVO';
        $referencia = trim($_POST['referencia'] ?? '');

        if (empty($concepto) || $monto <= 0) {
            $this->setFlash('Complete todos los campos correctamente', 'danger');
            $this->redirect('caja');
            return;
        }

        if ($this->cajaModel->registrarMovimiento($caja['id'], $usuario_id, $tipo, $concepto, $monto, $metodo_pago, $referencia)) {
            $this->setFlash('Movimiento registrado correctamente', 'success');
        } else {
            $this->setFlash('Error al registrar el movimiento', 'danger');
        }
        $this->redirect('caja');
    }

    /**
     * Eliminar movimiento
     */
    public function eliminarMovimiento($id) {
        if ($this->cajaModel->eliminarMovimiento($id)) {
            $this->setFlash('Movimiento eliminado', 'success');
        } else {
            $this->setFlash('Error al eliminar el movimiento', 'danger');
        }
        $this->redirect('caja');
    }

    /**
     * Mostrar pantalla de cierre de caja
     */
    public function cerrar() {
        $usuario_id = $_SESSION['user_id'];
        $caja = $this->cajaModel->getCajaAbierta($usuario_id);
        
        if (!$caja) {
            $this->setFlash('No tiene una caja abierta', 'danger');
            $this->redirect('caja');
            return;
        }

        $resumen = $this->cajaModel->getResumenCaja($caja['id']);
        
        $this->view('caja/cierre', [
            'titulo' => 'Cierre de Caja',
            'resumen' => $resumen
        ]);
    }

    /**
     * Procesar cierre de caja
     */
    public function procesarCierre() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('caja');
            return;
        }

        $usuario_id = $_SESSION['user_id'];
        $caja = $this->cajaModel->getCajaAbierta($usuario_id);
        
        if (!$caja) {
            $this->setFlash('No tiene una caja abierta', 'danger');
            $this->redirect('caja');
            return;
        }

        $monto_final_real = (float)($_POST['monto_final_real'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($monto_final_real < 0) {
            $this->setFlash('El monto final no puede ser negativo', 'danger');
            $this->redirect('caja/cerrar');
            return;
        }

        if ($this->cajaModel->cerrar($caja['id'], $monto_final_real, $observaciones)) {
            $this->setFlash('¡Caja cerrada correctamente!', 'success');
            $this->redirect('caja/comprobante/' . $caja['id']);
        } else {
            $this->setFlash('Error al cerrar la caja', 'danger');
            $this->redirect('caja/cerrar');
        }
    }

    /**
     * ✅ Comprobante de cierre (con facturacion.php cargado para las constantes)
     */
    public function comprobante($id) {
        // ✅ Cargar configuración de facturación para que las constantes existan
        $configPath = __DIR__ . '/../config/facturacion.php';
        if (file_exists($configPath)) {
            require_once $configPath;
        }
        
        $caja = $this->cajaModel->getCajaConDetalles($id);
        if (!$caja) {
            $this->setFlash('Caja no encontrada', 'danger');
            $this->redirect('caja');
            return;
        }

        $this->view('caja/comprobante', [
            'titulo' => 'Comprobante de Cierre',
            'caja' => $caja
        ]);
    }

    /**
     * Historial de cajas cerradas
     */
    public function historial() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'usuario_id' => $_GET['usuario_id'] ?? ''
        ];
        
        $resultado = $this->cajaModel->getHistorialPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );

        // ✅ Totales globales (no solo de la página)
        $totales = $this->cajaModel->getTotalesHistorial($filtros);
        
        $usuarioModel = new Usuario();
        $usuarios = $usuarioModel->getAllWithRoles();

        $this->view('caja/historial', [
            'titulo' => 'Historial de Cierres de Caja',
            'cajas' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'usuarios' => $usuarios,
            'filtros' => $filtros,
            'totales' => $totales
        ]);
    }

    /**
     * Ver detalle de una caja cerrada
     */
    public function show($id) {
        $caja = $this->cajaModel->getCajaConDetalles($id);
        if (!$caja) {
            $this->setFlash('Caja no encontrada', 'danger');
            $this->redirect('caja/historial');
            return;
        }

        $this->view('caja/show', [
            'titulo' => 'Detalle de Caja #' . $id,
            'caja' => $caja
        ]);
    }
}
