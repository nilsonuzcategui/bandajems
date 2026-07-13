<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/PropuestaCancion.php';

$database = new Database();
$db = $database->getConnection();
$propuesta = new PropuestaCancion($db);

if (empty($_POST['nombre_solicitante']) || empty($_POST['nombre_cancion'])) {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Nombre del solicitante y nombre de la canción son obligatorios"));
    exit;
}

$propuesta->nombre_solicitante = $_POST['nombre_solicitante'];
$propuesta->nombre_cancion = $_POST['nombre_cancion'];
$propuesta->url_referencia = isset($_POST['url_referencia']) ? $_POST['url_referencia'] : "";

if ($propuesta->create()) {
    echo json_encode(array("status" => "success"));
} else {
    http_response_code(503);
    echo json_encode(array("status" => "error"));
}