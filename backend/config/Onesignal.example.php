<?php
class OneSignalConfig
{
    // OneSignal App ID (público, también va en el frontend)
    const APP_ID = "TU_APP_ID_AQUI";

    // REST API Key (SECRETO — solo backend, nunca commitear)
    // Obtener en: OneSignal Dashboard → Settings → Keys & IDs → REST API Key
    const REST_API_KEY = "TU_REST_API_KEY_AQUI";

    // URL base de la API REST de OneSignal
    const API_BASE_URL = "https://onesignal.com/api/v1";

    // URL pública de la landing de actividades (link del push)
    const PUBLIC_ACTIVIDADES_URL = "https://micasajems.com/actividades.html";

    // Bandera para activar/desactivar el envío real de pushes (útil en local)
    const ENABLED = true;
}