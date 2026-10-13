<?php
class FacturaController extends Controller {
    private $facturaModel;

    public function __construct() {
        $this->requireAuth();
        $this->facturaModel = new FacturaElectronica();
    }

    /**
     * Listado de comprobantes emitidos
     */
    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'tipo' => $_GET['tipo'] ?? '',
            'estado' => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d')
        ];
        
        $resultado = $this->facturaModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );
        
        $resumen = $this->facturaModel->getResumen($filtros);
        
        $this->view('factura/index', [
            'titulo' => 'Comprobantes Electrónicos',
            'comprobantes' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'resumen' => $resumen,
            'filtros' => $filtros
        ]);
    }

    /**
     * Emitir comprobante desde una venta
     */
    public function emitir($venta_id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('ventas/show/' . $venta_id);
        return;
    }

    $tipo = $_POST['tipo'] ?? 'BOLETA';
    
    if (!in_array($tipo, ['FACTURA', 'BOLETA'])) {
        $this->setFlash('Tipo de comprobante inválido', 'danger');
        $this->redirect('ventas/show/' . $venta_id);
        return;
    }

    $result = $this->facturaModel->emitir($venta_id, $tipo);
    
    if ($result['success']) {
        // Mensaje según el modo
        if (!empty($result['modo_simulacion'])) {
            $this->setFlash(
                '🧪 <strong>SIMULACIÓN:</strong> Comprobante ' . $result['numero_completo'] . ' generado localmente. NO fue enviado a SUNAT.', 
                'warning'
            );
        } else {
            $this->setFlash(
                '✅ ' . $result['message'] . ' — ' . $result['numero_completo'], 
                'success'
            );
        }
    } else {
        $this->setFlash(
            '❌ ' . $result['message'], 
            'danger'
        );
    }
    
    $this->redirect('ventas/show/' . $venta_id);
}
    /**
     * Ver detalle del comprobante
     */
    public function show($id) {
        $comprobante = $this->facturaModel->find($id);
        if (!$comprobante) {
            $this->setFlash('Comprobante no encontrado', 'danger');
            $this->redirect('factura');
        }
        
        // Obtener datos de la venta
        $ventaModel = new Venta();
        $venta = $ventaModel->getByIdWithDetails($comprobante['venta_id']);
        
        $this->view('factura/show', [
            'titulo' => 'Detalle del Comprobante',
            'comprobante' => $comprobante,
            'venta' => $venta
        ]);
    }

    /**
     * Descargar XML
     */
    public function descargarXML($id) {
        $this->facturaModel->descargarXML($id);
    }

    /**
     * Reenviar a SUNAT (si hubo error de conexión)
     */
    public function reenviar($id) {
        $comprobante = $this->facturaModel->find($id);
        if (!$comprobante) {
            $this->setFlash('Comprobante no encontrado', 'danger');
            $this->redirect('factura');
        }
        
        $result = $this->facturaModel->emitir($comprobante['venta_id'], $comprobante['tipo']);
        
        if ($result['success']) {
            $this->setFlash('Comprobante reenviado correctamente', 'success');
        } else {
            $this->setFlash('Error al reenviar: ' . $result['message'], 'danger');
        }
        
        $this->redirect('factura/show/' . $id);
    }

    /**
     * Imprimir comprobante (vista HTML imprimible)
     */
    public function imprimir($id) {
        $comprobante = $this->facturaModel->find($id);
        if (!$comprobante) {
            die('Comprobante no encontrado');
        }
        
        $ventaModel = new Venta();
        $venta = $ventaModel->getByIdWithDetails($comprobante['venta_id']);
        
        $this->view('factura/imprimir', [
            'titulo' => 'Imprimir Comprobante',
            'comprobante' => $comprobante,
            'venta' => $venta
        ]);
    }

/**
 * Enviar comprobante por email
 */
public function email($id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('factura/show/' . $id);
    }

    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    $comprobanteModel = new ComprobanteEmail();
    $resultado = $comprobanteModel->enviarPorEmail($id, $email, $mensaje);

    if ($resultado['success']) {
        $this->setFlash('✅ ' . $resultado['message'], 'success');
    } else {
        $this->setFlash('❌ ' . $resultado['message'], 'danger');
    }

    $this->redirect('factura/show/' . $id);
}

/**
 * Descargar PDF del comprobante
 */
public function pdf($id) {
    $comprobanteModel = new ComprobanteEmail();
    $comprobanteModel->descargarPDF($id);
}

}