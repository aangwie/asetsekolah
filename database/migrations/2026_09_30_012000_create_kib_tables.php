<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kib_a_lands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->float('surface_area');
            $table->string('certificate_number');
            $table->date('certificate_date')->nullable();
            $table->text('address');
            $table->string('land_use');
            $table->timestamps();
        });

        Schema::create('kib_b_equipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('size_material')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->timestamps();
        });

        Schema::create('kib_c_buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('building_condition')->default('Tidak Bertingkat');
            $table->boolean('is_concrete')->default(true);
            $table->float('floor_area')->nullable();
            $table->text('address');
            $table->string('document_number')->nullable();
            $table->timestamps();
        });

        Schema::create('kib_d_networks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('construction_type');
            $table->float('length')->nullable();
            $table->float('width')->nullable();
            $table->text('address');
            $table->string('document_number')->nullable();
            $table->timestamps();
        });

        Schema::create('kib_e_others', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('book_title_author')->nullable();
            $table->string('art_spec')->nullable();
            $table->string('animal_type_size')->nullable();
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kib_e_others');
        Schema::dropIfExists('kib_d_networks');
        Schema::dropIfExists('kib_c_buildings');
        Schema::dropIfExists('kib_b_equipments');
        Schema::dropIfExists('kib_a_lands');
    }
};
