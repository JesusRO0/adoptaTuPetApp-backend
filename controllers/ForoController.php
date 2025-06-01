<?php

require_once __DIR__ . '/../models/Mensaje.php';

class ForoController {
    private $model;

    public function __construct($db) {
        $this->model = new Mensaje($db);
    }

    /**
     * Devuelve PDOStatement con todos los mensajes del foro.
     */
    public function listarMensajes($idUsuarioActual = null) {
        return $this->model->getAll($idUsuarioActual);
    }

    /**
     * Agrega un nuevo mensaje al foro.
     * Retorna array con datos del mensaje creado o false si hay error.
     */
    public function add($idUsuario, $texto, $imagenBase64 = null) {
        $imagenBin = null;
        if (!empty($imagenBase64)) {
            $imagenBin = base64_decode($imagenBase64);
        }
        return $this->model->create($idUsuario, $texto, $imagenBin);
    }

    /**
     * (Opcional) Actualiza likes si tu lógica lo requiere.
     */
    public function updateLikes($idPost, $likeCount) {
        return $this->model->updateLikes($idPost, $likeCount);
    }
}
