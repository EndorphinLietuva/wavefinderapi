<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * This migration creates a staging table for radio stations used for swapping (updating) data instantly.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('radio_stations_staging')) {
            Schema::dropIfExists('radio_stations_staging');
        }

        DB::statement('CREATE TABLE radio_stations_staging (LIKE radio_stations INCLUDING ALL)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_stations_staging');
    }
};
