<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingStatusChanged extends Notification
{
    use Queueable;

    protected $booking;
    protected $status;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($booking, string $status)
    {
        $this->booking = $booking;
        $this->status = $status;
        
        // Set message based on status
        switch ($status) {
            case 'confirmed':
                $this->message = __('messages.booking_confirmed');
                break;
            case 'rejected':
                $this->message = __('messages.booking_rejected');
                break;
            case 'cancelled':
                $this->message = __('messages.booking_cancelled');
                break;
            case 'modification_approved':
                $this->message = 'Your booking modification has been approved';
                break;
            case 'modification_rejected':
                $this->message = 'Your booking modification has been rejected';
                break;
            default:
                $this->message = __('messages.booking_status_updated');
        }
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
        if ($this->booking) {
            return [
                'booking_id' => $this->booking->id,
                'apartment_title' => $this->booking->apartment->title,
                'status' => $this->status,
                'message' => $this->message,
                'total_price' => '$' . number_format($this->booking->total_price, 2),
                'start_date' => $this->booking->start_date,
                'end_date' => $this->booking->end_date
            ];
        } else {
            return [
                'status' => $this->status,
                'message' => $this->message,
            ];
        }
    }
}