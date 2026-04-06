<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Miembro.php';

$database = new Database();
$db = $database->getConnection();
$miembro = new Miembro($db);

$stmt = $miembro->read();
$miembros = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode($miembros);
