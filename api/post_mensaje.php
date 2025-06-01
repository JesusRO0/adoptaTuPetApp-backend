<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ForoController.php';

// Leer JSON de entrada
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['usuarioId']) || empty($data['texto'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan usuarioId o texto"
    ]);
    exit;
}

$idUsuario     = intval($data['usuarioId']);
$texto         = trim($data['texto']);
$imagenBase64  = !empty($data['imagenMensaje']) ? $data['imagenMensaje'] : null;

$foroCtrl = new ForoController($pdo);
$nuevoMensaje = $foroCtrl->add($idUsuario, $texto, $imagenBase64);

if ($nuevoMensaje) {
    // Devolver SOLO el objeto "mensaje" directamente, sin envoltorio
    // El array $nuevoMensaje ya contiene las claves: idPost, idUsuario, usuarioNombre, fotoPerfil (binario), contenido, fecha, imagen (binario), likeCount, likedByUser
    // Adaptamos nombres a los que usa Android (Mensaje.java)
    $salida = [
        "idMensaje"        => (int)$nuevoMensaje['idPost'],
        "usuarioId"        => (int)$nuevoMensaje['idUsuario'],
        "usuarioNombre"    => $nuevoMensaje['usuarioNombre'],
        "fotoPerfil"       => !empty($nuevoMensaje['fotoPerfil'])
                                 ? base64_encode($nuevoMensaje['fotoPerfil'])
                                 : "",
        "texto"            => $nuevoMensaje['contenido'],
        "fechaPublicacion" => $nuevoMensaje['fecha'],
        "imagenMensaje"    => !empty($nuevoMensaje['imagen'])
                                 ? base64_encode($nuevoMensaje['imagen'])
                                 : "",
        "likeCount"        => 0,
        "likedByUser"      => false
    ];

    echo json_encode($salida);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al crear el mensaje"
    ]);
}
exit;
