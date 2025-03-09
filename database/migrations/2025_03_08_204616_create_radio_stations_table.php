<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('radio_stations', function (Blueprint $table) {
            $table->uuid('station_uuid')->primary();
            $table->uuid('change_uuid')->nullable();
            $table->uuid('server_uuid')->nullable();
            $table->string('name', 2048)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('url_resolved', 2048)->nullable();
            $table->string('home_page', 2048)->nullable();
            $table->string('favicon', 2048)->nullable();
            $table->string('tags', 1024)->nullable(); 
            $table->char('country_code', 2)->nullable();
            $table->string('iso_3166_2')->nullable(); 
            $table->string('state')->nullable();
            $table->string('language')->nullable();
            $table->string('language_codes')->nullable();
            $table->integer('votes')->nullable();
            $table->dateTime('last_change_time');
            $table->string('codec')->nullable();
            $table->integer('bit_rate')->nullable();
            $table->boolean('hls')->nullable();
            $table->boolean('last_check_ok')->nullable();
            $table->dateTime('last_check_time')->nullable();
            $table->dateTime('last_check_ok_time')->nullable();
            $table->dateTime('last_local_check_time')->nullable();
            $table->dateTime('click_timestamp')->nullable();
            $table->integer('click_count')->nullable();
            $table->integer('click_trend')->nullable();
            $table->boolean('ssl_error')->nullable();
            $table->double('geo_lat')->nullable();
            $table->double('geo_long')->nullable();
            $table->double('geo_distance')->nullable();
            $table->boolean('has_extended_info')->nullable();
            $table->timestamps();

            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_stations');
    }
};
