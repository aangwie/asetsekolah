<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_outflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->date('outflow_date');
            $table->enum('location_type', ['Sekolah', 'Luar Sekolah'])->default('Sekolah');
            $table->string('borrower_name')->nullable();
            $table->date('loan_date')->nullable();
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['asset_id', 'outflow_date', 'location_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_outflows');
    }
};
