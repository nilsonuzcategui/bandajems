<?php
class SendpulseConfig
{
    // API User ID - SendPulse > Settings > API > API keys
    const API_USER_ID = "TU_API_USER_ID_AQUI";

    // API Secret - SendPulse > Settings > API > API keys
    const API_SECRET = "TU_API_SECRET_AQUI";

    // Website ID de la web registrada en SendPulse Web Push
    const WEBSITE_ID = 0;

    // URL base de la API REST de SendPulse
    const API_BASE_URL = "https://api.sendpulse.com";

    // Tiempo de vida de la notificación push (en segundos). Máx 86400 (24h)
    const PUSH_TTL = 3600;

    // URL pública de la landing de actividades (se usa en el link del push)
    const PUBLIC_ACTIVIDADES_URL = "https://micasajems.com/actividades.html";

    // Bandera para activar/desactivar el envío real de pushes (útil en local)
    const ENABLED = true;
}
