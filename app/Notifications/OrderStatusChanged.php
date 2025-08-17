<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title(__('Order Status Updated'))
            ->body(__('Order #:id status changed to :status', [
                'id' => $this->order->id,
                'status' => __($this->newStatus)
            ]))
            ->icon('/icons/icon-192x192.png')
            ->badge('/icons/badge-72x72.png')
            ->action(__('View Order'), route('orders.show', $this->order))
            ->data([
                'order_id' => $this->order->id,
                'url' => route('orders.show', $this->order)
            ])
            ->vibrate([200, 100, 200]);
    }

    public function toArray($notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'message' => __('Order #:id status changed to :status', [
                'id' => $this->order->id,
                'status' => __($this->newStatus)
            ])
        ];
    }
}