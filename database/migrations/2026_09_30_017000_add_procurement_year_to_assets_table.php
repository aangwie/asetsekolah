<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->smallInteger('procurement_year')->storedAs('YEAR(acquisition_date)')->after('acquisition_date');
            $table->index('procurement_year');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['procurement_year']);
            $table->dropColumn('procurement_year');
        });
    }
};
