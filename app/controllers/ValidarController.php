<?php
class ValidarController extends Controller {
    private $manifiestoModel;

    public function __construct() {
        $this->requireAuth(); // 🔒 Proteger
        $this->manifiestoModel = new Manifiesto();
    }

    /**
     * Página pública de validación del manifiesto
     */
    public function manifiesto($hash) {
        $data = $this->manifiestoModel->getManifiestoByHash($hash);
        if (!$data) {
            // Mostrar error
            $this->view('validar/error', ['mensaje' => 'Manifiesto no encontrado o inválido', 'titulo' => 'Error de Validación']);
            return;
        }

        $this->view('validar/manifiesto', [
            'data' => $data,
            'titulo' => 'Validación de Manifiesto'
        ]);
    }
}