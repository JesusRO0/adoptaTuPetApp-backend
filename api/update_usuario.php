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
    $contrasenaNueva = $data->contrasena;
    $fotoPerfil = $data->fotoPerfil; // Base64

    // 1. Obtener la contraseña actual de la base de datos
    $stmt = $pdo->prepare("SELECT contrasena FROM usuario WHERE idUsuario = :idUsuario");
    $stmt->bindParam(':idUsuario', $id, PDO::PARAM_INT);
    $stmt->execute();
    $usuarioBD = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioBD) {
        echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
        exit;
    }

    $contrasenaActual = $usuarioBD['contrasena'];

    // 2. Comprobar si la contraseña nueva es diferente y no vacía
    $contrasenaParaGuardar = $contrasenaActual; // por defecto la actual

    if (!empty($contrasenaNueva)) {
        // Comparamos la nueva con la actual usando password_verify
        // Si no coincide, es que el usuario cambió la contraseña
        if (!password_verify($contrasenaNueva, $contrasenaActual)) {
            // Hashear la nueva contraseña
            $contrasenaParaGuardar = password_hash($contrasenaNueva, PASSWORD_BCRYPT);
        }
        // Si coincide, no cambiaremos el hash para evitar doble hash
    }

    // 3. Preparar la actualización con la contraseña correcta (hash actual o nuevo)
    $sql = "UPDATE usuario 
            SET usuario = :usuario, localidad = :localidad, email = :email, contrasena = :contrasena, fotoPerfil = :fotoPerfil
            WHERE idUsuario = :idUsuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $nombre);
    $stmt->bindParam(':localidad', $localidad);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':contrasena', $contrasenaParaGuardar);
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
