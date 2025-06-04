<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['idComentario']) || empty($data['idPost'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan idComentario o idPost"
    ]);
    exit;
}

$idComentario = intval($data['idComentario']);
$idPost       = intval($data['idPost']);

// 1) Borrar el comentario de la tabla `comentario`
$sqlDelete = "DELETE FROM comentario WHERE idComentario = :idc";
$stmtDelete = $pdo->prepare($sqlDelete);
$stmtDelete->bindParam(':idc', $idComentario, PDO::PARAM_INT);

if ($stmtDelete->execute()) {
    // 2) ACTUALIZAR commentCount en la tabla post (no bajar de 0)
    $sqlUpdate = "UPDATE post
                  SET commentCount = GREATEST(commentCount - 1, 0)
                  WHERE idPost = :idp";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':idp', $idPost, PDO::PARAM_INT);
    $stmtUpdate->execute();

    // 3) Responder con éxito
    echo json_encode([
        "success" => true,
        "message" => "Comentario eliminado"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al eliminar comentario"
    ]);
}
exit;
