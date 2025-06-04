<?php
// api/get_mensajes.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

// Si quieres marcar “likedByUser” usa el idUsuario desde GET (ej: ?idUsuario=123)
$idUsuarioActual = 0;
if (!empty($_GET['idUsuario'])) {
    $idUsuarioActual = intval($_GET['idUsuario']);
}

/**
 * Trae todos los posts del foro, junto con:
 *   - likeCount     = total de likes para cada post
 *   - commentCount  = total de comentarios para cada post (nueva columna en `post`)
 *   - likedByUser   = si el usuario actual ya dio “like” a ese post
 */
$stmt = $pdo->prepare("
    SELECT
      p.idPost                    AS idPost,
      p.idUsuario                 AS usuarioId,
      u.usuario                   AS usuarioNombre,
      u.fotoPerfil                AS fotoPerfil,
      p.contenido                 AS contenido,
      p.fecha                     AS fecha,
      p.imagen                    AS imagen,

      /* 1) Total de likes de este post (tabla `likepost`) */
      COALESCE(l.totalLikes, 0)   AS likeCount,

      /* 2) Total de comentarios de este post (columna nueva en post) */
      COALESCE(p.commentCount, 0) AS commentCount,

      /* 3) Saber si el usuario actual (idUsuarioActual) marcó like */
      CASE 
        WHEN ul.idUsuario IS NULL THEN 0 
        ELSE 1 
      END                          AS likedByUser

    FROM post p
    JOIN usuario u ON p.idUsuario = u.idUsuario

    /* Subconsulta: total de likes agrupados por idPost */
    LEFT JOIN (
      SELECT idPost, COUNT(*) AS totalLikes
      FROM likepost
      GROUP BY idPost
    ) l ON l.idPost = p.idPost

    /* Subconsulta: verifica si el usuario actual ya dio like */
    LEFT JOIN (
      SELECT idPost, idUsuario
      FROM likepost
      WHERE idUsuario = ?
    ) ul ON ul.idPost = p.idPost

    /* Sin WHERE: trae todos los posts ordenados por fecha descendente */
    ORDER BY p.fecha DESC
");
$stmt->execute([ $idUsuarioActual ]);

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

    // Armar el JSON que espera Mensaje.java
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['usuarioId'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        "likeCount"        => (int)$row['likeCount'],
        "commentCount"     => (int)$row['commentCount'],    // <-- Nueva línea
        "likedByUser"      => ((int)$row['likedByUser'] === 1)
    ];
}

echo json_encode($mensajes);
exit;
