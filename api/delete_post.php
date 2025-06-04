<?php
// api/delete_post.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$input = json_decode(file_get_contents("php://input"), true);
if (empty($input['usuarioId']) || empty($input['idPost'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan usuarioId o idPost"
    ]);
    exit;
}

$usuarioId = intval($input['usuarioId']);
$idPost    = intval($input['idPost']);

try {
    // 1) Verificar que el post realmente pertenezca a este usuario
    $stmtCheck = $pdo->prepare("
        SELECT 1 
          FROM post 
         WHERE idPost = :idp 
           AND idUsuario = :idu
    ");
    $stmtCheck->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtCheck->bindParam(':idu', $usuarioId, PDO::PARAM_INT);
    $stmtCheck->execute();
    if (!$stmtCheck->fetch()) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "No autorizado para eliminar este post"
        ]);
        exit;
    }

    // 2) Iniciar transacción para asegurar integridad
    $pdo->beginTransaction();

    // 3) Borrar todos los likes de este post (tabla `likepost`)
    $stmtLikes = $pdo->prepare("DELETE FROM likepost WHERE idPost = :idp");
    $stmtLikes->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtLikes->execute();

    // 4) Borrar todos los comentarios de este post (tabla `comentario`)
    $stmtComments = $pdo->prepare("DELETE FROM comentario WHERE idPost = :idp");
    $stmtComments->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtComments->execute();

    // 5) Finalmente, borrar el propio post
    $stmtDelete = $pdo->prepare("DELETE FROM post WHERE idPost = :idp");
    $stmtDelete->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $deleted = $stmtDelete->execute();

    if ($deleted) {
        $pdo->commit();
        echo json_encode([
            "success" => true,
            "message" => "Post eliminado correctamente"
        ]);
    } else {
        // Si algo falla al borrar el post, revertir
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Error al eliminar el post"
        ]);
    }
} catch (PDOException $e) {
    // En caso de excepción, hacer rollback y retornar error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
}

exit;
