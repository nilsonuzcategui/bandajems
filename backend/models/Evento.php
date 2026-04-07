<?php
class Evento
{
    private $conn;
    private $table_name = "eventos";
    private $table_historico = "historial_cambios"; // Nueva tabla para el log

    public $id;
    public $fecha;
    public $nombre_evento;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // 1. Obtener los 5 eventos clave (2 antes, hoy/proximo, 2 despues)
    public function readCronograma()
    {
        $query = "(SELECT * FROM " . $this->table_name . " WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 2)
                  UNION
                  (SELECT * FROM " . $this->table_name . " WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 3)
                  ORDER BY fecha ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. Crear evento con validación de fecha y retorno de ID
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

        // Limpieza
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->nombre_evento = htmlspecialchars(strip_tags($this->nombre_evento));

        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":nombre_evento", $this->nombre_evento);

        if ($stmt->execute()) {
            // Retornamos el último ID para usarlo en la asignación de músicos
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // 3. Actualizar Evento
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET fecha = :fecha, nombre_evento = :nombre_evento 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":nombre_evento", $this->nombre_evento);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // 4. Registrar Novedad en el Histórico (Para llevar la cuenta de quién falta más)
    public function registrarHistorico($miembro_id, $nota)
    {
        $query = "INSERT INTO " . $this->table_historico . " 
                  SET evento_id = :evento_id, miembro_id = :miembro_id, nota = :nota";

        $stmt = $this->conn->prepare($query);

        // Sanitización
        $nota = htmlspecialchars(strip_tags($nota));

        $stmt->bindParam(":evento_id", $this->id);
        $stmt->bindParam(":miembro_id", $miembro_id);
        $stmt->bindParam(":nota", $nota);

        return $stmt->execute();
    }
}
