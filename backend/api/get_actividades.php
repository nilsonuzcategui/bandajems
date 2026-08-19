<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../models/Actividad.php';

$database = new Database();
$db = $database->getConnection();
$actividad = new Actividad($db);

$filtros = [];
if (!empty($_GET['desde'])) $filtros['desde'] = $_GET['desde'];
if (!empty($_GET['hasta'])) $filtros['hasta'] = $_GET['hasta'];
if (!empty($_GET['categoria'])) $filtros['categoria'] = $_GET['categoria'];
if (!empty($_GET['estado'])) $filtros['estado'] = $_GET['estado'];
if (!empty($_GET['incluir_canceladas'])) $filtros['incluir_canceladas'] = true;

$stmt = $actividad->readAll($filtros);
$num = $stmt->rowCount();

if ($num > 0) {
    $actividades = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $actividades[] = $row;
    }
    echo json_encode($actividades);
} else {
    echo json_encode([]);
}
