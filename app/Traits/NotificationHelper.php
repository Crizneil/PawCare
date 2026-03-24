<?php

namespace App\Traits;

use Illuminate\Support\Facades\Notification;
use App\Notifications\TelegramAlertNotification;
use Illuminate\Support\Facades\Log;

trait NotificationHelper
{
    /**
     * Send a Telegram notification to all configured chat IDs.
     * 
     * @param mixed $data The data to notify about
     * @param string $type The notification type
     * @return void
     */
    protected function sendTelegramNotification($data, $type = 'appointment_new')
    {
        try {
            $chatIdString = config('services.telegram-bot-api.chat_id', env('TELEGRAM_CHAT_ID'));
            
            if (!$chatIdString) {
                return;
            }

            $chatIds = explode(',', $chatIdString);
            
            foreach ($chatIds as $chatId) {
                $chatId = trim($chatId);
                if ($chatId) {
                    Notification::route('telegram', $chatId)
                        ->notify(new TelegramAlertNotification($data, $type));
                }
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send Telegram notification ({$type}): " . $e->getMessage());
        }
    }
}
