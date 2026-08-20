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
     * @return array ['success' => bool, 'http_code' => int, 'response' => mixed]
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

        $url = OneSignalConfig::API_BASE_URL . "/notifications";

        $payload = [
            'app_id' => OneSignalConfig::APP_ID,
            'included_segments' => ['Subscribed Users'],
            'headings' => ['en' => $title, 'es' => $title],
            'contents' => ['en' => $body, 'es' => $body],
            'web_push_topic' => 'iglesia-actividades',
            'chrome_web_icon' => 'https://banda.micasajems.com/images/logobandajems.png',
            'firefox_icon' => 'https://banda.micasajems.com/images/logobandajems.png',
        ];

        if (!empty($link)) {
            $payload['url'] = $link;
        } else {
            $payload['url'] = OneSignalConfig::PUBLIC_ACTIVIDADES_URL;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . OneSignalConfig::REST_API_KEY,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'http_code' => 0,
                'response' => null,
                'message' => 'cURL error: ' . $error,
            ];
        }

        $decoded = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $decoded,
        ];
    }
}