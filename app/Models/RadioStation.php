<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadioStation extends Model
{
	protected $primaryKey = "station_uuid";
	public $incrementing = false;
	protected $keyType = "string";

	protected $fillable = [
		"station_uuid",
		"change_uuid",
		"server_uuid",
		"name",
		"url",
		"url_resolved",
		"home_page",
		"favicon",
		"tags",
		"country_code",
		"iso_3166_2",
		"state",
		"language",
		"language_codes",
		"votes",
		"last_change_time",
		"codec",
		"bitrate",
		"hls",
		"last_check_ok",
		"last_check_time",
		"last_check_ok_time",
		"last_local_check_time",
		"click_timestamp",
		"click_count",
		"click_trend",
		"ssl_error",
		"geo_lat",
		"geo_long",
		"geo_distance",
		"has_extended_info"
	];
}
