<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Cancion.php';

$database = new Database();
$db = $database->getConnection();
$cancion = new Cancion($db);

$stmt = $cancion->read();
$num = $stmt->rowCount();

// test

if ($num > 0) {
    $canciones = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($canciones, $row);
    }
    echo json_encode($canciones);
} else {
    echo json_encode(array());
}
