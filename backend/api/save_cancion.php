<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Cancion.php';

$database = new Database();
$db = $database->getConnection();
$cancion = new Cancion($db);

$cancion->titulo = $_POST['titulo'];
$cancion->url_youtube = $_POST['url_youtube'];
$cancion->url_spotify = $_POST['url_spotify'];

if ($cancion->create()) {
    echo json_encode(array("status" => "success"));
} else {
    http_response_code(503);
    echo json_encode(array("status" => "error"));
}
