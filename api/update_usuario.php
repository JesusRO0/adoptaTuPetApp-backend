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
    $fotoPerfilBase64 = $data->fotoPerfil; // Base64

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
        if (!password_verify($contrasenaNueva, $contrasenaActual)) {
            // Hashear la nueva contraseña
            $contrasenaParaGuardar = password_hash($contrasenaNueva, PASSWORD_BCRYPT);
        }
    }

    // 3. Decodificar la foto de Base64 a binario
    $fotoBinaria = null;
    if (!empty($fotoPerfilBase64)) {
        // quitar posible prefijo data URI
        if (preg_match('/^data:image\/\w+;base64,/', $fotoPerfilBase64)) {
            $fotoPerfilBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $fotoPerfilBase64);
        }
        // eliminar espacios o saltos de línea
        $fotoPerfilBase64 = preg_replace('/\s+/', '', $fotoPerfilBase64);
        $fotoBinaria = base64_decode($fotoPerfilBase64);
    }

    // 4. Preparar la actualización con la contraseña y la foto correctas
    $sql = "UPDATE usuario 
            SET usuario     = :usuario,
                localidad   = :localidad,
                email       = :email,
                contrasena  = :contrasena,
                fotoPerfil  = :fotoPerfil
            WHERE idUsuario = :idUsuario";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario',    $nombre);
    $stmt->bindParam(':localidad',  $localidad);
    $stmt->bindParam(':email',      $email);
    $stmt->bindParam(':contrasena', $contrasenaParaGuardar);

    // BLOB: si no hay imagen nueva, podemos usar NULL
    if ($fotoBinaria !== null) {
        $stmt->bindParam(':fotoPerfil', $fotoBinaria, PDO::PARAM_LOB);
    } else {
        $stmt->bindValue(':fotoPerfil', null, PDO::PARAM_NULL);
    }

    $stmt->bindParam(':idUsuario', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Perfil actualizado con éxito"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar en BD"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
}
