<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'transaction_type',
        'from_location_id',
        'to_location_id',
        'from_status',
        'to_status',
        'quantity',
        'batch_no',
        'unit_cost',
        'reference_number',
        'notes',
        'person_in_charge',
        'loan_form_no',
        'po_customer',
        'po_supplier',
        'loan_start_date',
        'loan_end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loan_start_date' => 'date',
            'loan_end_date' => 'date',
            'quantity' => 'integer',
        ];
    }

    /**
     * The hardware unit associated with this transaction.
     */
    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Alias for inventoryItem (used in audit log queries and views).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    /**
     * Origin location for the transaction.
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * Destination location for the transaction.
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * Fallback location (points to destination location).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * The client currently associated with the hardware unit.
     */
    public function client(): HasOneThrough
    {
        return $this->hasOneThrough(
            Client::class,
            InventoryItem::class,
            'id',
            'id',
            'inventory_item_id',
            'client_id'
        );
    }

    /**
     * The staff member / user who recorded this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
