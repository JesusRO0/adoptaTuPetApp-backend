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

/**
 * Trae solo los posts de este usuario, junto con:
 *   - likeCount     = total de likes de cada post
 *   - commentCount  = total de comentarios de cada post
 *   - likedByUser   = si el propio usuario ya dio “like” a su propio post (suele ser 0)
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

      /* 1) Total de likes de este post */
      COALESCE(l.totalLikes, 0)   AS likeCount,

      /* 2) Total de comentarios de este post */
      COALESCE(p.commentCount, 0) AS commentCount,

      /* 3) Saber si este mismo usuario marcó like (raro en su propio historial) */
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

    /* Subconsulta: si este usuario (idUsuario) ya dio like a ese post */
    LEFT JOIN (
      SELECT idPost, idUsuario
      FROM likepost
      WHERE idUsuario = ?
    ) ul ON ul.idPost = p.idPost

    WHERE p.idUsuario = ?
    ORDER BY p.fecha DESC
");
$stmt->execute([ $idUsuario, $idUsuario ]);

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

    // Construir el JSON que espera Mensaje.java
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['usuarioId'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        "likeCount"        => (int)$row['likeCount'],
        "commentCount"     => (int)$row['commentCount'],
        "likedByUser"      => ((int)$row['likedByUser'] === 1)
    ];
}

echo json_encode($mensajes);
exit;
