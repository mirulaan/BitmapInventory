<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'client_id',
        'serial_number',
        'batch_no',
        'unit_cost',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function currentLoan()
    {
        // Fetches only the most recent 'dispatch_loan' transaction for this item
        return $this->hasOne(InventoryTransaction::class)
                    ->where('transaction_type', 'dispatch_loan')
                    ->latestOfMany();
    }
}
