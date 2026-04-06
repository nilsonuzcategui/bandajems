<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Miembro.php';

$database = new Database();
$db = $database->getConnection();
$miembro = new Miembro($db);

$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : null;
$foto = isset($_POST['foto_url']) ? $_POST['foto_url'] : 'https://i.pravatar.cc/150?u=jems'; // Foto por defecto

if (!empty($nombre)) {
    $miembro->nombre = $nombre;
    $miembro->foto_url = $foto;

    if ($miembro->create()) {
        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Miembro registrado."));
    } else {
        http_response_code(503);
        echo json_encode(array("status" => "error", "message" => "No se pudo registrar."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Faltan datos."));
}
