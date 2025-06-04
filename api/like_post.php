<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Responder a solicitudes CORS preflight
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

// 1) Comprobar si ya existe un registro en likepost para este usuario y post
$sqlCheck = "SELECT 1 FROM likepost WHERE idUsuario = :idu AND idPost = :idp";
$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->bindParam(':idu', $usuarioId, PDO::PARAM_INT);
$stmtCheck->bindParam(':idp', $idPost,    PDO::PARAM_INT);
$stmtCheck->execute();

if ($stmtCheck->fetch()) {
    // Ya tenía like; devolvemos éxito sin cambiar nada en post.likeCount
    echo json_encode(["success" => true, "message" => "Ya tenías like"]);
    exit;
}

// 2) Insertar la fila en likepost
$sqlInsert = "INSERT INTO likepost (idUsuario, idPost) VALUES (:idu, :idp)";
$stmtInsert = $pdo->prepare($sqlInsert);
$stmtInsert->bindParam(':idu', $usuarioId, PDO::PARAM_INT);
$stmtInsert->bindParam(':idp', $idPost,    PDO::PARAM_INT);

if ($stmtInsert->execute()) {
    // 3) Si el INSERT tuvo éxito, actualizar post.likeCount -> sumar 1
    $sqlUpdate = "UPDATE post SET likeCount = likeCount + 1 WHERE idPost = :idp2";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':idp2', $idPost, PDO::PARAM_INT);
    $stmtUpdate->execute();
    
    echo json_encode([
        "success" => true,
        "message" => "Like agregado"
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error al agregar like"
    ]);
}

exit;
