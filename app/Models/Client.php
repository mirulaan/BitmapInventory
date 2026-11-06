<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'company_name',
        'contact_person',
        'contact_no',
        'email',
    ];

    /**
     * Relationship: Hardware items assigned or loaned to this client.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Relationship: Hardware units currently on active loan to this client.
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(InventoryItem::class)->where('status', 'on_loan');
    }
}
