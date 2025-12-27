<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApartmentActivity extends Notification
{
    use Queueable;

    protected $type;
    protected $apartmentId;
    protected $userId;

    /**
     * Create a new notification instance.
     */
    public function __construct($type, $apartmentId, $userId)
    {
        $this->type = $type;
        $this->apartmentId = $apartmentId;
        $this->userId = $userId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = match($this->type) {
            'booking' => 'A booking has been made for your apartment',
            'cancellation' => 'A booking has been cancelled for your apartment',
            'new_apartment' => 'A new apartment has been added',
            'update_apartment' => 'Apartment details have been updated',
            default => 'Apartment activity notification'
        };

        return [
            'type' => $this->type,
            'message' => $message,
            'apartment_id' => $this->apartmentId,
            'user_id' => $this->userId,
        ];
    }
}