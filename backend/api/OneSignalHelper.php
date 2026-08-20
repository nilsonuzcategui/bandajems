<?php
include_once __DIR__ . '/../config/Onesignal.php';

class OneSignalHelper
{
    /**
     * Envía una notificación push a todos los suscriptores suscritos de la app.
     *
     * @param string $title Título del push
     * @param string $body  Contenido del push
     * @param string|null $link URL a la que dirige al hacer click
     * @return array ['success' => bool, 'http_code' => int, 'response' => mixed, 'message' => string|null]
     */
    public static function sendPush($title, $body, $link = null)
    {
        if (!OneSignalConfig::ENABLED) {
            return [
                'success' => true,
                'http_code' => 200,
                'response' => 'PUSH_DISABLED',
                'message' => 'Envío de push desactivado por configuración.',
            ];
        }

        $fields = [
            'app_id' => OneSignalConfig::APP_ID,
            'included_segments' => ['Subscribed Users'],
            'headings' => ['en' => $title, 'es' => $title],
            'contents' => ['en' => $body, 'es' => $body],
        ];

        if (!empty($link)) {
            $fields['url'] = $link;
        } else {
            $fields['url'] = OneSignalConfig::PUBLIC_ACTIVIDADES_URL;
        }

        $ch = curl_init(OneSignalConfig::API_BASE_URL . "/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ' . OneSignalConfig::REST_API_KEY,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'http_code' => 0,
                'response' => null,
                'message' => 'cURL error: ' . $curlError,
            ];
        }

        $decoded = json_decode($response, true);
        $isOk = $httpCode >= 200 && $httpCode < 300;
        $apiMessage = is_array($decoded) && isset($decoded['errors'][0]) ? $decoded['errors'][0] : null;

        return [
            'success' => $isOk,
            'http_code' => $httpCode,
            'response' => $decoded,
            'message' => $apiMessage,
        ];
    }
}