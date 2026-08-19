<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Actividad.php';

$database = new Database();
$db = $database->getConnection();
$actividad = new Actividad($db);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta el id de la actividad."]);
    exit;
}

$actividad->id = $id;
$row = $actividad->readOne();

if ($row) {
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Actividad no encontrada."]);
}
