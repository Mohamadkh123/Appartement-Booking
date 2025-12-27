<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AdminWalletDeposit extends Notification
{
    use Queueable;

    protected $amount;
    protected $tenantId;

    /**
     * Create a new notification instance.
     */
    public function __construct($amount, $tenantId)
    {
        $this->amount = $amount;
        $this->tenantId = $tenantId;
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
        return [
            'type' => 'admin_wallet_deposit',
            'message' => 'Admin deposited amount to your wallet',
            'amount' => $this->amount,
            'tenant_id' => $this->tenantId,
            'action' => 'deposit'
        ];
    }
}