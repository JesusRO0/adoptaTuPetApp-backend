<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$response = [
    "success" => false,
    "message" => "Faltan datos obligatorios"
];

$rawData = file_get_contents("php://input");
$data = json_decode($rawData);

if (
    isset($data->email) &&
    isset($data->usuario) &&
    isset($data->localidad) &&
    isset($data->contrasena)
) {
    $controller = new AuthController($pdo);

    $fotoPerfil = null;
    if (!empty($data->fotoPerfil)) {
        $fotoPerfil = base64_decode($data->fotoPerfil);
    }

    $response = $controller->register(
        $data->email,
        $data->usuario,
        $data->localidad,
        $data->contrasena,
        $fotoPerfil
    );
}

echo json_encode($response);
exit;
