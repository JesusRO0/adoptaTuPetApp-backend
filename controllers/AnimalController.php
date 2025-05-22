<?php
// controllers/AnimalController.php

require_once __DIR__ . '/../models/Animal.php';

class AnimalController {
    private $db;

    public function __construct(PDO $pdo) {
        $this->db = $pdo;
    }

    /**
     * Añade un nuevo animal leyendo JSON desde php://input
     * y devuelve un JSON con success/message.
     */
    public function add() {
        // 1. Leer body
        $data = json_decode(file_get_contents('php://input'));

        // 2. Validar campos obligatorios
        $required = ['nombre','especie','raza','edad','localidad','sexo','tamano','descripcion','imagen','idUsuario'];
        foreach ($required as $field) {
            if (empty($data->$field)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Falta el campo $field"
                ]);
                return;
            }
        }

        // 3. Mapear a modelo y guardar
        $animal = new Animal($this->db);
        $animal->nombre     = $data->nombre;
        $animal->especie    = $data->especie;
        $animal->raza       = $data->raza;
        $animal->edad       = $data->edad;
        $animal->localidad  = $data->localidad;
        $animal->sexo       = $data->sexo;
        $animal->tamano     = $data->tamano;
        $animal->descripcion= $data->descripcion;
        $animal->imagen     = base64_decode($data->imagen);
        $animal->idUsuario  = $data->idUsuario;

        if ($animal->crear()) {
            echo json_encode(['success'=>true,'message'=>'Animal creado correctamente']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Error al guardar en BD']);
        }
    }
}
