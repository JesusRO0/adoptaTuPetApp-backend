<?php
// config/db.php

// (Opcionalmente) cabeceras comunes si vas a emitir JSON directamente desde aquí
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Datos de conexión (ya validados por ti)
$host     = "db4free.net";
$db_name  = "adoptatupetdb";
$username = "jesusr0";
$password = "Adoptatupet1995_07";

// Opciones recomendadas de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // Forzar UTF8MB4 desde el inicio
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    // DSN con charset utf8mb4
    $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // En caso de error de conexión, devolvemos JSON con código 500
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error en la conexión: " . $e->getMessage()
    ]);
    exit;
}
