<?php
// api/get_latest_animals.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

try {
    // Seleccionamos los 5 animales más recientes (por idAnimal descendente)
    $stmt = $pdo->prepare("
        SELECT idAnimal, nombre, especie, raza, edad, localidad, sexo, tamano, descripcion, imagen, idUsuario
          FROM animal
         ORDER BY idAnimal DESC
         LIMIT 5
    ");
    $stmt->execute();
    $animales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($animales);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
    exit;
}
