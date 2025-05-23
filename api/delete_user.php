<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents('php://input'));
if (empty($data->email)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Falta el campo email"]);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM usuario WHERE email = :email");
$stmt->bindParam(':email', $data->email);
if ($stmt->execute()) {
    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Usuario eliminado correctamente"]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "No se encontró ese email"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al borrar en BD"]);
}
