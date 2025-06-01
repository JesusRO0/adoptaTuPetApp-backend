<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';
// Leer parámetro idUsuario por GET
if (!isset($_GET['idUsuario'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Falta idUsuario"]);
    exit;
}
$idUsuario = intval($_GET['idUsuario']);
// Consulta a la tabla usuarios (asumiendo que tu tabla se llama `usuarios`)
$stmt = $pdo->prepare("SELECT idUsuario, usuario, email, localidad, fotoPerfil 
                       FROM usuarios WHERE idUsuario = ?");
$stmt->execute([$idUsuario]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    // Devolver el JSON con los campos que tu modelo Usuario espera
    echo json_encode([
        "idUsuario"   => (int)$row['idUsuario'],
        "usuario"     => $row['usuario'],
        "email"       => $row['email'],
        "localidad"   => $row['localidad'],
        "fotoPerfil"  => $row['fotoPerfil']  // asumimos que ya está guardada como Base64
    ]);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
}
?>
