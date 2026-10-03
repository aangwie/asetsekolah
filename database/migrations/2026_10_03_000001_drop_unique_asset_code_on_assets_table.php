<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ponytail: index biasa cukup; kembalikan unique bila butuh dedup ketat
        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropUnique(['asset_code']);
            });
        } catch (\Throwable) {
            // index mungkin bernama lain / sudah dicabut, lanjut
            try {
                Schema::table('assets', function (Blueprint $table) {
                    $table->dropUnique('assets_asset_code_unique');
                });
            } catch (\Throwable) {
            }
        }
        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->index('asset_code');
            });
        } catch (\Throwable) {
            // index sudah ada, abaikan
        }
    }

    public function down(): void
    {
        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->dropIndex(['asset_code']);
            });
        } catch (\Throwable) {
        }
        try {
            Schema::table('assets', function (Blueprint $table) {
                $table->unique('asset_code');
            });
        } catch (\Throwable) {
        }
    }
};
