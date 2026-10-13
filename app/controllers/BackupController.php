<?php
class BackupController extends Controller {
    private $backupModel;

    public function __construct() {
        $this->requireAuth();
        $this->requireAdmin();
        $this->backupModel = new Backup();
    }

    /**
     * Panel principal de backups
     */
public function index() {
    $paginacion = $this->getPaginacion();
    
    $filtros = [
        'buscar' => $_GET['buscar'] ?? '',
        'tipo' => $_GET['tipo'] ?? '',
        'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
        'fecha_fin' => $_GET['fecha_fin'] ?? ''
    ];
    
    $resultado = $this->backupModel->getPaginated(
        $paginacion['pagina'],
        $paginacion['por_pagina'],
        $filtros
    );
    
    $estadisticas = $this->backupModel->getEstadisticas();
    
    $this->view('backup/index', [
        'titulo' => 'Gestión de Backups',
        'backups' => $resultado['datos'],
        'paginator' => $resultado['paginator'],
        'estadisticas' => $estadisticas,
        'filtros' => $filtros
    ]);
}

    /**
     * Crear backup manual
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('backup');
        }

        $tipo = $_POST['tipo'] ?? 'manual';
        if (!in_array($tipo, ['manual', 'automatico'])) {
            $tipo = 'manual';
        }

        $resultado = $this->backupModel->crear($tipo, $_SESSION['user_id']);
        
        if ($resultado['success']) {
            $this->setFlash(
                '✅ Backup creado correctamente. Tamaño: ' . $resultado['tamano'], 
                'success'
            );
        } else {
            $this->setFlash('❌ ' . $resultado['message'], 'danger');
        }
        
        $this->redirect('backup');
    }

    /**
     * Descargar backup
     */
    public function descargar($id) {
        $this->backupModel->descargar($id);
    }

    /**
     * Eliminar backup
     */
    public function eliminar($id) {
        $resultado = $this->backupModel->eliminar($id);
        
        if ($resultado['success']) {
            $this->setFlash('Backup eliminado correctamente', 'success');
        } else {
            $this->setFlash($resultado['message'], 'danger');
        }
        
        $this->redirect('backup');
    }

    /**
     * Restaurar backup
     */
    public function restaurar($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('backup');
        }

        $confirmacion = $_POST['confirmacion'] ?? '';
        if ($confirmacion !== 'RESTAURAR') {
            $this->setFlash('Debes escribir "RESTAURAR" para confirmar', 'danger');
            $this->redirect('backup');
        }

        $resultado = $this->backupModel->restaurar($id);
        
        if ($resultado['success']) {
            $this->setFlash('✅ ' . $resultado['message'], 'success');
        } else {
            $this->setFlash('❌ ' . $resultado['message'], 'danger');
        }
        
        $this->redirect('backup');
    }

    /**
     * Limpiar backups antiguos
     */
    public function limpiar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('backup');
        }

        $dias = (int)($_POST['dias'] ?? 30);
        $eliminados = $this->backupModel->limpiarAntiguos($dias);
        
        $this->setFlash(
            "Se eliminaron {$eliminados} backup(s) con más de {$dias} días de antigüedad", 
            'info'
        );
        
        $this->redirect('backup');
    }
}