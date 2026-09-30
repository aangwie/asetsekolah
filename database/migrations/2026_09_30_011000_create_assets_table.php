<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->enum('kib_type', ['A', 'B', 'C', 'D', 'E']);
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('acquisition_date');
            $table->decimal('acquisition_value', 15, 2)->default(0);
            $table->enum('condition', ['baik', 'kurang_baik', 'rusak_berat'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'dipelihara', 'dihapus'])->default('tersedia');
            $table->string('qr_code_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['kib_type', 'status', 'condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
