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
if (empty($data['usuarioId']) || empty($data['idPost'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Faltan usuarioId o idPost"
    ]);
    exit;
}

$usuarioId = intval($data['usuarioId']);
$idPost    = intval($data['idPost']);

// 1) Eliminar el registro de likepost
$sqlDelete = "DELETE FROM likepost WHERE idUsuario = :idu AND idPost = :idp";
$stmtDelete = $pdo->prepare($sqlDelete);
$stmtDelete->bindParam(':idu', $usuarioId, PDO::PARAM_INT);
$stmtDelete->bindParam(':idp', $idPost,    PDO::PARAM_INT);

if ($stmtDelete->execute()) {
    // 2) Si el DELETE fue exitoso, actualizar post.likeCount (no bajar de 0)
    $sqlUpdate = "
        UPDATE post
           SET likeCount = CASE
                              WHEN likeCount > 0 THEN likeCount - 1
                              ELSE 0
                           END
         WHERE idPost = :idp2
    ";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':idp2', $idPost, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode([
        "success" => true,
        "message" => "Like removido"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al remover like"
    ]);
}
exit;
