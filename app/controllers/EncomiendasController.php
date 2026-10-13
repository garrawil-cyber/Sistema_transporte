<?php
class EncomiendasController extends Controller {
    private $encomiendaModel;
    private $clienteModel;

    public function __construct() {
        $this->requireAuth(); // 🔒 Proteger
        $this->encomiendaModel = new Encomienda();
        $this->clienteModel = new Cliente();
    }

    // Listar todas las encomiendas
    public function index() {
        $paginacion = $this->getPaginacion();
        
        $filtros = [
            'buscar'       => $_GET['buscar'] ?? '',
            'estado'       => $_GET['estado'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin'    => $_GET['fecha_fin'] ?? ''
        ];
        
        $resultado = $this->encomiendaModel->getPaginated(
            $paginacion['pagina'], 
            $paginacion['por_pagina'], 
            $filtros
        );

        // ✅ KPIs filtrados
        $stats = $this->encomiendaModel->getStatsFiltrados($filtros);
                
        $this->view('encomiendas/index', [
            'encomiendas' => $resultado['datos'],
            'paginator'   => $resultado['paginator'],
            'filtros'     => $filtros,
            'stats'       => $stats,
            'chartData'   => $this->encomiendaModel->getStatsUltimos7Dias(), // ✅ alineado
            'titulo'      => 'Gestión de Encomiendas'
        ]);
    }

    // Crear nueva encomienda
    public function create() {
        $errores = [];
        $data = [];
        $viajes = $this->encomiendaModel->getViajesDisponibles();
        $remitente = null;
        $destinatario = null;

        // Buscar remitente por DNI (vía AJAX o GET para este ejemplo simplificado)
        if (isset($_GET['buscar_remitente'])) {
            $dni = trim($_GET['buscar_remitente']);
            $remitente = $this->clienteModel->findByDocumento($dni);
            if (!$remitente) {
                $errores[] = 'Remitente no encontrado. Debe registrarlo primero en Clientes o completar el formulario manualmente.';
            }
        }

        if (isset($_GET['buscar_destinatario'])) {
            $dni = trim($_GET['buscar_destinatario']);
            $destinatario = $this->clienteModel->findByDocumento($dni);
            if (!$destinatario) {
                $errores[] = 'Destinatario no encontrado. Debe registrarlo primero en Clientes o completar el formulario manualmente.';
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'guardar') {
    // Capturar datos del remitente y destinatario
    $remitente_data = [
        'tipo_documento'    => $_POST['remitente_tipo_doc'] ?? 'DNI',
        'numero_documento'  => trim($_POST['remitente_dni'] ?? ''),
        'nombre'            => trim($_POST['remitente_nombre'] ?? ''),
        'apellido'          => trim($_POST['remitente_apellido'] ?? ''),
        'telefono'          => trim($_POST['remitente_telefono'] ?? ''),
        'email'             => trim($_POST['remitente_email'] ?? ''),
        'direccion'         => trim($_POST['remitente_direccion'] ?? '')
    ];

    $destinatario_data = [
        'tipo_documento'    => $_POST['destinatario_tipo_doc'] ?? 'DNI',
        'numero_documento'  => trim($_POST['destinatario_dni'] ?? ''),
        'nombre'            => trim($_POST['destinatario_nombre'] ?? ''),
        'apellido'          => trim($_POST['destinatario_apellido'] ?? ''),
        'telefono'          => trim($_POST['destinatario_telefono'] ?? ''),
        'email'             => trim($_POST['destinatario_email'] ?? ''),
        'direccion'         => trim($_POST['destinatario_direccion'] ?? '')
    ];

    // Validar campos obligatorios
    if (empty($remitente_data['numero_documento']) || empty($remitente_data['nombre']) || empty($remitente_data['apellido'])) {
        $errores[] = 'Datos del remitente incompletos (DNI, Nombre, Apellido).';
    }
    if (empty($destinatario_data['numero_documento']) || empty($destinatario_data['nombre']) || empty($destinatario_data['apellido'])) {
        $errores[] = 'Datos del destinatario incompletos (DNI, Nombre, Apellido).';
    }
    if (empty($_POST['viaje_id'])) {
        $errores[] = 'Debe seleccionar un viaje.';
    }
    if (empty($_POST['peso_kg']) || $_POST['peso_kg'] <= 0) {
        $errores[] = 'El peso es obligatorio y debe ser mayor a 0.';
    }
    if (empty($_POST['costo_envio']) || $_POST['costo_envio'] <= 0) {
        $errores[] = 'El costo de envío es obligatorio.';
    }

    if (empty($errores)) {
        try {
            // Registrar o actualizar remitente y destinatario
            $remitente_id    = $this->clienteModel->findOrCreate($remitente_data);
            $destinatario_id = $this->clienteModel->findOrCreate($destinatario_data);

            // Generar guía
            $guia = $this->encomiendaModel->generarGuia();

            $encomienda_data = [
                'guia'             => $guia,
                'remitente_id'     => $remitente_id,
                'destinatario_id'  => $destinatario_id,
                'viaje_id'         => (int)$_POST['viaje_id'],
                'usuario_id'       => $_SESSION['user_id'] ?? 1,
                'peso_kg'          => (float)$_POST['peso_kg'],
                'dimensiones'      => trim($_POST['dimensiones'] ?? ''),
                'descripcion'      => trim($_POST['descripcion'] ?? ''),
                'valor_declarado'  => (float)($_POST['valor_declarado'] ?? 0),
                'costo_envio'      => (float)$_POST['costo_envio']
            ];

            // ✅ CORREGIDO: registrar() ahora devuelve el ID (int) o false
            $encomienda_id = $this->encomiendaModel->registrar($encomienda_data);

            if ($encomienda_id) {
                // ✅ ENVÍO AUTOMÁTICO POR EMAIL
                $email_destino = $destinatario_data['email'] ?: $remitente_data['email'];

                if (!empty($email_destino) && filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $resultado = $this->encomiendaModel->enviarGuiaPorEmail($encomienda_id, $email_destino);

                        if ($resultado['success']) {
                            $this->setFlash('Encomienda registrada con éxito. Guía: ' . $guia . ' (enviada a ' . $email_destino . ')', 'success');
                        } else {
                            $this->setFlash('Encomienda registrada. Guía: ' . $guia . ' (el email no pudo enviarse)', 'warning');
                        }
                    } catch (Exception $e) {
                        Logger::error('Error al enviar guía automática: ' . $e->getMessage());
                        $this->setFlash('Encomienda registrada. Guía: ' . $guia, 'info');
                    }
                } else {
                    $this->setFlash('Encomienda registrada con éxito. Guía: ' . $guia, 'success');
                }

                $this->redirect('encomiendas/show/' . $encomienda_id);
                return;
            } else {
                $errores[] = 'No se pudo registrar la encomienda. Intente nuevamente.';
            }

        } catch (Exception $e) {
            Logger::error('Error al registrar encomienda: ' . $e->getMessage());
            $errores[] = 'Error al guardar: ' . $e->getMessage();
        }
    }
}

        // ✅ NUEVO: KPIs del día
        $stats = $this->encomiendaModel->getStatsHoy();
        $this->view('encomiendas/create', [
            'titulo'       => 'Nueva Encomienda',
            'viajes'       => $viajes,
            'remitente'    => $remitente,
            'destinatario' => $destinatario,
            'errores'      => $errores,
            'stats'        => $stats,           // ✅ NUEVO
            'data'         => $_POST ?? []
        ]);
    }

    // Ver detalle de una encomienda
    public function show($id) {
        $encomienda = $this->encomiendaModel->getByIdWithDetails($id);
        if (!$encomienda) {
            $this->setFlash('Encomienda no encontrada', 'danger');
            $this->redirect('encomiendas');
        }
        $this->view('encomiendas/show', [
            'encomienda' => $encomienda,
            'titulo' => 'Detalle de Encomienda'
        ]);
    }

    // Imprimir guía (ticket)
/**
 * Imprimir guía de envío
 */
public function printGuide($id) {
    $encomienda = $this->encomiendaModel->getByIdWithDetails($id);
    if (!$encomienda) {
        $this->setFlash('Encomienda no encontrada', 'danger');
        $this->redirect('encomiendas');
    }
    
    // Cargar la vista SIN layout (usando un método especial)
    $this->viewStandalone('encomiendas/guia', [
        'encomienda' => $encomienda,
        'titulo' => 'Guía de Envío'
    ]);
}



/**
 * Rastreo público de encomienda por guía (sin login)
 */
public function track($guia) {
    // No requiere autenticación
    $encomienda = $this->encomiendaModel->findByGuia($guia);
    
    if (!$encomienda) {
        http_response_code(404);
        echo "Encomienda no encontrada";
        return;
    }
    
    $this->viewStandalone('encomiendas/track', [
        'encomienda' => $encomienda,
        'titulo' => 'Rastreo de Encomienda'
    ]);
}
    /**
 * Eliminar encomienda
 */
public function delete($id) {
    $this->requireAuth();
    
    $encomienda = $this->encomiendaModel->find($id);
    if (!$encomienda) {
        $this->setFlash('Encomienda no encontrada', 'danger');
        $this->redirect('encomiendas');
    }

    // Verificar permisos: solo admin o quien la registró
    if ($_SESSION['rol_id'] != 1 && $encomienda['usuario_id'] != $_SESSION['user_id']) {
        $this->setFlash('No tienes permisos para eliminar esta encomienda', 'danger');
        $this->redirect('encomiendas');
    }

    // Verificar que no esté entregada
    if ($encomienda['estado'] == 'ENTREGADO') {
        $this->setFlash('No se puede eliminar una encomienda ya entregada', 'warning');
        $this->redirect('encomiendas');
    }

    // Registrar en auditoría
    Auditoria::registrar('DELETE', [
        'tabla_afectada' => 'encomiendas',
        'modulo' => 'encomiendas',
        'registro_id' => $id,
        'descripcion' => 'Eliminó encomienda ' . $encomienda['guia'],
        'valores_anteriores' => $encomienda
    ]);

    if ($this->encomiendaModel->delete($id)) {
        $this->setFlash('Encomienda eliminada correctamente', 'success');
    } else {
        $this->setFlash('Error al eliminar la encomienda', 'danger');
    }
    
    $this->redirect('encomiendas');
}

/**
 * Actualizar estado de la encomienda (rápido desde el listado)
 */
public function updateStatus($id) {
    $this->requireAuth();
    
    $encomienda = $this->encomiendaModel->find($id);
    if (!$encomienda) {
        $this->setFlash('Encomienda no encontrada', 'danger');
        $this->redirect('encomiendas');
    }

    $nuevo_estado = $_POST['estado'] ?? $_GET['estado'] ?? '';
    $estados_validos = ['REGISTRADO', 'EN_BODEGA', 'EN_TRANSITO', 'ENTREGADO', 'DEVUELTO'];

    if (!in_array($nuevo_estado, $estados_validos)) {
        $this->setFlash('Estado no válido', 'danger');
        $this->redirect('encomiendas');
    }

    $fecha_entrega = ($nuevo_estado === 'ENTREGADO') ? date('Y-m-d H:i:s') : null;

    if ($this->encomiendaModel->actualizarEstado($id, $nuevo_estado, $fecha_entrega)) {
        // Registrar en auditoría
        Auditoria::registrar('UPDATE', [
            'tabla_afectada' => 'encomiendas',
            'modulo' => 'encomiendas',
            'registro_id' => $id,
            'descripcion' => 'Cambió estado de ' . $encomienda['guia'] . ' a ' . $nuevo_estado,
            'valores_anteriores' => ['estado' => $encomienda['estado']],
            'valores_nuevos' => ['estado' => $nuevo_estado]
        ]);
        
        $this->setFlash('Estado actualizado a ' . $nuevo_estado, 'success');
    } else {
        $this->setFlash('Error al actualizar el estado', 'danger');
    }
    
    $this->redirect('encomiendas');
}

/**
 * Enviar guía por email
 */
public function enviarEmail($id) {
    $this->requireAuth();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('encomiendas/show/' . $id);
    }

    $email = trim($_POST['email'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->setFlash('Ingrese un correo electrónico válido', 'danger');
        $this->redirect('encomiendas/show/' . $id);
    }

    $resultado = $this->encomiendaModel->enviarGuiaPorEmail($id, $email, $mensaje);

    if ($resultado['success']) {
        $this->setFlash('✅ ' . $resultado['message'], 'success');
    } else {
        $this->setFlash('❌ ' . $resultado['message'], 'danger');
    }

    $this->redirect('encomiendas/show/' . $id);
}
}