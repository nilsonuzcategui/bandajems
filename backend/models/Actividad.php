<?php
class Actividad
{
    private $conn;
    private $table_name = "actividades";

    public $id;
    public $titulo;
    public $descripcion;
    public $lugar;
    public $fecha;
    public $hora_inicio;
    public $hora_fin;
    public $categoria;
    public $destacado;
    public $estado;
    public $creado_por;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function readAll($filtros = [])
    {
        $where = [];
        $params = [];

        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $where[] = "a.fecha BETWEEN :desde AND :hasta";
            $params[':desde'] = $filtros['desde'];
            $params[':hasta'] = $filtros['hasta'];
        } elseif (!empty($filtros['desde'])) {
            $where[] = "a.fecha >= :desde";
            $params[':desde'] = $filtros['desde'];
        } elseif (!empty($filtros['hasta'])) {
            $where[] = "a.fecha <= :hasta";
            $params[':hasta'] = $filtros['hasta'];
        }

        if (!empty($filtros['categoria'])) {
            $where[] = "a.categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }

        if (!empty($filtros['estado'])) {
            $where[] = "a.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        } elseif (empty($filtros['incluir_canceladas'])) {
            $where[] = "a.estado <> 'cancelada'";
        }

        $sql = "SELECT a.id, a.titulo, a.descripcion, a.lugar, a.fecha, a.hora_inicio, a.hora_fin,
                       a.categoria, a.destacado, a.estado, a.creado_por, a.created_at,
                       m.nombre AS creado_por_nombre
                FROM " . $this->table_name . " a
                LEFT JOIN miembros m ON m.id = a.creado_por";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.fecha ASC, a.hora_inicio ASC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT a.*, m.nombre AS creado_por_nombre
                  FROM " . $this->table_name . " a
                  LEFT JOIN miembros m ON m.id = a.creado_por
                  WHERE a.id = :id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . "
                  SET titulo=:titulo,
                      descripcion=:descripcion,
                      lugar=:lugar,
                      fecha=:fecha,
                      hora_inicio=:hora_inicio,
                      hora_fin=:hora_fin,
                      categoria=:categoria,
                      destacado=:destacado,
                      estado=:estado,
                      creado_por=:creado_por";

        $stmt = $this->conn->prepare($query);

        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->lugar = htmlspecialchars(strip_tags($this->lugar));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->hora_inicio = !empty($this->hora_inicio) ? htmlspecialchars(strip_tags($this->hora_inicio)) : null;
        $this->hora_fin = !empty($this->hora_fin) ? htmlspecialchars(strip_tags($this->hora_fin)) : null;
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->destacado = !empty($this->destacado) ? 1 : 0;
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":lugar", $this->lugar);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":destacado", $this->destacado);
        $stmt->bindParam(":estado", $this->estado);

        if (!empty($this->creado_por)) {
            $stmt->bindParam(":creado_por", $this->creado_por);
        } else {
            $stmt->bindValue(":creado_por", null, PDO::PARAM_NULL);
        }

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . "
                  SET titulo=:titulo,
                      descripcion=:descripcion,
                      lugar=:lugar,
                      fecha=:fecha,
                      hora_inicio=:hora_inicio,
                      hora_fin=:hora_fin,
                      categoria=:categoria,
                      destacado=:destacado,
                      estado=:estado
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->titulo = htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->lugar = htmlspecialchars(strip_tags($this->lugar));
        $this->fecha = htmlspecialchars(strip_tags($this->fecha));
        $this->hora_inicio = !empty($this->hora_inicio) ? htmlspecialchars(strip_tags($this->hora_inicio)) : null;
        $this->hora_fin = !empty($this->hora_fin) ? htmlspecialchars(strip_tags($this->hora_fin)) : null;
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->destacado = !empty($this->destacado) ? 1 : 0;
        $this->estado = htmlspecialchars(strip_tags($this->estado));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":lugar", $this->lugar);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":hora_inicio", $this->hora_inicio);
        $stmt->bindParam(":hora_fin", $this->hora_fin);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":destacado", $this->destacado);
        $stmt->bindParam(":estado", $this->estado);

        return $stmt->execute();
    }

    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function readProximas($limite = 5)
    {
        $query = "SELECT a.id, a.titulo, a.descripcion, a.lugar, a.fecha, a.hora_inicio, a.hora_fin,
                       a.categoria, a.destacado, a.estado
                FROM " . $this->table_name . " a
                WHERE a.fecha >= CURDATE() AND a.estado <> 'cancelada'
                ORDER BY a.fecha ASC, a.hora_inicio ASC
                LIMIT :limite";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":limite", (int) $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
}
