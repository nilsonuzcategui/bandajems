<?php
class Evento
{
    private $conn;
    private $table_name = "eventos";

    public $id;
    public $fecha;
    public $nombre_evento;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Obtener los 5 eventos clave (2 antes, hoy/proximo, 2 despues)
    public function readCronograma()
    {
        // Esta query es más compleja, requiere UNION para traer los registros exactos
        $query = "(SELECT * FROM " . $this->table_name . " WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 2)
                  UNION
                  (SELECT * FROM " . $this->table_name . " WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 3)
                  ORDER BY fecha ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear evento con validación de fecha
    public function create()
    {
        // Primero verificamos si ya existe la fecha
        $check = "SELECT id FROM " . $this->table_name . " WHERE fecha = :fecha";
        $stmtCheck = $this->conn->prepare($check);
        $stmtCheck->bindParam(":fecha", $this->fecha);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            return "FECHA_DUPLICADA";
        }

        $query = "INSERT INTO " . $this->table_name . " SET fecha=:fecha, nombre_evento=:nombre_evento";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":nombre_evento", $this->nombre_evento);

        if ($stmt->execute()) return true;
        return false;
    }
}
