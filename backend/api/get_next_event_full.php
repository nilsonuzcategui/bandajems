<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Si la petición es de tipo OPTIONS (Preflight), respondemos 200 y salimos
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// 1. Obtener el próximo evento (hoy o el más cercano a futuro)
$queryEv = "SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 1";
$stmtEv = $db->prepare($queryEv);
$stmtEv->execute();
$evento = $stmtEv->fetch(PDO::FETCH_ASSOC);

if (!$evento) {
    echo json_encode(["status" => "empty"]);
    exit;
}

$id = $evento['id'];

// 2. Obtener Lineup (Músicos y sus Roles)
$queryLineup = "SELECT m.nombre, m.foto_url, r.nombre_rol 
                FROM evento_lineup el
                JOIN miembros m ON el.miembro_id = m.id
                JOIN roles r ON el.rol_id = r.id
                WHERE el.evento_id = ?
                ORDER BY el.rol_id ASC";
$stmtL = $db->prepare($queryLineup);
$stmtL->execute([$id]);
$lineup = $stmtL->fetchAll(PDO::FETCH_ASSOC);

// 3. Obtener Setlist (Canciones)
$querySongs = "SELECT c.titulo, c.url_youtube, c.url_spotify, es.nota_tonalidad, m.nombre as cantante, m.foto_url 
               FROM evento_setlist es
               JOIN canciones c ON es.cancion_id = c.id
               LEFT JOIN miembros m ON es.cantante_id = m.id
               WHERE es.evento_id = ? 
               ORDER BY es.orden ASC";
$stmtS = $db->prepare($querySongs);
$stmtS->execute([$id]);
$songs = $stmtS->fetchAll(PDO::FETCH_ASSOC);

// 4. Obtener Histórico de este evento específico
$queryHist = "SELECT m.nombre, h.nota, h.creado_en 
              FROM historial_cambios h
              JOIN miembros m ON h.miembro_id = m.id
              WHERE h.evento_id = ? ORDER BY h.creado_en DESC";
$stmtH = $db->prepare($queryHist);
$stmtH->execute([$id]);
$historico = $stmtH->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "success",
    "evento" => $evento,
    "lineup" => $lineup,
    "setlist" => $songs,
    "historico" => $historico
]);
