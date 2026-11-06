<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'branch', 'floor', 'parent_id'];

    /**
     * Relationship: Items located at this location.
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    /**
     * Automatically format the location into "Branch · Floor · Name" or just "Name"
     */
    public function getFullPathAttribute(): string
    {
        $parts = array_filter([$this->branch, $this->floor, $this->name]);

        return ! empty($parts) ? implode(' · ', $parts) : ($this->name ?? 'Warehouse Location');
    }
}
