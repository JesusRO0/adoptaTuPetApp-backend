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

    // Cambiar a PDO
    $sql = "UPDATE usuario 
            SET usuario = :usuario, localidad = :localidad, email = :email, contrasena = :contrasena, fotoPerfil = :fotoPerfil
            WHERE idUsuario = :idUsuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $nombre);
    $stmt->bindParam(':localidad', $localidad);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contrasena', $contrasena);
    $stmt->bindParam(':fotoPerfil', $fotoPerfil);
    $stmt->bindParam(':idUsuario', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Perfil actualizado con éxito"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar en BD"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}
