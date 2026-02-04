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
        Schema::create('fingerprint_rssi', function (Blueprint $table) {
            $table->unsignedBigInteger('fingerprint_id');
            $table->unsignedBigInteger('access_point_id');
            $table->integer('rssi');
            $table->primary(['fingerprint_id', 'access_point_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_rssi');
    }
};
