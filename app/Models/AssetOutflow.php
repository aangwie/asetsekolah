<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetOutflow extends Model
{
    protected $fillable = ['asset_id', 'quantity', 'returned_quantity', 'outflow_date', 'location_type', 'borrower_name', 'loan_date', 'return_date', 'notes'];

    protected $casts = ['quantity' => 'integer', 'returned_quantity' => 'integer', 'outflow_date' => 'date', 'loan_date' => 'date', 'return_date' => 'date'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }

    public function borrowedQty(): int { return (int) ($this->quantity ?? 1); }

    public function returnedQty(): int { return (int) ($this->returned_quantity ?? 0); }

    public function outstandingQty(): int { return max(0, $this->borrowedQty() - $this->returnedQty()); }

    public function isFullyReturned(): bool { return $this->location_type === 'Luar Sekolah' && $this->outstandingQty() === 0 && ($this->return_date !== null || $this->returnedQty() > 0); }

    public function isPartiallyReturned(): bool { return $this->outstandingQty() > 0 && $this->returnedQty() > 0; }
}
