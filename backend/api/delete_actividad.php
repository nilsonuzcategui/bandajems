<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE");
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

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    $data = json_decode(file_get_contents("php://input"));
    $id = isset($data->id) ? (int) $data->id : 0;
}

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id de la actividad."]);
    exit;
}

$actividad->id = $id;

if ($actividad->delete()) {
    echo json_encode(["status" => "success", "message" => "Actividad eliminada."]);
} else {
    http_response_code(503);
    echo json_encode(["status" => "error", "message" => "No se pudo eliminar la actividad."]);
}
