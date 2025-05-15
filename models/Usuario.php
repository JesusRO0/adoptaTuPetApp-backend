<?php
class Usuario {
    private $conn;
    private $table_name = "usuario"; // nombre real de tu tabla

    public $idUsuario;
    public $fotoPerfil;
    public $email;
    public $usuario;
    public $localidad;
    public $contrasena;

    public function __construct($db) {
        $this->conn = $db;
    }

    // REGISTRO DE USUARIO
    public function registrar() {
        $query = "INSERT INTO " . $this->table_name . " 
            (fotoPerfil, email, usuario, localidad, contrasena) 
            VALUES (:fotoPerfil, :email, :usuario, :localidad, :contrasena)";
        
        $stmt = $this->conn->prepare($query);

        // Sanitizar entrada
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        $this->localidad = htmlspecialchars(strip_tags($this->localidad));
        $this->contrasena = password_hash($this->contrasena, PASSWORD_BCRYPT);

        // Si no hay foto, establecer una por defecto
        if (empty($this->fotoPerfil)) {
            $defaultPath = __DIR__ . "/../default-avatar.png";
            if (file_exists($defaultPath)) {
                $this->fotoPerfil = file_get_contents($defaultPath);
            } else {
                $this->fotoPerfil = null;
            }
        }

        // Bind parameters
        $stmt->bindParam(':fotoPerfil', $this->fotoPerfil, PDO::PARAM_LOB);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':localidad', $this->localidad);
        $stmt->bindParam(':contrasena', $this->contrasena);

        return $stmt->execute();
    }

    // LOGIN DE USUARIO
    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($this->contrasena, $row['contrasena'])) {
                // Rellenar datos del usuario logueado
                $this->idUsuario = $row['idUsuario'];
                $this->usuario = $row['usuario'];
                $this->localidad = $row['localidad'];
                $this->email = $row['email'];
                return true;
            }
        }
        return false;
    }

    // COMPROBAR SI EXISTE EMAIL
    public function existeEmail($email) {
        $query = "SELECT idUsuario FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // COMPROBAR SI EXISTE NOMBRE DE USUARIO
    public function existeNombreUsuario($usuario) {
        $query = "SELECT idUsuario FROM " . $this->table_name . " WHERE usuario = :usuario LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>
