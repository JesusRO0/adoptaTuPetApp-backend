<?php
// api/get_mensajes.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ForoController.php';

// Si quieres marcar “likedByUser” usa el idUsuario desde headers o token;
// aquí suponemos que te lo pasan en JSON o en header (ej: Authorization).
// Para simplificar, lo definimos a 0 (no autenticado) o leerlo de un parámetro.

$idUsuarioActual = 0;
if (!empty($_GET['idUsuario'])) {
    $idUsuarioActual = intval($_GET['idUsuario']);
}

$foroCtrl = new ForoController($pdo);
$stmt = $foroCtrl->listarMensajes($idUsuarioActual);

$mensajes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil BLOB a Base64 si no es nulo
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }
    // Convertir imagen BLOB a Base64 si no es nulo
    if (!empty($row['imagen'])) {
        $row['imagenMensaje'] = base64_encode($row['imagen']);
    } else {
        $row['imagenMensaje'] = "";
    }
    // Ajustar para que coincida con Mensaje.java en Android
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['idUsuario'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        "likeCount"        => (int)$row['likeCount'],
        "likedByUser"      => (bool)$row['likedByUser']
    ];
}

echo json_encode($mensajes);
exit;
