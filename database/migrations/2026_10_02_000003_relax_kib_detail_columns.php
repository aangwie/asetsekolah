<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `kib_a_lands` MODIFY COLUMN `surface_area` DOUBLE NULL');
            DB::statement('ALTER TABLE `kib_a_lands` MODIFY COLUMN `certificate_number` VARCHAR(255) NULL');
            DB::statement('ALTER TABLE `kib_b_equipments` MODIFY COLUMN `brand` VARCHAR(255) NULL');
        }
    }

    public function down(): void {}
};
