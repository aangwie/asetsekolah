<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KibDNetwork extends Model
{
    protected $fillable = ['asset_id', 'construction_type', 'length', 'width', 'address', 'document_number'];
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
