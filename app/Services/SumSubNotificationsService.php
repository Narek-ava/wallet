<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SumSubNotificationsService
{
    protected $telegramBotToken;
    protected $telegramChatId;
    protected $appName;
    protected $appEnv;

    public function __construct()
    {
        $this->telegramBotToken = config('sumsub.notifications.telegram.bot_token');
        $this->telegramChatId = config('sumsub.notifications.telegram.chat_id');
        $this->appName = config('app.name');
        $this->appEnv = config('app.env');
    }

    public function write(array $record, string $message = ''): void
    {
        if (!$this->telegramBotToken || !$this->telegramChatId) {
            throw new \InvalidArgumentException('Bot token or chat id is not defined for Telegram logger');
        }

        try {
            $this->sendTelegramNotification($this->formatText($this->generateMessageText($record), $message));
        } catch (\Throwable $exception) {
            Log::channel('single')->error($exception->getMessage());
        }
    }

    private function sendTelegramNotification(string $text): void
    {
        $params = [
            'text' => $text,
            'chat_id' => $this->telegramChatId,
            'parse_mode' => 'html',
        ];

        $url = 'https://api.telegram.org/bot%s/sendMessage';

        Http::get(sprintf($url, $this->telegramBotToken), http_build_query($params));
    }

    private function formatText(string $text, string $message): string
    {
        return view('logging.telegram-standard', [
                'appName' => $this->appName,
                'appEnv' => $this->appEnv,
                'datetime' => Carbon::now()->toDateTimeString(),
                'message' => $message,
                'formatted' => $text
            ]
        )->toHtml();
    }

    private function generateMessageText(array $data): string
    {
        $text = "\n";

        foreach ($data as $key => $value) {
            $text .= $key . ': ';
            $text .= is_array($value) ? json_encode($value) : $value;
            $text .= "\n";
        }

        return $text;
    }

}
