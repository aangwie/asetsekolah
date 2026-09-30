<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->string('logo_pemkab_path')->nullable()->after('logo_path');
            $table->string('logo_sekolah_path')->nullable()->after('logo_pemkab_path');
        });
    }

    public function down(): void
    {
        Schema::table('school_profiles', function (Blueprint $table) {
            $table->dropColumn(['logo_pemkab_path', 'logo_sekolah_path']);
        });
    }
};
