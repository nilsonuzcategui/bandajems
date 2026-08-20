<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Actividad.php';

$database = new Database();
$db = $database->getConnection();
$actividad = new Actividad($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id) || empty($data->titulo) || empty($data->fecha)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios (id, titulo, fecha)."]);
    exit;
}

$actividad->id = (int) $data->id;
$anterior = $actividad->readOne();
if (!$anterior) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "La actividad no existe."]);
    exit;
}

$actividad->titulo = $data->titulo;
$actividad->descripcion = isset($data->descripcion) ? $data->descripcion : "";
$actividad->lugar = isset($data->lugar) ? $data->lugar : "";
$actividad->fecha = $data->fecha;
$actividad->hora_inicio = !empty($data->hora_inicio) ? $data->hora_inicio : null;
$actividad->hora_fin = !empty($data->hora_fin) ? $data->hora_fin : null;
$actividad->categoria = !empty($data->categoria) ? $data->categoria : "otro";
$actividad->destacado = !empty($data->destacado) ? 1 : 0;
$actividad->estado = !empty($data->estado) ? $data->estado : "programada";

if ($actividad->update()) {
    echo json_encode([
        "status" => "success",
        "message" => "Actividad actualizada.",
    ]);
} else {
    http_response_code(503);
    echo json_encode(["status" => "error", "message" => "No se pudo actualizar la actividad."]);
}