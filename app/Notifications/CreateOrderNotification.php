<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreateOrderNotification extends Notification
{
    use Queueable;

    public $order;
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your New Order Has Been Created")
            ->greeting("Dear " . $this->order->customer->name . ",") // Customer's name from the order relationship
            ->line("Thank you for placing your order with us. We have successfully received it.")
            ->line("Order Details:")
            ->line("Order Number: " . $this->order->number)
            ->line("Total Amount: " . number_format($this->order->total_price, 2) . " $")
            ->line("Payment Method: " . ($this->order->paymentMethod->name ?? 'Not Specified'))
            ->line("Order Date: " . $this->order->created_at->format('Y-m-d H:i'))
            //->action('View Order Details', url('/orders/' . $this->order->id))
            ->line('Thank you for your trust in us! We look forward to providing you with an excellent shopping experience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
