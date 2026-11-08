<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * A transaction belongs to a product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * A transaction belongs to the user who performed it.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Query scope: only stock-in transactions.
     * Usage: InventoryTransaction::stockIn()->get()
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', 'stock_in');
    }

    /**
     * Query scope: only stock-out transactions.
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', 'stock_out');
    }

    /**
     * Query scope: newest first.
     * Usage: InventoryTransaction::recent()->take(10)->get()
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}