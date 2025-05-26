<?php
// api/delete_animal.php

// Permitir peticiones desde cualquier origen y devolver JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 1) Incluimos la configuración de BD (PDO)
require_once __DIR__ . '/../config/db.php';

// 2) Incluimos el modelo y el controlador de Animal
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../controllers/AnimalController.php';

// 3) Leemos el cuerpo de la petición como JSON
$input = json_decode(file_get_contents('php://input'), true);
$idAnimal = isset($input['idAnimal']) ? intval($input['idAnimal']) : null;

// 4) Validación básica
if ( !$idAnimal ) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta el parámetro idAnimal"
    ]);
    exit;
}

// 5) Instanciamos el controlador y llamamos al método de borrado
$ctrl = new AnimalController($pdo);
$result = $ctrl->eliminar($idAnimal);

// 6) Preparamos la respuesta usando el resultado del controlador
if ( $result['success'] ) {
    echo json_encode([
        "success" => true,
        "message" => "Animal (ID: {$idAnimal}) eliminado correctamente"
    ]);
} else {
    // En caso de fallo, devolvemos el mensaje que retorne el controlador
    echo json_encode([
        "success" => false,
        "message" => $result['message'] ?? "Error al eliminar el animal"
    ]);
}
