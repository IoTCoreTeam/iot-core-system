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
        Schema::table('control_urls', function (Blueprint $table) {
            $table->string('controller_id')->nullable()->after('node_id');
            $table->unique('controller_id', 'control_urls_controller_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('control_urls', function (Blueprint $table) {
            $table->dropUnique('control_urls_controller_id_unique');
            $table->dropColumn('controller_id');
        });
    }
};
