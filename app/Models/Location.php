<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['code', 'name', 'building_id', 'pic_user_id'];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
