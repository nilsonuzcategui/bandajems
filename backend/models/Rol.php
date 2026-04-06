<?php
class Rol
{
    private $conn;
    private $table_name = "roles";

    public $id;
    public $nombre_rol;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nombre_rol ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (nombre_rol) VALUES (:nombre_rol)";
        $stmt = $this->conn->prepare($query);

        // Limpieza de datos
        $this->nombre_rol = htmlspecialchars(strip_tags($this->nombre_rol));

        // Bind
        $stmt->bindParam(":nombre_rol", $this->nombre_rol);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
