<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category',
        'quantity',
        'reorder_level',
        'unit_price',
        'supplier',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    // ---------- Relationships ----------

    /**
     * All inventory transactions for this product.
     */
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /**
     * The single most recent transaction for this product.
     */
    public function latestTransaction()
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    // ---------- Business Logic Helpers ----------

    public function isLowStock(): bool
    {
        return $this->quantity > 0 && $this->quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Human-readable stock status: 'In Stock', 'Low Stock', or 'Out of Stock'.
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * Bootstrap color class matching the status above, for badges.
     */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        if ($this->isLowStock()) {
            return 'warning';
        }
        return 'success';
    }

    public function getTotalValueAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    // ---------- Query Scopes ----------

    /**
     * Products at or below reorder level, but still above zero.
     * Usage: Product::lowStock()->get()
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level')
                      ->where('quantity', '>', 0);
    }

    /**
     * Products with zero or negative quantity.
     * Usage: Product::outOfStock()->get()
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', '<=', 0);
    }

    /**
     * Products in a specific category.
     * Usage: Product::inCategory('Lumber')->get()
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}