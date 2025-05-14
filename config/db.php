<?php
$host = "db4free.net";
$db_name = "adoptatupetdb";
$username = "jesusr0"; // <-- cámbialo
$password = "Adoptatupet1995_"; // <-- cámbialo

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en la conexión: " . $e->getMessage()]);
    exit;
}
