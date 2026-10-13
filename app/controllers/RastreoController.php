<?php
class RastreoController extends Controller {
    private $encomiendaModel;

    public function __construct() {
        // ⚠️ NO requiere autenticación - es público
        $this->encomiendaModel = new Encomienda();
    }

    /**
     * Página principal de rastreo
     */
    public function index() {
        $guia = $_GET['guia'] ?? '';
        $encomienda = null;
        $error = null;

        // Si se ingresó una guía, buscarla
        if (!empty($guia)) {
            $guia = strtoupper(trim($guia));
            
            // Validación básica del formato (GUI-XXXXXXXX)
            if (!preg_match('/^GUI-[0-9]{8,}$/', $guia)) {
                $error = 'El formato de la guía no es válido. Ejemplo: GUI-20260001';
            } else {
                $encomienda = $this->encomiendaModel->findByGuia($guia);
                if (!$encomienda) {
                    $error = 'No se encontró ninguna encomienda con esa guía. Verifique el número e intente nuevamente.';
                }
            }
        }

        // Cargar vista pública (SIN layout del sistema)
        $this->viewStandalone('rastreo/index', [
            'titulo' => 'Rastreo de Encomienda',
            'guia' => $guia,
            'encomienda' => $encomienda,
            'error' => $error
        ]);
    }

    /**
     * Búsqueda AJAX de encomienda por guía
     */
    public function buscar() {
        header('Content-Type: application/json');

        // Rate limiting básico
        if (isset($_SESSION['ultimo_rastreo']) && (time() - $_SESSION['ultimo_rastreo']) < 2) {
            echo json_encode(['error' => 'Por favor espere unos segundos antes de buscar nuevamente']);
            exit;
        }
        $_SESSION['ultimo_rastreo'] = time();

        $guia = strtoupper(trim($_GET['guia'] ?? ''));
        
        if (empty($guia)) {
            echo json_encode(['error' => 'Ingrese un número de guía']);
            exit;
        }

        // Validar formato
        if (!preg_match('/^GUI-[0-9]{8,}$/', $guia)) {
            echo json_encode(['error' => 'Formato de guía inválido. Ejemplo: GUI-20260001']);
            exit;
        }

        // Buscar la encomienda
        $encomienda = $this->encomiendaModel->findByGuia($guia);
        
        if (!$encomienda) {
            echo json_encode(['error' => 'No se encontró ninguna encomienda con esa guía']);
            exit;
        }

        // Obtener datos completos
        $data = $this->encomiendaModel->getByIdWithDetails($encomienda['id']);
        
        // Ocultar datos sensibles para el público
        $data_publica = [
            'guia' => $data['guia'],
            'estado' => $data['estado'],
            'fecha_registro' => $data['fecha_registro'],
            'fecha_entrega' => $data['fecha_entrega'],
            'origen' => $data['origen'],
            'destino' => $data['destino'],
            'placa' => $data['placa'],
            'fecha_salida' => $data['fecha_salida'],
            'fecha_llegada' => $data['fecha_llegada'],
            'peso_kg' => $data['peso_kg'],
            'descripcion' => $data['descripcion'],
            'destinatario_nombre' => substr($data['destinatario_nombre'], 0, 1) . '*** ' . substr($data['destinatario_apellido'], 0, 1) . '***',
            'remitente_nombre' => substr($data['remitente_nombre'], 0, 1) . '*** ' . substr($data['remitente_apellido'], 0, 1) . '***',
        ];

        echo json_encode(['success' => true, 'data' => $data_publica]);
        exit;
    }
}
