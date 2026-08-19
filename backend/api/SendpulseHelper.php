<?php
include_once __DIR__ . '/../config/Sendpulse.php';

class SendpulseHelper
{
    private static $token = null;
    private static $tokenExpiresAt = 0;

    /**
     * Obtiene (o reutiliza) el access_token de SendPulse.
     */
    public static function getToken()
    {
        if (self::$token !== null && time() < self::$tokenExpiresAt - 60) {
            return self::$token;
        }

        $url = SendpulseConfig::API_BASE_URL . "/oauth/access_token";

        $data = [
            "grant_type" => "client_credentials",
            "client_id" => SendpulseConfig::API_USER_ID,
            "client_secret" => SendpulseConfig::API_SECRET,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/x-www-form-urlencoded",
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("Sendpulse token cURL error: " . $error);
            return null;
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400 || empty($decoded['access_token'])) {
            error_log("Sendpulse token error (" . $httpCode . "): " . $response);
            return null;
        }

        self::$token = $decoded['access_token'];
        self::$tokenExpiresAt = time() + (int) ($decoded['expires_in'] ?? 3600);

        return self::$token;
    }

    /**
     * Envía una notificación push a todos los suscriptores del website configurado.
     *
     * @param string $title Título del push
     * @param string $body  Contenido del push
     * @param string|null $link URL a la que dirige al hacer click
     * @param int|null $ttl Segundos de vida del push (opcional)
     * @return array ['success' => bool, 'http_code' => int, 'response' => mixed]
     */
    public static function sendPush($title, $body, $link = null, $ttl = null)
    {
        if (!SendpulseConfig::ENABLED) {
            return [
                'success' => true,
                'http_code' => 200,
                'response' => 'PUSH_DISABLED',
                'message' => 'Envío de push desactivado por configuración.',
            ];
        }

        $token = self::getToken();
        if (!$token) {
            return [
                'success' => false,
                'http_code' => 0,
                'response' => null,
                'message' => 'No se pudo obtener el token de SendPulse.',
            ];
        }

        $url = SendpulseConfig::API_BASE_URL . "/push/tasks";

        $payload = [
            "title" => $title,
            "website_id" => SendpulseConfig::WEBSITE_ID,
            "body" => $body,
            "ttl" => $ttl !== null ? (int) $ttl : (int) SendpulseConfig::PUSH_TTL,
        ];

        if (!empty($link)) {
            $payload["link"] = $link;
        } else {
            $payload["link"] = SendpulseConfig::PUBLIC_ACTIVIDADES_URL;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json",
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

        // Si el token expiró (401), intentamos una vez más refrescando
        if ($httpCode === 401) {
            self::$token = null;
            self::$tokenExpiresAt = 0;
            $token = self::getToken();
            if (!$token) {
                return [
                    'success' => false,
                    'http_code' => 401,
                    'response' => $decoded,
                    'message' => 'Token expirado y no se pudo refrescar.',
                ];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $decoded = json_decode($response, true);
        }

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'response' => $decoded,
        ];
    }
}
