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
            $table->uuid('stationuuid')->primary();
            $table->uuid('changeuuid')->nullable();
            $table->uuid('serveruuid')->nullable();
            $table->string('name');
            $table->string('url');
            $table->string('url_resolved');
            $table->string('homepage')->nullable();
            $table->string('favicon')->nullable();
            $table->string('tags')->nullable();
            $table->string('country')->nullable();
            $table->char('countrycode', 2)->nullable();
            $table->string('iso_3166_2')->nullable();
            $table->string('state')->nullable();
            $table->string('language')->nullable();
            $table->string('languagecodes')->nullable();
            $table->integer('votes')->nullable();
            $table->dateTime('lastchangetime');
            $table->string('codec')->nullable();
            $table->integer('bitrate')->nullable();
            $table->boolean('hls')->nullable();
            $table->boolean('lastcheckok');
            $table->dateTime('lastchecktime');
            $table->dateTime('lastcheckoktime');
            $table->dateTime('lastlocalchecktime');
            $table->dateTime('clicktimestamp');
            $table->integer('clickcount')->nullable();
            $table->integer('clicktrend')->nullable();
            $table->boolean('ssl_error');
            $table->double('geo_lat')->nullable();
            $table->double('geo_long')->nullable();
            $table->double('geo_distance')->nullable();
            $table->boolean('has_extended_info')->nullable();
            $table->timestamps();

            $table->index(['country']);
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
