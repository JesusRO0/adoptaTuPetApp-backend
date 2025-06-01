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

// Consultamos solo el contenido de los posts hechos por ese usuario.
// La relación entre usuario y post no estaba explícita en bd.sql: 
//    la tabla `post` no tiene columna `idUsuario` en el esquema que compartiste. 
// Por tanto, asumo que en tu base real sí enlazas `post.idPost` con `comentario.idPost` y
// solo se registran comentarios, pero para “historial de publicaciones” quizá te refieras
// al listado de comentarios de ese usuario. Si quieres los comentarios, haz:

$stmt = $pdo->prepare("
    SELECT 
        c.idComentario   AS idComentario,
        c.texto          AS texto,
        c.idUsuario      AS usuarioId,
        u.usuario        AS usuarioNombre,
        u.fotoPerfil     AS fotoPerfil,
        c.idPost         AS idPost,
        p.contenido      AS contenidoOriginal,
        c.idPost         AS idPostRelacion, 
        p.contenido      AS contenidoPost,
        c.idComentario   AS fecha -- tu tabla comentario no tiene fecha, ajústalo si existe
    FROM comentario c
    JOIN usuario u   ON c.idUsuario = u.idUsuario
    LEFT JOIN post p ON c.idPost = p.idPost
   WHERE c.idUsuario = ?
   ORDER BY c.idComentario DESC
");
$stmt->execute([$idUsuario]);

$mensajes = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil (BLOB) a Base64 solo si existe
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }
    // No hay imagen adjunta ni likeCount en tu esquema. Dejo esos campos vacíos o 0.
    $mensajes[] = [
        "idMensaje"        => (int)$row['idComentario'],
        "usuarioId"        => (int)$row['usuarioId'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['texto'],
        "fechaPublicacion" => "",           // tu tabla comentario no tiene campo fecha
        "imagenMensaje"    => "",
        "likeCount"        => 0,
        "likedByUser"      => false
    ];
}

echo json_encode($mensajes);
exit;
?>
