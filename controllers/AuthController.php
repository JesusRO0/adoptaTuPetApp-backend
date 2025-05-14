<?php
// Mostrar errores durante desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function login($email, $contrasena) {
        $usuario = new Usuario($this->db);
        $usuario->email = $email;
        $usuario->contrasena = $contrasena;

        if ($usuario->login()) {
            return [
                "success" => true,
                "message" => "Login correcto",
                "usuario" => [
                    "idUsuario" => $usuario->idUsuario,
                    "usuario" => $usuario->usuario,
                    "localidad" => $usuario->localidad
                ]
            ];
        } else {
            return [
                "success" => false,
                "message" => "Credenciales incorrectas"
            ];
        }
    }

    public function register($email, $usuario, $localidad, $contrasena, $fotoPerfil = null) {
        $usuarioModel = new Usuario($this->db);

        if ($usuarioModel->existeEmail($email)) {
            return [
                "success" => false,
                "message" => "El correo electrónico ya está en uso"
            ];
        }

        if ($usuarioModel->existeNombreUsuario($usuario)) {
            return [
                "success" => false,
                "message" => "El nombre de usuario ya está en uso"
            ];
        }

        $usuarioModel->email = $email;
        $usuarioModel->usuario = $usuario;
        $usuarioModel->localidad = $localidad;
        $usuarioModel->contrasena = $contrasena;

        if ($fotoPerfil !== null) {
            $usuarioModel->fotoPerfil = $fotoPerfil;
        }

        if ($usuarioModel->registrar()) {
            return [
                "success" => true,
                "message" => "Usuario registrado correctamente"
            ];
        } else {
            return [
                "success" => false,
                "message" => "No se pudo registrar el usuario"
            ];
        }
    }
}
