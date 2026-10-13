<?php
class ManifiestoController extends Controller {
    private $manifiestoModel;

    public function __construct() {
        $this->requireAuth();
        $this->manifiestoModel = new Manifiesto();
    }

    public function index() {
        $fecha  = $_GET['fecha'] ?? date('Y-m-d');
        $viajes = $this->manifiestoModel->getViajesParaManifiesto($fecha);

        $this->view('manifiesto/index', [
            'viajes' => $viajes,
            'fecha'  => $fecha,
            'titulo' => 'Generar Manifiesto de Pasajeros'
        ]);
    }

    public function asignarConductor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('manifiesto');
            return;
        }

        $viaje_id     = (int)($_POST['viaje_id']     ?? 0);
        $conductor_id = (int)($_POST['conductor_id'] ?? 0);

        if ($viaje_id <= 0) {
            $this->setFlash('Viaje no válido', 'danger');
            $this->redirect('manifiesto');
            return;
        }

        if ($conductor_id <= 0) {
            $this->setFlash('Seleccione un conductor', 'danger');
            $this->redirect('manifiesto/show/' . $viaje_id);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $sql = "UPDATE viajes SET usuario_creador = ? WHERE id = ?";
            $stmt = $db->prepare($sql);

            if ($stmt->execute([$conductor_id, $viaje_id])) {
                $this->setFlash('Conductor asignado correctamente', 'success');
            } else {
                $this->setFlash('Error al asignar conductor', 'danger');
            }
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al asignar conductor: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            $this->setFlash('Error al asignar conductor', 'danger');
        }

        $this->redirect('manifiesto/show/' . $viaje_id);
    }

    public function pdf($viaje_id) {
        $this->redirect('manifiesto/show/' . (int)$viaje_id);
    }

    /**
     * Mostrar el manifiesto con QR
     */
    public function show($viaje_id) {
        $viaje_id = (int)$viaje_id;

        if ($viaje_id <= 0) {
            $this->setFlash('ID de viaje inválido', 'danger');
            $this->redirect('manifiesto');
            return;
        }

        $data = $this->manifiestoModel->getManifiestoData($viaje_id);

        // ✅ FIX: return después de redirect
        if (!$data) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('manifiesto');
            return;
        }

        // Si no tiene conductor asignado, redirigir a asignar
        if (empty($data['viaje']['conductor_nombre'])) {
            $conductores = $this->manifiestoModel->getConductores();

            $this->view('manifiesto/asignar_conductor', [
                'viaje_id'    => $viaje_id,
                'conductores' => $conductores,
                'titulo'      => 'Asignar Conductor'
            ]);
            return;
        }

        // Generar/obtener hash y QR
        $hash   = $this->manifiestoModel->obtenerHash($viaje_id);
        $qrData = $this->generarQR($hash);

        $this->viewStandalone('manifiesto/show', [
            'data'   => $data,
            'qrData' => $qrData,
            'hash'   => $hash,
            'titulo' => 'Manifiesto de Pasajeros'
        ]);
    }

    /**
     * Generar código QR en base64
     */
    private function generarQR($hash) {
        $url = BASE_URL . 'validar/manifiesto/' . $hash;

        try {
            $autoload = __DIR__ . '/../../vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
            }

            if (!class_exists('BaconQrCode\Renderer\ImageRenderer')) {
                throw new Exception('bacon/bacon-qr-code no está disponible');
            }

            // Detectar backend disponible (Imagick o GD)
            $backend = null;
            if (extension_loaded('imagick') && class_exists('BaconQrCode\Renderer\Image\ImagickImageBackEnd')) {
                $backend = new \BaconQrCode\Renderer\Image\ImagickImageBackEnd();
            } elseif (extension_loaded('gd') && class_exists('BaconQrCode\Renderer\Image\GdImageBackEnd')) {
                $backend = new \BaconQrCode\Renderer\Image\GdImageBackEnd();
            } else {
                throw new Exception('Ni Imagick ni GD están disponibles');
            }

            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 2),
                $backend
            );

            $writer    = new \BaconQrCode\Writer($renderer);
            $qrString  = $writer->writeString($url);

            return 'data:image/png;base64,' . base64_encode($qrString);

        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al generar QR del manifiesto: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }

            // Fallback: usar API externa
            return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($url);
        }
    }

    /**
     * Enviar manifiesto por correo electrónico
     */
    public function enviarEmail($viaje_id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('manifiesto');
            return;
        }

        // ✅ FIX: Aceptar viaje_id desde POST si no viene en URL
        if (empty($viaje_id)) {
            $viaje_id = (int)($_POST['viaje_id'] ?? 0);
        }
        $viaje_id = (int)$viaje_id;

        if ($viaje_id <= 0) {
            $this->setFlash('ID de viaje no válido', 'danger');
            $this->redirect('manifiesto');
            return;
        }

        $destinatario = trim($_POST['email'] ?? '');

        if (empty($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('Ingrese un correo electrónico válido', 'danger');
            $this->redirect('manifiesto/show/' . $viaje_id);
            return;
        }

        // Obtener datos del manifiesto
        $data = $this->manifiestoModel->getManifiestoData($viaje_id);

        if (!$data) {
            $this->setFlash('Viaje no encontrado', 'danger');
            $this->redirect('manifiesto');
            return;
        }

        // Obtener hash
        $hash = $this->manifiestoModel->obtenerHash($viaje_id);

        // ✅ FIX: Verificar que EmailSender existe
        if (!class_exists('EmailSender')) {
            $this->setFlash('El sistema de email no está configurado. Contacte al administrador.', 'danger');
            $this->redirect('manifiesto/show/' . $viaje_id);
            return;
        }

        try {
            $emailSender = new EmailSender();

            if ($emailSender->enviarManifiesto($destinatario, $viaje_id, $data, $hash)) {
                $this->setFlash('Manifiesto enviado correctamente a ' . $destinatario, 'success');
            } else {
                $this->setFlash('Error al enviar el manifiesto. Verifique la configuración SMTP.', 'danger');
            }
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                try { Logger::error('Error al enviar manifiesto por email: ' . $e->getMessage()); }
                catch (Exception $le) { error_log($le->getMessage()); }
            }
            $this->setFlash('Error al enviar el manifiesto: ' . $e->getMessage(), 'danger');
        }

        $this->redirect('manifiesto/show/' . $viaje_id);
    }

    public function historial() {
        $paginacion = $this->getPaginacion();

        $filtros = [
            'buscar'       => $_GET['buscar']       ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin'    => $_GET['fecha_fin']    ?? ''
        ];

        $resultado = $this->manifiestoModel->getHistorialPaginated(
            $paginacion['pagina'],
            $paginacion['por_pagina'],
            $filtros
        );

        $this->view('manifiesto/historial', [
            'titulo'      => 'Historial de Manifiestos',
            'manifiestos' => $resultado['datos'],
            'paginator'   => $resultado['paginator'],
            'filtros'     => $filtros
        ]);
    }
}