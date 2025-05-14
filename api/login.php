<?php
require_once 'config/database.php';
require_once 'controllers/AuthController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Obtener y decodificar los datos recibidos por POST
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->contrasena)) {
    $controller = new AuthController($pdo);

    $response = $controller->login($data->email, $data->contrasena);

    // Devolver la respuesta del controlador
    echo json_encode($response);
} else {
    // Error por falta de campos
    echo json_encode([
        "success" => false,
        "message" => "Faltan el email o la contraseña"
    ]);
}
