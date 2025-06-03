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

// -------------------------------------------------------
// Aquí asumimos que tu tabla de posts se llama `post`,
// tu tabla de usuarios `usuario`, y la de likes `post_likes`.
// `post_likes` contendría algo como: idLike (PK), idPost, idUsuario.
// -------------------------------------------------------

$stmt = $pdo->prepare("
    SELECT 
      p.idPost                  AS idPost,
      p.idUsuario               AS usuarioId,
      u.usuario                 AS usuarioNombre,
      u.fotoPerfil              AS fotoPerfil,
      p.contenido               AS contenido,
      p.fecha                   AS fecha,
      p.imagen                  AS imagen,

      -- 1) Contamos cuántos likes totales tiene este post
      COALESCE(l.totalLikes, 0) AS likeCount,

      -- 2) Vemos si el usuario actual ya hizo 'like' (1) o no (0)
      CASE 
        WHEN ul.idUsuario IS NULL THEN 0 
        ELSE 1 
      END                        AS likedByUser

    FROM post p
    JOIN usuario u   ON p.idUsuario = u.idUsuario

    -- Subconsulta para contar todos los likes de cada post
    LEFT JOIN (
      SELECT idPost, COUNT(*) AS totalLikes
      FROM post_likes
      GROUP BY idPost
    ) l ON l.idPost = p.idPost

    -- Subconsulta para saber si el usuario actual (GET) ya marcó like
    LEFT JOIN (
      SELECT idPost, idUsuario
      FROM post_likes
      WHERE idUsuario = ?
    ) ul ON ul.idPost = p.idPost

    WHERE p.idUsuario = ?
    ORDER BY p.fecha DESC
");
$stmt->execute([$idUsuario, $idUsuario]);  // Pasamos dos veces: 1) para ul.idUsuario, 2) para filtrar p.idUsuario

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

    // Mapeo final 
    $mensajes[] = [
        "idMensaje"        => (int)$row['idPost'],
        "usuarioId"        => (int)$row['usuarioId'],
        "usuarioNombre"    => $row['usuarioNombre'],
        "fotoPerfil"       => $row['fotoPerfil'],
        "texto"            => $row['contenido'],
        "fechaPublicacion" => $row['fecha'],
        "imagenMensaje"    => $row['imagenMensaje'],
        "likeCount"        => (int)$row['likeCount'],
        "likedByUser"      => ((int)$row['likedByUser'] === 1)
    ];
}

echo json_encode($mensajes);
exit;
?>
