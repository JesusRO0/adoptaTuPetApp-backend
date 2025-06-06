<?php
// api/get_latest_animals.php

// Permitir peticiones CORS y definir tipo de contenido JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../config/db.php';

try {
    // 1) Seleccionamos los 5 animales más recientes (por idAnimal descendente)
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

    // 2) Obtenemos todos los registros como array asociativo
    $animales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3) Convertimos cada campo ‘imagen’ (BLOB) a Base64
    foreach ($animales as &$fila) {
        if (isset($fila['imagen']) && !empty($fila['imagen'])) {
            $fila['imagen'] = base64_encode($fila['imagen']);
        } else {
            $fila['imagen'] = null;
        }
    }

    // 4) Devolvemos el JSON resultante
    echo json_encode($animales);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error de base de datos: " . $e->getMessage()
    ]);
    exit;
}
