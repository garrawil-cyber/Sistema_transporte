<?php
class AuditoriaController extends Controller {
    private $auditoriaModel;
    private $usuarioModel;

    public function __construct() {
        $this->requireAuth();
        $this->requireAdmin();
        $this->auditoriaModel = new Auditoria();
        $this->usuarioModel = new Usuario();
    }

    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'usuario_id' => $_GET['usuario_id'] ?? '',
            'accion' => $_GET['accion'] ?? '',
            'tabla' => $_GET['tabla'] ?? '',
            'modulo' => $_GET['modulo'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-7 days')),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'ip' => $_GET['ip'] ?? ''
        ];
        
        $resultado = $this->auditoriaModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );
        
        $estadisticas = $this->auditoriaModel->getEstadisticas(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $usuarios = $this->usuarioModel->getAllWithRoles();
        $actividad_dia = $this->auditoriaModel->getActividadPorDia(30);
        $actividad_modulo = $this->auditoriaModel->getActividadPorModulo(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        $actividad_usuario = $this->auditoriaModel->getActividadPorUsuario(
            $filtros['fecha_inicio'], 
            $filtros['fecha_fin']
        );
        
        $this->view('auditoria/index', [
            'titulo' => 'Auditoría del Sistema',
            'registros' => $resultado['datos'],
            'paginator' => $resultado['paginator'],
            'estadisticas' => $estadisticas,
            'usuarios' => $usuarios,
            'actividad_dia' => $actividad_dia,
            'actividad_modulo' => $actividad_modulo,
            'actividad_usuario' => $actividad_usuario,
            'filtros' => $filtros
        ]);
    }

    public function show($id) {
        $registro = $this->auditoriaModel->find($id);
        if (!$registro) {
            $this->setFlash('Registro no encontrado', 'danger');
            $this->redirect('auditoria');
        }
        
        $usuario = null;
        if (!empty($registro['usuario_id'])) {
            $usuario = $this->usuarioModel->find($registro['usuario_id']);
        }
        
        $this->view('auditoria/show', [
            'titulo' => 'Detalle de Auditoría',
            'registro' => $registro,
            'usuario' => $usuario
        ]);
    }

    public function sesiones() {
        $sesiones = $this->auditoriaModel->getSesionesActivas();
        
        $this->view('auditoria/sesiones', [
            'titulo' => 'Sesiones Activas',
            'sesiones' => $sesiones
        ]);
    }

    public function exportar() {
        $filtros = [
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d'),
            'accion' => $_GET['accion'] ?? ''
        ];
        
        $datos = $this->auditoriaModel->exportarExcel($filtros);
        
        Auditoria::registrar('EXPORT', [
            'modulo' => 'auditoria',
            'descripcion' => 'Exportó auditoría a Excel (' . count($datos) . ' registros)'
        ]);
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="auditoria_' . date('Ymd') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo '<html><head><meta charset="UTF-8"></head><body>';
        echo '<h2>Auditoría del Sistema</h2>';
        echo '<p>Período: ' . $filtros['fecha_inicio'] . ' al ' . $filtros['fecha_fin'] . '</p>';
        echo '<table border="1">';
        echo '<tr style="background:#212529;color:#fff;">';
        echo '<th>ID</th><th>Fecha/Hora</th><th>Usuario</th><th>Acción</th>';
        echo '<th>Módulo</th><th>Tabla</th><th>Descripción</th><th>IP</th>';
        echo '</tr>';
        
        foreach ($datos as $d) {
            echo '<tr>';
            echo '<td>' . $d['id'] . '</td>';
            echo '<td>' . date('d/m/Y H:i:s', strtotime($d['fecha_hora'])) . '</td>';
            echo '<td>' . htmlspecialchars($d['usuario_nombre'] ?? 'Sistema') . '</td>';
            echo '<td>' . $d['accion'] . '</td>';
            echo '<td>' . $d['modulo'] . '</td>';
            echo '<td>' . $d['tabla_afectada'] . '</td>';
            echo '<td>' . htmlspecialchars($d['descripcion'] ?? '') . '</td>';
            echo '<td>' . $d['ip_origen'] . '</td>';
            echo '</tr>';
        }
        
        echo '</table></body></html>';
        exit;
    }

    public function limpiar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auditoria');
        }
        
        $dias = (int)($_POST['dias'] ?? 90);
        $eliminados = $this->auditoriaModel->limpiarAntiguos($dias);
        
        Auditoria::registrar('DELETE', [
            'modulo' => 'auditoria',
            'descripcion' => "Limpió $eliminados registros de auditoría con más de $dias días"
        ]);
        
        $this->setFlash("Se eliminaron {$eliminados} registros antiguos", 'success');
        $this->redirect('auditoria');
    }
}