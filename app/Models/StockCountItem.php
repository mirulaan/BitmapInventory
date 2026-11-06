<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_count_id',
        'inventory_item_id',
        'expected_location_id',
        'counted_location_id',
        'scanned_serial_number',
        'status',
        'reconciled',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'reconciled' => 'boolean',
            'scanned_at' => 'datetime',
        ];
    }

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function expectedLocation()
    {
        return $this->belongsTo(Location::class, 'expected_location_id');
    }

    public function countedLocation()
    {
        return $this->belongsTo(Location::class, 'counted_location_id');
    }
}
