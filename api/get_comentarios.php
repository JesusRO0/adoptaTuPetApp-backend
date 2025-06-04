<?php
// api/get_comentarios.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

// 1) Comprobamos que venga idPost por GET
if (empty($_GET['idPost'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta idPost"
    ]);
    exit;
}

$idPost = intval($_GET['idPost']);

// 2) Preparamos la consulta. Aquí usamos los nombres de columnas tal como están en tu BD:
//
//    comentario  (idComentario, texto, idUsuario, idPost, fecha)
//    usuario     (idUsuario, fotoPerfil, email, usuario, localidad, contrasena)
//
$stmt = $pdo->prepare("
    SELECT 
        c.idComentario   AS idComentario,
        c.texto          AS texto,
        c.idUsuario      AS usuarioId,
        u.usuario        AS usuarioNombre,
        u.fotoPerfil     AS fotoPerfil,
        c.fecha          AS fecha
    FROM comentario c
    JOIN usuario u ON c.idUsuario = u.idUsuario
    WHERE c.idPost = ?
    ORDER BY c.fecha ASC
");
$stmt->execute([$idPost]);

$comentarios = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Convertir fotoPerfil (BLOB) a Base64, si existe
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

// 3) Devolvemos el JSON
echo json_encode($comentarios);
exit;
?>
