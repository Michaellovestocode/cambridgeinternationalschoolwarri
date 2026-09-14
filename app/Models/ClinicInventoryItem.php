<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicInventoryItem extends Model
{
    protected $fillable = ['name', 'category', 'quantity', 'minimum_quantity', 'unit', 'is_active'];

    protected $casts = ['quantity' => 'decimal:2', 'minimum_quantity' => 'decimal:2', 'is_active' => 'boolean'];

    public function transactions() { return $this->hasMany(ClinicInventoryTransaction::class, 'inventory_item_id')->latest(); }

    public function isLowStock(): bool { return (float) $this->quantity <= (float) $this->minimum_quantity; }
}
