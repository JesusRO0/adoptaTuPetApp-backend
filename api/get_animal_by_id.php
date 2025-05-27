<?php
// api/get_animal_by_id.php

// Cabeceras CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1) Incluimos la configuración de BD
require_once __DIR__ . '/../config/db.php';     // define $pdo

// 2) Incluimos el controlador de animales
require_once __DIR__ . '/../controllers/AnimalController.php';

// 3) Instanciamos y llamamos al método de obtener por ID
$ctrl = new AnimalController($pdo);

// Validamos parámetro
if (!isset($_GET['idAnimal'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Falta idAnimal'
    ]);
    exit;
}

$id = (int) $_GET['idAnimal'];
$result = $ctrl->obtenerPorId($id);

if (!$result) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => "Animal con ID $id no encontrado"
    ]);
    exit;
}

// El campo imagen viene en binario; lo convertimos a Base64
$result['imagen'] = base64_encode($result['imagen']);

// Devolvemos el objeto
echo json_encode([
    'success' => true,
    'animal'  => $result
]);
