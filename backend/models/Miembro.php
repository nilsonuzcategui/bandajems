<?php
class Miembro
{
    private $conn;
    private $table_name = "miembros";

    public $id;
    public $nombre;
    public $foto_url;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Leer todos los miembros
    public function read()
    {
        $query = "SELECT id, nombre, foto_url FROM " . $this->table_name . " ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear miembro
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre, foto_url=:foto_url";
        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->foto_url = htmlspecialchars(strip_tags($this->foto_url));

        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":foto_url", $this->foto_url);

        if ($stmt->execute()) return true;
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
              SET nombre = :nombre, foto_url = :foto_url 
              WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':foto_url', $this->foto_url);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // También útil para cargar los datos en el modal
    public function readOne()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
