<?php
// Cabeceras se envían SIEMPRE antes de cualquier salida, incluso en errores fatales.
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No se recibió ninguna imagen."]);
    exit;
}

$file = $_FILES['foto'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Error al subir el archivo (código " . $file['error'] . ").",
    ]);
    exit;
}

$mime = strtolower($file['type']);
$extPorMime = [
    'image/jpeg' => 'jpg',
    'image/jpg'  => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

if (!isset($extPorMime[$mime])) {
    $extTmp = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extTmp, $allowedExt, true)) {
        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Formato no permitido. Usa JPG, PNG, WEBP o GIF.",
        ]);
        exit;
    }
    $ext = $extTmp === 'jpeg' ? 'jpg' : $extTmp;
} else {
    $ext = $extPorMime[$mime];
}

$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "La imagen supera el máximo permitido de 5MB.",
    ]);
    exit;
}

$info = @getimagesize($file['tmp_name']);
if ($info === false) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "El archivo no es una imagen válida.",
    ]);
    exit;
}

$nombreUnico = 'm_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

// Carpeta de subida DENTRO del backend (funciona aunque el frontend esté en otro host).
$directorioDestino = dirname(__DIR__) . '/uploads/miembros/';
$directorioDestino = str_replace('\\', '/', $directorioDestino);

if (!is_dir($directorioDestino)) {
    if (!@mkdir($directorioDestino, 0755, true) && !is_dir($directorioDestino)) {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "No se pudo crear la carpeta de destino en el backend.",
        ]);
        exit;
    }
}

$rutaFinal = $directorioDestino . $nombreUnico;

if (!@move_uploaded_file($file['tmp_name'], $rutaFinal)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "No se pudo guardar el archivo. Verifica permisos de escritura.",
    ]);
    exit;
}

@chmod($rutaFinal, 0644);

// URL ABSOLUTA servida por serve_image.php en el MISMO backend.
// En producción será https://api.micasajems.com/api/serve_image.php?f=...
// En local será http://localhost/bandajems/backend/api/serve_image.php?f=...
$esquema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$urlPublica = $esquema . '://' . $host . '/api/serve_image.php?f=' . urlencode($nombreUnico);

echo json_encode([
    "status" => "success",
    "url" => $urlPublica,
    "filename" => $nombreUnico,
    "message" => "Imagen subida correctamente.",
]);
