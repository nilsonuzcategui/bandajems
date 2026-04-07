<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Buscamos el SEGUNDO evento partiendo de hoy (OFFSET 1)
$query = "SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1 OFFSET 1";
$stmt = $db->prepare($query);
$stmt->execute();
$evento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    echo json_encode(["status" => "empty"]);
    exit;
}

// Traemos su lineup básico
$queryL = "SELECT m.nombre, r.nombre_rol 
           FROM evento_lineup el 
           JOIN miembros m ON el.miembro_id = m.id 
           JOIN roles r ON el.rol_id = r.id 
           WHERE el.evento_id = ?";
$stmtL = $db->prepare($queryL);
$stmtL->execute([$evento['id']]);
$lineup = $stmtL->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["status" => "success", "evento" => $evento, "lineup" => $lineup]);
