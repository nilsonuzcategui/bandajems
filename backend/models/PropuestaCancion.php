<?php
class PropuestaCancion
{
    private $conn;
    private $table_name = "propuestas_canciones";

    public $id;
    public $nombre_solicitante;
    public $nombre_cancion;
    public $url_referencia;
    public $estado;
    public $fecha_creacion;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT id, nombre_solicitante, nombre_cancion, url_referencia, estado, fecha_creacion 
                  FROM " . $this->table_name . " 
                  ORDER BY fecha_creacion DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET nombre_solicitante=:nombre_solicitante, 
                      nombre_cancion=:nombre_cancion, 
                      url_referencia=:url_referencia, 
                      estado='pendiente'";

        $stmt = $this->conn->prepare($query);

        $this->nombre_solicitante = htmlspecialchars(strip_tags($this->nombre_solicitante));
        $this->nombre_cancion = htmlspecialchars(strip_tags($this->nombre_cancion));
        $this->url_referencia = htmlspecialchars(strip_tags($this->url_referencia));

        $stmt->bindParam(":nombre_solicitante", $this->nombre_solicitante);
        $stmt->bindParam(":nombre_cancion", $this->nombre_cancion);
        $stmt->bindParam(":url_referencia", $this->url_referencia);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function updateEstado()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':estado', $this->estado);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

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
}