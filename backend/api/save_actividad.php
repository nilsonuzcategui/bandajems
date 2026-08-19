<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../config/Database.php';
include_once __DIR__ . '/../config/Sendpulse.php';
include_once __DIR__ . '/../models/Actividad.php';
include_once __DIR__ . '/SendpulseHelper.php';

$database = new Database();
$db = $database->getConnection();
$actividad = new Actividad($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->titulo) || empty($data->fecha)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Título y fecha son obligatorios."]);
    exit;
}

$actividad->titulo = $data->titulo;
$actividad->descripcion = isset($data->descripcion) ? $data->descripcion : "";
$actividad->lugar = isset($data->lugar) ? $data->lugar : "";
$actividad->fecha = $data->fecha;
$actividad->hora_inicio = !empty($data->hora_inicio) ? $data->hora_inicio : null;
$actividad->hora_fin = !empty($data->hora_fin) ? $data->hora_fin : null;
$actividad->categoria = !empty($data->categoria) ? $data->categoria : "otro";
$actividad->destacado = !empty($data->destacado) ? 1 : 0;
$actividad->estado = !empty($data->estado) ? $data->estado : "programada";
$actividad->creado_por = !empty($data->creado_por) ? (int) $data->creado_por : null;

try {
    $id = $actividad->create();
    if (!$id) {
        throw new Exception("No se pudo registrar la actividad.");
    }

    $pushResult = null;
    if ($actividad->estado !== "cancelada") {
        $categoriaLabel = ucfirst($actividad->categoria);
        $title = "📅 " . $actividad->titulo;
        $bodyParts = [];
        if (!empty($actividad->hora_inicio)) {
            $bodyParts[] = substr($actividad->hora_inicio, 0, 5);
        }
        if (!empty($actividad->lugar)) {
            $bodyParts[] = $actividad->lugar;
        }
        $bodyParts[] = $categoriaLabel;
        $body = implode(" • ", $bodyParts);

        $pushResult = SendpulseHelper::sendPush($title, $body);
    }

    http_response_code(201);
    echo json_encode([
        "status" => "success",
        "message" => "Actividad creada correctamente.",
        "id" => $id,
        "push" => $pushResult,
    ]);
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
