<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Delivery channels. 'database' stores it for the bell icon;
     * 'mail' is included per the lab's requirement, but needs real
     * SMTP settings in .env to actually send — harmless to leave in
     * even if mail isn't configured, since queued jobs just fail
     * silently into the failed_jobs table rather than breaking the app.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * What gets stored in the 'data' JSON column, and what the
     * bell-icon dropdown in layouts/app.blade.php reads from.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'sku' => $this->product->sku,
            'current_quantity' => $this->product->quantity,
            'reorder_level' => $this->product->reorder_level,
            'suggested_action' => $this->product->isOutOfStock()
                ? 'Reorder immediately — item is out of stock.'
                : 'Reorder soon — quantity has reached the reorder level.',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Low Stock Alert: {$this->product->name}")
            ->greeting("Hi {$notifiable->name},")
            ->line("\"{$this->product->name}\" (SKU: {$this->product->sku}) is running low.")
            ->line("Current quantity: {$this->product->quantity}")
            ->line("Reorder level: {$this->product->reorder_level}")
            ->line($this->product->isOutOfStock() ? 'This item is now out of stock.' : 'Consider reordering soon.')
            ->action('View Product', route('products.show', $this->product))
            ->line('Thank you for using BuildRight Hardware Inventory System.');
    }
}