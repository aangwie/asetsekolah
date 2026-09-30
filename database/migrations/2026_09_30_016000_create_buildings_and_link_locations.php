<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('building_id')->nullable()->after('name')->constrained()->nullOnDelete();
        });

        // Migrate legacy free-text building_name into buildings master.
        if (Schema::hasColumn('locations', 'building_name')) {
            $names = DB::table('locations')->select('building_name')->distinct()->whereNotNull('building_name')->where('building_name', '!=', '')->pluck('building_name');
            foreach ($names as $name) {
                $id = DB::table('buildings')->insertGetId([
                    'code' => 'GDG-'.strtoupper(substr(md5($name), 0, 6)),
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('locations')->where('building_name', $name)->update(['building_id' => $id]);
            }
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('building_name');
            });
        }
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('building_name')->nullable();
        });
        Schema::table('locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('building_id');
        });
        Schema::dropIfExists('buildings');
    }
};
