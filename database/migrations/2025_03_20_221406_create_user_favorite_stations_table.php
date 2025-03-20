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
        Schema::create('user_favorite_stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->uuid('station_uuid');
            $table->foreign('station_uuid')
                  ->references('station_uuid')
                  ->on('radio_stations')
                  ->onDelete('cascade');
            $table->timestamps();
            
            // Prevent duplicate favorites
            $table->unique(['user_id', 'station_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_favorite_stations');
    }
};
