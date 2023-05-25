<?php

namespace App\Logging;

use App\Enums\LogResult;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;

class SumsubLoggingHandler extends AbstractProcessingHandler
{
    const TELEGRAM_BOT_URL = 'https://api.telegram.org/bot<bot_token>/sendMessage';

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        $chatId = config('logging.channels.sumsub.chat_id');
        $botToken = config('logging.channels.sumsub.token');

        $env = config('app.env');
        $message = 'Env:' . $env . PHP_EOL;
        $message .= '['. Carbon::now()->toDateTimeString().'] ' . $env . '.' . $record['level_name'] . ' ' . $record['message'] . PHP_EOL;
        $message .= json_encode($record['context']);
        $data = [
            'text' => $message,
            'chat_id' => $chatId
        ];

        Http::post(str_replace('<bot_token>', $botToken, self::TELEGRAM_BOT_URL), $data);
    }
}
