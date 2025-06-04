<?php
// api/get_comentarios.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

// Verificamos que nos pasen idPost por GET
if (empty($_GET['idPost'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta idPost"
    ]);
    exit;
}

$idPost = intval($_GET['idPost']);

// Asumimos que tu tabla de comentarios se llama `comentario`
// con columnas: idComentario, texto, idUsuario, idPost, fecha
// Y que tu tabla de usuarios se llama `usuario` (para obtener nombre y foto).

$stmt = $pdo->prepare("
    SELECT 
        c.idComentario      AS idComentario,
        c.texto             AS texto,
        c.idUsuario         AS usuarioId,
        u.usuario           AS usuarioNombre,
        u.fotoPerfil        AS fotoPerfil,
        c.fecha             AS fecha
    FROM comentario c
    JOIN usuario u ON c.idUsuario = u.idUsuario
    WHERE c.idPost = ?
    ORDER BY c.fecha ASC
");
$stmt->execute([$idPost]);

$comentarios = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil BLOB a Base64 si no es nulo
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }

    $comentarios[] = [
        "idComentario"   => (int)$row['idComentario'],
        "texto"          => $row['texto'],
        "usuarioId"      => (int)$row['usuarioId'],
        "usuarioNombre"  => $row['usuarioNombre'],
        "fotoPerfil"     => $row['fotoPerfil'],
        "fecha"          => $row['fecha']
    ];
}

echo json_encode($comentarios);
exit;
?>
