<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class BhpTransaction extends Model
{
    protected $fillable = ['bhp_item_id', 'location_id', 'user_id', 'type', 'quantity', 'reference_number', 'recipient_or_supplier', 'transaction_date', 'notes'];
    protected $casts = ['transaction_date' => 'date'];

    public function item(): BelongsTo { return $this->belongsTo(BhpItem::class, 'bhp_item_id'); }
    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    protected static function booted(): void
    {
        static::creating(function (self $t) {
            if ($t->quantity < 1) throw new \InvalidArgumentException('Qty >= 1.');
        });
        static::created(function (self $t) {
            $delta = $t->type === 'in' ? $t->quantity : -$t->quantity;
            DB::table('bhp_items')->where('id', $t->bhp_item_id)->increment('current_stock', $delta);
        });
        static::updated(function (self $t) {
            $diff = $t->quantity - (int) $t->getOriginal('quantity');
            if ($diff !== 0 && (int) $t->bhp_item_id === (int) $t->getOriginal('bhp_item_id') && $t->type === $t->getOriginal('type')) {
                DB::table('bhp_items')->where('id', $t->bhp_item_id)->increment('current_stock', $t->type === 'in' ? $diff : -$diff);
            }
        });
        static::deleted(function (self $t) {
            $delta = $t->type === 'in' ? $t->quantity : -$t->quantity;
            DB::table('bhp_items')->where('id', $t->bhp_item_id)->increment('current_stock', -$delta);
        });
    }
}
