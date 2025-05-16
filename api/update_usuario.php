<?php
require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (
    isset($data->idUsuario, $data->usuario, $data->localidad, $data->email, $data->contrasena, $data->fotoPerfil)
) {
    $id = $data->idUsuario;
    $nombre = $data->usuario;
    $localidad = $data->localidad;
    $email = $data->email;
    $contrasena = $data->contrasena;
    $fotoPerfil = $data->fotoPerfil; // Base64

    $stmt = $conn->prepare("UPDATE usuario 
                            SET usuario = ?, localidad = ?, email = ?, contrasena = ?, fotoPerfil = ?
                            WHERE idUsuario = ?");
    $stmt->bind_param("sssssi", $nombre, $localidad, $email, $contrasena, $fotoPerfil, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Perfil actualizado con éxito"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar en BD"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}
?>
