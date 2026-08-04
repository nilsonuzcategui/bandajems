<?php
// Cabeceras primero: absolutamente todo se responde con CORS permitido,
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$baseUploads = dirname(__DIR__) . '/uploads/miembros/';
$baseUploads = str_replace('\\', '/', realpath($baseUploads) ?: $baseUploads);

$nombre = isset($_GET['f']) ? basename($_GET['f']) : '';
if ($nombre === '' || $nombre !== basename($nombre)) {
    http_response_code(400);
    exit;
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    exit;
}

$ruta = $baseUploads . $nombre;
if (!is_file($ruta)) {
    http_response_code(404);
    exit;
}

$mimePorExt = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'webp' => 'image/webp',
    'gif'  => 'image/gif',
];
$mime = $mimePorExt[$ext] ?? 'application/octet-stream';

header("Content-Type: " . $mime);
header("Content-Length: " . filesize($ruta));
header("Cache-Control: public, max-age=2592000, immutable");
header("X-Content-Type-Options: nosniff");

while (ob_get_level() > 0) {
    ob_end_clean();
}

readfile($ruta);
exit;
