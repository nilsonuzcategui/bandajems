<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/PropuestaCancion.php';

$database = new Database();
$db = $database->getConnection();
$propuesta = new PropuestaCancion($db);

$stmt = $propuesta->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $propuestas = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        array_push($propuestas, $row);
    }
    echo json_encode($propuestas);
} else {
    echo json_encode(array());
}