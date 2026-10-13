<?php
$modulos = ['ventas', 'encomiendas', 'giros', 'manifiesto', 'rutas', 'usuarios'];

foreach ($modulos as $modulo) {
    // Crear controlador
    $controllerFile = __DIR__ . "/app/controllers/" . ucfirst($modulo) . "Controller.php";
    if (!file_exists($controllerFile)) {
        $contenido = '<?php
class ' . ucfirst($modulo) . 'Controller extends Controller {
    public function __construct() {
        $this->requireAuth();
    }
    
    public function index() {
        $this->view(\'' . $modulo . '/index\', [\'titulo\' => \'Gestión de ' . ucfirst($modulo) . '\']);
    }
}';
        file_put_contents($controllerFile, $contenido);
        echo "✅ Creado controlador: " . ucfirst($modulo) . "Controller.php<br>";
    }

    // Crear vista
    $viewFile = __DIR__ . "/app/views/" . $modulo . "/index.php";
    if (!file_exists($viewFile)) {
        $dir = dirname($viewFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $contenido = '<div class="pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">' . ucfirst($modulo) . '</h1>
</div>
<div class="alert alert-success">
    <i class="fas fa-check-circle me-2"></i>
    Módulo de ' . ucfirst($modulo) . ' funcionando correctamente
</div>';
        file_put_contents($viewFile, $contenido);
        echo "✅ Creada vista: " . $modulo . "/index.php<br>";
    }
}
echo "<br>🎉 ¡Estructura creada correctamente!";