<?php
// Permitir cualquier origen (puedes poner http://localhost si quieres ser más específico)
header("Access-Control-Allow-Origin: *");
// Permitir los métodos que estamos usando
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
// Permitir las cabeceras personalizadas (importante para Content-Type JSON)
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Si la petición es de tipo OPTIONS (Preflight), respondemos 200 y salimos
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Evento.php';

$database = new Database();
$db = $database->getConnection();
$evento = new Evento($db);

// Como enviamos JSON desde el frontend, leemos el input raw
$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->nombre) &&
    !empty($data->fecha) &&
    !empty($data->lineup)
) {
    // Seteamos los datos básicos del evento
    $evento->nombre_evento = $data->nombre;
    $evento->fecha = $data->fecha;

    // var_dump($data); // Debug: Ver qué datos estamos recibiendo

    try {
        $db->beginTransaction();

        // 1. Crear el evento principal
        $id_evento = $evento->create();

        if ($id_evento === "FECHA_DUPLICADA") {
            throw new Exception("Ya existe un evento registrado para esta fecha.");
        }

        if (!$id_evento) {
            throw new Exception("No se pudo crear el evento principal.");
        }

        // 2. Insertar el Lineup (Músicos y Roles)
        $queryLineup = "INSERT INTO evento_lineup (evento_id, miembro_id, rol_id) VALUES (:ev, :mi, :ro)";
        $stmtLineup = $db->prepare($queryLineup);

        foreach ($data->lineup as $p) {
            $stmtLineup->bindParam(":ev", $id_evento);
            $stmtLineup->bindParam(":mi", $p->miembro_id);
            $stmtLineup->bindParam(":ro", $p->rol_id);
            $stmtLineup->execute();
        }

        // 3. Insertar el Setlist (Canciones)
        if (!empty($data->setlist)) {
            $querySetlist = "INSERT INTO evento_setlist (evento_id, cancion_id, orden) VALUES (:ev, :ca, :ord)";
            $stmtSetlist = $db->prepare($querySetlist);

            $orden = 1;
            foreach ($data->setlist as $s) {
                // Solo insertamos si la canción tiene un ID de la base de datos
                if (!empty($s->id)) {
                    $stmtSetlist->bindParam(":ev", $id_evento);
                    $stmtSetlist->bindParam(":ca", $s->id);
                    $stmtSetlist->bindParam(":ord", $orden);
                    $stmtSetlist->execute();
                    $orden++;
                }
            }
        }

        // Si todo salió bien, confirmamos los cambios
        $db->commit();

        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Evento JEMS creado con éxito", "id" => $id_evento));
    } catch (Exception $e) {
        // Si algo falla, revertimos todo lo que se haya insertado
        $db->rollBack();
        http_response_code(503);
        echo json_encode(array("status" => "error", "message" => $e->getMessage()));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Datos incompletos. Nombre, fecha y músicos son obligatorios."));
}
