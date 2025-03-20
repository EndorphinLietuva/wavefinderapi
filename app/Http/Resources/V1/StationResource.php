<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FavoriteStationService;

class StationResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$data = [
			"station_uuid" => $this->station_uuid,
			"change_uuid" => $this->change_uuid,
			"server_uuid" => $this->server_uuid,
			"name" => $this->name,
			"url" => $this->url,
			"url_resolved" => $this->url_resolved,
			"home_page" => $this->home_page,
			"favicon" => $this->favicon,
			"tags" => $this->tags,
			"country_code" => $this->country_code,
			"iso_3166_2" => $this->iso_3166_2,
			"state" => $this->state,
			"language" => $this->language,
			"language_codes" => $this->language_codes,
			"votes" => $this->votes,
			"last_change_time" => $this->last_change_time,
			"codec" => $this->codec,
			"bit_rate" => $this->bit_rate,
			"hls" => $this->hls,
			"last_check_ok" => $this->last_check_ok,
			"last_check_time" => $this->last_check_time,
			"last_check_ok_time" => $this->last_check_ok_time,
			"last_local_check_time" => $this->last_local_check_time,
			"click_timestamp" => $this->click_timestamp,
			"click_count" => $this->click_count,
			"click_trend" => $this->click_trend,
			"ssl_error" => $this->ssl_error,
			"geo_lat" => $this->geo_lat,
			"geo_long" => $this->geo_long,
			"geo_distance" => $this->geo_distance,
			"has_extended_info" => $this->has_extended_info,
			"created_at" => $this->created_at,
			"updated_at" => $this->updated_at
		];

		// Add is_favorite if the user is authenticated
		if ($request->user()) {
			$favoriteService = app(FavoriteStationService::class);
			$data["is_favorite"] = $favoriteService->isFavorite(
				$request->user(),
				$this->station_uuid
			);
		}

		return $data;
	}
}
