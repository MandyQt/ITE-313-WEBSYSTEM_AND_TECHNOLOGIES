<?php

namespace App\Observers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Exception;

class InventoryTransactionObserver
{
    public function created(InventoryTransaction $transaction): void
    {
        $product = Product::lockForUpdate()->find($transaction->product_id);

        if (!$product) {
            throw new Exception("Product not found for transaction #{$transaction->id}.");
        }

        $newQuantity = $product->quantity + $transaction->quantity;

        if ($newQuantity < 0) {
            throw new Exception(
                "This operation would result in negative inventory for '{$product->name}'. " .
                "Current stock: {$product->quantity}, requested change: {$transaction->quantity}."
            );
        }

        $product->quantity = $newQuantity;
        $product->save();

        $this->checkLowStock($product);
    }

    /**
     * Notifies admin users if this product just became low/out of stock.
     *
     * Duplicate-prevention (lab's "Advanced Consideration"): rather than
     * tracking a separate "last notified" timestamp, this checks whether
     * an UNREAD low-stock notification for this exact product already
     * exists. If one does, the admin hasn't acted on it yet, so sending
     * another would just be noise. Once they mark it read (or it's acted
     * on), a future drop in stock can notify again.
     */
    protected function checkLowStock(Product $product): void
    {
        if (!$product->isLowStock() && !$product->isOutOfStock()) {
            return;
        }

        $admins = User::where('is_admin', true)->get();

        foreach ($admins as $admin) {
            $alreadyNotified = $admin->unreadNotifications()
                ->where('data->product_id', $product->id)
                ->exists();

            if (!$alreadyNotified) {
                $admin->notify(new LowStockNotification($product));
            }
        }
    }
}