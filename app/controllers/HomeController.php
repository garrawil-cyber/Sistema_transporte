<?php
class HomeController extends Controller {
    private $dashboardModel;
    private $viajeModel;

    public function __construct() {
        $this->requireAuth();
        $this->dashboardModel = new Dashboard();
        $this->viajeModel = new Viaje();
    }

    public function index() {
        // ✅ Transición perezosa PROGRAMADO -> EN_CURSO por hora de salida,
        //    para que el Dashboard siempre coincida con lo que se ve en
        //    la pantalla de Viajes.
        $this->viajeModel->actualizarEstadosPorHora();

        // KPIs principales
        $kpis = $this->dashboardModel->getKPIs();
        
        // Gráficos
        $ventas_7dias = $this->dashboardModel->getVentasUltimos7Dias();
        $top_rutas = $this->dashboardModel->getTopRutas(5);
        $metodos_pago = $this->dashboardModel->getMetodosPago();
        $ocupabilidad = $this->dashboardModel->getOcupabilidadGlobal();
        
        // Alertas
        $alertas = $this->dashboardModel->getAlertas();
        
        // Actividad reciente
        $ultimas_ventas = $this->dashboardModel->getUltimasVentas(5);
        $ultimas_encomiendas = $this->dashboardModel->getUltimasEncomiendas(5);
        $ultimos_giros = $this->dashboardModel->getUltimosGiros(5);
        
        // Rendimiento
        $rendimiento_vendedores = $this->dashboardModel->getRendimientoVendedores();

        $this->view('home/index', [
            'titulo' => 'Dashboard',
            'kpis' => $kpis,
            'ventas_7dias' => $ventas_7dias,
            'top_rutas' => $top_rutas,
            'metodos_pago' => $metodos_pago,
            'ocupabilidad' => $ocupabilidad,
            'alertas' => $alertas,
            'ultimas_ventas' => $ultimas_ventas,
            'ultimas_encomiendas' => $ultimas_encomiendas,
            'ultimos_giros' => $ultimos_giros,
            'rendimiento_vendedores' => $rendimiento_vendedores
        ]);
    }
}