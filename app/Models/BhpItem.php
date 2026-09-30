<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BhpItem extends Model
{
    protected $fillable = ['code', 'name', 'category', 'unit', 'initial_stock', 'current_stock', 'minimum_stock', 'unit_price'];
    protected $casts = ['unit_price' => 'decimal:2'];

    public function transactions(): HasMany { return $this->hasMany(BhpTransaction::class); }
    public function getIsLowStockAttribute(): bool { return $this->current_stock <= $this->minimum_stock; }
}
