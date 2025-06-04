<?php
// api/post_comentario.php

// Permitir CORS y métodos POST
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Responder a preflight (OPTIONS) inmediatamente
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Incluir la conexión a la base de datos
require_once __DIR__ . '/../config/db.php';

// Leer el JSON enviado en el cuerpo de la petición
$input = json_decode(file_get_contents("php://input"), true);

// Validar que vengan los campos obligatorios
if (
    empty($input['idUsuario']) ||
    empty($input['idPost']) ||
    !isset($input['texto'])   // puede ser cadena vacía, pero debe existir
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan uno o más campos requeridos: idUsuario, idPost, texto"
    ]);
    exit;
}

$idUsuario = intval($input['idUsuario']);
$idPost    = intval($input['idPost']);
$texto     = trim($input['texto']);

// Insertar el comentario en la tabla 'comentario'
try {
    $stmt = $pdo->prepare("
        INSERT INTO comentario (texto, idUsuario, idPost)
        VALUES (:texto, :idUsuario, :idPost)
    ");
    $stmt->bindParam(':texto', $texto, PDO::PARAM_STR);
    $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
    $res = $stmt->execute();

    if ($res) {
        // Obtener el ID recién insertado
        $nuevoId = (int)$pdo->lastInsertId();

        // ACTUALIZAR commentCount en la tabla 'post'
        $updateStmt = $pdo->prepare("
            UPDATE post
            SET commentCount = commentCount + 1
            WHERE idPost = :idPost
        ");
        $updateStmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $updateStmt->execute();

        // Devolver JSON de éxito
        echo json_encode([
            "success"      => true,
            "message"      => "Comentario creado correctamente",
            "idComentario" => $nuevoId
        ]);
    } else {
        // Si la inserción falló por algún motivo
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al insertar el comentario"
        ]);
    }
} catch (PDOException $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
}

exit;
