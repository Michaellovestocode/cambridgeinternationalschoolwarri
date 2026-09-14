<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicInventoryTransaction extends Model
{
    protected $fillable = ['inventory_item_id', 'recorded_by', 'type', 'quantity', 'reason'];

    public function item() { return $this->belongsTo(ClinicInventoryItem::class, 'inventory_item_id'); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
}
