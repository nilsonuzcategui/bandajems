<?php
// Cabeceras para que el navegador entienda que enviamos JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Incluimos la conexión y el modelo
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Evento.php';

$database = new Database();
$db = $database->getConnection();

$evento = new Evento($db);

// Ejecutamos el método que creamos en el modelo
$stmt = $evento->readCronograma();
$num = $stmt->rowCount();

if ($num > 0) {
    $eventos_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $evento_item = array(
            "id" => $id,
            "fecha" => $fecha,
            "nombre_evento" => $nombre_evento
        );

        array_push($eventos_arr, $evento_item);
    }

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode($eventos_arr);
} else {
    // Si no hay eventos, devolvemos un array vacío
    http_response_code(200);
    echo json_encode(array());
}
