<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'quantity')) $table->unsignedInteger('quantity')->default(1)->after('acquisition_value');
            if (! Schema::hasColumn('assets', 'unit_price')) $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_price']);
        });
    }
};