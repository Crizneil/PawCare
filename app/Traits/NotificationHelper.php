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
            // 1. Determine the recipient(s)
            $chatIds = [];

            // A. Check if the $recipient can be determined from data
            $recipient = null;
            if ($data instanceof \App\Models\Appointment) {
                // If it's an appointment, get the user relationship (will load if needed)
                $recipient = $data->user;
            } elseif ($data instanceof \App\Models\User) {
                $recipient = $data;
            } elseif ($data instanceof \App\Models\Pet) {
                $recipient = $data->user;
            } elseif (isset($data->user)) {
                $recipient = $data->user;
            }

            if ($recipient && $recipient instanceof \App\Models\User && $recipient->telegram_chat_id) {
                $chatIds[] = $recipient->telegram_chat_id;
                Log::info("Telegram recipient found: {$recipient->name} (Chat ID: {$recipient->telegram_chat_id})");
            } else {
                Log::info("No private Telegram recipient found for type: {$type}");
            }

            // B. Fallback to global config (Admin/Staff alerts) if no private recipient or for specific types
            $globalChatIdString = config('services.telegram-bot-api.chat_id', env('TELEGRAM_CHAT_ID'));
            if ($globalChatIdString) {
                $globalIds = explode(',', $globalChatIdString);
                foreach ($globalIds as $gid) {
                    $gid = trim($gid);
                    if ($gid && !in_array($gid, $chatIds)) {
                        $chatIds[] = $gid;
                    }
                }
            }

            if (empty($chatIds)) {
                return;
            }

            // Verify bot token presence
            if (!config('services.telegram-bot-api.token')) {
                Log::error('Telegram Bot Token is missing in configuration! Check your .env for TELEGRAM_BOT_TOKEN.');
                return;
            }

            foreach ($chatIds as $chatId) {
                Notification::route('telegram', $chatId)
                    ->notify(new TelegramAlertNotification($data, $type));
            }
        } catch (\Throwable $e) {
            Log::error("Failed to send Telegram notification ({$type}): " . $e->getMessage());
        }
    }
}
