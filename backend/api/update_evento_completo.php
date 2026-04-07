<?php
// Cabeceras de CORS y JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejo del Preflight (OPTIONS) para evitar errores de CORS en navegadores
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Evento.php';

$database = new Database();
$db = $database->getConnection();
$evento = new Evento($db);

// Leemos el cuerpo de la petición (el JSON que envías)
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->evento_id) && !empty($data->nombre)) {

    // Iniciamos transacción para que si algo falla, no se borre el lineup anterior por error
    $db->beginTransaction();

    try {
        // 1. Seteamos y actualizamos datos básicos del evento
        $evento->id = $data->evento_id;
        $evento->nombre_evento = $data->nombre;
        $evento->fecha = $data->fecha;

        if (!$evento->update()) {
            throw new Exception("No se pudieron actualizar los datos principales del evento.");
        }

        // 2. Actualizar el Lineup (Músicos asignados)
        // Primero borramos el lineup viejo de este evento para evitar duplicados o basura
        $queryDelete = "DELETE FROM evento_lineup WHERE evento_id = ?";
        $stmtDelete = $db->prepare($queryDelete);
        $stmtDelete->execute([$evento->id]);

        // Si el objeto trae un nuevo lineup, lo insertamos
        if (!empty($data->lineup)) {
            $queryLineup = "INSERT INTO evento_lineup (evento_id, miembro_id, rol_id) VALUES (?, ?, ?)";
            $stmtLineup = $db->prepare($queryLineup);

            foreach ($data->lineup as $p) {
                if (!empty($p->miembro_id) && !empty($p->rol_id)) {
                    $stmtLineup->execute([$evento->id, $p->miembro_id, $p->rol_id]);
                }
            }
        }

        // 3. Registrar en el Histórico si hay reporte de incidencia
        if (!empty($data->miembro_afectado) && !empty($data->nota)) {
            // Usamos el método que ya tienes en el modelo Evento
            if (!$evento->registrarHistorico($data->miembro_afectado, $data->nota)) {
                throw new Exception("Error al registrar la incidencia en el histórico.");
            }
        }

        // Si llegamos aquí sin errores, confirmamos todo en la DB
        $db->commit();

        http_response_code(200);
        echo json_encode(array(
            "status" => "success",
            "message" => "Evento, lineup e histórico actualizados correctamente."
        ));
    } catch (Exception $e) {
        // Si algo falla, revertimos todos los cambios (el DELETE y el UPDATE)
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array(
            "status" => "error",
            "message" => $e->getMessage()
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Datos incompletos (ID y Nombre son obligatorios)."));
}
