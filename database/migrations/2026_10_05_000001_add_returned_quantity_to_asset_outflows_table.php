<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_outflows', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_outflows', 'returned_quantity')) $table->unsignedInteger('returned_quantity')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('asset_outflows', function (Blueprint $table) {
            if (Schema::hasColumn('asset_outflows', 'returned_quantity')) $table->dropColumn('returned_quantity');
        });
    }
};
