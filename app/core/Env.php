<?php
/**
 * Lector de archivo .env
 * Carga las variables en $_ENV y las expone vía Env::get()
 */
class Env {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Carga el archivo .env
     * @param string $path Ruta al archivo .env
     */
    public static function load($path) {
        if (self::$loaded) {
            return;
        }

        if (!file_exists($path)) {
            throw new Exception("Archivo .env no encontrado en: {$path}");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            // Separar en clave=valor
            if (strpos($line, '=') === false) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Quitar comillas
            if (preg_match('/^"(.*)"$/', $value, $m)) {
                $value = $m[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $m)) {
                $value = $m[1];
            }

            // Convertir booleanos y nulos
            $lower = strtolower($value);
            if ($lower === 'true')  $value = true;
            elseif ($lower === 'false') $value = false;
            elseif ($lower === 'null')  $value = null;

            self::$variables[$key] = $value;

            // También exponer en $_ENV y putenv (compatibilidad)
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }

        self::$loaded = true;
    }

    /**
     * Obtener una variable
     * @param string $key
     * @param mixed  $default Valor por defecto
     * @return mixed
     */
    public static function get($key, $default = null) {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        return $default;
    }

    /**
     * Verificar si una variable existe
     */
    public static function has($key) {
        return array_key_exists($key, self::$variables) || isset($_ENV[$key]);
    }
}