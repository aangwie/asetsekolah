<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    protected $fillable = ['asset_id', 'borrower_id', 'loan_date', 'expected_return_date', 'actual_return_date', 'status', 'condition_before', 'condition_after', 'notes'];
    protected $casts = ['loan_date' => 'date', 'expected_return_date' => 'date', 'actual_return_date' => 'date'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function borrower(): BelongsTo { return $this->belongsTo(User::class, 'borrower_id'); }
}
