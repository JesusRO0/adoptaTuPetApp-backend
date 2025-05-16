<?php
require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (isset($data->idUsuario, $data->usuario, $data->localidad)) {
    $id = $data->idUsuario;
    $nombre = $data->usuario;
    $localidad = $data->localidad;

    $stmt = $conn->prepare("UPDATE usuario SET usuario=?, localidad=? WHERE idUsuario=?");
    $stmt->bind_param("ssi", $nombre, $localidad, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Perfil actualizado"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}
?>
