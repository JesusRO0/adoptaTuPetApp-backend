<?php

class Mensaje {
    private $db;

    // Campos directos de la tabla post
    public $idPost;
    public $idUsuario;
    public $contenido;         // texto
    public $fecha;             // DATETIME
    public $imagen;            // BLOB (opcional)

    // Campos “derivados” que queremos exponer en el JSON
    public $usuarioNombre;     // nombre de usuario
    public $fotoPerfil;        // Base64 de foto de perfil
    public $likeCount;         // contador total
    public $likedByUser;       // booleano (0 ó 1)

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Obtener todos los posts (mensajes) con JOIN a usuario y conteo de likes.
     * @param int|null $idUsuarioActual  para marcar likedByUser (si es provisto)
     * @return PDOStatement
     */
    public function getAll($idUsuarioActual = null) {
        // Construimos la consulta principal para traer:
        //   p.idPost, p.idUsuario, u.usuario AS usuarioNombre, u.fotoPerfil, p.contenido, p.fecha, p.imagen,
        //   COUNT(l.idUsuario) AS likeCount,
        //   (CASE WHEN (SELECT 1 FROM likepost lp2 WHERE lp2.idPost = p.idPost AND lp2.idUsuario = :idu) IS NOT NULL THEN 1 ELSE 0 END) as likedByUser
        $sql = "
            SELECT 
                p.idPost,
                p.idUsuario,
                u.usuario AS usuarioNombre,
                u.fotoPerfil,
                p.contenido,
                p.fecha,
                p.imagen,
                COALESCE(l.likeCount, 0) AS likeCount,
                COALESCE(ul.liked, 0)   AS likedByUser
            FROM post p
            JOIN usuario u ON p.idUsuario = u.idUsuario
            LEFT JOIN (
                SELECT idPost, COUNT(*) AS likeCount
                FROM likepost
                GROUP BY idPost
            ) l ON p.idPost = l.idPost
            LEFT JOIN (
                SELECT idPost, 1 AS liked
                FROM likepost
                WHERE idUsuario = :idu
            ) ul ON p.idPost = ul.idPost
            ORDER BY p.fecha ASC
        ";
        $stmt = $this->db->prepare($sql);
        // Si no hay usuario logueado, forzamos :idu = 0 para que ul.liked sea siempre NULL
        $idu = ($idUsuarioActual !== null) ? intval($idUsuarioActual) : 0;
        $stmt->bindParam(':idu', $idu, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Crear un nuevo post (mensaje).
     * @param int $idUsuario
     * @param string $contenido
     * @param string|null $imagenData (binario decodificado de Base64) 
     * @return array|false   // retorna el registro recién creado, o false si falla
     */
    public function create($idUsuario, $contenido, $imagenData = null) {
        $sql = "INSERT INTO post (idUsuario, contenido, imagen) VALUES (:idu, :cont, :img)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idu', $idUsuario, PDO::PARAM_INT);
        $stmt->bindParam(':cont', $contenido);
        if ($imagenData !== null) {
            $stmt->bindParam(':img', $imagenData, PDO::PARAM_LOB);
        } else {
            $stmt->bindValue(':img', null, PDO::PARAM_NULL);
        }

        if (!$stmt->execute()) {
            return false;
        }

        $nuevoId = $this->db->lastInsertId();
        // Consultamos el post recién creado para devolver datos completos
        $sql2 = "
            SELECT 
                p.idPost,
                p.idUsuario,
                u.usuario AS usuarioNombre,
                u.fotoPerfil,
                p.contenido,
                p.fecha,
                p.imagen,
                0 AS likeCount,
                0 AS likedByUser
            FROM post p
            JOIN usuario u ON p.idUsuario = u.idUsuario
            WHERE p.idPost = :idPost
        ";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->bindParam(':idPost', $nuevoId, PDO::PARAM_INT);
        $stmt2->execute();
        return $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * (Opcional) Actualizar contador de likes manualmente
     * @param int $idPost
     * @param int $likeCount
     * @return bool
     */
    public function updateLikes($idPost, $likeCount) {
        $sql = "UPDATE post SET like_count = :lc WHERE idPost = :idPost"; 
        // Si en tu tabla 'post' no existe columna like_count, omite este método y en su lugar
        // manipula directamente la tabla likepost.
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lc', $likeCount, PDO::PARAM_INT);
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
