<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kib_e_others', function (Blueprint $table) {
            if (! Schema::hasColumn('kib_e_others', 'book_title')) $table->string('book_title')->nullable()->after('book_title_author');
            if (! Schema::hasColumn('kib_e_others', 'book_author')) $table->string('book_author')->nullable()->after('book_title');
            if (! Schema::hasColumn('kib_e_others', 'publication_year')) $table->smallInteger('publication_year')->nullable()->after('book_author');
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kib_a_lands` MODIFY COLUMN `address` TEXT NULL');
            DB::statement('ALTER TABLE `kib_a_lands` MODIFY COLUMN `land_use` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `kib_c_buildings` MODIFY COLUMN `address` TEXT NULL');
            DB::statement('ALTER TABLE `kib_d_networks` MODIFY COLUMN `construction_type` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `kib_d_networks` MODIFY COLUMN `address` TEXT NULL');
        }
    }

    public function down(): void
    {
        Schema::table('kib_e_others', function (Blueprint $table) {
            $table->dropColumn(['book_title', 'book_author', 'publication_year']);
        });
    }
};
