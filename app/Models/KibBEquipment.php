<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KibBEquipment extends Model
{
    protected $fillable = ['asset_id', 'brand', 'size_material', 'chassis_number', 'engine_number', 'serial_number'];
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
