<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KibEOther extends Model
{
    protected $fillable = ['asset_id', 'book_title_author', 'book_title', 'book_author', 'publication_year', 'art_spec', 'animal_type_size', 'quantity'];
    protected $casts = ['publication_year' => 'integer'];
    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
