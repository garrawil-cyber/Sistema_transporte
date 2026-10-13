<?php
class ReportesController extends Controller {
    private $reporteModel;
    private $rutaModel;
    private $usuarioModel;

    public function __construct() {
        $this->requireAuth();
        $this->reporteModel = new Reporte();
        $this->rutaModel = new Ruta();
        $this->usuarioModel = new Usuario();
    }

    /**
     * ============================================
     * PÁGINA PRINCIPAL
     * ============================================
     */
    public function index() {
        $this->view('reportes/index', ['titulo' => 'Módulo de Reportes']);
    }

    /**
     * ============================================
     * REPORTE DE VENTAS
     * ============================================
     */
    public function ventas() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $ruta_id = $_GET['ruta_id'] ?? null;
        $usuario_id = $_GET['usuario_id'] ?? null;

        $ventas = $this->reporteModel->getVentas($fecha_inicio, $fecha_fin, $ruta_id, $usuario_id);
        $resumen = $this->reporteModel->getResumenVentas($fecha_inicio, $fecha_fin, $usuario_id);
        $ventas_por_ruta = $this->reporteModel->getVentasPorRuta($fecha_inicio, $fecha_fin);
        $ventas_por_vendedor = $this->reporteModel->getVentasPorVendedor($fecha_inicio, $fecha_fin);
        $rutas = $this->rutaModel->all();
        $usuarios = $this->usuarioModel->getAllWithRoles();

        $this->view('reportes/ventas', [
            'titulo' => 'Reporte de Ventas',
            'ventas' => $ventas,
            'resumen' => $resumen,
            'ventas_por_ruta' => $ventas_por_ruta,
            'ventas_por_vendedor' => $ventas_por_vendedor,
            'rutas' => $rutas,
            'usuarios' => $usuarios,
            'filtros' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'ruta_id' => $ruta_id,
                'usuario_id' => $usuario_id
            ]
        ]);
    }

    /**
     * ============================================
     * REPORTE DE ENCOMIENDAS
     * ============================================
     */
    public function encomiendas() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $estado = $_GET['estado'] ?? null;

        $encomiendas = $this->reporteModel->getEncomiendas($fecha_inicio, $fecha_fin, $estado);
        $resumen = $this->reporteModel->getResumenEncomiendas($fecha_inicio, $fecha_fin);

        $this->view('reportes/encomiendas', [
            'titulo' => 'Reporte de Encomiendas',
            'encomiendas' => $encomiendas,
            'resumen' => $resumen,
            'filtros' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $estado
            ]
        ]);
    }

    /**
     * ============================================
     * REPORTE DE GIROS
     * ============================================
     */
    public function giros() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $estado = $_GET['estado'] ?? null;

        $giros = $this->reporteModel->getGiros($fecha_inicio, $fecha_fin, $estado);
        $resumen = $this->reporteModel->getResumenGiros($fecha_inicio, $fecha_fin);

        $this->view('reportes/giros', [
            'titulo' => 'Reporte de Giros',
            'giros' => $giros,
            'resumen' => $resumen,
            'filtros' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin,
                'estado' => $estado
            ]
        ]);
    }

    /**
     * ============================================
     * REPORTE DE OCUPABILIDAD
     * ============================================
     */
    public function ocupabilidad() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        $viajes = $this->reporteModel->getOcupabilidad($fecha_inicio, $fecha_fin);

        $this->view('reportes/ocupabilidad', [
            'titulo' => 'Reporte de Ocupabilidad',
            'viajes' => $viajes,
            'filtros' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ]
        ]);
    }

    /**
     * ============================================
     * REPORTE DE CAJA
     * ============================================
     */
public function caja() {
    try {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $usuario_id = (int)($_GET['usuario_id'] ?? ($_SESSION['user_id'] ?? 0));
        
        $usuario_seleccionado = $this->usuarioModel->find($usuario_id);
        if (!$usuario_seleccionado) {
            $this->setFlash('Usuario no encontrado', 'danger');
            $this->redirect('reportes');
            return;
        }

        $resumen = $this->reporteModel->getCierreCaja($usuario_id, $fecha);
        $detalle = $this->reporteModel->getDetalleCaja($usuario_id, $fecha);
        $usuarios = $this->usuarioModel->getAllWithRoles();

        $this->view('reportes/caja', [
            'titulo' => 'Cierre de Caja',
            'resumen' => is_array($resumen) ? $resumen : [],
            'detalle' => is_array($detalle) ? $detalle : [],
            'usuarios' => is_array($usuarios) ? $usuarios : [],
            'usuario_seleccionado' => $usuario_seleccionado,
            'filtros' => [
                'fecha' => $fecha,
                'usuario_id' => $usuario_id
            ]
        ]);
    } catch (Exception $e) {
        Logger::error('Error en reporte de caja: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        $this->setFlash('Error al generar el reporte de caja', 'danger');
        $this->redirect('reportes');
    }
}

    /**
     * ============================================
     * EXPORTAR A EXCEL
     * ============================================
     */
    public function exportarExcel($tipo) {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_' . $tipo . '_' . date('Ymd') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        switch ($tipo) {
            case 'ventas':
                $datos = $this->reporteModel->getVentas($fecha_inicio, $fecha_fin);
                $this->generarExcelVentas($datos);
                break;
            case 'encomiendas':
                $datos = $this->reporteModel->getEncomiendas($fecha_inicio, $fecha_fin);
                $this->generarExcelEncomiendas($datos);
                break;
            case 'giros':
                $datos = $this->reporteModel->getGiros($fecha_inicio, $fecha_fin);
                $this->generarExcelGiros($datos);
                break;
            default:
                $this->redirect('reportes');
        }
        exit;
    }

    private function generarExcelVentas($datos) {
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Reporte de Ventas</h2>';
        echo '<table border="1">';
        echo '<tr style="background:#212529;color:#fff;">
                <th>ID</th><th>Comprobante</th><th>Fecha</th><th>Cliente</th>
                <th>Documento</th><th>Ruta</th><th>Bus</th><th>Asiento</th>
                <th>Método Pago</th><th>Total</th><th>Vendedor</th>
              </tr>';
        foreach ($datos as $d) {
            echo '<tr>';
            echo '<td>' . $d['id'] . '</td>';
            echo '<td>' . $d['numero_comprobante'] . '</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($d['fecha_venta'])) . '</td>';
            echo '<td>' . $d['cliente_nombre'] . ' ' . $d['cliente_apellido'] . '</td>';
            echo '<td>' . $d['numero_documento'] . '</td>';
            echo '<td>' . $d['origen'] . ' → ' . $d['destino'] . '</td>';
            echo '<td>' . $d['placa'] . '</td>';
            echo '<td>' . $d['numero_asiento'] . '</td>';
            echo '<td>' . $d['metodo_pago'] . '</td>';
            echo '<td>S/ ' . number_format($d['precio_pagado'], 2) . '</td>';
            echo '<td>' . $d['vendedor'] . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
    }

    private function generarExcelEncomiendas($datos) {
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Reporte de Encomiendas</h2>';
        echo '<table border="1">';
        echo '<tr style="background:#212529;color:#fff;">
                <th>Guía</th><th>Fecha</th><th>Remitente</th><th>Destinatario</th>
                <th>Ruta</th><th>Peso (kg)</th><th>Costo</th><th>Estado</th>
              </tr>';
        foreach ($datos as $d) {
            echo '<tr>';
            echo '<td>' . $d['guia'] . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['fecha_registro'])) . '</td>';
            echo '<td>' . $d['remitente_nombre'] . ' ' . $d['remitente_apellido'] . '</td>';
            echo '<td>' . $d['destinatario_nombre'] . ' ' . $d['destinatario_apellido'] . '</td>';
            echo '<td>' . $d['origen'] . ' → ' . $d['destino'] . '</td>';
            echo '<td>' . $d['peso_kg'] . '</td>';
            echo '<td>S/ ' . number_format($d['costo_envio'], 2) . '</td>';
            echo '<td>' . $d['estado'] . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
    }

    private function generarExcelGiros($datos) {
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Reporte de Giros</h2>';
        echo '<table border="1">';
        echo '<tr style="background:#212529;color:#fff;">
                <th>Código</th><th>Fecha</th><th>Remitente</th><th>Destinatario</th>
                <th>Monto</th><th>Comisión</th><th>Estado</th>
              </tr>';
        foreach ($datos as $d) {
            echo '<tr>';
            echo '<td>' . $d['codigo'] . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($d['fecha_registro'])) . '</td>';
            echo '<td>' . $d['remitente_nombre'] . ' ' . $d['remitente_apellido'] . '</td>';
            echo '<td>' . $d['destinatario_nombre'] . ' ' . $d['destinatario_apellido'] . '</td>';
            echo '<td>S/ ' . number_format($d['monto'], 2) . '</td>';
            echo '<td>S/ ' . number_format($d['comision'], 2) . '</td>';
            echo '<td>' . $d['estado'] . '</td>';
            echo '</tr>';
        }
        echo '</table></body></html>';
    }

    /**
     * ============================================
     * EXPORTAR A PDF
     * ============================================
     */
    public function exportarPDF($tipo) {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        switch ($tipo) {
            case 'ventas':
                $this->exportarPDFVentas($fecha_inicio, $fecha_fin);
                break;
            case 'encomiendas':
                $this->exportarPDFEncomiendas($fecha_inicio, $fecha_fin);
                break;
            case 'giros':
                $this->exportarPDFGiros($fecha_inicio, $fecha_fin);
                break;
            case 'ocupabilidad':
                $this->exportarPDFOcupabilidad($fecha_inicio, $fecha_fin);
                break;
            case 'caja':
                $fecha = $_GET['fecha'] ?? date('Y-m-d');
                $usuario_id = $_GET['usuario_id'] ?? $_SESSION['user_id'];
                $this->exportarPDFCaja($fecha, $usuario_id);
                break;
            default:
                $this->redirect('reportes');
        }
    }

    private function exportarPDFVentas($fecha_inicio, $fecha_fin) {
        $ventas = $this->reporteModel->getVentas($fecha_inicio, $fecha_fin);
        $resumen = $this->reporteModel->getResumenVentas($fecha_inicio, $fecha_fin);
        $ventas_por_ruta = $this->reporteModel->getVentasPorRuta($fecha_inicio, $fecha_fin);
        $ventas_por_vendedor = $this->reporteModel->getVentasPorVendedor($fecha_inicio, $fecha_fin);

        $html = '<div class="resumen-container">';
        $html .= '<div class="resumen-item"><h4>Total Ventas</h4><div class="valor">' . ($resumen['total_ventas'] ?? 0) . '</div></div>';
        $html .= '<div class="resumen-item success"><h4>Total Recaudado</h4><div class="valor">S/ ' . number_format($resumen['total_recaudado'] ?? 0, 2) . '</div></div>';
        $html .= '<div class="resumen-item info"><h4>Promedio</h4><div class="valor">S/ ' . number_format($resumen['promedio'] ?? 0, 2) . '</div></div>';
        $html .= '</div>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:5px;">Ventas por Ruta</h4>';
        $html .= '<table><thead><tr><th>Ruta</th><th class="text-end">Ventas</th><th class="text-end">Total</th></tr></thead><tbody>';
        foreach ($ventas_por_ruta as $v) {
            $html .= '<tr><td>' . htmlspecialchars($v['origen'] . ' → ' . $v['destino']) . '</td><td class="text-end">' . $v['total_ventas'] . '</td><td class="text-end">S/ ' . number_format($v['total_recaudado'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:5px;margin-top:15px;">Detalle de Ventas</h4>';
        $html .= '<table><thead><tr><th>#</th><th>Comprobante</th><th>Fecha</th><th>Cliente</th><th>Ruta</th><th>Total</th></tr></thead><tbody>';
        $i = 1;
        foreach ($ventas as $v) {
            $html .= '<tr><td>' . $i++ . '</td><td>' . $v['numero_comprobante'] . '</td><td>' . date('d/m/Y', strtotime($v['fecha_venta'])) . '</td><td>' . htmlspecialchars($v['cliente_nombre'] . ' ' . $v['cliente_apellido']) . '</td><td>' . htmlspecialchars($v['origen'] . ' → ' . $v['destino']) . '</td><td class="text-end">S/ ' . number_format($v['precio_pagado'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf = new PdfExporter();
        $pdf->generar('Reporte de Ventas', $html, ['Período' => date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin))], 'landscape');
    }

    private function exportarPDFEncomiendas($fecha_inicio, $fecha_fin) {
        $encomiendas = $this->reporteModel->getEncomiendas($fecha_inicio, $fecha_fin);
        $resumen = $this->reporteModel->getResumenEncomiendas($fecha_inicio, $fecha_fin);

        $html = '<div class="resumen-container">';
        $html .= '<div class="resumen-item"><h4>Total Encomiendas</h4><div class="valor">' . ($resumen['total_encomiendas'] ?? 0) . '</div></div>';
        $html .= '<div class="resumen-item success"><h4>Total Recaudado</h4><div class="valor">S/ ' . number_format($resumen['total_recaudado'] ?? 0, 2) . '</div></div>';
        $html .= '<div class="resumen-item info"><h4>Peso Total</h4><div class="valor">' . number_format($resumen['total_peso'] ?? 0, 2) . ' kg</div></div>';
        $html .= '</div>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:5px;">Detalle de Encomiendas</h4>';
        $html .= '<table><thead><tr><th>Guía</th><th>Fecha</th><th>Remitente</th><th>Destinatario</th><th>Ruta</th><th>Peso</th><th>Estado</th><th class="text-end">Costo</th></tr></thead><tbody>';
        foreach ($encomiendas as $e) {
            $html .= '<tr><td>' . $e['guia'] . '</td><td>' . date('d/m/Y', strtotime($e['fecha_registro'])) . '</td><td>' . htmlspecialchars($e['remitente_nombre'] . ' ' . $e['remitente_apellido']) . '</td><td>' . htmlspecialchars($e['destinatario_nombre'] . ' ' . $e['destinatario_apellido']) . '</td><td>' . htmlspecialchars($e['origen'] . ' → ' . $e['destino']) . '</td><td>' . $e['peso_kg'] . ' kg</td><td>' . $e['estado'] . '</td><td class="text-end">S/ ' . number_format($e['costo_envio'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf = new PdfExporter();
        $pdf->generar('Reporte de Encomiendas', $html, ['Período' => date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin))], 'landscape');
    }

    private function exportarPDFGiros($fecha_inicio, $fecha_fin) {
        $giros = $this->reporteModel->getGiros($fecha_inicio, $fecha_fin);
        $resumen = $this->reporteModel->getResumenGiros($fecha_inicio, $fecha_fin);

        $html = '<div class="resumen-container">';
        $html .= '<div class="resumen-item"><h4>Total Giros</h4><div class="valor">' . ($resumen['total_giros'] ?? 0) . '</div></div>';
        $html .= '<div class="resumen-item success"><h4>Monto Total</h4><div class="valor">S/ ' . number_format($resumen['total_monto'] ?? 0, 2) . '</div></div>';
        $html .= '<div class="resumen-item warning"><h4>Comisiones</h4><div class="valor">S/ ' . number_format($resumen['total_comision'] ?? 0, 2) . '</div></div>';
        $html .= '</div>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:5px;">Detalle de Giros</h4>';
        $html .= '<table><thead><tr><th>Código</th><th>Fecha</th><th>Remitente</th><th>Destinatario</th><th>Monto</th><th>Comisión</th><th>Estado</th></tr></thead><tbody>';
        foreach ($giros as $g) {
            $html .= '<tr><td>' . $g['codigo'] . '</td><td>' . date('d/m/Y', strtotime($g['fecha_registro'])) . '</td><td>' . htmlspecialchars($g['remitente_nombre'] . ' ' . $g['remitente_apellido']) . '</td><td>' . htmlspecialchars($g['destinatario_nombre'] . ' ' . $g['destinatario_apellido']) . '</td><td class="text-end">S/ ' . number_format($g['monto'], 2) . '</td><td class="text-end">S/ ' . number_format($g['comision'], 2) . '</td><td>' . $g['estado'] . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf = new PdfExporter();
        $pdf->generar('Reporte de Giros', $html, ['Período' => date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin))], 'landscape');
    }

    private function exportarPDFOcupabilidad($fecha_inicio, $fecha_fin) {
        $viajes = $this->reporteModel->getOcupabilidad($fecha_inicio, $fecha_fin);

        $html = '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;padding-bottom:5px;">Detalle de Ocupabilidad</h4>';
        $html .= '<table><thead><tr><th>#</th><th>Ruta</th><th>Bus</th><th>Salida</th><th>Total</th><th>Ocupados</th><th>Libres</th><th>% Ocup.</th><th class="text-end">Recaudado</th></tr></thead><tbody>';
        $i = 1;
        foreach ($viajes as $v) {
            $html .= '<tr><td>' . $i++ . '</td><td>' . htmlspecialchars($v['origen'] . ' → ' . $v['destino']) . '</td><td>' . $v['placa'] . '</td><td>' . date('d/m/Y H:i', strtotime($v['fecha_salida'])) . '</td><td class="text-center">' . $v['total_asientos'] . '</td><td class="text-center">' . $v['asientos_ocupados'] . '</td><td class="text-center">' . $v['asientos_libres'] . '</td><td class="text-center">' . number_format($v['porcentaje_ocupacion'], 1) . '%</td><td class="text-end">S/ ' . number_format($v['total_recaudado'] ?? 0, 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf = new PdfExporter();
        $pdf->generar('Reporte de Ocupabilidad', $html, ['Período' => date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin))], 'landscape');
    }

    private function exportarPDFCaja($fecha, $usuario_id) {
        $resumen = $this->reporteModel->getCierreCaja($usuario_id, $fecha);
        $detalle = $this->reporteModel->getDetalleCaja($usuario_id, $fecha);
        $usuario = $this->usuarioModel->find($usuario_id);

        $total_general = 0;
        foreach ($resumen as $r) $total_general += $r['total'];

        $html = '<div style="text-align:center;margin-bottom:20px;">';
        $html .= '<h2 style="color:#0d6efd;">CIERRE DE CAJA</h2>';
        $html .= '<p><strong>Usuario:</strong> ' . htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) . '</p>';
        $html .= '<p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($fecha)) . '</p>';
        $html .= '</div>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;">Resumen por Método de Pago</h4>';
        $html .= '<table><thead><tr><th>Método</th><th class="text-center">Cantidad</th><th class="text-end">Total</th></tr></thead><tbody>';
        foreach ($resumen as $r) {
            $html .= '<tr><td>' . $r['metodo_pago'] . '</td><td class="text-center">' . $r['cantidad'] . '</td><td class="text-end">S/ ' . number_format($r['total'], 2) . '</td></tr>';
        }
        $html .= '</tbody><tfoot><tr><th>TOTAL</th><th class="text-center">' . count($detalle) . '</th><th class="text-end">S/ ' . number_format($total_general, 2) . '</th></tr></tfoot></table>';

        $html .= '<h4 style="color:#0d6efd;border-bottom:2px solid #0d6efd;margin-top:15px;">Detalle de Ventas</h4>';
        $html .= '<table><thead><tr><th>Hora</th><th>Comprobante</th><th>Cliente</th><th>Ruta</th><th class="text-end">Total</th></tr></thead><tbody>';
        foreach ($detalle as $d) {
            $html .= '<tr><td>' . date('H:i', strtotime($d['fecha_venta'])) . '</td><td>' . $d['numero_comprobante'] . '</td><td>' . htmlspecialchars($d['cliente_nombre'] . ' ' . $d['cliente_apellido']) . '</td><td>' . htmlspecialchars($d['origen'] . ' → ' . $d['destino']) . '</td><td class="text-end">S/ ' . number_format($d['precio_pagado'], 2) . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf = new PdfExporter();
        $pdf->generar('Cierre de Caja', $html, ['Fecha' => date('d/m/Y', strtotime($fecha))], 'portrait');
    }
}