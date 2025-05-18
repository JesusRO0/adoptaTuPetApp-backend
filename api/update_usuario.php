<?php
// api/update_usuario.php

// Permitir CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a BD
require_once __DIR__ . '/../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data['idUsuario'], $data['usuario'], $data['localidad'],
          $data['email'], $data['contrasena'], $data['fotoPerfil'])
) {
    $id                = $data['idUsuario'];
    $nombre            = $data['usuario'];
    $localidad         = $data['localidad'];
    $email             = $data['email'];
    $nuevaContrasena   = $data['contrasena'];
    $fotoPerfilBase64  = $data['fotoPerfil'];

    // 1) Recuperar hash actual de BD
    $stmt = $pdo->prepare("SELECT contrasena FROM usuario WHERE idUsuario = :idUsuario");
    $stmt->bindParam(':idUsuario', $id, PDO::PARAM_INT);
    $stmt->execute();

    if (! $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(["success"=>false, "message"=>"Usuario no encontrado"]);
        exit;
    }
    $hashActual = $row['contrasena'];

    // 2) Determinar hash a guardar
    if (!empty($nuevaContrasena) && !password_verify($nuevaContrasena, $hashActual)) {
        // Hasheamos sólo si realmente cambió
        $hashGuardar = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
    } else {
        // Sino, mantenemos el hash actual
        $hashGuardar = $hashActual;
    }

    // 3) Decodificar fotoPerfil Base64 a binario
    $binFoto = null;
    if (!empty($fotoPerfilBase64)) {
        // Quitar prefijo data URI si existe
        if (preg_match('/^data:image\/\w+;base64,/', $fotoPerfilBase64)) {
            $fotoPerfilBase64 = preg_replace(
                '/^data:image\/\w+;base64,/',
                '',
                $fotoPerfilBase64
            );
        }
        // Eliminar espacios y saltos de línea
        $fotoPerfilBase64 = preg_replace('/\s+/', '', $fotoPerfilBase64);
        $binFoto = base64_decode($fotoPerfilBase64);
        if ($binFoto === false) {
            echo json_encode(["success"=>false, "message"=>"Imagen inválida"]);
            exit;
        }
    }

    // 4) Preparar UPDATE
    $sql = "UPDATE usuario SET
                usuario    = :usuario,
                localidad  = :localidad,
                email      = :email,
                contrasena = :contrasena,
                fotoPerfil = :fotoPerfil
            WHERE idUsuario = :idUsuario";

    $upd = $pdo->prepare($sql);
    $upd->bindParam(':usuario',    $nombre);
    $upd->bindParam(':localidad',  $localidad);
    $upd->bindParam(':email',      $email);
    $upd->bindParam(':contrasena', $hashGuardar);
    // BLOB  
    if ($binFoto !== null) {
        $upd->bindParam(':fotoPerfil', $binFoto, PDO::PARAM_LOB);
    } else {
        // Si no envías imagen nueva, puedes pasar NULL o volver a leerla de BD
        $upd->bindValue(':fotoPerfil', null, PDO::PARAM_NULL);
    }
    $upd->bindParam(':idUsuario', $id, PDO::PARAM_INT);

    if ($upd->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Perfil actualizado con éxito"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error al actualizar en BD"
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos obligatorios"
    ]);
}
