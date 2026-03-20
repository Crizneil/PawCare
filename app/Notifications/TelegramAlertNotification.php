<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class TelegramAlertNotification extends Notification
{
    use Queueable;

    protected $data;
    protected $type;

    /**
     * @param mixed $data The model or array of data to notify about
     * @param string $type The type of notification (appointment_new, appointment_approved, appointment_rejected, appointment_cancelled, appointment_rescheduled, appointment_status_updated, account_created, vaccination_updated, pet_registered)
     */
    public function __construct($data, $type = 'appointment_new')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    public function toTelegram($notifiable)
    {
        switch ($this->type) {
            case 'appointment_new':
                return $this->formatAppointment("*New Appointment Request!* 📅");
            
            case 'appointment_approved':
                return $this->formatAppointment("*Appointment Approved!* ✅");

            case 'appointment_rejected':
                return $this->formatAppointment("*Appointment Rejected!* ❌", true);

            case 'appointment_cancelled':
                return $this->formatAppointment("*Appointment Cancelled!* ❌");

            case 'appointment_rescheduled':
                return $this->formatAppointment("*Appointment Rescheduled!* 🔄");

            case 'appointment_status_updated':
                return $this->formatAppointment("*Appointment Status Updated!* 📍\n*Status:* " . strtoupper($this->data->status));

            case 'account_created':
                return TelegramMessage::create()
                    ->to(env('TELEGRAM_CHAT_ID'))
                    ->content("*New Owner Registered!* 👤\n\n" .
                        "*Name:* " . $this->data->name . "\n" .
                        "*Email:* " . $this->data->email . "\n" .
                        "*Phone:* " . ($this->data->phone ?? 'N/A') . "\n" .
                        "*Address:* " . ($this->data->address ?? 'N/A'));

            case 'vaccination_updated':
                return TelegramMessage::create()
                    ->to(env('TELEGRAM_CHAT_ID'))
                    ->content("*Vaccination Updated!* 💉\n\n" .
                        "*Pet:* " . ($this->data->pet->name ?? 'Unknown') . "\n" .
                        "*Vaccine:* " . $this->data->vaccine_name . "\n" .
                        "*Date:* " . \Carbon\Carbon::parse($this->data->date_administered)->format('M d, Y') . "\n" .
                        "*Next Due:* " . ($this->data->next_due_date ? \Carbon\Carbon::parse($this->data->next_due_date)->format('M d, Y') : 'N/A') . "\n" .
                        "*Status:* " . strtoupper(str_replace('_', ' ', $this->data->status)));

            case 'pet_registered':
                return TelegramMessage::create()
                    ->to(env('TELEGRAM_CHAT_ID'))
                    ->content("*New Pet Registered!* 🐾\n\n" .
                        "*Name:* " . $this->data->name . "\n" .
                        "*Species:* " . $this->data->species . "\n" .
                        "*Breed:* " . $this->data->breed . "\n" .
                        "*Owner:* " . ($this->data->user->name ?? $this->data->owner));

            default:
                return TelegramMessage::create()
                    ->to(env('TELEGRAM_CHAT_ID'))
                    ->content("*PawCare Alert*\n\n" . json_encode($this->data));
        }
    }

    protected function formatAppointment($title, $includeReason = false)
    {
        $date = \Carbon\Carbon::parse($this->data->appointment_date)->format('M d, Y');
        $time = \Carbon\Carbon::parse($this->data->appointment_time)->format('h:i A');

        $content = $title . "\n\n" .
            "*Pet:* " . $this->data->pet_name . "\n" .
            "*Service:* " . $this->data->service_type . "\n" .
            "*Date:* " . $date . "\n" .
            "*Time:* " . $time . "\n" .
            "*Owner:* " . ($this->data->user->name ?? 'Unknown') . "\n" .
            "*Address:* " . ($this->data->address ?? 'N/A');

        if ($includeReason && $this->data->rejection_reason) {
            $content .= "\n*Reason:* " . $this->data->rejection_reason;
        }

        return TelegramMessage::create()
            ->to(env('TELEGRAM_CHAT_ID'))
            ->content($content);
    }
}
