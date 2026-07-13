<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/PropuestaCancion.php';

$database = new Database();
$db = $database->getConnection();
$propuesta = new PropuestaCancion($db);

$propuesta->id = $_POST['id'];

if ($propuesta->delete()) {
    echo json_encode(array("status" => "success"));
} else {
    http_response_code(503);
    echo json_encode(array("status" => "error"));
}