<?php
class Router {
    private $controller = DEFAULT_CONTROLLER;
    private $method = DEFAULT_METHOD;
    private $params = [];

    public function __construct() {
        $url = $this->getUrl();

        // Determinar controlador
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = ucfirst($url[0]) . 'Controller';
            $controllerFile = '../app/controllers/' . $controllerName . '.php';
            
            if (file_exists($controllerFile)) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // ✅ Controlador no encontrado → 404
                ErrorHandler::error404("Controlador no encontrado: {$url[0]}");
                return;
            }
        } else {
            $this->controller = DEFAULT_CONTROLLER . 'Controller';
        }

        // Verificar existencia del archivo
        $controllerFile = '../app/controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            ErrorHandler::error404("Controlador no encontrado: {$this->controller}");
            return;
        }

        require_once $controllerFile;
        $this->controller = new $this->controller;

        // Determinar método
        if (isset($url[1]) && !empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // ✅ Método no encontrado → 404
                ErrorHandler::error404("Método no encontrado: {$url[1]}");
                return;
            }
        }

        // Parámetros
        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}