<?php
// Cabeceras obligatorias para JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Incluimos la conexión y el modelo
include_once '../config/Database.php';
include_once '../models/Rol.php';

// Inicializamos base de datos
$database = new Database();
$db = $database->getConnection();

// Inicializamos el objeto Rol
$rol = new Rol($db);

// Consultar roles
$stmt = $rol->read();
$num = $stmt->rowCount();

// Verificamos si hay registros
if ($num > 0) {
    $roles_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $rol_item = array(
            "id" => $id,
            "nombre_rol" => $nombre_rol
        );

        array_push($roles_arr, $rol_item);
    }

    // Respuesta exitosa (200 OK)
    http_response_code(200);
    echo json_encode($roles_arr);
} else {
    // Si no hay roles (200 OK pero array vacío o mensaje)
    http_response_code(200);
    echo json_encode(array());
}
