<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'category',
        'unit_price',
        'min_stock_level',
        'description',
        'image_path',
    ];

    protected $casts = [
        'min_stock_level' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    /**
     * Relationship: One Product has many physical Inventory Items.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Check if the product has hit or breached its minimum stock alert threshold.
     */
    public function isLowStock(int $inStockQty): bool
    {
        return $this->min_stock_level > 0 && $inStockQty <= $this->min_stock_level;
    }

    /**
     * Get the public URL for the product image/icon.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $cleanPath = str_replace('\\', '/', $this->image_path);

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/'.ltrim($cleanPath, '/'));
        }

        return null;
    }
}
