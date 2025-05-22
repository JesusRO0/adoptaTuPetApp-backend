<?php
// api/add_animal.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Autocarga tu configuración de BD
require_once __DIR__ . '/../config/db.php';
// Importa el controlador
require_once __DIR__ . '/../controllers/AnimalController.php';

// Instancia y ejecuta
$controller = new AnimalController($pdo);
$controller->add();
