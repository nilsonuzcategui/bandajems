<?php
// Cabeceras para API JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Incluimos la conexión y el modelo con la ruta corregida para XAMPP
include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Cancion.php';

$database = new Database();
$db = $database->getConnection();

$cancion = new Cancion($db);

// Capturamos los datos enviados por AJAX
$id = isset($_POST['id']) ? $_POST['id'] : null;
$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : null;
$url_youtube = isset($_POST['url_youtube']) ? $_POST['url_youtube'] : null;
$url_spotify = isset($_POST['url_spotify']) ? $_POST['url_spotify'] : null;

// Validación básica: El ID y el Título son obligatorios
if (!empty($id) && !empty($titulo)) {

    // Seteamos los valores al objeto
    $cancion->id = $id;
    $cancion->titulo = $titulo;
    $cancion->url_youtube = $url_youtube;
    $cancion->url_spotify = $url_spotify;
    $cancion->url_otro = isset($_POST['url_otro']) ? $_POST['url_otro'] : null;

    // Intentamos actualizar
    if ($cancion->update()) {
        http_response_code(200);
        echo json_encode(array("status" => "success", "message" => "Canción actualizada correctamente."));
    } else {
        http_response_code(503);
        echo json_encode(array("status" => "error", "message" => "No se pudo actualizar la canción en la base de datos."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Faltan datos obligatorios (ID o Título)."));
}
