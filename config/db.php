<?php
class Database {
    private $host = "sql206.infinityfree.com"; // Servidor
    private $db_name = "if0_38964328_adoptatupet"; // Tu nombre de base de datos
    private $username = "if0_38964328"; // Tu usuario
    private $password = "Neofox1995"; // Contraseña
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Error en la conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
