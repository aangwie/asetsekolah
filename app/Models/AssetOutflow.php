<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetOutflow extends Model
{
    protected $fillable = ['asset_id', 'outflow_date', 'location_type', 'borrower_name', 'loan_date', 'return_date', 'notes'];

    protected $casts = ['outflow_date' => 'date', 'loan_date' => 'date', 'return_date' => 'date'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
