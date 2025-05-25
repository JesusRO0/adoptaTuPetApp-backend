<?php
// api/get_animals.php

// 1) Permitir CORS y JSON de salida
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// 2) Conectar a la base de datos
require_once __DIR__ . '/../config/db.php';

// 3) Incluir el modelo
require_once __DIR__ . '/../models/Animal.php';

// 4) Instanciar el modelo
$animal = new Animal($pdo);

// 5) Ejecutar la consulta
$stmt = $animal->obtenerTodos();

// 6) Procesar resultados
$animales = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Cada fila viene con claves iguales a los nombres de columna:
    $animales[] = [
        'idAnimal'   => (int) $row['idAnimal'],
        'nombre'     => $row['nombre'],
        'especie'    => $row['especie'],
        'raza'       => $row['raza'],
        'edad'       => $row['edad'],
        'localidad'  => $row['localidad'],
        'sexo'       => $row['sexo'],
        'tamano'     => $row['tamano'],
        'descripcion'=> $row['descripcion'],
        // Si quieres enviar la imagen en base64:
        'imagen'     => base64_encode($row['imagen']),
        'idUsuario'  => (int) $row['idUsuario']
    ];
}

// 7) Devolver JSON
echo json_encode($animales);
