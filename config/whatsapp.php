<?php

return [
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'clinic_assist_secret_token'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),
    'min_confidence_score' => (float) env('CHATBOT_MIN_CONFIDENCE', 0.4),
];
