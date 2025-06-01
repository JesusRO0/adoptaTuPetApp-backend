<?php 
// api/get_mensajes_usuario.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/ForoController.php';

// Verificamos que nos pasen idUsuario por GET
if (empty($_GET['idUsuario'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta idUsuario"
    ]);
    exit;
}

$idUsuario = intval($_GET['idUsuario']);

// Consulta sobre tabla `post` (en vez de “mensajes”)
$stmt = $pdo->prepare("
    SELECT 
        p.idPost       AS idPost,
        p.idUsuario    AS idUsuario,
        u.usuario      AS usuarioNombre,
        u.fotoPerfil   AS fotoPerfil,
        p.contenido    AS contenido,
        p.fecha        AS fecha,
        p.imagen       AS imagen,
        p.likeCount    AS likeCount
    FROM post p
    JOIN usuario u ON p.idUsuario = u.idUsuario
    WHERE p.idUsuario = ?
    ORDER BY p.fecha DESC
");
$stmt->execute([$idUsuario]);

$mensajes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertimos fotoPerfil (BLOB) a Base64 si existe
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }

    // Convertimos imagen del post a Base64 si existe
    if (!empty($row['imagen'])) {
        $row['imagenMensaje'] = base64_encode($row['imagen']);
    } else {
        $row['imagenMensaje'] = "";
    }

    // Ajustamos la estructura para que coincida con Mensaje.java
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['idUsuario'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        "likeCount"        => (int)$row['likeCount'],
        "likedByUser"      => false
    ];
}

echo json_encode($mensajes);
exit;
?>
