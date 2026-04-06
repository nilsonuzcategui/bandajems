<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Miembro.php';

$database = new Database();
$db = $database->getConnection();
$miembro = new Miembro($db);

// Capturamos el ID y los datos
$id = isset($_POST['id']) ? $_POST['id'] : null;
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
$foto = isset($_POST['foto_url']) ? $_POST['foto_url'] : null;

if (!empty($id) && !empty($nombre)) {
    $miembro->id = $id;
    $miembro->nombre = $nombre;
    $miembro->foto_url = $foto;

    if ($miembro->update()) { // Debes tener el método update en tu modelo
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Miembro actualizado."));
    } else {
        http_response_code(503);
        echo json_encode(array("status" => "error", "message" => "No se pudo actualizar."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Datos incompletos."));
}
