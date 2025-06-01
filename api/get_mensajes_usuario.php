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
// Usamos ForoController sólo si necesitas lógica adicional, 
// pero aquí mostramos la consulta SQL directamente:

$stmt = $pdo->prepare("
    SELECT 
        m.idPost      AS idPost,
        m.idUsuario   AS idUsuario,
        u.usuario     AS usuarioNombre,
        u.fotoPerfil  AS fotoPerfil,
        m.contenido   AS contenido,
        m.fecha       AS fecha,
        m.imagen      AS imagen,
        m.likeCount   AS likeCount
    FROM mensajes m
    JOIN usuario u ON m.idUsuario = u.idUsuario
   WHERE m.idUsuario = ?
   ORDER BY m.fecha DESC
");
$stmt->execute([$idUsuario]);

$mensajes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil (BLOB) a Base64
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }
    // Convertir imagen (BLOB) a Base64
    if (!empty($row['imagen'])) {
        $row['imagenMensaje'] = base64_encode($row['imagen']);
    } else {
        $row['imagenMensaje'] = "";
    }
    // Ajustar para que coincida con Mensaje.java
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
