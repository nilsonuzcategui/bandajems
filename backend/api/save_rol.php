<?php
// Cabeceras para API JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluimos la conexión y el modelo
include_once '../config/Database.php';
include_once '../models/Rol.php';

$database = new Database();
$db = $database->getConnection();

$rol = new Rol($db);

// Obtenemos los datos enviados por POST
// Si usas FormData en jQuery, usamos $_POST. Si usas JSON, usamos file_get_contents
$nombre = isset($_POST['nombre_rol']) ? $_POST['nombre_rol'] : null;

if (!empty($nombre)) {
    $rol->nombre_rol = $nombre;

    if ($rol->create()) {
        http_response_code(201);
        echo json_encode(array("status" => "success", "message" => "Rol creado correctamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("status" => "error", "message" => "No se pudo crear el rol."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Datos incompletos."));
}
