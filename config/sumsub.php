<?php

return [
    'notifications' => [
        'telegram' => [
            'bot_token' => env('SUMSUB_NOTIFICATIONS_TELEGRAM_BOT_TOKEN', ''),
            'chat_id' => env('SUMSUB_NOTIFICATIONS_TELEGRAM_CHAT_ID', ''),
        ]
    ]
];
