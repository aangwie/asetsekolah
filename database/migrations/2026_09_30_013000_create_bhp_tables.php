<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bhp_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('ATK');
            $table->string('unit', 20)->default('pcs');
            $table->integer('initial_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('bhp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhp_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity');
            $table->string('reference_number')->nullable();
            $table->string('recipient_or_supplier')->nullable();
            $table->date('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['bhp_item_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bhp_transactions');
        Schema::dropIfExists('bhp_items');
    }
};
