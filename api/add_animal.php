<?php
// api/add_animal.php

// --- Cabeceras CORS y JSON ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Para preflight de CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// --- Cargar configuración de BD ---
require_once __DIR__ . '/../config/db.php';            // Debe definir $pdo (instancia PDO)

// --- Importar controlador de Animal ---
require_once __DIR__ . '/../controllers/AnimalController.php';

// --- Instanciar y ejecutar la acción 'add' ---
$controller = new AnimalController($pdo);
$controller->add();
