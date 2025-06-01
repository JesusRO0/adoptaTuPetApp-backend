<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../config/db.php';

// Leer parámetro idUsuario por GET
if (!isset($_GET['idUsuario'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Falta idUsuario"
    ]);
    exit;
}

$idUsuario = intval($_GET['idUsuario']);

// Ahora usamos la tabla `usuario` (singular)
$stmt = $pdo->prepare("
    SELECT idUsuario, usuario, email, localidad, fotoPerfil
      FROM usuario
     WHERE idUsuario = ?
");
$stmt->execute([$idUsuario]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    // Si en la base de datos `fotoPerfil` está almacenada como BLOB, la convertimos a Base64
    if (!empty($row['fotoPerfil'])) {
        $row['fotoPerfil'] = base64_encode($row['fotoPerfil']);
    } else {
        $row['fotoPerfil'] = "";
    }

    // Devolver JSON con los campos que Usuario.java espera
    echo json_encode([
        "idUsuario"  => (int)$row['idUsuario'],
        "usuario"    => $row['usuario'],
        "email"      => $row['email'],
        "localidad"  => $row['localidad'],
        "fotoPerfil" => $row['fotoPerfil']
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Usuario no encontrado"
    ]);
}
exit;
?>
