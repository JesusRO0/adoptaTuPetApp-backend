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
    // 1) Obtener email del usuario que hace la petición
    $stmtUser = $pdo->prepare("SELECT email FROM usuario WHERE idUsuario = :idu");
    $stmtUser->bindParam(':idu', $usuarioId, PDO::PARAM_INT);
    $stmtUser->execute();
    $userRow = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!$userRow) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Usuario no encontrado"
        ]);
        exit;
    }
    $emailLogueado = $userRow['email'];

    // 2) Obtener el idUsuario del autor del post
    $stmtAuthor = $pdo->prepare("SELECT idUsuario FROM post WHERE idPost = :idp");
    $stmtAuthor->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtAuthor->execute();
    $postRow = $stmtAuthor->fetch(PDO::FETCH_ASSOC);
    if (!$postRow) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Post no encontrado"
        ]);
        exit;
    }
    $autorDelPost = intval($postRow['idUsuario']);

    // 3) Verificar si es autor o admin
    $esAdmin = strcasecmp($emailLogueado, "admin@gmail.com") === 0;
    $esAutor = ($autorDelPost === $usuarioId);
    if (!($esAutor || $esAdmin)) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "No autorizado para eliminar este post"
        ]);
        exit;
    }

    // 4) Iniciar transacción para asegurar integridad
    $pdo->beginTransaction();

    // 5) Borrar todos los likes de este post (tabla `likepost`)
    $stmtLikes = $pdo->prepare("DELETE FROM likepost WHERE idPost = :idp");
    $stmtLikes->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtLikes->execute();

    // 6) Borrar todos los comentarios de este post (tabla `comentario`)
    $stmtComments = $pdo->prepare("DELETE FROM comentario WHERE idPost = :idp");
    $stmtComments->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtComments->execute();

    // 7) Finalmente, borrar el propio post
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
