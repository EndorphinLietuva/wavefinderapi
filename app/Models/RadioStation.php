<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioStation extends Model
{
	protected $primaryKey = "stationuuid";
	public $incrementing = false;
	protected $keyType = "string";

	protected $fillable = [
		"stationuuid",
		"changeuuid",
		"serveruuid",
		"name",
		"url",
		"url_resolved",
		"homepage",
		"favicon",
		"tags",
		"country",
		"countrycode",
		"iso_3166_2",
		"state",
		"language",
		"languagecodes",
		"votes",
		"lastchangetime",
		"codec",
		"bitrate",
		"hls",
		"lastcheckok",
		"lastchecktime",
		"lastcheckoktime",
		"lastlocalchecktime",
		"clicktimestamp",
		"clickcount",
		"clicktrend",
		"ssl_error",
		"geo_lat",
		"geo_long",
		"geo_distance",
		"has_extended_info"
	];
}
