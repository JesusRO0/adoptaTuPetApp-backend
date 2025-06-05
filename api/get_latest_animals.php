<?php
// api/get_latest_animals.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/db.php';

try {
    // 1) Seleccionar los 5 animales más recientes
    //    Se asume que la tabla `animal` tiene al menos los siguientes campos:
    //    idAnimal, nombre, especie, raza, edad, localidad, sexo, tamano, descripcion, imagen, idUsuario
    $stmt = $pdo->prepare("
        SELECT 
            idAnimal,
            nombre,
            especie,
            raza,
            edad,
            localidad,
            sexo,
            tamano,
            descripcion,
            imagen,
            idUsuario
        FROM animal
        ORDER BY idAnimal DESC
        LIMIT 5
    ");
    $stmt->execute();
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Construir respuesta
    echo json_encode([
        "success" => true,
        "message" => "Últimos animales obtenidos correctamente",
        "animals" => $animals
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
    exit;
}

exit;
?>
