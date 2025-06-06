<?php
// controllers/AnimalController.php

// Permitir peticiones CORS (ajusta el origen según tu dominio)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';        // Debe definir $pdo (instancia PDO)
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
        // 1) Leer y decodificar body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'JSON inválido'
            ]);
            return;
        }

        // 2) Validar campos obligatorios
        $required = [
            'nombre','especie','raza','edad',
            'localidad','sexo','tamano',
            'descripcion','imagen','idUsuario'
        ];
        foreach ($required as $field) {
            if (empty($data->$field) && $data->$field !== "0") {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Falta el campo $field"
                ]);
                return;
            }
        }

        // 3) Mapear a modelo Animal
        $animal = new Animal($this->db);
        $animal->nombre      = $data->nombre;
        $animal->especie     = $data->especie;
        $animal->raza        = $data->raza;
        $animal->edad        = $data->edad;
        $animal->localidad   = $data->localidad;
        $animal->sexo        = $data->sexo;
        $animal->tamano      = $data->tamano;
        $animal->descripcion = $data->descripcion;
        // Decodificamos la cadena Base64 para obtener binario
        $animal->imagen      = base64_decode($data->imagen);
        $animal->idUsuario   = (int)$data->idUsuario;

        // 4) Intentar crear el registro
        if ($animal->crear()) {
            echo json_encode([
                'success' => true,
                'message' => 'Animal creado correctamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar en la base de datos'
            ]);
        }
    }

    /**
     * Elimina un animal dado su ID.
     * Devuelve un array con 'success' y 'message'.
     */
    public function eliminar(int $id) {
        // 1) Verificar que el animal existe
        $modelo = new Animal($this->db);
        $existe = $modelo->obtenerPorId($id);
        if (!$existe) {
            return ['success' => false, 'message' => "Animal con ID $id no existe"];
        }

        // 2) Intentar eliminar
        if ($modelo->eliminar($id)) {
            return ['success' => true, 'message' => "Animal con ID $id eliminado correctamente"];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el animal de la BD'];
        }
    }

    /**
     * Actualiza los datos de un animal existente.
     * Lee los datos en JSON desde php://input.
     */
    public function update() {
        // 1) Leer y decodificar el body
        $raw = file_get_contents('php://input');
        $data = json_decode($raw);

        if (!$data) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'JSON inválido'
            ]);
            return;
        }

        // 2) Validar campos obligatorios (incluye idAnimal)
        $required = [
            'idAnimal','nombre','especie','raza','edad',
            'localidad','sexo','tamano',
            'descripcion','imagen','idUsuario'
        ];
        foreach ($required as $field) {
            if (!isset($data->$field) || (empty($data->$field) && $data->$field !== "0")) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Falta el campo $field"
                ]);
                return;
            }
        }

        // 3) Mapear a modelo Animal
        $animal = new Animal($this->db);
        $animal->idAnimal    = (int)$data->idAnimal;
        $animal->nombre      = $data->nombre;
        $animal->especie     = $data->especie;
        $animal->raza        = $data->raza;
        $animal->edad        = $data->edad;
        $animal->localidad   = $data->localidad;
        $animal->sexo        = $data->sexo;
        $animal->tamano      = $data->tamano;
        $animal->descripcion = $data->descripcion;
        // Decodificamos la cadena Base64 para obtener binario
        $animal->imagen      = base64_decode($data->imagen);
        $animal->idUsuario   = (int)$data->idUsuario;

        // 4) Intentar actualizar el registro
        if ($animal->actualizar()) {
            echo json_encode([
                'success' => true,
                'message' => 'Animal actualizado correctamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el animal en la base de datos'
            ]);
        }
    }

    /**
     * Devuelve un solo animal por su ID
     */
    public function obtenerPorId(int $id) {
        $modelo = new Animal($this->db);
        return $modelo->obtenerPorId($id);
    }

    /**
     * NUEVO: Devuelve los 5 animales más recientes en JSON.
     * Utiliza el método obtenerUltimos() del modelo Animal.
     */
    public function obtenerUltimos() {
        $modelo = new Animal($this->db);
        $stmt = $modelo->obtenerUltimos();
        $animales = [];

        // Convertir cada registro: binario de imagen a Base64
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['imagen'] = base64_encode($row['imagen']);
            $animales[] = $row;
        }

        echo json_encode($animales);
    }
}
