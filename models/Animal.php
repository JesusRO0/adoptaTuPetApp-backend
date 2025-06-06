<?php
// models/Animal.php

class Animal {
    // Conexión y nombre de la tabla
    private $conn;
    private $table_name = "animal";

    // Propiedades que corresponden a las columnas de la tabla
    public $idAnimal;
    public $nombre;
    public $especie;
    public $raza;
    public $edad;
    public $localidad;
    public $sexo;
    public $tamano;
    public $descripcion;
    public $imagen;
    public $idUsuario;

    /**
     * Constructor: recibe la conexión PDO
     */
    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear un nuevo registro de animal
     * @return bool éxito o fracaso de la ejecución
     */
    public function crear() {
        $sql = "INSERT INTO " . $this->table_name . "
                (nombre, especie, raza, edad, localidad, sexo, tamano, descripcion, imagen, idUsuario)
                VALUES
                (:nombre, :especie, :raza, :edad, :localidad, :sexo, :tamano, :descripcion, :imagen, :idUsuario)";

        $stmt = $this->conn->prepare($sql);

        // Sanitizar y bind de parámetros
        $this->nombre      = htmlspecialchars(strip_tags($this->nombre));
        $this->especie     = htmlspecialchars(strip_tags($this->especie));
        $this->raza        = htmlspecialchars(strip_tags($this->raza));
        $this->edad        = htmlspecialchars(strip_tags($this->edad));
        $this->localidad   = htmlspecialchars(strip_tags($this->localidad));
        $this->sexo        = htmlspecialchars(strip_tags($this->sexo));
        $this->tamano      = htmlspecialchars(strip_tags($this->tamano));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        // La imagen debe venir ya limpia: cadena Base64 sin prefijo data:

        $stmt->bindParam(':nombre',      $this->nombre);
        $stmt->bindParam(':especie',     $this->especie);
        $stmt->bindParam(':raza',        $this->raza);
        $stmt->bindParam(':edad',        $this->edad);
        $stmt->bindParam(':localidad',   $this->localidad);
        $stmt->bindParam(':sexo',        $this->sexo);
        $stmt->bindParam(':tamano',      $this->tamano);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':imagen',      $this->imagen);
        $stmt->bindParam(':idUsuario',   $this->idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Obtener todos los registros de animales
     * @return PDOStatement
     */
    public function obtenerTodos() {
        $sql  = "SELECT * FROM " . $this->table_name;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener animales por id de usuario
     * @param int $idUsuario
     * @return PDOStatement
     */
    public function obtenerPorUsuario($idUsuario) {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idUsuario = :idUsuario";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Obtener un animal por su ID
     * @param int $idAnimal
     * @return array|false
     */
    public function obtenerPorId($idAnimal) {
        $sql  = "SELECT * FROM " . $this->table_name . " WHERE idAnimal = :idAnimal LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idAnimal', $idAnimal, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar un registro existente
     * @return bool
     */
    public function actualizar() {
        $sql = "UPDATE " . $this->table_name . "
                SET nombre      = :nombre,
                    especie     = :especie,
                    raza        = :raza,
                    edad        = :edad,
                    localidad   = :localidad,
                    sexo        = :sexo,
                    tamano      = :tamano,
                    descripcion = :descripcion,
                    imagen      = :imagen
                WHERE idAnimal   = :idAnimal";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':nombre',      $this->nombre);
        $stmt->bindParam(':especie',     $this->especie);
        $stmt->bindParam(':raza',        $this->raza);
        $stmt->bindParam(':edad',        $this->edad);
        $stmt->bindParam(':localidad',   $this->localidad);
        $stmt->bindParam(':sexo',        $this->sexo);
        $stmt->bindParam(':tamano',      $this->tamano);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':imagen',      $this->imagen);
        $stmt->bindParam(':idAnimal',    $this->idAnimal, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Eliminar un registro por ID
     * @param int $idAnimal
     * @return bool
     */
    public function eliminar($idAnimal) {
        $sql  = "DELETE FROM " . $this->table_name . " WHERE idAnimal = :idAnimal";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idAnimal', $idAnimal, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * NUEVO: Obtener los 5 animales más recientes (ordenados por idAnimal DESC).
     * @return PDOStatement
     */
    public function obtenerUltimos() {
        $sql  = "SELECT * FROM " . $this->table_name . "
                 ORDER BY idAnimal DESC
                 LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}
