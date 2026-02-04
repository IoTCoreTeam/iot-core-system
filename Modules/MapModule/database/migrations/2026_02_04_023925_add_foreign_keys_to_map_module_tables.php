<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });

        Schema::table('fingerprints', function (Blueprint $table) {
            $table->foreign('map_id')->references('id')->on('maps')->onDelete('cascade');
        });

        Schema::table('fingerprint_rssi', function (Blueprint $table) {
            $table->foreign('fingerprint_id')->references('id')->on('fingerprints')->onDelete('cascade');
            $table->foreign('access_point_id')->references('id')->on('access_points')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maps', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
        });

        Schema::table('fingerprints', function (Blueprint $table) {
            $table->dropForeign(['map_id']);
        });

        Schema::table('fingerprint_rssi', function (Blueprint $table) {
            $table->dropForeign(['fingerprint_id']);
            $table->dropForeign(['access_point_id']);
        });
    }
};
