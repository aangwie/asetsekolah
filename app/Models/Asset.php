<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    protected $fillable = ['asset_code', 'name', 'kib_type', 'location_id', 'acquisition_date', 'acquisition_value', 'quantity', 'unit_price', 'funding_source', 'proof_path', 'condition', 'status', 'qr_code_path', 'notes'];

    protected $casts = ['acquisition_date' => 'date', 'procurement_year' => 'integer', 'acquisition_value' => 'decimal:2', 'quantity' => 'integer', 'unit_price' => 'decimal:2'];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function kibA(): HasOne { return $this->hasOne(KibALand::class); }
    public function kibB(): HasOne { return $this->hasOne(KibBEquipment::class); }
    public function kibC(): HasOne { return $this->hasOne(KibCBuilding::class); }
    public function kibD(): HasOne { return $this->hasOne(KibDNetwork::class); }
    public function kibE(): HasOne { return $this->hasOne(KibEOther::class); }

    public function loans(): HasMany { return $this->hasMany(AssetLoan::class); }

    public function outflows(): HasMany { return $this->hasMany(AssetOutflow::class); }

    // ponytail: QR gen skip, add when Fase 2 (simplesoftwareio/simple-qrcode).
    public function detail(): ?Model
    {
        return match ($this->kib_type) {
            'A' => $this->kibA, 'B' => $this->kibB, 'C' => $this->kibC,
            'D' => $this->kibD, 'E' => $this->kibE, default => null,
        };
    }
}
