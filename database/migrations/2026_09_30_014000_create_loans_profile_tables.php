<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('borrower_id')->constrained('users')->cascadeOnDelete();
            $table->date('loan_date');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'borrowed', 'returned', 'overdue'])->default('pending');
            $table->enum('condition_before', ['baik', 'kurang_baik'])->default('baik');
            $table->enum('condition_after', ['baik', 'kurang_baik', 'rusak_berat'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('school_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('school_name');
            $table->string('npsn')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
        Schema::dropIfExists('asset_loans');
    }
};
