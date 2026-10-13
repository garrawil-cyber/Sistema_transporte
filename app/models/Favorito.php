<?php
class Favorito extends Model {
    protected $table = 'usuario_favoritos';

    /**
     * Obtener los favoritos de un usuario, ordenados
     */
    public function getByUsuario($usuario_id) {
        $sql = "SELECT * FROM usuario_favoritos 
                WHERE usuario_id = ? 
                ORDER BY orden ASC, fecha_agregado ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id]);
        return $stmt->fetchAll();
    }

    /**
     * Verificar si un módulo es favorito del usuario
     */
    public function isFavorito($usuario_id, $modulo) {
        $sql = "SELECT id FROM usuario_favoritos WHERE usuario_id = ? AND modulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$usuario_id, $modulo]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * Agregar un favorito
     */
    public function agregar($usuario_id, $modulo, $titulo, $icono, $url) {
        // Verificar límite (máximo 8 favoritos)
        $favoritos = $this->getByUsuario($usuario_id);
        if (count($favoritos) >= 8) {
            return ['success' => false, 'message' => 'Máximo 8 favoritos permitidos'];
        }

        // Verificar si ya existe
        if ($this->isFavorito($usuario_id, $modulo)) {
            return ['success' => false, 'message' => 'Ya está en favoritos'];
        }

        // Obtener el último orden
        $orden = count($favoritos);

        $sql = "INSERT INTO usuario_favoritos (usuario_id, modulo, titulo, icono, url, orden) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$usuario_id, $modulo, $titulo, $icono, $url, $orden]);

        return ['success' => $result, 'message' => $result ? 'Agregado a favoritos' : 'Error al agregar'];
    }

    /**
     * Eliminar un favorito
     */
    public function eliminar($usuario_id, $modulo) {
        $sql = "DELETE FROM usuario_favoritos WHERE usuario_id = ? AND modulo = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$usuario_id, $modulo]);
    }

    /**
     * Alternar (agregar/eliminar) un favorito
     */
    public function toggle($usuario_id, $modulo, $titulo, $icono, $url) {
        if ($this->isFavorito($usuario_id, $modulo)) {
            $this->eliminar($usuario_id, $modulo);
            return ['success' => true, 'action' => 'removed', 'message' => 'Eliminado de favoritos'];
        } else {
            $result = $this->agregar($usuario_id, $modulo, $titulo, $icono, $url);
            if ($result['success']) {
                return ['success' => true, 'action' => 'added', 'message' => 'Agregado a favoritos'];
            }
            return ['success' => false, 'action' => 'error', 'message' => $result['message']];
        }
    }
}