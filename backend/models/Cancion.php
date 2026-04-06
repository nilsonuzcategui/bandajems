<?php
class Cancion
{
    // Conexión de base de datos y nombre de la tabla
    private $conn;
    private $table_name = "canciones";

    // Atributos del objeto
    public $id;
    public $titulo;
    public $url_youtube;
    public $url_spotify;
    public $url_otro;

    // Constructor con $db como conexión de base de datos
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Leer todas las canciones
    public function read()
    {
        $query = "SELECT id, titulo, url_youtube, url_spotify, url_otro 
                  FROM " . $this->table_name . " 
                  ORDER BY titulo ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Crear una canción
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET titulo=:titulo, url_youtube=:url_youtube, url_spotify=:url_spotify, url_otro=:url_otro";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos (Sanitize)
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->url_youtube = htmlspecialchars(strip_tags($this->url_youtube));
        $this->url_spotify = htmlspecialchars(strip_tags($this->url_spotify));
        $this->url_otro = htmlspecialchars(strip_tags($this->url_otro));

        // Bind de parámetros
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":url_youtube", $this->url_youtube);
        $stmt->bindParam(":url_spotify", $this->url_spotify);
        $stmt->bindParam(":url_otro", $this->url_otro);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Actualizar una canción
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET titulo = :titulo, 
                      url_youtube = :url_youtube, 
                      url_spotify = :url_spotify, 
                      url_otro = :url_otro 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->url_youtube = htmlspecialchars(strip_tags($this->url_youtube));
        $this->url_spotify = htmlspecialchars(strip_tags($this->url_spotify));
        $this->url_otro = htmlspecialchars(strip_tags($this->url_otro));

        // Bind
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':url_youtube', $this->url_youtube);
        $stmt->bindParam(':url_spotify', $this->url_spotify);
        $stmt->bindParam(':url_otro', $this->url_otro);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar una canción
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Obtener una sola canción (útil para edición específica)
    public function readOne()
    {
        $query = "SELECT id, titulo, url_youtube, url_spotify, url_otro 
                  FROM " . $this->table_name . " 
                  WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->titulo = $row['titulo'];
            $this->url_youtube = $row['url_youtube'];
            $this->url_spotify = $row['url_spotify'];
            $this->url_otro = $row['url_otro'];
            return true;
        }
        return false;
    }
}
