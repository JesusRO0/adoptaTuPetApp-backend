<?php
// api/delete_animal.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AnimalController.php';

// Leer JSON de la petición
$input = json_decode(file_get_contents('php://input'), true);
$idAnimal = isset($input['idAnimal']) ? intval($input['idAnimal']) : null;

// Validar
if (!$idAnimal) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Parámetro 'idAnimal' faltante o inválido."
    ]);
    exit;
}

// Instanciar controlador y eliminar
$ctrl   = new AnimalController($pdo);
$result = $ctrl->eliminar($idAnimal);

// Devolver JSON según el resultado
echo json_encode([
    "success" => $result['success'],
    "message" => $result['message']
]);
