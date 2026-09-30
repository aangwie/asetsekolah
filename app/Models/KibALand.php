<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KibALand extends Model
{
    protected $fillable = ['asset_id', 'surface_area', 'certificate_number', 'certificate_date', 'address', 'land_use'];
    protected $casts = ['certificate_date' => 'date'];
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
