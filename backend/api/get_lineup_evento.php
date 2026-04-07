<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$evento_id = isset($_GET['evento_id']) ? $_GET['evento_id'] : null;

if ($evento_id) {
    $query = "SELECT rol_id, miembro_id FROM evento_lineup WHERE evento_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$evento_id]);

    $lineup = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($lineup);
} else {
    echo json_encode([]);
}
