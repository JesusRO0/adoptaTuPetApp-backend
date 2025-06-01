<?php
// api/get_mensajes_usuario.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

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

// Consultamos sobre la tabla `post` (ya que ahí está idUsuario, contenido, fecha, imagen)
$stmt = $pdo->prepare("
    SELECT 
      p.idPost       AS idPost,
      p.idUsuario    AS usuarioId,
      u.usuario      AS usuarioNombre,
      u.fotoPerfil   AS fotoPerfil,
      p.contenido    AS contenido,
      p.fecha        AS fecha,
      p.imagen       AS imagen
    FROM post p
    JOIN usuario u   ON p.idUsuario = u.idUsuario
    WHERE p.idUsuario = ?
    ORDER BY p.fecha DESC
");
$stmt->execute([$idUsuario]);

$mensajes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil (BLOB) a Base64, si existe
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }
    // Convertir imagen del post a Base64, si existe
    if (!empty($row['imagen'])) {
        $row['imagenMensaje'] = base64_encode($row['imagen']);
    } else {
        $row['imagenMensaje'] = "";
    }

    // Solo exhibimos los campos que tu modelo Mensaje.java espera:
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['usuarioId'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        // Como tu tabla `post` no almacena directamente likeCount,
        // devolvemos 0 y false para likedByUser por defecto:
        "likeCount"        => 0,
        "likedByUser"      => false
    ];
}

echo json_encode($mensajes);
exit;
?>
