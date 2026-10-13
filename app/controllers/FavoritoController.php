<?php
class FavoritoController extends Controller {
    private $favoritoModel;

    public function __construct() {
        $this->requireAuth();
        $this->favoritoModel = new Favorito();
    }

    /**
     * Alternar favorito (AJAX)
     */
    public function toggle() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $usuario_id = $_SESSION['user_id'];
        $modulo = trim($_POST['modulo'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $icono = trim($_POST['icono'] ?? 'fa-circle');
        $url = trim($_POST['url'] ?? '');

        if (empty($modulo) || empty($titulo) || empty($url)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $result = $this->favoritoModel->toggle($usuario_id, $modulo, $titulo, $icono, $url);
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Obtener lista de favoritos (AJAX)
     */
    public function listar() {
        $usuario_id = $_SESSION['user_id'];
        $favoritos = $this->favoritoModel->getByUsuario($usuario_id);
        header('Content-Type: application/json');
        echo json_encode($favoritos);
    }

    /**
     * Eliminar favorito (AJAX)
     */
    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $usuario_id = $_SESSION['user_id'];
        $modulo = trim($_POST['modulo'] ?? '');

        if (empty($modulo)) {
            echo json_encode(['success' => false, 'message' => 'Módulo no especificado']);
            return;
        }

        $result = $this->favoritoModel->eliminar($usuario_id, $modulo);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
}