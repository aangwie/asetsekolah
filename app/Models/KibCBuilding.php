<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KibCBuilding extends Model
{
    protected $fillable = ['asset_id', 'building_condition', 'is_concrete', 'floor_area', 'address', 'document_number'];
    protected $casts = ['is_concrete' => 'boolean'];
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
