<?php
// Cabeceras para permitir el acceso y formato JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Incluimos la configuración de la base de datos
include_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Obtenemos el ID del evento por la URL
$evento_id = isset($_GET['evento_id']) ? $_GET['evento_id'] : null;

if (!empty($evento_id)) {
    try {
        // Query para traer el setlist con los nombres de las canciones
        // Ordenamos por la columna 'orden' para que aparezcan tal cual se planearon
        $query = "SELECT 
                    es.cancion_id, 
                    c.titulo, 
                    es.orden, 
                    es.cantante_id, 
                    es.nota_tonalidad 
                  FROM evento_setlist es
                  JOIN canciones c ON es.cancion_id = c.id
                  WHERE es.evento_id = :ev_id
                  ORDER BY es.orden ASC";

        $stmt = $db->prepare($query);
        $stmt->bindParam(":ev_id", $evento_id);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $setlist_arr = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $setlist_item = array(
                    "cancion_id" => $cancion_id,
                    "titulo" => $titulo,
                    "orden" => $orden,
                    "cantante_id" => $cantante_id,
                    "nota_tonalidad" => $nota_tonalidad
                );
                array_push($setlist_arr, $setlist_item);
            }

            http_response_code(200);
            echo json_encode($setlist_arr);
        } else {
            // Si el evento existe pero no tiene canciones aún
            http_response_code(200);
            echo json_encode(array());
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("message" => "Error de servidor: " . $e->getMessage()));
    }
} else {
    // Si no se envió el ID del evento
    http_response_code(400);
    echo json_encode(array("message" => "No se proporcionó el ID del evento."));
}
