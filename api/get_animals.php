<?php
// api/get_animals.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1) Incluye tu configuración de BD
require_once __DIR__ . '/../config/db.php';

// 2) Incluye tu modelo y controlador
require_once __DIR__ . '/../models/Animal.php';

class AnimalController {
    private $model;
    public function __construct($db) {
        $this->model = new Animal($db);
    }

    // Devuelve el PDOStatement de todos los animales
    public function obtenerTodos() {
        return $this->model->obtenerTodos();
    }
}

$ctrl = new AnimalController($pdo);
$stmt = $ctrl->obtenerTodos();

$animales = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // El campo imagen en BD es LONGBLOB; lo convertimos a Base64
    $row['imagen'] = base64_encode($row['imagen']);
    $animales[] = $row;
}

// 3) Devolvemos el JSON
echo json_encode($animales);
